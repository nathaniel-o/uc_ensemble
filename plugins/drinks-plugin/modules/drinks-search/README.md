# Drinks Search Module

**Centralized WP_Query Operations for Drinks Plugin & Theme**

**Date**: October 27, 2025  

---

## 🎯 Mission: Centralize WP_Query Operations

**Goal**: Create a `drinks-search` module to eliminate duplicate WP_Query code and provide documented query API.

**Result**: ✅ **Success** - All queries migrated, 123 lines of duplication eliminated.

This module consolidates all WordPress query operations used throughout the drinks plugin and theme. It provides a consistent, documented API for querying drinks and media.

---

## Purpose

- **Single Source of Truth**: All WP_Query operations in one place
- **Maintainability**: Easier to update query logic without hunting through multiple files
- **Documentation**: Each query mode is clearly documented with its purpose
- **Performance**: Consistent query patterns and optimization
- **DRY Principle**: Eliminated 123 lines of duplicate code

---

## 📦 Module Structure

```
drinks-search/
├── drinks-search.php                      # Module loader
├── includes/
│   └── class-drinks-search.php            # 3 documented query modes
└── README.md                               # This file
```

The module is loaded automatically by the drinks plugin at:  
`plugins/drinks-plugin/drinks-plugin.php` line ~28

---

## Query Modes

### MODE 1: Get All Drink Posts
**Migrated from**: `plugins/drinks-plugin/drinks-plugin.php`  
**Method**: `get_all_drink_posts_query()`  
**Filters**: Posts with 'drinks' taxonomy (any status)  
**Returns**: `WP_Query` object  
**Use**: Count total drinks

**Example**:
```php
$query = get_drinks_search()->get_all_drink_posts_query();
$count = $query->found_posts;
```

---

### MODE 2: Get Published Drink Posts
**Migrated from**: `plugins/drinks-plugin/sync-drinks-metadata.php`  
**Method**: `get_published_drink_posts()`  
**Filters**: Published posts with 'drinks' taxonomy  
**Returns**: Array of `WP_Post` objects  
**Use**: Metadata sync operations

**Example**:
```php
$drinks = get_drinks_search()->get_published_drink_posts();
foreach ($drinks as $drink) {
    // Process each drink post
}
```

---

### MODE 3: Get All Media Attachments
**Migrated from**: 3 files (eliminated duplication!)
- `modules/cocktail-images/cocktail-images.php`
- `modules/cocktail-images/media-library-checker.php`
- `modules/cocktail-images/media-library-checker-web.php`

**Method**: `get_all_media_attachments()`  
**Returns**: Array of attachment data with metadata  
**Use**: Media library audit tools

**Alternative**: `get_all_media_attachments_query()` returns raw `WP_Query` object

**Example**:
```php
$media = get_drinks_search()->get_all_media_attachments();
foreach ($media as $attachment) {
    echo $attachment['title'] . ' - ' . $attachment['file'];
}
```

---

## 🚀 Usage

### From PHP (Theme or Plugin)

```php
// Get the drinks search instance
$drinks_search = get_drinks_search();

// MODE 1: Get all drinks (any status)
$query = $drinks_search->get_all_drink_posts_query();

// MODE 2: Get published drinks only
$drinks = $drinks_search->get_published_drink_posts();

// MODE 3: Get all media
$media = $drinks_search->get_all_media_attachments();
```

---

## Benefits Achieved

### ✅ Code Quality
- **Single Source of Truth**: All queries in one location
- **DRY Principle**: Eliminated 123 lines of duplicate code
- **Consistency**: Same data structures across all tools
- **Documentation**: Each mode clearly documented

### ✅ Maintainability
- **Centralized Updates**: Change query logic once, affects all consumers
- **Clear Dependencies**: MODE comments show where queries come from
- **Easy Testing**: Test query logic in one place
- **Code Navigation**: @see references point to implementation

### ✅ Performance
- **Consistent Patterns**: Same optimization approach everywhere
- **Future Caching**: Easy to add caching layer in module
- **Query Monitoring**: Single place to add logging/profiling

### ✅ Developer Experience
- **Clear API**: Simple `get_drinks_search()->method()` pattern
- **IntelliSense Support**: Single class with all methods
- **Example Code**: Usage examples for each mode
- **Self-Documenting**: MODE comments explain purpose

---

## 🔮 Future Enhancements

### Possible Additions
1. **Query caching** - Cache expensive media queries
2. **Query logging** - Track slow queries for optimization
3. **Advanced filters** - Filter drinks by ingredients, season
4. **Search analytics** - Track what users search for
5. **Elasticsearch** - Better full-text search if needed

### Not Needed Now
- ❌ Build step (vanilla JS works fine)
- ❌ Modal UI (carousel search works better)
- ❌ Complex queries (current ones are sufficient)

---

## 📝 Technical Notes

- ✅ Eliminated 123 lines of duplicate code
- ✅ All queries use `wp_reset_postdata()` to avoid conflicts
- ✅ MODE comments added to all calling code for traceability
- ✅ No breaking changes to public APIs
- ✅ Query results not cached by default (can be added per-query)

### Search Flow
- Theme intercepts search form submission (`uc-theme-ui/scripts/functions.js`)
- Theme calls `window.drinksPluginCarousel.loadImages()` to open carousel
- Plugin's carousel uses `drinks_filter_carousel` AJAX endpoint to fetch filtered drinks

