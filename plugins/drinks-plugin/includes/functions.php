<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Accessor for the DrinksPlugin instance
 */
if (!function_exists('get_drinks_plugin')) {
    function get_drinks_plugin() {
        global $drinks_plugin;
        return isset($drinks_plugin) ? $drinks_plugin : null;
    }
}

// Provide global wrappers only if they are not already defined by another plugin
if (!function_exists('uc_drink_query')) {
    function uc_drink_query() {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->uc_drink_query() : null;
    }
}

if (!function_exists('uc_get_drinks')) {
    function uc_get_drinks() {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->uc_get_drinks() : array();
    }
}

if (!function_exists('uc_random_carousel')) {
    function uc_random_carousel($drink_posts, $num_slides, $show_titles = 0, $show_content = 0) {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->uc_random_carousel($drink_posts, $num_slides, $show_titles, $show_content) : '';
    }
}

if (!function_exists('uc_filter_carousel')) {
    function uc_filter_carousel($srchStr, $drink_posts, $num_slides, $show_titles = 0, $show_content = 0, $supp_rand = 0) {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->uc_filter_carousel($srchStr, $drink_posts, $num_slides, $show_titles, $show_content, $supp_rand) : '';
    }
}

if (!function_exists('generate_slideshow_slides')) {
    function generate_slideshow_slides($images, $show_titles = 0, $show_content = 0) {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->generate_slideshow_slides($images, $show_titles, $show_content) : '';
    }
}

if (!function_exists('generate_single_slide')) {
    function generate_single_slide($image, $index, $is_duplicate, $show_titles, $show_content) {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->generate_single_slide($image, $index, $is_duplicate, $show_titles, $show_content) : '';
    }
}

if (!function_exists('uc_drink_query')) {
    function uc_drink_query() {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->uc_drink_query() : null;
    }
}

if (!function_exists('uc_get_drinks')) {
    function uc_get_drinks() {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->uc_get_drinks() : array();
    }
}

if (!function_exists('uc_generate_metadata_list')) {
    function uc_generate_metadata_list($post_id) {
        $plugin = get_drinks_plugin();
        return $plugin ? $plugin->uc_generate_metadata_list($post_id) : '';
    }
}

/**
 * Enable Jetpack carousel functionality on an image
 * Usage: echo uc_enable_jetpack_carousel_on_image($image_html, $image_id, $image_alt);
 */
if (!function_exists('uc_enable_jetpack_carousel_on_image')) {
    function uc_enable_jetpack_carousel_on_image($image_html, $image_id = '', $image_alt = '') {
        // Add carousel attributes to the image
        $image_html = str_replace('<img', '<img data-carousel-enabled="true"', $image_html);
        
        // If we have an image ID, add it as data attribute
        if ($image_id) {
            $image_html = str_replace('<img', '<img data-id="' . esc_attr($image_id) . '"', $image_html);
        }
        
        // If we have alt text, ensure it's set
        if ($image_alt && !strpos($image_html, 'alt=')) {
            $image_html = str_replace('<img', '<img alt="' . esc_attr($image_alt) . '"', $image_html);
        }
        
        return $image_html;
    }
}

/**
 * Create a Jetpack carousel-enabled image with figure wrapper
 * Usage: echo uc_create_jetpack_carousel_image($image_src, $image_alt, $image_id);
 */
if (!function_exists('uc_create_jetpack_carousel_image')) {
    function uc_create_jetpack_carousel_image($image_src, $image_alt = '', $image_id = '') {
        $html = '<figure data-carousel-enabled="true">';
        $html .= '<img src="' . esc_url($image_src) . '" ';
        
        if ($image_alt) {
            $html .= 'alt="' . esc_attr($image_alt) . '" ';
        }
        
        if ($image_id) {
            $html .= 'data-id="' . esc_attr($image_id) . '" ';
        }
        
        $html .= 'class="jetpack-carousel-enabled-image" />';
        
        if ($image_alt) {
            $html .= '<figcaption>' . esc_html($image_alt) . '</figcaption>';
        }
        
        $html .= '</figure>';
        
        return $html;
    }
}

/**
 * Enable Jetpack carousel functionality on an image (alias for backward compatibility)
 * @deprecated Use uc_enable_jetpack_carousel_on_image instead
 */
if (!function_exists('uc_enable_carousel_on_image')) {
    function uc_enable_carousel_on_image($image_html, $image_id = '', $image_alt = '') {
        return uc_enable_jetpack_carousel_on_image($image_html, $image_id, $image_alt);
    }
}

/**
 * Create a carousel-enabled image with figure wrapper (alias for backward compatibility)
 * @deprecated Use uc_create_jetpack_carousel_image instead
 */
if (!function_exists('uc_create_carousel_image')) {
    function uc_create_carousel_image($image_src, $image_alt = '', $image_id = '') {
        return uc_create_jetpack_carousel_image($image_src, $image_alt, $image_id);
    }
}


