<?php
/**
 * Plugin Name: Drinks Plugin
 * Plugin URI: https://example.com/drinks-plugin
 * Description: A plugin for displaying drinks with Pop Out and Lightbox functionality
 * Version: 2.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: drinks-plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DRINKS_PLUGIN_VERSION', '2.0.0');
define('DRINKS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DRINKS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Drinks Plugin Class
 */
class DrinksPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_head', array($this, 'add_lightbox_styles'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Plugin initialization
        load_plugin_textdomain('drinks-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        // Check if built assets exist, otherwise fall back to source files
        $build_path = DRINKS_PLUGIN_PATH . 'build/';
        $build_url = DRINKS_PLUGIN_URL . 'build/';
        
        if (file_exists($build_path . 'index.js')) {
            // Use built assets
            wp_enqueue_script(
                'drinks-plugin-editor',
                $build_url . 'index.js',
                array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-hooks', 'wp-compose'),
                DRINKS_PLUGIN_VERSION,
                true
            );
            
            if (file_exists($build_path . 'style-index.css')) {
                wp_enqueue_style(
                    'drinks-plugin-editor-style',
                    $build_url . 'style-index.css',
                    array(),
                    DRINKS_PLUGIN_VERSION
                );
            }
        } else {
            // Fallback to source files (for development)
            wp_enqueue_script(
                'drinks-plugin-editor',
                DRINKS_PLUGIN_URL . 'js/editor.js',
                array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-hooks', 'wp-compose'),
                DRINKS_PLUGIN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'drinks-plugin-editor-style',
                DRINKS_PLUGIN_URL . 'css/editor.css',
                array(),
                DRINKS_PLUGIN_VERSION
            );
        }
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        // Check if built assets exist, otherwise fall back to source files
        $build_path = DRINKS_PLUGIN_PATH . 'build/';
        $build_url = DRINKS_PLUGIN_URL . 'build/';
        
        if (file_exists($build_path . 'frontend.js')) {
            // Use built assets
            wp_enqueue_script(
                'drinks-plugin-frontend',
                $build_url . 'frontend.js',
                array(),
                DRINKS_PLUGIN_VERSION,
                true
            );
        } else {
            // Fallback to source files (for development)
            wp_enqueue_script(
                'drinks-plugin-frontend',
                DRINKS_PLUGIN_URL . 'js/frontend.js',
                array(),
                DRINKS_PLUGIN_VERSION,
                true
            );
        }
    }

    /**
     * Add lightbox styles
     */
    public function add_lightbox_styles() {
        ?>
        <style>
            /* Lightbox overlay */
            .drinks-lightbox-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                display: none;
                z-index: 10000;
                align-items: center;
                justify-content: center;
            }
            
            .drinks-lightbox-overlay.active {
                display: flex;
            }
            
            /* Lightbox content */
            .drinks-lightbox-content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
                text-align: center;
            }
            
            .drinks-lightbox-image {
                max-width: 100%;
                max-height: 80vh;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            }
            
            .drinks-lightbox-caption {
                color: white;
                margin-top: 15px;
                font-size: 18px;
                font-weight: bold;
            }
            
            /* Close button */
            .drinks-lightbox-close {
                position: absolute;
                top: -40px;
                right: 0;
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                font-size: 24px;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .drinks-lightbox-close:hover {
                background: rgba(255, 255, 255, 0.3);
            }
            
            /* Lightbox container styles */
            .wp-lightbox-container {
                position: relative;
                cursor: pointer;
            }
            
            .wp-lightbox-container:hover {
                opacity: 0.9;
                transition: opacity 0.2s ease;
            }
            
            /* Pop out effect styles */
            .cocktail-pop-out {
                transition: transform 0.3s ease;
            }
            
            .cocktail-pop-out:hover {
                transform: scale(1.05);
            }
        </style>
        <?php
    }
}

// Initialize the plugin
new DrinksPlugin(); 