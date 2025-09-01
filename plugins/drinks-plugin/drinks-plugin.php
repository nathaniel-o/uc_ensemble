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
        add_action('wp_ajax_filter_carousel', array($this, 'handle_filter_carousel'));
        add_action('wp_ajax_nopriv_filter_carousel', array($this, 'handle_filter_carousel'));
        
        // Add carousel lightbox functionality
        add_action('wp_footer', array($this, 'add_carousel_lightbox_script'));
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
            
            /* Pop out effect styles */
            .cocktail-pop-out {
                transition: transform 0.3s ease;
            }
            
            .cocktail-pop-out:hover {
                transform: scale(1.05);
            }
            
            /* Jetpack Carousel Lightbox Styles */
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
            
            .jetpack-carousel-lightbox-overlay.active {
                display: flex;
            }
            
            .jetpack-carousel-lightbox-content {
                position: relative;
                width: 95%;
                height: 95%;
                max-width: 1400px;
                background: #000;
                border-radius: 12px;
                overflow: hidden;
            }
            
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
            
            .jetpack-carousel-lightbox-title {
                color: white;
                font-size: 24px;
                font-weight: bold;
                margin: 0;
            }
            
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
            }
            
            .jetpack-carousel-lightbox-close:hover {
                background: rgba(255, 255, 255, 0.3);
            }
            
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
        // Get search term and exclude ID from POST data
        $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        $exclude_id = isset($_POST['exclude_id']) ? intval($_POST['exclude_id']) : 0;

        // Get drink posts
        $drink_posts = $this->uc_get_drinks();

        // If we have an exclude ID, filter it out
        if ($exclude_id > 0) {
            $drink_posts = array_filter($drink_posts, function($post) use ($exclude_id) {
                return $post['id'] != $exclude_id;
            });
            $drink_posts = array_values($drink_posts); // Re-index array
        }

        // Generate carousel HTML with 4 random drinks (plus the original makes 5 total)
        $filtered_carousel = $this->uc_random_carousel($drink_posts, 4, 0, 0); // Changed show_content to 0
        
        // Debug: Log what we're generating
        error_log('Drinks Plugin: AJAX filter_carousel - Generated carousel with length: ' . strlen($filtered_carousel));
        
        echo $filtered_carousel;

        wp_die(); // Required for proper AJAX response
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
        if ($total_slides === 4) {
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
                console.log('Drinks Plugin: Jetpack carousel lightbox initialized');
                
                // Add click handlers to carousel-enabled images
                document.addEventListener('click', handleJetpackCarouselImageClick);
                
                // Add keyboard support
                document.addEventListener('keydown', handleJetpackCarouselKeydown);
                
                // Setup carousel for existing images
                setupJetpackCarouselForImages();
                
                // Setup observer for dynamically added content
                setupJetpackCarouselObserver();
                
                console.log('Drinks Plugin: Event listeners and observers set up');
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
                
                console.log('Drinks Plugin: Carousel-related click detected on:', event.target);
                console.log('Drinks Plugin: Clicked element tagName:', event.target.tagName);
                console.log('Drinks Plugin: Clicked element classes:', event.target.className);
                
                // Debug: Show the element structure (only for carousel-related clicks)
                console.log('Drinks Plugin: Clicked element HTML:', event.target.outerHTML.substring(0, 300) + '...');
                
                // Debug: Check parent elements for carousel attributes
                let currentElement = event.target;
                let depth = 0;
                while (currentElement && depth < 5) {
                    console.log('Drinks Plugin: Parent', depth, ':', currentElement.tagName, 'classes:', currentElement.className);
                    if (currentElement.classList && currentElement.classList.contains('cocktail-carousel')) {
                        console.log('Drinks Plugin: Found cocktail-carousel at parent level', depth);
                    }
                    if (currentElement.hasAttribute('data-carousel-enabled')) {
                        console.log('Drinks Plugin: Found data-carousel-enabled at parent level', depth);
                    }
                    currentElement = currentElement.parentElement;
                    depth++;
                }
                
                // Check if the clicked element itself has the attribute
                if (event.target.hasAttribute('data-carousel-enabled')) {
                    console.log('Drinks Plugin: Clicked element has data-carousel-enabled');
                    const container = event.target;
                    const img = event.target.querySelector('img') || event.target;
                    openJetpackCarouselLightbox(img, container);
                    return;
                }
                
                // Check if it's an img and find parent with attribute or cocktail-carousel class
                if (event.target.tagName === 'IMG') {
                    console.log('Drinks Plugin: Clicked on IMG element, searching for parent container...');
                    
                    // First try to find container with data-carousel-enabled
                    let container = event.target.closest('[data-carousel-enabled]');
                    
                    // If not found, look for cocktail-carousel class (existing system)
                    if (!container) {
                        container = event.target.closest('.cocktail-carousel');
                        if (container) {
                            console.log('Drinks Plugin: Found container with cocktail-carousel class, treating as carousel-enabled');
                        }
                    }
                    
                    console.log('Drinks Plugin: Container found:', container);
                    
                    if (!container) {
                        console.log('Drinks Plugin: No carousel-enabled container found for IMG');
                        console.log('Drinks Plugin: Parent elements:', event.target.parentElement, event.target.parentElement?.parentElement);
                        return;
                    }
                    
                    event.preventDefault();
                    event.stopPropagation();
                    
                    console.log('Drinks Plugin: Opening Jetpack carousel lightbox for image:', event.target.src);
                    openJetpackCarouselLightbox(event.target, container);
                    return;
                }
                
                // If clicked on a container element, check if it has carousel attributes
                if (event.target.classList && (event.target.classList.contains('cocktail-carousel') || event.target.hasAttribute('data-carousel-enabled'))) {
                    console.log('Drinks Plugin: Clicked on carousel container element');
                    const container = event.target;
                    const img = container.querySelector('img');
                    
                    if (img) {
                        event.preventDefault();
                        event.stopPropagation();
                        console.log('Drinks Plugin: Opening Jetpack carousel lightbox for image:', img.src);
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
                        console.log('Drinks Plugin: Click near carousel elements but no carousel container found');
                    }
                    return;
                }
                
                console.log('Drinks Plugin: Container with carousel attributes found:', container);
                
                event.preventDefault();
                event.stopPropagation();
                
                const img = container.querySelector('img');
                console.log('Drinks Plugin: Image found in container:', img);
                
                if (!img) {
                    console.log('Drinks Plugin: No image found in container');
                    return;
                }
                
                console.log('Drinks Plugin: Opening Jetpack carousel lightbox for image:', img.src);
                openJetpackCarouselLightbox(img, container);
            }
            
            /**
             * Handle keyboard events for carousel
             */
            function handleJetpackCarouselKeydown(event) {
                console.log('Drinks Plugin: Keydown event:', event.key, 'Current lightbox:', !!currentJetpackCarouselLightbox);
                
                if (!currentJetpackCarouselLightbox) return;
                
                if (event.key === 'Escape') {
                    console.log('Drinks Plugin: Escape key pressed, closing carousel lightbox');
                    closeJetpackCarouselLightbox();
                }
            }
            
            /**
             * Open Jetpack carousel lightbox
             */
            function openJetpackCarouselLightbox(img, container) {
                const imageId = img.dataset.id || img.getAttribute('data-id') || '';
                const imageSrc = img.src;
                const imageAlt = img.alt || 'Drink Image';
                
                // Create Jetpack carousel lightbox overlay
                const overlay = createJetpackCarouselLightboxOverlay(imageSrc, imageAlt);
                document.body.appendChild(overlay);
                
                // Load additional drinks for carousel
                loadDrinksForJetpackCarousel(overlay, imageId);
                
                // Show lightbox
                requestAnimationFrame(() => {
                    overlay.classList.add('active');
                    currentJetpackCarouselLightbox = overlay;
                    document.body.style.overflow = 'hidden';
                });
            }
            
            /**
             * Close Jetpack carousel lightbox
             */
            function closeJetpackCarouselLightbox() {
                console.log('Drinks Plugin: closeJetpackCarouselLightbox called');
                console.log('Drinks Plugin: Current lightbox:', currentJetpackCarouselLightbox);
                
                if (!currentJetpackCarouselLightbox) {
                    console.log('Drinks Plugin: No current lightbox to close');
                    return;
                }
                
                console.log('Drinks Plugin: Removing active class and closing lightbox');
                currentJetpackCarouselLightbox.classList.remove('active');
                document.body.style.overflow = '';
                
                setTimeout(() => {
                    if (currentJetpackCarouselLightbox && currentJetpackCarouselLightbox.parentNode) {
                        console.log('Drinks Plugin: Removing lightbox from DOM');
                        currentJetpackCarouselLightbox.parentNode.removeChild(currentJetpackCarouselLightbox);
                    }
                    currentJetpackCarouselLightbox = null;
                    console.log('Drinks Plugin: Lightbox closed successfully');
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
                            <h3 class="jetpack-carousel-lightbox-title">🎠 Drinks Carousel</h3>
                            <button type="button" class="jetpack-carousel-lightbox-close" aria-label="Close carousel">&times;</button>
                        </div>
                        <div class="jetpack-carousel-lightbox-body">
                            <div class="wp-block-jetpack-slideshow aligncenter" data-autoplay="false" data-delay="3" data-effect="slide">
                                <div class="wp-block-jetpack-slideshow_container swiper-container">
                                    <ul class="wp-block-jetpack-slideshow_swiper-wrapper swiper-wrapper" id="jetpack-carousel-slides">
                                        <li class="wp-block-jetpack-slideshow_slide swiper-slide">
                                            <figure>
                                                <img src="${initialImageSrc}" alt="${initialImageAlt}" class="wp-block-jetpack-slideshow_image" />
                                                <figcaption>${initialImageAlt}</figcaption>
                                            </figure>
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
                    </div>
                `;
                
                // Add event listeners
                const closeButton = overlay.querySelector('.jetpack-carousel-lightbox-close');
                if (closeButton) {
                    console.log('Drinks Plugin: Close button found, adding click listener');
                    closeButton.addEventListener('click', (e) => {
                        console.log('Drinks Plugin: Close button clicked');
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
            function loadDrinksForJetpackCarousel(overlay, excludeImageId) {
                const slidesContainer = overlay.querySelector('#jetpack-carousel-slides');
                if (!slidesContainer) {
                    console.error('Drinks Plugin: No slides container found');
                    return;
                }
                
                console.log('Drinks Plugin: Starting to load drinks for carousel...');
                console.log('Drinks Plugin: Exclude ID:', excludeImageId);
                
                // Show loading state
                slidesContainer.innerHTML += '<li class="wp-block-jetpack-slideshow_slide swiper-slide"><div class="jetpack-carousel-loading"><div class="jetpack-carousel-loading-spinner"></div>Loading drinks...</div></li>';
                
                // Make AJAX call to get random drinks
                const formData = new FormData();
                formData.append('action', 'filter_carousel');
                formData.append('search_term', '');
                formData.append('exclude_id', excludeImageId);
                
                // Use localized WordPress AJAX URL
                const ajaxUrl = window.drinksPluginAjax ? window.drinksPluginAjax.ajaxurl : '/wp-admin/admin-ajax.php';
                console.log('Drinks Plugin: Using AJAX URL:', ajaxUrl);
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Drinks Plugin: AJAX response status:', response.status);
                    return response.text();
                })
                .then(html => {
                    console.log('Drinks Plugin: AJAX response HTML length:', html.length);
                    console.log('Drinks Plugin: AJAX response HTML preview:', html.substring(0, 200) + '...');
                    
                    // Remove loading slide
                    const loadingSlide = slidesContainer.querySelector('.jetpack-carousel-loading');
                    if (loadingSlide) {
                        loadingSlide.remove();
                    }
                    
                    // Parse the HTML and add slides
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newSlides = tempDiv.querySelectorAll('li');
                    
                    console.log('Drinks Plugin: Found', newSlides.length, 'new slides in AJAX response');
                    
                    newSlides.forEach((slide, index) => {
                        console.log('Drinks Plugin: Adding slide', index, ':', slide.outerHTML.substring(0, 100) + '...');
                        slidesContainer.appendChild(slide.cloneNode(true));
                    });
                    
                    console.log('Drinks Plugin: Total slides in container after adding:', slidesContainer.children.length);
                    
                    // Initialize Jetpack slideshow functionality
                    initializeJetpackSlideshow(overlay);
                    
                    console.log('Drinks Plugin: Jetpack carousel loaded with', slidesContainer.children.length, 'slides');
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
                console.log('Drinks Plugin: Initializing Jetpack slideshow...');
                
                // Check if Jetpack slideshow scripts are loaded
                if (typeof window.jetpackSlideshowSettings !== 'undefined') {
                    console.log('Drinks Plugin: Jetpack slideshow settings found, using native initialization');
                    // Jetpack slideshow is available, initialize it
                    const slideshow = overlay.querySelector('.wp-block-jetpack-slideshow');
                    if (slideshow) {
                        console.log('Drinks Plugin: Found slideshow element, initializing...');
                        // Trigger Jetpack slideshow initialization
                        if (window.jetpackSlideshowSettings && window.jetpackSlideshowSettings.init) {
                            window.jetpackSlideshowSettings.init(slideshow);
                            console.log('Drinks Plugin: Jetpack slideshow initialized successfully');
                        } else {
                            console.log('Drinks Plugin: Jetpack init function not found');
                        }
                    } else {
                        console.log('Drinks Plugin: No slideshow element found');
                    }
                } else {
                    console.log('Drinks Plugin: Jetpack slideshow not available, using fallback functionality');
                    // Fallback: Add basic slideshow functionality
                    addBasicSlideshowFunctionality(overlay);
                }
            }
            
            /**
             * Add basic slideshow functionality if Jetpack is not available
             */
            function addBasicSlideshowFunctionality(overlay) {
                console.log('Drinks Plugin: Setting up fallback slideshow functionality...');
                
                const slidesContainer = overlay.querySelector('.wp-block-jetpack-slideshow_swiper-wrapper');
                const slides = slidesContainer.querySelectorAll('.wp-block-jetpack-slideshow_slide');
                const prevButton = overlay.querySelector('.wp-block-jetpack-slideshow_button-prev');
                const nextButton = overlay.querySelector('.wp-block-jetpack-slideshow_button-next');
                const pagination = overlay.querySelector('.wp-block-jetpack-slideshow_pagination');
                
                console.log('Drinks Plugin: Fallback - Found', slides.length, 'slides');
                console.log('Drinks Plugin: Fallback - Prev button:', !!prevButton);
                console.log('Drinks Plugin: Fallback - Next button:', !!nextButton);
                console.log('Drinks Plugin: Fallback - Pagination:', !!pagination);
                
                let currentSlide = 0;
                
                // Show first slide
                console.log('Drinks Plugin: Fallback - Initial slide setup, showing slide:', currentSlide);
                showSlide(currentSlide);
                
                // Debug: Check slide visibility
                slides.forEach((slide, i) => {
                    console.log('Drinks Plugin: Fallback - Slide', i, 'display:', slide.style.display, 'classes:', slide.className);
                });
                
                // Previous button
                if (prevButton) {
                    prevButton.addEventListener('click', () => {
                        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                        showSlide(currentSlide);
                        console.log('Drinks Plugin: Fallback - Previous slide, now at:', currentSlide);
                    });
                }
                
                // Next button
                if (nextButton) {
                    nextButton.addEventListener('click', () => {
                        currentSlide = (currentSlide + 1) % slides.length;
                        showSlide(currentSlide);
                        console.log('Drinks Plugin: Fallback - Next slide, now at:', currentSlide);
                    });
                }
                
                // Pagination
                if (pagination && slides.length > 1) {
                    slides.forEach((slide, index) => {
                        const bullet = document.createElement('button');
                        bullet.className = 'swiper-pagination-bullet';
                        bullet.setAttribute('aria-label', `Go to slide ${index + 1}`);
                        bullet.addEventListener('click', () => {
                            currentSlide = index;
                            showSlide(currentSlide);
                            console.log('Drinks Plugin: Fallback - Jumped to slide:', currentSlide);
                        });
                        pagination.appendChild(bullet);
                    });
                }
                
                function showSlide(index) {
                    console.log('Drinks Plugin: Fallback - Showing slide:', index);
                    slides.forEach((slide, i) => {
                        if (i === index) {
                            slide.style.display = 'flex';
                            slide.classList.add('active');
                        } else {
                            slide.style.display = 'none';
                            slide.classList.remove('active');
                        }
                    });
                    
                    // Update pagination
                    const bullets = pagination.querySelectorAll('.swiper-pagination-bullet');
                    bullets.forEach((bullet, i) => {
                        bullet.classList.toggle('swiper-pagination-bullet-active', i === index);
                    });
                }
                
                console.log('Drinks Plugin: Fallback slideshow functionality set up successfully');
            }
            
            /**
             * Setup Jetpack carousel for existing images
             */
            function setupJetpackCarouselForImages() {
                // Look for both new attribute and existing cocktail-carousel class
                const newImages = document.querySelectorAll('[data-carousel-enabled] img');
                const existingImages = document.querySelectorAll('.cocktail-carousel img');
                
                console.log('Drinks Plugin: Found', newImages.length, 'images with data-carousel-enabled');
                console.log('Drinks Plugin: Found', existingImages.length, 'images with cocktail-carousel class');
                
                const allImages = [...new Set([...newImages, ...existingImages])];
                console.log('Drinks Plugin: Total carousel-enabled images:', allImages.length);
                
                if (allImages.length === 0) {
                    console.log('Drinks Plugin: No carousel-enabled images found. Checking for containers...');
                    const newContainers = document.querySelectorAll('[data-carousel-enabled]');
                    const existingContainers = document.querySelectorAll('.cocktail-carousel');
                    console.log('Drinks Plugin: Found', newContainers.length, 'containers with data-carousel-enabled');
                    console.log('Drinks Plugin: Found', existingContainers.length, 'containers with cocktail-carousel class');
                }
                
                allImages.forEach((img, index) => {
                    console.log('Drinks Plugin: Processing image', index, ':', img.src);
                    const container = img.closest('[data-carousel-enabled], .cocktail-carousel');
                    if (container) {
                        container.style.cursor = 'pointer';
                        console.log('Drinks Plugin: Set cursor pointer on container for image', index);
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
                console.log('Drinks Plugin: DOM still loading, waiting for DOMContentLoaded...');
                document.addEventListener('DOMContentLoaded', initJetpackCarouselLightbox);
            } else {
                console.log('Drinks Plugin: DOM already ready, initializing immediately...');
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
                console.log('Drinks Plugin: Testing Jetpack carousel system...');
                console.log('Drinks Plugin: Global object available:', !!window.drinksPluginJetpackCarousel);
                console.log('Drinks Plugin: Current carousel lightbox:', currentJetpackCarouselLightbox);
                
                const containers = document.querySelectorAll('[data-carousel-enabled]');
                console.log('Drinks Plugin: Found', containers.length, 'carousel-enabled containers');
                
                if (containers.length > 0) {
                    console.log('Drinks Plugin: First container:', containers[0]);
                    console.log('Drinks Plugin: First container classes:', containers[0].className);
                }
                
                return {
                    containers: containers.length,
                    lightbox: !!currentJetpackCarouselLightbox,
                    global: !!window.drinksPluginJetpackCarousel
                };
            };
            
            console.log('Drinks Plugin: Jetpack carousel script loaded successfully');
            console.log('Drinks Plugin: Test with: testJetpackCarousel()');
            
        })();
        </script>
        <?php
    }
}

// Initialize the plugin and expose global accessor
global $drinks_plugin;
$drinks_plugin = new DrinksPlugin();