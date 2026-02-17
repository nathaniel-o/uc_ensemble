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

/**
 * Output a single timeline chron entry (horlage right or left).
 *
 * @param string $side     'right' or 'left' — horlage alignment.
 * @param int    $position X position as percentage (echoed as left: N%;).
 * @param string $date     Date label for the entry.
 * @param string $memory   Description text for the entry.
 * @param string $id       Element id (e.g. 'chron12'). Least important; last.
 */
function timeline_chron_entry( $side, $position, $date, $memory, $id ) {
	$side = in_array( $side, array( 'left', 'right' ), true ) ? $side : 'right';
	$position = (int) $position;
	?>
	<div class="horlage <?php echo esc_attr( $side ); ?>" id="<?php echo esc_attr( $id ); ?>" style="left: <?php echo $position; ?>%;">
		<div class="content">
			<h5 class="h5"><?php echo esc_html( $date ); ?></h5>
			<p class="memory"><?php echo esc_html( $memory ); ?></p>
		</div>
	</div>
	<?php
}

?>

<div class = "timeCo"> 
			
			<h3 class="h3"><u>Untouched Cocktails ~ Story Unfolds</u></h3>
	
 </p>
			<div class="ruler">
				
						<?php
						/* chron12=newest (2026) → chron1=oldest (2014); 2nd param = left % (x position). */
						timeline_chron_entry( 'right', 0,   $d12[0], $d12[1], 'chron12' );
						timeline_chron_entry( 'left',  15,  $d11[0], $d11[1], 'chron11' );
						timeline_chron_entry( 'right', 45,  $d10[0], $d10[1], 'chron10' );
						timeline_chron_entry( 'left',  60,  $d9[0],  $d9[1],  'chron9' );
						timeline_chron_entry( 'right', 90, $d8[0],  $d8[1],  'chron8' );
						timeline_chron_entry( 'left',  105,  $d7[0],  $d7[1],  'chron7' );
						timeline_chron_entry( 'right', 135, $d6[0],  $d6[1],  'chron6' );
						timeline_chron_entry( 'left',  150, $d5[0],  $d5[1],  'chron5' );
						timeline_chron_entry( 'right', 180, $d4[0],  $d4[1],  'chron4' );
						timeline_chron_entry( 'left',  195, $d3[0],  $d3[1],  'chron3' );
						timeline_chron_entry( 'right', 240, $d2[0],  $d2[1],  'chron2' );
						timeline_chron_entry( 'left',  285, $d1[0],  $d1[1],  'chron1' );
						?>
						</div>
							
						  <!--EXPERIMENT HEREIN-->
							
					</div>

</div>    <!--END of timeCo-->

