<?php
/**
 * Dynamic render: drinks/drink-post-content
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Saved inner blocks (metadata list).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();
if ( ! $post_id && ! empty( $block->context['postId'] ) ) {
	$post_id = (int) $block->context['postId'];
}

if ( ! $post_id || ! has_term( '', 'drinks', $post_id ) ) {
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

global $drinks_plugin;
if ( ! $drinks_plugin || ! method_exists( $drinks_plugin, 'render_drink_post_content' ) ) {
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$attributes = isset( $attributes ) && is_array( $attributes ) ? $attributes : array();

echo $drinks_plugin->render_drink_post_content( $post_id, $content, $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
