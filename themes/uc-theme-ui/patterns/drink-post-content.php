<?php
/**
 * Title: Drink Post Content
 * Slug: uc-theme-ui/drink-post-content
 * Description: A pattern for displaying drink content with image and details
 * Categories: drinks, content
 * Keywords: drink, cocktail, recipe, details
 * Viewport Width: 1200
 * Block Types: core/post-content
 * Inserter: true
 */
?>

<!-- wp:group {"className":"pop-off"} -->
<div class="wp-block-group pop-off">
    <!-- wp:media-text {"mediaType":"image","align":"wide"} -->
    <div class="wp-block-media-text alignwide is-stacked-on-mobile">
        <figure class="wp-block-media-text__media"></figure>
        
        <div class="wp-block-media-text__content">
            <!-- wp:heading {"level":1} -->
            <h1 class="hidden">Drink Name</h1>
            <!-- /wp:heading -->

            <!-- wp:list -->
            <ul>
                <li><em>Category</em>: </li>
                <li><em>Color</em>: </li>
                <li><em>Glass</em>: </li>
                <li><em>Garnish</em>: </li>
                <li><em>Base</em>: </li>
                <li><em>Ice</em>: </li>
            </ul>
            <!-- /wp:list -->
        </div>
    </div>
    <!-- /wp:media-text -->
<!-- </div> -->
<!-- /wp:group -->
