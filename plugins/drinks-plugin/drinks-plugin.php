<?php
/**
* Plugin Name: Drinks Plugin
* Plugin URI: notyet
* Description: Jetpack-based Lightbox & Image Carousel fn, with custom Drink [Post] Selection & Styles. Drink Posts taxonomy defined ___ ? 
* Version: 1.0.4
* Author: Nathaniel
* License: GPL v2 or later
* Text Domain: drinks-plugin
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DRINKS_PLUGIN_VERSION', '1.0.4');
define('DRINKS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DRINKS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include global wrapper functions
require_once DRINKS_PLUGIN_PATH . 'includes/functions.php';

// Load cocktail-images module
require_once DRINKS_PLUGIN_PATH . 'modules/cocktail-images/cocktail-images.php';

// NOTE: drinks-search module has been consolidated into this file
// Documentation files remain in modules/drinks-search/ for the admin viewer

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
        // //error_log('Drinks Plugin: AJAX handlers registered for drinks_filter_carousel');
        
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
            
            //error_log('Drinks Plugin: Jetpack slideshow assets enqueued');
        } else {
            //error_log('Drinks Plugin: Jetpack slideshow view.js not found');
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
                // Enqueue frontend styles from external CSS file
                wp_enqueue_style(
                    'drinks-plugin-frontend-styles',
                    DRINKS_PLUGIN_URL . 'src/style.css',
                    array(),
                    DRINKS_PLUGIN_VERSION
                );
            }
            
            /**
            * AJAX handler for filter_carousel
            */
            public function handle_filter_carousel() {
                
                //error_log('Drinks Plugin: POST data: ' . print_r($_POST, true));
                
                // Get parameters from POST data
                $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
                $figcaption_text = isset($_POST['figcaption_text']) ? sanitize_text_field($_POST['figcaption_text']) : '';
                $show_content = isset($_POST['show_content']) ? intval($_POST['show_content']) : 0;
                $random = isset($_POST['random']) ? ($_POST['random'] === 'true' || $_POST['random'] === '1') : false;
                
                $num_slides = isset($_POST['num_slides']) ? intval($_POST['num_slides']) : 10;
                //error_log("handle_filter_carouselnumS_slides : " . $num_slides);
                
                // Get drink posts
                $drink_posts = $this->uc_get_drink_posts();
                
                // Use unified uc_image_carousel
                
                $options = array(
                    'drink_posts' => $drink_posts,
                    'num_slides' => $num_slides,
                    'show_titles' => 0,
                    'show_content' => $show_content
                );
                
                //  Parameters : $match_term (first slide) , $filter_term (R slides), $options (includes num_slides)
                //  * For Random, $match_term AND $filter_term must be false (empty string)
                if ($random) {
                    $filtered_carousel = $this->uc_image_carousel('', '', $options);
                } else if (!empty($search_term)) {
                    $filtered_carousel = $this->uc_image_carousel('', $search_term, $options);
                } else {
                    $filtered_carousel = $this->uc_image_carousel($figcaption_text, '', $options);
                }
                
                // Debug: Log what we're generating
                // //error_log('Drinks Plugin: AJAX filter_carousel - Generated carousel with length: ' . strlen($filtered_carousel));
                
                echo $filtered_carousel;
                // //error_log('Drinks Plugin: Sending response: ' . substr($filtered_carousel, 0, 100) . '...');
                
                wp_die(); // Required for proper AJAX response
            }
            
            /**
            * Handle AJAX request for pop out lightbox (drinks content)
            */
            public function handle_get_drink_content() {
                // //error_log('Drinks Plugin: handle_get_drink_content called');
                // //error_log('Drinks Plugin: POST data: ' . print_r($_POST, true));
                
                // Get image ID from POST data
                $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
                // //error_log('Drinks Plugin: Image ID from POST: ' . $image_id);
                
                if ($image_id <= 0) {
                    //error_log('Drinks Plugin: Invalid image ID, sending error response');
                    wp_send_json_error('Invalid image ID');
                    return;
                }
                
                // Get the post ID associated with this image
                $post_id = $this->get_post_id_from_image($image_id);
                // //error_log('Drinks Plugin: Post ID found: ' . ($post_id ? $post_id : 'false'));
                
                if (!$post_id) {
                    // //error_log('Drinks Plugin: No post found for this image, sending error response');
                    wp_send_json_error('No post found for this image');
                    return;
                }
                
                // Generate drink content HTML
                $drink_content = $this->uc_generate_drink_content_html($post_id);
                // //error_log('Drinks Plugin: Generated drink content length: ' . strlen($drink_content));
                
                if ($drink_content) {
                    // //error_log('Drinks Plugin: Sending success response with drink content');
                    wp_send_json_success($drink_content);
                } else {
                    // //error_log('Drinks Plugin: Could not generate drink content, sending error response');
                    wp_send_json_error('Could not generate drink content');
                }
            }
            
            /**
            * Get post ID from image attachment ID using title matching
            */
            private function get_post_id_from_image($image_id) {
                //error_log('Drinks Plugin: get_post_id_from_image called with image_id: ' . $image_id);
                
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
                    //error_log('Drinks Plugin: Found featured image relationship, returning post ID: ' . $posts[0]->ID);
                    return $posts[0]->ID;
                }
                
                // If not a featured image, check if it's attached to any post
                $attachment = get_post($image_id);
                if ($attachment && $attachment->post_parent > 0) {
                    //error_log('Drinks Plugin: Found attachment relationship, returning post ID: ' . $attachment->post_parent);
                    return $attachment->post_parent;
                }
                
                // If no attachment relationship found, use title matching
                if ($attachment) {
                    // Get the image title/alt text
                    $image_title = $attachment->post_title;
                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    
                    //error_log('Drinks Plugin: Image title: "' . $image_title . '", alt: "' . $image_alt . '"');
                    
                    // Prioritize title over alt text for drink matching
                    // Alt text is often a description, title is more likely to be the drink name
                    $search_title = !empty($image_title) ? $image_title : $image_alt;
                    
                    if (!empty($search_title)) {
                        //error_log('Drinks Plugin: Using search title: "' . $search_title . '"');
                        
                        // Get all drink posts
                        $drink_posts = $this->uc_get_drink_posts();
                        //error_log('Drinks Plugin: Found ' . count($drink_posts) . ' drink posts');
                        
                        // Get the normalize function from cocktail-images module
                        $cocktail_module = get_cocktail_images_module();
                        if ($cocktail_module) {
                            $normalized_search_title = $cocktail_module->normalize_title_for_matching($search_title);
                            //error_log('Drinks Plugin: Normalized search title: "' . $normalized_search_title . '"');
                            
                            // Find matching drink post by normalized title
                            foreach ($drink_posts as $post) {
                                $normalized_post_title = $cocktail_module->normalize_title_for_matching($post['title']);
                                //error_log('Drinks Plugin: Comparing "' . $normalized_search_title . '" vs "' . $normalized_post_title . '" (post: ' . $post['title'] . ')');
                                
                                // Check for exact match (case-insensitive)
                                if (strcasecmp($normalized_post_title, $normalized_search_title) === 0) {
                                    //error_log('Drinks Plugin: Found exact matching post ID: ' . $post['id']);
                                    return $post['id'];
                                }
                            }
                            
                            // If no exact match found, try partial matching
                            //error_log('Drinks Plugin: No exact match found, trying partial matching...');
                            foreach ($drink_posts as $post) {
                                $normalized_post_title = $cocktail_plugin->normalize_title_for_matching($post['title']);
                                
                                // Check if the search title contains the post title or vice versa
                                if (stripos($normalized_search_title, $normalized_post_title) !== false || 
                                stripos($normalized_post_title, $normalized_search_title) !== false) {
                                    //error_log('Drinks Plugin: Found partial matching post ID: ' . $post['id'] . ' (search: "' . $normalized_search_title . '" contains/contained in post: "' . $normalized_post_title . '")');
                                    return $post['id'];
                                }
                            }
                        } else {
                            //error_log('Drinks Plugin: Cocktail plugin not available, using fallback matching');
                            // Fallback to simple matching if cocktail plugin not available
                            $normalized_search_title = strtolower($search_title);
                            
                            foreach ($drink_posts as $post) {
                                $normalized_post_title = strtolower($post['title']);
                                //error_log('Drinks Plugin: Fallback comparing "' . $normalized_search_title . '" vs "' . $normalized_post_title . '" (post: ' . $post['title'] . ')');
                                
                                // Check for exact match (case-insensitive)
                                if (strcasecmp($normalized_post_title, $normalized_search_title) === 0) {
                                    //error_log('Drinks Plugin: Found exact matching post ID (fallback): ' . $post['id']);
                                    return $post['id'];
                                }
                            }
                            
                            // If no exact match found, try partial matching
                            //error_log('Drinks Plugin: No exact match found (fallback), trying partial matching...');
                            foreach ($drink_posts as $post) {
                                $normalized_post_title = strtolower($post['title']);
                                
                                // Check if the search title contains the post title or vice versa
                                if (stripos($normalized_search_title, $normalized_post_title) !== false || 
                                stripos($normalized_post_title, $normalized_search_title) !== false) {
                                    //error_log('Drinks Plugin: Found partial matching post ID (fallback): ' . $post['id'] . ' (search: "' . $normalized_search_title . '" contains/contained in post: "' . $normalized_post_title . '")');
                                    return $post['id'];
                                }
                            }
                        }
                    } else {
                        //error_log('Drinks Plugin: No search title available');
                    }
                } else {
                    //error_log('Drinks Plugin: No attachment found for image_id: ' . $image_id);
                }
                
                //error_log('Drinks Plugin: No matching post found, returning false');
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
                // Get category for data attributes
                $category_name = $drinks ? $drinks[0]->name : 'Uncategorized';
                
                $html = '<div class="wp-block-media-text alignwide is-stacked-on-mobile">';
                $html .= '<figure class="wp-block-media-text__media">';
                $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" class="wp-image-' . esc_attr($post_id) . '" data-drink-category="' . esc_attr($category_name) . '" />';
                $html .= '</figure>';
                $html .= '<div class="wp-block-media-text__content">';
                
                $html .= '<h1 data-drink-category="' . esc_attr($category_name) . '">' . esc_html(get_the_title($post_id)) . '</h1>';
                $html .= '<ul class="drink-metadata-list">';
                
                // Category
                $html .= '<li><em>Category</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($category_name) . '">' . esc_html($category_name) . '</a></li>';
                
                // Color
                if (!empty($color)) {
                    $formatted_color = $color;
                    if (substr_count($color, '/') > 1) {
                        // Find the position of the second slash
                        $first_pos = strpos($color, '/');
                        $second_pos = strpos($color, '/', $first_pos + 1);
                        if ($second_pos !== false) {
                            $formatted_color = substr_replace($color, "/\n", $second_pos, 1);
                        }
                    }
                    $html .= '<li><em>Color</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($color) . '">' . esc_html($formatted_color) . '</a></li>';
                }
                
                // Glass
                if (!empty($glass)) {
                    $formatted_glass = $glass;
                    if (substr_count($glass, '/') > 1) {
                        // Find the position of the second slash
                        $first_pos = strpos($glass, '/');
                        $second_pos = strpos($glass, '/', $first_pos + 1);
                        if ($second_pos !== false) {
                            $formatted_glass = substr_replace($glass, "/\n", $second_pos, 1);
                        }
                    }
                    $html .= '<li><em>Glass</em>: <a href="#" class="drink-filter-link" data-filter="' . esc_attr($glass) . '">' . esc_html($formatted_glass) . '</a></li>';
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
            * Retrieve Published Drink Posts from DB (Frontend Use)
            * 
            * U2: get_published_drink_posts() - Returns ONLY published posts
            * Used by: handle_filter_carousel(), handle_get_drink_content()
            * 
            * @return array Array of drink post data
            */
            public function uc_get_drink_posts() {
                // Get published drink posts directly (consolidated from drinks-search module)
                $posts = $this->get_published_drink_posts_raw();
                
                // Transform WP_Post objects into custom array format with expanded search fields
                $drink_posts = array();
                foreach ($posts as $post) {
                    // Get featured image ID and data
                    $thumbnail_id = get_post_thumbnail_id($post->ID);
                    $thumbnail_alt = '';
                    $thumbnail_title = '';
                    $thumbnail_caption = '';
                    $thumbnail_description = '';
                    
                    if ($thumbnail_id) {
                        $thumbnail_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                        $thumbnail_post = get_post($thumbnail_id);
                        if ($thumbnail_post) {
                            $thumbnail_title = $thumbnail_post->post_title;
                            $thumbnail_caption = $thumbnail_post->post_excerpt;
                            $thumbnail_description = $thumbnail_post->post_content;
                        }
                    }
                    
                    $drink_posts[] = array(
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'permalink' => get_permalink($post->ID),
                        'thumbnail' => get_the_post_thumbnail_url($post->ID, 'large'),
                        'thumbnail_id' => $thumbnail_id,
                        'excerpt' => $post->post_excerpt,
                        // Expanded searchable fields
                        'content' => $post->post_content,
                        'thumbnail_alt' => $thumbnail_alt,
                        'thumbnail_title' => $thumbnail_title,
                        'thumbnail_caption' => $thumbnail_caption,
                        'thumbnail_description' => $thumbnail_description,
                    );
                }
                
                return $drink_posts;
            }
            
            /**
             * Get Published Drink Posts (Raw WP_Post objects)
             * 
             * Consolidated from: modules/drinks-search/includes/class-drinks-search.php
             * Retrieves only published posts with 'drinks' taxonomy.
             * 
             * @return array Array of WP_Post objects
             */
            public function get_published_drink_posts_raw() {
                $args = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'drinks',
                            'operator' => 'EXISTS'
                        )
                    )
                );
                
                $query = new WP_Query($args);
                $posts = [];
                
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $posts[] = get_post(get_the_ID());
                    }
                    wp_reset_postdata();
                }
                
                return $posts;
            }
            
            /**
             * Get All Drink Posts Query (includes drafts, pending, etc.)  |  ADMIN ONLY 
             * 
             * Consolidated from: modules/drinks-search/includes/class-drinks-search.php
             * Use Case: Admin operations, total counts
             * 
             * @return WP_Query Query object with all drink posts
             */
            public function get_all_drink_posts_query() {
                $args = array(
                    'post_type' => 'post',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'drinks',
                            'operator' => 'EXISTS'
                        )
                    ),
                    'posts_per_page' => -1
                );
                
                return new WP_Query($args);
            }
            
            /**
             * Get All Media Attachments  |  ADMIN ONLY 
             * 
             * Consolidated from: modules/drinks-search/includes/class-drinks-search.php
             * Retrieves ALL media files from WordPress Media Library.
             * 
             * @return array Array of attachment data with full metadata
             */
            public function get_all_media_attachments() {
                $args = array(
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_wp_attached_file',
                            'compare' => 'EXISTS'
                        )
                    )
                );
                
                $query = new WP_Query($args);
                $attachments = [];
                
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $attachment_id = get_the_ID();
                        
                        // Get attachment metadata
                        $metadata = wp_get_attachment_metadata($attachment_id);
                        $file = get_attached_file($attachment_id);
                        
                        $attachments[] = [
                            'id' => $attachment_id,
                            'title' => get_the_title($attachment_id),
                            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                            'caption' => get_the_excerpt($attachment_id),
                            'description' => get_the_content($attachment_id),
                            'file' => $file,
                            'url' => wp_get_attachment_url($attachment_id),
                            'metadata' => $metadata
                        ];
                    }
                    wp_reset_postdata();
                }
                
                return $attachments;
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
                $num_slides = isset($options['num_slides']) ? intval($options['num_slides']) : 10;
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
                    //error_log('Drinks Plugin: MODE 1 - Filter mode with term: ' . $filter_term);
                    //error_log('Drinks Plugin: MODE 1 - num_slides parameter = ' . $num_slides);
                    $filtered_drinks = array_filter($drink_posts, function($drink) use ($filter_term) {
                        
                        // === POST DATA SEARCHES ===
                        
                        // Search in title
                        if (stripos($drink['title'], $filter_term) !== false) {
                            return true;
                        }
                        
                        // Search in excerpt
                        if (!empty($drink['excerpt']) && stripos($drink['excerpt'], $filter_term) !== false) {
                            return true;
                        }
                        
                        // Search in post content
                        if (!empty($drink['content']) && stripos($drink['content'], $filter_term) !== false) {
                            return true;
                        }
                        
                        // === FEATURED IMAGE SEARCHES ===
                        
                        // Search in featured image alt text (PRIORITY)
                        if (!empty($drink['thumbnail_alt']) && stripos($drink['thumbnail_alt'], $filter_term) !== false) {
                            return true;
                        }
                        
                        // Search in featured image title
                        if (!empty($drink['thumbnail_title']) && stripos($drink['thumbnail_title'], $filter_term) !== false) {
                            return true;
                        }
                        
                        // Search in featured image caption
                        if (!empty($drink['thumbnail_caption']) && stripos($drink['thumbnail_caption'], $filter_term) !== false) {
                            return true;
                        }
                        
                        // Search in featured image description
                        if (!empty($drink['thumbnail_description']) && stripos($drink['thumbnail_description'], $filter_term) !== false) {
                            return true;
                        }
                        
                        // === POST METADATA SEARCHES ===
                        
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
                        
                        // === FEATURED IMAGE METADATA SEARCHES (EXIF/IPTC) ===
                        
                        if (!empty($drink['thumbnail_id'])) {
                            $image_meta = wp_get_attachment_metadata($drink['thumbnail_id']);
                            if (!empty($image_meta['image_meta'])) {
                                // Search EXIF/IPTC data (keywords, caption, title)
                                $searchable_image_meta = array('title', 'caption', 'keywords');
                                foreach ($searchable_image_meta as $meta_key) {
                                    if (!empty($image_meta['image_meta'][$meta_key])) {
                                        $value = $image_meta['image_meta'][$meta_key];
                                        // Keywords can be an array
                                        if (is_array($value)) {
                                            foreach ($value as $keyword) {
                                                if (stripos($keyword, $filter_term) !== false) {
                                                    return true;
                                                }
                                            }
                                        } else if (stripos($value, $filter_term) !== false) {
                                            return true;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // === TAXONOMY SEARCHES ===
                        
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
                    
                    // For filter mode: use num_slides option (or fewer if fewer matches)
                    $dynamic_slide_count = min($filtered_count, $num_slides);
                    
                    //error_log('Drinks Plugin: MODE 1 - filtered_count = ' . $filtered_count . ', dynamic_slide_count = ' . $dynamic_slide_count);
                    
                    // Add matching drinks only (no random supplement) //
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
                    
                    //error_log('Drinks Plugin: MODE 1 - Actually added ' . count($slideshow_images) . ' slides to carousel');
                }
                // MODE 2: Clicked image first mode
                else if (!empty($match_term)) {
                    //error_log('Drinks Plugin: MODE 2 - Match mode with term: ' . $match_term);
                    //error_log('Drinks Plugin: MODE 2 - Looking for post matching figcaption: ' . $match_term);
                    
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
                            //error_log('Drinks Plugin: Found matching post: ' . $post['title']);
                            unset($drink_posts[$index]); // Remove it from the pool
                            break;
                        }
                    }
                    
                    // Add the clicked image as first slide
                    if ($clicked_post) {
                        //error_log('Drinks Plugin: Adding first slide - ID: ' . $clicked_post['id'] . ', Title: ' . $clicked_post['title']);
                        $slideshow_images[] = array(
                            'id' => $clicked_post['id'],
                            'src' => $clicked_post['thumbnail'],
                            'alt' => $clicked_post['title']
                        );
                        $used_ids[] = $clicked_post['id'];
                        $used_titles[] = $clicked_post['title'];
                        $drink_posts = array_values($drink_posts); // Re-index
                        
                        //error_log('Drinks Plugin: Generating carousel with clicked image first, then ' . ($num_slides - 1) . ' random slides');
                    }
                    
                    // Add random slides to fill remaining slots
                    $add_random_slides($slideshow_images, $used_ids, $used_titles, $drink_posts, $num_slides);
                }
                // MODE 3: Random mode (both figcaption and filter are empty)
                else {
                    //error_log('Drinks Plugin: MODE 3 - Random mode');
                    //error_log('Drinks Plugin: MODE 3 - Generating random carousel with ' . $num_slides . ' slides');
                    
                    // Add random slides
                    $add_random_slides($slideshow_images, $used_ids, $used_titles, $drink_posts, $num_slides);
                }
                
                // Generate the slideshow HTML
                $slides_html = $this->generate_slideshow_slides($slideshow_images, $show_titles, $show_content);
                
                // Debug: Log selected drinks to error log
                $drink_titles = array_map(function($img) { return $img['alt']; }, $slideshow_images);
                //error_log('Drinks Plugin: Selected drinks BEFORE generate_slideshow_slides: ' . json_encode($drink_titles));
                //error_log('Drinks Plugin: Number of drinks selected: ' . count($slideshow_images));
                //error_log('Drinks Plugin: Filter term: "' . $filter_term . '", Filtered count: ' . $filtered_count);
                
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
                
                /* // OLD LOOP LOGIC - COMMENTED OUT (no longer using continuous carousel)
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
                */
                
                // NO LOOP MODE: Generate slides without duplicates
                foreach ($images as $index => $image) {
                    $slides_html .= $this->generate_single_slide($image, $index, false, $show_titles, $show_content);
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
                // Add data-drink-category for layer-2 carousel filtering
                $category_name = $drinks ? $drinks[0]->name : '';
                if (!empty($category_name)) {
                    $html .= 'data-drink-category="' . esc_attr($category_name) . '" ';
                }
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
                
                // Add Drinks Search submenu (documentation viewer)
                add_submenu_page(
                    'drinks-plugin',           // Parent slug
                    'Drinks Search',           // Page title
                    'Drinks Search',           // Menu title
                    'manage_options',          // Capability
                    'drinks-search',           // Menu slug
                    array($this, 'drinks_search_admin_page')
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

<?php
            }
            
            /**
            * Drinks Search admin page callback - displays documentation
            * Consolidated from: modules/drinks-search/drinks-search.php
            */
            public function drinks_search_admin_page() {
                // Load documentation files from the docs folder
                $docs_path = DRINKS_PLUGIN_PATH . 'modules/drinks-search/';
                $readme_file = $docs_path . 'README.md';
                $modes_file = $docs_path . 'MODES-DOCUMENTATION.md';
                $flows_file = $docs_path . 'PROGRAM-FLOWS.md';
                
                $readme_content = file_exists($readme_file) ? file_get_contents($readme_file) : '# README not found';
                $modes_content = file_exists($modes_file) ? file_get_contents($modes_file) : '# MODES-DOCUMENTATION not found';
                $flows_content = file_exists($flows_file) ? file_get_contents($flows_file) : '# PROGRAM-FLOWS not found';
                
                // Convert Markdown to HTML
                $readme_html = $this->markdown_to_html_enhanced($readme_content);
                $modes_html = $this->markdown_to_html_enhanced($modes_content);
                $flows_html = $this->markdown_to_html_enhanced($flows_content);
                
                ?>
                <div class="wrap drinks-search-admin">
                    <style>
                        .drinks-search-admin {
                            max-width: 100%;
                            margin: 20px 20px;
                            padding: 0;
                        }
                        .drinks-search-header {
                            background: #fff;
                            padding: 20px;
                            margin-bottom: 20px;
                            border-bottom: 1px solid #ddd;
                        }
                        .drinks-search-header h1 {
                            margin: 0;
                            color: #2271b1;
                        }
                        .drinks-search-tabs {
                            display: flex;
                            gap: 10px;
                            margin-top: 15px;
                            border-bottom: 2px solid #e0e0e0;
                            flex-wrap: wrap;
                        }
                        .drinks-search-tab {
                            padding: 10px 20px;
                            background: #f5f5f5;
                            border: 1px solid #ddd;
                            border-bottom: none;
                            cursor: pointer;
                            font-weight: 600;
                            color: #135e96;
                            border-radius: 4px 4px 0 0;
                            transition: background 0.2s;
                        }
                        .drinks-search-tab:hover {
                            background: #e8e8e8;
                        }
                        .drinks-search-tab.active {
                            background: #2271b1;
                            color: #fff;
                        }
                        .drinks-search-content-wrapper {
                            display: flex;
                            gap: 15px;
                            min-height: 600px;
                        }
                        .drinks-search-column {
                            flex: 1;
                            background: #fff;
                            padding: 25px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            overflow-y: auto;
                            max-height: calc(100vh - 200px);
                            min-width: 0;
                        }
                        .drinks-search-column h1 { color: #2271b1; margin-top: 0; font-size: 28px; }
                        .drinks-search-column h2 { 
                            color: #2271b1; 
                            margin-top: 30px; 
                            padding-bottom: 10px;
                            border-bottom: 2px solid #e0e0e0;
                        }
                        .drinks-search-column h3 { color: #135e96; margin-top: 25px; }
                        .drinks-search-column code {
                            background: #f5f5f5;
                            padding: 2px 6px;
                            border-radius: 3px;
                            font-family: 'Courier New', monospace;
                            font-size: 13px;
                        }
                        .drinks-search-column pre {
                            background: #f5f5f5;
                            padding: 15px;
                            border-left: 3px solid #2271b1;
                            overflow-x: auto;
                            border-radius: 4px;
                            margin: 15px 0;
                        }
                        .drinks-search-column pre code {
                            background: transparent;
                            padding: 0;
                        }
                        .drinks-search-column ul, .drinks-search-column ol {
                            margin-left: 20px;
                            line-height: 1.8;
                        }
                        .drinks-search-column li {
                            margin-bottom: 8px;
                        }
                        .drinks-search-column strong {
                            color: #135e96;
                        }
                        .drinks-search-column hr {
                            border: none;
                            border-top: 1px solid #e0e0e0;
                            margin: 30px 0;
                        }
                        .drinks-search-column .status-badge {
                            display: inline-block;
                            background: #00a32a;
                            color: white;
                            padding: 4px 12px;
                            border-radius: 3px;
                            font-size: 12px;
                            font-weight: bold;
                            margin-left: 10px;
                        }
                        .drinks-search-column blockquote {
                            border-left: 4px solid #2271b1;
                            margin: 20px 0;
                            padding-left: 20px;
                            color: #666;
                            font-style: italic;
                        }
                        .drinks-search-column table {
                            border-collapse: collapse;
                            width: 100%;
                            margin: 20px 0;
                        }
                        .drinks-search-column table th,
                        .drinks-search-column table td {
                            border: 1px solid #ddd;
                            padding: 10px;
                            text-align: left;
                        }
                        .drinks-search-column table th {
                            background: #f5f5f5;
                            font-weight: bold;
                            color: #135e96;
                        }
                        .drinks-search-column table tr:hover {
                            background: #f9f9f9;
                        }
                        .drinks-search-content-wrapper.tab-view {
                            display: block;
                        }
                        .drinks-search-content-wrapper.tab-view .drinks-search-column {
                            display: none;
                        }
                        .drinks-search-content-wrapper.tab-view .drinks-search-column.active {
                            display: block;
                            max-width: 1400px;
                            margin: 0 auto;
                        }
                    </style>
                    
                    <div class="drinks-search-header">
                        <h1>🔍 Drinks Search - Documentation</h1>
                        <div class="drinks-search-tabs">
                            <div class="drinks-search-tab active" onclick="switchView('all')">All Columns</div>
                            <div class="drinks-search-tab" onclick="switchView('flows')">Program Flows</div>
                            <div class="drinks-search-tab" onclick="switchView('readme')">README</div>
                            <div class="drinks-search-tab" onclick="switchView('modes')">MODES</div>
                        </div>
                    </div>
                    
                    <div class="drinks-search-content-wrapper" id="content-wrapper">
                        <div class="drinks-search-column flows-column active" id="flows-column">
                            <?php echo $flows_html; ?>
                        </div>
                        <div class="drinks-search-column readme-column active" id="readme-column">
                            <?php echo $readme_html; ?>
                        </div>
                        <div class="drinks-search-column modes-column active" id="modes-column">
                            <?php echo $modes_html; ?>
                        </div>
                    </div>
                    
                    <script>
                        function switchView(view) {
                            const wrapper = document.getElementById('content-wrapper');
                            const flowsCol = document.getElementById('flows-column');
                            const readmeCol = document.getElementById('readme-column');
                            const modesCol = document.getElementById('modes-column');
                            const tabs = document.querySelectorAll('.drinks-search-tab');
                            
                            tabs.forEach(tab => tab.classList.remove('active'));
                            
                            if (view === 'all') {
                                wrapper.classList.remove('tab-view');
                                flowsCol.classList.add('active');
                                readmeCol.classList.add('active');
                                modesCol.classList.add('active');
                                tabs[0].classList.add('active');
                            } else if (view === 'flows') {
                                wrapper.classList.add('tab-view');
                                flowsCol.classList.add('active');
                                readmeCol.classList.remove('active');
                                modesCol.classList.remove('active');
                                tabs[1].classList.add('active');
                            } else if (view === 'readme') {
                                wrapper.classList.add('tab-view');
                                flowsCol.classList.remove('active');
                                readmeCol.classList.add('active');
                                modesCol.classList.remove('active');
                                tabs[2].classList.add('active');
                            } else if (view === 'modes') {
                                wrapper.classList.add('tab-view');
                                flowsCol.classList.remove('active');
                                readmeCol.classList.remove('active');
                                modesCol.classList.add('active');
                                tabs[3].classList.add('active');
                            }
                        }
                    </script>
                </div>
                <?php
            }
            
            /**
            * Convert markdown to HTML (basic conversion for main admin page)
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
            * Enhanced markdown to HTML converter (with tables support)
            * Used for Drinks Search documentation pages
            */
            private function markdown_to_html_enhanced($markdown) {
                // Escape HTML
                $html = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8');
                
                // Code blocks (triple backticks)
                $html = preg_replace_callback('/```(\w+)?\n(.*?)\n```/s', function($matches) {
                    $lang = $matches[1] ? ' class="language-' . $matches[1] . '"' : '';
                    $code = $matches[2];
                    return '<pre><code' . $lang . '>' . $code . '</code></pre>';
                }, $html);
                
                // Inline code
                $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
                
                // Tables - process before headers to avoid conflicts
                $html = preg_replace_callback('/\n(\|.+\|)\n(\|[-:\s|]+\|)\n((?:\|.+\|\n?)+)/m', function($matches) {
                    $header_row = $matches[1];
                    $body_rows = $matches[3];
                    
                    $headers = array_map('trim', explode('|', trim($header_row, '|')));
                    $table = '<table><thead><tr>';
                    foreach ($headers as $header) {
                        $table .= '<th>' . trim($header) . '</th>';
                    }
                    $table .= '</tr></thead><tbody>';
                    
                    $rows = explode("\n", trim($body_rows));
                    foreach ($rows as $row) {
                        if (empty(trim($row))) continue;
                        $cells = array_map('trim', explode('|', trim($row, '|')));
                        $table .= '<tr>';
                        foreach ($cells as $cell) {
                            $table .= '<td>' . trim($cell) . '</td>';
                        }
                        $table .= '</tr>';
                    }
                    
                    $table .= '</tbody></table>';
                    return "\n" . $table . "\n";
                }, $html);
                
                // Headers
                $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
                $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
                $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
                
                // Bold
                $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
                
                // Horizontal rules
                $html = preg_replace('/^---+$/m', '<hr>', $html);
                
                // Lists
                $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
                $html = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $html);
                
                // Wrap lists
                $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
                $html = preg_replace('/<\/ul>\s*<ul>/s', '', $html);
                
                // Links
                $html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $html);
                
                // Paragraphs
                $html = preg_replace('/\n\n/', '</p><p>', $html);
                $html = '<p>' . $html . '</p>';
                
                // Clean up
                $html = preg_replace('/<p>\s*<\/p>/', '', $html);
                $html = preg_replace('/<p>\s*(<h[123]|<hr|<pre|<ul|<table)/', '$1', $html);
                $html = preg_replace('/(<\/h[123]>|<\/hr>|<\/pre>|<\/ul>|<\/table>)\s*<\/p>/', '$1', $html);
                
                // Status badges
                $html = preg_replace('/✅/', '<span class="status-badge">✅</span>', $html);
                
                return $html;
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
                <!-- data-autoplay="false" is WRONG - causes autplay  -->
                <div class="wp-block-jetpack-slideshow aligncenter" data-autoplay="" data-delay="3" data-effect="slide">
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
                <!--                             <a aria-label="Pause Slideshow" class="wp-block-jetpack-slideshow_button-pause" role="button"></a>
                -->                            <div class="wp-block-jetpack-slideshow_pagination swiper-pagination swiper-pagination-white swiper-pagination-custom"></div>
                </div>
                </div>
                </div>
                <!-- See More button -->
                <button type="button" class="drinks-carousel-see-more" aria-label="See more results">See More</button>
                </div>
                </div>
                <?php
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
                    //error_log('Drinks Plugin Sync Error: ' . $e->getMessage());
                    //error_log('Drinks Plugin Sync Error Trace: ' . $e->getTraceAsString());
                    wp_send_json_error(array(
                        'message' => 'Error running sync: ' . $e->getMessage()
                    ));
                }
            }
            
        }
        
        // Initialize the plugin and expose global accessor
        global $drinks_plugin;
        $drinks_plugin = new DrinksPlugin();