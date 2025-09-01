/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, createElement } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Initialize drinks plugin
console.log('Drinks Plugin: Block editor integration loaded');

/**
 * Add Pop Out and Nothing settings to core/image block
 */
addFilter(
    'editor.BlockEdit',
    'drinks-plugin/with-cocktail-controls',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            // Only apply to core/image blocks
            if (props.name !== 'core/image') {
                return createElement(BlockEdit, props);
            }
            
            const { attributes, setAttributes } = props;
            const { cocktailPopOut = true, cocktailNothing = false } = attributes;
            
            // Handle mutually exclusive toggles
            const handleCarouselChange = (value) => {
                if (value) {
                    // Enable carousel, disable pop out
                    setAttributes({ 
                        cocktailPopOut: true, 
                        cocktailNothing: false 
                    });
                } else {
                    // Disable carousel, enable pop out
                    setAttributes({ 
                        cocktailPopOut: false, 
                        cocktailNothing: true 
                    });
                }
            };
            
            const handlePopOutChange = (value) => {
                if (value) {
                    // Enable pop out, disable carousel
                    setAttributes({ 
                        cocktailNothing: true, 
                        cocktailPopOut: false 
                    });
                } else {
                    // Disable pop out, enable carousel
                    setAttributes({ 
                        cocktailNothing: false, 
                        cocktailPopOut: true 
                    });
                }
            };
            
            return createElement(Fragment, {},
                createElement(BlockEdit, props),
                createElement(InspectorControls, {},
                    createElement(PanelBody, { 
                        title: __('Drinks Plugin Settings', 'drinks-plugin'),
                        initialOpen: false 
                    },
                        createElement(PanelRow, {},
                            createElement(ToggleControl, {
                                label: __('Carousel', 'drinks-plugin'),
                                help: __('Enable carousel functionality for this image', 'drinks-plugin'),
                                checked: cocktailPopOut,
                                onChange: handleCarouselChange
                            })
                        ),
                        createElement(PanelRow, {},
                            createElement(ToggleControl, {
                                label: __('Pop Out', 'drinks-plugin'),
                                help: __('Enable core lightbox functionality', 'drinks-plugin'),
                                checked: cocktailNothing,
                                onChange: handlePopOutChange
                            })
                        )
                    )
                )
            );
        };
    })
);

// Add cocktail attributes to core/image block
addFilter(
    'blocks.registerBlockType',
    'drinks-plugin/add-cocktail-attributes',
    (settings, name) => {
        if (name !== 'core/image') {
            return settings;
        }
        
        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                cocktailPopOut: {
                    type: 'boolean',
                    default: true  // Changed to true - Carousel enabled by default
                },
                cocktailNothing: {
                    type: 'boolean',
                    default: false
                }
            }
        };
    }
);

// Add custom class and lightbox attributes to image blocks
addFilter(
    'blocks.getSaveContent.extraProps',
    'drinks-plugin/image-block-save-props',
    function(props, blockType, attributes) {
        if (blockType.name === 'core/image') {
            let className = props.className || '';
            let newProps = { ...props };
            
            // Remove existing cocktail classes first
            className = className.replace(/\bcocktail-nothing\b/g, '').replace(/\bcocktail-carousel\b/g, '');
            
            // Add classes based on current state
            if (attributes.cocktailNothing) {
                className += ' cocktail-pop-out';
                newProps['data-wp-lightbox'] = 'true';
                newProps['data-wp-lightbox-group'] = 'drinks-plugin';
            }
            
            if (attributes.cocktailPopOut) {
                className += ' cocktail-carousel';
            }
            
            newProps.className = className.trim();
            return newProps;
        }
        return props;
    }
);

// Ensure attributes are properly loaded and handled
addFilter(
    'blocks.getBlockAttributes',
    'drinks-plugin/ensure-attributes',
    function(attributes, blockType) {
        if (blockType.name === 'core/image') {
            // Ensure our attributes exist with proper defaults
            const enhancedAttributes = {
                ...attributes,
                cocktailNothing: attributes.cocktailNothing === true,
                cocktailPopOut: attributes.cocktailPopOut === true,
            };
            
            return enhancedAttributes;
        }
        return attributes;
    }
);

// Add filter to parse attributes from block content
addFilter(
    'blocks.getBlockAttributes',
    'drinks-plugin/parse-block-attributes',
    function(attributes, blockType, content) {
        if (blockType.name === 'core/image') {
            // Parse data attributes from the block content if they exist
            if (content && typeof content === 'string') {
                const popOutMatch = content.match(/data-cocktail-pop-out="([^"]*)"/);
                const nothingMatch = content.match(/data-cocktail-nothing="([^"]*)"/);
                
                if (popOutMatch) {
                    attributes.cocktailPopOut = popOutMatch[1] === 'true';
                }
                if (nothingMatch) {
                    attributes.cocktailNothing = nothingMatch[1] === 'true';
                }
            }
        }
        return attributes;
    }
);

// Add filter to ensure attributes are properly serialized
addFilter(
    'blocks.getSaveContent.extraProps',
    'drinks-plugin/save-attributes-as-data',
    function(props, blockType, attributes) {
        if (blockType.name === 'core/image') {
            // Add data attributes to preserve our custom attributes
            return {
                ...props,
                'data-cocktail-pop-out': attributes.cocktailPopOut ? 'true' : 'false',
                'data-cocktail-nothing': attributes.cocktailNothing ? 'true' : 'false',
            };
        }
        return props;
    }
);

// Add editor view styling
addFilter(
    'editor.BlockListBlock',
    'drinks-plugin/editor-styling',
    function(BlockListBlock) {
        return function(props) {
            if (props.name === 'core/image') {
                const { attributes } = props;
                const originalElement = wp.element.createElement(BlockListBlock, props);
                
                // Remove existing cocktail classes and add current ones
                let className = originalElement.props.className || '';
                className = className.replace(/\bcocktail-nothing\b/g, '').replace(/\bcocktail-carousel\b/g, '');
                
                if (attributes.cocktailPopOut) {
                    className += ' cocktail-carousel';
                }
                if (attributes.cocktailNothing) {
                    className += ' cocktail-pop-out';
                }
                
                className = className.trim();
                
                if (className !== originalElement.props.className) {
                    return wp.element.cloneElement(originalElement, {
                        className: className
                    });
                }
            }
            return wp.element.createElement(BlockListBlock, props);
        };
    }
);
