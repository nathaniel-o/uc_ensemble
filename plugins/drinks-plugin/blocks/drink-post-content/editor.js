/**
 * Drink Post Content — image picker + metadata list.
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element || ! wp.components ) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var Button = wp.components.Button;
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
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var imageId = attributes.imageId || 0;
			var imageUrl = attributes.imageUrl || '';
			var imageAlt = attributes.imageAlt || '';

			var blockProps = useBlockProps( {
				className: 'drinks-drink-post-content-editor pop-off',
			} );

			function onSelectImage( media ) {
				if ( ! media || ! media.id ) {
					return;
				}
				setAttributes( {
					imageId: media.id,
					imageUrl: media.url || '',
					imageAlt: media.alt || media.title || '',
				} );
			}

			function onRemoveImage() {
				setAttributes( {
					imageId: 0,
					imageUrl: '',
					imageAlt: '',
				} );
			}

			var imageArea = createElement(
				MediaUploadCheck,
				null,
				createElement( MediaUpload, {
					onSelect: onSelectImage,
					allowedTypes: [ 'image' ],
					value: imageId,
					render: function ( { open } ) {
						if ( imageUrl ) {
							return createElement(
								'figure',
								{ className: 'wp-block-media-text__media drinks-drink-post-content__media' },
								createElement( 'img', {
									src: imageUrl,
									alt: imageAlt,
									className: imageId ? 'wp-image-' + imageId : '',
									onClick: open,
									style: { cursor: 'pointer', width: '100%', height: 'auto', display: 'block' },
								} ),
								createElement(
									'div',
									{ className: 'drinks-drink-post-content__media-actions' },
									createElement(
										Button,
										{ variant: 'secondary', onClick: open },
										__( 'Replace image', 'drinks-plugin' )
									),
									createElement(
										Button,
										{ variant: 'link', isDestructive: true, onClick: onRemoveImage },
										__( 'Remove', 'drinks-plugin' )
									)
								)
							);
						}

						return createElement(
							'figure',
							{ className: 'wp-block-media-text__media drinks-drink-post-content__media is-empty' },
							createElement(
								Button,
								{
									variant: 'secondary',
									onClick: open,
									className: 'drinks-drink-post-content__select-image',
								},
								__( 'Select drink image', 'drinks-plugin' )
							)
						);
					},
				} )
			);

			return createElement(
				'div',
				blockProps,
				createElement(
					'div',
					{
						className: 'wp-block-media-text alignwide is-stacked-on-mobile drinks-drink-post-content__layout',
					},
					imageArea,
					createElement(
						'div',
						{ className: 'wp-block-media-text__content' },
						createElement(
							'p',
							{
								className: 'drinks-drink-post-content__title-hint',
							},
							__( 'Post title (auto on frontend)', 'drinks-plugin' )
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
