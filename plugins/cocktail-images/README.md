# Cocktail Images Plugin

A WordPress plugin for managing cocktail images and drink-related functionality with intelligent title matching and image cycling.

## Description

This plugin provides comprehensive functionality for managing and displaying cocktail images on your WordPress site. It includes:

- **Intelligent Image Matching**: Advanced title normalization and exact matching for finding related images
- **Image Cycling**: Click-to-cycle through images with matching titles
- **Featured Image Detection**: Check if images are featured in posts
- **Title Normalization**: Consistent title processing across JavaScript and PHP
- **Carousel Generation**: Dynamic carousel creation with random and filtered drink images
- **Drink Post Management**: Functions to query and manage drink posts with taxonomy
- **Metadata Generation**: Automatic generation of drink metadata lists
- **Slideshow Support**: Complete slideshow functionality with infinite loops

## Features

### **AJAX Handlers**
- `filter_carousel` - Filter carousel by search terms
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

### **Carousel Functions**
- `uc_random_carousel()` - Generate random carousel slides
- `uc_filter_carousel()` - Generate filtered carousel slides
- `generate_slideshow_slides()` - Generate slideshow HTML

### **Drink Management**
- `uc_get_drinks()` - Retrieve drink posts from database
- `uc_drink_query()` - Query drink posts with taxonomy

### **Metadata Functions**
- `uc_generate_metadata_list()` - Generate drink metadata
- `uc_update_all_drink_excerpts()` - Update drink excerpts
- `uc_clear_all_drink_excerpts()` - Clear drink excerpts

## Installation

1. Upload the `cocktail-images` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the plugin through the 'Cocktail Images' menu in the admin dashboard

## Usage

The plugin provides object-oriented methods for all functionality:

```php
// Using plugin instance
$plugin = get_cocktail_images_plugin();
$drink_posts = $plugin->uc_get_drinks();
$carousel = $plugin->uc_random_carousel($drink_posts, 5, 0, 1);
```

### **JavaScript Functions**
- `ucOneDrinkAllImages()` - Main function for image cycling
- `ucNormalizeTitle()` - Title normalization helper
- `ucDoesImageHavePost()` - Check if image is featured in posts
- `ucSetupOneDrinkAllImages()` - Setup automatic image cycling

## AJAX Endpoints

- `wp_ajax_filter_carousel` / `wp_ajax_nopriv_filter_carousel`
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
- 'drinks' taxonomy must be registered

## Version

1.0.0

## License

GPL v2 or later 