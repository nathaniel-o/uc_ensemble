<?php
// Basic security: Check if user is logged in and is admin
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

if (!current_user_can('administrator')) {
    die('Access denied');
}

// Add basic styling for output
echo '<style>
    body { font-family: monospace; padding: 20px; }
    .update { color: green; }
    .error { color: red; }
    .info { color: blue; }
</style>';

echo "<h2>Syncing Drinks Metadata...</h2>";

// Read and decode drinks.json with debugging
$json_file = dirname(dirname(__FILE__)) . '\uc-theme-ui\drinks.json';
echo "<div class='info'>Attempting to read: " . $json_file . "</div>";

// Check if file exists
if (!file_exists($json_file)) {
    die("<div class='error'>drinks.json file not found!</div>");
}

// Read file contents
$json_content = file_get_contents($json_file);
if ($json_content === false) {
    die("<div class='error'>Unable to read file contents!</div>");
}

// Show first 200 characters of file (for debugging)
echo "<pre>First 200 characters of file:\n" . htmlspecialchars(substr($json_content, 0, 200)) . "...</pre>";

// Try to decode and show any errors
$drinks_data = json_decode($json_content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "<div class='error'>JSON Error: " . json_last_error_msg() . "</div>";
    
    // Show line numbers for easier debugging
    $lines = explode("\n", $json_content);
    echo "<pre>";
    foreach ($lines as $i => $line) {
        echo ($i + 1) . ": " . htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
    die();
}

// Function to sync a single drink's metadata
function sync_drink_metadata($drink_data) {
    // Find post by title
    $posts = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'title' => $drink_data['cocktail'],
        'posts_per_page' => 1
    ));

    if (empty($posts)) {
        echo "<div class='error'>Post not found: " . esc_html($drink_data['cocktail']) . "</div>";
        return;
    }

    $post = $posts[0];
    $post_id = $post->ID;

    // Define metadata fields to sync
    $meta_fields = array(
        'pageCode' => 'page_code',
        'color' => 'drink_color',
        'glass' => 'glass_type',
        'garnish1' => 'garnish_1',
        'garnish2' => 'garnish_2',
        'garnish3' => 'garnish_3',
        'base' => 'base_spirit',
        'ice' => 'ice_type'
    );

    // Compare and update each field
    foreach ($meta_fields as $json_key => $meta_key) {
        $current_value = get_post_meta($post_id, $meta_key, true);
        $new_value = isset($drink_data[$json_key]) ? trim($drink_data[$json_key]) : '';

        if ($current_value !== $new_value) {
            update_post_meta($post_id, $meta_key, $new_value);
            echo "<div class='update'>Updated " . esc_html($drink_data['cocktail']) . 
                 ": " . esc_html($meta_key) . 
                 " from '" . esc_html($current_value) . 
                 "' to '" . esc_html($new_value) . "'</div>";
        }
    }

    // Handle tags separately
    if (!empty($drink_data['tags'])) {
        wp_set_post_tags($post_id, $drink_data['tags'], false);
        echo "<div class='info'>Updated tags for: " . esc_html($drink_data['cocktail']) . "</div>";
    }
}

// Process all drinks
$processed = 0;
foreach ($drinks_data as $drink) {
    sync_drink_metadata($drink);
    $processed++;
}

echo "<h3>Sync complete! Processed $processed drinks.</h3>";