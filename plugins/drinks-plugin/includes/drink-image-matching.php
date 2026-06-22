<?php
/**
 * Shared drink image title matching and random alternate selection.
 * Used by cocktail-images (blocks, srcset, AJAX) and drinks-plugin (lightboxes).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Two-letter filename codes excluded from word matching (prefix/suffix).
 *
 * @return string[]
 */
function drinks_get_match_ignored_codes() {
    return array('T2', 'AU', 'SO', 'SU', 'SP', 'FP', 'EV', 'RO', 'WI');
}

/**
 * Extract significant words (>=3 letters) from an image title for matching.
 * Client display keys use ucNormalizeTitle() in modules/cocktail-images/src/image-utils.js.
 */
function drinks_extract_match_words($title) {
    if ($title === null || $title === '') {
        return array();
    }

    $text = (string) $title;

    if (strpos($text, ':') !== false) {
        $text = substr($text, 0, strpos($text, ':'));
    }

    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/^T2-/i', '', $text);

    $suffix_codes = array_values(array_filter(
        drinks_get_match_ignored_codes(),
        function ($code) {
            return $code !== 'T2';
        }
    ));
    $codes_pattern = implode('|', array_map('preg_quote', $suffix_codes));
    $previous = null;
    while ($previous !== $text) {
        $previous = $text;
        $text = preg_replace('/[\s_-]+(?:\d+|[A-Za-z])$/u', '', $text);
        $text = preg_replace('/(?:' . $codes_pattern . ')\d+$/iu', '', $text);
        $text = preg_replace('/[\s_-]+(?:' . $codes_pattern . ')$/iu', '', $text);
    }
    $text = trim($text, " \t\n\r\0\x0B_-");

    $text = remove_accents($text);
    $text = strtolower($text);
    $text = preg_replace("/[''`´]|[\x{2018}\x{2019}\x{2032}]|[\x{02BC}]/u", '', $text);
    $text = preg_replace('/[^a-z0-9\s]/u', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    if ($text === '') {
        return array();
    }

    $significant = array();

    foreach (explode(' ', $text) as $word) {
        if (strlen($word) < 3) {
            continue;
        }
        if (in_array(strtoupper($word), drinks_get_match_ignored_codes(), true)) {
            continue;
        }
        $significant[] = $word;
    }

    return $significant;
}

/**
 * Short drink name for visible captions (mirrors ucNormalizeTitle() in image-utils.js).
 */
function drinks_normalize_title_for_display($title, $preserve_capitalization = true) {
    if ($title === null || $title === '') {
        return '';
    }

    $base = (string) $title;
    if (strpos($base, ':') !== false) {
        $base = substr($base, 0, strpos($base, ':'));
    }

    $base = preg_replace('/^T2-/i', '', $base);
    $base = str_replace(array('-', '_'), ' ', $base);
    $base = preg_replace('/\s+/', ' ', trim($base));
    $base = preg_replace('/(AU|SO|SU|SP|FP|EV|RO|WI)$/i', '', $base);

    $words = array_filter(
        explode(' ', $base),
        function ($word) {
            return strlen($word) >= 3;
        }
    );

    $base = implode(' ', $words);

    if (!$preserve_capitalization) {
        $base = strtolower($base);
    }

    return trim($base);
}

/**
 * Sorted, unique significant words as a single string (display / legacy comparisons).
 */
function drinks_normalize_title_for_matching($title) {
    $words = drinks_extract_match_words($title);
    $words = array_values(array_unique($words));
    sort($words, SORT_STRING);

    return implode(' ', $words);
}

/**
 * True when both titles share the same set of significant words (>=3 letters each).
 */
function drinks_titles_match_significant_words($title_a, $title_b) {
    $words_a = drinks_extract_match_words($title_a);
    $words_b = drinks_extract_match_words($title_b);

    if (empty($words_a) || empty($words_b)) {
        return false;
    }

    $words_a = array_values(array_unique($words_a));
    $words_b = array_values(array_unique($words_b));
    sort($words_a, SORT_STRING);
    sort($words_b, SORT_STRING);

    return $words_a === $words_b;
}

/**
 * Remove dimension patterns like -225x300 from uploaded image URLs.
 */
function drinks_trim_image_dimensions($url) {
    if (!$url) {
        return $url;
    }

    return preg_replace('/-\d+x\d+\.(jpg|jpeg|png|webp)$/i', '.$1', $url);
}

/**
 * Full-size attachment URL (trimmed dimensions, aligned with client cycling).
 */
function drinks_get_original_image_url($attachment_id) {
    $file_path = get_attached_file($attachment_id);
    if (!$file_path) {
        return false;
    }

    $upload_dir = wp_upload_dir();
    $original_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);

    return drinks_trim_image_dimensions($original_url);
}

