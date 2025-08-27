# Drinks Plugin

A WordPress plugin for displaying drinks with Pop Out and Lightbox functionality using WordPress block editor integration.

## Features

### Current Features
- **Enhanced core image blocks** with side panel settings
- **Pop Out toggle** - Enable pop out effect for images
- **Nothing toggle** - Enable WordPress core lightbox functionality
- **Real-time editor feedback** - See changes immediately in the block editor
- **Persistent state** - Settings saved and restored across page refreshes
- **Clean CSS class management** - Classes added/removed properly when toggled

### Technical Features
- WordPress block editor integration
- Custom attributes for image blocks
- CSS class management
- Data attribute serialization
- Editor view styling

## Installation

1. Upload the `drinks-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically enhance image blocks with new controls

## Usage

### Block Editor Integration
The plugin enhances **existing core image blocks** with Pop Out and Lightbox functionality:

1. **Add an image block** to your post/page
2. **Select the image block**
3. **Open the Block Settings panel** (right sidebar)
4. **Look for "Drinks Plugin Settings"** section
5. **Toggle "Pop Out"** on/off for pop out effects
6. **Toggle "Nothing"** on/off for core lightbox functionality

### How It Works

#### Pop Out Toggle
- **ON**: Adds `cocktail-pop-out` CSS class to the image
- **OFF**: Removes `cocktail-pop-out` CSS class from the image
- **Use Case**: Apply custom CSS animations or effects

#### Nothing Toggle (Core Lightbox)
- **ON**: Adds `cocktail-nothing` CSS class and WordPress core lightbox attributes
- **OFF**: Removes lightbox functionality
- **Use Case**: Enable WordPress's built-in lightbox for image galleries

### CSS Classes Applied
- `.cocktail-pop-out` - Applied when Pop Out is enabled
- `.cocktail-nothing` - Applied when Nothing (lightbox) is enabled
- `data-wp-lightbox="true"` - WordPress core lightbox attribute
- `data-wp-lightbox-group="drinks-plugin"` - Groups lightbox images

## Development

This plugin uses modern WordPress block editor APIs and follows WordPress development best practices:

- **Block Editor Integration**: Uses `wp.hooks`, `wp.compose`, `wp.element`
- **Attribute Management**: Custom boolean attributes with proper defaults
- **State Persistence**: Attributes saved as data attributes in HTML
- **Editor Feedback**: Real-time visual updates in the editor

## Version History

- 1.0.0 - Initial release with basic structure and "See Drinks" message
- 1.1.0 - Added Lightbox functionality with sample drinks gallery
- 1.2.0 - Added block editor integration with side panel Lightbox settings
- 1.3.0 - Removed custom block, enhanced core image blocks only
- 2.0.0 - **MAJOR UPDATE**: Removed lightbox/carousel, added Pop Out and Nothing toggles

## Technical Notes

- **WordPress Version**: Requires WordPress 5.0+ (Gutenberg)
- **Dependencies**: wp-blocks, wp-element, wp-editor, wp-components, wp-i18n, wp-hooks, wp-compose
- **File Structure**: Clean separation of concerns with dedicated JS and CSS files
- **Performance**: Lightweight, no external dependencies 