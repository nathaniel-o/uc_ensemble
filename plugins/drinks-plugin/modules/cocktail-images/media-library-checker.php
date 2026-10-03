<?php
/**
 * Media Library Checker Script
 * 
 * This script checks the WordPress media library and assesses the ucDoesImageHavePost logic
 * by testing each image against the post matching algorithm. Results are output to a text file.
 * 
 * Usage: Run this script from the WordPress root directory
 * php media-library-checker.php
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Ensure we're in a WordPress environment
if (!defined('ABSPATH')) {
    die('This script must be run from within WordPress');
}

class MediaLibraryChecker {
    
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
    
    public function __construct($output_file = 'media-library-results.txt') {
        $this->output_file = $output_file;
    }
    
    /**
     * Main execution method
     */
    public function run() {
        echo "=== Media Library Checker ===\n";
        echo "Starting analysis...\n\n";
        
        // Get all media attachments
        $attachments = $this->get_all_media_attachments();
        
        if (empty($attachments)) {
            echo "No media attachments found.\n";
            return;
        }
        
        echo "Found " . count($attachments) . " media attachments.\n\n";
        
        // Process each attachment
        foreach ($attachments as $attachment) {
            $this->process_attachment($attachment);
        }
        
        // Generate report
        $this->generate_report();
        
        echo "\n=== Analysis Complete ===\n";
        echo "Results saved to: " . $this->output_file . "\n";
        echo "Total images processed: " . $this->stats['total_images'] . "\n";
        echo "Matched images: " . $this->stats['matched_images'] . "\n";
        echo "Unmatched images: " . $this->stats['unmatched_images'] . "\n";
        echo "Exact matches: " . $this->stats['exact_matches'] . "\n";
        echo "Partial matches: " . $this->stats['partial_matches'] . "\n";
        echo "Errors: " . $this->stats['errors'] . "\n";
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
        
        echo "Processing image {$this->stats['total_images']}: {$attachment['title']}\n";
        
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
            echo "  Error: " . $e->getMessage() . "\n";
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
        $report = "=== Media Library Analysis Report ===\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Summary statistics
        $report .= "SUMMARY STATISTICS:\n";
        $report .= "==================\n";
        $report .= "Total images processed: " . $this->stats['total_images'] . "\n";
        $report .= "Matched images: " . $this->stats['matched_images'] . "\n";
        $report .= "Unmatched images: " . $this->stats['unmatched_images'] . "\n";
        $report .= "Exact matches: " . $this->stats['exact_matches'] . "\n";
        $report .= "Partial matches: " . $this->stats['partial_matches'] . "\n";
        $report .= "Errors: " . $this->stats['errors'] . "\n\n";
        
        $match_percentage = $this->stats['total_images'] > 0 ? 
            round(($this->stats['matched_images'] / $this->stats['total_images']) * 100, 2) : 0;
        $report .= "Match rate: {$match_percentage}%\n\n";
        
        // Detailed results
        $report .= "DETAILED RESULTS:\n";
        $report .= "=================\n\n";
        
        foreach ($this->results as $index => $result) {
            $report .= "Image " . ($index + 1) . ":\n";
            $report .= "  ID: " . $result['attachment_id'] . "\n";
            $report .= "  Title: " . $result['title'] . "\n";
            $report .= "  Alt: " . ($result['alt'] ?: 'N/A') . "\n";
            $report .= "  Normalized Title: " . $result['normalized_title'] . "\n";
            
            if (isset($result['error'])) {
                $report .= "  Status: ERROR - " . $result['error'] . "\n";
            } else {
                if ($result['has_match']) {
                    $match_type = $result['exact_match'] ? 'EXACT MATCH' : 'PARTIAL MATCH';
                    $report .= "  Status: {$match_type}\n";
                    $report .= "  Total matches found: " . $result['total_matches'] . "\n";
                    
                    if (isset($result['primary_match'])) {
                        $report .= "  Primary match: " . $result['primary_match']['title'] . " (ID: " . $result['primary_match']['id'] . ")\n";
                        $report .= "  Primary match URL: " . $result['primary_match']['url'] . "\n";
                    }
                    
                    if (!empty($result['other_matches'])) {
                        $report .= "  Other matches:\n";
                        foreach ($result['other_matches'] as $match) {
                            $report .= "    - " . $match['title'] . " (ID: " . $match['id'] . ")\n";
                        }
                    }
                } else {
                    $report .= "  Status: NO MATCH\n";
                }
            }
            
            $report .= "\n";
        }
        
        // Unmatched images summary
        $unmatched = array_filter($this->results, function($result) {
            return !isset($result['error']) && !$result['has_match'];
        });
        
        if (!empty($unmatched)) {
            $report .= "UNMATCHED IMAGES:\n";
            $report .= "=================\n";
            foreach ($unmatched as $result) {
                $report .= "- " . $result['title'] . " (ID: " . $result['attachment_id'] . ")\n";
            }
            $report .= "\n";
        }
        
        // Exact matches summary
        $exact_matches = array_filter($this->results, function($result) {
            return !isset($result['error']) && $result['has_match'] && $result['exact_match'];
        });
        
        if (!empty($exact_matches)) {
            $report .= "EXACT MATCHES:\n";
            $report .= "==============\n";
            foreach ($exact_matches as $result) {
                $report .= "- " . $result['title'] . " → " . $result['primary_match']['title'] . "\n";
            }
            $report .= "\n";
        }
        
        return $report;
    }
}

// Run the script
if (php_sapi_name() === 'cli') {
    $checker = new MediaLibraryChecker();
    $checker->run();
} else {
    echo "This script should be run from the command line.\n";
    echo "Usage: php media-library-checker.php\n";
}
?>
