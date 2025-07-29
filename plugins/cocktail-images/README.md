# Cocktail Images Plugin

A WordPress plugin for managing cocktail images and drink-related functionality.

## Description

This plugin provides comprehensive functionality for managing and displaying cocktail images on your WordPress site. It includes:

- **Image Randomization**: AJAX-powered image randomization with category filtering
- **Carousel Generation**: Dynamic carousel creation with random and filtered drink images
- **Drink Post Management**: Functions to query and manage drink posts with taxonomy
- **Metadata Generation**: Automatic generation of drink metadata lists
- **Slideshow Support**: Complete slideshow functionality with infinite loops
- **Backward Compatibility**: Global functions for seamless integration with existing themes

## Features

- **AJAX Handlers**: 
  - `filter_carousel` - Filter carousel by search terms
  - `randomize_image` - Randomize images with category filtering
- **Carousel Functions**:
  - `uc_random_carousel()` - Generate random carousel slides
  - `uc_filter_carousel()` - Generate filtered carousel slides
  - `generate_slideshow_slides()` - Generate slideshow HTML
- **Drink Management**:
  - `uc_get_drinks()` - Retrieve drink posts from database
  - `uc_drink_query()` - Query drink posts with taxonomy
- **Metadata Functions**:
  - `uc_generate_metadata_list()` - Generate drink metadata
  - `uc_update_all_drink_excerpts()` - Update drink excerpts
  - `uc_clear_all_drink_excerpts()` - Clear drink excerpts

## Installation

1. Upload the `cocktail-images` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the plugin through the 'Cocktail Images' menu in the admin dashboard

## Usage

The plugin provides both object-oriented methods and global functions for backward compatibility:

```php
// Using global functions (backward compatible)
$drink_posts = uc_get_drinks();
$carousel = uc_random_carousel($drink_posts, 5, 0, 1);

// Using plugin instance
$plugin = get_cocktail_images_plugin();
$drink_posts = $plugin->uc_get_drinks();
$carousel = $plugin->uc_random_carousel($drink_posts, 5, 0, 1);
```

## AJAX Endpoints

- `wp_ajax_filter_carousel` / `wp_ajax_nopriv_filter_carousel`
- `wp_ajax_randomize_image` / `wp_ajax_nopriv_randomize_image`

## Dependencies

- WordPress 5.0+
- PHP 7.4+
- 'drinks' taxonomy must be registered

## Version

1.0.0

## License

GPL v2 or later 