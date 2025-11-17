# Drinks Search Module

**Centralized WP_Query Operations for Drinks Plugin & Theme**



---

## 🚀 Usage

Access the global DrinksSearch instance:

```php
global $drinks_search;

// MODE 1: Get all drinks (any status) - Admin use
$query = $drinks_search->get_all_drink_posts_query();
$count = $query->found_posts;

// MODE 2: Get published drinks only - Frontend use ✅
$drinks = $drinks_search->get_published_drink_posts();
foreach ($drinks as $post) {
    echo $post->post_title;
}

// MODE 3: Get all media attachments - Checker tools
$media = $drinks_search->get_all_media_attachments();
foreach ($media as $attachment) {
    echo $attachment['title'] . ' - ' . $attachment['file'];
}
```

---

## Architecture Summary

### Single WP_Query Strategy
Both search-based and click-based carousels use **ONE** `WP_Query` per AJAX request:
1. Fetch all published drink posts (MODE 2)
2. Filter in-memory using PHP `array_filter()` operations
3. No N+1 query problems

### Filtering Approaches
- **Search mode**: `array_filter()` on title + metadata + taxonomy
- **Click mode**: `array_filter()` to match exact title, add random drinks
- **Random mode**: Shuffle all drinks

**See PROGRAM-FLOWS.md for detailed flow documentation.**

---

