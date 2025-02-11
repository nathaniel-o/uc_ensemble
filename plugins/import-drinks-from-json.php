<?php
/*
 * Plugin Name:       Import Drinks from json Plugin
 * Plugin URI:        // More Here 
 * Description:       Make WordPress Drinkable. 
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Nathaniel OToole  
 * Author URI:        //  More Here 
 * License:           GPL v2 or later //Change Here? 
 * License URI:        // https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        //
 * Text Domain:       //my-basics-plugin
 * Domain Path:       /languages
 * Requires Plugins:  //
 */


// Best Practice : Execute only if WP request //Does this need be nested? 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


 /* Register Query Carousel Style as User Option*/
/* function register_query_carousel_style() {
    register_block_style('core/query', [
        'name' => 'carousel',
        'label' => 'Carousel'
    ]);
}
add_action('init', 'register_query_carousel_style'); */








// Hook to init to ensure WordPress is fully loaded
function import_drinks_to_posts() {
	// Increase PHP execution time limit
	set_time_limit(300); // Set to 5 minutes

    // Only run if not completed and user is admin
    if (get_option('drinks_import_completed') || !current_user_can('manage_options')) {
        return;
    }

	// Get current batch progress
    $current_batch = get_option('drinks_import_batch', 0);

    // Map pageCode to full category names
    $category_map = [
        'EV-Everyday' => 'Everyday Cocktails',
        'SO-Special Occasion' => 'Special Occasion Cocktails',
        'FP-Fireplace' => 'Fireplace Cocktails',
        'RO-Romantic' => 'Romantic Cocktails',
        'SU-Summertime' => 'Summertime Cocktails',
        'SP-Springtime' => 'Springtime Cocktails',
        'AU-Autumnal' => 'Autumnal Cocktails',
        'WI-Wintertime' => 'Wintertime Cocktails'
    ];

    // Create taxonomy terms if they don't exist
    foreach ($category_map as $code => $name) {
        if (!term_exists($name, 'drinks')) {
            $result = wp_insert_term($name, 'drinks', [
                'slug' => sanitize_title($name)
            ]);
            if (is_wp_error($result)) {
                error_log('Failed to create term: ' . $name . ' - ' . $result->get_error_message());
            }
        }
    }

    // Read the JSON file (Outside Theme Directory)
    $json_file = WP_CONTENT_DIR . '/nso/drinks-26Dec24.json';
    // C:\xampp\htdocs\wordpress\wp-content\nso\drinks-26Dec24.json
    $json_data = file_get_contents($json_file);
    $drinks = json_decode($json_data, true);

	// Process in batches of 5
    $batch_size = 5;
    $total_drinks = count($drinks);
	
	
	while($current_batch < $total_drinks){
		$drinks_slice = array_slice($drinks, $current_batch, $batch_size);


		foreach ($drinks_slice as $drink) {
			// Improved post existence check using WP_Query
			$existing_query = new WP_Query(array(
				'post_type' => 'post',
				'title' => $drink['cocktail'],
				'posts_per_page' => 1,
				'post_status' => 'any'
			));

			// Debug logging
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log(sprintf('Processing drink: %s, Exists: %s', 
					$drink['cocktail'], 
					$existing_query->have_posts() ? 'Yes' : 'No'
				));
			}

			// Prepare the post content with the featured image
			$post_content = sprintf('<!-- wp:group {"layout":{"type":"constrained"}} -->
			<div class="wp-block-group">
				<!-- wp:media-text {"mediaId":%d,"mediaLink":"%s","mediaType":"image"} -->
				<div class="wp-block-media-text alignwide is-stacked-on-mobile">
					<figure class="wp-block-media-text__media">
						<img src="%s" alt="%s" class="wp-image-%d"/>
					</figure>
                    
					<div class="wp-block-media-text__content">

                    <!-- wp:heading {"level":1} -->
                    <h1>%s</h1>
                    <!-- /wp:heading -->

						<!-- wp:list -->
						<ul>
							<li><em>Category</em>: %s</li>
							<li><em>Color</em>: %s</li>
							<li><em>Glass</em>: %s</li>
							<li><em>Garnish</em>: %s</li>%s%s
							<li><em>Base</em>: %s</li>
							<li><em>Ice</em>: %s</li>
						</ul>
						<!-- /wp:list -->
					</div>
				</div>
				<!-- /wp:media-text -->
			</div>
			<!-- /wp:group -->',
				get_post_thumbnail_id($existing_query->posts[0]->ID),
				get_the_post_thumbnail_url($existing_query->posts[0]->ID, 'full'),
				esc_url(get_the_post_thumbnail_url($existing_query->posts[0]->ID, 'full')),
				esc_attr($drink['cocktail']),
				get_post_thumbnail_id($existing_query->posts[0]->ID),
				esc_html($drink['cocktail']),
				esc_html($category_map[$drink['pageCode']] ?? $drink['pageCode']),
				esc_html($drink['color']),
				esc_html($drink['Glass'] ?? $drink['glass'] ?? ''),
				esc_html($drink['Garnish'] ?? $drink['garnish1'] ?? ''),
				!empty($drink['garnish2']) ? "\n                <li><em>Garnish 2</em>: " . esc_html($drink['garnish2']) . "</li>" : '',
				!empty($drink['garnish3']) ? "\n                <li><em>Garnish 3</em>: " . esc_html($drink['garnish3']) . "</li>" : '',
				esc_html($drink['base']),
				esc_html($drink['ice'])
			);

			// Prepare the post data - only update content
			$post_data = array(
				'ID' => $existing_query->posts[0]->ID,
				'post_content' => $post_content,
			);

			if ($existing_query->have_posts()) {
				// Update existing post content only
				$existing_query->the_post();
				$post_id = get_the_ID();
				
				// Debug logging
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log(sprintf('Updating existing post content ID: %d', $post_id));
				}
				
				$post_id = wp_update_post($post_data);
			} else {
				// Skip creating new posts
				continue;
			}

			wp_reset_postdata(); // Clean up after WP_Query

			if (!is_wp_error($post_id)) {
				// Update categories and meta data regardless of new/existing post
				// Set category based on pageCode
				$pageCode = $drink['pageCode'];
				foreach ($category_map as $code => $name) {
					if (strpos($pageCode, substr($code, 0, 2)) === 0) {
						wp_set_object_terms($post_id, $name, 'drinks');
						break;
					}
				}

				// Update meta data
				update_post_meta($post_id, 'drink_src', $drink['src']);
				update_post_meta($post_id, 'drink_page_code', $drink['pageCode']);
				update_post_meta($post_id, 'drink_color', $drink['color']);
				update_post_meta($post_id, 'drink_glass', $drink['Glass'] ?? $drink['glass'] ?? '');
				update_post_meta($post_id, 'drink_garnish1', $drink['Garnish'] ?? $drink['garnish1'] ?? '');
				update_post_meta($post_id, 'drink_garnish2', $drink['garnish2']);
				update_post_meta($post_id, 'drink_garnish3', $drink['garnish3']);
				update_post_meta($post_id, 'drink_base', $drink['base']);
				update_post_meta($post_id, 'drink_ice', $drink['ice']);
			}

			$current_batch++;
			update_option('drinks_import_batch', $current_batch);
		}

		// Check if we've processed all drinks
		if ($current_batch >= count($drinks)) {
			update_option('drinks_import_completed', true);
			delete_option('drinks_import_batch');
			break;
		}

		// "You might want to add a progress indicator" 
		//  Inside the while loop, after processing each batch:
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log(sprintf('Processed %d of %d drinks', $current_batch, $total_drinks));
		}

	} // END WHILE

	// Mark as completed
	update_option('drinks_import_completed', true);
}  //END import_drinks_to_posts()


