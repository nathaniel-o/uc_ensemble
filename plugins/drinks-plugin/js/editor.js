/**
 * WordPress dependencies
 */
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, createElement: el } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;
const { __ } = wp.i18n;

/**
 * Add Lightbox setting to core/image block
 */
addFilter(
    'editor.BlockEdit',
    'drinks-plugin/with-lightbox-setting',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            // Only apply to core/image blocks
            if (props.name !== 'core/image') {
                return el(BlockEdit, props);
            }
            
            const { attributes, setAttributes } = props;
            const { lightboxEnabled = true } = attributes;
            
            return el(Fragment, {},
                el(BlockEdit, props),
                el(InspectorControls, {},
                    el(PanelBody, { 
                        title: __('Drinks Plugin Lightbox', 'drinks-plugin'),
                        initialOpen: false 
                    },
                        el(ToggleControl, {
                            label: __('Enable Lightbox', 'drinks-plugin'),
                            help: __('Show this image in a Lightbox when clicked (Drinks Plugin)', 'drinks-plugin'),
                            checked: lightboxEnabled,
                            onChange: (value) => setAttributes({ lightboxEnabled: value })
                        })
                    )
                )
            );
        };
    })
);

// Add Lightbox attribute to core/image block
addFilter(
    'blocks.registerBlockType',
    'drinks-plugin/add-lightbox-attribute',
    (settings, name) => {
        if (name !== 'core/image') {
            return settings;
        }
        
        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                lightboxEnabled: {
                    type: 'boolean',
                    default: true
                }
            }
        };
    }
); 