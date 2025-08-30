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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
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
     * Enqueue admin styles
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'drinks-plugin-admin',
            DRINKS_PLUGIN_URL . 'css/admin.css',
            array(),
            DRINKS_PLUGIN_VERSION
        );
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

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Drinks Plugin',
            'Drinks Plugin',
            'manage_options',
            'drinks-plugin',
            array($this, 'admin_page'),
            'dashicons-custom-glass',
            31
        );
    }

    /**
     * Admin page callback
     */
    public function admin_page() {
        // Read the README file
        $readme_path = DRINKS_PLUGIN_PATH . 'README.md';
        $readme_content = '';
        
        if (file_exists($readme_path)) {
            $readme_content = file_get_contents($readme_path);
            // Convert markdown to HTML (basic conversion)
            $readme_content = $this->markdown_to_html($readme_content);
        } else {
            $readme_content = '<p>README.md file not found.</p>';
        }
        
        ?>
        <div class="wrap">
            <h1>Drinks Plugin</h1>
            
            <div class="card">
                <h2>Description</h2>
                <p>A modern WordPress plugin for enhanced image display with Pop Out effects, Core Lightbox integration, and automatic dimension analysis for aspect ratio management.</p>
            </div>

            <div class="card">
                <h2>Documentation</h2>
                <div class="drinks-plugin-readme">
                    <?php echo $readme_content; ?>
                </div>
            </div>
        </div>

        <style>
            .drinks-plugin-readme {
                max-width: 100%;
                overflow-x: auto;
            }
            .drinks-plugin-readme h1,
            .drinks-plugin-readme h2,
            .drinks-plugin-readme h3,
            .drinks-plugin-readme h4,
            .drinks-plugin-readme h5,
            .drinks-plugin-readme h6 {
                color: #23282d;
                margin-top: 1.5em;
                margin-bottom: 0.5em;
            }
            .drinks-plugin-readme h1 {
                font-size: 2em;
                border-bottom: 1px solid #eee;
                padding-bottom: 0.3em;
            }
            .drinks-plugin-readme h2 {
                font-size: 1.5em;
                border-bottom: 1px solid #eee;
                padding-bottom: 0.3em;
            }
            .drinks-plugin-readme h3 {
                font-size: 1.25em;
            }
            .drinks-plugin-readme code {
                background: #f1f1f1;
                padding: 2px 4px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
            }
            .drinks-plugin-readme pre {
                background: #f1f1f1;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
            }
            .drinks-plugin-readme pre code {
                background: none;
                padding: 0;
            }
            .drinks-plugin-readme ul,
            .drinks-plugin-readme ol {
                margin-left: 20px;
            }
            .drinks-plugin-readme li {
                margin-bottom: 5px;
            }
            .drinks-plugin-readme blockquote {
                border-left: 4px solid #0073aa;
                margin: 0;
                padding-left: 15px;
                color: #666;
            }
            .drinks-plugin-readme table {
                border-collapse: collapse;
                width: 100%;
                margin: 15px 0;
            }
            .drinks-plugin-readme th,
            .drinks-plugin-readme td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .drinks-plugin-readme th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
        </style>
        <?php
    }

    /**
     * Convert markdown to HTML (basic conversion)
     */
    private function markdown_to_html($markdown) {
        // Convert headers
        $markdown = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $markdown);
        $markdown = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $markdown);
        
        // Convert bold and italic
        $markdown = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $markdown);
        $markdown = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $markdown);
        
        // Convert code blocks
        $markdown = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $markdown);
        $markdown = preg_replace('/`(.*?)`/', '<code>$1</code>', $markdown);
        
        // Convert lists
        $markdown = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $markdown);
        $markdown = preg_replace('/^- (.*$)/m', '<li>$1</li>', $markdown);
        
        // Convert links
        $markdown = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $markdown);
        
        // Convert paragraphs
        $markdown = preg_replace('/^(?!<[h|li|ul|ol|pre|blockquote])(.+)$/m', '<p>$1</p>', $markdown);
        
        // Clean up empty paragraphs
        $markdown = preg_replace('/<p><\/p>/', '', $markdown);
        
        return $markdown;
    }
}

// Initialize the plugin
new DrinksPlugin(); 