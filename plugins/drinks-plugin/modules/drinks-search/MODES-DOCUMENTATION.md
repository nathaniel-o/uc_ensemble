#   Reference - Technical Specifications

**Detailed technical documentation for DrinksSearch class methods**

This document provides:
- Input/output specifications for each  
- Example data structures
- Use case details
- Caller references

**See PROGRAM-FLOWS.md for frontend flow documentation.**

---

##   1: Get All Drink Posts Query

**Method**: `get_all_drink_posts_query()`

### Description
Retrieves all posts that have ANY term in the 'drinks' taxonomy. No status filter - includes drafts, pending, etc. Returns a WP_Query object.

**Status**: Admin use only (includes unpublished posts)

### Historical Context
Originally in `drinks-plugin.php`, now centralized in drinks-search module.

### Example Input ($args)
```php
array(
    'post_type'       => 'post',
    'tax_query'       => array(
        array(
            'taxonomy' => 'drinks',  // Plural
            'operator' => 'EXISTS'
        )
    ),
    'posts_per_page'  => -1  // Get ALL posts (no limit)
)
```

### Example Output (WP_Query Object)
```php
// Returns WP_Query object with properties:
WP_Query Object (
    [query] => Array (...)
    [query_vars] => Array (
        [post_type] => post
        [tax_query] => Array (...)
        [posts_per_page] => -1
    )
    [posts] => Array (
        [0] => WP_Post Object (
            [ID] => 123
            [post_title] => 'Margarita'
            [post_content] => '...'
            [post_status] => 'publish'
            ...
        ),
        [1] => WP_Post Object (...)
        ...
    )
    [post_count] => 150
    [found_posts] => 150
    [max_num_pages] => 1
)

// Usage Example:
global $drinks_search;
$query = $drinks_search->get_all_drink_posts_query();
$total_count = $query->found_posts;  // e.g., 150
$has_posts = $query->have_posts();   // true/false
```

### Use Cases
- Admin counts of all cocktail recipes (including unpublished)
- Custom admin loops requiring the query object
- Operations that need to include drafts/pending posts

---

##   2: Get Published Drink Posts ✅

**Method**: `get_published_drink_posts()`

### Description
**This is the FRONTEND   for all user-facing features.**  
Retrieves only published posts with 'drinks' taxonomy. Returns an array of WP_Post objects.

**Status**: Published only ✅  
**Used by**: Pop-outs, carousels, search, metadata sync

### Historical Context
Originally in `sync-drinks-metadata.php`, now used by all frontend flows.

### Example Input ($args)
```php
array(
    'post_type'       => 'post',
    'post_status'     => 'publish',  // ONLY published posts
    'posts_per_page'  => -1,
    'tax_query'       => array(
        array(
            'taxonomy' => 'drinks',
            'operator' => 'EXISTS'
        )
    )
)
```

### Example Output (Array of WP_Post Objects)
```php
// Returns array of WP_Post objects:
Array (
    [0] => WP_Post Object (
        [ID] => 123
        [post_author] => '1'
        [post_date] => '2025-01-15 10:30:00'
        [post_date_gmt] => '2025-01-15 10:30:00'
        [post_content] => '<!-- wp:paragraph -->
<p>Mix tequila, lime juice...</p>'
        [post_title] => 'Margarita'
        [post_excerpt] => ''
        [post_status] => 'publish'
        [post_name] => 'margarita'
        [post_type] => 'post'
        [filter] => 'raw'
        ...
    ),
    [1] => WP_Post Object (
        [ID] => 456
        [post_title] => 'Mojito'
        [post_status] => 'publish'
        ...
    ),
    [2] => WP_Post Object (
        [ID] => 789
        [post_title] => 'Old Fashioned'
        [post_status] => 'publish'
        ...
    )
)

// Usage Example:
global $drinks_search;
$drinks = $drinks_search->get_published_drink_posts();
foreach ($drinks as $drink_post) {
    echo $drink_post->ID;            // 123
    echo $drink_post->post_title;    // "Margarita"
    echo $drink_post->post_content;  // "Mix tequila..."
}
```

### Use Cases
- ✅ Frontend carousels (random, filtered, matched)
- ✅ Pop-out lightbox content
- ✅ Search-based filtering
- ✅ Metadata sync operations
- ✅ Bulk processing of published recipes

---

##   3: Get All Media Attachments

**Method**: `get_all_media_attachments()`

### Description
Retrieves ALL media files (images, PDFs, videos) from WordPress Media Library with full metadata. Filters by posts that have a file path stored in `_wp_attached_file` meta.

