# Cocktail Images Plugin

A WordPress plugin for managing cocktail images with intelligent title matching and image cycling functionality.

## Description

This plugin provides **FOCUSED** functionality for managing and displaying cocktail images on your WordPress site. It includes:

- **Intelligent Image Matching**: Advanced title normalization and exact matching for finding related images
- **Image Cycling**: Click-to-cycle through images with matching titles
- **Featured Image Detection**: Check if images are featured in posts
- **Title Normalization**: Consistent title processing across JavaScript and PHP

## 🚨 **Important: Carousel Functionality Removed**
**ALL carousel and drink management functionality has been completely removed** and moved to the drinks-plugin. This plugin now focuses solely on image matching and cycling features.

## 🆕 **Recent Updates**
- **Public API Access** - `normalize_title_for_matching()` function is now public for use by other plugins
- **Global Accessor Function** - `get_cocktail_images_plugin()` provides easy access to plugin instance
- **Enhanced Integration** - Better integration with drinks-plugin for shared title normalization

## Features

### **AJAX Handlers**
- `randomize_image` - Randomize images with category filtering
- `find_matching_image` - Find images with matching titles (exact match)
- `check_featured_image` - Check if an image is featured in any post

### **Image Matching & Cycling**
- **Title Normalization**: Consistent processing across JS and PHP
  - Truncates at colon (`:`)
  - Removes T2- prefix
  - Replaces hyphens/underscores with spaces
  - Filters out words <3 letters
  - Case-insensitive matching
- **Exact Matching**: Finds images with identical normalized titles
- **Image Cycling**: Click images to cycle through matching alternatives
- **Featured Image Detection**: Identifies images used as post featured images



### **Image Processing**
- **Title Normalization**: Consistent processing across JS and PHP
- **Exact Matching**: Finds images with identical normalized titles
- **Image Cycling**: Click images to cycle through matching alternatives
- **Featured Image Detection**: Identifies images used as post featured images

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
- `ucNormalizeTitle()` - Title normalization helper
- `ucDoesImageHavePost()` - Check if image is featured in posts
- `ucSetupOneDrinkAllImages()` - Setup automatic image cycling

## AJAX Endpoints

- `wp_ajax_randomize_image` / `wp_ajax_nopriv_randomize_image`
- `wp_ajax_find_matching_image` / `wp_ajax_nopriv_find_matching_image`
- `wp_ajax_check_featured_image` / `wp_ajax_nopriv_check_featured_image`

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
The following functions have been moved to the `drinks-plugin`:

- `uc_drink_query()` - Query drink posts with taxonomy
- `uc_get_drinks()` - Retrieve drink posts from database
- `uc_random_carousel()` - Generate random carousel slides
- `uc_filter_carousel()` - Generate filtered carousel slides
- `generate_slideshow_slides()` - Generate slideshow HTML
- `generate_single_slide()` - Generate individual slide HTML
- `uc_generate_metadata_list()` - Generate drink metadata lists

### AJAX Handlers
- `filter_carousel` endpoint moved to drinks-plugin

### Usage After Migration
To use carousel functionality, ensure the `drinks-plugin` is active and use its functions:

```php
// Get drink posts
$drink_posts = uc_get_drinks();

// Generate carousel
$carousel = uc_random_carousel($drink_posts, 5, 0, 1);
```

## License

GPL v2 or later 