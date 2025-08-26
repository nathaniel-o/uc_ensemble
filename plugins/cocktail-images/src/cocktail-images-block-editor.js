/**
 * Cocktail Images Block Editor Integration
 * Adds inspector controls to image blocks
 */

(function() {
    'use strict';
    
    console.log('Cocktail Images Block Editor script loaded');

    // Wait for WordPress to be ready
    wp.domReady(function() {
        console.log('Cocktail Images: DOM Ready');
        
        // Check if required WordPress components are available
        if (!wp.hooks || !wp.compose || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n) {
            console.error('Cocktail Images: Required WordPress components not available');
            return;
        }
        
        const { addFilter } = wp.hooks;
        const { createHigherOrderComponent } = wp.compose;
        const { Fragment } = wp.element;
        const { InspectorControls } = wp.blockEditor;
        const { PanelBody, PanelRow, ToggleControl } = wp.components;
        const { __ } = wp.i18n;

        // Add custom attributes to image blocks
        addFilter(
            'blocks.registerBlockType',
            'cocktail-images/image-block-attributes',
            function(settings, name) {
                if (name === 'core/image') {
                    return {
                        ...settings,
                        attributes: {
                            ...settings.attributes,
                            cocktailNothing: {
                                type: 'boolean',
                                default: false,
                            },
                            cocktailPopOut: {
                                type: 'boolean',
                                default: false,
                            },
                        },
                    };
                }
                return settings;
            }
        );

        // Add custom controls to image block inspector
        const withCocktailImageControls = createHigherOrderComponent(function(BlockEdit) {
            return function(props) {
                // Only apply to image blocks
                if (props.name !== 'core/image') {
                    return wp.element.createElement(BlockEdit, props);
                }

                const { attributes, setAttributes } = props;
                const { cocktailNothing, cocktailPopOut } = attributes;

                return wp.element.createElement(Fragment, {},
                    wp.element.createElement(BlockEdit, props),
                    wp.element.createElement(InspectorControls, {},
                        wp.element.createElement(PanelBody, {
                            title: __('Cocktail Images', 'cocktail-images'),
                            initialOpen: false
                        },
                            wp.element.createElement(PanelRow, {},
                                wp.element.createElement(ToggleControl, {
                                    label: __('Nothing', 'cocktail-images'),
                                    help: __('This button does nothing', 'cocktail-images'),
                                    checked: cocktailNothing,
                                    onChange: function(value) {
                                        setAttributes({ cocktailNothing: value });
                                    }
                                })
                            ),
                            wp.element.createElement(PanelRow, {},
                                wp.element.createElement(ToggleControl, {
                                    label: __('Pop Out', 'cocktail-images'),
                                    help: __('Enable pop out effect for this image', 'cocktail-images'),
                                    checked: cocktailPopOut,
                                    onChange: function(value) {
                                        setAttributes({ cocktailPopOut: value });
                                    }
                                })
                            )
                        )
                    )
                );
            };
        }, 'withCocktailImageControls');

        addFilter(
            'editor.BlockEdit',
            'cocktail-images/with-cocktail-image-controls',
            withCocktailImageControls
        );

        // Add custom class to image blocks when pop out is enabled (nothing button does nothing)
        addFilter(
            'blocks.getSaveContent.extraProps',
            'cocktail-images/image-block-save-props',
            function(props, blockType, attributes) {
                if (blockType.name === 'core/image') {
                    let className = props.className || '';
                    
                    if (attributes.cocktailPopOut) {
                        className += ' cocktail-pop-out';
                    }
                    
                    return {
                        ...props,
                        className: className.trim()
                    };
                }
                return props;
            }
        );

        console.log('Cocktail Images Block Editor integration loaded');
        
        // Test if filters are working
        console.log('Cocktail Images: Filters registered successfully');
    });

})();
