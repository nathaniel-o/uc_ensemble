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

// Define module constants using parent plugin paths
if (!defined('COCKTAIL_IMAGES_VERSION')) {
    define('COCKTAIL_IMAGES_VERSION', '1.0.6.' . time());
}
if (!defined('COCKTAIL_IMAGES_PLUGIN_DIR')) {
    define('COCKTAIL_IMAGES_PLUGIN_DIR', DRINKS_PLUGIN_PATH . 'modules/cocktail-images/');
}
if (!defined('COCKTAIL_IMAGES_PLUGIN_URL')) {
    define('COCKTAIL_IMAGES_PLUGIN_URL', DRINKS_PLUGIN_URL . 'modules/cocktail-images/');
}

// Include functions file
require_once COCKTAIL_IMAGES_PLUGIN_DIR . 'includes/functions.php';

/**
 * Main plugin class
 */
class Cocktail_Images_Module {
    
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
        
        // AJAX handlers for image management only
        add_action('wp_ajax_randomize_image', array($this, 'handle_randomize_image'));
        add_action('wp_ajax_nopriv_randomize_image', array($this, 'handle_randomize_image'));
        add_action('wp_ajax_find_matching_image', array($this, 'handle_find_matching_image'));
        add_action('wp_ajax_nopriv_find_matching_image', array($this, 'handle_find_matching_image'));
        add_action('wp_ajax_check_featured_image', array($this, 'handle_check_featured_image'));
        add_action('wp_ajax_nopriv_check_featured_image', array($this, 'handle_check_featured_image'));
        
        // Hook to prevent WordPress default image scaling
        add_filter('intermediate_image_sizes_advanced', array($this, 'uc_serve_img_real_size'), 10, 1);
        add_action('wp_ajax_run_media_library_analysis', array($this, 'handle_run_media_library_analysis'));
        add_action('wp_ajax_sync_metadata', array($this, 'handle_sync_metadata'));
        add_action('wp_ajax_toggle_srcset_enhancement', array($this, 'handle_toggle_srcset_enhancement'));
        add_action('wp_ajax_clear_srcset_cache', array($this, 'handle_clear_srcset_cache'));
        add_action('wp_ajax_rebuild_srcset_cache', array($this, 'handle_rebuild_srcset_cache'));
        
        // Hook to enhance srcset with matching images
        add_filter('wp_calculate_image_srcset', array($this, 'enhance_srcset_with_matching_images'), 10, 5);
        
        // Cache management hooks - clear cache when media library changes
        add_action('add_attachment', array($this, 'clear_srcset_cache_for_image'));
        add_action('delete_attachment', array($this, 'clear_srcset_cache_for_image'));
        add_action('attachment_updated', array($this, 'clear_srcset_cache_for_image'));
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
        // Frontend: enqueue source file since dist was removed
        wp_enqueue_script(
            'cocktail-images-frontend',
            COCKTAIL_IMAGES_PLUGIN_URL . 'src/cocktail-images.js',
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
            COCKTAIL_IMAGES_PLUGIN_URL . 'src/block-editor.js',
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
        
        // Enqueue lightbox CSS for frontend
        wp_enqueue_style(
            'cocktail-images-lightbox-css',
            COCKTAIL_IMAGES_PLUGIN_URL . 'assets/css/cocktail-images-lightbox.css',
            array(),
            COCKTAIL_IMAGES_VERSION
        );
    }
    
    /**
     * Add admin menu
     * DISABLED: This module's content is now displayed in the main Drinks Plugin admin page
     */
    public function add_admin_menu() {
        // Menu registration disabled - content is integrated into drinks-plugin admin page
        /*
        add_menu_page(
            'Cocktail Images',
            'Cocktail Images',
            'manage_options',
            'cocktail-images',
            array($this, 'admin_page'),
            'dashicons-format-image',
            30
        );
        */
    }
    
    /**
     * Get admin page content (without wrapper) for integration into main plugin admin page
     */
    public function get_admin_content() {
        ob_start();
        $this->render_admin_content();
        return ob_get_clean();
    }
    
