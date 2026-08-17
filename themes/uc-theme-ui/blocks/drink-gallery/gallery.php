<?php
/**
 * Drink gallery helpers — used by the uc/drink-gallery dynamic block.
 */

if ( ! function_exists( 'uc_gallery_term_slug_to_css_id' ) ) {
	/**
	 * Map drinks taxonomy slug → style.css CSS-var prefix (mirrors uc_page_id mapping).
	 */
	function uc_gallery_term_slug_to_css_id( $term_slug ) {
		$slug = strtolower( (string) $term_slug );
		$slug = preg_replace( '/-2$/', '', $slug );

		$map = array(
			'fp-fireplace'        => 'fireplace',
			'fireplace'           => 'fireplace',
			'ev-everyday'         => 'everyday',
			'everyday'            => 'everyday',
			'ro-romantic'         => 'romantic',
			'romantic'            => 'romantic',
			'su-summertime'       => 'summertime',
			'summertime'          => 'summertime',
			'sp-springtime'       => 'springtime',
			'springtime'          => 'springtime',
			'so-special-occasion' => 'special-occasion',
			'special-occasion'    => 'special-occasion',
			'wi-winter'           => 'winter',
			'wi-wintertime'       => 'winter',
			'wintertime'          => 'winter',
			'winter'              => 'winter',
			'au-autumnal'         => 'autumnal',
			'autumnal'            => 'autumnal',
		);

		return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
	}
}

if ( ! function_exists( 'uc_gallery_primary_drink_term' ) ) {
	/**
	 * Prefer a child drinks term (e.g. Seasonal → Summertime).
	 */
	function uc_gallery_primary_drink_term( $post_id ) {
		$terms = wp_get_post_terms( $post_id, 'drinks' );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		$seasonal_id = null;
		foreach ( $terms as $term ) {
			if ( isset( $term->slug ) && 'seasonal' === strtolower( $term->slug ) ) {
				$seasonal_id = (int) $term->term_id;
				break;
			}
		}

		if ( $seasonal_id ) {
			foreach ( $terms as $term ) {
				if ( (int) $term->parent === $seasonal_id ) {
					return $term;
				}
			}
		}

		foreach ( $terms as $term ) {
			if ( ! empty( $term->parent ) ) {
				return $term;
			}
		}

		return $terms[0];
	}
}

if ( ! function_exists( 'uc_gallery_normalize_search_text' ) ) {
	/**
	 * Lowercase / strip accents / collapse punctuation for inclusive substring matching.
	 */
	function uc_gallery_normalize_search_text( $text ) {
		if ( $text === null || $text === '' ) {
			return '';
		}

		$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( function_exists( 'remove_accents' ) ) {
			$text = remove_accents( $text );
		}
		$text = strtolower( $text );
		$text = preg_replace( "/[''`´]|[\x{2018}\x{2019}\x{2032}]|[\x{02BC}]/u", '', $text );
		$text = preg_replace( '/[^a-z0-9\s]+/u', ' ', $text );
		$text = preg_replace( '/\s+/', ' ', $text );

		return trim( $text );
	}
}

if ( ! function_exists( 'uc_gallery_build_search_text' ) ) {
	/**
	 * Inclusive haystack for gallery live-filter (letter substring / OR-on-space).
	 * Own logic — not the drinks-plugin carousel tokenizer.
	 */
	function uc_gallery_build_search_text( $post_id, $thumb_id, $terms, $primary_term, $css_category ) {
		$parts = array(
			get_the_title( $post_id ),
			get_post_field( 'post_excerpt', $post_id ),
			get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ),
			$css_category,
		);

		$thumb_post = get_post( $thumb_id );
		if ( $thumb_post ) {
			$parts[] = $thumb_post->post_title;
			$parts[] = $thumb_post->post_excerpt;
			$parts[] = $thumb_post->post_content;
		}

		if ( $primary_term ) {
			$parts[] = $primary_term->name;
			$parts[] = $primary_term->slug;
		}

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$parts[] = $term->name;
				$parts[] = $term->slug;
			}
		}

		foreach ( array( 'drink_color', 'drink_glass', 'drink_garnish1', 'drink_garnish2', 'drink_base', 'drink_ice' ) as $meta_key ) {
			$meta_value = get_post_meta( $post_id, $meta_key, true );
			if ( ! empty( $meta_value ) ) {
				$parts[] = $meta_value;
			}
		}

		$image_meta = wp_get_attachment_metadata( $thumb_id );
		if ( ! empty( $image_meta['image_meta'] ) ) {
			foreach ( array( 'title', 'caption', 'keywords' ) as $meta_key ) {
				if ( empty( $image_meta['image_meta'][ $meta_key ] ) ) {
					continue;
				}
				$value = $image_meta['image_meta'][ $meta_key ];
				if ( is_array( $value ) ) {
					$parts = array_merge( $parts, $value );
				} else {
					$parts[] = $value;
				}
			}
		}

		return uc_gallery_normalize_search_text( implode( ' ', array_filter( $parts, 'strlen' ) ) );
	}
}