---

## WP_Query Contexts

This section explains how `WP_Query` is used differently for click-based vs search-based carousels, and illustrates the complete program flow for each scenario.

### Key Insight

**Both click-based and search-based carousels use the SAME WP_Query call** - they just filter the results differently in PHP. There's only ONE `WP_Query` execution per AJAX request, which fetches all drink posts, then filtering happens in-memory using PHP array operations.

### 🔍 SEARCH-BASED CAROUSEL FLOW

```
User types search → Enter
         ↓
Theme: searchListen() catches form submit
         ↓
Theme: ucSearch(e) 
         ↓
Theme: openFilteredDrinksCarousel(searchQuery)
         ↓
Theme: window.drinksPluginCarousel.loadImages(overlay, '', searchQuery, null)
         ↓
Plugin Frontend JS: loadCarouselImages(overlay, matchTerm='', filterTerm='searchQuery', container=null)
         ↓
AJAX Call: drinks_filter_carousel
    - action: 'drinks_filter_carousel'
    - search_term: 'searchQuery'        ← FILTER
    - figcaption_text: ''                ← NO MATCH
         ↓
╔═══════════════════════════════════════════════════════════╗
║  Plugin Backend: handle_filter_carousel()                 ║
║  ────────────────────────────────────────────             ║
║  1. Get ALL drink posts via WP_Query (MODE 2):           ║
║     $drink_posts = $this->uc_get_drink_posts()            ║
║        ↓ calls get_drinks_search()->get_published_drink_posts() ║
║        ↓ uses WP_Query with 'drinks' taxonomy            ║
║        ↓ Returns array of ALL drink posts                ║
║                                                            ║
║  2. Filter in PHP (NO new WP_Query):                      ║
║     uc_image_carousel('', 'searchQuery', $options)        ║
║        ↓ Uses array_filter() to search:                   ║
║        ↓   - Post title                                   ║
║        ↓   - Metadata (color, glass, garnish, base, ice)  ║
║        ↓   - Taxonomy terms                               ║
║        ↓ Returns filtered slides as HTML                  ║
╚═══════════════════════════════════════════════════════════╝
         ↓
Return HTML carousel slides
         ↓
Frontend JS: Parse HTML, inject into DOM, show overlay
```

### 🖱️ CLICK-BASED CAROUSEL FLOW

```
User clicks drink image
         ↓
Plugin Frontend JS: handleCocktailCarouselClick(event)
         ↓
Plugin Frontend JS: openCocktailCarousel(img, container)
         ↓
Plugin Frontend JS: loadCarouselImages(overlay, '', '', container)
         ↓
Auto-extract figcaption from container → matchTerm = 'Martini'
         ↓
AJAX Call: drinks_filter_carousel
    - action: 'drinks_filter_carousel'
    - search_term: ''                    ← NO FILTER
    - figcaption_text: 'Martini'         ← MATCH (clicked drink first)
         ↓
╔═══════════════════════════════════════════════════════════╗
║  Plugin Backend: handle_filter_carousel()                 ║
║  ────────────────────────────────────────────             ║
║  1. Get ALL drink posts via WP_Query (MODE 2):           ║
║     $drink_posts = $this->uc_get_drink_posts()            ║
║        ↓ SAME WP_Query as search mode                     ║
║        ↓ Returns array of ALL drink posts                ║
║                                                            ║
║  2. Match + Randomize in PHP (NO new WP_Query):          ║
║     uc_image_carousel('Martini', '', $options)            ║
║        ↓ Finds drink matching 'Martini' → slide 1         ║
║        ↓ Adds random drinks for remaining slides          ║
║        ↓ Returns carousel HTML                            ║
╚═══════════════════════════════════════════════════════════╝
         ↓
Return HTML carousel slides
         ↓
Frontend JS: Parse HTML, inject into DOM, show overlay
```

### WP_Query Implementation

**Same query for both modes:**
```php
// MODE 2 from drinks-search module
get_drinks_search()->get_published_drink_posts()
    ↓
WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'tax_query' => [
        'taxonomy' => 'drinks',
        'operator' => 'EXISTS'
    ]
])
```

**Returns:** ALL published drink posts (no filtering in query)

### Filtering Logic

- **Search mode:** PHP `array_filter()` searches title + metadata + taxonomy
- **Click mode:** PHP `array_filter()` matches exact title, then adds random drinks
- **Both:** NO additional WP_Query calls - just in-memory array operations

### Parameters Matrix

| Trigger | `matchTerm` (figcaption) | `filterTerm` (search) | Result |
|---------|-------------------------|----------------------|--------|
| **Search form** | `''` (empty) | `'martini'` | Filtered drinks matching "martini" |
| **Click image** | `'Martini'` (from figcaption) | `''` (empty) | Clicked drink first + random |
| **Random** | `''` | `''` | Random drinks only |

### Design Benefits

- ✅ **Single WP_Query** = Efficient (fetches once per AJAX call)
- ✅ **PHP filtering** = Fast (array operations on already-fetched data)
- ✅ **Flexible** = Same backend handles search, click, random modes
- ✅ **No N+1 queries** = Doesn't query DB for each drink's metadata
- ✅ **Centralized** = All query logic in drinks-search module

---

