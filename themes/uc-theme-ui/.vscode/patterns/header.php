<?php
/**
 * Title: header
 * Slug: header
 * Categories: header
 */
?>	
<!-- Pattern code goes here. -->




<?php 
    /*    PASTE BELOW to "carry over" PHP variables    */
	//<script>
  	//	var jsvar = <?php echo json_encode($PHPVar); ? > ;
	// < / script >
	 
	


?>


<!--div class="menu">    FROM WORDPRESS
	<ul>
		<li class="page_item page-item-27"><a href="http://localhost/wordpress/everyday-cocktails/">Everyday Cocktails</a></li>
		<li class="page_item page-item-25"><a href="http://localhost/wordpress/fireplace-cocktails/">Fireplace Cocktails</a></li>
		<li class="page_item page-item-29"><a href="http://localhost/wordpress/romantic-cocktails/">Romantic Cocktails</a></li>
		<li class="page_item page-item-32"><a href="http://localhost/wordpress/special-occasion-cocktails/">Special Occasion Cocktails</a></li>
		<li class="page_item page-item-34"><a href="http://localhost/wordpress/springtime-cocktails/">Springtime Cocktails</a></li>
		<li class="page_item page-item-36"><a href="http://localhost/wordpress/summertime-cocktails/">Summertime Cocktails</a></li>
	</ul>
</div-->
<div>
<!-- NAVIGATION MENU  -->
<nav class = "topnav">
            
<a href="index.html" >
	<figure class = "icon">
				<img src = "https://untouchedcocktails.com/wp-content/uploads/2023/04/logo062022.jpg" />
	</figure>
	</a>
	  
	<div class="write-over">
		<p id="size-this"> 
			UntouchedCocktails.com © 2024   </p>
		<p id="size-this">
			information@untouchedcocktails.com    </p>
		<p id="size-this"> 
			# (617) 759-2057    </p>
	</div>



			 
			<div id = "tierOne">
				<a href="http://localhost/wordpress/fireplace-cocktails/" class="page">Fireplace Cocktails</a>
				<a href="special-occasion.html" class="page">Special Occasion Cocktails</a>
				<a href="http://localhost/wordpress/everyday-cocktails/" class="page">EveryDay Cocktails</a>
				<a href="summertime.html" class="page">Seasonal Cocktails</a>
				<a href="romantic.html" class="page">Romantic Cocktails</a>
				<a href="http://localhost/wordpress/contact-us/" class="page">Learn More</a>
				<a href="index.html" class="page">Home Page</a>
				<a href="http://localhost/wordpress/an_gallery/" class="page">Gallery</a>
				<a href ="" class="page">Blog</a>
			</div>


		   
		<!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
		<a onclick="myFunction()">
			<div class ="hamburger icon">	</div>
		</a> 
</nav>
</div>





<?php 

$page_id = uc_page_id($_SERVER['REQUEST_URI']);
echo '<script> console.log( "test" );</script>';


//echo "<pre>". $page_id ."</pre>";

$dynamic_h1 = uc_dynamic_h1($page_id);
echo $dynamic_h1;


echo "<script> var pageID = '$page_id'; </script>";


/*
Do Some Styling, early in page load
*/
echo "<script> ucInsertBackground(); ucColorH1(); </script>";





#echo "<pre> Any String Here </pre>";

?>


<!-- wp:template-part {"slug":"source-data", "tagName":"source-data"}  /-->





<!--  wp:pattern {"slug":"carousell", "tagName":"div"}  /-->




