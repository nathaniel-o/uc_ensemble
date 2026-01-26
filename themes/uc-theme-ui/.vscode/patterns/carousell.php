<?php
/**
 * Title: carousell
 * Slug: carousell
 * Categories: carousell
 */
?>	
<!-- Pattern code goes here. -->

<!--  SEARCH BAR  -->

<section class = "srch">
      <input type="search" id="ucQuery" name="searchBar" placeholder="Explore Drinks"/>

      <button class="testBtn"><label for="ucQuery">
         <!-- an Iconoir import , where stroke = "color"  -->
         
         </label></button>
</section>



<?php 
$old_carousel = ' 
<section class="carousel hidden" aria-label="Gallery">
  <ol class="carousel__viewport">

    <li id="carousel__slide1"  tabindex="0"  class="carousel__slide">
      
        <div class="carousel__snapper">
            <a href="#carousel__slide4"
               class="carousel__prev">
                        
               </a>
        
           <a href="#carousel__slide2"
              class="carousel__next">
              
              </a>
         
      </div>
    </li>

    <li id="carousel__slide2"
        tabindex="0"
        class="carousel__slide">
      <div class="carousel__snapper">

      
 
        
      <a href="#carousel__slide1"
         class="carousel__prev">

         </a>
      <a href="#carousel__slide3"
         class="carousel__next">
         
         </a>
        
         </div>
    </li>

    <li id="carousel__slide3"
        tabindex="0"
        class="carousel__slide">
      <div class="carousel__snapper"></div>

          
      <a href="#carousel__slide2"
         class="carousel__prev">

                        </a>
      <a href="#carousel__slide1"
         class="carousel__next">
         

         </a>
         
       
    </li>
    

  </ol> 


  <!-- NOT IN USE 
      li id="carousel__slide4"
        tabindex="0"
        class="carousel__slide">
      <div class="carousel__snapper">
      
      </div>
      
      <a href="#carousel__slide3"
         class="carousel__prev">Go to previous slide</a>
      <a href="#carousel__slide1"
         class="carousel__next">Go to first slide</a>
         
   </li>



   <aside class="carousel__navigation">
    <ol class="carousel__navigation-list">
      <li class="carousel__navigation-item">
        <a href="#carousel__slide1"
           class="carousel__navigation-button">Go to slide 1</a>
      </li>
      <li class="carousel__navigation-item">
        <a href="#carousel__slide2"
           class="carousel__navigation-button">Go to slide 2</a>
      </li>
      <li class="carousel__navigation-item">
        <a href="#carousel__slide3"
           class="carousel__navigation-button">Go to slide 3</a>
      </li>
      <li class="carousel__navigation-item">
        <a href="#carousel__slide4"
           class="carousel__navigation-button">Go to slide 4</a>
           </li>
    </ol>
  </aside-->


</section>';

$new_carousel ='


<section class ="carousel">

   <div class = "viewport">


      <div class = "carousel_snapper" id = "carousel_slide1">
         <a href="#carousel_slide4">
         <?xml version="1.0" encoding="UTF-8"?>
         <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M11 6L5 12L11 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 6L13 12L19 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
         <a href="#carousel__slide2" class ="carousel_next">
            <?xml version="1.0" encoding="UTF-8"?>
            <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M13 6L19 12L13 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 6L11 12L5 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
      </div>

      <div class = "carousel_snapper" id = "carousel_slide2">
         <a href="#carousel_slide1">
            <?xml version="1.0" encoding="UTF-8"?>
            <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M11 6L5 12L11 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 6L13 12L19 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
         <a href="#carousel__slide3" class ="carousel_next">
            <?xml version="1.0" encoding="UTF-8"?>
            <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M13 6L19 12L13 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 6L11 12L5 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
      </div>
   
      <div class = "carousel_snapper" id = "carousel_slide3">
         <a href="#carousel_slide2">
            <?xml version="1.0" encoding="UTF-8"?>
            <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M11 6L5 12L11 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 6L13 12L19 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
         <a href="#carousel__slide4" class ="carousel_next">
            <?xml version="1.0" encoding="UTF-8"?>
            <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M13 6L19 12L13 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 6L11 12L5 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
      </div>

      <div class = "carousel_snapper" id = "carousel_slide4">
         <a href="#carousel_slide3">
            <?xml version="1.0" encoding="UTF-8"?>
            <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M11 6L5 12L11 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 6L13 12L19 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
         <a href="#carousel__slide2" class ="carousel_next">
            <?xml version="1.0" encoding="UTF-8"?>
            <svg width="6em" height="6em" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M13 6L19 12L13 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 6L11 12L5 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
         </a>
      </div>


   </div>

</section>';




echo $old_carousel;

?>