    /**
     * Admin page callback (wrapper for standalone page if needed)
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Cocktail Images Module</h1>
            <?php $this->render_admin_content(); ?>
        </div>
        <?php
    }
    
    /**
     * Render admin content (cards only, no wrapper)
     */
    private function render_admin_content() {
        // Read the README.md file and display it
        $readme_path = COCKTAIL_IMAGES_PLUGIN_DIR . 'README.md';
        
        if (file_exists($readme_path)) {
            $readme_content = file_get_contents($readme_path);
            
            // Simple markdown to HTML conversion for basic formatting
            $html_content = $this->simple_markdown_to_html($readme_content);
            
            ?>
                <div class="card">
                    <?php echo $html_content; ?>
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
                    </div>
                </div>
                
                <div class="card">
                    <h2>Sync Metadata</h2>
                    <p>Copy metadata from 'primary' images (those with metadata/featured in posts) to 'secondary' images that match using the same logic as <code>ucOneDrinkAllImages</code>.</p>
                    
                    <div class="sync-metadata-section">
                        <button type="button" id="sync-metadata" class="button button-primary">
                            <span class="dashicons dashicons-update"></span>
                            Sync Metadata
                        </button>
                        
                        <div id="sync-results" style="margin-top: 15px; display: none;">
                            <h3>Sync Results</h3>
                            <div id="sync-output"></div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h2>Srcset Enhancement</h2>
                    <p>Automatically enhance featured images' srcset with matching drink images using <code>ucOneDrinkAllImages</code> logic. This provides fallback options if the primary image fails to load.</p>
                    
                    <div class="srcset-settings-section">
                        <label>
                            <input type="checkbox" id="enhance-srcset-toggle" <?php checked(get_option('cocktail_images_enhance_srcset', true)); ?>>
                            Enable Srcset Enhancement
                        </label>
                        
                        <p class="description">
                            When enabled, featured images will automatically include matching drink images in their srcset attribute, 
                            providing robust fallback options for better user experience.
                        </p>
                        
                        <div style="margin-top: 10px;">
                            <button type="button" id="clear-srcset-cache" class="button button-secondary">
                                <span class="dashicons dashicons-trash"></span>
                                Clear Srcset Cache
                            </button>
                            
                            <button type="button" id="rebuild-srcset-cache" class="button button-primary" style="margin-left: 10px;">
                                <span class="dashicons dashicons-update"></span>
                                Rebuild Cache
                            </button>
                        </div>
                    </div>
                    
                    <script>
                    jQuery(document).ready(function($) {
                        var resultsUrl = null;
                        
                        $('#run-media-analysis').on('click', function() {
                            var button = $(this);
                            var originalText = button.html();
                            
                            // Disable button and show loading
                            button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spinning"></span> Running Analysis...');
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
                        
                        // Sync Metadata functionality
                        $('#sync-metadata').on('click', function() {
                            var button = $(this);
                            var originalText = button.html();
                            
                            // Disable button and show loading
                            button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spinning"></span> Syncing...');
                            $('#sync-results').hide();
                            
                            // Make AJAX request to sync metadata
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'sync_metadata',
                                    nonce: '<?php echo wp_create_nonce("sync_metadata_nonce"); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        var output = '<div style="background: #f0f0f1; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap;">';
                                        output += response.data.output || response.data.message || 'Success!';
                                        output += '</div>';
                                        $('#sync-output').html(output);
                                        $('#sync-results').show();
                                    } else {
                                        alert('Error: ' + (response.data.message || 'Unknown error occurred'));
                                    }
                                },
                                error: function() {
                                    alert('Error: Failed to sync metadata. Please try again.');
                                },
                                complete: function() {
                                    // Re-enable button
                                    button.prop('disabled', false).html(originalText);
                                }
                            });
                        });
                        
                        // Srcset Enhancement Toggle
                        $('#enhance-srcset-toggle').on('change', function() {
                            var isEnabled = $(this).is(':checked');
                            var button = $(this);
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'toggle_srcset_enhancement',
                                    enabled: isEnabled ? 1 : 0,
                                    nonce: '<?php echo wp_create_nonce("srcset_enhancement_nonce"); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        var message = isEnabled ? 'Srcset enhancement enabled' : 'Srcset enhancement disabled';
                                        // Show a brief success message
                                        var notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
                                        $('.wrap h1').after(notice);
                                        setTimeout(function() {
                                            notice.fadeOut();
                                        }, 3000);
                                    } else {
                                        alert('Error: ' + (response.data.message || 'Failed to update setting'));
                                        // Revert checkbox
                                        button.prop('checked', !isEnabled);
                                    }
                                },
                                error: function() {
                                    alert('Error: Failed to update setting. Please try again.');
                                    // Revert checkbox
                                    button.prop('checked', !isEnabled);
                                }
                            });
                        });
                        
                        // Clear Srcset Cache
                        $('#clear-srcset-cache').on('click', function() {
                            var button = $(this);
                            var originalText = button.html();
                            
                            // Disable button and show loading
                            button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spinning"></span> Clearing...');
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'clear_srcset_cache',
                                    nonce: '<?php echo wp_create_nonce("srcset_enhancement_nonce"); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        var message = 'Srcset cache cleared successfully';
                                        var notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
                                        $('.wrap h1').after(notice);
                                        setTimeout(function() {
                                            notice.fadeOut();
                                        }, 3000);
                                    } else {
                                        alert('Error: ' + (response.data.message || 'Failed to clear cache'));
                                    }
                                },
                                error: function() {
                                    alert('Error: Failed to clear cache. Please try again.');
                                },
                                complete: function() {
                                    // Re-enable button
                                    button.prop('disabled', false).html(originalText);
                                }
                            });
                        });
                        
                        // Rebuild Srcset Cache
                        $('#rebuild-srcset-cache').on('click', function() {
                            var button = $(this);
                            var originalText = button.html();
                            
                            // Disable button and show loading
                            button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spinning"></span> Rebuilding...');
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'rebuild_srcset_cache',
                                    nonce: '<?php echo wp_create_nonce("srcset_enhancement_nonce"); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        var message = 'Cache rebuilt successfully: ' + response.data.message;
                                        var notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
                                        $('.wrap h1').after(notice);
                                        setTimeout(function() {
                                            notice.fadeOut();
                                        }, 5000);
                                    } else {
                                        alert('Error: ' + (response.data.message || 'Failed to rebuild cache'));
                                    }
                                },
                                error: function() {
                                    alert('Error: Failed to rebuild cache. Please try again.');
                                },
                                complete: function() {
                                    // Re-enable button
                                    button.prop('disabled', false).html(originalText);
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
                    .media-library-checker-section {
                        padding: 15px 0;
                    }
                    </style>
                </div>
            <?php
        } else {
            ?>
                <div class="card">
                    <p>README.md file not found.</p>
                </div>
            <?php
        }
    }
    
    /**
     * Simple markdown to HTML converter for basic formatting
     */
    private function simple_markdown_to_html($markdown) {
        $html = $markdown;
        
        // Convert headers
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $html);
        
        // Convert bold text
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        
        // Convert code blocks with language specifiers (```php, ```js, etc.)
        $html = preg_replace('/```(\w+)\n(.*?)\n```/s', '<pre><code class="language-$1">$2</code></pre>', $html);
        
        // Convert simple code blocks (``` without language)
        $html = preg_replace('/```\n(.*?)\n```/s', '<pre><code>$1</code></pre>', $html);
        
        // Convert inline code
        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);
        
        // Convert unordered lists
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
        
        // Convert line breaks
        $html = nl2br($html);
        
        return $html;
    }
    
    /**
                
                <h3>AJAX Handlers</h3>
                <ul>
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
                

            </div>

            <div class="card">
                <h2>Usage</h2>
                <p>The plugin provides object-oriented methods for all functionality:</p>
                
                <h3>Using Module Instance</h3>
                <pre><code>$module = get_cocktail_images_module();

        // Access image matching and cycling features only</code></pre>
                        
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
            // Normalize the title using the same logic as matching
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
                // Get true original image without WordPress scaling
                $file_path = get_attached_file($attachment_id);
                $upload_dir = wp_upload_dir();
                $original_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
                $metadata = wp_get_attachment_metadata($attachment_id);
                $attachment_data = array(
                    0 => $original_url,
                    1 => $metadata['width'] ?? 0,
                    2 => $metadata['height'] ?? 0
                );
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
        
        // Get true original image without WordPress scaling
        $file_path = get_attached_file($attachment_id);
        $upload_dir = wp_upload_dir();
        $original_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
        $metadata = wp_get_attachment_metadata($attachment_id);
        $attachment_data = array(
            0 => $original_url,
            1 => $metadata['width'] ?? 0,
            2 => $metadata['height'] ?? 0
        );
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
    public function normalize_title_for_matching($title) {
        $normalized = $title;
        
        // Truncate at colon if present
        if (strpos($normalized, ':') !== false) {
            $normalized = substr($normalized, 0, strpos($normalized, ':'));
        }
        
        $normalized = preg_replace('/^T2-/', '', $normalized); // Remove T2- prefix
        $normalized = str_replace(['-', '_'], ' ', $normalized); // Replace - and _ with space
        $normalized = preg_replace('/\s+/', ' ', $normalized); // Normalize spaces
        $normalized = trim($normalized); // Remove leading/trailing spaces
        
        // Remove category codes from the end (AU, SO, SU, SP, FP, EV, RO, WI)
        $normalized = preg_replace('/(AU|SO|SU|SP|FP|EV|RO|WI)$/', '', $normalized);
        
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
     * Handle sync metadata AJAX request
     */
    public function handle_sync_metadata() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'sync_metadata_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        $output = "Starting metadata sync...\n\n";
        
        // Get all image attachments
        $all_attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        ));
        
        $output .= "Found " . count($all_attachments) . " total images\n\n";
        
        // Find primary images (those with metadata or featured in posts)
        $primary_images = array();
        $secondary_images = array();
        
        foreach ($all_attachments as $attachment) {
            $attachment_id = $attachment->ID;
            
            // Check if image has metadata or is featured in posts
            $has_metadata = $this->image_has_metadata($attachment_id);
            $is_featured = $this->image_is_featured($attachment_id);
            
            if ($has_metadata || $is_featured) {
                $primary_images[] = $attachment;
                $output .= "Primary image: " . $attachment->post_title . " (ID: $attachment_id) - ";
                $output .= ($has_metadata ? "has metadata" : "") . ($has_metadata && $is_featured ? ", " : "") . ($is_featured ? "is featured" : "") . "\n";
            } else {
                $secondary_images[] = $attachment;
            }
        }
        
        $output .= "\nFound " . count($primary_images) . " primary images and " . count($secondary_images) . " secondary images\n\n";
        
        // Process each primary image and find matching secondary images
        $synced_count = 0;
        
        foreach ($primary_images as $primary) {
            $primary_title = $primary->post_title;
            $normalized_primary_title = $this->normalize_title_for_matching($primary_title);
            
            $output .= "Processing primary: " . $primary_title . " (normalized: " . $normalized_primary_title . ")\n";
            
            // Find matching secondary images
            $matching_secondaries = array();
            
            foreach ($secondary_images as $secondary) {
                $secondary_title = $secondary->post_title;
                $normalized_secondary_title = $this->normalize_title_for_matching($secondary_title);
                
                if (strcasecmp($normalized_primary_title, $normalized_secondary_title) === 0) {
                    $matching_secondaries[] = $secondary;
                }
            }
            
            if (!empty($matching_secondaries)) {
                $output .= "  Found " . count($matching_secondaries) . " matching secondary images:\n";
                
                foreach ($matching_secondaries as $secondary) {
                    $sync_result = $this->sync_image_metadata($primary->ID, $secondary->ID);
                    if ($sync_result === true) {
                        $synced_count++;
                        $output .= "    ✓ Synced metadata to: " . $secondary->post_title . " (ID: " . $secondary->ID . ")\n";
                    } elseif ($sync_result === false) {
                        $output .= "    - No empty fields to sync for: " . $secondary->post_title . " (ID: " . $secondary->ID . ") - already has metadata\n";
                    } else {
                        $output .= "    ✗ Failed to sync metadata to: " . $secondary->post_title . " (ID: " . $secondary->ID . ")\n";
                    }
                }
            } else {
                $output .= "  No matching secondary images found\n";
            }
            
            $output .= "\n";
        }
        
        $output .= "Sync complete! Synced metadata to $synced_count secondary images.\n";
        
        wp_send_json_success(array('output' => $output));
    }
    
    /**
     * Check if image has metadata
     */
    private function image_has_metadata($attachment_id) {
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        // Check if it has meaningful metadata beyond basic file info
        if (!$metadata) {
            return false;
        }
        
        // Check for alt text
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if (!empty($alt_text)) {
            return true;
        }
        
        // Check for caption
        $caption = wp_get_attachment_caption($attachment_id);
        if (!empty($caption)) {
            return true;
        }
        
        // Check for description
        $attachment = get_post($attachment_id);
        if (!empty($attachment->post_content)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if image is featured in any posts
     */
    private function image_is_featured($attachment_id) {
        // Check if this image is set as featured image in any posts
        $posts_with_featured = get_posts(array(
            'post_type' => 'any',
            'meta_key' => '_thumbnail_id',
            'meta_value' => $attachment_id,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));
        
        return !empty($posts_with_featured);
    }
    
    /**
     * Sync metadata from primary to secondary image (only fills empty fields)
     */
    private function sync_image_metadata($primary_id, $secondary_id) {
        try {
            // Get metadata from primary image
            $primary_alt = get_post_meta($primary_id, '_wp_attachment_image_alt', true);
            $primary_caption = wp_get_attachment_caption($primary_id);
            $primary_description = get_post_field('post_content', $primary_id);
            
            // Get current metadata from secondary image
            $current_alt = get_post_meta($secondary_id, '_wp_attachment_image_alt', true);
            $current_caption = wp_get_attachment_caption($secondary_id);
            $current_description = get_post_field('post_content', $secondary_id);
            
            $updated_fields = array();
            
            // Only update alt text if secondary image doesn't have one
            if (!empty($primary_alt) && empty($current_alt)) {
                update_post_meta($secondary_id, '_wp_attachment_image_alt', $primary_alt);
                $updated_fields[] = 'alt text';
            }
            
            // Only update caption if secondary image doesn't have one
            if (!empty($primary_caption) && empty($current_caption)) {
                wp_update_post(array(
                    'ID' => $secondary_id,
                    'post_excerpt' => $primary_caption
                ));
                $updated_fields[] = 'caption';
            }
            
            // Only update description if secondary image doesn't have one
            if (!empty($primary_description) && empty($current_description)) {
                wp_update_post(array(
                    'ID' => $secondary_id,
                    'post_content' => $primary_description
                ));
                $updated_fields[] = 'description';
            }
            
            // Return true if any fields were updated, false if nothing was updated
            return !empty($updated_fields);
            
        } catch (Exception $e) {
            error_log('Error syncing metadata: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enhance srcset with matching images using ucOneDrinkAllImages logic
     */
    public function enhance_srcset_with_matching_images($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        // Check if feature is enabled
        if (!get_option('cocktail_images_enhance_srcset', true)) {
            return $sources;
        }
        
        // Get current image title for matching
        $current_title = get_post_field('post_title', $attachment_id);
        if (empty($current_title)) {
            return $sources;
        }
        
        // Find matching images using cached results
        $matching_images = $this->find_matching_images_for_srcset($current_title, $attachment_id);
        
        if (empty($matching_images)) {
            return $sources;
        }
        
        // Add matching images to srcset
        foreach ($matching_images as $match) {
            // Get full-size URL (trimmed dimensions like in ucOneDrinkAllImages)
            $full_url = $this->get_original_image_url($match['id']);
            
            if ($full_url && isset($match['width']) && $match['width'] > 0) {
                $sources[$match['width']] = array(
                    'url' => $full_url,
                    'descriptor' => 'w',
                    'value' => $match['width']
                );
            }
        }
        
        // Sort by width (WordPress expects this)
        ksort($sources);
        
        return $sources;
    }
    
    
    /**
     * Check if image is used in any post content
     */
    private function image_is_used_in_content($attachment_id) {
        // Get image URL to search for in content
        $image_url = wp_get_attachment_url($attachment_id);
        $image_filename = basename($image_url);
        
        if (empty($image_url)) {
            return false;
        }
        
        // Search for image usage in post content
        $posts_with_image = get_posts(array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_thumbnail_id',
                    'value' => $attachment_id,
                    'compare' => '='
                )
            )
        ));
        
        // Also check if image filename appears in content
        if (empty($posts_with_image)) {
            $posts_with_image = get_posts(array(
                'post_type' => 'any',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                's' => $image_filename
            ));
        }
        
        return !empty($posts_with_image);
    }
    
    /**
     * Find matching images for srcset enhancement (server-side version of ucOneDrinkAllImages logic)
     */
    private function find_matching_images_for_srcset($current_title, $exclude_id) {
        // Check cache first
        $cache_key = 'srcset_matches_' . md5($current_title . '_' . $exclude_id);
        $cached_matches = get_transient($cache_key);
        
        if ($cached_matches !== false) {
            return $cached_matches;
        }
        
        // Normalize title using existing logic
        $normalized_title = $this->normalize_title_for_matching($current_title);
        
        // Get all image attachments (excluding current image)
        $all_attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'exclude' => array($exclude_id)
        ));
        
        $matching_images = array();
        
        foreach ($all_attachments as $attachment) {
            $attachment_title = $attachment->post_title;
            $normalized_attachment_title = $this->normalize_title_for_matching($attachment_title);
            
            // Check for exact match (same as ucOneDrinkAllImages)
            if (strcasecmp($normalized_attachment_title, $normalized_title) === 0) {
                // Get image metadata
                $metadata = wp_get_attachment_metadata($attachment->ID);
                
                if ($metadata && isset($metadata['width']) && isset($metadata['height'])) {
                    $matching_images[] = array(
                        'id' => $attachment->ID,
                        'title' => $attachment->post_title,
                        'width' => $metadata['width'],
                        'height' => $metadata['height']
                    );
                }
            }
        }
        
        // Limit to reasonable number of matches (max 3 for performance)
        $matching_images = array_slice($matching_images, 0, 3);
        
        // Cache results for 24 hours (longer cache since we're pre-caching everything)
        set_transient($cache_key, $matching_images, DAY_IN_SECONDS);
        
        return $matching_images;
    }
    
    /**
     * Get original image URL (trimmed dimensions like in ucOneDrinkAllImages)
     */
    private function get_original_image_url($attachment_id) {
        $file_path = get_attached_file($attachment_id);
        if (!$file_path) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $original_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
        
        // Trim dimensions like in ucOneDrinkAllImages
        return $this->trim_image_dimensions($original_url);
    }
    
    /**
     * Trim image dimensions from URL (same as ucOneDrinkAllImages JavaScript version)
     */
    private function trim_image_dimensions($url) {
        if (!$url) {
            return $url;
        }
        
        // Remove dimension patterns like -225x300, -768x1024, etc. from JPG, PNG, and WebP files
        return preg_replace('/-\d+x\d+\.(jpg|jpeg|png|webp)$/i', '.$1', $url);
    }
    
    /**
     * Handle toggle srcset enhancement AJAX request
     */
    public function handle_toggle_srcset_enhancement() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'srcset_enhancement_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;
        $enabled = (bool)$enabled;
        
        // Update the option
        update_option('cocktail_images_enhance_srcset', $enabled);
        
        wp_send_json_success(array(
            'message' => $enabled ? 'Srcset enhancement enabled' : 'Srcset enhancement disabled',
            'enabled' => $enabled
        ));
    }
    
    /**
     * Handle clear srcset cache AJAX request
     */
    public function handle_clear_srcset_cache() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'srcset_enhancement_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        // Clear all srcset-related transients
        global $wpdb;
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_srcset_matches_%'
            )
        );
        
        // Also clear transient timeouts
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_srcset_matches_%'
            )
        );
        
        wp_send_json_success(array(
            'message' => "Cleared $deleted cached srcset matches",
            'cleared_count' => $deleted
        ));
    }
    
    /**
     * Handle rebuild srcset cache AJAX request
     */
    public function handle_rebuild_srcset_cache() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'srcset_enhancement_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        // Clear existing cache first
        $this->clear_all_srcset_cache();
        
        // Rebuild cache for all images
        $result = $this->pre_cache_all_srcset_matches();
        
        wp_send_json_success(array(
            'message' => "Rebuilt cache for {$result['processed']} images, found {$result['matches']} total matches",
            'processed' => $result['processed'],
            'matches' => $result['matches']
        ));
    }
    
    /**
     * Pre-cache matching images for all media library items
     */
    private function pre_cache_all_srcset_matches() {
        $all_attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        ));
        
        $processed_count = 0;
        $total_matches = 0;
        
        foreach ($all_attachments as $attachment) {
            $title = $attachment->post_title;
            if (empty($title)) {
                continue;
            }
            
            $normalized_title = $this->normalize_title_for_matching($title);
            
            // Find matches for this image
            $matches = $this->find_matching_images_for_srcset($title, $attachment->ID);
            $total_matches += count($matches);
            $processed_count++;
        }
        
        return array(
            'processed' => $processed_count,
            'matches' => $total_matches
        );
    }
    
    /**
     * Clear srcset cache for a specific image when it's modified
     */
    public function clear_srcset_cache_for_image($attachment_id) {
        // Get the attachment title to find the cache key
        $title = get_post_field('post_title', $attachment_id);
        if (empty($title)) {
            return;
        }
        
        // Clear cache for this specific image
        $cache_key = 'srcset_matches_' . md5($title . '_' . $attachment_id);
        delete_transient($cache_key);
        
        // Also clear cache for any images that might match this one
        $this->clear_matching_srcset_cache($title);
    }
    
    /**
     * Clear cache for images that match the given title
     */
    private function clear_matching_srcset_cache($title) {
        $normalized_title = $this->normalize_title_for_matching($title);
        
        // Get all image attachments
        $all_attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        ));
        
        foreach ($all_attachments as $attachment) {
            $attachment_title = $attachment->post_title;
            $normalized_attachment_title = $this->normalize_title_for_matching($attachment_title);
            
            // If this image matches the title, clear its cache
            if (strcasecmp($normalized_attachment_title, $normalized_title) === 0) {
                $cache_key = 'srcset_matches_' . md5($attachment_title . '_' . $attachment->ID);
                delete_transient($cache_key);
            }
        }
    }
    
    /**
     * Clear all srcset cache
     */
    private function clear_all_srcset_cache() {
        global $wpdb;
        
        // Clear all srcset-related transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_srcset_matches_%'
            )
        );
        
        // Also clear transient timeouts
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_srcset_matches_%'
            )
        );
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
     * Uses consolidated method from DrinksPlugin class
     */
    private function get_all_media_attachments() {
        global $drinks_plugin;
        return $drinks_plugin->get_all_media_attachments();
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
            <a href="' . admin_url('admin.php?page=drinks-plugin') . '">← Back to Drinks Plugin Admin</a>
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
    
    /**
     * Prevent WordPress default image scaling
     * This function does one thing: disables WordPress's automatic image scaling
     */
    public function uc_serve_img_real_size($sizes) {
        // Return empty array to prevent WordPress from creating any scaled versions
        return array();
    }
    
}

// Initialize the plugin
global $cocktail_images_module;
$cocktail_images_module = new Cocktail_Images_Module();

/**
 * Accessor for the Cocktail_Images_Module instance
 */
if (!function_exists('get_cocktail_images_module')) {
    function get_cocktail_images_module() {
        global $cocktail_images_module;
        return isset($cocktail_images_module) ? $cocktail_images_module : null;
    }
} 