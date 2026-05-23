/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 * 
 * Mirrors wp:navigation-link structure for the Home button.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { label, url, description, title } = attributes;

	const blockProps = useBlockProps( {
		className: 'wp-block-navigation-item wp-block-navigation-link uc-home-button menu-item menu-item-type-post_type menu-item-object-page',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Link Settings', 'home-button' ) }>
					<TextControl
						label={ __( 'Label', 'home-button' ) }
						value={ label }
						onChange={ ( newLabel ) => {
							setAttributes( { label: newLabel } );
						} }
						help={ __( 'The text displayed next to the logo.', 'home-button' ) }
					/>
					<TextControl
						label={ __( 'URL', 'home-button' ) }
						value={ url }
						onChange={ ( newUrl ) => {
							setAttributes( { url: newUrl } );
						} }
						help={ __( 'The link destination. Leave empty to use site home URL.', 'home-button' ) }
						placeholder="https://example.com/welcome/"
					/>
					<TextControl
						label={ __( 'Title Attribute', 'home-button' ) }
						value={ title }
						onChange={ ( newTitle ) => {
							setAttributes( { title: newTitle } );
						} }
						help={ __( 'Optional tooltip text on hover.', 'home-button' ) }
					/>
					<TextControl
						label={ __( 'Description', 'home-button' ) }
						value={ description }
						onChange={ ( newDesc ) => {
							setAttributes( { description: newDesc } );
						} }
						help={ __( 'Optional description for accessibility.', 'home-button' ) }
					/>
				</PanelBody>
			</InspectorControls>
			<li { ...blockProps }>
				<a
					href={ url || '#' }
					className="wp-block-navigation-item__content"
					rel="home"
					onClick={ ( e ) => e.preventDefault() }
				>
					<span className="wp-block-site-logo">
						<span className="custom-logo-placeholder" style={ {
							display: 'inline-block',
							width: '40px',
							height: '40px',
							backgroundColor: '#ddd',
							borderRadius: '4px',
							verticalAlign: 'middle',
						} } />
					</span>
					<span className="wp-block-navigation-item__label">
						{ label || __( 'Home', 'home-button' ) }
					</span>
				</a>
			</li>
		</>
	);
}
