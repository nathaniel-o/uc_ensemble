/*
*   Functions wrapped in DOM listener by functions.php :   
*		ucInsertTierOneBg / ucInsertDrinkPostsBg (as testing_backgrounds) , 
*		styleImagesByPageID , 
*
*
*/


	function styleImagesByPageID(variableID, targetContainer) {
		
		if(pageID.includes("springtime")){
			variableID = "summertime";
		}  //  (Else variableID = pageID as passed in functions.php)


		// Compose variable names
		const borderVar = `var(--${variableID}-border)`;
		const fontColorVar = `var(--${variableID}-font-color)`;
		const shadowVar = `var(--${variableID}-shadow)`;

		/* console.log(borderVar);
		console.log(fontColorVar);
		console.log(shadowVar); */

		if(!targetContainer){
			targetContainer = '.entry-content';
		}

		// Get all images within .entry-content
		const imageContainer = document.querySelector(targetContainer);
		if (!imageContainer) {    //  If no target, no action. 
			return;
		}
	
		const images = imageContainer.querySelectorAll('img');

		images.forEach(img => {
			// 1. Apply border variable
			img.style.border = borderVar;

/* 			console.log(img);
 */
			// 2 & 3. If image is in a figure with figcaption, style the caption
			const figure = img.closest('figure');
			if (figure) {
				const caption = figure.querySelector('figcaption');
				if (caption) {
					caption.style.color = fontColorVar;
					caption.style.textShadow = shadowVar;
				}
			}
		});
	}

	/*
		Simple background function that works for all page types
		Now that pageID is set to drinks taxonomy for single posts, we can use one function
	*/
	function ucStyleBackground(){
		let anPage = document.querySelector("body");

		let bgImgVar;
		// Build CSS variables using pageID (which is now drinks taxonomy for single posts)
		let bgColorVar = 'var(--' + pageID + '-bg-color)';
		//console.log("DEBUG: pageID =", pageID);
		//console.log("Setting background color: " + bgColorVar);
		

		if(!pageID.includes('autumnal') && !pageID.includes('springtime') && !pageID.includes('winter') && !pageID.includes('fireplace') && !pageID.includes('romantic') && !pageID.includes('summertime') && !pageID.includes('special-occasion')){
			bgImgVar = 'var(--' + pageID + '-bg-img)';
			console.log("Setting background image: " + bgImgVar);
			// Apply background color and image for non-autumnal/non-springtime/non-winter/non-fireplace/non-romantic pages
			
			anPage.style.backgroundImage = bgImgVar;

		} else if(pageID.includes('springtime') || pageID.includes('summertime') || pageID.includes('winter') || pageID.includes('autumnal') || pageID.includes('fireplace') || pageID.includes('special-occasion')){
			// Create repeating pattern for springtime, summertime, winter, fireplace	
			ucCreateRepeatingPattern(pageID);
		}else if(pageID.includes('romantic')){
				// For romantic, create single full-coverage SVG with CSS object-fit	
					ucCreateFullCoverageSvg(pageID);
		}
				anPage.style.backgroundColor = bgColorVar;
	}
		
	

	function ucCreateRepeatingPattern(pageType) {
		const containerId = pageType + '-svg-container';
		const container = document.getElementById(containerId);
		
		if (!container) return;
		
		const originalSvg = container.querySelector('svg');
		if (!originalSvg) return;
		
		// Clear existing content
		container.innerHTML = '';
		
		// Get container dimensions
		const containerRect = container.getBoundingClientRect();
		const containerWidth = containerRect.width;
		const containerHeight = containerRect.height;
		console.log(`[BG] ${pageType} container size:`, containerWidth, 'x', containerHeight);
		
		// Set pattern size based on page type
		let patternWidth, patternHeight;
		if (pageType.includes('springtime') || pageType.includes('summertime')) {     /* summertime funnels to springtime logic */
			patternWidth = 500;
			patternHeight = 1200;  /* Increased from 800 to add vertical spacing */ 
		} else if (pageType.includes('special-occasion')) {
			patternWidth = 500;
			patternHeight = 900;  /* Same as springtime/summertime */
		} else if (pageType.includes('winter')) {
			patternWidth = 600;
			patternHeight = 800;
		} else if (pageType.includes('fireplace')) {
			patternWidth = 600;
			patternHeight = 800;
		} else if (pageType.includes('autumnal')) {
			patternWidth = 600;
			patternHeight = 800;
		} else if (pageType.includes('romantic')) {
			patternWidth = 325;  // One complete SVG = one tile
			patternHeight = 400;
		}
		
		// Calculate how many repetitions we need
		const cols = Math.ceil(containerWidth / patternWidth) + 1;
		const rows = Math.ceil(containerHeight / patternHeight) + 1;
		
		// Create repeating pattern
		for (let row = 0; row < rows; row++) {
			for (let col = 0; col < cols; col++) {
				const svgClone = originalSvg.cloneNode(true);
				// Ensure sizing is controlled by JS, not inline attributes from source
				svgClone.removeAttribute('width');
				svgClone.removeAttribute('height');
				// Favor consistent aspect behavior
				svgClone.setAttribute('preserveAspectRatio', 'xMidYMid meet');
				// Remove any embedded <style> tags inside the SVG that may override our CSS
				try {
					const embeddedStyles = svgClone.querySelectorAll('style');
					embeddedStyles.forEach(s => s.remove());
				} catch(e) { /* ignore */ }
				svgClone.style.position = 'absolute';
				svgClone.style.left = (col * patternWidth) + 'px';
				svgClone.style.top = (row * patternHeight) + 'px';
				svgClone.style.width = patternWidth + 'px';
				svgClone.style.height = patternHeight + 'px';
				

				// Make Springtime, Summertime & Special Occasion glasses SVG fit better.
				if(pageID.includes("summertime") || pageID.includes("springtime") || pageID.includes("special-occasion")){
					svgClone.setAttribute('viewBox', '0 0 500 1200');  /*  MODIFY HERE TO CHANGE "FIT" of SHAPES?   */ 
					svgClone.setAttribute('preserveAspectRatio', 'xMidYMid meet');  /*  close to actual content size = better "fit"  */ 
				}
				container.appendChild(svgClone);
			}
		}
		
		console.log(`Created repeating pattern for ${pageType}: cols=${cols}, rows=${rows}, total=${cols * rows}`);
	}

	function ucCreateFullCoverageSvg(pageType) {
		const containerId = pageType + '-svg-container';
		const container = document.getElementById(containerId);
		
		if (!container) return;
		
		const originalSvg = container.querySelector('svg');
		if (!originalSvg) return;
		
		// Clear existing content
		container.innerHTML = '';
		
		// Get container dimensions
		const containerRect = container.getBoundingClientRect();
		const containerWidth = containerRect.width;
		const containerHeight = containerRect.height;
		
		// Create single SVG - no repeats
		const svgClone = originalSvg.cloneNode(true);
		svgClone.style.position = 'absolute';
		svgClone.style.left = '0px';
		svgClone.style.top = '0px';
		svgClone.style.width = '100%';
		svgClone.style.height = '100%';
		//svgClone.style.transform = 'scale(0.5)'; // Scale down to 50%
		svgClone.style.transformOrigin = 'center'; // Scale from center
		
		// Set the correct viewBox to show all content
		// The clipPath shows content area is 718.37489 x 1277.1528
		svgClone.setAttribute('viewBox', '350 0 718.37489 1277.1528');  /*  MODIFY HERE TO CHANGE "FIT" of HEARTS  */ 
		svgClone.setAttribute('preserveAspectRatio', 'xMidYMid meet');
		
		container.appendChild(svgClone);
		
		console.log(`Created single SVG for ${pageType}: ${containerWidth}x${containerHeight}px`);
	}



	function ucColorH1(){

			var headings = document.querySelectorAll("h1");
					// Where more than one h1 exists...
					for (let i = 0; i < headings.length; i++){
						var heading = headings[i];
						
						// Reset any existing inline styles
						heading.style.cssText = '';
						
						// Apply page-specific styling based on pageID
							if(pageID.includes("everyday")){
								heading.style.color = "var(--everyday-font-color)";
								heading.style.textShadow = "var(--everyday-text-shadow)";
							heading.style.fontFamily = "var(--std-baskerville-font)";
							heading.style.accentColor = "var(--everyday-accent-color)";
						}
							else if(pageID.includes("romantic")){
								heading.style.color = "var(--romantic-font-color)";
								heading.style.textShadow = "var(--romantic-text-shadow)";
							heading.style.accentColor = "var(--romantic-accent-color)";
						}
							else if(pageID.includes("summertime")){
								heading.style.color = "var(--summertime-font-color)";
								heading.style.textShadow = "var(--summertime-text-shadow)";
						}
							else if(pageID.includes("springtime")){
								heading.style.color = "var(--summertime-font-color)";
								heading.style.textShadow = "var(--summertime-text-shadow)";
						}
							else if(pageID.includes("fireplace")){
								heading.style.color = "var(--fireplace-font-color)";
								heading.style.textShadow = "var(--fireplace-text-shadow)";
						}
							else if(pageID.includes("special-occasion")){
								heading.style.fontFamily = "var(--special-occasion-header-font)";
								heading.style.color = "var(--special-occasion-font-color)";
								heading.style.textShadow = "var(--special-occasion-text-shadow)";
						}
						else if(pageID.includes("gallery")){
							heading.style.color = "var(--gallery-font-color)";
							heading.style.textShadowColor = "var(--std-text-shadow)";
						}
						else if(pageID.includes("home")){
							heading.style.color = "var(--std-font-color)";
							heading.style.textShadowColor = "var(--std-text-shadow)";
							
						}
							else if(pageID.includes("winter")){
								heading.style.color = "var(--winter-font-color)";
								heading.style.textShadow = "var(--winter-text-shadow)";
						}
							else if(pageID.includes("autumnal")){
								heading.style.color = "var(--autumnal-font-color)";
								heading.style.textShadow = "var(--std-text-shadow)";
						}
						else {
							// Default styling for other pages
							heading.style.color = "var(--std-font-color)";
							heading.style.textShadowColor = "var(--std-text-shadow)";
							heading.style.fontFamily = "var(--std-baskerville-font)";
						}
						
						// Apply common styling to all headings
						heading.style.padding = "0.5rem 1rem";
						heading.style.margin = "1rem 0";
						heading.style.textAlign = "center";
						heading.style.borderRadius = "4px";
						heading.style.transition = "var(--std-transition)";
						
					}

					console.log("H1 styling complete for", headings.length, " : ", pageID, "headings");


	}




			function updateImageLinks() {
				// Find all figures with images and captions
				const figures = document.querySelectorAll('figure.wp-block-image');
				
				figures.forEach(figure => {
					const link = figure.querySelector('a');
					const caption = figure.querySelector('figcaption');
					
					if (link && caption) {
						// Get the href from the existing link
						const href = link.getAttribute('href');
						
						// Create a new link that will wrap everything
						const newLink = document.createElement('a');
						newLink.href = href;
						
						// Move the image into the new link
						const img = link.querySelector('img');
						newLink.appendChild(img);
						
						// Move the figcaption into the new link
						newLink.appendChild(caption);
						
						// Remove the old link
						link.remove();
						
						// Add the new link to the figure
						figure.appendChild(newLink);
					}
				});
			}
			
			// Run when the DOM is fully loaded
			document.addEventListener('DOMContentLoaded', updateImageLinks);



			





			/*	Repurposed from NavBar to Generic for Carousel, etc.  */
			function showHide(lmnt) {
				const element = document.querySelector(lmnt);
				console.log(element);
				if (element) {
					if (element.style.display === "none") {


						element.style.display = "block";
					} else {
						element.style.display = "none";
					}
				}
			}


			function ucAjaxCarousel(e){

				//console.log(e.target)
				e.preventDefault(); //Stop page refresh

				let searchValue = '';

				// Check if event target is an image
				if (e.target.tagName === 'IMG') {
					// Find the closest parent with .post & .post-#### class
					const postElement = e.target.closest('.post[class*="post-"]');
					if (postElement) {
						// Get the post title from within this element
						searchValue = postElement.querySelector('.wp-block-post-title')?.textContent || 'Title not found';

					}
				}
				// Check if event target is a button
				else if (e.target.tagName === 'BUTTON') {
					// Get search term from button's parent element
					searchValue = e.target.closest('.search-container')?.querySelector('input')?.value || '';
				}
				

				//  Make AJAX call to WordPress
					fetch(`${window.location.origin}/wordpress/wp-admin/admin-ajax.php`, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: `action=filter_carousel&search_term=${encodeURIComponent(searchValue)}`
					})
					.then(response => response.text())
					.then(html => {
						// Update the carousel with the new HTML
						const carousel = document.querySelector('.uc-swiper-list');
						if (carousel) {
							carousel.innerHTML = html;
						}
						console.log(carousel);

						// Portrait/landscape classes are now handled by drinks-plugin
						// Image orientation detection is automatic
						document.querySelector('.uc-slideshow').classList.toggle("hidden");
					})
					.catch(error => console.error('Error:', error));
				
			}



			// Apply Click Evt Lstnr to an Array of Figures
			function ucListenIteratively(anyFigureArray){  //  Repurposed to use AJAX & functions.php uc_filter_carousel

				for(const figure of anyFigureArray){
					const nodes = figure.childNodes;
					nodes.forEach(element => {
					//console.log(element);
							element.addEventListener("click", 
								(e) => {	
									

									ucAjaxCarousel(e);
							
									

								
								})	}
						);	};


							
			}

			//
			window.addEventListener("load", (event) => {
				// ... existing load event code ...
				
				/* const toggleButton = document.querySelector('.toggle-button');
				if (toggleButton) {
					toggleButton.addEventListener('click', () => {
						showHide('.uc-slideshow'); //Does styling inline
					});
				} */

				/* const allFigures = document.querySelectorAll(".portrait, .landscape");
				if(allFigures.length > 0){
					ucListenIteratively(allFigures);
				} */

				/* if(document.querySelector(".wp-block-search__button")){
					document.querySelector(".wp-block-search__button").addEventListener("click", (e) => {
						ucAjaxCarousel(e);
					});
				} */
				
			}); 
				











			/*    ucRemoveMenuItem filters current page off navbar...	*/
			function ucRemoveMenuItem(){
				
					var thisPage = document.getElementsByTagName("title")[0].innerText;
					//console.log(thisPage);
					var thesePages = document.getElementById("tierOne");
					thesePages = Array.from(thesePages.children);
					
					for (let i = 0; i < thesePages.length; i++){
						
						let currentPage =  thesePages[i].innerText;
						//console.log(currentPage);
				
						/* FIXED BELOW if(currentPage == thisPage){ */
						if(thisPage.includes(currentPage)){   
							
							thesePages[i].setAttribute("id", "hidden"); /*EFFECTIVE*/
							//console.log("IF Succeeded");
							/*console.log(thesePages[i]);*/
					
				
						}
					}
					//console.log(thisPage);
					//console.log(thesePages);
	
			}


			/*  Accepts .querySelector type DOM item, 
			*   Returns height in px of tallest child item, Recursion style
			*   Called By welcome-carousel.php pattern <script insert 
			*/
			function findTallestChild(node) {
			
			 
				let maxHeight = 0;
			  
				function traverse(node) {
				  const childHeight = node.offsetHeight; // or node.clientHeight
				  if (childHeight > maxHeight) maxHeight = childHeight;
			  
				  if (node.children.length === 0) return maxHeight; // leaf node, return height
				  /* node.children.forEach was not a function (NOT due queryselector, not sure why  */
				  Array.from(node.children).forEach((child) => traverse(child));
				}
			  
			
					traverse(node);
					return maxHeight;
	
				
			}  /* END findTallestChild */

	
	
	
			////    ////    ////    //// 
		/*    MISC. HELPER FUNCTIONS    */




	/*    SEARCH & FILTER FUNCTIONS    
        ////    ////    ////    ////    */
								
	




	function getRandomInt(max) {
		return Math.floor(Math.random() * max);
	  }

		   ////    ////    ////    ////
	/*    SEARCH & FILTER FUNCTIONS    */
 





	function ucStylePopOff(){
		const popoff = document.querySelector(".wp-block-media-text");
		const theFig = document.querySelector(".pop-off figure");
		//console.log(theFig);

		if (theFig) {
			if (theFig.classList.contains("landscape")) {
				// For landscape images, always use column layout
				popoff.style.flexDirection = "column";
			} else if (theFig.classList.contains("portrait")) {
				createOrientationHandler(
					// Portrait screen orientation callback
					() => {
						popoff.style.flexDirection = "column";
					},
					// Landscape screen orientation callback
					() => {
						popoff.style.flexDirection = "row";
					}
				);
			}
		}
	}
	document.addEventListener("DOMContentLoaded", (event) => {
		ucStylePopOff();
	});


	/**
	 * Creates an orientation handler that executes different callbacks for portrait/landscape
	 * @param {Function} portraitCallback - Function to execute in portrait mode
	 * @param {Function} landscapeCallback - Function to execute in landscape mode
	 * @returns {Function} Cleanup function to remove event listener
	 */
	function createOrientationHandler(portraitCallback, landscapeCallback) {
		// Function to handle orientation changes
		function handleOrientation(mediaQuery) {
			if (mediaQuery.matches) { // Landscape mode
				landscapeCallback();
			} else { // Portrait mode
				portraitCallback();
			}
		}

		// Create media query for landscape orientation
		const landscapeQuery = window.matchMedia("(orientation: landscape)");
		
		// Initial check
		handleOrientation(landscapeQuery);
		
		// Add listener for orientation changes
		landscapeQuery.addEventListener('change', handleOrientation);

		// Return cleanup function
		return () => landscapeQuery.removeEventListener('change', handleOrientation);
	}
    /* 
	*    Customize WP Header
	*/
    function ucCustomizeWPHeader() {
		const theLogo = `<div class="wp-block-site-logo uc-extra-logo"><a href="untouchedcocktails.com" class="custom-logo-link" rel="home"><img width="512" height="512" src="http://untouchedcocktails.com/wp-content/uploads/2024/12/logo512x.jpg" class="custom-logo" alt="Untouched Cocktails" decoding="async" fetchpriority="high" srcset="http://untouchedcocktails.com/wp-content/uploads/2024/12/logo512x.jpg 512w, http://untouchedcocktails.com/wp-content/uploads/2024/12/logo512x-300x300.jpg 300w, http://untouchedcocktails.com/wp-content/uploads/2024/12/logo512x-150x150.jpg 150w" sizes="(max-width: 512px) 100vw, 512px" data-attachment-id="2684" data-permalink="http://localhost/wordpress/logo512x/" data-orig-file="http://untouchedcocktails.com/wp-content/uploads/2024/12/logo512x.jpg" data-orig-size="512,512" data-comments-opened="1" data-image-meta="{&quot;aperture&quot;:&quot;0&quot;,&quot;credit&quot;:&quot;&quot;,&quot;camera&quot;:&quot;&quot;,&quot;caption&quot;:&quot;&quot;,&quot;created_timestamp&quot;:&quot;0&quot;,&quot;copyright&quot;:&quot;&quot;,&quot;focal_length&quot;:&quot;0&quot;,&quot;iso&quot;:&quot;0&quot;,&quot;shutter_speed&quot;:&quot;0&quot;,&quot;title&quot;:&quot;&quot;,&quot;orientation&quot;:&quot;1&quot;}" data-image-title="logo512x" data-image-description="" data-image-caption="" data-medium-file="http://untouchedcocktails.com/wp-content/uploads/2024/12/logo512x-300x300.jpg" data-large-file="http://untouchedcocktails.com/wp-content/uploads/2024/12/logo512x.jpg"></a></div>`;
		const mobileNav = document.querySelector(".wp-block-navigation");
	
		createOrientationHandler(
			// Portrait callback
			() => {
				if (!mobileNav.querySelector('.uc-extra-logo')) {
					mobileNav.insertAdjacentHTML('afterbegin', theLogo);
				}
			},
			// Landscape callback
			() => {
				const extraLogo = mobileNav.querySelector('.uc-extra-logo');
				if (extraLogo) {
					extraLogo.remove();
				}
			}
		);







		// Only target anchors to avoid removing nested markup or hrefs
		const links = Array.from(document.querySelectorAll("a.wp-block-navigation-item__content"));
		//console.log(links);

		// Array of seasonal cocktail names to match
		const seasonalNames = [
			"Summertime Cocktails",
			"Aurumnal Cocktails",
			"Springtime Cocktails",
			"Wintertime Cocktails"
		];

		// Find indexes of links containing any seasonal cocktail name
		const matchingIndexes = links.reduce((acc, link, index) => {
			if (seasonalNames.some(name => link.textContent.includes(name))) {
				acc.push(index);
			}
			return acc;
		}, []);

		// Replace only the visible label text; preserve anchor and attributes
		matchingIndexes.forEach(index => {
			const anchor = links[index];
			if (!anchor) return;
			const label = anchor.querySelector('.wp-block-navigation-item__label');
			if (label) {
				label.textContent = "Seasonal Cocktails";
			} else {
				anchor.textContent = "Seasonal Cocktails";
			}
		});

		console.log("Indexes of seasonal cocktail links:", matchingIndexes);

		
 	}









