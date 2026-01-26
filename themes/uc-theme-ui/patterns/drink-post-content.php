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

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:media-text {"mediaId":0,"mediaLink":"","mediaType":"image"} -->
    <div class="wp-block-media-text alignwide is-stacked-on-mobile">
        <figure class="wp-block-media-text__media">
            <img src="" alt="" class="wp-image-0" />
        </figure>
        
        <div class="wp-block-media-text__content">
            <!-- w-p-:-h-e-a-d-i-n-g- --{-"-l-e-v-e-l-"-:1-}- - - ->
            <h1>Drink Name</h1>
            <! - - -/-w-p-:-h-ea-d-i-n-g- -->

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
</div>
<!-- /wp:group -->