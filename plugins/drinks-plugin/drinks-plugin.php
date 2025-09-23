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

// Include global wrapper functions
require_once DRINKS_PLUGIN_PATH . 'includes/functions.php';

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

        // AJAX handlers for carousel functionality
        add_action('wp_ajax_drinks_filter_carousel', array($this, 'handle_filter_carousel'));
        add_action('wp_ajax_nopriv_drinks_filter_carousel', array($this, 'handle_filter_carousel'));
        error_log('Drinks Plugin: AJAX handlers registered for drinks_filter_carousel');
        
        // Add AJAX action for pop out lightbox (drinks content)
        add_action('wp_ajax_get_drink_content', array($this, 'handle_get_drink_content'));
        add_action('wp_ajax_nopriv_get_drink_content', array($this, 'handle_get_drink_content'));
        
        // Add carousel lightbox functionality
        add_action('wp_footer', array($this, 'add_carousel_lightbox_script'));
		
		// Admin: meta box and saving for drink metadata
		add_action('add_meta_boxes', array($this, 'add_drink_meta_box'));
		add_action('save_post', array($this, 'save_drink_meta'));
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
        
        // Localize script with WordPress variables
        wp_localize_script(
            'drinks-plugin-frontend',
            'drinksPluginAjax',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('drinks_plugin_nonce')
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
            }
            
            /* Portrait orientation specific styles */
            figure[data-carousel-enabled="true"].portrait {
                max-width: 60vw;
                max-height: 85vh;
            }
            
            figure[data-carousel-enabled="true"].portrait img {
                max-height: 50vh;
                max-width: 100%;
            }
            
            /* Mobile responsive adjustments */
            @media (max-width: 768px) {
                figure[data-carousel-enabled="true"] {
                    max-height: 80vh;
                }
                
                figure[data-carousel-enabled="true"] img {
                    max-height: 40vh;
                }
                
                figure[data-carousel-enabled="true"].landscape {
                    max-width: 95vw;
                    max-height: 70vh;
                }
                
                figure[data-carousel-enabled="true"].landscape img {
                    max-height: 35vh;
                }
                
                figure[data-carousel-enabled="true"].portrait {
                    max-width: 80vw;
                    max-height: 80vh;
                }
                
                figure[data-carousel-enabled="true"].portrait img {
                    max-height: 45vh;
                }
            }
            
            /* Pop out effect styles */
            .cocktail-pop-out {
                transition: transform 0.3s ease;
            }
            
            
            /* Jetpack Carousel Lightbox Styles */
            /* General lightbox overlay (used by pop-out). Support legacy jetpack class until rebuild. */
            .drinks-lightbox-overlay,
            .jetpack-carousel-lightbox-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.95);
                display: none;
                z-index: 10001;
                align-items: center;
                justify-content: center;
            }
            
            .drinks-lightbox-overlay.active,
            .jetpack-carousel-lightbox-overlay.active {
                display: flex;
            }
            
            .drinks-lightbox-content,
            .jetpack-carousel-lightbox-content {
                position: relative;
                width: 95%;
                height: 95%;
                max-width: 1400px;
                background: #000;
                border-radius: 12px;
                overflow: hidden;
            }
            
            .drinks-lightbox-header,
            .jetpack-carousel-lightbox-header {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, transparent 100%);
                padding: 20px;
                z-index: 10;
                display: flex;
                justify-content: space-between;
                align-items: center;
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
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                font-size: 28px;
                width: 44px;
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
                z-index: 20;
            }
            
            .drinks-lightbox-close:hover,
            .jetpack-carousel-lightbox-close:hover {
                background: rgba(255, 255, 255, 0.3);
            }
            
            .drinks-lightbox-body,
            .jetpack-carousel-lightbox-body {
                height: 100%;
                padding-top: 80px;
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
                margin: 0 auto !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure img {
                max-width: 100% !important;
                max-height: 50vh !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain !important;
                object-position: center !important;
                border-radius: 8px !important;
                display: block !important;
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
                max-height: 40vh !important;
                max-width: 100% !important;
            }
            
            /* Portrait specific overrides */
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure.portrait {
                max-width: 60vw !important;
                max-height: 85vh !important;
            }
            
            .jetpack-carousel-lightbox-overlay .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide figure.portrait img {
                max-height: 50vh !important;
                max-width: 100% !important;
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
                margin: 0;
                text-align: center;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide img {
                max-width: 100%;
                max-height: 80%;
                object-fit: contain;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
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

/*                 font-size: 0;
 */            }

            /* Pop-out list typography to match single post list */
            .drinks-content-popout .wp-block-media-text__content ul {
                list-style: none;
                margin: 0;
                padding: var(--drink-content-padding, 3%);
/*                 font-size: clamp(1rem, 2.5vw, 1.2rem);
 */                line-height: var(--drink-list-line-height, 1.6);
                text-shadow: grey;
                
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
            
            /* Fallback slideshow fixes */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide {
                display: none; /* Hide all slides initially */
            }
            
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_slide.active {
                display: flex !important; /* Show active slide */
            }
            
            /* Navigation button positioning and visibility fixes */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-prev,
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_button-next {
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
                z-index: 20 !important; /* Higher z-index to ensure visibility */
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
            
            /* Ensure pagination is visible and properly positioned */
            .jetpack-carousel-lightbox-body .wp-block-jetpack-slideshow_pagination {
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
                z-index: 20 !important;
                position: absolute !important;
                bottom: 30px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                gap: 10px !important;
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
        </style>
        <?php
    }

    /**
     * AJAX handler for filter_carousel
     */
    public function handle_filter_carousel() {
        error_log('Drinks Plugin: AJAX handler called!');
        error_log('Drinks Plugin: POST data: ' . print_r($_POST, true));
        
        // Get search term and figcaption text from POST data
        $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        $figcaption_text = isset($_POST['figcaption_text']) ? sanitize_text_field($_POST['figcaption_text']) : '';
        $show_content = isset($_POST['show_content']) ? intval($_POST['show_content']) : 0;
        
        error_log('Drinks Plugin: Received figcaption_text: ' . $figcaption_text);

        // Get drink posts
        $drink_posts = $this->uc_get_drinks();

        // If we have figcaption text, we want to make the matching post the first slide
        if (!empty($figcaption_text)) {
            error_log('Drinks Plugin: Looking for post matching figcaption: ' . $figcaption_text);
            error_log('Drinks Plugin: Total drink posts available: ' . count($drink_posts));
            
        
            // Find the post that matches the figcaption text using normalized title matching
            $clicked_post = null;
            foreach ($drink_posts as $index => $post) {
                error_log('Drinks Plugin: Checking post ' . $post['id'] . ' (' . $post['title'] . ') against figcaption: ' . $figcaption_text);
                
                // Use normalized title matching from cocktail-images plugin
                $cocktail_plugin = get_cocktail_images_plugin();
                if ($cocktail_plugin) {
                    $normalized_post_title = $cocktail_plugin->normalize_title_for_matching($post['title']);
                    $normalized_figcaption = $cocktail_plugin->normalize_title_for_matching($figcaption_text);
                } else {
                    // Fallback to simple matching if cocktail plugin not available
                    $normalized_post_title = strtolower($post['title']);
                    $normalized_figcaption = strtolower($figcaption_text);
                }
                
                error_log('Drinks Plugin: Normalized post title: "' . $normalized_post_title . '" vs figcaption: "' . $normalized_figcaption . '"');
                
                // Check for exact match (case-insensitive)
                if (strcasecmp($normalized_post_title, $normalized_figcaption) === 0) {
                    $clicked_post = $post;
                    error_log('Drinks Plugin: Found matching post: ' . $post['title']);
                    unset($drink_posts[$index]); // Remove it from the pool
                    break;
                }
            }
            
            if (!$clicked_post) {
                error_log('Drinks Plugin: No post found matching figcaption: ' . $figcaption_text . ', falling back to random carousel');
                $filtered_carousel = $this->uc_random_carousel($drink_posts, 4, 0, $show_content);
            } else {
                // Re-index the remaining posts
                $drink_posts = array_values($drink_posts);
                error_log('Drinks Plugin: Remaining posts after removing clicked: ' . count($drink_posts));
                
                // Generate carousel with clicked image first, then 4 random others
                $filtered_carousel = $this->uc_carousel_with_first_slide($clicked_post, $drink_posts, 4, 0, $show_content);
            }
        } else {
            // Generate carousel HTML with 4 random drinks
            $filtered_carousel = $this->uc_random_carousel($drink_posts, 4, 0, $show_content);
        }
        
        // Debug: Log what we're generating
        // error_log('Drinks Plugin: AJAX filter_carousel - Generated carousel with length: ' . strlen($filtered_carousel));
        
        echo $filtered_carousel;
        error_log('Drinks Plugin: Sending response: ' . substr($filtered_carousel, 0, 100) . '...');

        wp_die(); // Required for proper AJAX response
    }

    /**
     * Handle AJAX request for pop out lightbox (drinks content)
     */
    public function handle_get_drink_content() {
        error_log('Drinks Plugin: handle_get_drink_content called');
        error_log('Drinks Plugin: POST data: ' . print_r($_POST, true));
        
        // Get image ID from POST data
        $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
        error_log('Drinks Plugin: Image ID from POST: ' . $image_id);
        
        if ($image_id <= 0) {
            error_log('Drinks Plugin: Invalid image ID, sending error response');
            wp_send_json_error('Invalid image ID');
            return;
        }
        
        // Get the post ID associated with this image
        $post_id = $this->get_post_id_from_image($image_id);
        error_log('Drinks Plugin: Post ID found: ' . ($post_id ? $post_id : 'false'));
        
        if (!$post_id) {
            error_log('Drinks Plugin: No post found for this image, sending error response');
            wp_send_json_error('No post found for this image');
            return;
        }
        
        // Generate drink content HTML
        $drink_content = $this->uc_generate_drink_content_html($post_id);
        error_log('Drinks Plugin: Generated drink content length: ' . strlen($drink_content));
        
        if ($drink_content) {
            error_log('Drinks Plugin: Sending success response with drink content');
            wp_send_json_success($drink_content);
        } else {
            error_log('Drinks Plugin: Could not generate drink content, sending error response');
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
                $drink_posts = $this->uc_get_drinks();
                error_log('Drinks Plugin: Found ' . count($drink_posts) . ' drink posts');
                
                // Get the normalize function from cocktail-images plugin
                $cocktail_plugin = get_cocktail_images_plugin();
                if ($cocktail_plugin) {
                    $normalized_search_title = $cocktail_plugin->normalize_title_for_matching($search_title);
                    error_log('Drinks Plugin: Normalized search title: "' . $normalized_search_title . '"');
                    
                    // Find matching drink post by normalized title
                    foreach ($drink_posts as $post) {
                        $normalized_post_title = $cocktail_plugin->normalize_title_for_matching($post['title']);
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
        $html .= '<ul>';
        $html .= '<li><em>Category</em>: ' . esc_html($drinks ? $drinks[0]->name : 'Uncategorized') . '</li>';
        $html .= '<li><em>Color</em>: ' . esc_html($color) . '</li>';
        $html .= '<li><em>Glass</em>: ' . esc_html($glass) . '</li>';
        $html .= '<li><em>Garnish</em>: ' . esc_html($garnish) . '</li>';
        $html .= '<li><em>Base</em>: ' . esc_html($base) . '</li>';
        $html .= '<li><em>Ice</em>: ' . esc_html($ice) . '</li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Count drink posts, return weird Query Object 
     */
    public function uc_drink_query() {
        $drink_query = new WP_Query(array(
            'post_type' => 'post', // or your custom post type
            'tax_query' => array(
                array(
                    'taxonomy' => 'drinks', //Plural 
                    'operator' => 'EXISTS'
                )
            ),
            'posts_per_page' => -1
        ));

        return $drink_query;
    }
    
    /**
     * Retrieve Drink Posts from DB 
     */
    public function uc_get_drinks() {
        $drink_query = $this->uc_drink_query();
       
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
     * Build Carousel with a specific first slide, then random images
     */
    public function uc_carousel_with_first_slide($first_slide_post, $drink_posts, $num_slides, $show_titles = 0, $show_content = 0) {
        $slideshow_images = array();
        $used_ids = array();

        // Add the first slide (clicked image)
        if ($first_slide_post) {
            error_log('Drinks Plugin: Adding first slide - ID: ' . $first_slide_post['id'] . ', Title: ' . $first_slide_post['title']);
            $slideshow_images[] = array(
                'id' => $first_slide_post['id'],
                'src' => $first_slide_post['thumbnail'],
                'alt' => $first_slide_post['title']
            );
            $used_ids[] = $first_slide_post['id'];
            
            // Remove the clicked image from the available pool to prevent duplicates
            foreach ($drink_posts as $index => $drink) {
                if ($drink['id'] === $first_slide_post['id']) {
                    unset($drink_posts[$index]);
                    break;
                }
            }
            $drink_posts = array_values($drink_posts); // Re-index the array
        } else {
            // error_log('Drinks Plugin: No first slide post provided');
        }

        // Add random slides from the remaining posts
        while (count($slideshow_images) < $num_slides + 1) { // +1 because we already added the first slide
            if (empty($drink_posts)) {
                break;
            }

            $random_index = array_rand($drink_posts);
            $random_drink = $drink_posts[$random_index];
            
            // Only add if not already used (this should always be true now, but keeping for safety)
            if (!in_array($random_drink['id'], $used_ids)) {
                $slideshow_images[] = array(
                    'id' => $random_drink['id'],
                    'src' => $random_drink['thumbnail'],
                    'alt' => $random_drink['title']
                );
                $used_ids[] = $random_drink['id'];
                
                // Remove used drink from available pool
                unset($drink_posts[$random_index]);
                $drink_posts = array_values($drink_posts);
            }
        }

        return $this->generate_slideshow_slides($slideshow_images, $show_titles, $show_content);
    }

    /**
     * Build Carousel with Random Images taken from Drink Posts
     */
    public function uc_random_carousel($drink_posts, $num_slides, $show_titles = 0, $show_content = 0) {
        $slideshow_images = array();
        $used_ids = array();

        // Keep trying until we have the requested number of slides
        while (count($slideshow_images) < $num_slides) {
            if (empty($drink_posts)) {
                break;
            }

            $random_index = array_rand($drink_posts);
            $random_drink = $drink_posts[$random_index];
            
            // Only add if not already used
            if (!in_array($random_drink['id'], $used_ids)) {
                $slideshow_images[] = array(
                    'id' => $random_drink['id'],
                    'src' => $random_drink['thumbnail'],
                    'alt' => $random_drink['title']
                );
                $used_ids[] = $random_drink['id'];
                
                // Remove used drink from available pool
                unset($drink_posts[$random_index]);
                $drink_posts = array_values($drink_posts);
            }
        }

        return $this->generate_slideshow_slides($slideshow_images, $show_titles, $show_content);
    }
    
    /**
     * An Copy of uc_random_carousel, returns <li><figure>..etc Slides Content via generate_slideshow_slides
     */
    public function uc_filter_carousel($srchStr, $drink_posts, $num_slides, $show_titles = 0, $show_content = 0, $supp_rand = 0) {
        // Filter drinks matching search string
        $filtered_drinks = array_filter($drink_posts, function($drink) use ($srchStr) {
            return stripos($drink['title'], $srchStr) !== false;
        });
        $filtered_drinks = array_values($filtered_drinks);

        $slideshow_images = array();
        $used_ids = array();

        // First add matching drinks
        while (count($slideshow_images) < $num_slides && !empty($filtered_drinks)) {
            $random_index = array_rand($filtered_drinks);
            $random_drink = $filtered_drinks[$random_index];
            
            if (!in_array($random_drink['id'], $used_ids)) {
                $slideshow_images[] = array(
                    'id' => $random_drink['id'],
                    'src' => $random_drink['thumbnail'],
                    'alt' => $random_drink['title']
                );
                $used_ids[] = $random_drink['id'];
                
                unset($filtered_drinks[$random_index]);
                $filtered_drinks = array_values($filtered_drinks);
            }
        }

        // If supp_rand is true and we need more slides, add random ones
        if ($supp_rand && count($slideshow_images) < $num_slides) {
            while (count($slideshow_images) < $num_slides && !empty($drink_posts)) {
                $random_index = array_rand($drink_posts);
                $random_drink = $drink_posts[$random_index];
                
                if (!in_array($random_drink['id'], $used_ids)) {
                    $slideshow_images[] = array(
                        'id' => $random_drink['id'],
                        'src' => $random_drink['thumbnail'],
                        'alt' => $random_drink['title']
                    );
                    $used_ids[] = $random_drink['id'];
                    
                    unset($drink_posts[$random_index]);
                    $drink_posts = array_values($drink_posts);
                }
            }
        }

        return $this->generate_slideshow_slides($slideshow_images, $show_titles, $show_content);
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
		$base = get_post_meta($post->ID, 'drink_base', true);
		$ice = get_post_meta($post->ID, 'drink_ice', true);

		echo '<p><label for="drink_color"><strong>' . esc_html__('Color', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_color" name="drink_color" value="' . esc_attr($color) . '" class="widefat" /></p>';

		echo '<p><label for="drink_glass"><strong>' . esc_html__('Glass', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_glass" name="drink_glass" value="' . esc_attr($glass) . '" class="widefat" /></p>';

		echo '<p><label for="drink_garnish1"><strong>' . esc_html__('Garnish', 'drinks-plugin') . '</strong></label>';
		echo '<input type="text" id="drink_garnish1" name="drink_garnish1" value="' . esc_attr($garnish) . '" class="widefat" /></p>';

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
     */
    public function add_carousel_lightbox_script() {
        ?>
        <script>
        (function() {
            'use strict';
            
            // Jetpack Carousel Lightbox functionality
            let currentJetpackCarouselLightbox = null;
            
            /**
             * Initialize Jetpack carousel lightbox functionality
             */
            function initJetpackCarouselLightbox() {
                // console.log('Drinks Plugin: Jetpack carousel lightbox initialized');
                
                // Add click handlers to carousel-enabled images
                document.addEventListener('click', handleJetpackCarouselImageClick);
                
                // Add keyboard support
                document.addEventListener('keydown', handleJetpackCarouselKeydown);
                
                // Setup carousel for existing images
                setupJetpackCarouselForImages();
                
                // Setup observer for dynamically added content
                setupJetpackCarouselObserver();
                
                // console.log('Drinks Plugin: Event listeners and observers set up');
            }
            
            /**
             * Handle clicks on carousel-enabled images
             */
            function handleJetpackCarouselImageClick(event) {
                // Only process clicks that might be carousel-related
                const isCarouselRelated = event.target.closest('.cocktail-carousel, [data-carousel-enabled], .wp-block-image, figure, img');
                
                if (!isCarouselRelated) {
                    // This click is not carousel-related, ignore it silently
                    return;
                }
                // console.log('Drinks Plugin: Carousel-related click detected on:', event.target);
                // console.log('Drinks Plugin: Clicked element tagName:', event.target.tagName);
                // console.log('Drinks Plugin: Clicked element classes:', event.target.className);
                
                // Debug: Show the element structure (only for carousel-related clicks)
                // console.log('Drinks Plugin: Clicked element HTML:', event.target.outerHTML.substring(0, 300) + '...');
                
                // Debug: Check parent elements for carousel attributes
                let currentElement = event.target;
                let depth = 0;
                while (currentElement && depth < 5) {
                    // console.log('Drinks Plugin: Parent', depth, ':', currentElement.tagName, 'classes:', currentElement.className);
                    if (currentElement.classList && currentElement.classList.contains('cocktail-carousel')) {
                        // console.log('Drinks Plugin: Found cocktail-carousel at parent level', depth);
                    }
                    if (currentElement.hasAttribute('data-carousel-enabled')) {
                        // console.log('Drinks Plugin: Found data-carousel-enabled at parent level', depth);
                    }
                    currentElement = currentElement.parentElement;
                    depth++;
                }
                
                // Check if the clicked element itself has the attribute
                if (event.target.hasAttribute('data-carousel-enabled')) {
                    // console.log('Drinks Plugin: Clicked element has data-carousel-enabled');
                    const container = event.target;
                    const img = event.target.querySelector('img') || event.target;
                    openJetpackCarouselLightbox(img, container);
                    return;
                }
                
                // Check if it's an img and find parent with attribute or cocktail-carousel class
                if (event.target.tagName === 'IMG') {
                    // console.log('Drinks Plugin: Clicked on IMG element, searching for parent container...');
                    
                    // First try to find container with data-carousel-enabled
                    let container = event.target.closest('[data-carousel-enabled]');
                    
                    // If not found, look for cocktail-carousel class (existing system)
                    if (!container) {
                        container = event.target.closest('.cocktail-carousel');
                        if (container) {
                            // console.log('Drinks Plugin: Found container with cocktail-carousel class, treating as carousel-enabled');
                        }
                    }
                    
                    // console.log('Drinks Plugin: Container found:', container);
                    
                    if (!container) {
                        // console.log('Drinks Plugin: No carousel-enabled container found for IMG');
                        // console.log('Drinks Plugin: Parent elements:', event.target.parentElement, event.target.parentElement?.parentElement);
                        return;
                    }
                    
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // console.log('Drinks Plugin: Opening Jetpack carousel lightbox for image:', event.target.src);
                    openJetpackCarouselLightbox(event.target, container);
                    return;
                }
                
                // If clicked on a container element, check if it has carousel attributes
                if (event.target.classList && (event.target.classList.contains('cocktail-carousel') || event.target.hasAttribute('data-carousel-enabled'))) {
                    // console.log('Drinks Plugin: Clicked on carousel container element');
                    const container = event.target;
                    const img = container.querySelector('img');
                    
                    if (img) {
                        event.preventDefault();
                        event.stopPropagation();
                        // console.log('Drinks Plugin: Opening Jetpack carousel lightbox for image:', img.src);
                        openJetpackCarouselLightbox(img, container);
                        return;
                    }
                }
                
                // Check for any parent container with carousel attributes
                let container = event.target.closest('[data-carousel-enabled]');
                if (!container) {
                    container = event.target.closest('.cocktail-carousel');
                }
                
                // Only log errors if this was a click on a potentially carousel-related element
                if (!container) {
                    // Check if this click was anywhere near a carousel element
                    const nearbyCarousel = event.target.closest('.wp-block-columns, .wp-block-column, .wp-block-image, figure, img');
                    if (nearbyCarousel) {
                        // console.log('Drinks Plugin: Click near carousel elements but no carousel container found');
                    }
                    return;
                }
                
                // console.log('Drinks Plugin: Container with carousel attributes found:', container);
                
                event.preventDefault();
                event.stopPropagation();
                
                const img = container.querySelector('img');
                // console.log('Drinks Plugin: Image found in container:', img);
                
                if (!img) {
                    // console.log('Drinks Plugin: No image found in container');
                    return;
                }
                
                // console.log('Drinks Plugin: Opening Jetpack carousel lightbox for image:', img.src);
                openJetpackCarouselLightbox(img, container);
            }
            
            /**
             * Handle keyboard events for carousel
             */
            function handleJetpackCarouselKeydown(event) {
                // console.log('Drinks Plugin: Keydown event:', event.key, 'Current lightbox:', !!currentJetpackCarouselLightbox);
                
                if (!currentJetpackCarouselLightbox) return;
                
                if (event.key === 'Escape') {
                    // console.log('Drinks Plugin: Escape key pressed, closing carousel lightbox');
                    closeJetpackCarouselLightbox();
                }
            }
            
            /**
             * Open Jetpack carousel lightbox
             */
            function openJetpackCarouselLightbox(img, container) {
                console.log('Drinks Plugin: Image element:', img);
                console.log('Drinks Plugin: Image dataset.id:', img.dataset.id);
                console.log('Drinks Plugin: Image getAttribute(data-id):', img.getAttribute('data-id'));
                console.log('Drinks Plugin: Image attributes:', img.attributes);
                const imageId = img.dataset.id || img.getAttribute('data-id') || img.dataset.attachmentId || img.getAttribute('data-attachment-id') || '';
                console.log('Drinks Plugin: Final imageId:', imageId);
                
                // Get figcaption text for title-based matching
                const figcaption = container.querySelector('figcaption');
                const figcaptionText = figcaption ? figcaption.textContent.trim() : '';
                console.log('Drinks Plugin: Figcaption text:', figcaptionText);
                
                const imageSrc = img.src;
                const imageAlt = img.alt || 'Drink Image';
                
                // Create Jetpack carousel lightbox overlay
                const overlay = createJetpackCarouselLightboxOverlay(imageSrc, imageAlt);
                document.body.appendChild(overlay);
                
                // Load additional drinks for carousel
                loadDrinksForJetpackCarousel(overlay, figcaptionText);
                
                // Show lightbox
                requestAnimationFrame(() => {
                    overlay.classList.add('active');
                    currentJetpackCarouselLightbox = overlay;
                    document.body.style.overflow = 'hidden';
                    
                    // Dynamic height adjustment
                    adjustCarouselHeight(overlay);
                });
            }
            
            /**
             * Adjust carousel height dynamically based on content
             */
            function adjustCarouselHeight(overlay) {
                // console.log('Drinks Plugin: Adjusting carousel height dynamically...');
                
                const figures = overlay.querySelectorAll('.wp-block-jetpack-slideshow_slide figure');
                // console.log('Drinks Plugin: Found', figures.length, 'figures to adjust');
                
                figures.forEach((figure, index) => {
                    const img = figure.querySelector('img');
                    const figcaption = figure.querySelector('figcaption');
                    
                    if (img && figcaption) {
                        // Wait for image to load
                        if (img.complete) {
                            adjustFigureHeight(figure, img, figcaption);
                        } else {
                            img.addEventListener('load', () => {
                                adjustFigureHeight(figure, img, figcaption);
                            }, { once: true });
                        }
                    }
                });
            }
            
            /**
             * Adjust individual figure height
             */
            function adjustFigureHeight(figure, img, figcaption) {
                const viewportHeight = window.innerHeight;
                const availableHeight = viewportHeight * 0.9; // Use 90% of viewport
                
                // Calculate caption height
                const captionHeight = figcaption.offsetHeight + 32; // Add margin
                
                // Calculate optimal image height
                const maxImageHeight = availableHeight - captionHeight;
                
                // console.log('Drinks Plugin: Viewport height:', viewportHeight);
                // console.log('Drinks Plugin: Available height:', availableHeight);
                // console.log('Drinks Plugin: Caption height:', captionHeight);
                // console.log('Drinks Plugin: Max image height:', maxImageHeight);
                
                // Apply dynamic sizing
                if (figure.classList.contains('landscape')) {
                    img.style.maxHeight = Math.min(maxImageHeight, viewportHeight * 0.4) + 'px';
                    figure.style.maxHeight = availableHeight + 'px';
                } else if (figure.classList.contains('portrait')) {
                    img.style.maxHeight = Math.min(maxImageHeight, viewportHeight * 0.5) + 'px';
                    figure.style.maxHeight = availableHeight + 'px';
                } else {
                    img.style.maxHeight = Math.min(maxImageHeight, viewportHeight * 0.5) + 'px';
                    figure.style.maxHeight = availableHeight + 'px';
                }
                
                // console.log('Drinks Plugin: Applied dynamic sizing to figure');
            }
            
            /**
             * Close Jetpack carousel lightbox
             */
            function closeJetpackCarouselLightbox() {
                // console.log('Drinks Plugin: closeJetpackCarouselLightbox called');
                // console.log('Drinks Plugin: Current lightbox:', currentJetpackCarouselLightbox);
                
                if (!currentJetpackCarouselLightbox) {
                    // console.log('Drinks Plugin: No current lightbox to close');
                    return;
                }
                
                // console.log('Drinks Plugin: Removing active class and closing lightbox');
                currentJetpackCarouselLightbox.classList.remove('active');
                document.body.style.overflow = '';
                
                setTimeout(() => {
                    if (currentJetpackCarouselLightbox && currentJetpackCarouselLightbox.parentNode) {
                        // console.log('Drinks Plugin: Removing lightbox from DOM');
                        currentJetpackCarouselLightbox.parentNode.removeChild(currentJetpackCarouselLightbox);
                    }
                    currentJetpackCarouselLightbox = null;
                    // console.log('Drinks Plugin: Lightbox closed successfully');
                }, 300);
            }
            
            /**
             * Create Jetpack carousel lightbox overlay
             */
            function createJetpackCarouselLightboxOverlay(initialImageSrc, initialImageAlt) {
                const overlay = document.createElement('div');
                overlay.className = 'jetpack-carousel-lightbox-overlay';
                overlay.innerHTML = `
                    <div class="jetpack-carousel-lightbox-content">
                        <div class="jetpack-carousel-lightbox-header">
                            <button type="button" class="jetpack-carousel-lightbox-close" aria-label="Close carousel">&times;</button>
                        </div>
                        <div class="jetpack-carousel-lightbox-body">
                            <div class="wp-block-jetpack-slideshow aligncenter" data-autoplay="false" data-delay="3" data-effect="slide">
                                <div class="wp-block-jetpack-slideshow_container swiper-container">
                                    <ul class="wp-block-jetpack-slideshow_swiper-wrapper swiper-wrapper" id="jetpack-carousel-slides">
                                        <!-- Slides will be loaded via AJAX -->
                                    </ul>
                                    
                                    <!-- Slideshow controls -->
                                    <a class="wp-block-jetpack-slideshow_button-prev swiper-button-prev swiper-button-white" role="button" tabindex="0" aria-label="Previous slide"></a>
                                    <a class="wp-block-jetpack-slideshow_button-next swiper-button-next swiper-button-white" role="button" tabindex="0" aria-label="Next slide"></a>
                                    <a aria-label="Pause Slideshow" class="wp-block-jetpack-slideshow_button-pause" role="button"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add event listeners
                const closeButton = overlay.querySelector('.jetpack-carousel-lightbox-close');
                if (closeButton) {
                    // console.log('Drinks Plugin: Close button found, adding click listener');
                    closeButton.addEventListener('click', (e) => {
                        // console.log('Drinks Plugin: Close button clicked');
                        e.preventDefault();
                        e.stopPropagation();
                        closeJetpackCarouselLightbox();
                    });
                } else {
                    console.error('Drinks Plugin: Close button not found in overlay');
                }
                
                // Close on overlay click
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        closeJetpackCarouselLightbox();
                    }
                });
                
                return overlay;
            }
            
            /**
             * Load drinks for Jetpack carousel
             */
            function loadDrinksForJetpackCarousel(overlay, figcaptionText) {
                const slidesContainer = overlay.querySelector('#jetpack-carousel-slides');
                if (!slidesContainer) {
                    console.error('Drinks Plugin: No slides container found');
                    return;
                }
                
                console.log('Drinks Plugin: Starting to load drinks for carousel...');
                console.log('Drinks Plugin: Figcaption text:', figcaptionText);
                console.log('Drinks Plugin: Figcaption type:', typeof figcaptionText);
                
                // Show loading state
                slidesContainer.innerHTML = '<li class="wp-block-jetpack-slideshow_slide swiper-slide"><div class="jetpack-carousel-loading"><div class="jetpack-carousel-loading-spinner"></div>Loading drinks...</div></li>';
                
                // Make AJAX call to get random drinks
                const formData = new FormData();
                formData.append('action', 'drinks_filter_carousel');
                formData.append('search_term', '');
                formData.append('figcaption_text', figcaptionText);
                
                // Use localized WordPress AJAX URL
                const ajaxUrl = window.drinksPluginAjax ? window.drinksPluginAjax.ajaxurl : '/wp-admin/admin-ajax.php';
                // console.log('Drinks Plugin: Using AJAX URL:', ajaxUrl);
                
                console.log('Drinks Plugin: About to make AJAX call to:', ajaxUrl);
                console.log('Drinks Plugin: FormData contents:', Array.from(formData.entries()));
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // console.log('Drinks Plugin: AJAX response status:', response.status);
                    // console.log('Drinks Plugin: AJAX response headers:', response.headers);
                    return response.text();
                })
                .then(html => {
                    // console.log('Drinks Plugin: AJAX response HTML length:', html.length);
                    // console.log('Drinks Plugin: AJAX response HTML preview:', html.substring(0, 200) + '...');
                    // console.log('Drinks Plugin: Full AJAX response:', html);
                    
                    // Replace the loading slide with the new slides
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newSlides = tempDiv.querySelectorAll('li');
                    
                    // console.log('Drinks Plugin: Found', newSlides.length, 'new slides in AJAX response');
                    
                    // Debug: Check the first slide
                    if (newSlides.length > 0) {
                        const firstSlide = newSlides[0];
                        const firstImg = firstSlide.querySelector('img');
                        if (firstImg) {
                            console.log('Drinks Plugin: First slide image ID:', firstImg.getAttribute('data-id'));
                            console.log('Drinks Plugin: First slide image alt:', firstImg.getAttribute('alt'));
                        }
                    }
                    
                    // Clear the container and add all new slides
                    slidesContainer.innerHTML = '';
                    
                    newSlides.forEach((slide, index) => {
                        // console.log('Drinks Plugin: Adding slide', index, ':', slide.outerHTML.substring(0, 100) + '...');
                        slidesContainer.appendChild(slide.cloneNode(true));
                    });
                    
                    // console.log('Drinks Plugin: Total slides in container after adding:', slidesContainer.children.length);
                    
                    // Initialize Jetpack slideshow functionality
                    initializeJetpackSlideshow(overlay);
                    
                    // Adjust height after images are loaded
                    setTimeout(() => {
                        adjustCarouselHeight(overlay);
                    }, 100);
                    
                    // console.log('Drinks Plugin: Jetpack carousel loaded with', slidesContainer.children.length, 'slides');
                })
                .catch(error => {
                    console.error('Drinks Plugin: Error loading drinks for Jetpack carousel:', error);
                    const loadingSlide = slidesContainer.querySelector('.jetpack-carousel-loading');
                    if (loadingSlide) {
                        loadingSlide.innerHTML = '<div class="jetpack-carousel-loading">Error loading drinks</div>';
                    }
                });
            }
            
            /**
             * Initialize Jetpack slideshow functionality
             */
            function initializeJetpackSlideshow(overlay) {
                // console.log('Drinks Plugin: Initializing Jetpack slideshow...');
                
                // Check if Jetpack slideshow scripts are loaded
                if (typeof window.jetpackSlideshowSettings !== 'undefined') {
                    // console.log('Drinks Plugin: Jetpack slideshow settings found, using native initialization');
                    // Jetpack slideshow is available, initialize it
                    const slideshow = overlay.querySelector('.wp-block-jetpack-slideshow');
                    if (slideshow) {
                        // console.log('Drinks Plugin: Found slideshow element, initializing...');
                        // Trigger Jetpack slideshow initialization
                        if (window.jetpackSlideshowSettings && window.jetpackSlideshowSettings.init) {
                            window.jetpackSlideshowSettings.init(slideshow);
                            // console.log('Drinks Plugin: Jetpack slideshow initialized successfully');
                        } else {
                            // console.log('Drinks Plugin: Jetpack init function not found');
                        }
                    } else {
                        // console.log('Drinks Plugin: No slideshow element found');
                    }
                } else {
                    // console.log('Drinks Plugin: Jetpack slideshow not available, using fallback functionality');
                    // console.log('Drinks Plugin: About to call addBasicSlideshowFunctionality...');
                    // Fallback: Add basic slideshow functionality
                    addBasicSlideshowFunctionality(overlay);
                    // console.log('Drinks Plugin: addBasicSlideshowFunctionality completed');
                }
            }
            
            /**
             * Add basic slideshow functionality if Jetpack is not available
             */
            function addBasicSlideshowFunctionality(overlay) {
                // console.log('Drinks Plugin: Setting up fallback slideshow functionality...');
                
                const slidesContainer = overlay.querySelector('.wp-block-jetpack-slideshow_swiper-wrapper');
                const slides = slidesContainer.querySelectorAll('.wp-block-jetpack-slideshow_slide');
                const prevButton = overlay.querySelector('.wp-block-jetpack-slideshow_button-prev');
                const nextButton = overlay.querySelector('.wp-block-jetpack-slideshow_button-next');
                const pagination = overlay.querySelector('.wp-block-jetpack-slideshow_pagination');
                
                // console.log('Drinks Plugin: Fallback - Found', slides.length, 'slides');
                // console.log('Drinks Plugin: Fallback - Prev button:', !!prevButton);
                // console.log('Drinks Plugin: Fallback - Next button:', !!nextButton);
                // console.log('Drinks Plugin: Fallback - Pagination:', !!pagination);
                // console.log('Drinks Plugin: Fallback - Slides container:', slidesContainer);
                // console.log('Drinks Plugin: Fallback - Overlay:', overlay);
                
                // Start at slide 1 (index 1) because index 0 is the clone of the last slide
                // This ensures the clicked image (which should be the first real slide) is shown
                let currentSlide = 1;
                
                // Show first slide
                // console.log('Drinks Plugin: Fallback - Initial slide setup, showing slide:', currentSlide);
                showSlide(currentSlide);
                
                // Debug: Check slide visibility
                slides.forEach((slide, i) => {
                    // console.log('Drinks Plugin: Fallback - Slide', i, 'display:', slide.style.display, 'classes:', slide.className);
                });
                
                // Previous button
                if (prevButton) {
                    // console.log('Drinks Plugin: Adding previous button event listener');
                    prevButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                        showSlide(currentSlide);
                        // console.log('Drinks Plugin: Fallback - Previous slide, now at:', currentSlide);
                    });
                } else {
                    // console.log('Drinks Plugin: Previous button not found!');
                }
                
                // Next button
                if (nextButton) {
                    // console.log('Drinks Plugin: Adding next button event listener');
                    nextButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        currentSlide = (currentSlide + 1) % slides.length;
                        showSlide(currentSlide);
                        // console.log('Drinks Plugin: Fallback - Next slide, now at:', currentSlide);
                    });
                } else {
                    // console.log('Drinks Plugin: Next button not found!');
                }
                
                // Pagination
                if (pagination && slides.length > 1) {
                    // Only create bullets for the real slides (not the clones)
                    // For 5 real slides + 2 clones = 7 total, we want 5 bullets
                    const realSlidesCount = slides.length - 2; // Subtract 2 clones
                    for (let i = 0; i < realSlidesCount; i++) {
                        const bullet = document.createElement('button');
                        bullet.className = 'swiper-pagination-bullet';
                        bullet.setAttribute('aria-label', `Go to slide ${i + 1}`);
                        bullet.addEventListener('click', () => {
                            // Map bullet index to actual slide index (accounting for clone at start)
                            currentSlide = i + 1; // +1 because slide 0 is a clone
                            showSlide(currentSlide);
                            // console.log('Drinks Plugin: Fallback - Jumped to slide:', currentSlide);
                        });
                        pagination.appendChild(bullet);
                    }
                }
                
                function showSlide(index) {
                    // console.log('Drinks Plugin: Fallback - Showing slide:', index);
                    slides.forEach((slide, i) => {
                        if (i === index) {
                            slide.style.display = 'flex';
                            slide.classList.add('active');
                            
                            // Apply dynamic height adjustment to the new slide
                            const figure = slide.querySelector('figure');
                            const img = slide.querySelector('img');
                            const figcaption = slide.querySelector('figcaption');
                            
                            if (figure && img && figcaption) {
                                setTimeout(() => {
                                    adjustFigureHeight(figure, img, figcaption);
                                }, 50);
                            }
                        } else {
                            slide.style.display = 'none';
                            slide.classList.remove('active');
                        }
                    });
                    
                    // Update pagination if it exists
                    if (pagination) {
                        const bullets = pagination.querySelectorAll('.swiper-pagination-bullet');
                        bullets.forEach((bullet, i) => {
                            // Map slide index to bullet index (slide index - 1 because slide 0 is a clone)
                            bullet.classList.toggle('swiper-pagination-bullet-active', i === (index - 1));
                        });
                    }
                    
                    // console.log('Drinks Plugin: Fallback - Slide', index, 'is now active');
                }
                
                // console.log('Drinks Plugin: Fallback slideshow functionality set up successfully');
            }
            
            /**
             * Setup Jetpack carousel for existing images
             */
            function setupJetpackCarouselForImages() {
                // Look for both new attribute and existing cocktail-carousel class
                const newImages = document.querySelectorAll('[data-carousel-enabled] img');
                const existingImages = document.querySelectorAll('.cocktail-carousel img');
                
                // console.log('Drinks Plugin: Found', newImages.length, 'images with data-carousel-enabled');
                // console.log('Drinks Plugin: Found', existingImages.length, 'images with cocktail-carousel class');
                
                const allImages = [...new Set([...newImages, ...existingImages])];
                // console.log('Drinks Plugin: Total carousel-enabled images:', allImages.length);
                
                if (allImages.length === 0) {
                    // console.log('Drinks Plugin: No carousel-enabled images found. Checking for containers...');
                    const newContainers = document.querySelectorAll('[data-carousel-enabled]');
                    const existingContainers = document.querySelectorAll('.cocktail-carousel');
                    // console.log('Drinks Plugin: Found', newContainers.length, 'containers with data-carousel-enabled');
                    // console.log('Drinks Plugin: Found', existingContainers.length, 'containers with cocktail-carousel class');
                }
                
                allImages.forEach((img, index) => {
                    // console.log('Drinks Plugin: Processing image', index, ':', img.src);
                    const container = img.closest('[data-carousel-enabled], .cocktail-carousel');
                    if (container) {
                        container.style.cursor = 'pointer';
                        // console.log('Drinks Plugin: Set cursor pointer on container for image', index);
                    }
                });
            }
            
            /**
             * Setup Jetpack carousel observer for dynamically added content
             */
            function setupJetpackCarouselObserver() {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.addedNodes.length > 0) {
                            mutation.addedNodes.forEach((node) => {
                                if (node.nodeType === 1 && node.querySelector) {
                                    const carouselImages = node.querySelectorAll('[data-carousel-enabled] img');
                                    carouselImages.forEach(img => {
                                        const container = img.closest('[data-carousel-enabled]');
                                        if (container) {
                                            container.style.cursor = 'pointer';
                                        }
                                    });
                                }
                            });
                        }
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
            
            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                // console.log('Drinks Plugin: DOM still loading, waiting for DOMContentLoaded...');
                document.addEventListener('DOMContentLoaded', initJetpackCarouselLightbox);
            } else {
                // console.log('Drinks Plugin: DOM already ready, initializing immediately...');
                initJetpackCarouselLightbox();
            }
            
            // Make functions globally available
            window.drinksPluginJetpackCarousel = {
                init: initJetpackCarouselLightbox,
                open: openJetpackCarouselLightbox,
                close: closeJetpackCarouselLightbox,
                setup: setupJetpackCarouselForImages
            };
            
            // Add global test function for debugging
            window.testJetpackCarousel = function() {
                // console.log('Drinks Plugin: Testing Jetpack carousel system...');
                // console.log('Drinks Plugin: Global object available:', !!window.drinksPluginJetpackCarousel);
                // console.log('Drinks Plugin: Current carousel lightbox:', currentJetpackCarouselLightbox);
                
                const containers = document.querySelectorAll('[data-carousel-enabled]');
                // console.log('Drinks Plugin: Found', containers.length, 'carousel-enabled containers');
                
                if (containers.length > 0) {
                    // console.log('Drinks Plugin: First container:', containers[0]);
                    // console.log('Drinks Plugin: First container classes:', containers[0].className);
                }
                
                return {
                    containers: containers.length,
                    lightbox: !!currentJetpackCarouselLightbox,
                    global: !!window.drinksPluginJetpackCarousel
                };
            };
            
            // console.log('Drinks Plugin: Jetpack carousel script loaded successfully');
            // console.log('Drinks Plugin: Test with: testJetpackCarousel()');
            
        })();
        </script>
        <?php
    }
    
}

// Initialize the plugin and expose global accessor
global $drinks_plugin;
$drinks_plugin = new DrinksPlugin();