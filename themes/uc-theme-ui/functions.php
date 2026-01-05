<?php

// ===== AFTER_SETUP_THEME HOOKS =====
add_action( 'after_setup_theme', 'theme_support_setup' );
function theme_support_setup() {   
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
}

add_action('after_setup_theme', function() {
    add_theme_support('post-thumbnails');  //forget what this does
    add_theme_support('wp-block-styles');  //forget what this does
	add_theme_support('custom-logo');
    //add_theme_support( 'disable-layout-styles' );   // Wanted to widen Group Blocks, instead ruins Header.
});

// ===== INIT HOOKS =====
add_action( 'init', 'uc_register_taxonomy_drinks' );
function uc_register_taxonomy_drinks() {
    $labels = array(
        'name'              => _x( 'Drinks', 'taxonomy general name' ),
        'singular_name'     => _x( 'Drink', 'taxonomy singular name' ),
        // ...
    );
    $args = array(
        'hierarchical'      => true, // hierarchical taxonomy
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'drink' ),
    );
    register_taxonomy( 'drinks', array( 'post' ), $args );
}

// ===== WP_ENQUEUE_SCRIPTS HOOKS =====
add_action( 'wp_enqueue_scripts', 'uc_enqueue_styles'  );
function uc_enqueue_styles(){
	wp_enqueue_style( 
		'uc-theme-slug',
		get_theme_file_uri( 'style.css' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		'all', 
	);

	wp_enqueue_style( 
		'iconoir',
		'https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css',
		array()
	);
	
	// Add custom CSS for rotated images
	wp_add_inline_style('uc-theme-slug', '
		img.rotate-90 { transform: rotate(90deg); }
		img.rotate-180 { transform: rotate(180deg); }
		img.rotate-270 { transform: rotate(270deg); }
		img.rotate-custom { transform: rotate(var(--rotation-angle)); }
	');
}

add_action( 'wp_enqueue_scripts', 'uc_enqueue_script' );
function uc_enqueue_script(){
	wp_enqueue_script(
	'uc-script',
	get_theme_file_uri('/scripts/functions.js'),
	array( ),  /*  params: load strategy async/defer, in_footer t/f  */ 
  	time() );
}

// ===== INIT HOOKS - AJAX HANDLER =====
// NOTE: AJAX handler has been relocated to drinks-search module
// The module automatically registers the 'modal_search' AJAX action
// See: plugins/drinks-plugin/modules/drinks-search/includes/class-drinks-search.php
// MODE 1: General Site-Wide Search

//    custom page id stuff. 
add_action('wp_head', function() {
    # FOR DEBUG //error_log('Registered patterns: ' . print_r(WP_Block_Patterns_Registry::get_instance()->get_all_registered(), true));
    
    // Get the page slug and make it global
    global $page_id;
    $page_id = uc_page_id();
    
    // Echo pageID for JavaScript use
    if (!empty($page_id) && $page_id != 'wp-json') {
        echo '<script> var pageID = "' . esc_js($page_id) . '"</script>';
        echo '<script> console.log("Page Slug: ' . esc_js($page_id) . '");</script>';
    }
    
    echo dom_content_loaded('styleImagesByPageID(pageID);', 'ucColorH1();', 'ucStyleBackground();');    //    Pass JS backgrounds function into DOMContent Evt Lstnr
    #echo dom_content_loaded('ucSetupOneDrinkAllImages();', 0, 0);    //    Initialize caption normalization from cocktail-images module

    uc_insert_background($page_id);

});

// Return Drink Category if page is Single Post, else trim "-cocktails" from Page Slug
function uc_page_id() {    
    // Only run on frontend, not in admin
    if (is_admin()) {
        return '';
    }

    // Check if this is a 404 page - use home styling
    if (is_404()) {
        return 'home';
    }

    // Check if this is a single post page
    if (is_single()) {
        $post_id = get_the_ID();
        
        // Get the drinks taxonomy terms for this post
        $terms = wp_get_post_terms($post_id, 'drinks');
        
        if (!empty($terms) && !is_wp_error($terms)) {
            // Use the first drinks category as the pageID
            $slug = $terms[0]->slug;
            
            // Remove the trailing -cocktails if exists (due simplified CSS vars)
            $slug = preg_replace('/-cocktails$/', '', $slug);
            
            // Remove -2 suffix if it exists (for CSS variable compatibility)
            $slug = preg_replace('/-2$/', '', $slug);
            
            // Map specific category codes to their CSS variable names
            $category_mapping = array(
                'fp-fireplace' => 'fireplace',
                'ev-everyday' => 'everyday',
                'ro-romantic' => 'romantic',
                'su-summertime' => 'summertime',
                'sp-springtime' => 'springtime', // springtime uses summertime CSS vars
                'so-special-occasion' => 'special-occasion',
                'wi-winter' => 'winter',
                'au-autumnal' => 'autumnal'
            );
            
            // Apply mapping if exists, otherwise use original slug
            if (isset($category_mapping[$slug])) {
                $slug = $category_mapping[$slug];
            }
            
            return $slug;
        }
    }
    
    // Default behavior for non-single posts or posts without drinks taxonomy
    // Get current URL path and remove leading/trailing slashes
    $slug = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    
    // Check if this is the home page (empty path or just the WordPress folder)
    if (empty($slug) || $slug === 'wordpress-fresh1' || $slug === 'wordpress-fresh2' || $slug ==='wordpress-new' || $slug ==='wordpress-new1') {
        return 'home';
    }

    // Remove any backslash and all preceding characters 
    $slug = preg_replace('/^.*\//', '', $slug);

    //  Finally, remove the trailing -cocktails if exists (due simplified CSS vars)
    $slug = preg_replace('/-cocktails$/', '', $slug);

    return $slug;
}


// Insert background based on page ID (SVG overlays only - colors handled by JS)
function uc_insert_background($page_slug) {
	
	if (strpos($page_slug, 'autumnal') !== false) {
		// Load SVG file content 
		$svg_path = get_template_directory() . '/images/autumnal-bg.svg';
		if (file_exists($svg_path)) {
			$svg_content = file_get_contents($svg_path);
			echo '<div id="autumnal-svg-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;">';
			echo $svg_content;
			echo '</div>';
		}
	} else if (strpos($page_slug, 'springtime') !== false) {
		// Load SVG file content 
		$svg_path = get_template_directory() . '/images/New Springtime background transparent.svg';
		if (file_exists($svg_path)) {
			$svg_content = file_get_contents($svg_path);
			echo '<div id="springtime-svg-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;">';
			echo $svg_content;
			echo '</div>';
		}
	} else if (strpos($page_slug, 'summertime') !== false) {
		// Load SVG file content (reuse Springtime SVG as source)
		$svg_path = get_template_directory() . '/images/New Springtime background transparent.svg';
		if (file_exists($svg_path)) {
			$svg_content = file_get_contents($svg_path);
			echo '<div id="summertime-svg-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;">';
			echo $svg_content;
			echo '</div>';
		}
	} else if (strpos($page_slug, 'winter') !== false) {
		// Load SVG file content 
		$svg_path = get_template_directory() . '/images/New Winter background transparent.svg';
		if (file_exists($svg_path)) {
			$svg_content = file_get_contents($svg_path);
			echo '<div id="winter-svg-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;">';
			echo $svg_content;
			echo '</div>';
		}
	} else if (strpos($page_slug, 'fireplace') !== false) {
		// Load SVG file content 
		$svg_path = get_template_directory() . '/images/New Fireplace background transparent.svg';
		if (file_exists($svg_path)) {
			$svg_content = file_get_contents($svg_path);
			echo '<div id="fireplace-svg-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;">';
			echo $svg_content;
			echo '</div>';
		}
	} else if (strpos($page_slug, 'special-occasion') !== false) {
		// Load SVG file content 
		$svg_path = get_template_directory() . '/images/New Special Occasion background transparent.svg';
		if (file_exists($svg_path)) {
			$svg_content = file_get_contents($svg_path);
			echo '<div id="special-occasion-svg-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;">';
			echo $svg_content;
			echo '</div>';
		}
	} else if (strpos($page_slug, 'romantic') !== false) {
		// Load SVG file content 
		$svg_path = get_template_directory() . '/images/New Romantic background transparent.svg';
		if (file_exists($svg_path)) {
			$svg_content = file_get_contents($svg_path);
			echo '<div id="romantic-svg-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;">';
			echo $svg_content;
			echo '</div>';
		}
	}
	// Note: Background colors are handled by ucStyleBackground() JavaScript function
}

/*
* Wrapper function that applies DOMContentLoaded event listener to testing_backgrounds output
*/
function dom_content_loaded($your_function, $another, $more) {
    $background_script = $your_function;

    if ($another != 0) {
        $background_script .= $another;
    }

    if ($more != 0) {
        $background_script .= $more;
    }
    
    // If there's no script output, return empty
    if (empty($background_script)) {
        return '';
    }
    
    // Wrap in DOMContentLoaded event listener
    return '<script>document.addEventListener("DOMContentLoaded", function() { ' . $background_script . ' });</script>';
}

function uc_dynamic_tagline($uc_page_id){

	$dynamic_h1 = '<h1>';
	#  Generate 2nd lines where <h1> is nonstandard
	if (str_contains($uc_page_id, "gallery")){
		$dynamic_h1 .= " ~ Gallery Page ~ </h1>";
	}  else if (str_contains($uc_page_id, "contact")){
        $dynamic_h1 .= "Learn More </h1>";
	} else if (str_contains($uc_page_id, "home")){ 
		$dynamic_h1 .= "Celebrating ~Every~ Occasion</h1>";
	}

	return $dynamic_h1;
}

// Params : Hook then Callback w/o () 
add_action('wp_dashboard_setup', 'uc_add_custom_dashboard_widget');

/// Ask jetpack to support MD for custom Post types . 
/* add_action('init', 'my_custom_init');
 function my_custom_init() {
 add_post_type_support( 'custom-post-type', 'wpcom-markdown' );
 } */


function uc_add_custom_dashboard_widget() {
    wp_add_dashboard_widget(
        'uc-custom-dashboard-widget',           // Widget ID (html)
        'Goings On',                    // Widget title
        'uc_issues_dashboard_widget'    //from https://github.com/nathaniel-o/uc_ensemble/issues
        /* 'uc_todo_md_widget'            // Display callback */
    );
}


function uc_issues_dashboard_widget() {
    // Add refresh button
    echo '<button id="uc-refresh-todos" class="button button-small" style="margin-bottom: 10px;">
            <span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Refresh
          </button>';
    
    echo '<div id="github-issues-container" style="margin-top: 15px;"></div>';
    
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            loadGitHubIssues();
            
            const refreshBtn = document.getElementById("uc-refresh-todos");
            if (refreshBtn) {
                refreshBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    this.disabled = true;
                    this.innerHTML = \'<span class="dashicons dashicons-update" style="animation: rotation 1s infinite linear;"></span> Loading...\';
                    loadGitHubIssues();
                });
            }
        });
        
        function loadGitHubIssues() {
            const container = document.getElementById("github-issues-container");
            container.innerHTML = "<p>Loading issues...</p>";
            
            fetch("https://api.github.com/repos/nathaniel-o/uc_ensemble/issues")
                .then(response => response.json())
                .then(issues => {
                    if (issues.length === 0) {
                        container.innerHTML = "<p>No open issues found.</p>";
                        return;
                    }
                    
                    let html = "<ul style=\"list-style: none; padding: 0;\">";
                    issues.forEach(issue => {
                        html += `
                            <li style="border-bottom: 1px solid #ddd; padding: 10px 0;">
                                <a href="${issue.html_url}" target="_blank" style="text-decoration: none;">
                                    <strong>#${issue.number}</strong>: ${issue.title}
                                </a>
                                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                    Opened by ${issue.user.login} • ${new Date(issue.created_at).toLocaleDateString()}
                                </div>
                            </li>
                        `;
                    });
                    html += "</ul>";
                    container.innerHTML = html;
                    
                    // Re-enable refresh button
                    const refreshBtn = document.getElementById("uc-refresh-todos");
                    if (refreshBtn) {
                        refreshBtn.disabled = false;
                        refreshBtn.innerHTML = \'<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Refresh\';
                    }
                })
                .catch(error => {
                    container.innerHTML = "<p style=\"color: red;\">Error loading issues: " + error.message + "</p>";
                });
        }
        
        // Add rotation animation for loading spinner
        const style = document.createElement("style");
        style.textContent = "@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }";
        document.head.appendChild(style);
    </script>';
}