/**
 * All attachment IDs with the same normalized media title (includes $attachment_id).
 */
function drinks_find_all_matching_attachment_ids($attachment_id) {
    $attachment_id = (int) $attachment_id;
    $title = get_post_field('post_title', $attachment_id);

    if (empty($title)) {
        return array($attachment_id);
    }

    if (stripos($title, 'banner') !== false) {
        return array($attachment_id);
    }

    $normalized_base = drinks_normalize_title_for_matching($title);

    $all_attachments = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ));

    $matching_ids = array();

    foreach ($all_attachments as $candidate_id) {
        $candidate_title = get_post_field('post_title', $candidate_id);
        if (empty($candidate_title)) {
            continue;
        }

        $normalized_candidate = drinks_normalize_title_for_matching($candidate_title);
        if (strcasecmp($normalized_candidate, $normalized_base) === 0) {
            $matching_ids[] = (int) $candidate_id;
        }
    }

    return !empty($matching_ids) ? $matching_ids : array($attachment_id);
}

/**
 * Random pick from title-matched pool.
 *
 * @param bool $exclude_current When true, never return $attachment_id (timed on-page swapping).
 */
function drinks_pick_random_matching_attachment_id($attachment_id, $exclude_current = false) {
    $attachment_id = (int) $attachment_id;
    $pool = drinks_find_all_matching_attachment_ids($attachment_id);

    if (count($pool) <= 1) {
        return $attachment_id;
    }

    if ($exclude_current) {
        $alternates = array_values(array_diff($pool, array($attachment_id)));
        if (!empty($alternates)) {
            return $alternates[array_rand($alternates)];
        }
    }

    return $pool[array_rand($pool)];
}

/**
 * Image attributes for rendered output (blocks, lightboxes, AJAX).
 *
 * @return array<string, mixed>|null
 */
function drinks_get_attachment_image_render_data($attachment_id) {
    $attachment_id = (int) $attachment_id;
    $attachment = get_post($attachment_id);

    if (!$attachment || $attachment->post_type !== 'attachment') {
        return null;
    }

    $src = drinks_get_original_image_url($attachment_id);
    if (!$src) {
        return null;
    }

    $metadata = wp_get_attachment_metadata($attachment_id);
    $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    if ($alt === '') {
        $alt = $attachment->post_title;
    }

    $caption = wp_get_attachment_caption($attachment_id);
    if (empty($caption)) {
        $caption = drinks_normalize_title_for_matching($attachment->post_title);
    }

    $width = isset($metadata['width']) ? (int) $metadata['width'] : 0;
    $height = isset($metadata['height']) ? (int) $metadata['height'] : 0;

    return array(
        'src' => $src,
        'alt' => $alt,
        'attachment_id' => $attachment_id,
        'srcset' => wp_get_attachment_image_srcset($attachment_id),
        'sizes' => wp_get_attachment_image_sizes($attachment_id),
        'width' => $width,
        'height' => $height,
        'data_orig_file' => $src,
        'data_orig_size' => $width && $height ? $width . ',' . $height : '',
        'data_image_title' => $attachment->post_title,
        'data_image_caption' => $caption,
        'data_medium_file' => wp_get_attachment_image_url($attachment_id, 'medium'),
        'data_large_file' => wp_get_attachment_image_url($attachment_id, 'large'),
    );
}

/**
 * Pick a random title-matched alternate and return render data for display.
 *
 * @return array<string, mixed>|null
 */
function drinks_randomize_attachment_for_render($attachment_id) {
    $attachment_id = (int) $attachment_id;
    if ($attachment_id <= 0) {
        return null;
    }

    $picked_id = drinks_pick_random_matching_attachment_id($attachment_id);

    return drinks_get_attachment_image_render_data($picked_id);
}

/**
 * First image attachment ID referenced in drink post block content.
 */
function drinks_extract_content_attachment_id($post_id) {
    $post_id = (int) $post_id;
    $post = get_post($post_id);

    if (!$post || empty($post->post_content)) {
        return 0;
    }

    if (function_exists('parse_blocks')) {
        $find_in_blocks = function ($blocks) use (&$find_in_blocks) {
            foreach ($blocks as $block) {
                if (!empty($block['attrs']['mediaId'])) {
                    return (int) $block['attrs']['mediaId'];
                }

                if (($block['blockName'] ?? '') === 'core/image' && !empty($block['attrs']['id'])) {
                    return (int) $block['attrs']['id'];
                }

                if (!empty($block['innerBlocks'])) {
                    $found = $find_in_blocks($block['innerBlocks']);
                    if ($found > 0) {
                        return $found;
                    }
                }
            }

            return 0;
        };

        $from_blocks = $find_in_blocks(parse_blocks($post->post_content));
        if ($from_blocks > 0) {
            return $from_blocks;
        }
    }

    if (preg_match('/wp-image-(\d+)/', $post->post_content, $matches)) {
        return (int) $matches[1];
    }

    return 0;
}

