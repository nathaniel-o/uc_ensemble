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
        'uc_render_dashboard_widget'            // Display callback
    );
}



function uc_render_dashboard_widget() {
    // Prevent direct access
    if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
    // Replace 1 with 2 in 3 (where 3 is /wordpress-fresh1/wp-admin/)  //  Just use ABSPATH instead.
    //$path_2 = ABSPATH . 'wp-content/nso/anTODOS.md'; 
    
    // Use WP_CONTENT_DIR instead of ABSPATH . 'wp-content/'
    $path_2 = WP_CONTENT_DIR . '/nso/anTODOS.md';
    echo '<p>' . $path_2 .  '</p>';
   
    

    // on local, above works. 
    //on live:  /wordpress/core/6.8.3/wp-content/nso/anTODOS.md
    // need : untouchedcocktails.com/wp-content/

    // outputs 1 long string. Parsing is the best way to format. No dependencies this is low maintenance. 
    //$to_do_list = file_get_contents($path_2);
     
    
if (file_exists($path_2)) {
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    WP_Filesystem();
    global $wp_filesystem;
    $to_do_list = $wp_filesystem->get_contents($path_2);
}


    //$to_do_list = uc_space_md_for_html_echo($to_do_list);
    //echo '<article>' . $to_do_list . '</article>';



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

function uc_space_md_for_html_echo($to_do_list){

    // Replace "- [" with "<br>- ["  then replace any 3digits '#nn' with '<br><br>#'
    $to_do_lines = preg_replace('/- \[/', "<br>- [", $to_do_list);
    // Also date headings 
    $to_do_lines = preg_replace('/^# \\d{1,2} (?:JANUARY|FEBRUARY|MARCH|APRIL|MAY|JUNE|JULY|AUGUST|SEPTEMBER|OCTOBER|NOVEMBER|DECEMBER) \\d{2}$/im', 
                                '<br>$0<br>', $to_do_lines);
    
    // more date indent & other #s                            
    $to_do_lines = preg_replace('/#/', '<br><br>#', $to_do_lines);

    // match literal tildas with any non-tildas between, add line breaks.
    $to_do_lines = preg_replace('/~~([^~]+)~~/', '<br>~~$1~~<br>', $to_do_lines);
    return $to_do_lines;
}


/**
 * Basic Markdown to HTML converter
 */
function uc_markdown_to_html($markdown) {
    // Convert headers (start with most specific to avoid conflicts)
    $markdown = preg_replace('/^#### (.*$)/m', '<h4>$1</h4>', $markdown);
    $markdown = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $markdown);
    $markdown = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $markdown);
    $markdown = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $markdown);
    
    // Convert bold and italic
    $markdown = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $markdown);
    $markdown = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $markdown);
    
    // Convert code blocks
    $markdown = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $markdown);
    $markdown = preg_replace('/`(.*?)`/', '<code>$1</code>', $markdown);
    
    // Convert lists (unordered)
    $markdown = preg_replace('/^\- (.*$)/m', '<li>$1</li>', $markdown);
    
    // Wrap consecutive <li> in <ul>
    $markdown = preg_replace('/(<li>.*?<\/li>\n?)+/s', '<ul>$0</ul>', $markdown);
    
    // Convert links: [text](url)
    $markdown = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $markdown);
    
    // Convert horizontal rules
    $markdown = preg_replace('/^---$/m', '<hr>', $markdown);
    
    // Convert paragraphs (lines that don't start with HTML tags)
    $lines = explode("\n", $markdown);
    $output = '';
    $in_paragraph = false;
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Skip empty lines
        if (empty($trimmed)) {
            if ($in_paragraph) {
                $output .= '</p>';
                $in_paragraph = false;
            }
            continue;
        }
        
        // If line starts with HTML tag, don't wrap in <p>
        if (preg_match('/^<(h[1-6]|ul|ol|li|pre|code|hr|div|blockquote)/', $trimmed)) {
            if ($in_paragraph) {
                $output .= '</p>';
                $in_paragraph = false;
            }
            $output .= $line . "\n";
        } else {
            // Regular text - wrap in paragraph
            if (!$in_paragraph) {
                $output .= '<p>';
                $in_paragraph = true;
            } else {
                $output .= ' ';
            }
            $output .= $trimmed;
        }
    }
    
    if ($in_paragraph) {
        $output .= '</p>';
    }
    
    return $output;
}

?>