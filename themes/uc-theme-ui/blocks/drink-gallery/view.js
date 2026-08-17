/**
 * UC Drink Gallery — live-filter from the site search bar (keyup only).
 *
 * Inclusive matching (gallery-owned, not drinks-plugin tokenizer):
 *   a. letter substring — "sum" matches "summertime"
 *   b. space = OR — "bourbon cherry" matches items with either term
 *
 * Does not alter form submit / Enter / Search button (carousel summon).
 */
( function () {
	'use strict';

	function normalize( text ) {
		if ( text == null || text === '' ) {
			return '';
		}
		var value = String( text );
		try {
			value = value.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, '' );
		} catch ( e ) {
			/* older browsers */
		}
		value = value.toLowerCase();
		value = value.replace( /[''`´\u2018\u2019\u2032\u02BC]/g, '' );
		value = value.replace( /[^a-z0-9\s]+/g, ' ' );
		value = value.replace( /\s+/g, ' ' );
		return value.trim();
	}

	/**
	 * Split query on spaces. Empty query → show all.
	 * Multiple terms → OR (any term substring-matches haystack).
	 */
	function parseInclusiveTerms( query ) {
		var normalized = normalize( query );
		if ( ! normalized ) {
			return [];
		}
		return normalized.split( ' ' ).filter( Boolean );
	}

	function itemMatches( haystack, terms ) {
		if ( ! terms.length ) {
			return true;
		}
		if ( ! haystack ) {
			return false;
		}
		for ( var i = 0; i < terms.length; i++ ) {
			if ( haystack.indexOf( terms[ i ] ) !== -1 ) {
				return true;
			}
		}
		return false;
	}

	function applyFilter( root, query ) {
		var terms = parseInclusiveTerms( query );
		var items = root.querySelectorAll( '.gallery-drink-item' );
		var visible = 0;

		items.forEach( function ( item ) {
			var haystack = item.getAttribute( 'data-search-text' ) || '';
			var match = itemMatches( haystack, terms );
			item.classList.toggle( 'is-filtered-out', ! match );
			item.hidden = ! match;
			if ( match ) {
				visible += 1;
			}
		} );

		var empty = root.querySelector( '.drink-gallery-filter-empty' );
		if ( empty ) {
			empty.hidden = visible > 0 || ! terms.length;
		}
	}

	function findSearchInputs() {
		return document.querySelectorAll(
			'.wp-block-search__input, form[role="search"] input[type="search"]'
		);
	}

	function bindGalleries() {
		var galleries = document.querySelectorAll( '.uc-drink-gallery-page[data-uc-gallery-filter="1"]' );
		if ( ! galleries.length ) {
			return;
		}

		var roots = [];
		galleries.forEach( function ( root ) {
			if ( root.dataset.ucFilterBound === '1' ) {
				return;
			}
			root.dataset.ucFilterBound = '1';
			roots.push( root );
		} );

		if ( ! roots.length ) {
			return;
		}

		function onKeyup( event ) {
			var input = event.target;
			if ( ! input || input.type !== 'search' ) {
				return;
			}
			if ( ! input.classList.contains( 'wp-block-search__input' ) && ! input.closest( 'form[role="search"]' ) ) {
				return;
			}
			var query = input.value;
			roots.forEach( function ( root ) {
				applyFilter( root, query );
			} );
		}

		findSearchInputs().forEach( function ( input ) {
			if ( input.dataset.ucGalleryFilterBound === '1' ) {
				return;
			}
			input.dataset.ucGalleryFilterBound = '1';
			// keyup only — leave submit / Enter / Search button to theme carousel handler
			input.addEventListener( 'keyup', onKeyup );
			input.addEventListener( 'search', onKeyup ); // clear (x) control
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', bindGalleries );
	} else {
		bindGalleries();
	}
} )();
