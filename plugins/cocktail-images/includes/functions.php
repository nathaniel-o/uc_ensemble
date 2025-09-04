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
 * Get the main plugin instance
 */
function get_cocktail_images_plugin() {
    global $cocktail_images_plugin;
    return $cocktail_images_plugin;
}

