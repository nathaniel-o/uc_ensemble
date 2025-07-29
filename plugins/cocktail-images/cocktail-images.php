<?php
/**
 * Plugin Name: Cocktail Images
 * Plugin URI: 
 * Description: A plugin for managing cocktail images
 * Version: 1.0.0
 * Author: 
 * License: GPL v2 or later
 * Text Domain: cocktail-images
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('COCKTAIL_IMAGES_VERSION', '1.0.0');
define('COCKTAIL_IMAGES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COCKTAIL_IMAGES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include functions file
require_once COCKTAIL_IMAGES_PLUGIN_DIR . 'includes/functions.php';

/**
 * Main plugin class
 */
class Cocktail_Images_Plugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add AJAX handlers
        add_action('wp_ajax_filter_carousel', array($this, 'handle_filter_carousel'));
        add_action('wp_ajax_nopriv_filter_carousel', array($this, 'handle_filter_carousel'));
        add_action('wp_ajax_randomize_image', array($this, 'handle_randomize_image'));
        add_action('wp_ajax_nopriv_randomize_image', array($this, 'handle_randomize_image'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Plugin initialization code will go here
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'cocktail-images-js',
            COCKTAIL_IMAGES_PLUGIN_URL . 'assets/js/cocktail-images.js',
            array(),
            COCKTAIL_IMAGES_VERSION,
            true
        );
        
        // Localize script with AJAX URL
        wp_localize_script('cocktail-images-js', 'cocktailImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cocktail_images_nonce')
        ));
        
        // Add DOM content loaded call for image randomization
        add_action('wp_head', array($this, 'add_dom_content_loaded_call'));
    }
    
    /**
     * Add DOM content loaded call for image randomization
     */
    public function add_dom_content_loaded_call() {
        // Check if the dom_content_loaded function exists in the theme
        if (function_exists('dom_content_loaded')) {
           # echo dom_content_loaded('ucSetupImageRandomization;', 0, 0);
            echo dom_content_loaded('ucOneDrinkAllImages;', 0, 0);
        } /* else {
            // Fallback if theme function doesn't exist
            echo '<script>document.addEventListener("DOMContentLoaded", function() { ucSetupImageRandomization(); });</script>';
        } */
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Cocktail Images',
            'Cocktail Images',
            'manage_options',
            'cocktail-images',
            array($this, 'admin_page'),
            'dashicons-format-image',
            30
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Cocktail Images Plugin</h1>
            
            <div class="card">
                <h2>Description</h2>
                <p>This plugin provides comprehensive functionality for managing and displaying cocktail images on your WordPress site. It includes:</p>
                <ul>
                    <li><strong>Image Randomization</strong>: AJAX-powered image randomization with category filtering</li>
                    <li><strong>Carousel Generation</strong>: Dynamic carousel creation with random and filtered drink images</li>
                    <li><strong>Drink Post Management</strong>: Functions to query and manage drink posts with taxonomy</li>
                    <li><strong>Metadata Generation</strong>: Automatic generation of drink metadata lists</li>
                    <li><strong>Slideshow Support</strong>: Complete slideshow functionality with infinite loops</li>
                    <li><strong>Backward Compatibility</strong>: Global functions for seamless integration with existing themes</li>
                </ul>
            </div>

            <div class="card">
                <h2>Features</h2>
                <h3>AJAX Handlers</h3>
                <ul>
                    <li><code>filter_carousel</code> - Filter carousel by search terms</li>
                    <li><code>randomize_image</code> - Randomize images with category filtering</li>
                </ul>
                
                <h3>Carousel Functions</h3>
                <ul>
                    <li><code>uc_random_carousel()</code> - Generate random carousel slides</li>
                    <li><code>uc_filter_carousel()</code> - Generate filtered carousel slides</li>
                    <li><code>generate_slideshow_slides()</code> - Generate slideshow HTML</li>
                </ul>
                
                <h3>Drink Management</h3>
                <ul>
                    <li><code>uc_get_drinks()</code> - Retrieve drink posts from database</li>
                    <li><code>uc_drink_query()</code> - Query drink posts with taxonomy</li>
                </ul>
                
                <h3>Metadata Functions</h3>
                <ul>
                    <li><code>uc_generate_metadata_list()</code> - Generate drink metadata</li>
                    <li><code>uc_update_all_drink_excerpts()</code> - Update drink excerpts</li>
                    <li><code>uc_clear_all_drink_excerpts()</code> - Clear drink excerpts</li>
                </ul>
            </div>

            <div class="card">
                <h2>Usage</h2>
                <p>The plugin provides both object-oriented methods and global functions for backward compatibility:</p>
                
                <h3>Using Global Functions (Backward Compatible)</h3>
                <pre><code>$drink_posts = uc_get_drinks();
$carousel = uc_random_carousel($drink_posts, 5, 0, 1);</code></pre>
                
                <h3>Using Plugin Instance</h3>
                <pre><code>$plugin = get_cocktail_images_plugin();
$drink_posts = $plugin->uc_get_drinks();
$carousel = $plugin->uc_random_carousel($drink_posts, 5, 0, 1);</code></pre>
            </div>

            <div class="card">
                <h2>AJAX Endpoints</h2>
                <ul>
                    <li><code>wp_ajax_filter_carousel</code> / <code>wp_ajax_nopriv_filter_carousel</code></li>
                    <li><code>wp_ajax_randomize_image</code> / <code>wp_ajax_nopriv_randomize_image</code></li>
                </ul>
            </div>

            <div class="card">
                <h2>Dependencies</h2>
                <ul>
                    <li>WordPress 5.0+</li>
                    <li>PHP 7.4+</li>
                    <li>'drinks' taxonomy must be registered</li>
                </ul>
            </div>

            <div class="card">
                <h2>Plugin Information</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Version</th>
                        <td>1.0.0</td>
                    </tr>
                    <tr>
                        <th scope="row">License</th>
                        <td>GPL v2 or later</td>
                    </tr>
                    <tr>
                        <th scope="row">Text Domain</th>
                        <td>cocktail-images</td>
                    </tr>
                </table>
            </div>

            <style>
                .card {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    border-radius: 4px;
                    padding: 20px;
                    margin: 20px 0;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                .card h2 {
                    margin-top: 0;
                    color: #23282d;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }
                .card h3 {
                    color: #23282d;
                    margin-top: 20px;
                }
                .card ul {
                    margin-left: 20px;
                }
                .card pre {
                    background: #f1f1f1;
                    padding: 10px;
                    border-radius: 3px;
                    overflow-x: auto;
                }
                .card code {
                    background: #f1f1f1;
                    padding: 2px 4px;
                    border-radius: 3px;
                }
                .form-table th {
                    width: 150px;
                }
            </style>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for filter_carousel
     */
    public function handle_filter_carousel() {
        $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        
        // Get drink posts
        $drink_posts = $this->uc_get_drinks();
         
        // Generate carousel HTML
        $filtered_carousel = $this->uc_filter_carousel($search_term, $drink_posts, 5, 0, 1, 1);
        echo $filtered_carousel;

        wp_die(); // Required for proper AJAX response
    }
    
    /**
     * AJAX handler for image randomization
     */
    public function handle_randomize_image() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cocktail_images_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        $current_id = isset($_POST['current_id']) ? sanitize_text_field($_POST['current_id']) : '';
        error_log('Current ID: ' . $current_id);
        
        // Define category IDs
        $category_ids = array('AU', 'SO', 'SU', 'SP', 'FP', 'EV', 'RO', 'WI');
        
        // Parse current image title for category ID
        $current_category = '';
        if (!empty($current_id)) {
            $current_attachment = get_post($current_id);
            if ($current_attachment) {
                $current_title = $current_attachment->post_title;
                foreach ($category_ids as $id) {
                    if (strpos($current_title, $id) !== false) {
                        $current_category = $id;
                        break;
                    }
                }
            }
        }
        
        // Build query args
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'orderby' => 'rand',
            'exclude' => array($current_id) // Exclude current image if provided
        );
        
        // If we found a category, filter by it
        if (!empty($current_category)) {
            // Get all images and filter by title content
            $all_attachments = get_posts(array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'posts_per_page' => -1,
                'exclude' => array($current_id)
            ));
            
            // Filter attachments that contain the category ID
            $filtered_attachments = array();
            foreach ($all_attachments as $attachment) {
                if (strpos($attachment->post_title, $current_category) !== false) {
                    $filtered_attachments[] = $attachment;
                }
            }
            
            if (!empty($filtered_attachments)) {
                $random_attachment = array($filtered_attachments[array_rand($filtered_attachments)]);
            } else {
                $random_attachment = get_posts($args);
            }
        } else {
            $random_attachment = get_posts($args);
        }
        
        if (empty($random_attachment)) {
            wp_send_json_error(array('message' => 'No images found in Media Library'));
            return;
        }
        
        $attachment = $random_attachment[0];
        $attachment_id = $attachment->ID;
        
        // Get attachment data
        $attachment_data = wp_get_attachment_image_src($attachment_id, 'large');
        $attachment_metadata = wp_get_attachment_metadata($attachment_id);
        
        // Get responsive image data
        $image_srcset = wp_get_attachment_image_srcset($attachment_id);
        $image_sizes = wp_get_attachment_image_sizes($attachment_id);
        
        // Get caption and format it properly
        $caption = wp_get_attachment_caption($attachment_id);
        if (empty($caption)) {
            // If no caption, use the attachment title
            $caption = $attachment->post_title;
            // Normalize the title by:
            // 1. Replace hyphens and underscores with spaces
            // 2. Remove numbers and special characters
            // 3. Remove file extensions
            // 4. Clean up extra spaces
            // 5. Remove 'T2' anywhere it exists
            // 6. Remove anything after underscore
            $normalized_title = $attachment->post_title;
            $normalized_title = preg_replace('/_\w+.*$/', '', $normalized_title); // Remove anything after underscore
            $normalized_title = str_replace(['-', '_'], ' ', $normalized_title); // Replace - and _ with space
            $normalized_title = preg_replace('/[\d\-_]+$/', '', $normalized_title); // Remove trailing numbers and separators
            $normalized_title = preg_replace('/\.\w+$/', '', $normalized_title); // Remove file extension
            $normalized_title = preg_replace('/\s+/', ' ', $normalized_title); // Clean up multiple spaces
            $normalized_title = str_replace('T2', '', $normalized_title); // Remove 'T2' anywhere
            $normalized_title = preg_replace('/\s+/', ' ', $normalized_title); // Clean up multiple spaces again
            $normalized_title = trim($normalized_title); // Remove leading/trailing spaces
            
            $caption = $normalized_title;
        }
        
        // Prepare the response with all WordPress image attributes
        $image_data = array(
            'id' => $attachment_id,
            'title' => $attachment->post_title,
            'src' => $attachment_data[0],
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: $attachment->post_title,
            'attachment_id' => $attachment_id,
            'srcset' => $image_srcset,
            'sizes' => $image_sizes,
            'data_orig_file' => $attachment_data[0],
            'data_orig_size' => $attachment_data[1] . ',' . $attachment_data[2],
            'data_image_title' => $attachment->post_title,
            'data_image_caption' => $caption,
            'data_medium_file' => wp_get_attachment_image_url($attachment_id, 'medium'),
            'data_large_file' => wp_get_attachment_image_url($attachment_id, 'large')
        );
        
        wp_send_json_success(array('image' => $image_data));
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
        //error_log('Starting uc_random_carousel');
        //error_log('Requested slides: ' . $num_slides);
        //error_log('Available posts: ' . count($drink_posts));

        $slideshow_images = array();
        $used_ids = array();

        // Keep trying until we have the requested number of slides
        while (count($slideshow_images) < $num_slides) {
            if (empty($drink_posts)) {
                //error_log('No more available posts to select from');
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
                //error_log('Added slide ' . count($slideshow_images) . ': ' . $random_drink['title']);
                
                // Remove used drink from available pool
                unset($drink_posts[$random_index]);
                $drink_posts = array_values($drink_posts);
            }
        }

        //error_log('Final number of slides: ' . count($slideshow_images));
        return $this->generate_slideshow_slides($slideshow_images, $show_titles, $show_content);
    }
    
    /**
     * An Copy of uc_random_carousel, returns <li><figure>..etc Slides Content via generate_slideshow_slides
     */
    public function uc_filter_carousel($srchStr, $drink_posts, $num_slides, $show_titles = 0, $show_content = 0, $supp_rand = 0) {
        //error_log('Starting uc_filter_carousel');
        //error_log('Search term: ' . $srchStr);
        //error_log('Requested slides: ' . $num_slides);
        //error_log('Available posts: ' . count($drink_posts));

        // Filter drinks matching search string
        $filtered_drinks = array_filter($drink_posts, function($drink) use ($srchStr) {
            return stripos($drink['title'], $srchStr) !== false;
        });
        $filtered_drinks = array_values($filtered_drinks);
        
        //error_log('Matching posts found: ' . count($filtered_drinks));

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
                //error_log('Added matching slide ' . count($slideshow_images) . ': ' . $random_drink['title']);
                
                unset($filtered_drinks[$random_index]);
                $filtered_drinks = array_values($filtered_drinks);
            }
        }

        // If supp_rand is true and we need more slides, add random ones
        if ($supp_rand && count($slideshow_images) < $num_slides) {
            //error_log('Supplementing with random slides. Current count: ' . count($slideshow_images));
            
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
                    //error_log('Added random slide ' . count($slideshow_images) . ': ' . $random_drink['title']);
                    
                    unset($drink_posts[$random_index]);
                    $drink_posts = array_values($drink_posts);
                }
            }
        }

        //error_log('Final number of slides: ' . count($slideshow_images));
        return $this->generate_slideshow_slides($slideshow_images, $show_titles, $show_content);
    }
    
    /**
     * Function to generate slideshow HTML, optional param for more data 
     */
    public function generate_slideshow_slides($images, $show_titles = 0, $show_content = 0) {
        $slides_html = '';
        $total_slides = count($images);
        
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
        $html .= '<figure>';
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
    
    
}

// Initialize the plugin
global $cocktail_images_plugin;
$cocktail_images_plugin = new Cocktail_Images_Plugin(); 