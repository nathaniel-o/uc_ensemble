# Cocktail Images Module

Image matching, cycling, srcset enhancement, and server-side random first paint for drink photos.

Carousel and drink search live in **drinks-plugin**. This module handles media-library title matching only.

## Features

### Title matching and cycling
- **`ucOneDrinkAllImages()`** — click a `core/image` block to cycle through title-matched alternates
- **`extract_match_words()`** — canonical word extraction (PHP public API)
- **`titles_match_significant_words()`** — symmetric match on significant-word sets
- **`normalize_title_for_matching()`** — sorted, unique significant words as a string (display / legacy comparisons)

### Server-side random first paint
- **`randomize_core_image_block_at_render()`** — `render_block` filter on `core/image`
- On frontend page load, picks a random title-matched alternate before first paint (no client-side flash)
- Skips banner images (title contains `banner`)
- Timed `ucOneDrinkAllImages` cycles (5–40s) still run after load

### Srcset enhancement
- **`enhance_srcset_with_matching_images()`** — `wp_calculate_image_srcset` filter
- Adds up to 3 full-resolution alternate URLs for title-matched images
- Admin toggle, cache clear, and rebuild tools under Cocktail Images settings
- Uses significant-word matching (punctuation-insensitive)

### Figcaption display
- Figcaptions show normalized titles; originals preserved in `data-original-caption` for SEO

## Title matching logic

Matching is **not** a literal string compare. Both titles are reduced to a set of **significant words**; they match when the sets are equal.

### Word extraction (`extract_match_words`)
1. Truncate at `:` (drink name vs description)
2. Lowercase, `remove_accents()`, strip apostrophes and punctuation
3. Keep words **≥ 3 letters**
4. Drop two-letter codes: `T2`, `AU`, `SO`, `SU`, `SP`, `FP`, `EV`, `RO`, `WI`

### Comparison (`titles_match_significant_words`)
- Both titles must produce at least one significant word
- Unique word sets must be identical (order-independent)
- Case- and punctuation-insensitive

### Examples

| Title A | Title B | Match? |
|---------|---------|--------|
| `Bourbon-Old-Fashioned_AU` | `bourbon old fashioned` | yes |
| `Lover's Kiss` | `Lovers Kiss` | yes |
| `T2-Bourbon AU` | `Bourbon` | yes (codes ignored) |
| `Bourbon Manhattan` | `Bourbon` | no (extra word) |

## Usage

```php
$plugin = get_cocktail_images_module();

$words = $plugin->extract_match_words('Cherry-Gin-and-Tonic_RO: Bright red...');
// ['cherry', 'gin', 'tonic']

$plugin->titles_match_significant_words('Old-Fashioned', 'old fashioned'); // true

$plugin->normalize_title_for_matching('Bourbon-Old-fashioned_AU');
// 'bourbon fashioned old'
```

## JavaScript

| Function | Purpose |
|----------|---------|
| `ucOneDrinkAllImages()` | Click-to-cycle through title-matched images |
| `ucNormalizeTitle(title, preserveCapitalization)` | Client-side pre-normalization before AJAX |
| `ucSetupOneDrinkAllImages()` | Timed auto-cycle on `DOMContentLoaded` |
| `trimImageDimensions()` / `trimSrcsetDimensions()` | Full-resolution URL helpers |

## AJAX endpoints

| Action | Purpose |
|--------|---------|
| `find_matching_image` | Find title-matched alternates for click cycling |
| `randomize_image` | Category-filtered random image (legacy click randomize) |
| `check_featured_image` | Check if image is featured in a post |
| `toggle_srcset_enhancement` | Enable/disable srcset enhancement |
| `clear_srcset_cache` / `rebuild_srcset_cache` | Srcset match cache management |

## Relation to drinks-plugin pop-outs and carousels

Title matching and random alternate selection live in **`includes/drink-image-matching.php`** (shared by this module and drinks-plugin).

| Feature | Randomized? | How |
|---------|-------------|-----|
| **Page `core/image` blocks** | Yes | `randomize_core_image_block_at_render` → `drinks_pick_random_matching_attachment_id()` |
| **Pop-out lightbox** | Yes | `get_drink_content` AJAX → `drinks_randomize_attachment_for_render()` |
| **Carousel slides** | Yes | `generate_single_slide()` → `drinks_randomize_attachment_for_render()` |
| **Search carousel** | No | Driven by search term, not title-matched alternates |

After changing matching rules, clear or rebuild the srcset cache in admin.

## Dependencies

- WordPress 5.0+
- PHP 7.4+
- drinks-plugin (carousel, pop-out, search)

## Migration notes

These moved to drinks-plugin:

- `uc_drink_query()`, `uc_get_drinks()`, `uc_filter_carousel()`, `uc_generate_metadata_list()`
- `filter_carousel` AJAX endpoint
