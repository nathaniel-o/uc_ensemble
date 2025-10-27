<?php
/**
 * Drinks Search Module
 * 
 * Centralizes all WP_Query operations for the Drinks Plugin and Theme.
 * Provides consistent API for searching posts, drinks, and media.
 * 
 * @package DrinksPlugin
 * @subpackage DrinksSearch
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include the main class
require_once plugin_dir_path(__FILE__) . 'includes/class-drinks-search.php';

// Initialize the module
global $drinks_search;
$drinks_search = new DrinksSearch();

/**
 * Helper function to get the DrinksSearch instance
 * 
 * @return DrinksSearch
 */
function get_drinks_search() {
    global $drinks_search;
    return $drinks_search;
}

/**
 * Add Drinks Search submenu to Drinks Plugin admin menu
 */
add_action('admin_menu', 'drinks_search_add_admin_menu', 20);

function drinks_search_add_admin_menu() {
    add_submenu_page(
        'drinks-plugin',           // Parent slug
        'Drinks Search',           // Page title
        'Drinks Search',           // Menu title
        'manage_options',          // Capability
        'drinks-search',           // Menu slug
        'drinks_search_admin_page' // Callback function
    );
}

/**
 * Admin page callback - displays README content
 */
function drinks_search_admin_page() {
    // Load README content
    $readme_file = plugin_dir_path(__FILE__) . 'README.md';
    
    if (!file_exists($readme_file)) {
        echo '<div class="wrap"><h1>Drinks Search</h1><p>README file not found.</p></div>';
        return;
    }
    
    $readme_content = file_get_contents($readme_file);
    
    // Simple Markdown to HTML conversion
    $html = drinks_search_markdown_to_html($readme_content);
    
    ?>
    <div class="wrap drinks-search-admin">
        <style>
            .drinks-search-admin {
                max-width: 1200px;
                margin: 20px auto;
                padding: 0 20px;
            }
            .drinks-search-admin h1 { color: #2271b1; margin-top: 30px; }
            .drinks-search-admin h2 { 
                color: #2271b1; 
                margin-top: 30px; 
                padding-bottom: 10px;
                border-bottom: 2px solid #e0e0e0;
            }
            .drinks-search-admin h3 { color: #135e96; margin-top: 25px; }
            .drinks-search-admin code {
                background: #f5f5f5;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
                font-size: 13px;
            }
            .drinks-search-admin pre {
                background: #f5f5f5;
                padding: 15px;
                border-left: 3px solid #2271b1;
                overflow-x: auto;
                border-radius: 4px;
            }
            .drinks-search-admin pre code {
                background: transparent;
                padding: 0;
            }
            .drinks-search-admin ul, .drinks-search-admin ol {
                margin-left: 20px;
                line-height: 1.8;
            }
            .drinks-search-admin li {
                margin-bottom: 8px;
            }
            .drinks-search-admin strong {
                color: #135e96;
            }
            .drinks-search-admin hr {
                border: none;
                border-top: 1px solid #e0e0e0;
                margin: 30px 0;
            }
            .drinks-search-admin .status-badge {
                display: inline-block;
                background: #00a32a;
                color: white;
                padding: 4px 12px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
                margin-left: 10px;
            }
            .drinks-search-admin blockquote {
                border-left: 4px solid #2271b1;
                margin: 20px 0;
                padding-left: 20px;
                color: #666;
                font-style: italic;
            }
        </style>
        <?php echo $html; ?>
    </div>
    <?php
}

/**
 * Simple Markdown to HTML converter
 * 
 * @param string $markdown Markdown content
 * @return string HTML content
 */
function drinks_search_markdown_to_html($markdown) {
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
    
    // Headers
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    
    // Bold
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    
    // Horizontal rules
    $html = preg_replace('/^---+$/m', '<hr>', $html);
    
    // Lists (simple implementation)
    $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $html);
    
    // Wrap lists
    $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
    $html = preg_replace('/<\/ul>\s*<ul>/s', '', $html); // Merge consecutive lists
    
    // Links
    $html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $html);
    
    // Paragraphs
    $html = preg_replace('/\n\n/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';
    
    // Clean up empty paragraphs
    $html = preg_replace('/<p>\s*<\/p>/', '', $html);
    $html = preg_replace('/<p>\s*(<h[123]|<hr|<pre|<ul)/', '$1', $html);
    $html = preg_replace('/(<\/h[123]>|<\/hr>|<\/pre>|<\/ul>)\s*<\/p>/', '$1', $html);
    
    // Status badges
    $html = preg_replace('/✅/', '<span class="status-badge">✅</span>', $html);
    
    return $html;
}

