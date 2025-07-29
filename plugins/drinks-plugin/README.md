# Drinks Plugin

A WordPress plugin for displaying drinks with LightBox and Carousel functionality using WordPress Interactivity API.

## Features

### Current Features
- Basic plugin structure
- "See Drinks" message display (proof of concept)
- **Lightbox functionality using WordPress Interactivity API**
- **Enhanced core image blocks with side panel Lightbox settings**
- Sample drinks gallery with 6 popular cocktails
- Responsive grid layout
- Click to open Lightbox with drink details
- Escape key to close Lightbox
- Click outside to close Lightbox

### Planned Features
- Carousel component for drinks display
- Interactive drink gallery with filtering
- Custom drink data management
- Advanced Lightbox features (navigation, zoom, etc.)

## Installation

1. Upload the `drinks-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The "See Drinks" message will appear in the top-right corner of your site
4. A drinks gallery with Lightbox functionality will be displayed on your site

## Usage

### Block Editor Integration
The plugin enhances **existing core image blocks** with Lightbox functionality:

1. **Add an image block** to your post/page
2. **Select the image block**
3. **Open the Block Settings panel** (right sidebar)
4. **Look for "Drinks Plugin Lightbox"** section
5. **Toggle "Enable Lightbox"** on/off
6. The setting appears in the **side panel** instead of the hover menu

### Lightbox Features
- **Works with existing WordPress image blocks**
- Click on any image to open the Lightbox
- View larger images with captions
- Press Escape key or click outside to close
- Responsive design that works on all devices
- Smooth animations and transitions
- **Side panel control** instead of hover menu

## Sample Drinks Included
- Mojito
- Margarita
- Martini
- Old Fashioned
- Negroni
- Gin & Tonic

## Development

This plugin uses WordPress Interactivity API for enhanced user experience and follows modern WordPress development practices. The Lightbox implementation is similar to WordPress Core but with custom side panel integration.

## Version History

- 1.0.0 - Initial release with basic structure and "See Drinks" message
- 1.1.0 - Added Lightbox functionality with sample drinks gallery
- 1.2.0 - Added block editor integration with side panel Lightbox settings
- 1.3.0 - Removed custom block, enhanced core image blocks only 