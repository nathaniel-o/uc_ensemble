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
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add block editor integration for image block inspector controls
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('enqueue_block_assets', array($this, 'enqueue_block_assets'));
        
        // Add AJAX handlers
        add_action('wp_ajax_filter_carousel', array($this, 'handle_filter_carousel'));
        add_action('wp_ajax_nopriv_filter_carousel', array($this, 'handle_filter_carousel'));
        add_action('wp_ajax_randomize_image', array($this, 'handle_randomize_image'));
        add_action('wp_ajax_nopriv_randomize_image', array($this, 'handle_randomize_image'));
        add_action('wp_ajax_find_matching_image', array($this, 'handle_find_matching_image'));
        add_action('wp_ajax_nopriv_find_matching_image', array($this, 'handle_find_matching_image'));
        add_action('wp_ajax_check_featured_image', array($this, 'handle_check_featured_image'));
        add_action('wp_ajax_nopriv_check_featured_image', array($this, 'handle_check_featured_image'));
        add_action('wp_ajax_run_media_library_analysis', array($this, 'handle_run_media_library_analysis'));
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
        // Frontend: enqueue frontend-only bundle
        wp_enqueue_script(
            'cocktail-images-frontend',
            COCKTAIL_IMAGES_PLUGIN_URL . 'dist/frontend.js',
            array(),
            COCKTAIL_IMAGES_VERSION,
            true
        );

        // Localize script with AJAX URL
        wp_localize_script('cocktail-images-frontend', 'cocktailImagesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cocktail_images_nonce')
        ));
        
        /**
         * Add DOM content loaded call for image randomization
         * Echoes a <script>
         * Requires dom_content_loaded fn in theme's functions.php
        */
        add_action('wp_footer', function() {
            if (function_exists('dom_content_loaded')) {
                echo dom_content_loaded('ucOneDrinkAllImages;', 0, 0);
            }
        });
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'cocktail-images-block-editor-js',
            COCKTAIL_IMAGES_PLUGIN_URL . 'dist/block-editor.js',
            array(
                'wp-blocks',
                'wp-element',
                'wp-block-editor',
                'wp-components',
                'wp-i18n',
                'wp-hooks',
                'wp-compose'
            ),
            COCKTAIL_IMAGES_VERSION,
            true
        );
    }
    
    /**
     * Enqueue block assets (CSS for iframe)
     */
    public function enqueue_block_assets() {
        wp_enqueue_style(
            'cocktail-images-block-editor-css',
            COCKTAIL_IMAGES_PLUGIN_URL . 'assets/css/cocktail-images-block-editor.css',
            array(),
            COCKTAIL_IMAGES_VERSION
        );
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
                <p>This plugin provides comprehensive functionality for managing and displaying cocktail images on your WordPress site with intelligent title matching and image cycling. It includes:</p>
                <ul>
                    <li><strong>Intelligent Image Matching</strong>: Advanced title normalization and exact matching for finding related images</li>
                    <li><strong>Image Cycling</strong>: Click-to-cycle through images with matching titles</li>
                    <li><strong>Featured Image Detection</strong>: Check if images are featured in posts</li>
                    <li><strong>Title Normalization</strong>: Consistent title processing across JavaScript and PHP</li>
                    <li><strong>Carousel Generation</strong>: Dynamic carousel creation with random and filtered drink images</li>
                    <li><strong>Drink Post Management</strong>: Functions to query and manage drink posts with taxonomy</li>
                    <li><strong>Metadata Generation</strong>: Automatic generation of drink metadata lists</li>
                    <li><strong>Slideshow Support</strong>: Complete slideshow functionality with infinite loops</li>
                </ul>
            </div>

            <div class="card">
                <h2>Features</h2>
                
                <h3>AJAX Handlers</h3>
                <ul>
                    <li><code>filter_carousel</code> - Filter carousel by search terms</li>
                    <li><code>randomize_image</code> - Randomize images with category filtering</li>
                    <li><code>find_matching_image</code> - Find images with matching titles (exact match)</li>
                    <li><code>check_featured_image</code> - Check if an image is featured in any post</li>
                </ul>
                
                <h3>Image Matching & Cycling</h3>
                <ul>
                    <li><strong>Title Normalization</strong>: Consistent processing across JS and PHP
                        <ul>
                            <li>Truncates at colon (<code>:</code>)</li>
                            <li>Removes T2- prefix</li>
                            <li>Replaces hyphens/underscores with spaces</li>
                            <li>Filters out words &lt;3 letters</li>
                            <li>Case-insensitive matching</li>
                        </ul>
                    </li>
                    <li><strong>Exact Matching</strong>: Finds images with identical normalized titles</li>
                    <li><strong>Image Cycling</strong>: Click images to cycle through matching alternatives</li>
                    <li><strong>Featured Image Detection</strong>: Identifies images used as post featured images</li>
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
                <p>The plugin provides object-oriented methods for all functionality:</p>
                
                <h3>Using Plugin Instance</h3>
                <pre><code>$plugin = get_cocktail_images_plugin();
$drink_posts = $plugin->uc_get_drinks();
$carousel = $plugin->uc_random_carousel($drink_posts, 5, 0, 1);</code></pre>
                
                <h3>JavaScript Functions</h3>
                <ul>
                    <li><code>ucOneDrinkAllImages()</code> - Main function for image cycling</li>
                    <li><code>ucNormalizeTitle()</code> - Title normalization helper</li>
                    <li><code>ucDoesImageHavePost()</code> - Check if image is featured in posts</li>
                    <li><code>ucSetupOneDrinkAllImages()</code> - Setup automatic image cycling</li>
                </ul>
            </div>

            <div class="card">
                <h2>Title Matching Logic</h2>
                <p>The plugin uses title matching to find related images:</p>
                <ol>
                    <li><strong>Normalization</strong>: Both JavaScript and PHP normalize titles identically</li>
                    <li><strong>Word Filtering</strong>: Removes words shorter than 3 characters</li>
                    <li><strong>Exact Matching</strong>: Finds images with identical normalized titles</li>
                    <li><strong>Case Insensitive</strong>: Matching works regardless of capitalization</li>
                </ol>
                
                <h3>Example</h3>
                <ul>
                    <li><strong>Original</strong>: "Cherry-Gin-and-Tonic_RO-T-2: Bright red cocktail..."</li>
                    <li><strong>Normalized</strong>: "cherry gin tonic" (removes short words, hyphens, etc.)</li>
                    <li><strong>Matches</strong>: Only images with exactly "cherry gin tonic" as normalized title</li>
                </ul>
            </div>

            <div class="card">
                <h2>AJAX Endpoints</h2>
                <ul>
                    <li><code>wp_ajax_filter_carousel</code> / <code>wp_ajax_nopriv_filter_carousel</code></li>
                    <li><code>wp_ajax_randomize_image</code> / <code>wp_ajax_nopriv_randomize_image</code></li>
                    <li><code>wp_ajax_find_matching_image</code> / <code>wp_ajax_nopriv_find_matching_image</code></li>
                    <li><code>wp_ajax_check_featured_image</code> / <code>wp_ajax_nopriv_check_featured_image</code></li>
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
                <h2>Media Library Analysis</h2>
                <p>Analyze your media library to see how well images match with posts using the <code>ucDoesImageHavePost</code> logic.</p>
                
                <div class="media-library-checker-section">
                    <button type="button" id="run-media-analysis" class="button button-primary">
                        <span class="dashicons dashicons-chart-bar"></span>
                        Run Media Library Analysis
                    </button>

                    
                    <button type="button" id="view-results" class="button button-secondary" style="margin-left: 10px; display: none;">
                        <span class="dashicons dashicons-external"></span>
                        View Results
                    </button>
                    
                    <!--div id="analysis-status" style="display: none; margin-top: 10px;">
                        <div class="notice notice-info">
                            <p><strong>Analysis in progress...</strong> This may take a few moments depending on your media library size.</p>
                        </div>
                    </div-->
                    
                    <!--div id="analysis-results" style="display: none; margin-top: 15px;">
                        <div class="notice notice-success">
                            <p><strong>Analysis complete!</strong> <a href="#" id="view-results-link" target="_blank">View detailed results in new tab</a></p>
                        </div>
                    </div-->
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    var resultsUrl = null;
                    
                    $('#run-media-analysis').on('click', function() {
                        var button = $(this);
                        var originalText = button.html();
                        
                        // Disable button and show loading
                        button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spinning"></span> Running Analysis...');
                        $('#analysis-results').hide();
                        $('#view-results').hide();
                        
                        // Make AJAX request to run analysis
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'run_media_library_analysis',
                                nonce: '<?php echo wp_create_nonce("media_library_analysis_nonce"); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    resultsUrl = response.data.results_url;
                                    $('#analysis-results').show();
                                    $('#view-results').show();
                                } else {
                                    alert('Error: ' + (response.data.message || 'Unknown error occurred'));
                                }
                            },
                            error: function() {
                                alert('Error: Failed to run analysis. Please try again.');
                            },
                            complete: function() {
                                // Re-enable button
                                button.prop('disabled', false).html(originalText);
                            }
                        });
                    });
                    
                    $('#view-results').on('click', function() {
                        if (resultsUrl) {
                            window.open(resultsUrl, '_blank');
                        } else {
                            alert('No results available. Please run the analysis first.');
                        }
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
                .media-library-checker-section {
                    padding: 15px 0;
                }
                </style>
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
     * AJAX handler for finding matching images by title
     */
    public function handle_find_matching_image() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cocktail_images_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        $current_id = isset($_POST['current_id']) ? sanitize_text_field($_POST['current_id']) : '';
        $base_title = isset($_POST['base_title']) ? sanitize_text_field($_POST['base_title']) : '';
        $current_index = isset($_POST['current_index']) ? intval($_POST['current_index']) : 0;
        $is_new_search = isset($_POST['is_new_search']) ? (bool)$_POST['is_new_search'] : false;
        
        error_log('Finding matching images for base title: ' . $base_title . ' (index: ' . $current_index . ', new search: ' . ($is_new_search ? 'yes' : 'no') . ')');
        
        if (empty($base_title)) {
            wp_send_json_error(array('message' => 'No base title provided'));
            return;
        }
        
        // Get all image attachments
        $all_attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'exclude' => array($current_id)
        ));
        
        // Find matching images based on exact title match only (case insensitive)
        $matching_attachments = array();
        
        foreach ($all_attachments as $attachment) {
            $attachment_title = $attachment->post_title;
            
            // Normalize attachment title for comparison
            $normalized_attachment_title = $this->normalize_title_for_matching($attachment_title);
            $normalized_base_title = $this->normalize_title_for_matching($base_title);
            
            // Check for exact match only (case-insensitive)
            if (strcasecmp($normalized_attachment_title, $normalized_base_title) === 0) {
                $matching_attachments[] = $attachment;
            }
        }
        
        error_log('Found ' . count($matching_attachments) . ' exact matches for: ' . $normalized_base_title);
        
        if (empty($matching_attachments)) {
            wp_send_json_error(array('message' => 'No matching images found'));
            return;
        }
        
        // Log match count and URLs
        $match_urls = array();
        foreach ($matching_attachments as $match) {
            $match_urls[] = basename($match->post_title);
        }
        error_log('Found ' . count($matching_attachments) . ' matches: ' . implode(', ', $match_urls));
        
        // If it's a new search, return all matches
        if ($is_new_search) {
            $all_matches = array();
            foreach ($matching_attachments as $attachment) {
                $attachment_id = $attachment->ID;
                $attachment_data = wp_get_attachment_image_src($attachment_id, 'large');
                $image_srcset = wp_get_attachment_image_srcset($attachment_id);
                $image_sizes = wp_get_attachment_image_sizes($attachment_id);
                
                // Get caption and format it properly
                $caption = wp_get_attachment_caption($attachment_id);
                if (empty($caption)) {
                    $caption = $attachment->post_title;
                    $normalized_title = $this->normalize_title_for_matching($attachment->post_title);
                    $caption = $normalized_title;
                }
                
                $all_matches[] = array(
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
            }
            
            wp_send_json_success(array(
                'all_matches' => $all_matches,
                'total_matches' => count($all_matches)
            ));
            return;
        }
        
        // For cycling through cached matches, return just the current match
        $total_matches = count($matching_attachments);
        $next_index = $current_index % $total_matches; // Cycle through matches
        $attachment = $matching_attachments[$next_index];
        $attachment_id = $attachment->ID;
        
        // Get attachment data
        $attachment_data = wp_get_attachment_image_src($attachment_id, 'large');
        $image_srcset = wp_get_attachment_image_srcset($attachment_id);
        $image_sizes = wp_get_attachment_image_sizes($attachment_id);
        
        // Get caption and format it properly
        $caption = wp_get_attachment_caption($attachment_id);
        if (empty($caption)) {
            $caption = $attachment->post_title;
            $normalized_title = $this->normalize_title_for_matching($attachment->post_title);
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
            'data_large_file' => wp_get_attachment_image_url($attachment_id, 'large'),
            'total_matches' => $total_matches,
            'current_index' => $next_index,
            'next_index' => ($next_index + 1) % $total_matches
        );
        
        wp_send_json_success(array('image' => $image_data));
    }
    
    /**
     * AJAX handler for finding posts that match the image title
     */
    public function handle_check_featured_image() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cocktail_images_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        $image_title = isset($_POST['image_title']) ? sanitize_text_field($_POST['image_title']) : '';
        
        if (empty($image_title)) {
            wp_send_json_error(array('message' => 'No image title provided'));
            return;
        }
        
        // Normalize the image title
        $normalized_image_title = $this->normalize_title_for_matching($image_title);
        
        error_log('Searching for posts matching image title: ' . $normalized_image_title);
        
        // Search for posts with matching titles
        $matching_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 5, // Limit to 5 matches
            's' => $normalized_image_title // Search in title and content
        ));
        
        // If no posts found with search, try exact title matching
        if (empty($matching_posts)) {
            $matching_posts = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 5,
                'title' => $normalized_image_title
            ));
        }
        
        // If still no matches, try partial matching
        if (empty($matching_posts)) {
            $words = explode(' ', $normalized_image_title);
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $partial_matches = get_posts(array(
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'posts_per_page' => 3,
                        's' => $word
                    ));
                    $matching_posts = array_merge($matching_posts, $partial_matches);
                }
            }
            // Remove duplicates
            $matching_posts = array_unique($matching_posts, SORT_REGULAR);
        }
        
        if (!empty($matching_posts)) {
            // Look for exact title match first
            $exact_match = null;
            $other_matches = array();
            
            foreach ($matching_posts as $post) {
                if (strcasecmp($post->post_title, $normalized_image_title) === 0) {
                    $exact_match = $post;
                    break;
                } elseif ($this->is_word_contained_match($post->post_title, $normalized_image_title)) {
                    $exact_match = $post;
                    break;
                } else {
                    $other_matches[] = $post;
                }
            }
            
            // If we found an exact match, return only that
            if ($exact_match) {
                wp_send_json_success(array(
                    'post_id' => $exact_match->ID,
                    'post_title' => $exact_match->post_title,
                    'image_title' => $image_title,
                    'normalized_image_title' => $normalized_image_title,
                    'total_matches' => count($matching_posts),
                    'exact_match' => true,
                    'other_matches' => array_map(function($post) {
                        return array(
                            'id' => $post->ID,
                            'title' => $post->post_title,
                            'url' => get_permalink($post->ID)
                        );
                    }, $other_matches)
                ));
            } else {
                // No exact match found - return all matches as other_matches
                wp_send_json_success(array(
                    'post_id' => null,
                    'post_title' => null,
                    'image_title' => $image_title,
                    'normalized_image_title' => $normalized_image_title,
                    'total_matches' => count($matching_posts),
                    'exact_match' => false,
                    'other_matches' => array_map(function($post) {
                        return array(
                            'id' => $post->ID,
                            'title' => $post->post_title,
                            'url' => get_permalink($post->ID)
                        );
                    }, $matching_posts)
                ));
            }
        } else {
            wp_send_json_success(array(
                'post_id' => null,
                'image_title' => $image_title,
                'normalized_image_title' => $normalized_image_title,
                'total_matches' => 0,
                'exact_match' => false,
                'message' => 'No matching posts found'
            ));
        }
    }
    
    /**
     * Check if all words from normalized title are contained in post title
     */
    private function is_word_contained_match($post_title, $normalized_title) {
        $post_title_lower = strtolower($post_title);
        $normalized_lower = strtolower($normalized_title);
        
        // Split into words
        $normalized_words = explode(' ', $normalized_lower);
        $post_words = explode(' ', $post_title_lower);
        
        // Check if all normalized words appear in post title
        foreach ($normalized_words as $word) {
            if (strlen($word) < 3) continue; // Skip short words
            
            $word_found = false;
            foreach ($post_words as $post_word) {
                if ($post_word === $word) {
                    $word_found = true;
                    break;
                }
            }
            
            if (!$word_found) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Helper function to normalize titles for matching
     */
    private function normalize_title_for_matching($title) {
        $normalized = $title;
        
        // Truncate at colon if present
        if (strpos($normalized, ':') !== false) {
            $normalized = substr($normalized, 0, strpos($normalized, ':'));
        }
        
        $normalized = preg_replace('/^T2-/', '', $normalized); // Remove T2- prefix
        $normalized = str_replace(['-', '_'], ' ', $normalized); // Replace - and _ with space
        $normalized = preg_replace('/\s+/', ' ', $normalized); // Normalize spaces
        $normalized = trim($normalized); // Remove leading/trailing spaces
        
        // Filter out words <3 letters (same as JavaScript)
        $words = explode(' ', $normalized);
        $filtered_words = array_filter($words, function($word) {
            return strlen($word) >= 3;
        });
        $normalized = implode(' ', $filtered_words);
        
        return $normalized;
    }
    
    /**
     * AJAX handler for running media library analysis
     */
    public function handle_run_media_library_analysis() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'media_library_analysis_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        // Check admin privileges
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Access denied. Admin privileges required.'));
            return;
        }
        
        try {
            // Create a unique filename for this analysis
            $timestamp = current_time('Y-m-d_H-i-s');
            $filename = 'media-library-analysis-' . $timestamp . '.html';
            $filepath = wp_upload_dir()['basedir'] . '/' . $filename;
            
            // Run the analysis and generate the report
            $analysis = new MediaLibraryAnalysis($filepath);
            $analysis->run();
            
            // Return the URL to the results
            $results_url = wp_upload_dir()['baseurl'] . '/' . $filename;
            
            wp_send_json_success(array(
                'message' => 'Analysis completed successfully',
                'results_url' => $results_url,
                'filename' => $filename
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Error running analysis: ' . $e->getMessage()
            ));
        }
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

/**
 * Media Library Analysis Class
 * Handles the analysis of media library images against post matching logic
 */
class MediaLibraryAnalysis {
    
    private $output_file;
    private $results = [];
    private $stats = [
        'total_images' => 0,
        'matched_images' => 0,
        'unmatched_images' => 0,
        'exact_matches' => 0,
        'partial_matches' => 0,
        'errors' => 0
    ];
    
    public function __construct($output_file) {
        $this->output_file = $output_file;
    }
    
    /**
     * Main execution method
     */
    public function run() {
        // Get all media attachments
        $attachments = $this->get_all_media_attachments();
        
        if (empty($attachments)) {
            throw new Exception('No media attachments found.');
        }
        
        // Process each attachment
        foreach ($attachments as $attachment) {
            $this->process_attachment($attachment);
        }
        
        // Generate report
        $this->generate_report();
    }
    
    /**
     * Get all media attachments from the database
     */
    private function get_all_media_attachments() {
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
     * Process a single attachment
     */
    private function process_attachment($attachment) {
        $this->stats['total_images']++;
        
        try {
            // Test the ucDoesImageHavePost logic
            $result = $this->test_image_post_matching($attachment);
            
            // Store result
            $this->results[] = $result;
            
            // Update stats
            if ($result['has_match']) {
                $this->stats['matched_images']++;
                if ($result['exact_match']) {
                    $this->stats['exact_matches']++;
                } else {
                    $this->stats['partial_matches']++;
                }
            } else {
                $this->stats['unmatched_images']++;
            }
            
        } catch (Exception $e) {
            $this->stats['errors']++;
            $this->results[] = [
                'attachment_id' => $attachment['id'],
                'title' => $attachment['title'],
                'error' => $e->getMessage(),
                'has_match' => false,
                'exact_match' => false
            ];
        }
    }
    
    /**
     * Test image post matching using the same logic as ucDoesImageHavePost
     */
    private function test_image_post_matching($attachment) {
        $image_title = $attachment['title'];
        
        // Normalize the image title (same as PHP normalize_title_for_matching)
        $normalized_image_title = $this->normalize_title_for_matching($image_title);
        
        // Search for posts with matching titles
        $matching_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            's' => $normalized_image_title
        ));
        
        // If no posts found with search, try exact title matching
        if (empty($matching_posts)) {
            $matching_posts = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 5,
                'title' => $normalized_image_title
            ));
        }
        
        // If still no matches, try partial matching
        if (empty($matching_posts)) {
            $words = explode(' ', $normalized_image_title);
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $partial_matches = get_posts(array(
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'posts_per_page' => 3,
                        's' => $word
                    ));
                    $matching_posts = array_merge($matching_posts, $partial_matches);
                }
            }
            // Remove duplicates
            $matching_posts = array_unique($matching_posts, SORT_REGULAR);
        }
        
        $result = [
            'attachment_id' => $attachment['id'],
            'title' => $attachment['title'],
            'alt' => $attachment['alt'],
            'normalized_title' => $normalized_image_title,
            'has_match' => !empty($matching_posts),
            'exact_match' => false,
            'matching_posts' => [],
            'total_matches' => count($matching_posts)
        ];
        
        if (!empty($matching_posts)) {
            // Look for exact title match first
            $exact_match = null;
            $other_matches = array();
            
            foreach ($matching_posts as $post) {
                if (strcasecmp($post->post_title, $normalized_image_title) === 0) {
                    $exact_match = $post;
                    break;
                } elseif ($this->is_word_contained_match($post->post_title, $normalized_image_title)) {
                    $exact_match = $post;
                    break;
                } else {
                    $other_matches[] = $post;
                }
            }
            
            if ($exact_match) {
                $result['exact_match'] = true;
                $result['primary_match'] = [
                    'id' => $exact_match->ID,
                    'title' => $exact_match->post_title,
                    'url' => get_permalink($exact_match->ID)
                ];
                $result['other_matches'] = array_map(function($post) {
                    return [
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'url' => get_permalink($post->ID)
                    ];
                }, $other_matches);
            } else {
                // No exact match found - don't assign a primary match
                $result['other_matches'] = array_map(function($post) {
                    return [
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'url' => get_permalink($post->ID)
                    ];
                }, $matching_posts);
            }
        }
        
        return $result;
    }
    
    /**
     * Check if all words from normalized title are contained in post title
     */
    private function is_word_contained_match($post_title, $normalized_title) {
        $post_title_lower = strtolower($post_title);
        $normalized_lower = strtolower($normalized_title);
        
        // Split into words
        $normalized_words = explode(' ', $normalized_lower);
        $post_words = explode(' ', $post_title_lower);
        
        // Check if all normalized words appear in post title
        foreach ($normalized_words as $word) {
            if (strlen($word) < 3) continue; // Skip short words
            
            $word_found = false;
            foreach ($post_words as $post_word) {
                if ($post_word === $word) {
                    $word_found = true;
                    break;
                }
            }
            
            if (!$word_found) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Normalize title for matching (same as PHP version)
     */
    private function normalize_title_for_matching($title) {
        $normalized = $title;
        
        // Truncate at colon if present
        if (strpos($normalized, ':') !== false) {
            $normalized = substr($normalized, 0, strpos($normalized, ':'));
        }
        
        $normalized = preg_replace('/^T2-/', '', $normalized); // Remove T2- prefix
        $normalized = str_replace(['-', '_'], ' ', $normalized); // Replace - and _ with space
        $normalized = preg_replace('/\s+/', ' ', $normalized); // Normalize spaces
        $normalized = trim($normalized); // Remove leading/trailing spaces
        
        // Filter out words <3 letters
        $words = explode(' ', $normalized);
        $filtered_words = array_filter($words, function($word) {
            return strlen($word) >= 3;
        });
        $normalized = implode(' ', $filtered_words);
        
        return $normalized;
    }
    
    /**
     * Generate and save the report
     */
    private function generate_report() {
        $report = $this->build_report_content();
        
        // Write to file
        file_put_contents($this->output_file, $report);
    }
    
    /**
     * Build the report content
     */
    private function build_report_content() {
        $report = '<!DOCTYPE html>
<html>
<head>
    <title>Media Library Analysis Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #0073aa; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #0073aa; }
        .stat-label { color: #666; margin-top: 5px; }
        .results { background: #fff; border: 1px solid #ddd; border-radius: 5px; }
        .result-item { padding: 15px; border-bottom: 1px solid #eee; }
        .result-item:last-child { border-bottom: none; }
        .match-exact { background: #d4edda; border-left: 4px solid #28a745; }
        .match-partial { background: #fff3cd; border-left: 4px solid #ffc107; }
        .no-match { background: #f8d7da; border-left: 4px solid #dc3545; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .summary { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        h1, h2, h3 { color: #333; }
        .back-link { margin-top: 20px; }
        .back-link a { color: #0073aa; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Media Library Analysis Report</h1>
            <p>Generated: ' . date('Y-m-d H:i:s') . '</p>
        </div>';
        
        // Summary statistics
        $match_percentage = $this->stats['total_images'] > 0 ? 
            round(($this->stats['matched_images'] / $this->stats['total_images']) * 100, 2) : 0;
        
        $report .= '
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number">' . $this->stats['total_images'] . '</div>
                <div class="stat-label">Total Images</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . $this->stats['matched_images'] . '</div>
                <div class="stat-label">Matched Images</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . $this->stats['unmatched_images'] . '</div>
                <div class="stat-label">Unmatched Images</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . $this->stats['exact_matches'] . '</div>
                <div class="stat-label">Exact Matches</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . $this->stats['partial_matches'] . '</div>
                <div class="stat-label">Partial Matches</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . $this->stats['errors'] . '</div>
                <div class="stat-label">Errors</div>
            </div>
        </div>
        
        <div class="summary">
            <h3>Summary</h3>
            <p><strong>Match Rate:</strong> ' . $match_percentage . '% of images have matching posts</p>
            <p><strong>Exact Match Rate:</strong> ' . ($this->stats['total_images'] > 0 ? round(($this->stats['exact_matches'] / $this->stats['total_images']) * 100, 2) : 0) . '% of images have exact title matches</p>
        </div>
        
        <div class="results">
            <h3>Detailed Results</h3>';
        
        foreach ($this->results as $index => $result) {
            $css_class = $this->get_result_class($result);
            
            $report .= '
            <div class="result-item ' . $css_class . '">
                <h4>Image ' . ($index + 1) . ': ' . htmlspecialchars($result['title']) . '</h4>
                <p><strong>ID:</strong> ' . $result['attachment_id'] . '</p>
                <p><strong>Alt Text:</strong> ' . htmlspecialchars($result['alt'] ?: 'N/A') . '</p>
                <p><strong>Normalized Title:</strong> ' . htmlspecialchars($result['normalized_title']) . '</p>';
            
            if (isset($result['error'])) {
                $report .= '
                <p><strong>Status:</strong> <span style="color: red;">ERROR - ' . htmlspecialchars($result['error']) . '</span></p>';
            } else {
                if ($result['has_match']) {
                    $match_type = $result['exact_match'] ? 'EXACT MATCH' : 'PARTIAL MATCH';
                    $report .= '
                    <p><strong>Status:</strong> <span style="color: ' . ($result['exact_match'] ? 'green' : 'orange') . ';">' . $match_type . '</span></p>
                    <p><strong>Total matches found:</strong> ' . $result['total_matches'] . '</p>';
                    
                    if (isset($result['primary_match'])) {
                        $report .= '
                        <p><strong>Primary match:</strong> 
                            <a href="' . $result['primary_match']['url'] . '" target="_blank">
                                ' . htmlspecialchars($result['primary_match']['title']) . '
                            </a> (ID: ' . $result['primary_match']['id'] . ')
                        </p>';
                    } else {
                        $report .= '
                        <p><strong>Status:</strong> <span style="color: orange;">NO EXACT MATCHES</span></p>';
                    }
                    
                    if (!empty($result['other_matches'])) {
                        $report .= '
                        <p><strong>Other matches:</strong></p>
                        <ul>';
                        foreach ($result['other_matches'] as $match) {
                            $report .= '
                            <li>
                                <a href="' . $match['url'] . '" target="_blank">
                                    ' . htmlspecialchars($match['title']) . '
                                </a> (ID: ' . $match['id'] . ')
                            </li>';
                        }
                        $report .= '
                        </ul>';
                    }
                } else {
                    $report .= '
                    <p><strong>Status:</strong> <span style="color: red;">NO MATCH</span></p>';
                }
            }
            
            $report .= '
            </div>';
        }
        
        $report .= '
        </div>
        
        <div class="back-link">
            <a href="' . admin_url('admin.php?page=cocktail-images') . '">← Back to Cocktail Images Admin</a>
        </div>
    </div>
</body>
</html>';
        
        return $report;
    }
    
    /**
     * Get CSS class for result styling
     */
    private function get_result_class($result) {
        if (isset($result['error'])) {
            return 'error';
        }
        if ($result['has_match']) {
            return $result['exact_match'] ? 'match-exact' : 'match-partial';
        }
        return 'no-match';
    }
}

// Initialize the plugin
global $cocktail_images_plugin;
$cocktail_images_plugin = new Cocktail_Images_Plugin(); 