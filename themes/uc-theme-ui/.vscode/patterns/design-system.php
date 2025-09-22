<?php
/**
 * Title: design-system
 * Slug: design-system
 * Categories: header
 */
?>	
<!-- Pattern code goes here. -->



<!-- wp:pattern {"slug":"header","tagName":"header"} /-->





<?php echo 
'<title>Design</title> 

<!--  link rel="stylesheet" type="text/css" href="../style.css"/  -->

<div class = "tier-one-mobile-pages">

    <div class = "home main">
            <!-- wp:template-part {"slug":"index-main","tagName":"main"} /-->
    </div>
    <div class = "contact-us main">
            <!-- wp:template-part {"slug":"contact-us-main","tagName":"main"} /-->
    </div>
    <div class = "everyday main">
            <!-- wp:template-part {"slug":"everyday-main","tagName":"main"} /-->
    </div>        
    <div class = "fireplace main">
            <!-- wp:template-part {"slug":"fireplace-main","tagName":"main"} /-->
    </div>
    <div class = "romantic main">
            <!-- wp:template-part {"slug":"romantic-main","tagName":"main"} /-->
    </div>
    <div class = "special-occasion main">
            <!-- wp:template-part {"slug":"special-occasion-main","tagName":"main"} /-->
    </div>
    <div class = "springtime main">
            <!-- wp:template-part {"slug":"springtime-main","tagName":"main"} /-->
    </div>
    <div class = "summertime main">
            <!-- wp:template-part {"slug":"summertime-main","tagName":"main"} /-->
    </div>

    <div class = "source-library main">
    
        <!-- wp:template-part {"slug":"gallery", "tagName":"div"} /--> <!-- WORKS  4/5  -->     </div>

    <div class = "main">
        <!--COPY main into new template-part or pattern-->
        <!--MUST include "tier-one" -->
        <main class = "tier-one blank placeholder">
                Future Page Here
        </main>
    </div>

</div>


<!-- wp:template-part {"slug":"footer", "tagName":"footer"}  /-->  <!-- BROKE?  1/5 --> 

' ?>
