# Drinks Plugin

A modern WordPress plugin for enhanced image display with Pop Out effects, Core Lightbox integration, automatic dimension analysis for aspect ratio management, and **complete carousel functionality migrated from cocktail-images plugin**.

## 🚨 **Migration Notice**
This plugin now contains **ALL carousel and drink management functionality** that was previously in the cocktail-images plugin. The cocktail-images plugin has been cleaned of these features and now focuses solely on image matching and cycling.

## 🚀 Features

### Core Functionality
- **Enhanced Image Blocks** - Extends WordPress core image blocks with additional controls
- **Carousel Functionality** - Toggle carousel functionality for images
- **Core Lightbox Integration** - Seamless integration with WordPress's built-in lightbox
- **Automatic Dimension Analysis** - `ucPortraitLandscape` function for aspect ratio management
- **Drink Management** - Query and retrieve drink posts with taxonomy support
- **Carousel Generation** - Complete carousel system migrated from cocktail-images plugin
- **AJAX Handlers** - Filter carousel by search terms with live updates
- **Metadata Generation** - Generate drink metadata lists (category, color, glass, garnish, base, ice)
- **Modern Build System** - Uses `@wordpress/scripts` for development and production builds

### Technical Features
- **WordPress Block Editor Integration** - Custom inspector controls for image blocks
- **Modern JavaScript** - ES6 modules with `@wordpress/scripts` build system
- **CSS Management** - Centralized styling with high-specificity overrides
- **Dynamic Content Support** - MutationObserver for dynamically added images
- **Responsive Design** - Mobile-friendly lightbox and image handling

## 📦 Installation

### Prerequisites
- WordPress 5.0+ (Gutenberg)
- Node.js and npm (for development)

### Installation Steps
1. Upload the `drinks-plugin` folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. For development: Run `npm install` and `npm run dev`
4. For production: Run `npm run build`

## 🛠️ Development

### Build System
This plugin uses `@wordpress/scripts` for modern development:

```bash
# Install dependencies
npm install

# Development mode with hot reloading
npm run dev

# Production build
npm run build

# Start development server
npm run start

# Linting and formatting
npm run lint:js
npm run lint:css
npm run format
```

### Project Structure
```
drinks-plugin/
├── src/
│   ├── index.js          # Block editor enhancements
│   ├── frontend.js       # Frontend functionality
│   └── style.css         # Plugin styles
├── build/                # Compiled assets (generated)
│   ├── index.js          # Built editor script
│   ├── frontend.js       # Built frontend script
│   ├── index.asset.php   # Editor dependencies
│   └── frontend.asset.php # Frontend dependencies
├── js/                   # Legacy source files
├── css/                  # Legacy source files
├── package.json          # Dependencies and scripts
├── webpack.config.js     # Build configuration
├── .gitignore           # Git ignore rules
└── drinks-plugin.php    # Main plugin file
```

## 🎯 Usage

### Block Editor Integration
The plugin enhances **WordPress core image blocks** with additional controls:

1. **Add an image block** to your post/page
2. **Select the image block**
3. **Open Block Settings panel** (right sidebar)
4. **Find "Drinks Plugin Settings"** section
5. **Toggle controls** as needed

### Drink Management Functions
The plugin provides comprehensive drink management functions:

```php
// Query drink posts with taxonomy
$drink_query = $plugin->uc_drink_query();

// Get drink posts as array
$drink_posts = $plugin->uc_get_drinks();

// Generate drink metadata list
$metadata = $plugin->uc_generate_metadata_list($post_id);
```

### Carousel Functions
The plugin provides carousel generation functions:

```php
// Generate random carousel
$carousel = $plugin->uc_random_carousel($drink_posts, 5, 0, 1);

// Generate filtered carousel
$filtered_carousel = $plugin->uc_filter_carousel('margarita', $drink_posts, 3, 0, 1, 1);

// Generate slideshow HTML
$slideshow = $plugin->generate_slideshow_slides($images, $show_titles, $show_content);

// Generate single slide
$slide = $plugin->generate_single_slide($image, $index, $is_duplicate, $show_titles, $show_content);
```

### AJAX Endpoints
- `wp_ajax_filter_carousel` / `wp_ajax_nopriv_filter_carousel` - Filter carousel by search terms

### Global Functions
The plugin provides global wrapper functions for easy access from anywhere in your theme or other plugins:

```php
// Drink management
uc_drink_query()
uc_get_drinks()
uc_generate_metadata_list($post_id)

// Carousel generation
uc_random_carousel($drink_posts, $num_slides, $show_titles, $show_content)
uc_filter_carousel($search_term, $drink_posts, $num_slides, $show_titles, $show_content, $supp_rand)
generate_slideshow_slides($images, $show_titles, $show_content)
generate_single_slide($image, $index, $is_duplicate, $show_titles, $show_content)
```

