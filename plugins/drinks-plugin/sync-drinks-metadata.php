<?php
/**
 * Sync Drinks Metadata Script
 * 
 * This script reads <ul> content from drink posts and updates their metadata
 * to match the content, cleaning up prefixes like "Fruit:" from garnish fields.
 * 
 * Usage: Run this script from the WordPress root directory
 * php sync-drinks-metadata.php
 */

// Load WordPress (only if not already loaded)
if (!defined('ABSPATH')) {
    // Try to find wp-config.php relative to this file
    $wp_config_paths = [
        dirname(__FILE__) . '/../../../../wp-config.php',
        dirname(__FILE__) . '/../../../wp-config.php',
        dirname(__FILE__) . '/../../wp-config.php',
        'wp-config.php'
    ];
    
    $wp_config_found = false;
    foreach ($wp_config_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            require_once(dirname($path) . '/wp-load.php');
            $wp_config_found = true;
            break;
        }
    }
    
    if (!$wp_config_found || !defined('ABSPATH')) {
        die('This script must be run from within WordPress or with proper wp-config.php path');
    }
}

class DrinksMetadataSync {
    
    private $stats = [
        'total_posts' => 0,
        'processed_posts' => 0,
        'updated_posts' => 0,
        'errors' => 0
    ];
    
    private $field_mapping = [
        'Category' => 'drinks_taxonomy',
        'Color' => 'drink_color',
        'Glass' => 'drink_glass',
        'Garnish' => 'drink_garnish1',
        'Garnish 2' => 'drink_garnish2',
        'Base' => 'drink_base',
        'Ice' => 'drink_ice'
    ];
    
    public function run() {
        // Check if we're in CLI mode or web mode
        $is_cli = php_sapi_name() === 'cli';
        
        if ($is_cli) {
            echo "=== Drinks Metadata Sync ===\n";
            echo "Starting metadata synchronization...\n\n";
        }
        
        // Get all drink posts
        $drink_posts = $this->get_drink_posts();
        
        if (empty($drink_posts)) {
            if ($is_cli) {
                echo "No drink posts found.\n";
            }
            return;
        }
        
        if ($is_cli) {
            echo "Found " . count($drink_posts) . " drink posts.\n\n";
        }
        
        // Process each post
        foreach ($drink_posts as $post) {
            $this->process_post($post, $is_cli);
        }
        
        // Display results
        $this->display_results($is_cli);
    }
    
    /**
     * Get all drink posts (posts with 'drinks' taxonomy)
     * 
     * Uses consolidated method from DrinksPlugin class
     */
    private function get_drink_posts() {
        global $drinks_plugin;
        return $drinks_plugin->get_published_drink_posts_raw();
    }
    
    /**
     * Process a single post
     */
    private function process_post($post, $is_cli = true) {
        $this->stats['total_posts']++;
        
        if ($is_cli) {
            echo "Processing post {$this->stats['total_posts']}: {$post->post_title}\n";
        }
        
        try {
            // Extract metadata from post content
            $content_metadata = $this->extract_metadata_from_content($post->post_content);
            
            if (empty($content_metadata)) {
                if ($is_cli) {
                    echo "  No metadata found in content\n";
                }
                return;
            }
            
            if ($is_cli) {
                echo "  Found metadata: " . implode(', ', array_keys($content_metadata)) . "\n";
            }
            
            // Get current metadata
            $current_metadata = $this->get_current_metadata($post->ID);
            
            // Compare and update if different
            $updated = $this->update_metadata_if_different($post->ID, $content_metadata, $current_metadata);
            
            if ($updated) {
                $this->stats['updated_posts']++;
                if ($is_cli) {
                    echo "  ✓ Updated metadata\n";
                }
            } else {
                if ($is_cli) {
                    echo "  - No changes needed\n";
                }
            }
            
            $this->stats['processed_posts']++;
            
        } catch (Exception $e) {
            $this->stats['errors']++;
            if ($is_cli) {
                echo "  Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Extract metadata from post content by parsing <ul> elements
     */
    private function extract_metadata_from_content($content) {
        $metadata = [];
        
        // Look for <ul class="wp-block-list"> or similar patterns
        if (preg_match('/<ul[^>]*class="[^"]*wp-block-list[^"]*"[^>]*>(.*?)<\/ul>/s', $content, $matches)) {
            $ul_content = $matches[1];
            
            // Parse each <li> element
            if (preg_match_all('/<li[^>]*><em[^>]*>([^<]+)<\/em>\s*:\s*([^<]+)<\/li>/i', $ul_content, $li_matches, PREG_SET_ORDER)) {
                foreach ($li_matches as $match) {
                    $field_name = trim($match[1]);
                    $field_value = trim(strip_tags($match[2]));
                    
                    // Skip if field value is empty (e.g., "Category: " with nothing after colon)
                    if (empty($field_value)) {
                        continue;
                    }
                    
                    // Clean up the field value
                    $field_value = $this->clean_field_value($field_name, $field_value);
                    
                    if (!empty($field_value)) {
                        $metadata[$field_name] = $field_value;
                    }
                }
            }
        }
        
        return $metadata;
    }
    
    /**
     * Clean field values by removing common prefixes and applying proper capitalization
     */
    private function clean_field_value($field_name, $value) {
        // Remove common prefixes based on field type
        switch ($field_name) {
            case 'Garnish':
                // Remove "Fruit:", "Herb:", etc.
                $value = preg_replace('/^(Fruit|Herb|Vegetable|Spice|Other):\s*/i', '', $value);
                break;
                
            case 'Glass':
                // Remove "Type:" or similar prefixes
                $value = preg_replace('/^(Type|Style):\s*/i', '', $value);
                break;
                
            case 'Base':
                // Remove "Spirit:" or similar prefixes
                $value = preg_replace('/^(Spirit|Alcohol|Liquor):\s*/i', '', $value);
                break;
                
            case 'Color':
                // Remove "Shade:" or similar prefixes
                $value = preg_replace('/^(Shade|Hue|Tone):\s*/i', '', $value);
                break;
                
            case 'Ice':
                // Remove "Type:" or similar prefixes
                $value = preg_replace('/^(Type|Style|Form):\s*/i', '', $value);
                break;
        }
        
        // General cleanup
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value); // Normalize spaces
        
        // Apply proper capitalization
        $value = $this->apply_proper_capitalization($value);
        
        return $value;
    }
    
    /**
     * Apply proper capitalization rules:
     * - With slashes: Each/Word (both sides capitalized)
     * - Without slashes: First word only (sentence case)
     */
    private function apply_proper_capitalization($value) {
        // Check if value contains slashes
        if (strpos($value, '/') !== false) {
            // Split by slash and capitalize each part
            $parts = explode('/', $value);
            $capitalized_parts = array();
            
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    // Capitalize first letter of each word in the part
                    $capitalized_parts[] = ucwords(strtolower($part));
                }
            }
            
            return implode('/', $capitalized_parts);
        } else {
            // No slashes - just capitalize first letter (sentence case)
            return ucfirst(strtolower($value));
        }
    }
    
