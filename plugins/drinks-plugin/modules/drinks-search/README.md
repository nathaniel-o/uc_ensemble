# Drinks Search Module

Centralized WP_Query operations for the Drinks Plugin and theme.

## Deprecation Notice

As of v1.0.6, the `DrinksSearch` class was consolidated into `DrinksPlugin`. This folder now holds admin documentation only.

## Usage

Access search methods via the global `DrinksPlugin` instance:

```php
global $drinks_plugin;

// All drinks (any status) — admin
$query = $drinks_plugin->get_all_drink_posts_query();

// Published drinks (raw WP_Post objects) — backend
$drinks = $drinks_plugin->get_published_drink_posts_raw();

// Published drinks with expanded data — frontend carousel
$drinks = $drinks_plugin->uc_get_drink_posts();

// All media attachments — checker tools
$media = $drinks_plugin->get_all_media_attachments();
```

`uc_get_drink_posts()` returns: id, title, permalink, thumbnail, thumbnail_id, excerpt, content, thumbnail_alt, thumbnail_title, thumbnail_caption, thumbnail_description.

## Search Fields

| Category | Fields |
|----------|--------|
| Post data | title, excerpt, content |
| Featured image | alt text, title, caption, description |
| Image EXIF/IPTC | title, caption, keywords |
| Post metadata | drink_color, drink_glass, drink_garnish1, drink_garnish2, drink_base, drink_ice |
| Taxonomy | drinks term names |

## Deprecated (pre-1.0.6)

```php
global $drinks_search; // Do not use
$drinks_search->get_published_drink_posts();
$drinks_search->get_all_drink_posts_query();
$drinks_search->get_all_media_attachments();
```

## Architecture

One `WP_Query` per AJAX request: fetch published drinks via `uc_get_drink_posts()`, then filter in PHP with `array_filter()`.

- **Search mode** — filter by title, excerpt, content, image data, metadata, taxonomy
- **Click mode** — match exact title, then add random drinks
- **Random mode** — shuffle all drinks

See `PROGRAM-FLOWS.md` for flow documentation.

## vs WordPress Default Search

WordPress default search runs a single SQL `LIKE` query against post title and content and returns a standard post list. Drinks search fetches all published drink posts once, filters in PHP across title, excerpt, content, featured-image metadata, EXIF/IPTC keywords, drink custom fields, and the drinks taxonomy, then shows matches in a carousel.

WordPress admin search is more forgiving of punctuation and word boundaries. It tokenizes the query (splits on spaces and punctuation, drops very short terms) and matches each token independently with `LIKE`. Drinks search uses raw `stripos()` on the full query string as typed, so punctuation must match exactly: searching `Lover's` will not match a title stored as `Lovers` or with a typographic apostrophe (`'` vs `'`). The same applies to hyphens and other separators — `old fashioned` will not match `Old-Fashioned`.

### Multi-word queries (current behavior)

Filter mode passes the entire search string to `stripos()` as one substring. It does **not** split on spaces. A query like `gin martini` only matches fields that contain the contiguous phrase `gin martini`, not posts where `gin` and `martini` appear separately. WordPress default search requires each word to appear somewhere (AND logic), which is broader.

### Search normalization (implemented)

WordPress provides `remove_accents()` but no public `normalize_*` for search text; tokenization lives inside `WP_Query::parse_search()`. Drinks search uses `normalize_search_text()` and `parse_search_tokens()` on `DrinksPlugin`: lowercase, strip apostrophes and punctuation, collapse whitespace, then AND-match each token across all searchable fields. Carousel behavior is unchanged.