// Add an admin notice to show import progress
function drinks_import_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $import_completed = get_option('drinks_import_completed', false);
    $current_batch = get_option('drinks_import_batch', 0);

    if (!$import_completed) {
        ?>
        <div class="notice notice-info">
            <p>Drinks import in progress. Processed <?php echo $current_batch; ?> drinks so far. 
               <a href="<?php echo admin_url('?trigger_drinks_import=1'); ?>">Continue Import</a></p>
        </div>
        <?php
    } else {
        ?>
        <div class="notice notice-success">
            <p>Drinks import completed. 
               <a href="<?php echo admin_url('?reset_drinks_import=1'); ?>">Reset Import</a> | 
               <a href="<?php echo admin_url('?import_drinks_again=1'); ?>">Import Again</a></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'drinks_import_admin_notice');


// Add handler for reset  
function handle_drinks_import_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['reset_drinks_import'])) {
        delete_option('drinks_import_completed');
        delete_option('drinks_import_batch');
        wp_safe_redirect(admin_url());
        exit;
    }

    if (isset($_GET['import_drinks_again']) || isset($_GET['trigger_drinks_import'])) {
        delete_option('drinks_import_completed');
        delete_option('drinks_import_batch');
        import_drinks_to_posts();
        wp_safe_redirect(admin_url());
        exit;
    }
}
//  handle_drinks_import_actions executed on page load, prompted by Btns
add_action('admin_init', 'handle_drinks_import_actions');