**Note**: These functions are automatically available when the plugin is active and provide the same functionality as the class methods.

### Available Controls

#### Carousel Toggle
- **Purpose**: Enables carousel functionality for images
- **CSS Class**: Adds `cocktail-pop-out` to image container
- **Use Case**: Carousel integration and functionality

#### Pop Out Toggle (Core Lightbox)
- **Purpose**: Enables WordPress's built-in lightbox functionality
- **CSS Class**: Adds `cocktail-nothing` to image container
- **Attributes**: Adds `data-wp-lightbox="true"` and grouping
- **Use Case**: Image galleries with lightbox viewing

### Automatic Dimension Analysis
The `ucPortraitLandscape` function automatically:
- **Analyzes image dimensions** to determine longest side
- **Assigns CSS classes** (`.portrait` or `.landscape`) for aspect ratio management
- **Maintains image proportions** and prevents stretching
- **Works with dynamic content** via MutationObserver

## 🎨 CSS Classes

### Applied Classes
- `.cocktail-carousel` - Carousel functionality enabled
- `.cocktail-pop-out` - Core lightbox enabled
- `.portrait` - Height is longest dimension
- `.landscape` - Width is longest dimension

### Lightbox Attributes
- `data-wp-lightbox="true"` - Enables core lightbox
- `data-wp-lightbox-group="drinks-plugin"` - Groups lightbox images

## 🔧 Configuration

### Build System Configuration
The plugin automatically uses built assets when available:
- **Development**: Uses source files from `js/` and `css/` directories
- **Production**: Uses compiled assets from `build/` directory
- **Fallback**: Graceful fallback to source files if build fails

### Webpack Configuration
The plugin uses a custom webpack configuration (`webpack.config.js`) to build multiple entry points:
- **index.js** - Block editor enhancements
- **frontend.js** - Frontend functionality with `ucPortraitLandscape`

This ensures both editor and frontend scripts are properly built and optimized.

### CSS Customization
All plugin styles are in `src/style.css`:
- **Core Lightbox overrides** with high specificity
- **Pop-out and carousel styles** 
- **Image orientation styles** for `.portrait` and `.landscape`
- **Responsive design** with media queries

## 🐛 Debugging

### Console Logging
The plugin includes comprehensive debugging:
- **Function calls** with detailed logging
- **Image processing** status and results
- **Dimension analysis** with measurements
- **Error handling** with warnings

### Debug Messages
Look for these console prefixes:
- `🔍 ucPortraitLandscape:` - Dimension analysis
- `🚀 Drinks Plugin:` - Initialization
- `🖼️ ucPortraitLandscape:` - Class assignments
- `⚠️ ucPortraitLandscape:` - Warnings and errors

## 📋 Version History

### Version 2.0.0 (Current)
- **Modern Build System** - Integrated `@wordpress/scripts`
- **Enhanced Functionality** - Improved lightbox and pop-out features
- **Automatic Dimension Analysis** - `ucPortraitLandscape` function
- **Better CSS Management** - Centralized styles with overrides
- **Comprehensive Debugging** - Detailed console logging

### Previous Versions
- 1.3.0 - Removed custom block, enhanced core image blocks
- 1.2.0 - Added block editor integration
- 1.1.0 - Added lightbox functionality
- 1.0.0 - Initial release

## 🔗 Dependencies

### WordPress Dependencies
- `wp-blocks` - Block editor integration
- `wp-element` - React components
- `wp-editor` - Editor functionality
- `wp-components` - UI components
- `wp-i18n` - Internationalization
- `wp-hooks` - WordPress hooks system
- `wp-compose` - Higher-order components

### Development Dependencies
- `@wordpress/scripts` - Build system and tooling
- Modern JavaScript tooling via `@wordpress/scripts`

## 🤝 Contributing

### Development Workflow
1. **Fork the repository**
2. **Create feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make changes** and test thoroughly
4. **Run build** (`npm run build`)
5. **Commit changes** (`git commit -m 'Add amazing feature'`)
6. **Push to branch** (`git push origin feature/amazing-feature`)
7. **Open Pull Request**

### Code Standards
- **JavaScript**: ES6+ with WordPress coding standards
- **CSS**: Organized with clear sections and comments
- **PHP**: WordPress coding standards
- **Documentation**: Clear comments and README updates

## 📄 License

This plugin is developed for WordPress and follows WordPress development practices.

## 🆘 Support

For issues and questions:
1. Check the console for debug messages
2. Verify WordPress version compatibility
3. Test with default theme
4. Check for plugin conflicts

---

**Built with ❤️ for WordPress developers** 