if ( ! function_exists( 'uc_get_gallery_drink_items' ) ) {
	/**
	 * Build gallery data from published drink posts (one item per post w/ featured image).
	 *
	 * @param bool $shuffle Randomize order on each call.
	 * @return array{drinks: array, filter_terms: WP_Term[], css_categories: string[]}
	 */
	function uc_get_gallery_drink_items( $shuffle = true ) {
		global $drinks_plugin;

		$raw_posts = array();
		if ( isset( $drinks_plugin ) && is_object( $drinks_plugin ) && method_exists( $drinks_plugin, 'get_published_drink_posts_raw' ) ) {
			$raw_posts = $drinks_plugin->get_published_drink_posts_raw();
		} else {
			$query = new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'no_found_rows'  => true,
					'tax_query'      => array(
						array(
							'taxonomy' => 'drinks',
							'operator' => 'EXISTS',
						),
					),
				)
			);
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$raw_posts[] = get_post();
				}
				wp_reset_postdata();
			}
		}

		$gallery_drinks  = array();
		$term_slugs_used = array();
		$css_categories  = array();

		foreach ( $raw_posts as $post ) {
			$thumb_id = (int) get_post_thumbnail_id( $post->ID );
			if ( ! $thumb_id ) {
				continue;
			}

			$terms      = wp_get_post_terms( $post->ID, 'drinks' );
			$term_slugs = array();
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_slugs[] = $term->slug;
					$term_slugs_used[ $term->slug ] = $term;
				}
			}

			$primary_term = uc_gallery_primary_drink_term( $post->ID );
			$css_category = $primary_term ? uc_gallery_term_slug_to_css_id( $primary_term->slug ) : '';
			if ( $css_category ) {
				$css_categories[ $css_category ] = true;
			}

			$thumb_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
			if ( ! $thumb_alt ) {
				$thumb_alt = get_the_title( $post->ID );
			}

			$gallery_drinks[] = array(
				'id'            => $post->ID,
				'title'         => get_the_title( $post->ID ),
				'permalink'     => get_permalink( $post->ID ),
				'thumbnail'     => get_the_post_thumbnail_url( $post->ID, 'large' ),
				'thumbnail_id'  => $thumb_id,
				'thumbnail_alt' => $thumb_alt,
				'term_slugs'    => $term_slugs,
				'category_name' => $primary_term ? $primary_term->name : '',
				'css_category'  => $css_category,
				'search_text'   => uc_gallery_build_search_text( $post->ID, $thumb_id, $terms, $primary_term, $css_category ),
			);
		}

		if ( $shuffle && count( $gallery_drinks ) > 1 ) {
			shuffle( $gallery_drinks );
		}

		$filter_terms = array();
		foreach ( $term_slugs_used as $slug => $term ) {
			if ( 'seasonal' === $slug ) {
				continue;
			}
			$filter_terms[] = $term;
		}
		usort(
			$filter_terms,
			function ( $a, $b ) {
				return strcasecmp( $a->name, $b->name );
			}
		);

		return array(
			'drinks'         => $gallery_drinks,
			'filter_terms'   => $filter_terms,
			'css_categories' => array_keys( $css_categories ),
		);
	}
}