function uc_todo_md_widget() {
    // Prevent direct access
    if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
    //$path_2 = ABSPATH . 'wp-content/nso/anTODOS.md'; 
    
    // Use WP_CONTENT_DIR instead of ABSPATH . 'wp-content/'
    $path_2 = WP_CONTENT_DIR . '/nso/anTODOS.md';
    
    // Read file using WP_Filesystem
    if (file_exists($path_2)) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
        global $wp_filesystem;
        $to_do_list = $wp_filesystem->get_contents($path_2);
    }

    //$to_do_list = uc_space_md_for_html_echo($to_do_list);
    //echo '<article>' . $to_do_list . '</article>';

    // Add refresh button
    echo '<button id="uc-refresh-todos" class="button button-small" style="margin-bottom: 10px;">
            <span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Refresh
          </button>';
    
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const refreshBtn = document.getElementById("uc-refresh-todos");
            if (refreshBtn) {
                refreshBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    // Add loading state
                    this.disabled = true;
                    this.innerHTML = \'<span class="dashicons dashicons-update" style="animation: rotation 1s infinite linear;"></span> Loading...\';
                    
                    // Reload the page to refresh widget
                    location.reload();
                });
            }
        });
        
        // Add rotation animation for loading spinner
        const style = document.createElement("style");
        style.textContent = "@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }";
        document.head.appendChild(style);
    </script>';

    echo '<pre class="uc_td" style="max-height: 400px; overflow-y: scroll; ">' . esc_html($to_do_list) . ' </pre>'; // this keeps whitespace but shows overflow x  
    // Works

    // Then , limit number of lines based on current date +-


    //  Also, move from #postbox-container-1.postboxcontainer 
    // to parent id dashboard-widgets class=metabox holder
    if (true){
        //global $wp;
        //$page = home_url($wp->request);
        $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
   
        if(str_contains($actual_link, 'wp-admin')){

            echo '<script>
                console.log("Dashboard: ' . $actual_link . '");

                let moveThis = document.querySelector("#uc-custom-dashboard-widget");
                let goalCtnr = document.querySelector(".metabox-holder")
                // Prepend actually deletes the inital instance as well. 
                goalCtnr.prepend(moveThis);


            </script>';

        }

        

    }
    
}


