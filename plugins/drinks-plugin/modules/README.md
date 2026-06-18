# Drinks Plugin - Module System

## Overview

The drinks-plugin now contains former cocktail-images plugin. 

## Module Structure

```
drinks-plugin/
├── includes/
│   └── drink-image-matching.php  # Shared title matching + random alternates
├── modules/
│   └── cocktail-images/          # Image management module
│       ├── cocktail-images.php   # Main module file
│       ├── includes/             # PHP includes
│       ├── src/                  # JavaScript source files
│       ├── assets/               # CSS and other assets
│       └── README.md             # Module documentation
└── drinks-plugin.php             # Main plugin file (loads modules)
```

## includes/drink-image-matching.php

Central functions for:

- **Title matching:** `drinks_extract_match_words()`, `drinks_normalize_title_for_matching()`, `drinks_titles_match_significant_words()`
- **Image URLs:** `drinks_get_original_image_url()`, `drinks_trim_image_dimensions()`
- **Random alternates:** `drinks_find_all_matching_attachment_ids()`, `drinks_pick_random_matching_attachment_id()`, `drinks_get_attachment_image_render_data()`, `drinks_randomize_attachment_for_render()`

Used by cocktail-images (page `core/image` render, srcset, AJAX cycling) and drinks-plugin (pop-out + carousel lightboxes).

## How Modules Work

1. **Main Plugin Initialization**
   - The main `drinks-plugin.php` defines core constants (`DRINKS_PLUGIN_PATH`, `DRINKS_PLUGIN_URL`)
   - Modules are loaded via `require_once` in the main plugin file

2. **Module Constants**
   - Modules use the parent plugin's paths as base
   - Example: `COCKTAIL_IMAGES_PLUGIN_DIR = DRINKS_PLUGIN_PATH . 'modules/cocktail-images/'`

3. **Build Process**
   - `npm run build` compiles `drinks-plugin/src/` → `build/`
   - cocktail-images loads `src/image-utils.js`, `image-fade.js`, `image-matching-cycle.js`, `cocktail-images.js` directly (no build step)
   - Pop-out / carousel lightbox UI lives in `drinks-plugin/src/frontend.js` only (not cocktail-images)

## Frontend JavaScript layout

| File | Role |
|------|------|
| `cocktail-images/src/image-utils.js` | URL trim, title helpers → `window.cocktailImagesUtils` |
| `cocktail-images/src/image-fade.js` | Opacity swap → `window.cocktailImagesFade` |
| `cocktail-images/src/image-matching-cycle.js` | `find_matching_image` cycling → `window.cocktailImagesMatching` |
| `cocktail-images/src/cocktail-images.js` | Legacy randomize, globals (`ucOneDrinkAllImages`, etc.) |
| `drinks-plugin/src/frontend.js` | Pop-out, carousel, basic lightbox — depends on cocktail-images scripts |

Removed duplicates (2026): `cocktail-images/src/lightbox.js` (unused; drinks-plugin owns lightbox clicks), `js/frontend.js` (legacy fallback).

## Accessing Module Functionality

### From PHP

```php
// Shared image matching (preferred for drinks-plugin and lightboxes)
$normalized_title = drinks_normalize_title_for_matching($title);
$image_data = drinks_randomize_attachment_for_render($attachment_id);

// Cocktail-images module (backward-compatible wrappers)
$cocktail_module = get_cocktail_images_module();
if ($cocktail_module) {
    $normalized_title = $cocktail_module->normalize_title_for_matching($title);
}
```

### From JavaScript

```javascript
// Shared utils / fade / matching (cocktail-images module)
window.cocktailImagesUtils.ucNormalizeTitle(title);
window.cocktailImagesMatching.cycleMatchedImage(img, { figure });
window.cocktailImagesMatching.startMatchedImageCycle(img, { intervalMs: 12000 });
window.cocktailImagesFade.swapImageWithFade(img, applySwap, { fadeMs: 300, holdMs: 600 });

// Legacy globals (cocktail-images.js)
ucOneDrinkAllImages(event);
ucDoesImageHavePost(img);

// Pop-out / carousel (drinks-plugin/src/frontend.js)
window.drinksPluginPopOut.open(img, container);
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

