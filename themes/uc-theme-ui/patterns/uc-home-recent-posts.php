<?php
/**
 * Title: Home Recent Posts
 * Slug: uc-theme-ui/home-recent-posts
 * Description: Recent posts excluding site/admin authors (nathaniel, juliarotoole25229, information43e7b5f30d).
 * Categories: posts, query
 * Keywords: home, recent, posts, query
 * Viewport Width: 1200
 * Inserter: true
 */
?>
<!-- wp:query {"queryId":10,"namespace":"uc-theme-ui/home-recent-posts","query":{"perPage":20,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":null,"parents":[],"format":[],"ucExcludeAuthors":["nathaniel","juliarotoole25229","information43e7b5f30d"]}} -->
<div class="wp-block-query">
	<!-- wp:post-template -->
	<!-- wp:drinks/drink-post-content /-->
	<!-- /wp:post-template -->

	<!-- wp:query-pagination -->
	<!-- wp:query-pagination-previous /-->
	<!-- wp:query-pagination-numbers /-->
	<!-- wp:query-pagination-next /-->
	<!-- /wp:query-pagination -->

	<!-- wp:query-no-results -->
	<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
	<p></p>
	<!-- /wp:paragraph -->
	<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
