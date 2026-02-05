<?php
/**
 * Title: timeline
 * Slug: timeline
 * Categories: featured
 * Description: an_timeline
 */
?>
<?php
 /*
 *  Declare timeline Entries
 * where $d1 is the First date Chronologically 
 *(furthest right/bottom on timeline)
 */

 $d1 = ["2014", "After years of enjoying cocktails crafted at the kitchen counter, Julia's husband
(Bob) decides to build her a professional-grade bar, using blended woodgrains
and finished with an exquisite curved moulding."];
 $d2 = ["2015", "Julia begins to explore new types of liquors and mixers, experimenting with colors and garnishes as part of the cocktail experience."];
 $d3 = ["2017", "Bob begins taking photos of cocktails – the beginning of our comprehensive
library. The images motivate research into layering, liquid density, infusion, and
other mixing techniques; the spectrum of possibilities grows exponentially!"];
 $d4 = ["2020", "COVID-19 lockdown inspires more creativity, resulting in more attention on the physical appearance (in addition to taste profile) of each cocktail. Julia joins Bob in taking and keeping a visual library of cocktails, and on average, three or four cocktails (with 8-10 pictures per cocktail) are captured each week."];
 $d5 = ["2021", "Experimentation with botanicals, glass shapes, and garnishes. But what are we going to DO with all of these photos?!"];
 $d6 = ["April 2022", "Nathaniel and Julia discuss the possibility of building a gallery-based website where the photos can be publicly shared. We continue to take new pictures."];
 $d7 = ["2022", "Nathaniel and Julia discuss the possibility of building a gallery-based website where the photos can be publicly shared. We decide upon a visually-driven philosophy, approaching cocktails as works of art, rather than recipes. We establish parameters and begin sketching out what the website might look like. Logo design is complete! Nathaniel begins the website build, researching coding and scripting. Our existing library of (literally) thousands of photos is sorted, vetted, and catalogued. Meanwhile, the library continues to grow."];
 $d8 = ["Spring 2023", "The composition of our site is finalized, using cocktail categories as an organizing structure, and including site details such as color, shapes, and layout. We begin to explore new and different glass profiles to enrich the visual variety of cocktails. The Home Page is both beautiful and functional! A systematic labelling of searchable terms is applied to all of the photos in the library. We experiment with new photo environments."];
 $d9 = ["Fall 2023", "Nathaniel introduces the more complex scripting aspects of the site's functionality. We continue to build the library, and using a defined set of parameters, tag photos so that visitors to the website may search by elements seen in the pictures."];
 $d10 = ["2024", "Overhaul of the color schematics for the website pages, so that search results assist users in exploring the gallery more organically. Nathaniel incorporates new functionality using existing plug-ins as well as writing his own, thereby further customizing the user's experience on the site. Meanwhile, Julia starts an in-person cocktail experience business (Clear Ice &amp; Bitters) that will become a source of additional and new images to add to the gallery, maintaining the key principle that photos used for UntouchedCocktails.com are unedited or filtered, taken before a first sip."];
 $d11 = ["2025", "Beta testing begins on the desktop site, with subsequent changes to coding, additional plug-ins, and several revisions of visual presentation."];
 $d12 = ["2026", "Beta testing is performed the mobile site, and then UntouchedCocktails.com goes LIVE! Visitors can enjoy a gallery of Fireplace, Everyday, Special Occasion, Romantic, and Seasonal Cocktails; search ingredients, glasses, garnishes, and other elements; and Contact Us for more information."];

?>

<div class = "timeCo"> 
			
			<h3 class="h3"><u>Untouched Cocktails ~ Story Unfolds</u></h3>
					
 
			<div class="ruler">
						<!-- 2 -->
						<div class = "horlage right" id="chron11">
						<div class = "content">
							<h5 class="h5"><?php echo $d10[0]; ?></h5>
							<p class = "memory"><?php echo $d10[1]; ?></p>
						</div>
						</div>
						
						<!-- 1 -->
						<div class = "horlage left" id="chron12">
						<div class = "content">
							<h5 class="h5"><?php echo $d11[0]; ?></h5>
							<p class = "memory"><?php echo $d11[1]; ?></p>
						</div>
						</div>
						
						<!-- 4 -->
						<div class = "horlage right" id="chron9">
						<div class = "content">
							<h5 class="h5"><?php echo $d8[0]; ?></h5>
							<p class = "memory"><?php echo $d8[1]; ?></p>
						</div>
						</div>
						
						<!-- 3 --> 
						<div class = "horlage left" id="chron10">
						<div class = "content">
							<h5 class="h5"><?php echo $d9[0]; ?></h5>
							<p class = "memory"> <?php echo $d9[1]; ?>
							</p>
						</div>
						</div>
						
						<!-- 6 -->
						<div class = "horlage right" id="chron8">
						<div class = "content">
							<h5 class="h5"><?php echo $d6[0]; ?></h5>
							<p class = "memory"> <?php echo $d6[1]; ?></p>
						</div>
						</div>
						
						<!-- 5 -->
						<div class = "horlage left" id="chron7">
						<div class = "content">
							<h5 class="h5"><?php echo $d7[0]; ?></h5>
							<p class = "memory"><?php echo $d7[1]; ?></p>
						</div>
						</div>
						
						<!-- 8 -->
						<div class = "horlage right" id="chron6">
						<div class = "content">
						<h5 class="h5"><?php echo $d4[0]; ?></h5>
						<p class = "memory"> <?php echo $d4[1]; ?>
							</p>
						</div>
						</div>
						
						<!-- 7 -->
						<div class = "horlage left" id="chron5">
						<div class = "content">
							<h5 class="h5"><?php echo $d5[0]; ?></h5>
							<p class = "memory"><?php echo $d5[1]; ?>					
							</p>
						</div>
						</div>

						<!-- ? -->
						<div class = "horlage right" id="chron4">
						<div class = "content">
							<h5 class="h5"><?php echo $d4[0]; ?></h5>
							<p class = "memory"><?php echo $d4[1]; ?></p>
						</div>
						</div>
						
						<!-- 10 -->
						<div class = "horlage left" id="chron2">
						<div class = "content">
							<h5 class="h5"><?php echo $d2[0]; ?></h5>
							<p class = "memory"><?php echo $d2[1] ;?></div>
							</p>
						</div>
						
						<!-- 9 -->
						<div class = "horlage right" id="chron3">
						<div class = "content">
							<h5 class="h5"> <?php echo $d3[0]; ?></h5>
							<p class = "memory"><?php echo $d3[1]; ?></p>
						</div>
						</div>
						
						<!-- 12 
						<div class = "horlage left" id = "left6">
						<div class = "content">
							<h5 class="h5">March 2020</h5>
							<p class = "memory">COVID-19 lockdown inspires more creativity, resulting in more attention on the physical appearance (in addition to taste profile) of each cocktail. </p>
						</div>
						</div-->
						
						<!-- 11 -->
						<div class = "horlage right" id="chron1">
						<div class = "content">
							<h5 class="h5"> <?php echo $d1[0]; ?></h5>
							<p class = "memory"> <?php echo $d1[1]; ?></p>
						</div>
						</div>
						
						
						</div>
							
						  
						  <!--EXPERIMENT HEREIN-->
							
					</div>

</div>    <!--END of timeCo-->

