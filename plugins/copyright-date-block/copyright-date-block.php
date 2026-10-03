<?php
/**
 * Plugin Name:       Copyright Date Block
 * Description:       Display a copyright notice with dynamic year updates. Perfect for footers and legal pages.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            an WordPress tutorial
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       copyright-date-block
 * Update URI:        false
 *
 * @package CopyrightDateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */

 add_action( 'init', 'create_block_copyright_date_init' );

function create_block_copyright_date_init() {
	register_block_type( __DIR__ . '/build' );
}
