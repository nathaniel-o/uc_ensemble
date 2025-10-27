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

		// Set background color - home uses std vars, others use page-specific
		let bgColorVar = pageID === 'home' ? 'var(--std-bg-color)' : 'var(--' + pageID + '-bg-color)';
		anPage.style.backgroundColor = bgColorVar;
		
		// Apply background image for everyday only
		if(pageID.includes('everyday')){
			anPage.style.backgroundImage = 'var(--' + pageID + '-bg-img)';
		} 
		// For other category pages, do pattern based on SVG from functions.php
		else if(pageID.includes('springtime') || pageID.includes('summertime') || pageID.includes('winter') || pageID.includes('autumnal') || pageID.includes('fireplace') || pageID.includes('special-occasion')){
			// Create repeating pattern for springtime, summertime, winter, fireplace	
			ucCreateRepeatingPattern(pageID);
		}
		else if(pageID.includes('romantic')){
			ucCreateFullCoverageSvg(pageID);
		}

		
				
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



			//    ucRemoveMenuItem filters current page off navbar...	
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




	function getRandomInt(max) {
		return Math.floor(Math.random() * max);
	  }
 


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
  

	//Remove 3 digits of anString ; modifies original string
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


	////    ////    ////    ////    ////    ////    ////    ////
	/*    SEARCH & FILTER FUNCTIONS    */
	////    ////    ////    ////    ////    ////    ////    ////

	// Modal Search Functionality (welcome/home/about pages)
	document.addEventListener('DOMContentLoaded', searchListen );
	
	function searchListen(){
		
		// Find all search forms
		const searchForms = document.querySelectorAll('.wp-block-search__button-inside form, form[role="search"]');
		
		searchForms.forEach(form => {
			form.addEventListener('submit', ucSearch);
		});
	}
	
	//modify search behavior - default: open filtered drinks carousel
	function ucSearch(e){
		e.preventDefault(); // Always prevent default
		
		console.log('ucSearch triggered');
		
		const form = e.target; // Get the form from the event
		const searchQuery = form.querySelector('input[type="search"]').value.trim();
		
		console.log('Search query:', searchQuery);
		
		if (!searchQuery) {
			debugger;
			return; // Empty search, do nothing
		}
		
		// Open filtered drinks carousel using drinks plugin
		openFilteredDrinksCarousel(searchQuery);
	}
	
	// MODE 1: uc_image_carousel filtered, not matching, not supp'd random. 	
	// Open drinks carousel filtered by search term (reuses drinks-plugin functions)
	function openFilteredDrinksCarousel(searchTerm) {
		console.log('Opening filtered drinks carousel for:', searchTerm);
		console.log('window.drinksPluginCarousel available?', !!window.drinksPluginCarousel);
		
		// Check if drinks plugin carousel functions are available
		if (!window.drinksPluginCarousel) {
			console.error('Drinks plugin carousel not available, falling back to search page');
			// Use absolute path to ensure it goes to WordPress search
			const wpBasePath = window.location.pathname.split('/')[1];
			const searchUrl = window.location.origin + '/' + wpBasePath + '/?s=' + encodeURIComponent(searchTerm);
			console.log('Redirecting to:', searchUrl);
			window.location.href = searchUrl;
			return;
		}
		
		// Use pre-existing carousel overlay (added by drinks plugin in PHP)
		const overlay = document.getElementById('drinks-carousel-overlay');
		if (!overlay) {
			console.error('Carousel overlay not found in DOM');
			return;
		}
		
		// Load filtered drinks using unified function
		// matchTerm = empty, filterTerm = searchTerm
		window.drinksPluginCarousel.loadImages(overlay, '', searchTerm, null);
		
		// Show overlay
		requestAnimationFrame(() => {
			overlay.style.opacity = '1';
			overlay.style.pointerEvents = 'auto';
			overlay.classList.add('active');
			document.body.style.overflow = 'hidden';
		});
	}
 









/* 
			function ucAjaxCarousel(e){
				console.log("ucAjaxCarousel");
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


							
			} */


								/* window.addEventListener("load", (event) =>{

			//    On Contact Page, Handle Form?  
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
			
			
		}); */



/* 		function ucStylePopOff(){
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
		}); */
	
