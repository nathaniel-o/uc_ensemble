<?php
/**
 * Global functions for Cocktail Images plugin
 * All carousel and drink management functions have been moved to drinks-plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the main module instance
 * This is a legacy wrapper - the actual function is defined in cocktail-images.php
 */
if (!function_exists('get_cocktail_images_module')) {
    function get_cocktail_images_module() {
        global $cocktail_images_module;
        return isset($cocktail_images_module) ? $cocktail_images_module : null;
    }
}

