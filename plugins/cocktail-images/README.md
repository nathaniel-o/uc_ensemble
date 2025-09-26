# Cocktail Images Plugin

A WordPress plugin for managing cocktail images with intelligent title matching and image cycling functionality.

## Description

This plugin provides **FOCUSED** functionality for managing and displaying cocktail images on your WordPress site. It includes:

- **Intelligent Image Matching**: Advanced title normalization and exact matching for finding related images
- **Title Normalization**: Consistent title processing across JavaScript and PHP

## 🚨 **Important: Carousel Functionality Removed**
**ALL carousel and drink management functionality has been completely removed** and moved to the drinks-plugin. This plugin now focuses solely on image matching and cycling features.

## 🆕 **Recent Updates**
- **Normalized Figcaption Display** - Figcaptions now show normalized titles (e.g., "Holiday Punch") while preserving original captions for SEO
- **Image Resolution Optimization** - `ucOneDrinkAllImages()` now serves full-resolution images by trimming dimension suffixes
- **Performance Enhancement** - Optimized srcset handling to single element for faster processing
- **Public API Access** - `normalize_title_for_matching()` function is now public for use by other plugins
- **Global Accessor Function** - `get_cocktail_images_plugin()` provides easy access to plugin instance
- **Enhanced Integration** - Better integration with drinks-plugin for shared title normalization

## Features

### **Normalized Figcaption Display**
- **Clean Display**: Figcaptions show only normalized titles (e.g., "Holiday Punch" instead of full descriptions)
- **Capitalization Preserved**: Original capitalization from media titles is maintained in display
- **SEO Preservation**: Original captions are preserved in `data-original-caption` attribute for search engines
- **Dynamic Updates**: Figcaptions update when images are cycled via `ucOneDrinkAllImages()`
- **Consistent Logic**: Uses the same normalization logic as image matching

### **Image Matching & Cycling**
- **Title Normalization**: Consistent processing across JS and PHP
  - Truncates at colon (`:`)
  - Removes T2- prefix
  - Replaces hyphens/underscores with spaces
  - Filters out words <3 letters
  - Case-insensitive matching
- **Exact Matching**: Finds images with identical normalized titles

## Installation

1. Upload the `cocktail-images` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the plugin through the 'Cocktail Images' menu in the admin dashboard

## Usage

The plugin provides object-oriented methods for image management functionality:

```php
// Using plugin instance
$plugin = get_cocktail_images_plugin();

// Access image matching and cycling features
// (All carousel and drink management functions moved to drinks-plugin)

// Use the public title normalization function
$normalized_title = $plugin->normalize_title_for_matching('Bourbon-Old-fashioned_AU-T-2-2');
// Result: "bourbon old fashioned"
```

### **Integration with Other Plugins**
The plugin now provides public access to its title normalization function for use by other plugins:

```php
// Get the plugin instance
$cocktail_plugin = get_cocktail_images_plugin();

if ($cocktail_plugin) {
    // Use the shared normalization logic
    $normalized_title = $cocktail_plugin->normalize_title_for_matching($title);
}
```

### **JavaScript Functions**
- `ucOneDrinkAllImages()` - Main function for image cycling
  - **Performance Optimized**: Uses trimmed URLs to serve full-resolution images
  - **Smart Srcset Handling**: Optimizes srcset to single element for better performance
  - **URL Trimming**: Removes dimension suffixes (-225x300.jpg) to get original images
  - `trimImageDimensions()` - Helper to remove dimension suffixes from URLs
  - `trimSrcsetDimensions()` - Helper to optimize srcset to single element
- `ucNormalizeTitle(title, preserveCapitalization)` - Title normalization helper (preserveCapitalization: true for display, false for matching)
- `ucDoesImageHavePost()` - Check if image is featured in posts
- `ucSetupOneDrinkAllImages()` - Setup automatic image cycling (includes figcaption normalization)

## AJAX Endpoints

- `wp_ajax_randomize_image` / `wp_ajax_nopriv_randomize_image` - Randomize images with category filtering
- `wp_ajax_find_matching_image` / `wp_ajax_nopriv_find_matching_image` - Find images with matching titles (exact match)
- `wp_ajax_check_featured_image` / `wp_ajax_nopriv_check_featured_image` - Check if an image is featured in any post

## Title Matching Logic

The plugin uses title matching to find related images:

1. **Normalization**: Both JavaScript and PHP normalize titles identically
2. **Word Filtering**: Removes words shorter than 3 characters
3. **Exact Matching**: Finds images with identical normalized titles
4. **Case Insensitive**: Matching works regardless of capitalization

### **Example**
- **Original**: "Cherry-Gin-and-Tonic_RO-T-2: Bright red cocktail..."
- **Normalized**: "cherry gin tonic" (removes short words, hyphens, etc.)
- **Matches**: Only images with exactly "cherry gin tonic" as normalized title

## Dependencies

- WordPress 5.0+
- PHP 7.4+

## Version

### Version 1.0.1 (Current)
- **Public API Access** - Made `normalize_title_for_matching()` function public
- **Global Accessor** - Added `get_cocktail_images_plugin()` function
- **Enhanced Integration** - Better integration with drinks-plugin

### Version 1.0.0
- Initial release with image matching and cycling functionality

## Migration Notes

### Carousel and Drink Management Functions
The following functions have been moved to the `drinks-plugin` and are actively used:

- `uc_drink_query()` - Query drink posts with taxonomy
- `uc_get_drinks()` - Retrieve drink posts from database
- `uc_filter_carousel()` - Generate filtered carousel slides
- `uc_generate_metadata_list()` - Generate drink metadata lists

### AJAX Handlers
- `filter_carousel` endpoint moved to drinks-plugin

### Usage After Migration
To use carousel functionality, ensure the `drinks-plugin` is active and use its functions:

```php
// Get drink posts
$drink_posts = uc_get_drinks();

// Generate filtered carousel
$filtered_carousel = uc_filter_carousel($search_term, $drink_posts, 5, 0, 1, 1);
```

## License

GPL v2 or later