// Add a trigger for manual import continuation //NOT USEFUL ? 
function handle_manual_import_trigger() {
    if (isset($_GET['trigger_drinks_import']) && current_user_can('manage_options')) {
        import_drinks_to_posts();
        wp_redirect(admin_url());
        exit;
    }
}
//UNCOMMENT TO IMPORT DRINKS on load - also does not update 
add_action('admin_init', 'handle_manual_import_trigger');    //  Does Nothing? 


 	// MANUAL RESET - uncomment Temporarily.
	/* function reset_drinks_import() {    //    This Works Beautifully    
	  delete_option('drinks_import_completed');
	delete_option('drinks_import_batch');
	}
	add_action('init', 'reset_drinks_import');

    //  PLUS the manual import
    add_action('init', 'import_drinks_to_posts', 20); // Priority 20 to ensure taxonomy is registered first
   */
    









    // Add update drinks tags function
function update_drinks_tags() {
    // Only run if user is admin
    if (!current_user_can('manage_options')) {
        return;
    }

    // Read the JSON file (Inside Theme Directory)
    //$json_file = get_template_directory() . '/drinks.json';
    // Read the JSON file (Outside Theme Directory)
    $json_file = WP_CONTENT_DIR . '/nso/drinks-20Jan25.json';

    
    $json_data = file_get_contents($json_file);
    $drinks = json_decode($json_data, true);

    $updated_count = 0;

    foreach ($drinks as $drink) {
        // Find the post by title using WP_Query instead of deprecated get_page_by_title
        $query = new WP_Query([
            'post_type' => 'post',
            'title' => $drink['cocktail'],
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ]);

        if ($query->have_posts()) {
            $query->the_post();
            $existing_post_id = get_the_ID();
            
            // Get tags from drink data
            // Check if tags exist and is an array
            $tags = isset($drink['tags']) ? $drink['tags'] : [];
            
            // If tags is already an array, use it directly
            // If it's a string, split it
            if (is_string($drink['tags'])) {
                $tags = array_filter(explode(',', $drink['tags']));
            }
            
            // Sanitize and trim each tag
            $tags = array_map(function($tag) {
                return sanitize_text_field(trim($tag));
            }, $tags);
            
            // Set the tags for the post
            if (!empty($tags)) {
                wp_set_post_tags($existing_post_id, $tags, true); // true to append tags
                $updated_count++;
            }
        }
    }
    
    wp_reset_postdata(); // Clean up after WP_Query

    // Store the count of updated posts
    update_option('drinks_tags_update_count', $updated_count);
    update_option('drinks_tags_update_completed', true);
}

// Add admin notice for tags update
function drinks_tags_update_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (get_option('drinks_tags_update_completed')) {
        $updated_count = get_option('drinks_tags_update_count', 0);
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Drinks tags update completed. Updated <?php echo $updated_count; ?> drinks. 
               <a href="<?php echo admin_url('?reset_drinks_tags_update=1'); ?>">Reset Tags Update</a> | 
               <a href="<?php echo admin_url('?trigger_drinks_tags_update=1'); ?>">Update Tags Again</a>
            </p>
        </div>
        <?php
    } else {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>Update drinks with tags from JSON file. 
               <a href="<?php echo admin_url('?trigger_drinks_tags_update=1'); ?>">Start Tags Update</a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'drinks_tags_update_admin_notice');

// Handle tags update actions
function handle_drinks_tags_update_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['reset_drinks_tags_update'])) {
        delete_option('drinks_tags_update_completed');
        delete_option('drinks_tags_update_count');
        wp_redirect(admin_url());
        exit;
    }

    if (isset($_GET['trigger_drinks_tags_update'])) {
        update_drinks_tags();
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'handle_drinks_tags_update_actions');






