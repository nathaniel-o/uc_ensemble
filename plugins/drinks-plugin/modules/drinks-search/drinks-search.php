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
 * REMOVED: get_drinks_search() wrapper function
 * 
 * Previously provided singleton accessor, now directly use:
 * global $drinks_search;
 * $drinks_search->method();
 * 
 * This eliminates unnecessary function call overhead and indirection.
 */

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
 * Admin page callback - displays README, MODES, and PROGRAM-FLOWS in columns
 */
function drinks_search_admin_page() {
    // Load documentation files
    $readme_file = plugin_dir_path(__FILE__) . 'README.md';
    $modes_file = plugin_dir_path(__FILE__) . 'MODES-DOCUMENTATION.md';
    $flows_file = plugin_dir_path(__FILE__) . 'PROGRAM-FLOWS.md';
    
    $readme_content = file_exists($readme_file) ? file_get_contents($readme_file) : '# README not found';
    $modes_content = file_exists($modes_file) ? file_get_contents($modes_file) : '# MODES-DOCUMENTATION not found';
    $flows_content = file_exists($flows_file) ? file_get_contents($flows_file) : '# PROGRAM-FLOWS not found';
    
    // Convert Markdown to HTML
    $readme_html = drinks_search_markdown_to_html($readme_content);
    $modes_html = drinks_search_markdown_to_html($modes_content);
    $flows_html = drinks_search_markdown_to_html($flows_content);
    
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
                min-width: 0; /* Allow flex shrinking */
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
            
            /* Single column view for tabs */
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
            <h1>🔍 Drinks Search Module - Documentation</h1>
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
                
                // Remove active class from all tabs and columns
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
    
    // Tables - process before headers to avoid conflicts
    $html = preg_replace_callback('/\n(\|.+\|)\n(\|[-:\s|]+\|)\n((?:\|.+\|\n?)+)/m', function($matches) {
        $header_row = $matches[1];
        $body_rows = $matches[3];
        
        // Process header
        $headers = array_map('trim', explode('|', trim($header_row, '|')));
        $table = '<table><thead><tr>';
        foreach ($headers as $header) {
            $table .= '<th>' . trim($header) . '</th>';
        }
        $table .= '</tr></thead><tbody>';
        
        // Process body rows
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
    
    // Clean up empty paragraphs and fix paragraph wrapping around block elements
    $html = preg_replace('/<p>\s*<\/p>/', '', $html);
    $html = preg_replace('/<p>\s*(<h[123]|<hr|<pre|<ul|<table)/', '$1', $html);
    $html = preg_replace('/(<\/h[123]>|<\/hr>|<\/pre>|<\/ul>|<\/table>)\s*<\/p>/', '$1', $html);
    
    // Status badges
    $html = preg_replace('/✅/', '<span class="status-badge">✅</span>', $html);
    
    return $html;
}