/*    ACTIONS : WHERE THE FUNCTIONS ARE CALLED     */
    ////    ////    ////    ////
       ////    ////    ////    ////
/*    ACTIONS : WHERE THE FUNCTIONS ARE CALLED     */
	
		/*		...WHEN PAGE LOADS... 		*/
		/* document.addEventListener("DOMContentLoaded", (event) =>{ */
		window.addEventListener("load", (event) =>{
			//console.log("Resources Loaded");
			


 			ucCustomizeWPHeader();
			//ucAddPaginationLeftArrowToCarousel();


			/*    On Contact Page, Handle Form?  */
			if(pageID.includes("contact")===true){
				//TRYING to prevent auto page refresh

				//Get form element
				var form=document.getElementById("contact-form");
				//console.log(form);

				function submitForm(event){
				
				//Preventing page refresh
			//	event.preventDefault();
				}

			
				//Calling a function during form submission.
				form.addEventListener('submit', submitForm);
			


			}
			

			if(pageID.includes("home")){ //Show/Hide carousel on home page Only 
				const carousel = document.querySelector('.welcome-carousel');
				
				// Function to handle orientation changes
				function handleOrientation(mediaQuery) {
					if (mediaQuery.matches) {
						// Landscape mode
						carousel.style.display = 'none';
					} else {
						// Portrait mode
						carousel.style.display = 'block';
					}
				}
			
				// Create media query for landscape orientation
				const landscapeQuery = window.matchMedia("(orientation: landscape)");
				
				// Initial check
				handleOrientation(landscapeQuery);
				
				// Add listener for orientation changes
				landscapeQuery.addEventListener('change', handleOrientation);
			}

			/* if(document.querySelector('.carousel')){ //great 
				resizeCarousel();
			} */

			
			
		});































		/*Remove 3 digits of anString 

		modifies original string
		*/

	function ucRemovePrefix(anString){ 
			/*ACCEPTS tags[i] in .pop-off, column constructing for loop above*/

			let ucStr = Array.from(anString);
			//console.log(ucStr);
			//console.log("Arr");

			/*quick fix*/ 
			ucStr.shift();
			ucStr.shift(); ucStr.shift();

			
			/*	join() excludes commas from array, unlike .toString()	*/
			anString = ucStr.join("");
			//console.log(anString);
			//console.log("final");



			return anString;
	}

