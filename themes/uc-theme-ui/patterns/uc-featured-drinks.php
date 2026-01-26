<?php
/**
 * Title: UC-Featured-Drinks
 * Slug: uc-featured-drinks
 * Categories: 
 */
?>	
<!-- Pattern code goes here. -->


 
 <!-- wp:query {"queryId":1,"query":{"perPage":50,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
	 <div class="wp-block-query">
        <!-- wp:post-template {"layout":{"type":"grid","columnCount":1}} -->
            <!-- wp:columns {"verticalAlignment":"center"} -->
            <div class="wp-block-columns are-vertically-aligned-center">
                <!-- wp:column {"width":"40%"} -->
                <div class="wp-block-column" style="flex-basis:40%">
                    <!-- wp:post-featured-image {"isLink":true,"width":"100%","height":"auto"} /-->
                </div>
                <!-- /wp:column -->

                <!-- wp:column {"width":"60%"} -->
                <div class="wp-block-column" style="flex-basis:60%">
                    <!-- wp:post-title {"isLink":true,"fontSize":"x-large"} /-->
                    <!-- wp:group {"className":"drink-metadata"} -->
                    <div class="wp-block-group drink-metadata">
                        <!-- wp:list -->
                        <?php
                        $drinks = get_the_terms(get_the_ID(), 'drinks');
                        $color = get_post_meta(get_the_ID(), 'drink_color', true);
                        $glass = get_post_meta(get_the_ID(), 'drink_glass', true);
                        $garnish = get_post_meta(get_the_ID(), 'drink_garnish1', true);
                        $base = get_post_meta(get_the_ID(), 'drink_base', true);
                        $ice = get_post_meta(get_the_ID(), 'drink_ice', true);
                        ?>
                        <ul>
                            <?php if ($drinks) : ?>
                                <li><em>Category</em>: <?php echo esc_html($drinks[0]->name); ?></li>
                            <?php endif; ?>
                            <?php if ($color) : ?>
                                <li><em>Color</em>: <?php echo esc_html($color); ?></li>
                            <?php endif; ?>
                            <?php if ($glass) : ?>
                                <li><em>Glass</em>: <?php echo esc_html($glass); ?></li>
                            <?php endif; ?>
                            <?php if ($garnish) : ?>
                                <li><em>Garnish</em>: <?php echo esc_html($garnish); ?></li>
                            <?php endif; ?>
                            <?php if ($base) : ?>
                                <li><em>Base</em>: <?php echo esc_html($base); ?></li>
                            <?php endif; ?>
                            <?php if ($ice) : ?>
                                <li><em>Ice</em>: <?php echo esc_html($ice); ?></li>
                            <?php endif; ?>
                        </ul>
                        <!-- /wp:list -->
                    </div>
                    <!-- /wp:group -->
                    <!-- wp:read-more {"content":"Read More","style":{"spacing":{"margin":{"top":"1rem"}}}} /-->
                    <!-- wp:post-date /-->
                </div>
                <!-- /wp:column -->
            </div>
            <!-- /wp:columns -->
        <!-- /wp:post-template -->
        
        <!-- wp:query-pagination -->
            <!-- wp:query-pagination-previous /-->
            <!-- wp:query-pagination-numbers /-->
            <!-- wp:query-pagination-next /-->
        <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:query -->