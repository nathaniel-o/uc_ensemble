<?php
/**
 * Convert legacy group + media-text drink cards to drinks/drink-post-content.
 */
if (!defined('ABSPATH')) {
    exit;
}

function drinks_migrate_find_block(array $blocks, $name) {
    foreach ($blocks as $block) {
        if (($block['blockName'] ?? '') === $name) {
            return $block;
        }
        if (!empty($block['innerBlocks'])) {
            $found = drinks_migrate_find_block($block['innerBlocks'], $name);
            if ($found) {
                return $found;
            }
        }
    }

    return null;
}

function drinks_migrate_block_contains(array $block, $name) {
    if (($block['blockName'] ?? '') === $name) {
        return true;
    }
    foreach ($block['innerBlocks'] ?? array() as $inner) {
        if (drinks_migrate_block_contains($inner, $name)) {
            return true;
        }
    }

    return false;
}

function drinks_migrate_is_empty_noise_block(array $block) {
    $name = $block['blockName'] ?? '';
    if ($name === '') {
        return trim(wp_strip_all_tags($block['innerHTML'] ?? '')) === '';
    }
    if ($name === 'core/paragraph') {
        return trim(wp_strip_all_tags($block['innerHTML'] ?? '')) === '';
    }
    if ($name === 'core/columns') {
        foreach ($block['innerBlocks'] ?? array() as $column) {
            foreach ($column['innerBlocks'] ?? array() as $child) {
                if (!drinks_migrate_is_empty_noise_block($child)) {
                    return false;
                }
            }
        }
        return true;
    }

    return false;
}

function drinks_migrate_is_legacy_drink_card(array $block) {
    $name = $block['blockName'] ?? '';
    if ($name === 'drinks/drink-post-content') {
        return false;
    }
    if ($name === 'core/media-text') {
        return drinks_migrate_block_contains($block, 'core/list');
    }
    if ($name === 'core/group') {
        return drinks_migrate_block_contains($block, 'core/media-text')
            && drinks_migrate_block_contains($block, 'core/list');
    }

    return false;
}

function drinks_migrate_image_attrs_from_media_text(array $media_text) {
    $image_id = isset($media_text['attrs']['mediaId']) ? (int) $media_text['attrs']['mediaId'] : 0;
    $html = (string) ($media_text['innerHTML'] ?? '');
    $url = '';
    $alt = '';
    if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $html, $match)) {
        $url = $match[1];
    }
    if (preg_match('/<img[^>]+alt=["\']([^"\']*)/i', $html, $match)) {
        $alt = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if ($image_id > 0 && $url === '') {
        $url = (string) wp_get_attachment_url($image_id);
    }
    if ($image_id > 0 && $alt === '') {
        $alt = (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
    }

    return array($image_id, $url, $alt);
}

function drinks_migrate_legacy_card_to_block(array $card) {
    $media_text = ('core/media-text' === ($card['blockName'] ?? ''))
        ? $card
        : drinks_migrate_find_block(array($card), 'core/media-text');
    $list = drinks_migrate_find_block(array($card), 'core/list');

    $image_id = 0;
    $image_url = '';
    $image_alt = '';
    if ($media_text) {
        list($image_id, $image_url, $image_alt) = drinks_migrate_image_attrs_from_media_text($media_text);
    }

    $inner_blocks = $list ? array($list) : array();
    $inner_content = $list ? array("\n", null, "\n") : array("\n");

    return array(
        'blockName' => 'drinks/drink-post-content',
        'attrs' => array(
            'imageId' => $image_id,
            'imageUrl' => $image_url,
            'imageAlt' => $image_alt,
            'metadata' => array(
                'categories' => array('drinks', 'content'),
                'patternName' => 'uc-theme-ui/drink-post-content',
                'name' => 'Drink Post Content',
            ),
        ),
        'innerBlocks' => $inner_blocks,
        'innerHTML' => $list ? "\n\n" : "\n",
        'innerContent' => $inner_content,
    );
}

function drinks_convert_legacy_drink_post_content($post_id, $dry_run = false) {
    $post_id = (int) $post_id;
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post') {
        return array('ok' => false, 'reason' => 'not-a-post');
    }
    if (!drinks_post_has_drink_taxonomy($post_id)) {
        return array('ok' => false, 'reason' => 'not-a-drink');
    }
    if (strpos($post->post_content, 'wp:drinks/drink-post-content') !== false) {
        return array('ok' => false, 'reason' => 'already-converted');
    }

    $blocks = parse_blocks($post->post_content);
    $out = array();
    $converted = false;

    foreach ($blocks as $block) {
        if (drinks_migrate_is_empty_noise_block($block)) {
            continue;
        }
        if (!$converted && drinks_migrate_is_legacy_drink_card($block)) {
            $out[] = drinks_migrate_legacy_card_to_block($block);
            $converted = true;
            continue;
        }
        $out[] = $block;
    }

    if (!$converted) {
        return array('ok' => false, 'reason' => 'no-legacy-card');
    }

    $new_content = serialize_blocks($out);
    if ($dry_run) {
        return array('ok' => true, 'dry_run' => true);
    }

    $updated = wp_update_post(
        array(
            'ID' => $post_id,
            'post_content' => wp_slash($new_content),
        ),
        true
    );

    if (is_wp_error($updated)) {
        return array('ok' => false, 'reason' => $updated->get_error_message());
    }

    return array('ok' => true, 'id' => $post_id);
}

function drinks_migrate_legacy_drink_post_content($dry_run = false) {
    $query = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => array('publish', 'draft', 'private', 'pending'),
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'drinks',
                'operator' => 'EXISTS',
            ),
        ),
        'no_found_rows' => true,
    ));

    $converted = 0;
    $skipped = 0;
    $failed = 0;
    $reasons = array();

    foreach ($query->posts as $post_id) {
        $result = drinks_convert_legacy_drink_post_content($post_id, $dry_run);
        if (!empty($result['ok'])) {
            $converted++;
            continue;
        }
        $reason = $result['reason'] ?? 'unknown';
        if ($reason === 'already-converted') {
            $skipped++;
        } else {
            $failed++;
            $reasons[$post_id] = $reason;
        }
    }

    return array(
        'converted' => $converted,
        'skipped' => $skipped,
        'failed' => $failed,
        'reasons' => $reasons,
        'dry_run' => (bool) $dry_run,
    );
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command(
        'drinks migrate-post-content',
        function ($args, $assoc_args) {
            $dry_run = !empty($assoc_args['dry-run']);
            $result = drinks_migrate_legacy_drink_post_content($dry_run);
            WP_CLI::log(sprintf(
                '%s %d drink posts (%d already converted, %d failed)',
                $dry_run ? 'Would convert' : 'Converted',
                $result['converted'],
                $result['skipped'],
                $result['failed']
            ));
            foreach ($result['reasons'] as $id => $reason) {
                WP_CLI::warning($id . ': ' . $reason);
            }
            if ($result['failed'] > 0) {
                WP_CLI::error('Some posts could not be converted.', false);
                return;
            }
            WP_CLI::success($dry_run ? 'Dry run complete.' : 'Migration complete.');
        }
    );
}
