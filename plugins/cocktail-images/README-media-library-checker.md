# Media Library Checker Scripts

This directory contains scripts to analyze your WordPress media library and test the `ucDoesImageHavePost` logic from the Cocktail Images plugin.

## What These Scripts Do

The scripts check every image in your WordPress media library and test whether they match with posts using the same logic as the `ucDoesImageHavePost` function. This helps you understand:

- Which images have matching posts
- Which images have exact title matches vs partial matches
- Which images have no matches at all
- The overall effectiveness of the matching algorithm

## Files Included

1. **`media-library-checker.php`** - Command-line version
2. **`media-library-checker-web.php`** - Web-based version
3. **`README-media-library-checker.md`** - This documentation

## How to Use

### Option 1: Command Line Script

1. **Navigate to your WordPress root directory:**
   ```bash
   cd /path/to/your/wordpress
   ```

2. **Run the script:**
   ```bash
   php media-library-checker.php
   ```

3. **Check the results:**
   The script will create a file called `media-library-results.txt` with detailed analysis.

### Option 2: Web-Based Script

1. **Access via browser:**
   ```
   http://your-site.com/media-library-checker-web.php
   ```

2. **Click "Run Analysis"** to start the process

3. **View results** in the browser with a nice interface

4. **Download results** as a text file if needed

## What the Analysis Shows

### Summary Statistics
- Total images processed
- Number of matched images
- Number of unmatched images
- Number of exact matches
- Number of partial matches
- Number of errors
- Match rate percentage

### Detailed Results for Each Image
- Image ID and title
- Alt text
- Normalized title (after processing)
- Match status (exact match, partial match, or no match)
- Primary matching post (if any)
- Other matching posts (if any)
- Links to matching posts

### Special Sections
- **Unmatched Images List** - Images that don't match any posts
- **Exact Matches List** - Images with perfect title matches

## Understanding the Matching Logic

The scripts use the same logic as the `ucDoesImageHavePost` function:

1. **Title Normalization:**
   - Removes T2- prefix
   - Replaces hyphens and underscores with spaces
   - Normalizes multiple spaces
   - Filters out words shorter than 3 letters
   - Truncates at colons

2. **Matching Process:**
   - First tries a general search using the normalized title
   - If no results, tries exact title matching
   - If still no results, tries partial matching with individual words
   - Prioritizes exact matches over partial matches

## Example Output

```
=== Media Library Analysis Report ===
Generated: 2024-01-15 14:30:25

SUMMARY STATISTICS:
==================
Total images processed: 150
Matched images: 89
Unmatched images: 61
Exact matches: 45
Partial matches: 44
Errors: 0

Match rate: 59.33%

DETAILED RESULTS:
=================

Image 1:
  ID: 123
  Title: T2-Margarita-Cocktail
  Alt: Margarita cocktail
  Normalized Title: margarita cocktail
  Status: EXACT MATCH
  Total matches found: 1
  Primary match: Margarita Cocktail (ID: 456)
  Primary match URL: http://your-site.com/margarita-cocktail/
```

## Security Notes

- The web version requires admin privileges
- Both scripts should be removed after use in production
- The scripts only read data and don't modify anything

## Troubleshooting

### Common Issues

1. **"Access denied" error:**
   - Make sure you're logged in as an administrator
   - Check file permissions

2. **"No media attachments found":**
   - Verify you have images in your media library
   - Check that the script is running from the correct WordPress directory

3. **Memory/timeout errors:**
   - For large media libraries, consider running the command-line version
   - You may need to increase PHP memory limits

### Performance Tips

- The web version is better for smaller media libraries (< 500 images)
- The command-line version is better for larger libraries
- Results are cached in memory during processing

## Customization

You can modify the scripts to:

- Change the output file name
- Adjust the number of matches returned
- Add additional analysis criteria
- Export results in different formats (CSV, JSON, etc.)

## Support

If you encounter issues:

1. Check that you're running the script from the WordPress root directory
2. Verify you have admin privileges
3. Check your WordPress installation is working correctly
4. Review the error messages for specific issues

## Cleanup

After using the scripts:

1. **Remove the script files** from your WordPress directory
2. **Delete any result files** you no longer need
3. **Consider backing up** the results if they're important

---

**Note:** These scripts are for analysis purposes only and do not modify your WordPress installation or media library.
