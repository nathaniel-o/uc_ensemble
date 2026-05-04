<?php
/**
 * Plugin Name:       Home Button
 * Description:       A customizable home button block for navigation.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Untouched Cocktails
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       home-button
 * Update URI:        false
 *
 * @package HomeButton
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
add_action( 'init', 'create_block_home_button_init' );

function create_block_home_button_init() {
	register_block_type( __DIR__ . '/build' );
}

add_action( 'wp_enqueue_scripts', 'home_button_enqueue_script' );

function home_button_enqueue_script() {
	wp_add_inline_script( 'wp-block-navigation', '
function ucHomeBtn() { const navContainer = document.querySelector(\'ul.wp-block-navigation__container.is-responsive.wp-block-navigation\'); if (navContainer) { const navItems = Array.from(navContainer.children); const ucHomeBtn = document.createElement("li"); ucHomeBtn.classList.add("uc-home-button"); ucHomeBtn.classList.add("wp-block-navigation-item"); const theLogo = navItems[0].children[0]; const theLink = navItems[1].children[0]; navItems[0].remove(); navItems[1].remove(); ucHomeBtn.appendChild(theLogo); ucHomeBtn.appendChild(theLink); navContainer.prepend(ucHomeBtn); } } document.addEventListener("DOMContentLoaded", ucHomeBtn);
	' );
}
