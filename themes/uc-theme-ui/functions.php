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

require_once get_theme_file_path( 'inc/gallery.php' );

add_action( 'init', 'uc_register_drink_gallery_block' );
function uc_register_drink_gallery_block() {
	$block_dir = get_theme_file_path( 'blocks/drink-gallery' );
	$block_uri = get_theme_file_uri( 'blocks/drink-gallery' );
	$editor_js = $block_dir . '/editor.js';

	wp_register_script(
		'uc-drink-gallery-editor',
		$block_uri . '/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		file_exists( $editor_js ) ? (string) filemtime( $editor_js ) : wp_get_theme()->get( 'Version' ),
		true
	);

	register_block_type(
		$block_dir,
		array(
			'render_callback' => 'uc_render_drink_gallery_block',
			'editor_script'   => 'uc-drink-gallery-editor',
		)
	);
}

/**
 * Server render for uc/drink-gallery.
 */
function uc_render_drink_gallery_block( $attributes, $content, $block ) {
	ob_start();
	include get_theme_file_path( 'blocks/drink-gallery/render.php' );
	return ob_get_clean();
}

add_action( 'init', 'uc_register_gallery_patterns' );
function uc_register_gallery_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category(
		'gallery',
		array(
			'label' => __( 'Gallery', 'untouchedcocktails-theme' ),
		)
	);
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
// uc_page_id() must run on `wp` (before block templates render), not only wp_head.
add_action( 'wp', function () {
	global $page_id;
	$page_id = uc_page_id();
}, 1 );

add_action('wp_head', function() {
    # FOR DEBUG //error_log('Registered patterns: ' . print_r(WP_Block_Patterns_Registry::get_instance()->get_all_registered(), true));
    
    global $page_id;
    if ( empty( $page_id ) ) {
        $page_id = uc_page_id();
    }
    echo '<script>// console.log("PHP $page_id: ' . esc_js($page_id) . '");</script>';
    // Echo pageID for JavaScript use
    if (!empty($page_id) && $page_id != 'wp-json') {
        echo '<script> var pageID = "' . esc_js($page_id) . '"</script>';
        echo '<script> console.log(pageID);</script>';
    }
    
    echo dom_content_loaded('ucPlaceSinglePostTitle();', 'styleImagesByPageID(pageID);ucColorH1();', 'ucStyleBackground();');    //    Pass JS backgrounds function into DOMContent Evt Lstnr
    #echo dom_content_loaded('ucSetupOneDrinkAllImages();', 0, 0);    //    Initialize caption normalization from cocktail-images module

});

add_action( 'wp_body_open', function () {
	global $page_id;
	if ( empty( $page_id ) ) {
		$page_id = uc_page_id();
	}
	uc_insert_background( $page_id );
}, 1 );

/**
 * Hide core/comments unless uc_page_id() set $uc_comments_enabled.
 */
function uc_render_comments() {
	add_filter(
		'render_block',
		function ( $block_content, $block ) {
			global $uc_comments_enabled;
			$name = $block['blockName'] ?? '';
			$slug = $block['attrs']['slug'] ?? '';

			if ( $name === 'core/template-part' && $slug === 'uc-comments' ) {
				return empty( $uc_comments_enabled ) ? '' : $block_content;
			}

			if ( $name === 'core/comments' ) {
				return empty( $uc_comments_enabled ) ? '' : $block_content;
			}

			return $block_content;
		},
		10,
		2
	);
}
add_action( 'init', 'uc_render_comments' );

add_filter( 'body_class', 'uc_single_drink_body_class' );
function uc_single_drink_body_class( $classes ) {
	if ( uc_is_single_drink_post() ) {
		$classes[] = 'single-drink';
	}

	return $classes;
}

function uc_is_single_drink_post( $post_id = null ) {
	$post_id = $post_id ?: get_queried_object_id();
	return is_singular( 'post' ) && $post_id && has_term( '', 'drinks', $post_id );
}

/**
 * Wrap wide media-text in .pop-off on drink posts that lack it in saved content.
 */
