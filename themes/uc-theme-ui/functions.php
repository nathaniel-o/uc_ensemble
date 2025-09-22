<?php


add_action( 'wp_enqueue_scripts', 'uc_enqueue_script' );
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
function uc_enqueue_script(){
	wp_enqueue_script(
	'uc-script',
	get_theme_file_uri('/scripts/functions.js'),
	array( ),  /*  params: load strategy async/defer, in_footer t/f  */ 
  	time() );
}

// Insert background based on page ID
function uc_insert_background() {
	$page_id = get_the_ID();
	$page_slug = get_post_field('post_name', $page_id);
	
	// Get background color from CSS custom property
	$bg_color = 'var(--bg-color)';
	
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
	} else {
		// Apply background color and image for other pages
		$bg_image = 'var(--bg-image)';
		echo '<style>body { background-color: ' . $bg_color . '; background-image: ' . $bg_image . '; }</style>';
	}
}


add_action( 'after_setup_theme', 'theme_support_setup' );
# I Forget what this does. 
function theme_support_setup() {   
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
}

add_action( 'init', 'uc_register_taxonomy_drinks' );
// taxonomy.php
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

//Passes slug from URI to JS for more usage. 
function uc_page_id() {    
    // Only run on frontend, not in admin
    if (is_admin()) {
        return;
    }

    add_action('wp_head', function() {
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
                
                echo '<script> var pageID = "' . esc_js($slug) . '"</script>';
                echo '<script> console.log("Single Post - Drinks Category Slug: ' . esc_js($slug) . '");</script>';
                return;
            }
        }
        
        // Default behavior for non-single posts or posts without drinks taxonomy
        // Get current URL path and remove leading/trailing slashes
        $slug = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        
        // Remove any backslash and all preceding characters i.e. the wordpress-folder/ prefix. 
        $slug = preg_replace('/^.*\//', '', $slug);

        //  Finally, remove the trailing -cocktails if exists (due simplified CSS vars)
        $slug = preg_replace('/-cocktails$/', '', $slug);

        
        // Set to 'home' if empty
        if (empty($slug)) {
            $slug = 'home';
        }

        if ($slug != 'wp-json') {
            echo '<script> var pageID = "' . esc_js($slug) . '"</script>';
            echo '<script> console.log("Page Slug: ' . esc_js($slug) . '");</script>';
        }
    });
}
add_action('init', 'uc_page_id');  
  #Must pass REFERENCE to a function, as a STRING to Hook





/* // Add AJAX handler for filter_carousel
add_action('wp_ajax_filter_carousel', 'handle_filter_carousel');
add_action('wp_ajax_nopriv_filter_carousel', 'handle_filter_carousel');

function handle_filter_carousel() {
    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
    
    // Get drink posts
    $drink_posts = uc_get_drinks();
     
    // Generate carousel HTML
	$filtered_carousel = uc_filter_carousel($search_term, $drink_posts, 5, 0, 1, 1);
    echo $filtered_carousel;

   # //error_log($filtered_carousel);
    wp_die(); // Required for proper AJAX response
} */







add_action('wp_head', function() {
    # FOR DEBUG //error_log('Registered patterns: ' . print_r(WP_Block_Patterns_Registry::get_instance()->get_all_registered(), true));
    echo dom_content_loaded('styleImagesByPageID(pageID);', 'ucColorH1();', 'ucStyleBackground();');    //    Pass JS backgrounds function into DOMContent Evt Lstnr
    echo dom_content_loaded(0,0,0);

    uc_insert_background();

});

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

/*
*    Simple background function that works for all page types
*    Now that pageID is set to drinks taxonomy for single posts, we can use one function
*
*function ucInsertBackground(){
*    return 'ucInsertBackground();';
*}
*/



add_action('after_setup_theme', function() {
    add_theme_support('post-thumbnails');  //forget what this does
    add_theme_support('wp-block-styles');  //forget what this does

	add_theme_support('custom-logo');
});



?>



<?php

// Uncomment the following line to update all excerpts, then comment it out again
//add_action('init', 'uc_update_all_drink_excerpts');

// Clear all drink post excerpts
/* function uc_clear_all_drink_excerpts() {
    $drinks_query = uc_drink_query();

    if ($drinks_query->have_posts()) {
        while ($drinks_query->have_posts()) {
            $drinks_query->the_post();
            $post_id = get_the_ID();
            
            wp_update_post(array(
                'ID' => $post_id,
                'post_excerpt' => ''  // Set excerpt to empty string
            ));
        }
        wp_reset_postdata();
    }
} */

// Uncomment the following line to run once, then comment it out again
//add_action('init', 'uc_clear_all_drink_excerpts');






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









