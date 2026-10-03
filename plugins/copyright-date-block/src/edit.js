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
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
/*import './editor.scss';*/

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {    //destructuring syntax :: props.attributes.startingYear
    const { startingYear, showStartYear, showCopyright } = attributes;    //destructuring syntax :: props.attributes.startingYear

    const currentYear = new Date().getFullYear().toString();
    
    // Build display text based on settings
    const copyrightWord = showCopyright ? 'Copyright ' : '';
    const yearRange = showStartYear && startingYear !== currentYear 
        ? `${startingYear} - ${currentYear}`
        : currentYear;
    const displayText = `${copyrightWord}© ${yearRange}`;
    
    return (
        <div { ...useBlockProps() }>
            <InspectorControls>
            <PanelBody title={ __( 'Settings', 'copyright-date-block' ) }>
                <TextControl
                    label={ __( 'Starting Year', 'copyright-date-block' ) }
                    value={ startingYear }
                    onChange={ ( newStartingYear ) => {
                        // update startingYear with newValue
                        setAttributes( { startingYear: newStartingYear } );
                    } }
                />
                <ToggleControl
                    label={ __( 'Display Start Year?', 'copyright-date-block' ) }
                    checked={ showStartYear }
                    onChange={ ( value ) => {
                        setAttributes( { showStartYear: value } );
                    } }
                    help={ __( 'Show the starting year in the copyright notice (e.g., "2000 - 2025" vs just "2025")', 'copyright-date-block' ) }
                />
                <ToggleControl
                    label={ __( 'Display Copyright?', 'copyright-date-block' ) }
                    checked={ showCopyright }
                    onChange={ ( value ) => {
                        setAttributes( { showCopyright: value } );
                    } }
                    help={ __( 'Show the word "Copyright" before the symbol (e.g., "Copyright © 2025" vs just "© 2025")', 'copyright-date-block' ) }
                />
            </PanelBody>
            </InspectorControls>
            <p>
                { displayText }
            </p>
        </div>
    );
}
