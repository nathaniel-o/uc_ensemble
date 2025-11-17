<?php
/**
 * Media Library Checker - Web Version
 * 
 * This script checks the WordPress media library and assesses the ucDoesImageHavePost logic
 * by testing each image against the post matching algorithm. Results are displayed in the browser.
 * 
 * Usage: Access this file via browser: http://your-site.com/media-library-checker-web.php
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Ensure we're in a WordPress environment
if (!defined('ABSPATH')) {
    die('This script must be run from within WordPress');
}

// Check if user has admin privileges
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

class MediaLibraryCheckerWeb {
    
    private $results = [];
    private $stats = [
        'total_images' => 0,
        'matched_images' => 0,
        'unmatched_images' => 0,
        'exact_matches' => 0,
        'partial_matches' => 0,
        'errors' => 0
    ];
    
    public function __construct() {
        // Handle form submission
        if (isset($_POST['run_analysis'])) {
            $this->run_analysis();
        }
    }
    
    /**
     * Display the main interface
     */
    public function display() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Media Library Checker</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .container { max-width: 1200px; margin: 0 auto; }
                .header { background: #f0f0f0; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
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
                .btn { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
                .btn:hover { background: #005a87; }
                .summary { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
                .loading { text-align: center; padding: 20px; }
                .progress { width: 100%; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden; }
                .progress-bar { height: 100%; background: #0073aa; transition: width 0.3s; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Media Library Checker</h1>
                    <p>This tool analyzes your WordPress media library and tests the <code>ucDoesImageHavePost</code> logic to see which images match with posts.</p>
                </div>
                
                <?php if (empty($this->results)): ?>
                    <form method="post">
                        <button type="submit" name="run_analysis" class="btn">Run Analysis</button>
                    </form>
                <?php else: ?>
                    <!-- Statistics -->
                    <div class="stats">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $this->stats['total_images']; ?></div>
                            <div class="stat-label">Total Images</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $this->stats['matched_images']; ?></div>
                            <div class="stat-label">Matched Images</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $this->stats['unmatched_images']; ?></div>
                            <div class="stat-label">Unmatched Images</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $this->stats['exact_matches']; ?></div>
                            <div class="stat-label">Exact Matches</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $this->stats['partial_matches']; ?></div>
                            <div class="stat-label">Partial Matches</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $this->stats['errors']; ?></div>
                            <div class="stat-label">Errors</div>
                        </div>
                    </div>
                    
                    <?php 
                    $match_percentage = $this->stats['total_images'] > 0 ? 
                        round(($this->stats['matched_images'] / $this->stats['total_images']) * 100, 2) : 0;
                    ?>
                    <div class="summary">
                        <h3>Summary</h3>
                        <p><strong>Match Rate:</strong> <?php echo $match_percentage; ?>% of images have matching posts</p>
                        <p><strong>Exact Match Rate:</strong> <?php echo $this->stats['total_images'] > 0 ? round(($this->stats['exact_matches'] / $this->stats['total_images']) * 100, 2) : 0; ?>% of images have exact title matches</p>
                    </div>
                    
                    <!-- Results -->
                    <div class="results">
                        <h3>Detailed Results</h3>
                        <?php foreach ($this->results as $index => $result): ?>
                            <div class="result-item <?php echo $this->get_result_class($result); ?>">
                                <h4>Image <?php echo ($index + 1); ?>: <?php echo htmlspecialchars($result['title']); ?></h4>
                                <p><strong>ID:</strong> <?php echo $result['attachment_id']; ?></p>
                                <p><strong>Alt Text:</strong> <?php echo htmlspecialchars($result['alt'] ?: 'N/A'); ?></p>
                                <p><strong>Normalized Title:</strong> <?php echo htmlspecialchars($result['normalized_title']); ?></p>
                                
                                <?php if (isset($result['error'])): ?>
                                    <p><strong>Status:</strong> <span style="color: red;">ERROR - <?php echo htmlspecialchars($result['error']); ?></span></p>
                                <?php else: ?>
                                    <?php if ($result['has_match']): ?>
                                        <p><strong>Status:</strong> 
                                            <?php echo $result['exact_match'] ? '<span style="color: green;">EXACT MATCH</span>' : '<span style="color: orange;">PARTIAL MATCH</span>'; ?>
                                        </p>
                                        <p><strong>Total matches found:</strong> <?php echo $result['total_matches']; ?></p>
                                        
                                                                                 <?php if (isset($result['primary_match'])): ?>
                                             <p><strong>Primary match:</strong> 
                                                 <a href="<?php echo $result['primary_match']['url']; ?>" target="_blank">
                                                     <?php echo htmlspecialchars($result['primary_match']['title']); ?>
                                                 </a> (ID: <?php echo $result['primary_match']['id']; ?>)
                                             </p>
                                         <?php else: ?>
                                             <p><strong>Status:</strong> <span style="color: orange;">NO EXACT MATCHES</span></p>
                                         <?php endif; ?>
                                        
                                        <?php if (!empty($result['other_matches'])): ?>
                                            <p><strong>Other matches:</strong></p>
                                            <ul>
                                                <?php foreach ($result['other_matches'] as $match): ?>
                                                    <li>
                                                        <a href="<?php echo $match['url']; ?>" target="_blank">
                                                            <?php echo htmlspecialchars($match['title']); ?>
                                                        </a> (ID: <?php echo $match['id']; ?>)
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p><strong>Status:</strong> <span style="color: red;">NO MATCH</span></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="?download=1" class="btn">Download Results as Text File</a>
                        <a href="?" class="btn" style="background: #6c757d;">Run New Analysis</a>
                    </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
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
     * Run the analysis
     */
    private function run_analysis() {
        // Get all media attachments
        $attachments = $this->get_all_media_attachments();
        
        if (empty($attachments)) {
            echo '<p>No media attachments found.</p>';
            return;
        }
        
        // Process each attachment
        foreach ($attachments as $attachment) {
            $this->process_attachment($attachment);
        }
    }
    
    /**
     * Get all media attachments from the database
     * 
     * NOTE: WP_Query has been relocated to drinks-search module
     * MODE 4: Get All Media Attachments
     * @see modules/drinks-search/includes/class-drinks-search.php
     */
    private function get_all_media_attachments() {
        global $drinks_search;
        return $drinks_search->get_all_media_attachments();
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
}

// Handle download request
if (isset($_GET['download']) && $_GET['download'] == '1') {
    $checker = new MediaLibraryCheckerWeb();
    $checker->run_analysis();
    
    // Generate text report
    $report = "=== Media Library Analysis Report ===\n";
    $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Add statistics
    $report .= "SUMMARY STATISTICS:\n";
    $report .= "==================\n";
    $report .= "Total images processed: " . $checker->stats['total_images'] . "\n";
    $report .= "Matched images: " . $checker->stats['matched_images'] . "\n";
    $report .= "Unmatched images: " . $checker->stats['unmatched_images'] . "\n";
    $report .= "Exact matches: " . $checker->stats['exact_matches'] . "\n";
    $report .= "Partial matches: " . $checker->stats['partial_matches'] . "\n";
    $report .= "Errors: " . $checker->stats['errors'] . "\n\n";
    
    // Set headers for download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="media-library-results.txt"');
    header('Content-Length: ' . strlen($report));
    
    echo $report;
    exit;
}

// Display the interface
$checker = new MediaLibraryCheckerWeb();
$checker->display();
?>