/**
 * Find a renderable media-library attachment by normalized drink title.
 */
function drinks_find_attachment_id_by_drink_title($drink_title) {
    $normalized = drinks_normalize_title_for_matching($drink_title);
    if ($normalized === '') {
        return 0;
    }

    static $attachment_index = null;

    if ($attachment_index === null) {
        $attachment_index = array();

        foreach (get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
        )) as $attachment_id) {
            $title = get_post_field('post_title', $attachment_id);
            if ($title === '' || stripos($title, 'banner') !== false) {
                continue;
            }

            $key = drinks_normalize_title_for_matching($title);
            if ($key === '') {
                continue;
            }

            if (!isset($attachment_index[$key])) {
                $attachment_index[$key] = array();
            }

            $attachment_index[$key][] = (int) $attachment_id;
        }
    }

    if (empty($attachment_index[$normalized])) {
        return 0;
    }

    $pool = $attachment_index[$normalized];
    shuffle($pool);

    foreach ($pool as $attachment_id) {
        if (drinks_get_attachment_image_render_data($attachment_id)) {
            return $attachment_id;
        }
    }

    return 0;
}

/**
 * Resolve the best renderable attachment for a drink post.
 * Order: featured image, block content mediaId, title-matched media library.
 */
function drinks_resolve_drink_attachment_id($post_id, $drink_title = '') {
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return 0;
    }

    if ($drink_title === '') {
        $drink_title = get_the_title($post_id);
    }

    $candidates = array();

    $featured_id = (int) get_post_thumbnail_id($post_id);
    if ($featured_id > 0) {
        $candidates[] = $featured_id;
    }

    $content_id = drinks_extract_content_attachment_id($post_id);
    if ($content_id > 0) {
        $candidates[] = $content_id;
    }

    $title_match_id = drinks_find_attachment_id_by_drink_title($drink_title);
    if ($title_match_id > 0) {
        $candidates[] = $title_match_id;
    }

    $candidates = array_values(array_unique(array_filter($candidates)));

    foreach ($candidates as $attachment_id) {
        if (drinks_get_attachment_image_render_data($attachment_id)) {
            return $attachment_id;
        }

        $render_data = drinks_randomize_attachment_for_render($attachment_id);
        if ($render_data && !empty($render_data['attachment_id'])) {
            return (int) $render_data['attachment_id'];
        }
    }

    return 0;
}

/**
 * Whether a carousel slide array has a usable image URL.
 */
function drinks_carousel_slide_has_image($slide) {
    return is_array($slide) && !empty($slide['src']) && is_string($slide['src']);
}

/**
 * Pick a random title-matched image for one carousel slide at summon/build time.
 *
 * @param array $drink Drink row from uc_get_drink_posts().
 * @return array{id:int,src:string,alt:string,attachment_id:int}
 */
function drinks_randomize_drink_for_carousel_slide($drink) {
    $post_id = isset($drink['id']) ? (int) $drink['id'] : 0;
    $title = isset($drink['title']) ? $drink['title'] : '';

    $slide = array(
        'id' => $post_id,
        'src' => '',
        'alt' => $title,
        'attachment_id' => 0,
    );

    $attachment_id = drinks_resolve_drink_attachment_id($post_id, $title);

    if ($attachment_id > 0) {
        $render_data = drinks_randomize_attachment_for_render($attachment_id);
        if ($render_data && !empty($render_data['src'])) {
            $slide['src'] = $render_data['src'];
            $slide['attachment_id'] = (int) $render_data['attachment_id'];
            if (!empty($render_data['alt'])) {
                $slide['alt'] = $render_data['alt'];
            }
        }
    }

    if ($slide['src'] === '' && !empty($drink['thumbnail']) && is_string($drink['thumbnail'])) {
        $slide['src'] = $drink['thumbnail'];
        if ($attachment_id > 0) {
            $slide['attachment_id'] = $attachment_id;
        }
    }

    return $slide;
}

/**
 * Resolve a lightbox/carousel image reference to an attachment ID.
 * Accepts attachment IDs or drink post IDs (carousel data-id).
 */
function drinks_resolve_lightbox_attachment_id($image_or_post_id, $post_id = 0) {
    $id = (int) $image_or_post_id;

    if ($id > 0 && get_post_type($id) === 'attachment') {
        return $id;
    }

    $post_id = (int) ($post_id ?: $id);

    if ($post_id > 0) {
        return (int) get_post_thumbnail_id($post_id);
    }

    return 0;
}
