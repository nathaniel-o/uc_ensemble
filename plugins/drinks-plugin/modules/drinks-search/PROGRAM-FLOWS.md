# Program Flows - Frontend Usage

**All frontend flows calling DrinksSearch class (excluding admin tools)**

---

## ✅ Architecture Changes (Current State)

### Frontend Query Mode
**MODE 2: Published Posts Only** ✅  
All frontend interactions now correctly use `get_published_drink_posts()` which returns ONLY published posts.

### Code Simplification
**Removed `get_drinks_search()` wrapper** ✅  
Eliminated unnecessary function indirection. All code now uses:
```php
global $drinks_search;
$drinks_search->method();
```

---

## 🔄 Frontend Program Flows

### FLOW 1: Pop-Out Clicks 🖼️→📋

User clicks image with `data-cocktail-pop-out="true"`

**JavaScript Path:**
```
frontend.js: handleCocktailPopOutClick()
  ↓
frontend.js: openCocktailPopOutLightbox()
  ↓
frontend.js: loadDrinksForContentLightbox()
  ↓
AJAX: 'get_drink_content' (image_id)
```

**PHP Backend:**
```
DrinksPlugin::handle_get_drink_content()
  ↓
$this->uc_get_drink_posts()
  ↓
$drinks_search->get_published_drink_posts() ✅ MODE 2
  ↓
Returns: Published drink posts array
```

**Result:** Displays pop-out lightbox with drink recipe details

---

### FLOW 2: Carousel Clicks 🖼️→🎠

User clicks image with `data-cocktail-carousel="true"`

**JavaScript Path:**
```
frontend.js: handleCocktailCarouselClick()
  ↓
frontend.js: openCocktailCarousel()
  ↓
frontend.js: ucSummonCarousel({matchTerm: '', filterTerm: '', ...})
  ↓
frontend.js: loadCarouselImages()
  ↓
AJAX: 'drinks_filter_carousel' (search_term, figcaption_text, random)
```

**PHP Backend:**
```
DrinksPlugin::handle_filter_carousel()
  ↓
$this->uc_get_drink_posts()
  ↓
$drinks_search->get_published_drink_posts() ✅ MODE 2
  ↓
$this->uc_image_carousel($match_term, $filter_term, $options)
  ↓
Returns: Jetpack slideshow HTML with filtered/random drinks
```

**Result:** Opens carousel overlay with drink slideshow

---

### FLOW 3: Pop-Out → Carousel 📋→🎠

User clicks image or H1 in pop-out lightbox

**JavaScript Path:**
```
frontend.js: setupPopOutToCarouselClick() event listener
  ↓
frontend.js: closeDrinksContentLightbox()
  ↓
frontend.js: openCocktailCarouselFromPopOut()
  ↓
frontend.js: ucSummonCarousel({matchTerm: '', filterTerm: '', ...})
```

**Then follows FLOW 2 path**

**Result:** Closes pop-out, opens random carousel

---

### FLOW 4: Pop-Out Links → Filtered Carousel 📋🔗→🎠

User clicks `<a class="drink-filter-link">` in pop-out

**JavaScript Path:**
```
frontend.js: handleDrinkFilterLinkClick()
  ↓
Gets data-filter attribute (category/tag)
  ↓
frontend.js: ucSummonCarousel({matchTerm: '', filterTerm: filterTerm, ...})
```

**Then follows FLOW 2 path with filterTerm set**

**Result:** Closes pop-out, opens filtered carousel showing drinks matching category/tag

---

### FLOW 5: Search Bar → Carousel 🔍→🎠

User submits search from theme search bar

**JavaScript Path:**
```
Theme JS: searchListen() catches form submit
  ↓
Theme JS: ucSearch(e)
  ↓
Theme JS: openFilteredDrinksCarousel(searchTerm)
  ↓
frontend.js: openFilteredDrinksCarousel(searchTerm)
  ↓
frontend.js: ucSummonCarousel({matchTerm: '', filterTerm: searchTerm, ...})
```

**Then follows FLOW 2 path with filterTerm = searchTerm**

**Result:** Opens carousel filtered by search term

---

### FLOW 6: "See More" Button 🎠→🎠

User clicks "See More" button in carousel footer

**JavaScript Path:**
```
frontend.js: Reloads carousel with same filter
  ↓
frontend.js: ucSummonCarousel() with existing filterTerm
```

**Then follows FLOW 2 path**

**Result:** Regenerates carousel with same filter (new random drinks if applicable)

---

## 🎯 Key Architecture Components

### Centralized AJAX Entry Points
- **`handle_filter_carousel()`** - All carousel requests (random, filtered, matched)
- **`handle_get_drink_content()`** - All pop-out content requests

### Single Carousel Function
**`ucSummonCarousel(context)`** - Unified handler for all carousel scenarios

**Context parameters:**
- `matchTerm` - Drink name to show first (from figcaption)
- `filterTerm` - Search/category term to filter by
- `container` - DOM element reference
- `isOverlay` - Overlay vs inline mode
- `closePopOut` - Close existing pop-out first

### DrinksSearch Methods Used by Frontend

**MODE 2:** `get_published_drink_posts()`
- Returns: Array of published WP_Post objects
- Used by: All frontend carousel and pop-out flows
- Status filter: `post_status = 'publish'` ✅

**MODE 3:** `get_all_media_attachments()`
- Returns: Array of all media attachments with metadata
- Used by: Admin media checker tools only (not frontend)

---

## 📊 Query Efficiency

### Single WP_Query Per Request
Both search-based and click-based carousels use **ONE** `WP_Query` execution per AJAX call:

1. Fetch all published drink posts
2. Filter results in-memory using PHP array operations
3. No N+1 query problems

### Filtering Logic
- **Search mode:** `array_filter()` on title + metadata + taxonomy
- **Click mode:** `array_filter()` to match exact title, then add random drinks
- **Random mode:** Shuffle all drinks

### Parameters Matrix

| Trigger | matchTerm | filterTerm | Result |
|---------|-----------|------------|--------|
| Search form | `''` | `'martini'` | Filtered drinks matching "martini" |
| Click image | `'Martini'` | `''` | Clicked drink first + random |
| Random | `''` | `''` | Random drinks only |

---

## 📁 Modified Files

All frontend calls updated to use MODE 2 (published only):

1. `/plugins/drinks-plugin/drinks-plugin.php`
2. `/plugins/drinks-plugin/sync-drinks-metadata.php`
3. `/plugins/drinks-plugin/modules/cocktail-images/media-library-checker.php`
4. `/plugins/drinks-plugin/modules/cocktail-images/media-library-checker-web.php`
5. `/plugins/drinks-plugin/modules/cocktail-images/cocktail-images.php`
6. `/plugins/drinks-plugin/modules/drinks-search/drinks-search.php`

---

## ✅ Design Benefits

- **Single WP_Query** = Efficient database access
- **PHP filtering** = Fast in-memory operations
- **Flexible** = Same backend handles all modes
- **No N+1 queries** = Metadata pre-loaded
- **Centralized** = All query logic in drinks-search module
- **Published only** = No draft posts leak to frontend ✅

---


