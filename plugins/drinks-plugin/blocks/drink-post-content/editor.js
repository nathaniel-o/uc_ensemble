/**
 * Drink Post Content — editor registration with editable metadata list.
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var createElement = wp.element.createElement;
	var __ = wp.i18n.__;

	var LIST_TEMPLATE = [
		[
			'core/list',
			{
				ordered: false,
				values:
					'<li><em>Category</em>: </li>' +
					'<li><em>Color</em>: </li>' +
					'<li><em>Glass</em>: </li>' +
					'<li><em>Garnish</em>: </li>' +
					'<li><em>Base</em>: </li>' +
					'<li><em>Ice</em>: </li>',
			},
		],
	];

	registerBlockType( 'drinks/drink-post-content', {
		edit: function () {
			var blockProps = useBlockProps( {
				className: 'drinks-drink-post-content-editor pop-off',
			} );

			return createElement(
				'div',
				blockProps,
				createElement(
					'div',
					{
						className: 'wp-block-media-text alignwide is-stacked-on-mobile',
						style: {
							display: 'grid',
							gridTemplateColumns: '1fr 1fr',
							gap: '1rem',
							alignItems: 'start',
						},
					},
					createElement(
						'figure',
						{
							className: 'wp-block-media-text__media',
							style: {
								border: '2px dashed #ccc',
								minHeight: '160px',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'center',
								margin: 0,
							},
						},
						createElement(
							'span',
							{ style: { opacity: 0.65, fontSize: '0.9em' } },
							__( 'Featured image (auto on frontend)', 'drinks-plugin' )
						)
					),
					createElement(
						'div',
						{ className: 'wp-block-media-text__content' },
						createElement(
							'p',
							{
								style: {
									marginTop: 0,
									fontWeight: 600,
									fontSize: '1.1em',
								},
							},
							__( 'Post title (auto on frontend)', 'drinks-plugin' )
						),
						createElement(
							'p',
							{
								style: {
									margin: '0 0 0.75rem',
									fontSize: '0.85em',
									opacity: 0.75,
								},
							},
							__(
								'Edit the list below, or leave placeholder rows — drink metadata fills in on the frontend. Each value summons a filtered carousel when clicked.',
								'drinks-plugin'
							)
						),
						createElement( InnerBlocks, {
							allowedBlocks: [ 'core/list' ],
							template: LIST_TEMPLATE,
							templateLock: false,
						} )
					)
				)
			);
		},
		save: function () {
			return createElement( InnerBlocks.Content );
		},
	} );
} )( window.wp );
