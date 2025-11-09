# Drinks Search Class - MODE Documentation

This document provides detailed information about each MODE in the `DrinksSearch` class, including:
- Which files call each method
- Example input arguments ($args[])
- Example output structure

---

## MODE 1: Get All Drink Posts Query

**Method**: `get_all_drink_posts_query()`

### Description
Retrieves all posts that have ANY term in the 'drinks' taxonomy. No status filter - includes drafts, pending, etc. Returns a WP_Query object for when you need the query object itself.

### Called From
- **File**: `plugins/drinks-plugin/drinks-plugin.php`
- **Line**: 1338
- **Context**: `uc_drink_post_query()` method - Used to count drink posts and retrieve the query object

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
$query = get_drinks_search()->get_all_drink_posts_query();
$total_count = $query->found_posts;  // e.g., 150
$has_posts = $query->have_posts();   // true/false
```

### Use Cases
- Get total count of all cocktail recipes (published + unpublished)
- Need the query object for custom loop processing
- Admin operations that need to include drafts/pending posts

---

## MODE 2: Get Published Drink Posts

**Method**: `get_published_drink_posts()`

### Description
**This is the FRONT END mode for click and search-based carousels.**  
Retrieves only published posts with 'drinks' taxonomy. Returns an array of WP_Post objects for direct manipulation.

### Called From
- **File**: `plugins/drinks-plugin/sync-drinks-metadata.php`
- **Line**: 96
- **Context**: `get_drink_posts()` method - Used to sync metadata across all published drink posts in bulk operations

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
$drinks = get_drinks_search()->get_published_drink_posts();
foreach ($drinks as $drink_post) {
    echo $drink_post->ID;            // 123
    echo $drink_post->post_title;    // "Margarita"
    echo $drink_post->post_content;  // "Mix tequila..."
}
```

### Use Cases
- Front-end carousels and drink listings
- Sync metadata operations across published drinks
- Bulk processing of cocktail recipes
- Any operation that needs direct post object manipulation

---

## MODE 3: Get All Media Attachments

**Method**: `get_all_media_attachments()`

### Description
Retrieves ALL media files (images, PDFs, videos) from WordPress Media Library with full metadata. Filters by posts that have a file path stored in `_wp_attached_file` meta.

### Called From
Multiple files in the cocktail-images module:

1. **File**: `plugins/drinks-plugin/modules/cocktail-images/media-library-checker.php`
   - **Line**: 81
   - **Context**: Media library audit tool to verify file integrity

2. **File**: `plugins/drinks-plugin/modules/cocktail-images/media-library-checker-web.php`
   - **Line**: 225
   - **Context**: Web-based media library checker interface

3. **File**: `plugins/drinks-plugin/modules/cocktail-images/cocktail-images.php`
   - **Line**: 1753
   - **Context**: Main cocktail images processing module

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
$media = get_drinks_search()->get_all_media_attachments();
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
- Verifying that image files exist on disk
- Bulk processing of media metadata
- Generating reports on media usage
- Finding orphaned or missing files

---

## Quick Reference Table

| MODE | Method | Returns | Post Status | Taxonomy | Use Case |
|------|--------|---------|-------------|----------|----------|
| 1 | `get_all_drink_posts_query()` | WP_Query | All statuses | drinks EXISTS | Admin counts, includes drafts |
| 2 | `get_published_drink_posts()` | Array<WP_Post> | publish only | drinks EXISTS | Front-end carousels, sync ops |
| 3 | `get_all_media_attachments()` | Array<AttachmentData> | inherit | N/A | Media audit, file checks |
| 3v | `get_all_media_attachments_query()` | WP_Query | inherit | N/A | Custom media loops |

---

## Notes

### Global Helper Function
All methods can be accessed via the global helper function:
```php
$drinks_search = get_drinks_search();
```

### Performance Considerations
- All methods use `posts_per_page => -1` to retrieve ALL matching posts
- For large databases (1000+ posts), consider adding pagination or caching
- MODE 2 processes posts in a loop, which adds overhead but provides clean post objects

### Post Status Values
- **publish**: Live posts visible on the front-end
- **draft**: Unpublished posts being edited
- **pending**: Posts awaiting review
- **inherit**: Used by attachments (inherits parent post status)

### Taxonomy Note
The taxonomy is **'drinks'** (plural), not 'drink' (singular). This is consistent across all queries.

---

## Migration History

These queries were previously duplicated across multiple files:
- MODE 1: Originally in `drinks-plugin.php`
- MODE 2: Originally in `sync-drinks-metadata.php`
- MODE 3: Originally duplicated 3× in cocktail-images module files

All WP_Query operations have now been centralized into the `DrinksSearch` class for maintainability.