    /**
     * Get current metadata for a post
     */
    private function get_current_metadata($post_id) {
        $metadata = [];
        
        foreach ($this->field_mapping as $field_name => $meta_key) {
            if ($meta_key === 'drinks_taxonomy') {
                // Get taxonomy terms
                $terms = get_the_terms($post_id, 'drinks');
                $metadata[$field_name] = $terms ? $terms[0]->name : '';
            } else {
                $metadata[$field_name] = get_post_meta($post_id, $meta_key, true);
            }
        }
        
        return $metadata;
    }
    
    /**
     * Update metadata if different from content
     */
    private function update_metadata_if_different($post_id, $content_metadata, $current_metadata) {
        $updated = false;
        
        foreach ($content_metadata as $field_name => $content_value) {
            if (isset($this->field_mapping[$field_name])) {
                $meta_key = $this->field_mapping[$field_name];
                $current_value = isset($current_metadata[$field_name]) ? $current_metadata[$field_name] : '';
                
                // Compare values (case-insensitive)
                if (strcasecmp(trim($current_value), trim($content_value)) !== 0) {
                    if ($meta_key === 'drinks_taxonomy') {
                        // Update taxonomy
                        $this->update_drinks_taxonomy($post_id, $content_value);
                    } else {
                        // Update post meta
                        update_post_meta($post_id, $meta_key, $content_value);
                    }
                    
                    // Only echo in CLI mode - this will be captured by ob_start in web mode
                    if (php_sapi_name() === 'cli') {
                        echo "    {$field_name}: '{$current_value}' → '{$content_value}'\n";
                    }
                    $updated = true;
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * Update drinks taxonomy
     */
    private function update_drinks_taxonomy($post_id, $category_name) {
        // Find or create the term
        $term = get_term_by('name', $category_name, 'drinks');
        
        if (!$term) {
            // Create new term if it doesn't exist
            $term_result = wp_insert_term($category_name, 'drinks');
            if (!is_wp_error($term_result)) {
                $term_id = $term_result['term_id'];
            } else {
                echo "    Error creating taxonomy term: " . $term_result->get_error_message() . "\n";
                return;
            }
        } else {
            $term_id = $term->term_id;
        }
        
        // Set the term for the post
        wp_set_post_terms($post_id, array($term_id), 'drinks');
    }
    
    /**
     * Display final results
     */
    private function display_results($is_cli = true) {
        if ($is_cli) {
            echo "\n=== Sync Complete ===\n";
            echo "Total posts found: " . $this->stats['total_posts'] . "\n";
            echo "Posts processed: " . $this->stats['processed_posts'] . "\n";
            echo "Posts updated: " . $this->stats['updated_posts'] . "\n";
            echo "Errors: " . $this->stats['errors'] . "\n";
            
            if ($this->stats['updated_posts'] > 0) {
                echo "\n✓ Successfully updated " . $this->stats['updated_posts'] . " posts with cleaned metadata.\n";
            } else {
                echo "\n- No posts needed updates.\n";
            }
        }
    }
}

// Run the script
if (php_sapi_name() === 'cli') {
    $sync = new DrinksMetadataSync();
    $sync->run();
} else {
    echo "This script should be run from the command line.\n";
    echo "Usage: php sync-drinks-metadata.php\n";
}
?>
