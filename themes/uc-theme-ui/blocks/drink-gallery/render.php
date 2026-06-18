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

$gallery_id   = $uid;
$filter_all   = $uid . '-gf-all';
$filter_group = $uid . '-gallery-filter';

$data = uc_get_gallery_drink_items( $shuffle );

$gallery_drinks  = $data['drinks'];
$filter_terms    = $data['filter_terms'];
$css_categories  = $data['css_categories'];

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'id'    => $uid . '-wrap',
		'class' => 'tier-one source-library uc-drink-gallery-page',
	)
);
?>

<div <?php echo $wrapper_attrs; ?>>

	<nav class="drink-gallery-filters" aria-label="<?php esc_attr_e( 'Filter drinks by category', 'untouchedcocktails-theme' ); ?>">
		<label for="<?php echo esc_attr( $filter_all ); ?>" class="drink-gallery-filter lbl-<?php echo esc_attr( $filter_all ); ?>">
			<?php esc_html_e( 'All', 'untouchedcocktails-theme' ); ?>
		</label>
		<?php foreach ( $filter_terms as $term ) : ?>
			<?php $input_id = $uid . '-gf-' . $term->slug; ?>
			<label for="<?php echo esc_attr( $input_id ); ?>" class="drink-gallery-filter lbl-<?php echo esc_attr( $input_id ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</label>
		<?php endforeach; ?>
	</nav>

	<input type="radio" name="<?php echo esc_attr( $filter_group ); ?>" id="<?php echo esc_attr( $filter_all ); ?>" class="uc-gf-input" checked="checked" />
	<?php foreach ( $filter_terms as $term ) : ?>
		<input
			type="radio"
			name="<?php echo esc_attr( $filter_group ); ?>"
			id="<?php echo esc_attr( $uid . '-gf-' . $term->slug ); ?>"
			class="uc-gf-input"
		/>
	<?php endforeach; ?>

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

	#<?php echo esc_attr( $uid ); ?>-wrap:has(#<?php echo esc_attr( $filter_all ); ?>:checked) .lbl-<?php echo esc_attr( $filter_all ); ?> {
		background: var(--std-font-color, #241547);
		color: #fff;
	}
	<?php foreach ( $filter_terms as $term ) : ?>
		<?php $input_id = $uid . '-gf-' . $term->slug; ?>
	#<?php echo esc_attr( $uid ); ?>-wrap:has(#<?php echo esc_attr( $input_id ); ?>:checked) .lbl-<?php echo esc_attr( $input_id ); ?> {
		background: var(--std-font-color, #241547);
		color: #fff;
	}
	#<?php echo esc_attr( $input_id ); ?>:checked ~ #<?php echo esc_attr( $gallery_id ); ?> .gallery-drink-item:not([data-terms~="<?php echo esc_attr( $term->slug ); ?>"]) {
		display: none;
	}
	<?php endforeach; ?>
</style>