function uc_wrap_drink_pop_off() {
	add_filter(
		'render_block',
		function ( $block_content, $block ) {
			if ( ( $block['blockName'] ?? '' ) !== 'core/media-text' ) {
				return $block_content;
			}

			if ( ! uc_is_single_drink_post() ) {
				return $block_content;
			}

			if ( ( $block['attrs']['align'] ?? '' ) !== 'wide' ) {
				return $block_content;
			}

			static $post_has_pop_off = null;
			if ( null === $post_has_pop_off ) {
				$post               = get_post( get_queried_object_id() );
				$post_has_pop_off   = $post && str_contains( $post->post_content, 'pop-off' );
			}
			if ( $post_has_pop_off ) {
				return $block_content;
			}

			return '<div class="wp-block-group pop-off is-layout-flow wp-block-group-is-layout-flow">' . $block_content . '</div>';
		},
		10,
		2
	);
}
add_action( 'init', 'uc_wrap_drink_pop_off' );

// Return Drink Category if page is Single Post, else trim "-cocktails" from Page Slug
function uc_page_id() {
	global $uc_comments_enabled;
	$uc_comments_enabled = false;

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
        $uc_comments_enabled = ! empty( $terms ) && ! is_wp_error( $terms );
        
        if ($uc_comments_enabled) {
            // Prefer a child of "seasonal" when both parent and child are assigned
            $seasonal_term_id = null;
            foreach ($terms as $t) {
                if (isset($t->slug) && strtolower($t->slug) === 'seasonal') {
                    $seasonal_term_id = (int) $t->term_id;
                    break;
                }
            }
            $selected_term = $terms[0];
            if ($seasonal_term_id) {
                foreach ($terms as $t) {
                    if ((int) $t->parent === $seasonal_term_id) {
                        $selected_term = $t;
                        break;
                    }
                }
            }
            $slug = $selected_term->slug;
            
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


function uc_sanitize_background_svg( $svg_content ) {
	return preg_replace( '/(<svg\b[^>]*)\s+(width|height)="[^"]*"/i', '$1', $svg_content );
}

function uc_echo_svg_background_container( $container_id, $svg_path ) {
	if ( ! file_exists( $svg_path ) ) {
		return;
	}

	$svg_content = uc_sanitize_background_svg( file_get_contents( $svg_path ) );
	printf(
		'<div id="%1$s" class="uc-svg-background" aria-hidden="true" style="position:fixed;inset:0;width:100%%;height:100%%;max-width:100vw;max-height:100vh;overflow:hidden;pointer-events:none;z-index:-1;">%2$s</div>',
		esc_attr( $container_id ),
		$svg_content
	);
}

// Insert background based on page ID (SVG overlays only - colors handled by JS)
function uc_insert_background( $page_slug ) {
	$theme_images = get_template_directory() . '/images/';

	if ( strpos( $page_slug, 'autumnal' ) !== false ) {
		uc_echo_svg_background_container( 'autumnal-svg-container', $theme_images . 'autumnal-bg.svg' );
	} elseif ( strpos( $page_slug, 'springtime' ) !== false ) {
		uc_echo_svg_background_container( 'springtime-svg-container', $theme_images . 'New Springtime background transparent.svg' );
	} elseif ( strpos( $page_slug, 'summertime' ) !== false ) {
		uc_echo_svg_background_container( 'summertime-svg-container', $theme_images . 'New Springtime background transparent.svg' );
	} elseif ( strpos( $page_slug, 'winter' ) !== false ) {
		uc_echo_svg_background_container( 'winter-svg-container', $theme_images . 'New Winter background transparent.svg' );
	} elseif ( strpos( $page_slug, 'fireplace' ) !== false ) {
		uc_echo_svg_background_container( 'fireplace-svg-container', $theme_images . 'New Fireplace background transparent.svg' );
	} elseif ( strpos( $page_slug, 'special-occasion' ) !== false ) {
		uc_echo_svg_background_container( 'special-occasion-svg-container', $theme_images . 'New Special Occasion background transparent.svg' );
	} elseif ( strpos( $page_slug, 'romantic' ) !== false ) {
		uc_echo_svg_background_container( 'romantic-svg-container', $theme_images . 'New Romantic background transparent.svg' );
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
                // console.log("Dashboard: ' . $actual_link . '");

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
 * Returns: 'Springtime', 'Summertime', 'Autumnal', or 'Winter'
 * NOTE: Return values must match keys in $seasonal_urls array in uc_filter_seasonal_nav_block()
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
        return 'Winter';
    }
}

function uc_get_seasonal_urls() {
    return [
        'Springtime' => 'springtime-cocktails',
        'Summertime' => 'summertime-cocktails',
        'Autumnal'   => 'autumnal-cocktails',
        'Winter'     => 'winter-cocktails',
    ];
}

function uc_get_current_seasonal_url() {
    $current_season = uc_get_current_season();
    $seasonal_urls  = uc_get_seasonal_urls();

    if (!isset($seasonal_urls[$current_season])) {
        // error_log("UC Seasonal Link: Season key '$current_season' not found in seasonal URL list");
        return '';
    }

    return home_url('/' . $seasonal_urls[$current_season] . '/');
}

function uc_replace_seasonal_hrefs($block_content, $new_url) {
    if (empty($new_url)) {
        return $block_content;
    }

    foreach (uc_get_seasonal_urls() as $slug) {
        $block_content = preg_replace_callback(
            '/href=(["\'])([^"\']*' . preg_quote($slug, '/') . '[^"\']*)\1/i',
            function ($matches) use ($new_url) {
                return 'href=' . $matches[1] . esc_url($new_url) . $matches[1];
            },
            $block_content
        );
    }

    return $block_content;
}

/**
 * True when a navigation URL is a single site-root path like /welcome/ (for home_url()).
 * Skips protocol-relative (//), scheme URLs, mailto:, etc.
 */
function uc_nav_link_is_root_path_for_home( $url ) {
    if ( ! is_string( $url ) || $url === '' ) {
        return false;
    }
    if ( strpos( $url, '/' ) !== 0 ) {
        return false;
    }
    if ( strpos( $url, '//' ) === 0 ) {
        return false;
    }
    return true;
}

/**
 * Turn root-relative nav hrefs into home_url() so the ribbon works in a subdirectory
 * (e.g. http://localhost/uc.co/...) and at the domain root. Block attrs stay unchanged
 * so uc_filter_seasonal_nav_block can still match seasonal paths in $block['attrs']['url'].
 */
function uc_filter_navigation_link_home_url( $block_content, $block ) {
    if ( $block['blockName'] !== 'core/navigation-link' || empty( $block['attrs']['url'] ) ) {
        return $block_content;
    }
    $raw = $block['attrs']['url'];
    if ( ! uc_nav_link_is_root_path_for_home( $raw ) ) {
        return $block_content;
    }
    $resolved = esc_url( home_url( $raw ) );
    foreach ( array( '"', "'" ) as $q ) {
        $needle = 'href=' . $q . $raw . $q;
        $pos    = strpos( $block_content, $needle );
        if ( $pos !== false ) {
            return substr_replace( $block_content, 'href=' . $q . $resolved . $q, $pos, strlen( $needle ) );
        }
    }
    return $block_content;
}
add_filter( 'render_block', 'uc_filter_navigation_link_home_url', 9, 2 );

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
        'Winter Cocktails'
    ];
    
    // Seasonal URL slugs
    $seasonal_urls = uc_get_seasonal_urls();
    
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
        $new_url = uc_get_current_seasonal_url();

        if (empty($new_url)) {
            return $block_content;
        }
        
        $current_season = uc_get_current_season();
        $new_label = $current_season . ' Cocktails';
        
        // Replace the label text in the rendered HTML (match any seasonal name)
        foreach ($seasonal_names as $old_name) {
            $block_content = str_replace('>' . $old_name . '<', '>' . esc_html($new_label) . '<', $block_content);
        }
        
        $block_content = uc_replace_seasonal_hrefs($block_content, $new_url);
    }
    
    return $block_content;
}
add_filter('render_block', 'uc_filter_seasonal_nav_block', 10, 2);

/**
 * Keep the Welcome page's visible "Seasonal Cocktails" card generic, but point it
 * at the current season's page using the same date logic as the navigation.
 */
function uc_filter_welcome_seasonal_image_link($block_content, $block) {
    if (($block['blockName'] ?? '') !== 'core/image') {
        return $block_content;
    }

    if (!is_front_page() && !is_page('welcome')) {
        return $block_content;
    }

    if (strpos($block_content, 'Seasonal Cocktails') === false) {
        return $block_content;
    }

    return uc_replace_seasonal_hrefs($block_content, uc_get_current_seasonal_url());
}
add_filter('render_block', 'uc_filter_welcome_seasonal_image_link', 10, 2);


?>