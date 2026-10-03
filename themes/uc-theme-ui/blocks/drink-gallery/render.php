<?php
/**
 * Dynamic render: uc/drink-gallery
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$columns = isset( $attributes['columns'] ) ? max( 1, min( 6, (int) $attributes['columns'] ) ) : 4;
$shuffle = ! isset( $attributes['shuffle'] ) || $attributes['shuffle'];

$uid = ! empty( $attributes['anchor'] )
	? sanitize_html_class( $attributes['anchor'] )
	: 'uc-drink-gallery-' . ( ! empty( $block->parsed_block['id'] ) ? (int) $block->parsed_block['id'] : wp_unique_id() );

$gallery_id = $uid;

$data = uc_get_gallery_drink_items( $shuffle );

$gallery_drinks = $data['drinks'];
$css_categories = $data['css_categories'];

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'id'    => $uid . '-wrap',
		'class' => 'tier-one source-library uc-drink-gallery-page',
	)
);
?>

<div <?php echo $wrapper_attrs; ?> data-uc-gallery-filter="1">

	<div
		id="<?php echo esc_attr( $gallery_id ); ?>"
		class="image-gallery gallery uc-drink-gallery-grid"
		role="list"
		aria-live="polite"
	>
		<?php if ( empty( $gallery_drinks ) ) : ?>
			<p class="drink-gallery-empty"><?php esc_html_e( 'No drink posts with featured images found.', 'untouchedcocktails-theme' ); ?></p>
		<?php else : ?>
			<?php foreach ( $gallery_drinks as $drink ) : ?>
				<figure
					class="gallery-drink-item cocktail-pop-out<?php echo $drink['css_category'] ? ' drink-cat-' . esc_attr( $drink['css_category'] ) : ''; ?>"
					data-cocktail-pop-out="true"
					data-wp-lightbox="true"
					data-wp-lightbox-group="drinks-plugin"
					data-cocktail-carousel="false"
					data-terms="<?php echo esc_attr( implode( ' ', $drink['term_slugs'] ) ); ?>"
					data-search-text="<?php echo esc_attr( $drink['search_text'] ); ?>"
					<?php if ( $drink['css_category'] ) : ?>
						data-gallery-category="<?php echo esc_attr( $drink['css_category'] ); ?>"
					<?php endif; ?>
					role="listitem"
				>
					<a href="<?php echo esc_url( $drink['permalink'] ); ?>" aria-label="<?php echo esc_attr( $drink['title'] ); ?>">
						<img
							src="<?php echo esc_url( $drink['thumbnail'] ); ?>"
							alt="<?php echo esc_attr( $drink['thumbnail_alt'] ); ?>"
							class="wp-image-<?php echo esc_attr( $drink['thumbnail_id'] ); ?>"
							loading="lazy"
							decoding="async"
							<?php if ( $drink['category_name'] ) : ?>
								data-drink-category="<?php echo esc_attr( $drink['category_name'] ); ?>"
							<?php endif; ?>
						/>
					</a>
				</figure>
			<?php endforeach; ?>
			<p class="drink-gallery-empty drink-gallery-filter-empty" hidden>
				<?php esc_html_e( 'No drinks match that search.', 'untouchedcocktails-theme' ); ?>
			</p>
		<?php endif; ?>
	</div>

</div>

<style>
	<?php foreach ( $css_categories as $css_id ) : ?>
	#<?php echo esc_attr( $gallery_id ); ?> .gallery-drink-item[data-gallery-category="<?php echo esc_attr( $css_id ); ?>"] {
		border: var(--<?php echo esc_attr( $css_id ); ?>-border, var(--std-border));
		box-shadow: 1px 3px 14px #ccccff;
	}
	<?php endforeach; ?>
</style>
