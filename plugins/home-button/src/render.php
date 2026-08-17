<?php
/**
 * Renders the Home Button block on the front end.
 * Mirrors wp:navigation-link structure with site logo support.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

$label = ! empty( $attributes['label'] ) ? esc_html( $attributes['label'] ) : __( 'Home', 'home-button' );
$url = ! empty( $attributes['url'] ) ? esc_url( $attributes['url'] ) : esc_url( home_url( '/' ) );
$description = ! empty( $attributes['description'] ) ? esc_attr( $attributes['description'] ) : '';
$title_attr = ! empty( $attributes['title'] ) ? esc_attr( $attributes['title'] ) : '';
$custom_class = ! empty( $attributes['className'] ) ? esc_attr( $attributes['className'] ) : 'uc-home-button menu-item menu-item-type-post_type menu-item-object-page';

// Get custom logo
$custom_logo_id = get_theme_mod( 'custom_logo' );
$logo_html = '';

if ( $custom_logo_id ) {
	$logo_html = sprintf(
		'<span class="wp-block-site-logo"><img src="%s" alt="%s" class="custom-logo" /></span>',
		esc_url( wp_get_attachment_image_url( $custom_logo_id, 'thumbnail' ) ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

// Build classes matching wp:navigation-link structure
$wrapper_classes = 'wp-block-navigation-item wp-block-navigation-link ' . $custom_class;
?>
<li class="<?php echo esc_attr( $wrapper_classes ); ?>">
	<a class="wp-block-navigation-item__content" href="<?php echo $url; ?>" rel="home"<?php echo $title_attr ? ' title="' . $title_attr . '"' : ''; ?><?php echo $description ? ' aria-description="' . $description . '"' : ''; ?>>
		<?php echo $logo_html; ?>
		<span class="wp-block-navigation-item__label"><?php echo $label; ?></span>
	</a>
</li>