// ===== SEASONAL NAVIGATION LINK FILTER =====

/**
 * Get the current season based on date
 * Returns: 'Springtime', 'Summertime', 'Autumnal', or 'Wintertime'
 */
function uc_get_current_season() {
    $month = (int) date('n'); // 1-12
    $day = (int) date('j');   // 1-31
    
    // Using approximate seasonal dates (Northern Hemisphere)
    // Spring: March 20 - June 20
    // Summer: June 21 - September 21
    // Autumn: September 22 - December 20
    // Winter: December 21 - March 19
    
    if (($month == 3 && $day >= 20) || $month == 4 || $month == 5 || ($month == 6 && $day <= 20)) {
        return 'Springtime';
    } elseif (($month == 6 && $day >= 21) || $month == 7 || $month == 8 || ($month == 9 && $day <= 21)) {
        return 'Summertime';
    } elseif (($month == 9 && $day >= 22) || $month == 10 || $month == 11 || ($month == 12 && $day <= 20)) {
        return 'Autumnal';
    } else {
        return 'Wintertime';
    }
}

/**
 * Filter Block Editor Navigation to show current season
 * Works with core/navigation-link blocks
 */
function uc_filter_seasonal_nav_block($block_content, $block) {
    // Only process navigation-link blocks
    if ($block['blockName'] !== 'core/navigation-link') {
        return $block_content;
    }
    
    // Seasonal link texts to match (what's in the menu currently)
    $seasonal_names = [
        'Summertime Cocktails',
        'Autumnal Cocktails', 
        'Springtime Cocktails',
        'Wintertime Cocktails'
    ];
    
    // Seasonal URL slugs
    $seasonal_urls = [
        'Springtime'  => 'springtime-cocktails',
        'Summertime'  => 'summertime-cocktails',
        'Autumnal'    => 'autumnal-cocktails',
        'Wintertime'  => 'wintertime-cocktails',
    ];
    
    // Check if this block's label matches any seasonal name
    $label = isset($block['attrs']['label']) ? $block['attrs']['label'] : '';
    
    // Also check for URL-based matching (in case label isn't set)
    $block_url = isset($block['attrs']['url']) ? $block['attrs']['url'] : '';
    $is_seasonal_url = false;
    foreach ($seasonal_urls as $season => $slug) {
        if (strpos($block_url, $slug) !== false || strpos($block_content, $slug) !== false) {
            $is_seasonal_url = true;
            break;
        }
    }
    
    if (in_array($label, $seasonal_names) || $is_seasonal_url) {
        $current_season = uc_get_current_season();
        $new_label = $current_season . ' Cocktails';
        $new_url = home_url('/' . $seasonal_urls[$current_season] . '/');
        
        
        // Replace the label text in the rendered HTML (match any seasonal name)
        foreach ($seasonal_names as $old_name) {
            $block_content = str_replace('>' . $old_name . '<', '>' . esc_html($new_label) . '<', $block_content);
        }
        
        // Replace ALL seasonal URLs with the current season's URL
        foreach ($seasonal_urls as $season => $slug) {
            // Match href containing any seasonal slug
            $block_content = preg_replace(
                '/href="([^"]*' . preg_quote($slug, '/') . '[^"]*)"/i',
                'href="' . esc_url($new_url) . '"',
                $block_content
            );
        }
    }
    
    return $block_content;
}
add_filter('render_block', 'uc_filter_seasonal_nav_block', 10, 2);


?>