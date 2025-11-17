/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/compose":
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["compose"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ ((module) => {

module.exports = window["wp"]["hooks"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__);
/**
 * WordPress dependencies
 */







// Initialize drinks plugin
console.log('Drinks Plugin: Block editor integration loaded');

/**
 * Add Pop Out and Nothing settings to core/image block
 */
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('editor.BlockEdit', 'drinks-plugin/with-cocktail-controls', (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_1__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    // Only apply to core/image blocks
    if (props.name !== 'core/image') {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(BlockEdit, props);
    }
    const {
      attributes,
      setAttributes
    } = props;
    const {
      cocktailCarousel = false,
      cocktailPopOut = true
    } = attributes;

    // Handle mutually exclusive toggles (only disable the other if it's active)
    const handleCarouselChange = value => {
      if (value) {
        // Enable carousel, disable pop out only if it's currently active
        if (cocktailPopOut) {
          setAttributes({
            cocktailCarousel: true,
            cocktailPopOut: false
          });
        } else {
          setAttributes({
            cocktailCarousel: true
          });
        }
      } else {
        // Disable carousel, don't change pop out state
        setAttributes({
          cocktailCarousel: false
        });
      }
    };
    const handlePopOutChange = value => {
      if (value) {
        // Enable pop out, disable carousel only if it's currently active
        if (cocktailCarousel) {
          setAttributes({
            cocktailPopOut: true,
            cocktailCarousel: false
          });
        } else {
          setAttributes({
            cocktailPopOut: true
          });
        }
      } else {
        // Disable pop out, don't change carousel state
        setAttributes({
          cocktailPopOut: false
        });
      }
    };
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.Fragment, {}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(BlockEdit, props), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, {}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)('Drinks Plugin Settings', 'drinks-plugin'),
      initialOpen: false
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelRow, {}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)('Carousel', 'drinks-plugin'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)('Enable carousel functionality for this image', 'drinks-plugin'),
      checked: cocktailCarousel,
      onChange: handleCarouselChange
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelRow, {}, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)('Pop Out', 'drinks-plugin'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)('Enable core lightbox functionality', 'drinks-plugin'),
      checked: cocktailPopOut,
      onChange: handlePopOutChange
    })))));
  };
}));

// Add cocktail attributes to core/image block
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('blocks.registerBlockType', 'drinks-plugin/add-cocktail-attributes', (settings, name) => {
  if (name !== 'core/image') {
    return settings;
  }
  return {
    ...settings,
    attributes: {
      ...settings.attributes,
      cocktailCarousel: {
        type: 'boolean',
        default: false
      },
      cocktailPopOut: {
        type: 'boolean',
        default: true // Pop Out enabled by default
      }
    }
  };
});

// Add custom class and lightbox attributes to image blocks
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('blocks.getSaveContent.extraProps', 'drinks-plugin/image-block-save-props', function (props, blockType, attributes) {
  if (blockType.name === 'core/image') {
    let className = props.className || '';
    let newProps = {
      ...props
    };

    // Remove existing cocktail classes first
    className = className.replace(/\bcocktail-nothing\b/g, '').replace(/\bcocktail-carousel\b/g, '');

    // Add classes based on current state
    if (attributes.cocktailPopOut) {
      className += ' cocktail-pop-out';
      newProps['data-wp-lightbox'] = 'true';
      newProps['data-wp-lightbox-group'] = 'drinks-plugin';
    }
    if (attributes.cocktailCarousel) {
      className += ' cocktail-carousel';
    }
    newProps.className = className.trim();
    return newProps;
  }
  return props;
});

// Ensure attributes are properly loaded and handled
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('blocks.getBlockAttributes', 'drinks-plugin/ensure-attributes', function (attributes, blockType) {
  if (blockType.name === 'core/image') {
    // Ensure our attributes exist with proper defaults
    const enhancedAttributes = {
      ...attributes,
      cocktailPopOut: attributes.cocktailPopOut === true,
      cocktailCarousel: attributes.cocktailCarousel === true
    };
    return enhancedAttributes;
  }
  return attributes;
});

// Add filter to parse attributes from block content
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('blocks.getBlockAttributes', 'drinks-plugin/parse-block-attributes', function (attributes, blockType, content) {
  if (blockType.name === 'core/image') {
    // Parse data attributes from the block content if they exist
    if (content && typeof content === 'string') {
      const carouselMatch = content.match(/data-cocktail-carousel="([^"]*)"/);
      const popOutMatch = content.match(/data-cocktail-pop-out="([^"]*)"/);
      if (carouselMatch) {
        attributes.cocktailCarousel = carouselMatch[1] === 'true';
      }
      if (popOutMatch) {
        attributes.cocktailPopOut = popOutMatch[1] === 'true';
      }
    }
  }
  return attributes;
});

// Add filter to ensure attributes are properly serialized
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('blocks.getSaveContent.extraProps', 'drinks-plugin/save-attributes-as-data', function (props, blockType, attributes) {
  if (blockType.name === 'core/image') {
    // Add data attributes to preserve our custom attributes
    return {
      ...props,
      'data-cocktail-carousel': attributes.cocktailCarousel ? 'true' : 'false',
      'data-cocktail-pop-out': attributes.cocktailPopOut ? 'true' : 'false'
    };
  }
  return props;
});

// Add editor view styling
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('editor.BlockListBlock', 'drinks-plugin/editor-styling', function (BlockListBlock) {
  return function (props) {
    if (props.name === 'core/image') {
      const {
        attributes
      } = props;
      const originalElement = wp.element.createElement(BlockListBlock, props);

      // Remove existing cocktail classes and add current ones
      let className = originalElement.props.className || '';
      className = className.replace(/\bcocktail-nothing\b/g, '').replace(/\bcocktail-carousel\b/g, '');
      if (attributes.cocktailCarousel) {
        className += ' cocktail-carousel';
      }
      if (attributes.cocktailPopOut) {
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
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map