**Used by**: Media library audit and checker tools (admin only)

### Historical Context
Originally duplicated across 3 files in cocktail-images module:
- `media-library-checker.php`
- `media-library-checker-web.php`
- `cocktail-images.php`

Now centralized, eliminating duplication.

### Example Input ($args)
```php
array(
    'post_type'       => 'attachment',
    'post_status'     => 'inherit',  // Attachments use 'inherit' status
    'posts_per_page'  => -1,
    'meta_query'      => array(
        array(
            'key'     => '_wp_attached_file',
            'compare' => 'EXISTS'
        )
    )
)
```

### Example Output (Array of Attachment Data)
```php
// Returns array of attachment data with full metadata:
Array (
    [0] => Array (
        [id] => 543
        [title] => 'margarita-cocktail'
        [alt] => 'Classic Margarita in a glass'
        [caption] => 'A refreshing margarita'
        [description] => 'Traditional Mexican cocktail with salt rim'
        [file] => '/opt/lampp/htdocs/uc.com/wp-content/uploads/2025/01/margarita.jpg'
        [url] => 'http://uc.com/wp-content/uploads/2025/01/margarita.jpg'
        [metadata] => Array (
            [width] => 1920
            [height] => 1080
            [file] => '2025/01/margarita.jpg'
            [filesize] => 245678
            [sizes] => Array (
                [thumbnail] => Array (
                    [file] => 'margarita-150x150.jpg'
                    [width] => 150
                    [height] => 150
                    [mime-type] => 'image/jpeg'
                )
                [medium] => Array (...)
                [large] => Array (...)
            )
            [image_meta] => Array (
                [aperture] => '0'
                [camera] => ''
                [created_timestamp] => '1736935800'
                ...
            )
        )
    ),
    [1] => Array (
        [id] => 544
        [title] => 'mojito-drink'
        [alt] => 'Mojito with mint leaves'
        [file] => '/opt/lampp/htdocs/uc.com/wp-content/uploads/2025/01/mojito.jpg'
        [url] => 'http://uc.com/wp-content/uploads/2025/01/mojito.jpg'
        [metadata] => Array (...)
    )
)

// Usage Example:
global $drinks_search;
$media = $drinks_search->get_all_media_attachments();
foreach ($media as $attachment) {
    echo $attachment['id'];          // 543
    echo $attachment['title'];       // "margarita-cocktail"
    echo $attachment['file'];        // "/opt/lampp/.../margarita.jpg"
    echo $attachment['url'];         // "http://uc.com/.../margarita.jpg"
    
    // Check if file exists
    if (file_exists($attachment['file'])) {
        echo "File exists on disk";
    }
    
    // Access metadata
    $width = $attachment['metadata']['width'];  // 1920
    $height = $attachment['metadata']['height']; // 1080
}
```

### Use Cases
- Media library audit and integrity checking
- Verifying image files exist on disk
- Bulk media metadata processing
- Generating media usage reports
- Finding orphaned or missing files

---

## Quick Reference Table

|   | Method | Returns | Post Status | Taxonomy | Use Case |
|------|--------|---------|-------------|----------|----------|
| 1 | `get_all_drink_posts_query()` | WP_Query | All statuses | drinks EXISTS | Admin counts, includes drafts |
| 2 | `get_published_drink_posts()` | Array<WP_Post> | publish only | drinks EXISTS | Front-end carousels, sync ops |
| 3 | `get_all_media_attachments()` | Array<AttachmentData> | inherit | N/A | Media audit, file checks |
| 3v | `get_all_media_attachments_query()` | WP_Query | inherit | N/A | Custom media loops |

---

## Technical Notes

### Global Access Pattern
All methods accessed via global singleton:
```php
global $drinks_search;
$drinks_search->method();
```

**Note**: The `get_drinks_search()` wrapper function was removed for minimal effective code.

### Performance Considerations
- All methods use `posts_per_page => -1` (retrieve ALL matching posts)
-   2 processes posts in a loop for clean WP_Post objects
- For large databases (1000+ posts), consider adding pagination or caching
- Frontend uses single query + in-memory filtering (no N+1 problems)

### Post Status Reference
- **publish**: Live posts visible on frontend ✅
- **draft**: Unpublished posts being edited
- **pending**: Posts awaiting review  
- **inherit**: Attachment status (inherits parent post status)

### Taxonomy Naming
The taxonomy is **'drinks'** (plural), not 'drink' (singular). Consistent across all queries.

---

