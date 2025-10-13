# Drinks Plugin - Module System

## Overview

The drinks-plugin now contains former cocktail-images plugin. 

## Module Structure

```
drinks-plugin/
├── modules/
│   └── cocktail-images/          # Image management module
│       ├── cocktail-images.php   # Main module file
│       ├── includes/             # PHP includes
│       ├── src/                  # JavaScript source files
│       ├── assets/               # CSS and other assets
│       └── README.md             # Module documentation
└── drinks-plugin.php             # Main plugin file (loads modules)
```

## How Modules Work

1. **Main Plugin Initialization**
   - The main `drinks-plugin.php` defines core constants (`DRINKS_PLUGIN_PATH`, `DRINKS_PLUGIN_URL`)
   - Modules are loaded via `require_once` in the main plugin file

2. **Module Constants**
   - Modules use the parent plugin's paths as base
   - Example: `COCKTAIL_IMAGES_PLUGIN_DIR = DRINKS_PLUGIN_PATH . 'modules/cocktail-images/'`

3. **Build Process**
   - `npm run build` only affects `drinks-plugin/src/` directory
   - Modules are NOT affected by the build process
   - Module JavaScript files are loaded directly from source

## Accessing Module Functionality

### From PHP

```php
// Get the cocktail-images module instance
$cocktail_module = get_cocktail_images_module();

// Use module methods
if ($cocktail_module) {
    $normalized_title = $cocktail_module->normalize_title_for_matching($title);
}
```

### From JavaScript

Module JavaScript files are loaded separately and provide global functions:

```javascript
// Available from cocktail-images module
ucOneDrinkAllImages();
ucNormalizeTitle(title);
ucDoesImageHavePost(imageTitle);
```

## Adding New Modules

1. Create a new directory in `modules/`
2. Create a main PHP file that defines module constants
3. Use conditional constants to reference parent plugin paths:
   ```php
   if (!defined('MY_MODULE_DIR')) {
       define('MY_MODULE_DIR', DRINKS_PLUGIN_PATH . 'modules/my-module/');
   }
   ```
4. Load the module in `drinks-plugin.php`:
   ```php
   require_once DRINKS_PLUGIN_PATH . 'modules/my-module/my-module.php';
   ```

## Migration Notes

The cocktail-images plugin was migrated to a module:

- **Class renamed:** `Cocktail_Images_Plugin` → `Cocktail_Images_Module`
- **Global variable:** `$cocktail_images_plugin` → `$cocktail_images_module`
- **Accessor function:** `get_cocktail_images_plugin()` → `get_cocktail_images_module()`
- **All references updated** in drinks-plugin.php to use the new function name

## Benefits of Module System

1. **Single Plugin Activation** - Users only activate drinks-plugin
2. **Shared Resources** - Modules can share utilities and constants
3. **Better Organization** - Clear separation of concerns
4. **Easier Maintenance** - Related code is grouped together
5. **No Duplication** - Shared functionality (like title normalization) in one place

