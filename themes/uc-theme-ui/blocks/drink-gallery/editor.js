/**
 * UC Drink Gallery — editor registration (required for block inserter).
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var createElement = wp.element.createElement;
	var __ = wp.i18n.__;

	registerBlockType( 'uc/drink-gallery', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( {
				className: 'uc-drink-gallery-editor',
			} );

			return createElement(
				'div',
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __( 'Gallery settings', 'untouchedcocktails-theme' ), initialOpen: true },
						createElement( RangeControl, {
							label: __( 'Columns', 'untouchedcocktails-theme' ),
							value: attributes.columns || 4,
							onChange: function ( value ) {
								setAttributes( { columns: value } );
							},
							min: 1,
							max: 6,
						} ),
						createElement( ToggleControl, {
							label: __( 'Shuffle on each page load', 'untouchedcocktails-theme' ),
							checked: attributes.shuffle !== false,
							onChange: function ( value ) {
								setAttributes( { shuffle: value } );
							},
						} )
					)
				),
				createElement(
					'div',
					blockProps,
					createElement(
						'div',
						{
							style: {
								border: '2px dashed #241547',
								borderRadius: '8px',
								padding: '2rem',
								textAlign: 'center',
								background: 'rgba(255,255,255,0.6)',
							},
						},
						createElement( 'strong', null, __( 'UC Drink Gallery', 'untouchedcocktails-theme' ) ),
						createElement(
							'p',
							{ style: { margin: '0.5rem 0 0', fontSize: '0.9em', opacity: 0.75 } },
							__(
								'Published drink posts render here on the frontend — randomized, filterable, one image per post.',
								'untouchedcocktails-theme'
							)
						)
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
