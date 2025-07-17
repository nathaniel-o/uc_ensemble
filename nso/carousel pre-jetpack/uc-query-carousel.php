<?php
/**
 * Title: UC Query Carousel 
 * Slug: uc-query-carousel
 * Categories: featured
 */
?>




<!-- wp:group {"className":"carousel carousel-query"} -->
<div class="wp-block-group carousel carousel-query">
    <!-- wp:group {"className":"carousel__viewport"} -->
    <div class="wp-block-group carousel__viewport">
        <!-- wp:query {"queryId":17,"query":{"perPage":5,"pages":1,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list","columns":1}} -->
        <div class="wp-block-query">
            <!-- wp:post-template -->
            <!-- wp:group {"className":"carousel__slide"} -->
            <div class="wp-block-group carousel__slide">
                <!-- wp:group {"className":"carousel__snapper angled-gradient"} -->
                <div class="wp-block-group carousel__snapper angled-gradient">
                    <!-- wp:post-featured-image {"isLink":true,"width":"100%","height":"300px"} /-->
                    <!-- wp:group {"className":"carousel-subheads"} -->
                    <div class="wp-block-group carousel-subheads">
                        <!-- wp:post-title {"isLink":true,"fontSize":"large"} /-->
                        <!-- wp:post-date {"format":"F j, Y"} /-->
                    </div>
                    <!-- /wp:group -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
            <!-- /wp:post-template -->
        </div>
        <!-- /wp:query -->
    </div>I
    <!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:html -->
<script> 
window.onload = () => { 
    const lookHere = document.querySelector('.carousel');
    if (lookHere) {
        const carouselHeight = findTallestChild(lookHere) * 1.1;
        lookHere.style.height = carouselHeight + 'px';   
    }

}
</script>
<!-- /wp:html -->