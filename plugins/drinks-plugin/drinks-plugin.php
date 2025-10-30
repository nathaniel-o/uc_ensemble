<?php
/**
 * Plugin Name: Drinks Plugin
 * Plugin URI: notyet
 * Description: Jetpack-based Lightbox & Image Carousel fn, with custom Drink [Post] Selection & Styles. Drink Posts taxonomy defined ___ ? 
 * Version: 1.0.1
 * Author: Nathaniel
 * License: GPL v2 or later
 * Text Domain: drinks-plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DRINKS_PLUGIN_VERSION', '1.0.1');
define('DRINKS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DRINKS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include global wrapper functions
require_once DRINKS_PLUGIN_PATH . 'includes/functions.php';

// Load cocktail-images module
require_once DRINKS_PLUGIN_PATH . 'modules/cocktail-images/cocktail-images.php';

// Load drinks-search module (centralized WP_Query operations)
require_once DRINKS_PLUGIN_PATH . 'modules/drinks-search/drinks-search.php';

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
        add_action('wp_footer', array($this, 'add_carousel_overlay_html'), 1); // Add inside wp-site-blocks via footer
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        // AJAX handlers for carousel functionality
        add_action('wp_ajax_drinks_filter_carousel', array($this, 'handle_filter_carousel'));
        add_action('wp_ajax_nopriv_drinks_filter_carousel', array($this, 'handle_filter_carousel'));
        // error_log('Drinks Plugin: AJAX handlers registered for drinks_filter_carousel');
        
        // Add AJAX action for pop out lightbox (drinks content)
        add_action('wp_ajax_get_drink_content', array($this, 'handle_get_drink_content'));
        add_action('wp_ajax_nopriv_get_drink_content', array($this, 'handle_get_drink_content'));
        
        // Carousel lightbox functionality moved to frontend.js
        // add_action('wp_footer', array($this, 'add_carousel_lightbox_script'));
        
        // Admin: meta box and saving for drink metadata
        add_action('add_meta_boxes', array($this, 'add_drink_meta_box'));
        add_action('save_post', array($this, 'save_drink_meta'));
        
        // Admin: sync metadata functionality
        add_action('wp_ajax_sync_drinks_metadata', array($this, 'handle_sync_drinks_metadata'));
        
        // Force root-level search URLs (prevents /page-slug/?s= pattern)
        // add_action('template_redirect', array($this, 'force_root_search_url'), 1);
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Plugin initialization
        load_plugin_textdomain('drinks-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Force search queries to root-level URL
     * Prevents /page-slug/?s= pattern on local installs
     * Redirects to /?s= for consistent search page behavior
     * 
     * COMMENTED OUT: Not needed when home_url() passed to JS handles subdirectory installs
     */
    /* public function force_root_search_url() {
        // Only run on search queries
        if (!is_search()) {
            return;
        }
        
        // Get the search term
        $search_term = get_search_query();
        
        // Check if we're not already at root-level search
        $request_uri = $_SERVER['REQUEST_URI'];
        $parsed_url = parse_url($request_uri);
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '/';
        
        // If path is not root (/) and we have a search query, redirect to root search
        if ($path !== '/' && !empty($search_term)) {
            $root_search_url = home_url('/?s=' . urlencode($search_term));
            wp_redirect($root_search_url, 301);
            exit;
        }
    } */
    
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
        // Directly enqueue Jetpack's slideshow assets if Jetpack is active
        if (file_exists(WP_PLUGIN_DIR . '/jetpack/_inc/blocks/slideshow/view.js')) {
            // Enqueue required WordPress dependencies
            wp_enqueue_script('wp-i18n');
            wp_enqueue_script('wp-escape-html');
            wp_enqueue_script('wp-dom-ready');
            
            // Enqueue Jetpack's slideshow view script with all dependencies
            wp_enqueue_script(
                'jetpack-slideshow-view',
                plugins_url('jetpack/_inc/blocks/slideshow/view.js'),
                array('wp-dom-ready', 'wp-i18n', 'wp-escape-html'),
                '1.0',
                true
            );
            
            // Set the base URL that Jetpack needs
            wp_add_inline_script(
                'jetpack-slideshow-view',
                'window.Jetpack_Block_Assets_Base_Url = "' . plugins_url('jetpack/_inc/blocks/') . '";',
                'before'
            );
            
            error_log('Drinks Plugin: Jetpack slideshow assets enqueued');
        } else {
            error_log('Drinks Plugin: Jetpack slideshow view.js not found');
        }
        
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
        
        // Localize script with WordPress variables
        wp_localize_script(
            'drinks-plugin-frontend',
            'drinksPluginAjax',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('drinks_plugin_nonce')
            )
        );
        
        // Pass WordPress home URL to JavaScript (handles subdirectory installs)
        wp_localize_script(
            'drinks-plugin-frontend',
            'drinksPluginConfig',
            array(
                'homeUrl' => home_url('/')
            )
        );
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
                z-index: 20;
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
            
            /* Carousel-enabled figure styles */
            figure[data-carousel-enabled="true"] {
                max-width: 100%;
                max-height: 85vh;
                margin: 0 auto;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            
            figure[data-carousel-enabled="true"] img {
                max-width: 100%;
                max-height: 50vh;
                width: auto;
                height: auto;
                object-fit: contain;
                object-position: center;
                border-radius: 8px;
                display: block;
                margin-top: 0;
                margin-bottom: 0;
            }
            
            figure[data-carousel-enabled="true"] figcaption {
                margin-top: 1rem;
                text-align: center;
                max-width: 100%;
                padding: 0 1rem;
                font-size: clamp(0.9rem, 2vw, 1.2rem);
                color: #333;
                line-height: 1.4;
                flex-shrink: 0;
            }
            
            /* Landscape orientation specific styles */
            figure[data-carousel-enabled="true"].landscape {
                max-width: 90vw;
                max-height: 75vh;
            }
            
            figure[data-carousel-enabled="true"].landscape img {
                max-height: 40vh;
                max-width: 100%;
                margin-top: 0;
                margin-bottom: 0;
            }
            
            /* Portrait orientation specific styles */
            figure[data-carousel-enabled="true"].portrait {
                max-width: 60vw;
                max-height: 85vh;
            }
            
            figure[data-carousel-enabled="true"].portrait img {
                max-height: 50vh;
                max-width: 100%;
                margin-top: 0;
                margin-bottom: 0;
            }
            
            /* Mobile responsive adjustments */
            @media (max-width: 768px) {
                figure[data-carousel-enabled="true"] {
                    max-height: 80vh;
                }
                
                figure[data-carousel-enabled="true"] img {
                    max-height: 40vh;
                    margin-top: 0;
                    margin-bottom: 0;
                }
                
                figure[data-carousel-enabled="true"].landscape {
                    max-width: 95vw;
                    max-height: 70vh;
                }
                
                figure[data-carousel-enabled="true"].landscape img {
                    max-height: 35vh;
                    margin-top: 0;
                    margin-bottom: 0;
                }
                
                figure[data-carousel-enabled="true"].portrait {
                    max-width: 80vw;
                    max-height: 80vh;
                }
                
                figure[data-carousel-enabled="true"].portrait img {
                    max-height: 45vh;
                    margin-top: 0;
                    margin-bottom: 0;
                }
            }
            
            /* Pop out effect styles */
            .cocktail-pop-out {
                transition: transform 0.3s ease;
            }
            
            
            /* Jetpack Carousel Lightbox Styles */
            /* General lightbox overlay (used by pop-out). Support legacy jetpack class until rebuild. */
            .drinks-lightbox-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 85%;
                height: 85%;
                background: linear-gradient(135deg, rgba(250, 213, 188,0.8) 0%, rgba(36, 21, 71, 0.8) 100%);
                display: none;
                z-index: 50;
                align-items: center;
                justify-content: center;
                margin: auto;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
            
            /* Carousel overlay should be on top of everything */
            .jetpack-carousel-lightbox-overlay {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 85%;
                height: 85%;
                background: linear-gradient(135deg, rgba(250, 213, 188,0.8) 0%, rgba(36, 21, 71, 0.8) 100%);
                display: none;
                z-index: 100;
                align-items: center;
                justify-content: center;
                /* Initial hidden state */
                opacity: 0;
                pointer-events: none;
            }
            
            .drinks-lightbox-overlay.active,
            .jetpack-carousel-lightbox-overlay.active {
                display: flex;
            }
            
            .drinks-lightbox-content,
            .jetpack-carousel-lightbox-content {
                position: relative;
                width: 95%;
                height: 105%;
                max-width: 1400px;
                background: transparent;
                border-radius: 12px;
                overflow: hidden;
            }
            
            .jetpack-carousel-lightbox-body {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
            }
            
            .drinks-lightbox-header,
            .jetpack-carousel-lightbox-header {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                background: transparent;
                padding: 20px;
                z-index: 5;
                display: flex;
                justify-content: flex-end;
                align-items: flex-start;
                gap: 20px;
            }
            
            .drinks-search-results-header {
                color: white;
                font-size: 1.2rem;
                font-weight: 600;
                margin: 0;
                padding: 10px 20px;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
                order: 2;
                align-self: flex-start;
                background: rgba(36, 21, 71, 0.7);
                border-radius: 8px;
                backdrop-filter: blur(10px);
            }
            
            .drinks-lightbox-title,
            .jetpack-carousel-lightbox-title {
                color: white;
                font-size: 24px;
                font-weight: bold;
                margin: 0;
            }
            
            .drinks-lightbox-close,
            .jetpack-carousel-lightbox-close {
                background: #241547;
                border: none;
                color: white;
                font-size: 28px;
                width: 44px;
                order: 1;
                height: 44px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s ease;
                position: absolute;
                top: 16px;
                left: 16px; /* Align close button to left */
                right: auto;
                z-index: 6;
            }
            
            .drinks-lightbox-close:hover,
            .jetpack-carousel-lightbox-close:hover {
                background: rgba(36, 21, 71, 0.8);
            }
            
            /* See More button */
            .drinks-carousel-see-more {
                position: absolute;
                bottom: 20px;
                right: 20px;
                background: #241547;
                color: white;
                border: none;
                padding: 12px 24px;
                font-size: 1rem;
                font-weight: 600;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.3s ease;
                z-index: 10;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            }
            
            .drinks-carousel-see-more:hover {
                background: rgba(36, 21, 71, 0.9);
                transform: translateY(-2px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
            }
            
            /* 404 content in carousel */
            .drinks-404-slide {
                display: flex !important;
                align-items: center;
                justify-content: center;
                min-height: 400px;
            }
            
            .drinks-404-content {
                text-align: center;
                color: white;
                padding: 40px;
                background-color:var(--std-bg-color);
                border-radius: 36px;
            }
            
            .drinks-404-content h1 {
                font-size: 4rem;
                font-weight: bold;
                margin: 0 0 20px 0;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            }
            
            .drinks-404-content p {
                font-size: 1.5rem;
                margin: 10px 0;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
            }
            
            .drinks-404-content strong {
                color: #fad5bc;
                font-weight: bold;
            }
            
            .drinks-lightbox-body {
                height: 100%;
                padding-top: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            /* Ensure Jetpack slideshow fits properly in lightbox */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow {
                width: 100%;
                height: 100%;
                position: relative;
                overflow: visible !important; /* Allow navigation to extend beyond container */
            }
            
            /* Force carousel figure sizing with high specificity */
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure {
                max-width: 100% !important;
                max-height: 85vh !important;
                margin: auto !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure img {
                max-width: 90% !important;
                max-height: 90% !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain !important;
                object-position: center !important;
                border-radius: 8px !important;
                display: block !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
            
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure figcaption {
                margin-top: 1rem !important;
                text-align: center !important;
                max-width: 100% !important;
                padding: 0 1rem !important;
                font-size: clamp(0.9rem, 2vw, 1.2rem) !important;
                line-height: 1.4 !important;
                flex-shrink: 0 !important;
            }
            
            /* Landscape specific overrides */
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure.landscape {
                max-width: 90vw !important;
                max-height: 75vh !important;
            }
            
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure.landscape img {
                max-height: 90% !important;
                max-width: 90% !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
            
            /* Portrait specific overrides */
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure.portrait {
                max-width: 60vw !important;
                max-height: 85vh !important;
            }
            
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure.portrait img {
                max-height: 90% !important;
                max-width: 90% !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_container {
                width: 100%;
                height: 100%;
                position: relative;
                overflow: visible !important; /* Allow navigation to extend beyond container */
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_swiper-wrapper {
                height: 100%;
                overflow: visible !important; /* Allow navigation to extend beyond container */
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide {
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure {
                margin: auto;
                text-align: center;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide img {
                max-width: 90% !important;
                max-height: 90% !important;
                object-fit: contain !important;
                border-radius: 8px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figcaption {
                color: white;
                margin-top: 20px;
                font-size: 18px;
                font-weight: bold;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            }

            /* Drinks Pop-out should inherit host page colors and borders */
            .drinks-popout-content,
            .drinks-popout-body,
            .drinks-content-popout .wp-block-media-text__content {
                color: var(--autumnal-font-color, inherit);
                margin: auto;
                padding: 3%; 
            }

            .drinks-content-popout .wp-block-media-text__media img {
                border: var(--autumnal-border, none);
                max-width: 100%;
                max-height: var(--drink-image-max-height, 70vh);
                width: auto;
                height: auto;
                object-fit: contain;
                object-position: center;
            }

            .drinks-content-popout .wp-block-media-text__media h1 {
                color: #241547;
                font-weight: bold;
                margin: 0;
            }

            /* Pop-out list typography to match single post list */
            .drinks-content-popout .wp-block-media-text__content ul {
                list-style: none;
                margin: 0;
                margin-top: 0;
                padding: var(--drink-content-padding, 3%);
/*                 font-size: clamp(1rem, 2.5vw, 1.2rem);
 */                line-height: var(--drink-list-line-height, 1.6);
                text-shadow: grey;
                
            }
            
            /* Ensure drink metadata list has no bullets */
            .drink-metadata-list {
                list-style: none !important;
                padding-left: 0 !important;
            }
            
            .drink-metadata-list li {
                list-style: none !important;
                margin-bottom: var(--drink-list-item-margin, 8px);
            }
            
            .drink-metadata-list li::before {
                display: none !important;
                content: none !important;
            }
            
            .drinks-content-popout li::before {
                display: none !important;
                content: none !important;
            }
            
            /* Style metadata filter links */
            .drink-filter-link {
                color: inherit;
                text-decoration: underline;
                cursor: pointer;
                transition: opacity 0.2s ease;
            }
            
            .drink-filter-link:hover {
                opacity: 0.7;
            }
            
            .drinks-content-popout .wp-block-media-text__content li {
                 margin-bottom: var(--drink-list-item-margin, 8px);
            }
            .drinks-content-popout .wp-block-media-text__content em {
                font-weight: bold;
                color: #000; /* keep labels black in pop-out */
                font-style: normal;
                margin-right: var(--drink-em-margin-right, 0.25em);
            }
            
            /* Responsive sizing for pop-out images */
            @media (max-width: 768px) {
                .drinks-content-popout .wp-block-media-text__media img {
                    max-height: var(--drink-image-max-height-tablet, 60vh);
                }
                
                .drinks-content-popout .wp-block-media-text__content {
                    padding: var(--drink-content-padding-tablet, 2%);
                }
                
                .drinks-content-popout .wp-block-media-text__content ul {
                    padding: var(--drink-content-padding-tablet, 2%);
                }
            }
            
            @media (max-width: 480px) {
                .drinks-content-popout .wp-block-media-text__media img {
                    max-height: var(--drink-image-max-height-mobile, 50vh);
                }
                
                .drinks-content-popout .wp-block-media-text__content {
                    padding: var(--drink-content-padding-mobile, 1%);
                }
                
                .drinks-content-popout .wp-block-media-text__content ul {
                    padding: var(--drink-content-padding-mobile, 1%);
                }
            }
            
            /* Swiper handles slide visibility - don't override with display:none */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide {
                /* Swiper manages visibility via transform and opacity */
                /* Do NOT set display: none as it breaks Swiper rendering */
            }
            
            /* Navigation button positioning and visibility fixes */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-prev,
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-next {
                display: flex !important;
                opacity: 1;
                visibility: visible;
                z-index: 7 !important; /* Higher z-index to ensure visibility */
                position: absolute !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
                width: 50px !important;
                height: 50px !important;
                background: rgba(0, 0, 0, 0.6) !important;
                border: 2px solid rgba(255, 255, 255, 0.3) !important;
                border-radius: 50% !important;
                color: white !important;
                font-size: 24px !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
                pointer-events: auto !important;
                user-select: none !important;
            }
            
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-prev:hover,
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-next:hover {
                background: rgba(0, 0, 0, 0.8) !important;
                border-color: rgba(255, 255, 255, 0.6) !important;
                transform: translateY(-50%) scale(1.1) !important;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-prev {
                left: 20px !important;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-next {
                right: 20px !important;
            }
            
            /* Position pause button */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-pause {
                position: absolute !important;
                bottom: 30px !important;
                left: 30px !important;
                z-index: 7 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 40px !important;
                height: 40px !important;
                background: rgba(0, 0, 0, 0.6) !important;
                border: 2px solid rgba(255, 255, 255, 0.8) !important;
                border-radius: 50% !important;
                cursor: pointer !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Create pause icon using pseudo-elements */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-pause::before,
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-pause::after {
                content: '' !important;
                display: block !important;
                width: 3px !important;
                height: 14px !important;
                background: white !important;
                border-radius: 1px !important;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-pause::before {
                margin-right: 4px !important;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-pause:hover {
                background: rgba(0, 0, 0, 0.8) !important;
                border-color: white !important;
            }
            
            /* Ensure pagination is visible and properly positioned */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_pagination {
                display: flex !important;
                opacity: 1;
                visibility: visible;
                z-index: 7 !important;
                position: absolute !important;
                bottom: 30px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                gap: 10px !important;
            }
            /*    Hide pagination generated by frontend.js , using swiper functions   */
            .jetpack-carousel-lightbox-body .swiper-pagination-custom {
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }
            
            /* Style the pagination bullets */
            .jetpack-carousel-lightbox-body .swiper-pagination-bullet {
                background: rgba(255, 255, 255, 0.5) !important;
                opacity: 1 !important;
                margin: 0 !important;
                width: 12px !important;
                height: 12px !important;
                border-radius: 50% !important;S
                border: none !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
            }
            
            .jetpack-carousel-lightbox-body .swiper-pagination-bullet:hover {
                background: rgba(255, 255, 255, 0.8) !important;
                transform: scale(1.2) !important;
            }
            
            .jetpack-carousel-lightbox-body .swiper-pagination-bullet-active {
                background: white !important;
                transform: scale(1.1) !important;
            }
            
            /* Loading state */
            .jetpack-carousel-loading {
                color: white;
                font-size: 18px;
                text-align: center;
                padding: 40px;
            }
            
            .jetpack-carousel-loading-spinner {
                border: 3px solid rgba(255, 255, 255, 0.3);
                border-top: 3px solid white;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* Search page carousel - make it inline instead of overlay */
            body.search #drinks-carousel-overlay {
                position: relative !important;
                width: 100% !important;
                height: auto !important;
                transform: none !important;
                top: auto !important;
                left: auto !important;
                z-index: 1 !important;
                opacity: 1 !important;
                pointer-events: auto !important;
                margin: 2rem auto !important;
                overflow: visible !important;
            }

            body.search #drinks-carousel-overlay .jetpack-carousel-lightbox-content {
                position: relative !important;
                width: 100% !important;
                height: auto !important;
                transform: none !important;
                background-color: transparent !important;
                overflow: visible !important;
            }

            body.search #drinks-carousel-overlay .jetpack-carousel-lightbox-body {
                position: relative !important;
                height: auto !important;
                overflow: visible !important;
            }

            body.search #drinks-carousel-overlay .jetpack-carousel-lightbox-header {
                position: relative !important;
                padding: 1rem !important;
            }

            body.search #drinks-carousel-overlay .wp-block-jetpack-slideshow {
                height: auto !important;
                overflow: visible !important;
            }

            body.search #drinks-carousel-overlay .wp-block-jetpack-slideshow_container {
                height: auto !important;
                position: relative !important;
                overflow: visible !important;
            }

            body.search #drinks-carousel-overlay .jetpack-carousel-lightbox-close {
                display: none !important; /* Hide close button on search page */
            }

            body.search {
                overflow-x: hidden !important; /* Prevent horizontal scrolling */
                overflow-y: visible !important; /* Allow vertical scrolling */
            }
           
        </style>
        <?php
    }

    /**
     * AJAX handler for filter_carousel
     */
    public function handle_filter_carousel() {
        echo '<script>console.log("IMAGE CAROUSEL");</script>';
        // error_log('Drinks Plugin: AJAX handler called!');
        // error_log('Drinks Plugin: POST data: ' . print_r($_POST, true));
        
        // Get parameters from POST data
        $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        $figcaption_text = isset($_POST['figcaption_text']) ? sanitize_text_field($_POST['figcaption_text']) : '';
        $show_content = isset($_POST['show_content']) ? intval($_POST['show_content']) : 0;
        $random = isset($_POST['random']) ? ($_POST['random'] === 'true' || $_POST['random'] === '1') : false;
        
        /* error_log('Drinks Plugin: Received figcaption_text: ' . $figcaption_text);
        error_log('Drinks Plugin: Received search_term: ' . $search_term);
        error_log('Drinks Plugin: Random mode: ' . ($random ? 'true' : 'false')); */

        // Get drink posts
        $drink_posts = $this->uc_get_drink_posts();

        // Use unified uc_image_carousel
        
        $options = array(
            'drink_posts' => $drink_posts,
            'num_slides' => 5,
            'show_titles' => 0,
            'show_content' => $show_content
        );
        
        // FIXME  :: filter_carousel is less slides instead random
        // FIXME  :: results notice if none OR if random supplement is false 
        
        //  Parameters : $match_term (first slide) , $filter_term (R slides), $options (constant for now)
        //  * For Random, $match_term AND $filter_term must be false (empty string)
        if ($random) {
            $filtered_carousel = $this->uc_image_carousel('', '', $options);
        } else if (!empty($search_term)) {
            $filtered_carousel = $this->uc_image_carousel('', $search_term, $options);
        } else {
            $filtered_carousel = $this->uc_image_carousel($figcaption_text, '', $options);
        }
        
        // Debug: Log what we're generating
        // error_log('Drinks Plugin: AJAX filter_carousel - Generated carousel with length: ' . strlen($filtered_carousel));
        
        echo $filtered_carousel;
        // error_log('Drinks Plugin: Sending response: ' . substr($filtered_carousel, 0, 100) . '...');

        wp_die(); // Required for proper AJAX response
    }

    /**
     * Handle AJAX request for pop out lightbox (drinks content)
     */
    public function handle_get_drink_content() {
        // error_log('Drinks Plugin: handle_get_drink_content called');
        // error_log('Drinks Plugin: POST data: ' . print_r($_POST, true));
        
        // Get image ID from POST data
        $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
        // error_log('Drinks Plugin: Image ID from POST: ' . $image_id);
        
        if ($image_id <= 0) {
            error_log('Drinks Plugin: Invalid image ID, sending error response');
            wp_send_json_error('Invalid image ID');
            return;
        }
        
        // Get the post ID associated with this image
        $post_id = $this->get_post_id_from_image($image_id);
        // error_log('Drinks Plugin: Post ID found: ' . ($post_id ? $post_id : 'false'));
        
        if (!$post_id) {
            // error_log('Drinks Plugin: No post found for this image, sending error response');
            wp_send_json_error('No post found for this image');
            return;
        }
        
        // Generate drink content HTML
        $drink_content = $this->uc_generate_drink_content_html($post_id);
        // error_log('Drinks Plugin: Generated drink content length: ' . strlen($drink_content));
        
        if ($drink_content) {
            // error_log('Drinks Plugin: Sending success response with drink content');
            wp_send_json_success($drink_content);
        } else {
            // error_log('Drinks Plugin: Could not generate drink content, sending error response');
            wp_send_json_error('Could not generate drink content');
        }
    }

    /**
     * Get post ID from image attachment ID using title matching
     */
    private function get_post_id_from_image($image_id) {
        error_log('Drinks Plugin: get_post_id_from_image called with image_id: ' . $image_id);
        
        // First, try the original attachment relationship method as fallback
        // Check if this image is a featured image of any post
        $posts = get_posts(array(
            'meta_key' => '_thumbnail_id',
            'meta_value' => $image_id,
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 1
        ));
        
        if (!empty($posts)) {
            error_log('Drinks Plugin: Found featured image relationship, returning post ID: ' . $posts[0]->ID);
            return $posts[0]->ID;
        }
        
        // If not a featured image, check if it's attached to any post
        $attachment = get_post($image_id);
        if ($attachment && $attachment->post_parent > 0) {
            error_log('Drinks Plugin: Found attachment relationship, returning post ID: ' . $attachment->post_parent);
            return $attachment->post_parent;
        }
        
        // If no attachment relationship found, use title matching
        if ($attachment) {
            // Get the image title/alt text
            $image_title = $attachment->post_title;
            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            
            error_log('Drinks Plugin: Image title: "' . $image_title . '", alt: "' . $image_alt . '"');
            
            // Prioritize title over alt text for drink matching
            // Alt text is often a description, title is more likely to be the drink name
            $search_title = !empty($image_title) ? $image_title : $image_alt;
            
            if (!empty($search_title)) {
                error_log('Drinks Plugin: Using search title: "' . $search_title . '"');
                
                // Get all drink posts
                $drink_posts = $this->uc_get_drink_posts();
                error_log('Drinks Plugin: Found ' . count($drink_posts) . ' drink posts');
                
                // Get the normalize function from cocktail-images module
                $cocktail_module = get_cocktail_images_module();
                if ($cocktail_module) {
                    $normalized_search_title = $cocktail_module->normalize_title_for_matching($search_title);
                    error_log('Drinks Plugin: Normalized search title: "' . $normalized_search_title . '"');
                    
                    // Find matching drink post by normalized title
                    foreach ($drink_posts as $post) {
                        $normalized_post_title = $cocktail_module->normalize_title_for_matching($post['title']);
                        error_log('Drinks Plugin: Comparing "' . $normalized_search_title . '" vs "' . $normalized_post_title . '" (post: ' . $post['title'] . ')');
                        
                        // Check for exact match (case-insensitive)
                        if (strcasecmp($normalized_post_title, $normalized_search_title) === 0) {
                            error_log('Drinks Plugin: Found exact matching post ID: ' . $post['id']);
                            return $post['id'];
                        }
                    }
                    
                    // If no exact match found, try partial matching
                    error_log('Drinks Plugin: No exact match found, trying partial matching...');
                    foreach ($drink_posts as $post) {
                        $normalized_post_title = $cocktail_plugin->normalize_title_for_matching($post['title']);
                        
                        // Check if the search title contains the post title or vice versa
                        if (stripos($normalized_search_title, $normalized_post_title) !== false || 
                            stripos($normalized_post_title, $normalized_search_title) !== false) {
                            error_log('Drinks Plugin: Found partial matching post ID: ' . $post['id'] . ' (search: "' . $normalized_search_title . '" contains/contained in post: "' . $normalized_post_title . '")');
                            return $post['id'];
                        }
                    }
                } else {
                    error_log('Drinks Plugin: Cocktail plugin not available, using fallback matching');
                    // Fallback to simple matching if cocktail plugin not available
                    $normalized_search_title = strtolower($search_title);
                    
                    foreach ($drink_posts as $post) {
                        $normalized_post_title = strtolower($post['title']);
                        error_log('Drinks Plugin: Fallback comparing "' . $normalized_search_title . '" vs "' . $normalized_post_title . '" (post: ' . $post['title'] . ')');
                        
                        // Check for exact match (case-insensitive)
                        if (strcasecmp($normalized_post_title, $normalized_search_title) === 0) {
                            error_log('Drinks Plugin: Found exact matching post ID (fallback): ' . $post['id']);
                            return $post['id'];
                        }
                    }
                    
                    // If no exact match found, try partial matching
                    error_log('Drinks Plugin: No exact match found (fallback), trying partial matching...');
                    foreach ($drink_posts as $post) {
                        $normalized_post_title = strtolower($post['title']);
                        
                        // Check if the search title contains the post title or vice versa
                        if (stripos($normalized_search_title, $normalized_post_title) !== false || 
                            stripos($normalized_post_title, $normalized_search_title) !== false) {
                            error_log('Drinks Plugin: Found partial matching post ID (fallback): ' . $post['id'] . ' (search: "' . $normalized_search_title . '" contains/contained in post: "' . $normalized_post_title . '")');
                            return $post['id'];
                        }
                    }
                }
            } else {
                error_log('Drinks Plugin: No search title available');
            }
        } else {
            error_log('Drinks Plugin: No attachment found for image_id: ' . $image_id);
        }
        
        error_log('Drinks Plugin: No matching post found, returning false');
        return false;
    }

    /**
     * Generate drink content HTML for pop out lightbox
     */
    public function uc_generate_drink_content_html($post_id, $image_url = '', $image_alt = '') {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        // Get drink metadata
        $drinks = get_the_terms($post_id, 'drinks');
        $color = get_post_meta($post_id, 'drink_color', true);
        $glass = get_post_meta($post_id, 'drink_glass', true);
        $garnish = get_post_meta($post_id, 'drink_garnish1', true);
        $base = get_post_meta($post_id, 'drink_base', true);
        $ice = get_post_meta($post_id, 'drink_ice', true);
        
        // Get featured image if no image URL provided
        if (empty($image_url)) {
            $image_url = get_the_post_thumbnail_url($post_id, 'large');
        }
        
        // Get image alt if not provided
        if (empty($image_alt)) {
            $image_alt = get_the_title($post_id);
        }
        
        // Generate HTML matching the "Drink Post Content" template part
        $html = '<div class="wp-block-media-text alignwide is-stacked-on-mobile">';
        $html .= '<figure class="wp-block-media-text__media">';
        $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" class="wp-image-' . esc_attr($post_id) . '" />';
        $html .= '</figure>';
        $html .= '<div class="wp-block-media-text__content">';
        $html .= '<h1>' . esc_html(get_the_title($post_id)) . '</h1>';
        $html .= '<ul class="drink-metadata-list">';
        
        // Category
        $category_name = $drinks ? $drinks[0]->name : 'Uncategorized';
        $html .= '<li><em>Category</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($category_name) . '">' . esc_html($category_name) . '</a></li>';
        
        // Color
        if (!empty($color)) {
            $html .= '<li><em>Color</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($color) . '">' . esc_html($color) . '</a></li>';
        }
        
        // Glass
        if (!empty($glass)) {
            $html .= '<li><em>Glass</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($glass) . '">' . esc_html($glass) . '</a></li>';
        }
        
        // Garnish
        if (!empty($garnish)) {
            $html .= '<li><em>Garnish</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($garnish) . '">' . esc_html($garnish) . '</a></li>';
        }
        
        // Base
        if (!empty($base)) {
            $html .= '<li><em>Base</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($base) . '">' . esc_html($base) . '</a></li>';
        }
        
        // Ice
        if (!empty($ice)) {
            $html .= '<li><em>Ice</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($ice) . '">' . esc_html($ice) . '</a></li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Count drink posts, return Query Object
     * 
     * NOTE: WP_Query has been relocated to drinks-search module
     * MODE 2: Get All Drink Posts
     * @see modules/drinks-search/includes/class-drinks-search.php
     */
    public function uc_drink_post_query() {
        return get_drinks_search()->get_all_drink_posts_query();
    }
    
    /**
     * Retrieve Drink Posts from DB 
     */
    public function uc_get_drink_posts() {
        $drink_query = $this->uc_drink_post_query();
       
        $post_count = $drink_query->found_posts;
        //echo "Number of posts with drinks: " . $post_count; 

        //  Copy results into a clean Array 
        $drink_posts = array();
        if ($drink_query->have_posts()) {
            while ($drink_query->have_posts()) {
                $drink_query->the_post();
                $drink_posts[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'thumbnail' => get_the_post_thumbnail_url(null, 'large'),
                    'excerpt' => get_the_excerpt()
                );
            }
            wp_reset_postdata();
        }
       return $drink_posts;
    }

    /**
     * Unified image carousel generator
     * Supports: random mode, clicked-image-first mode, and filter mode
     * @param string $match_term Text from clicked image caption to prioritize that image first
     * @param string $filter_term Search term to filter drink posts by title
     * @param array $options Settings array with keys: drink_posts (required), num_slides (default 5), show_titles (default 0), show_content (default 0)
     * For Random, $match_term AND $filter_term must be false (empty string)
     * 
     */
    public function uc_image_carousel($match_term = '', $filter_term = '', $options = array()) {
        

        // Extract options with defaults
        $drink_posts = isset($options['drink_posts']) ? $options['drink_posts'] : array();
        $num_slides = isset($options['num_slides']) ? intval($options['num_slides']) : 5;
        $show_titles = isset($options['show_titles']) ? intval($options['show_titles']) : 0;
        $show_content = isset($options['show_content']) ? intval($options['show_content']) : 0;
        
        $slideshow_images = array();
        $used_ids = array();
        $used_titles = array(); // Track drink titles to avoid duplicates
        $filtered_count = 0; // Track count of filtered drinks
        $total_drinks = count($drink_posts); // Track total available drinks for "of Y" display
        
        /**
         * Helper function to add random slides to fill remaining slots
         */
        $add_random_slides = function(&$slideshow_images, &$used_ids, &$used_titles, &$drink_posts, $target_count) {
            while (count($slideshow_images) < $target_count) {
                if (empty($drink_posts)) {
                    break;
                }
                
                $random_index = array_rand($drink_posts);
                $random_drink = $drink_posts[$random_index];
                
                // Check both ID and title to avoid duplicates
                if (!in_array($random_drink['id'], $used_ids) && 
                    !in_array($random_drink['title'], $used_titles)) {
                    $slideshow_images[] = array(
                        'id' => $random_drink['id'],
                        'src' => $random_drink['thumbnail'],
                        'alt' => $random_drink['title']
                    );
                    $used_ids[] = $random_drink['id'];
                    $used_titles[] = $random_drink['title'];
                    
                    unset($drink_posts[$random_index]);
                    $drink_posts = array_values($drink_posts);
                }
            }
        };
        
        // MODE 1: Filter mode - filter by search term
        if (!empty($filter_term)) {
            echo '<script>console.log("uc_image_carousel MODE 1: Filter mode with term: ' . esc_js($filter_term) . '");</script>';
            $filtered_drinks = array_filter($drink_posts, function($drink) use ($filter_term) {
                // Search in title
                if (stripos($drink['title'], $filter_term) !== false) {
                    return true;
                }
                
                // Search in metadata fields
                $post_id = $drink['id'];
                $metadata_fields = array(
                    'drink_color',
                    'drink_glass',
                    'drink_garnish1',
                    'drink_garnish2',
                    'drink_base',
                    'drink_ice'
                );
                
                foreach ($metadata_fields as $field) {
                    $meta_value = get_post_meta($post_id, $field, true);
                    if (!empty($meta_value) && stripos($meta_value, $filter_term) !== false) {
                        return true;
                    }
                }
                
                // Search in drinks taxonomy terms
                $drinks_terms = get_the_terms($post_id, 'drinks');
                if (!empty($drinks_terms) && !is_wp_error($drinks_terms)) {
                    foreach ($drinks_terms as $term) {
                        if (stripos($term->name, $filter_term) !== false) {
                            return true;
                        }
                    }
                }
                
                return false;
            });
            $filtered_drinks = array_values($filtered_drinks);
            $filtered_count = count($filtered_drinks); // Store the count
            
            // For filter mode: dynamic slide count (up to 10, or fewer if fewer matches)
            $dynamic_slide_count = min($filtered_count, 10);
            
            // Add matching drinks only (no random supplement)
            while (count($slideshow_images) < $dynamic_slide_count && !empty($filtered_drinks)) {
                $random_index = array_rand($filtered_drinks);
                $random_drink = $filtered_drinks[$random_index];
                
                // Check both ID and title to avoid duplicates
                if (!in_array($random_drink['id'], $used_ids) && 
                    !in_array($random_drink['title'], $used_titles)) {
                    $slideshow_images[] = array(
                        'id' => $random_drink['id'],
                        'src' => $random_drink['thumbnail'],
                        'alt' => $random_drink['title']
                    );
                    $used_ids[] = $random_drink['id'];
                    $used_titles[] = $random_drink['title'];
                    
                    unset($filtered_drinks[$random_index]);
                    $filtered_drinks = array_values($filtered_drinks);
                }
            }
        }
        // MODE 2: Clicked image first mode
        else if (!empty($match_term)) {
            echo '<script>console.log("uc_image_carousel MODE 2: Match mode with term: ' . esc_js($match_term) . '");</script>';
            error_log('Drinks Plugin: Looking for post matching figcaption: ' . $match_term);
            
            // Find the post that matches the figcaption text
            $clicked_post = null;
            foreach ($drink_posts as $index => $post) {
                // Use normalized title matching from cocktail-images module
                $cocktail_module = get_cocktail_images_module();
                if ($cocktail_module) {
                    $normalized_post_title = $cocktail_module->normalize_title_for_matching($post['title']);
                    $normalized_figcaption = $cocktail_module->normalize_title_for_matching($match_term);
                } else {
                    // Fallback to simple matching
                    $normalized_post_title = strtolower($post['title']);
                    $normalized_figcaption = strtolower($match_term);
                }
                
                if (strcasecmp($normalized_post_title, $normalized_figcaption) === 0) {
                    $clicked_post = $post;
                    error_log('Drinks Plugin: Found matching post: ' . $post['title']);
                    unset($drink_posts[$index]); // Remove it from the pool
                    break;
                }
            }
            
            // Add the clicked image as first slide
            if ($clicked_post) {
                error_log('Drinks Plugin: Adding first slide - ID: ' . $clicked_post['id'] . ', Title: ' . $clicked_post['title']);
                $slideshow_images[] = array(
                    'id' => $clicked_post['id'],
                    'src' => $clicked_post['thumbnail'],
                    'alt' => $clicked_post['title']
                );
                $used_ids[] = $clicked_post['id'];
                $used_titles[] = $clicked_post['title'];
                $drink_posts = array_values($drink_posts); // Re-index
                
                error_log('Drinks Plugin: Generating carousel with clicked image first, then ' . ($num_slides - 1) . ' random slides');
            }
            
            // Add random slides to fill remaining slots
            $add_random_slides($slideshow_images, $used_ids, $used_titles, $drink_posts, $num_slides);
        }
        // MODE 3: Random mode (both figcaption and filter are empty)
        else {
            echo '<script>console.log("uc_image_carousel MODE 3: Random mode");</script>';
            error_log('Drinks Plugin: Generating random carousel with ' . $num_slides . ' slides');
            
            // Add random slides
            $add_random_slides($slideshow_images, $used_ids, $used_titles, $drink_posts, $num_slides);
        }
        
        // Generate the slideshow HTML
        $slides_html = $this->generate_slideshow_slides($slideshow_images, $show_titles, $show_content);
        
        // Debug: Log selected drinks to error log
        $drink_titles = array_map(function($img) { return $img['alt']; }, $slideshow_images);
        error_log('Drinks Plugin: Selected drinks BEFORE generate_slideshow_slides: ' . json_encode($drink_titles));
        error_log('Drinks Plugin: Number of drinks selected: ' . count($slideshow_images));
        error_log('Drinks Plugin: Filter term: "' . $filter_term . '", Filtered count: ' . $filtered_count);
        
        // Add search results header showing "X of Y" format consistently
        $num_slides = count($slideshow_images);
        if (!empty($filter_term)) {
            // Filter mode: show "X slides of Y matching results"
            $search_header = '<h5 class="drinks-search-results-header">Search Results: ' . $num_slides . ' of ' . $filtered_count . '</h5>';
        } else if (!empty($match_term)) {
            // Match mode: show "X slides of Y total drinks" (clicked image first + random from total pool)
            $search_header = '<h5 class="drinks-search-results-header">Search Results: ' . $num_slides . ' of ' . $total_drinks . '</h5>';
        } else {
            // Random mode: show "X slides of Y total drinks" (random selection from total pool)
            $search_header = '<h5 class="drinks-search-results-header">Search Results: ' . $num_slides . ' of ' . $total_drinks . '</h5>';
        }
        return $search_header . $slides_html;
    }
    
    /**
     * Function to generate slideshow HTML, optional param for more data 
     */
    public function generate_slideshow_slides($images, $show_titles = 0, $show_content = 0) {
        $slides_html = '';
        $total_slides = count($images);
        
        // Ensure we only have the requested number of slides
        if ($total_slides === 0) {
            return '';
        }
        
        // For lightbox carousel, we want exactly 5 slides total (including clones for infinite loop)
        if ($total_slides === 5) {
            // Add second-to-last slide as first clone (to avoid duplicating the clicked image)
            $second_to_last_image = $images[3]; // Index 3 (4th slide)
            $slides_html .= $this->generate_single_slide($second_to_last_image, 3, true, $show_titles, $show_content);
            
            // Generate regular slides (5 slides)
            foreach ($images as $index => $image) {
                $slides_html .= $this->generate_single_slide($image, $index, false, $show_titles, $show_content);
            }
            
            // Add second slide as last clone (to avoid duplicating the clicked image)
            $second_image = $images[1]; // Index 1 (2nd slide)
            $slides_html .= $this->generate_single_slide($second_image, 1, true, $show_titles, $show_content);
            
            // Total: 1 (second-to-last clone) + 5 (regular) + 1 (second clone) = 7 slides for infinite loop
        } elseif ($total_slides === 4) {
            // Add last slide as first for infinite loop
            $last_image = end($images);
            $slides_html .= $this->generate_single_slide($last_image, 3, true, $show_titles, $show_content);
            
            // Generate regular slides (4 slides)
            foreach ($images as $index => $image) {
                $slides_html .= $this->generate_single_slide($image, $index, false, $show_titles, $show_content);
            }
            
            // Add first slide as last for infinite loop
            $first_image = reset($images);
            $slides_html .= $this->generate_single_slide($first_image, 0, true, $show_titles, $show_content);
            
            // Total: 1 (last clone) + 4 (regular) + 1 (first clone) = 6 slides for infinite loop
        } else {
            // Fallback for other cases - use original logic
            // Add last slide as first for infinite loop
            $last_image = end($images);
            $slides_html .= $this->generate_single_slide($last_image, $total_slides - 1, true, $show_titles, $show_content);
            
            // Generate regular slides
            foreach ($images as $index => $image) {
                $slides_html .= $this->generate_single_slide($image, $index, false, $show_titles, $show_content);
            }
            
            // Add first slide as last for infinite loop
            $first_image = reset($images);
            $slides_html .= $this->generate_single_slide($first_image, 0, true, $show_titles, $show_content);
        }
        
        return $slides_html;
    }
    
    /**
     * Generate single slide HTML
     */
    public function generate_single_slide($image, $index, $is_duplicate, $show_titles, $show_content) {
        // Get post meta data
        $post = get_post($image['id']);
        $drinks = get_the_terms($image['id'], 'drinks');  // Changed from 'category' to 'drinks'
        $color = get_post_meta($image['id'], 'drink_color', true);
        $glass = get_post_meta($image['id'], 'drink_glass', true);
        $garnish = get_post_meta($image['id'], 'drink_garnish1', true);
        $base = get_post_meta($image['id'], 'drink_base', true);
        $ice = get_post_meta($image['id'], 'drink_ice', true);

        $slide_classes = array(
            'wp-block-jetpack-slideshow_slide',
            'swiper-slide',
            $is_duplicate ? 'swiper-slide-duplicate' : ''
        );

        $html = '<li class="' . implode(' ', array_filter($slide_classes)) . '" ';
        $html .= 'data-swiper-slide-index="' . $index . '" aria-hidden="true">';
        $html .= '<figure data-carousel-enabled="true">';
        $html .= '<img alt="' . esc_attr($image['alt']) . '" ';
        $html .= 'class="wp-block-jetpack-slideshow_image wp-image-' . esc_attr($image['id']) . '" ';
        $html .= 'data-id="' . esc_attr($image['id']) . '" ';
        $html .= 'src="' . esc_url($image['src']) . '">';
        
        // Always add figcaption with the image alt text
        $html .= '<figcaption>' . esc_html($image['alt']) . '</figcaption>';

        if ($show_content) {
            $html .= '<div class="slideshow-content">';
            $html .= '<h3><a href="' . get_permalink($image['id']) . '">' . esc_html($image['alt']) . '</a></h3>';
            $html .= '<ul class="wp-block-list">';
            $html .= '<li><em>Category</em>: ' . esc_html($drinks ? $drinks[0]->name : 'Uncategorized') . '</li>';
            $html .= '<li><em>Color</em>: ' . esc_html($color) . '</li>';
            $html .= '<li><em>Glass</em>: ' . esc_html($glass) . '</li>';
            $html .= '<li><em>Garnish</em>: ' . esc_html($garnish) . '</li>';
            $html .= '<li><em>Base</em>: ' . esc_html($base) . '</li>';
            $html .= '<li><em>Ice</em>: ' . esc_html($ice) . '</li>';
            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '</figure>';
        $html .= '</li>';

        return $html;
    }
    
    /**
     * Generate metadata list for a post
     */
    public function uc_generate_metadata_list($post_id) {
        $drinks = get_the_terms($post_id, 'drinks');
        $color = get_post_meta($post_id, 'drink_color', true);
        $glass = get_post_meta($post_id, 'drink_glass', true);
        $garnish = get_post_meta($post_id, 'drink_garnish1', true);
        $base = get_post_meta($post_id, 'drink_base', true);
        $ice = get_post_meta($post_id, 'drink_ice', true);

        // Start an unordered list
        $output = '<ul class="drink-metadata-list">';
        
        if ($drinks) {
            $output .= sprintf("<li>Category: %s</li>", esc_html($drinks[0]->name));
        }
        if ($color) {
            $output .= sprintf("<li>Color: %s</li>", esc_html($color));
        }
        if ($glass) {
            $output .= sprintf("<li>Glass: %s</li>", esc_html($glass));
        }
        if ($garnish) {
            $output .= sprintf("<li>Garnish: %s</li>", esc_html($garnish));
        }
        if ($base) {
            $output .= sprintf("<li>Base: %s</li>", esc_html($base));
        }
        if ($ice) {
            $output .= sprintf("<li>Ice: %s</li>", esc_html($ice));
        }
        
        $output .= '</ul>';
        
        return $output;
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
        // CLEAR any stuck WordPress admin notices for this page
        if (isset($_GET['page']) && $_GET['page'] === 'drinks-plugin') {
            // Remove any transient notices
            delete_transient('drinks_sync_notice');
            delete_transient('drinks_sync_error');
            
            // Clear any session-based notices
            if (isset($_SESSION['drinks_notices'])) {
                unset($_SESSION['drinks_notices']);
            }
        }
        
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
            
            <style>
                .drinks-admin-columns {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-top: 20px;
                }
                .drinks-admin-column {
                    min-width: 0; /* Prevents grid blowout */
                }
                .drinks-admin-column h2:first-child {
                    margin-top: 0;
                    padding: 15px;
                    background: #f0f0f1;
                    border-radius: 4px 4px 0 0;
                    margin-bottom: 0;
                    border-bottom: 2px solid #0073aa;
                }
                @media (max-width: 1200px) {
                    .drinks-admin-columns {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
            
            <div class="drinks-admin-columns">
                <!-- Left Column: Drinks Plugin Content -->
                <div class="drinks-admin-column">
                    <h2>🍹 Drinks Plugin</h2>
                    
                    <div class="card">
                        <h3>Description</h3>
                        <p>A modern WordPress plugin for enhanced image display with Pop Out effects, Core Lightbox integration, and automatic dimension analysis for aspect ratio management.</p>
                    </div>

                    <div class="card">
                <h2>Documentation</h2>
                <div class="drinks-plugin-readme">
                    <?php echo $readme_content; ?>
                </div>
            </div>
            
            <div class="card">
                <h2>Sync Drinks Metadata</h2>
                <p>Sync drink metadata from post content to custom fields. This will read <code>&lt;ul&gt;</code> content from all drink posts and update their metadata with proper capitalization and prefix removal.</p>
                
                <div class="sync-metadata-section">
                    <button type="button" id="sync-drinks-metadata" class="button button-primary">
                        <span class="dashicons dashicons-update"></span>
                        Sync All Drinks Metadata
                    </button>
                    
                    <div id="sync-status" style="display: none; margin-top: 15px;">
                        <div class="notice notice-info is-dismissible">
                            <p><strong>Sync in progress...</strong> This may take a few moments depending on the number of drink posts.</p>
                        </div>
                    </div>
                    
                    <div id="sync-results" style="display: none; margin-top: 15px;">
                        <div class="notice notice-success is-dismissible">
                            <p><strong>Sync complete!</strong> <span id="sync-summary"></span></p>
                            <button type="button" class="notice-dismiss" onclick="jQuery('#sync-results').hide();"><span class="screen-reader-text">Dismiss this notice.</span></button>
                        </div>
                    </div>
                    
                    <div id="sync-error" style="display: none; margin-top: 15px;">
                        <div class="notice notice-error is-dismissible">
                            <p><strong>Sync failed!</strong> <span id="sync-error-message"></span></p>
                            <button type="button" class="notice-dismiss" onclick="jQuery('#sync-error').hide();"><span class="screen-reader-text">Dismiss this notice.</span></button>
                        </div>
                    </div>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    // Clear stuck WordPress admin notices containing sync-related text
                    function clearSyncNotices() {
                        $('#sync-status, #sync-results, #sync-error').hide();
                        
                        $('.notice').each(function() {
                            var text = $(this).text();
                            if (text.includes('Sync') || text.includes('sync') || 
                                text.includes('metadata') || text.includes('Metadata')) {
                                $(this).remove();
                            }
                        });
                    }
                    
                    clearSyncNotices();
                    
                    // Watch for WordPress trying to re-add notices
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            mutation.addedNodes.forEach(function(node) {
                                if (node.nodeType === 1 && $(node).hasClass('notice')) {
                                    var text = $(node).text();
                                    if (text.includes('Sync') || text.includes('metadata')) {
                                        $(node).remove();
                                    }
                                }
                            });
                        });
                    });
                    observer.observe(document.body, { childList: true, subtree: true });
                    
                    $('#sync-drinks-metadata').on('click', function() {
                        // Show confirmation dialog
                        if (!confirm('Are you sure? This will update ALL drinks\' metadata.')) {
                            return;
                        }
                        
                        var button = $(this);
                        var originalText = button.html();
                        
                        // Clear previous messages and show loading
                        clearSyncNotices();
                        button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spinning"></span> Syncing...');
                        $('#sync-status').show();
                        
                        // Make AJAX request to sync metadata
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'sync_drinks_metadata',
                                nonce: '<?php echo wp_create_nonce("sync_drinks_metadata_nonce"); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    var summary = response.data.summary || 'Metadata sync completed successfully.';
                                    $('#sync-summary').text(summary);
                                    $('#sync-results').show();
                                    setTimeout(function() { $('#sync-results').fadeOut(); }, 10000);
                                } else {
                                    $('#sync-error-message').text(response.data.message || 'Unknown error occurred');
                                    $('#sync-error').show();
                                }
                            },
                            error: function(xhr, status, error) {
                                $('#sync-error-message').text('Failed to sync metadata. Error: ' + error);
                                $('#sync-error').show();
                            },
                            complete: function() {
                                button.prop('disabled', false).html(originalText);
                                $('#sync-status').hide();
                            }
                        });
                    });
                });
                </script>
                
                <style>
                .spinning {
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .sync-metadata-section {
                    padding: 15px 0;
                }
                </style>
            </div>
                </div>
                <!-- End Left Column -->
                
                <!-- Right Column: Cocktail Images Module Content -->
                <div class="drinks-admin-column">
                    <h2>🖼️ Cocktail Images Module</h2>
                    <?php
                    // Get cocktail-images module content
                    $cocktail_module = get_cocktail_images_module();
                    if ($cocktail_module) {
                        echo $cocktail_module->get_admin_content();
                    } else {
                        echo '<div class="card"><p>Cocktail Images module not loaded.</p></div>';
                    }
                    ?>
                </div>
                <!-- End Right Column -->
            </div>
            <!-- End Columns -->
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

	/**
	 * Register the Drinks meta box on post edit screens
	 */
	public function add_drink_meta_box() {
		add_meta_box(
			'drinks_meta_box',
			__('Drink Details', 'drinks-plugin'),
			array($this, 'render_drink_meta_box'),
			'post',
			'side',
			'default'
		);
	}

	/**
	 * Render the Drinks meta box fields
	 */
	public function render_drink_meta_box($post) {
		// Add nonce for security
		wp_nonce_field('drinks_meta_box_nonce_action', 'drinks_meta_box_nonce');

		$color = get_post_meta($post->ID, 'drink_color', true);
		$glass = get_post_meta($post->ID, 'drink_glass', true);
		$garnish = get_post_meta($post->ID, 'drink_garnish1', true);
		$garnish2 = get_post_meta($post->ID, 'drink_garnish2', true);
		$base = get_post_meta($post->ID, 'drink_base', true);
		$ice = get_post_meta($post->ID, 'drink_ice', true);

		echo '<p><label for="drink_color"><strong>' . esc_html__('Color', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_color" name="drink_color" value="' . esc_attr($color) . '" class="widefat" /></p>';

		echo '<p><label for="drink_glass"><strong>' . esc_html__('Glass', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_glass" name="drink_glass" value="' . esc_attr($glass) . '" class="widefat" /></p>';

		echo '<p><label for="drink_garnish1"><strong>' . esc_html__('Garnish', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_garnish1" name="drink_garnish1" value="' . esc_attr($garnish) . '" class="widefat" /></p>';

		echo '<p><label for="drink_garnish2"><strong>' . esc_html__('Garnish 2', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_garnish2" name="drink_garnish2" value="' . esc_attr($garnish2) . '" class="widefat" /></p>';

		echo '<p><label for="drink_base"><strong>' . esc_html__('Base', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_base" name="drink_base" value="' . esc_attr($base) . '" class="widefat" /></p>';

		echo '<p><label for="drink_ice"><strong>' . esc_html__('Ice', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_ice" name="drink_ice" value="' . esc_attr($ice) . '" class="widefat" /></p>';

		// Helper note about taxonomy
		echo '<p style="margin-top:12px;">' . esc_html__('Tip: Set the Drink Category in the Drinks taxonomy panel.', 'drinks-plugin') . '</p>';
	}

	/**
	 * Save handler for Drinks meta box
	 */
	public function save_drink_meta($post_id) {
		// Verify nonce
		if (!isset($_POST['drinks_meta_box_nonce']) || !wp_verify_nonce($_POST['drinks_meta_box_nonce'], 'drinks_meta_box_nonce_action')) {
			return;
		}

		// Bail on autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Permissions
		$post_type = isset($_POST['post_type']) ? $_POST['post_type'] : get_post_type($post_id);
		if ($post_type !== 'post') {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// Sanitize and save fields
		$fields = array(
			'drink_color',
			'drink_glass',
			'drink_garnish1',
			'drink_garnish2',
			'drink_base',
			'drink_ice',
		);

		foreach ($fields as $field_key) {
			if (isset($_POST[$field_key])) {
				$sanitized = sanitize_text_field(wp_unslash($_POST[$field_key]));
				update_post_meta($post_id, $field_key, $sanitized);
			}
		}
	}
    
    /**
     * Add Jetpack carousel lightbox script to footer
     * DEPRECATED: All carousel lightbox functionality has been moved to frontend.js
     * This function is no longer called (hook disabled on line 51)
     */
    /**
     * Add hidden carousel overlay HTML to footer for Jetpack initialization
     */
    public function add_carousel_overlay_html() {
        // Only add on frontend, not admin
        if (is_admin()) {
            return;
        }
        ?>
        <div class="jetpack-carousel-lightbox-overlay" id="drinks-carousel-overlay">
            <div class="jetpack-carousel-lightbox-content">
                <div class="jetpack-carousel-lightbox-header">
                    <button type="button" class="jetpack-carousel-lightbox-close" aria-label="Close carousel">&times;</button>
                </div>
                <div class="jetpack-carousel-lightbox-body">
                <div class="wp-block-jetpack-slideshow aligncenter" data-autoplay="false" data-delay="3" data-effect="slide">
                    <div class="wp-block-jetpack-slideshow_container swiper-container">
                        <ul class="wp-block-jetpack-slideshow_swiper-wrapper swiper-wrapper" id="jetpack-carousel-slides">
                            <!-- Dummy slide for Jetpack initialization - will be replaced when carousel opens -->
                            <li class="wp-block-jetpack-slideshow_slide swiper-slide" data-swiper-slide-index="0">
                                <figure><img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1' height='1'%3E%3C/svg%3E" alt="Loading..."></figure>
                            </li>
                        </ul>
                            
                            <!-- Slideshow controls -->
                            <a class="wp-block-jetpack-slideshow_button-prev swiper-button-prev swiper-button-white" role="button" tabindex="0" aria-label="Previous slide"></a>
                            <a class="wp-block-jetpack-slideshow_button-next swiper-button-next swiper-button-white" role="button" tabindex="0" aria-label="Next slide"></a>
                            <a aria-label="Pause Slideshow" class="wp-block-jetpack-slideshow_button-pause" role="button"></a>
                            <div class="wp-block-jetpack-slideshow_pagination swiper-pagination swiper-pagination-white swiper-pagination-custom"></div>
                        </div>
                    </div>
                </div>
                <!-- See More button -->
                <button type="button" class="drinks-carousel-see-more" aria-label="See more results">See More</button>
            </div>
        </div>
        <?php
    }
    
    public function add_carousel_lightbox_script() {
        // All carousel/lightbox JavaScript has been consolidated in src/frontend.js
        // This eliminates ~687 lines of redundant code
        return;
        
        /* REDUNDANT CODE REMOVED - See frontend.js for:
         * - createCarouselOverlay()
         * - closeCarousel()
         * - loadCarouselImages()
         * - initializeJetpackSlideshow()
         * - addBasicSlideshowFunctionality()
         * - handleCocktailCarouselClick()
         * - All carousel setup and observers
         * 
         * Previously this function contained ~687 lines of duplicate JavaScript
         */
    }
    
    /**
     * AJAX handler for syncing drinks metadata
     */
    public function handle_sync_drinks_metadata() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'sync_drinks_metadata_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        // Check admin privileges
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Access denied. Admin privileges required.'));
            return;
        }
        
        try {
            // Include the sync script class
            $sync_script_path = DRINKS_PLUGIN_PATH . 'sync-drinks-metadata.php';
            
            if (!file_exists($sync_script_path)) {
                wp_send_json_error(array('message' => 'Sync script not found at: ' . $sync_script_path));
                return;
            }
            
            // Create a custom output buffer to capture the sync results
            ob_start();
            
            // Include the sync script (it will detect WordPress is already loaded)
            require_once $sync_script_path;
            
            // Run the sync
            $sync = new DrinksMetadataSync();
            $sync->run();
            
            // Get the output
            $output = ob_get_clean();
            
            // Parse the output to extract summary
            $summary = 'Metadata sync completed.';
            if (preg_match('/Posts updated: (\d+)/', $output, $matches)) {
                $updated_count = intval($matches[1]);
                if ($updated_count > 0) {
                    $summary = "Successfully updated {$updated_count} posts with cleaned metadata.";
                } else {
                    $summary = "No posts needed updates.";
                }
            }
            
            wp_send_json_success(array(
                'message' => 'Sync completed successfully',
                'summary' => $summary,
                'output' => $output
            ));
            
        } catch (Exception $e) {
            error_log('Drinks Plugin Sync Error: ' . $e->getMessage());
            error_log('Drinks Plugin Sync Error Trace: ' . $e->getTraceAsString());
            wp_send_json_error(array(
                'message' => 'Error running sync: ' . $e->getMessage()
            ));
        }
    }
    
}

// Initialize the plugin and expose global accessor
global $drinks_plugin;
$drinks_plugin = new DrinksPlugin();