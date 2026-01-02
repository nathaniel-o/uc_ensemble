# Drinks Search Module

**Centralized WP_Query Operations for Drinks Plugin & Theme**

---

## ⚠️ DEPRECATION NOTICE

**As of version 1.0.5, the `DrinksSearch` class has been consolidated into the main `DrinksPlugin` class.**

This module now only provides the admin documentation viewer (this page and related docs).

---

## 🚀 New Usage (v1.0.5+)

Access search methods via the global DrinksPlugin instance:

```php
global $drinks_plugin;

// Get all drinks (any status) - Admin use
$query = $drinks_plugin->get_all_drink_posts_query();
$count = $query->found_posts;

// Get published drinks only (raw WP_Post objects) - Backend operations
$drinks = $drinks_plugin->get_published_drink_posts_raw();
foreach ($drinks as $post) {
    
    echo $post->post_title;
}

// Get published drinks with expanded data - Frontend carousel use ✅
$drinks = $drinks_plugin->uc_get_drink_posts();
// Returns array with: id, title, permalink, thumbnail, thumbnail_id, excerpt,
// content, thumbnail_alt, thumbnail_title, thumbnail_caption, thumbnail_description

// Get all media attachments - Checker tools
$media = $drinks_plugin->get_all_media_attachments();
foreach ($media as $attachment) {
    echo $attachment['title'] . ' - ' . $attachment['file'];
}
```

---

## 🔍 Expanded Search Fields (v1.0.5+)

The search bar now searches across these fields:

| Category | Fields Searched |
|----------|-----------------|
| **Post Data** | title, excerpt, content |
| **Featured Image** | alt text, title, caption, description |
| **Image EXIF/IPTC** | title, caption, keywords |
| **Post Metadata** | drink_color, drink_glass, drink_garnish1, drink_garnish2, drink_base, drink_ice |
| **Taxonomy** | drinks taxonomy term names |

---

## ❌ Deprecated Usage (Pre-1.0.5)

```php
// OLD - DO NOT USE
global $drinks_search;
$drinks_search->get_published_drink_posts();  // ❌ Deprecated
$drinks_search->get_all_drink_posts_query();   // ❌ Deprecated
$drinks_search->get_all_media_attachments();   // ❌ Deprecated
```

---

## Architecture Summary

### Single WP_Query Strategy
Both search-based and click-based carousels use **ONE** `WP_Query` per AJAX request:
1. Fetch all published drink posts via `uc_get_drink_posts()`
2. Filter in-memory using PHP `array_filter()` operations
3. No N+1 query problems

### Filtering Approaches
- **Search mode**: `array_filter()` on title + excerpt + content + featured image data + metadata + taxonomy
- **Click mode**: `array_filter()` to match exact title, add random drinks
- **Random mode**: Shuffle all drinks

**See PROGRAM-FLOWS.md for detailed flow documentation.**

---

