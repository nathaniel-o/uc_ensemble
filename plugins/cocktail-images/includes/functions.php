<?php
/**
 * Global functions for Cocktail Images plugin
 * Provides backward compatibility for functions moved from theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the main plugin instance
 */
function get_cocktail_images_plugin() {
    global $cocktail_images_plugin;
    return $cocktail_images_plugin;
}

/**
 * Global wrapper functions for backward compatibility
 */

function uc_drink_query() {
    $plugin = get_cocktail_images_plugin();
    if ($plugin) {
        return $plugin->uc_drink_query();
    }
    return null;
}

function uc_get_drinks() {
    $plugin = get_cocktail_images_plugin();
    if ($plugin) {
        return $plugin->uc_get_drinks();
    }
    return array();
}

function uc_random_carousel($drink_posts, $num_slides, $show_titles = 0, $show_content = 0) {
    $plugin = get_cocktail_images_plugin();
    if ($plugin) {
        return $plugin->uc_random_carousel($drink_posts, $num_slides, $show_titles, $show_content);
    }
    return '';
}

function uc_filter_carousel($srchStr, $drink_posts, $num_slides, $show_titles = 0, $show_content = 0, $supp_rand = 0) {
    $plugin = get_cocktail_images_plugin();
    if ($plugin) {
        return $plugin->uc_filter_carousel($srchStr, $drink_posts, $num_slides, $show_titles, $show_content, $supp_rand);
    }
    return '';
}

function generate_slideshow_slides($images, $show_titles = 0, $show_content = 0) {
    $plugin = get_cocktail_images_plugin();
    if ($plugin) {
        return $plugin->generate_slideshow_slides($images, $show_titles, $show_content);
    }
    return '';
}

function generate_single_slide($image, $index, $is_duplicate, $show_titles, $show_content) {
    $plugin = get_cocktail_images_plugin();
    if ($plugin) {
        return $plugin->generate_single_slide($image, $index, $is_duplicate, $show_titles, $show_content);
    }
    return '';
}

function uc_generate_metadata_list($post_id) {
    $plugin = get_cocktail_images_plugin();
    if ($plugin) {
        return $plugin->uc_generate_metadata_list($post_id);
    }
    return '';
}
