/**
 * Drinks Plugin - Frontend Lightbox Functionality
 */

// Lightbox state
let currentLightbox = null;
let isAnimating = false;
let currentDrinksContentLightbox = null;
let currentCarousel = null;

/**
 * Initialize lightbox functionality
 */
function initLightbox() {
    // console.log('Drinks Plugin: Frontend lightbox initialized');

    // Add click handlers to lightbox containers
    document.addEventListener('click', handleLightboxClick);
    
    // Add click handlers for cocktail-specific features
    document.addEventListener('click', handleCocktailPopOutClick);
    // Note: Carousel functionality is handled by the PHP inline script to avoid conflicts
    // document.addEventListener('click', handleCocktailCarouselClick);
    
    // Add keyboard support
    document.addEventListener('keydown', handleLightboxKeydown);
    
    // Add touch support for mobile
    document.addEventListener('touchstart', handleLightboxTouch, { passive: true });
    
    // Setup lightbox for existing images
    setupLightboxForImages();
    
    // Setup cocktail features for existing images
    setupCocktailFeaturesForImages();
    
    // Setup observer for dynamically added content
    setupLightboxObserver();
}

/**
 * Handle clicks on basic lightbox containers (data-wp-lightbox)
 */
function handleLightboxClick(event) {
    const container = event.target.closest('[data-wp-lightbox]');
    if (!container) return;

    // Check if this container has cocktail-specific functionality that should override basic lightbox
    if (container.hasAttribute('data-cocktail-pop-out') || container.hasAttribute('data-cocktail-carousel')) {
        // Let the cocktail-specific handlers deal with this
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    const img = container.querySelector('img');
    if (!img) return;

    openLightbox(img, container);
}

/**
 * Handle clicks on cocktail pop-out containers (drinks content)
 */
function handleCocktailPopOutClick(event) {
    const container = event.target.closest('[data-cocktail-pop-out="true"]');
    if (!container) return;

    const img = container.querySelector('img');
    if (!img) return;

    event.preventDefault();
    event.stopPropagation();
    // console.log('Drinks Plugin: Opening cocktail pop-out for image:', img.src);
    openCocktailPopOutLightbox(img, container);
}

/**
 * Handle clicks on cocktail carousel containers (Jetpack slideshow)
 */
function handleCocktailCarouselClick(event) {
    const container = event.target.closest('[data-cocktail-carousel="true"]');
    if (!container) return;

    const img = container.querySelector('img');
    if (!img) return;

    event.preventDefault();
    event.stopPropagation();
    // console.log('Drinks Plugin: Opening cocktail carousel slideshow for image:', img.src);
    openCocktailCarousel(img, container);
}

/**
 * Handle keyboard events
 */
function handleLightboxKeydown(event) {
    if (!currentLightbox) return;

    if (event.key === 'Escape') {
        closeLightbox();
    }
}

/**
 * Handle touch events for mobile
 */
function handleLightboxTouch(event) {
    if (!currentLightbox) return;

    // Close on tap outside image
    if (event.target === currentLightbox) {
        closeLightbox();
    }
}

/**
 * Open lightbox
 */
    function openLightbox(img, container) {
        if (isAnimating) return;

        // console.log('Drinks Plugin: Opening lightbox for image:', img.src);

    // Get image data
    const src = img.src;
    const alt = img.alt || '';
    let caption = '';

    // Get caption if it exists
    const figcaption = container.querySelector('figcaption');
    if (figcaption) {
        caption = figcaption.textContent;
    }

    // Create overlay
    const overlay = createLightboxOverlay(src, alt, caption);
    document.body.appendChild(overlay);

    // Show lightbox
    requestAnimationFrame(() => {
        overlay.classList.add('active');
        currentLightbox = overlay;
        document.body.style.overflow = 'hidden';
    });
}

/**
 * Open cocktail pop-out drinks content lightbox
 */
function openCocktailPopOutLightbox(img, container) {
    // Extract image ID from class name (wp-image-123) or data attributes
    let imageId = img.dataset.id || img.getAttribute('data-id') || '';
    if (!imageId) {
        // Try to extract from class name like "wp-image-123"
        const classMatch = img.className.match(/wp-image-(\d+)/);
        if (classMatch) {
            imageId = classMatch[1];
        }
    }
    
    const imageSrc = img.src;
    const imageAlt = img.alt || 'Drink Image';
    
    // Create drinks content pop-out overlay
    const overlay = createDrinksContentLightboxOverlay(imageSrc, imageAlt);
    document.body.appendChild(overlay);
    
    // Load drink content for lightbox
    loadDrinksForContentLightbox(overlay, imageId, img);
    
    // Show pop-out
    requestAnimationFrame(() => {
        overlay.classList.add('active');
        currentDrinksContentLightbox = overlay;
        document.body.style.overflow = 'hidden';
    });
}

/**
 * Open cocktail carousel (Jetpack slideshow)
 */
function openCocktailCarousel(img, container) {
    // Extract image ID from class name (wp-image-123) or data attributes
    let imageId = img.dataset.id || img.getAttribute('data-id') || '';
    if (!imageId) {
        // Try to extract from class name like "wp-image-123"
        const classMatch = img.className.match(/wp-image-(\d+)/);
        if (classMatch) {
            imageId = classMatch[1];
        }
    }
    
    const imageSrc = img.src;
    const imageAlt = img.alt || 'Drink Image';
    
    // Create carousel overlay (reverted to original Jetpack slideshow)
    const overlay = createCarouselOverlay(imageSrc, imageAlt);
    document.body.appendChild(overlay);
    
    // Load carousel images
    loadCarouselImages(overlay, imageId, img);
    
    // Show carousel
    requestAnimationFrame(() => {
        overlay.classList.add('active');
        currentCarousel = overlay;
        document.body.style.overflow = 'hidden';
    });
}

/**
 * Close lightbox
 */
    function closeLightbox() {
        if (!currentLightbox || isAnimating) return;

        // console.log('Drinks Plugin: Closing lightbox');

    isAnimating = true;
    currentLightbox.classList.remove('active');
    document.body.style.overflow = '';

    setTimeout(() => {
        if (currentLightbox && currentLightbox.parentNode) {
            currentLightbox.parentNode.removeChild(currentLightbox);
        }
        currentLightbox = null;
        isAnimating = false;
    }, 300);
}

/**
 * Close drinks content lightbox
 */
function closeDrinksContentLightbox() {
    // console.log('Drinks Plugin (closeDrinksContentLightbox): closeDrinksContentPopOut called');
    // console.log('Drinks Plugin (closeDrinksContentLightbox): Current pop-out lightbox:', currentDrinksContentLightbox);
    
    if (!currentDrinksContentLightbox) {
        // console.log('Drinks Plugin (closeDrinksContentLightbox): No current lightbox to close');
        return;
    }
    
    // console.log('Drinks Plugin (closeDrinksContentLightbox): Removing active class and closing pop-out');
    currentDrinksContentLightbox.classList.remove('active');
    document.body.style.overflow = '';
    
    setTimeout(() => {
        if (currentDrinksContentLightbox && currentDrinksContentLightbox.parentNode) {
            // console.log('Drinks Plugin (closeDrinksContentLightbox): Removing pop-out from DOM');
            currentDrinksContentLightbox.parentNode.removeChild(currentDrinksContentLightbox);
        }
        currentDrinksContentLightbox = null;
        // console.log('Drinks Plugin (closeDrinksContentLightbox): Pop-out closed successfully');
    }, 300);
}

/**
 * Create lightbox overlay
 */
function createLightboxOverlay(src, alt, caption) {
    const overlay = document.createElement('div');
    overlay.className = 'drinks-lightbox-overlay';
    overlay.innerHTML = `
        <div class="drinks-lightbox-content">
            <button class="drinks-lightbox-close" aria-label="Close lightbox">&times;</button>
            <img class="drinks-lightbox-image" src="${src}" alt="${alt}" />
            ${caption ? `<div class="drinks-lightbox-caption">${caption}</div>` : ''}
        </div>
    `;

    // Add close button handler
    const closeButton = overlay.querySelector('.drinks-lightbox-close');
    closeButton.addEventListener('click', closeLightbox);

    // Add click outside to close
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeLightbox();
        }
    });

    return overlay;
}

/**
 * Create drinks content lightbox overlay
 */
function createDrinksContentLightboxOverlay(initialImageSrc, initialImageAlt) {
    const overlay = document.createElement('div');
    // Keep existing class for styling, add specific pop-out class for clarity
    // Prefer pop-out/lightbox class, keep jetpack class only for legacy styles if present elsewhere
    overlay.className = 'drinks-lightbox-overlay drinks-popout-overlay';
    overlay.innerHTML = `
        <div class="drinks-lightbox-content drinks-popout-content">
            <div class="drinks-lightbox-header drinks-popout-header">
                <button type="button" class="drinks-lightbox-close" aria-label="Close pop-out">&times;</button>
            </div>
            <div class="drinks-lightbox-body drinks-popout-body">
                <div class="drinks-content-popout" id="drinks-content-popout">
                    <div class="drink-content-slide active" id="initial-drink-content">
                        <!-- Initial drink content will be loaded here -->
                        <div class="drink-content-loading">
                            <div class="drink-content-loading-spinner"></div>
                            Loading drink content...
                        </div>
                        
                        <!-- Navigation controls -->
                        <div class="drinks-content-navigation">
                            <button class="drinks-content-prev" aria-label="Previous drink">&larr;</button>
                            <button class="drinks-content-next" aria-label="Next drink">&rarr;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add event listeners
    const closeButton = overlay.querySelector('.drinks-lightbox-close');
    if (closeButton) {
        // console.log('Drinks Plugin (setupLightboxForImages): Close button found, adding click listener');
        closeButton.addEventListener('click', (e) => {
            // console.log('Drinks Plugin (setupLightboxForImages): Close button clicked');
            e.preventDefault();
            e.stopPropagation();
            closeDrinksContentLightbox();
        });
    } else {
        // console.error('Drinks Plugin (setupLightboxForImages): Close button not found in overlay');
    }
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeDrinksContentLightbox();
        }
    });
    
    return overlay;
}

/**
 * Setup lightbox for existing images
 */
    function setupLightboxForImages() {
        const images = document.querySelectorAll('[data-wp-lightbox] img');
        // console.log('Drinks Plugin: Found', images.length, 'lightbox images');
    
    images.forEach(img => {
        // Images are already wrapped in containers with data-wp-lightbox
        // Just ensure they have proper styling
        const container = img.closest('[data-wp-lightbox]');
        if (container) {
            container.style.cursor = 'pointer';
        }
    });
}

/**
 * Setup cocktail features for existing images
 */
function setupCocktailFeaturesForImages() {
    const cocktailPopOutContainers = document.querySelectorAll('[data-cocktail-pop-out="true"]');
    cocktailPopOutContainers.forEach(container => {
        container.style.cursor = 'pointer';
        // console.log('Drinks Plugin (initLightbox): Set cursor pointer on cocktail pop-out container');
    });

    const cocktailCarouselContainers = document.querySelectorAll('[data-cocktail-carousel="true"]');
    cocktailCarouselContainers.forEach(container => {
        container.style.cursor = 'pointer';
        // console.log('Drinks Plugin (initLightbox): Set cursor pointer on cocktail carousel container');
    });
}

/**
 * Setup lightbox observer for dynamically added content
 */
function setupLightboxObserver() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && node.querySelector) {
                        const lightboxImages = node.querySelectorAll('[data-wp-lightbox] img');
                        lightboxImages.forEach(img => {
                            const container = img.closest('[data-wp-lightbox]');
                            if (container) {
                                container.style.cursor = 'pointer';
                            }
                        });
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

/**
 * Load drinks for content lightbox
 */
function loadDrinksForContentLightbox(overlay, excludeImageId, img) {
    const contentContainer = overlay.querySelector('#drinks-content-popout');
    if (!contentContainer) {
        console.error('Drinks Plugin: No drinks content container found');
        return;
    }
    
    // console.log('Drinks Plugin (loadDrinksContent): Starting to load drinks content for pop-out...');
    // console.log('Drinks Plugin (loadDrinksContent): Exclude ID:', excludeImageId);
    
    // Show loading state
    const loadingElement = contentContainer.querySelector('.drink-content-loading');
    if (loadingElement) {
        loadingElement.innerHTML = '<div class="drink-content-loading-spinner"></div>Loading drink content...';
    }
    
    // Make AJAX call to get drink content
    const formData = new FormData();
    formData.append('action', 'get_drink_content');
    formData.append('image_id', excludeImageId);
    
    // Use localized WordPress AJAX URL
    const ajaxUrl = window.drinksPluginAjax ? window.drinksPluginAjax.ajaxurl : '/wp-admin/admin-ajax.php';
    // console.log('Drinks Plugin (loadDrinksContent): Using AJAX URL for pop-out:', ajaxUrl);
    
    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // console.log('Drinks Plugin (loadDrinksContent): AJAX response status:', response.status);
        return response.json();
    })
    .then(data => {
        // console.log('Drinks Plugin (loadDrinksContent): AJAX response data (pop-out):', data);
        
        // Remove loading state
        const loadingElement = contentContainer.querySelector('.drink-content-loading');
        if (loadingElement) {
            loadingElement.remove();
        }
        
        if (data.success && data.data) {
            // console.log('Drinks Plugin (loadDrinksContent): Found drink content, displaying in pop-out');
            contentContainer.innerHTML = data.data;
            
            // Apply dynamic styling based on drink category
            ucStyleLightBoxesByPageID(img);
            
            // Add navigation event listeners
            addDrinksContentNavigation(overlay);
        } else {
            // console.log('Drinks Plugin (loadDrinksContent): No drink content found in pop-out response');
            contentContainer.innerHTML = '<div class="drink-content-error">No drink content available</div>';
        }
        
        // console.log('Drinks Plugin (loadDrinksContent): Drink content loaded successfully (pop-out)');
    })
    .catch(error => {
        console.error('Drinks Plugin: Error loading drinks content:', error);
        const loadingElement = contentContainer.querySelector('.drink-content-loading');
        if (loadingElement) {
            loadingElement.innerHTML = '<div class="drink-content-error">Error loading drink content</div>';
        }
    });
}

/**
 * Add navigation event listeners for drinks content
 */
function addDrinksContentNavigation(overlay) {
    const prevButton = overlay.querySelector('.drinks-content-prev');
    const nextButton = overlay.querySelector('.drinks-content-next');
    
    if (prevButton) {
        prevButton.addEventListener('click', () => {
            // console.log('Drinks Plugin (addDrinksContentNavigation): Previous button clicked');
            // TODO: Implement previous drink navigation
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            // console.log('Drinks Plugin (addDrinksContentNavigation): Next button clicked');
            // TODO: Implement next drink navigation
        });
    }
}

/**
 * Test function for drinks content lightbox
 */
function testDrinksContent() {
    // console.log('Drinks Plugin (testDrinksContent): Testing drinks content lightbox system...');
    // console.log('Drinks Plugin (testDrinksContent): Global object available:', !!window.drinksPluginDrinksContent);
    // console.log('Drinks Plugin (testDrinksContent): Current drinks content lightbox:', currentDrinksContentLightbox);
    
    const containers = document.querySelectorAll('[data-cocktail-pop-out="true"]');
    // console.log('Drinks Plugin (testDrinksContent): Found', containers.length, 'cocktail-pop-out containers');
    
    if (containers.length > 0) {
        // console.log('Drinks Plugin (testDrinksContent): First container:', containers[0]);
        // console.log('Drinks Plugin (testDrinksContent): First container classes:', containers[0].className);
    }
    
    return {
        containers: containers.length,
        lightbox: !!currentDrinksContentLightbox,
        global: !!window.drinksPluginPopOut
    };
}

/**
 * Create carousel overlay (Jetpack slideshow)
 */
function createCarouselOverlay(initialImageSrc, initialImageAlt) {
    const overlay = document.createElement('div');
    overlay.className = 'jetpack-carousel-lightbox-overlay';
    overlay.innerHTML = `
        <div class="jetpack-carousel-lightbox-content">
            <div class="jetpack-carousel-lightbox-header">
                <button type="button" class="jetpack-carousel-lightbox-close" aria-label="Close carousel">&times;</button>
            </div>
            <div class="jetpack-carousel-lightbox-body">
                <div class="wp-block-jetpack-slideshow aligncenter" data-autoplay="false" data-delay="3" data-effect="slide">
                    <div class="wp-block-jetpack-slideshow_container swiper-container">
                        <ul class="wp-block-jetpack-slideshow_swiper-wrapper swiper-wrapper" id="jetpack-carousel-slides">
                            <li class="wp-block-jetpack-slideshow_slide swiper-slide">
                                <figure>
                                    <img src="${initialImageSrc}" alt="${initialImageAlt}" class="wp-block-jetpack-slideshow_image" />
                                    <figcaption>${initialImageAlt}</figcaption>
                                </figure>
                            </li>
                        </ul>
                        
                        <!-- Slideshow controls -->
                        <a class="wp-block-jetpack-slideshow_button-prev swiper-button-prev swiper-button-white" role="button" tabindex="0" aria-label="Previous slide"></a>
                        <a class="wp-block-jetpack-slideshow_button-next swiper-button-next swiper-button-white" role="button" tabindex="0" aria-label="Next slide"></a>
                        <a aria-label="Pause Slideshow" class="wp-block-jetpack-slideshow_button-pause" role="button"></a>
                        <div class="wp-block-jetpack-slideshow_pagination swiper-pagination swiper-pagination-white swiper-pagination-custom"></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add event listeners
    const closeButton = overlay.querySelector('.jetpack-carousel-lightbox-close');
    if (closeButton) {
        closeButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeCarousel();
        });
    }
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeCarousel();
        }
    });
    
    return overlay;
}

/**
 * Close carousel
 */
function closeCarousel() {
    // console.log('Drinks Plugin (closeCarousel): closeCarousel called');
    // console.log('Drinks Plugin (closeCarousel): Current carousel:', currentCarousel);
    
    if (!currentCarousel) {
        // console.log('Drinks Plugin (closeCarousel): No current carousel to close');
        return;
    }
    
    // console.log('Drinks Plugin (closeCarousel): Removing active class and closing carousel');
    currentCarousel.classList.remove('active');
    document.body.style.overflow = '';
    
    setTimeout(() => {
        if (currentCarousel && currentCarousel.parentNode) {
            // console.log('Drinks Plugin (closeCarousel): Removing carousel from DOM');
            currentCarousel.parentNode.removeChild(currentCarousel);
        }
        currentCarousel = null;
        // console.log('Drinks Plugin (closeCarousel): Carousel closed successfully');
    }, 300);
}

/**
 * Load carousel images (Jetpack slideshow)
 */
function loadCarouselImages(overlay, excludeImageId, img) {
    const slidesContainer = overlay.querySelector('#jetpack-carousel-slides');
    if (!slidesContainer) {
        console.error('Drinks Plugin: No slides container found');
        return;
    }
    
    // console.log('Drinks Plugin (loadCarouselImages): Starting to load carousel images...');
    // console.log('Drinks Plugin (loadCarouselImages): Exclude ID:', excludeImageId);
    
    // Show loading state
    slidesContainer.innerHTML += '<li class="wp-block-jetpack-slideshow_slide swiper-slide"><div class="jetpack-carousel-loading"><div class="jetpack-carousel-loading-spinner"></div>Loading carousel images...</div></li>';
    
    // Make AJAX call to get random drinks for carousel
    const formData = new FormData();
    formData.append('action', 'filter_carousel');
    formData.append('search_term', '');
    formData.append('exclude_id', excludeImageId);
    formData.append('show_content', '1'); // Show captions/content
    
    // Use localized WordPress AJAX URL
    const ajaxUrl = window.drinksPluginAjax ? window.drinksPluginAjax.ajaxurl : '/wp-admin/admin-ajax.php';
    // console.log('Drinks Plugin (loadCarouselImages): Using AJAX URL:', ajaxUrl);
    
    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // console.log('Drinks Plugin (loadCarouselImages): AJAX response status:', response.status);
        return response.text();
    })
    .then(html => {
        // console.log('Drinks Plugin (loadCarouselImages): AJAX response HTML length:', html.length);
        // console.log('Drinks Plugin (loadCarouselImages): AJAX response HTML preview:', html.substring(0, 200) + '...');
        
        // Remove loading slide
        const loadingSlide = slidesContainer.querySelector('.jetpack-carousel-loading');
        if (loadingSlide) {
            loadingSlide.remove();
        }
        
        // Parse the HTML and add slides
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const newSlides = tempDiv.querySelectorAll('li');
        
        // console.log('Drinks Plugin (loadCarouselImages): Found', newSlides.length, 'new slides in AJAX response');
        
        newSlides.forEach((slide, index) => {
            // console.log('Drinks Plugin (loadCarouselImages): Adding slide', index, ':', slide.outerHTML.substring(0, 100) + '...');
            slidesContainer.appendChild(slide.cloneNode(true));
        });
        
        // console.log('Drinks Plugin (loadCarouselImages): Total slides in container after adding:', slidesContainer.children.length);
        
        // Apply dynamic styling to carousel slides based on drink categories
        ucStyleLightBoxesByPageID(img);
        
        // Initialize Jetpack slideshow functionality
        initializeJetpackSlideshow(overlay);
        
        // console.log('Drinks Plugin (loadCarouselImages): Jetpack carousel loaded with', slidesContainer.children.length, 'slides');
    })
    .catch(error => {
        console.error('Drinks Plugin: Error loading carousel images:', error);
        const loadingSlide = slidesContainer.querySelector('.jetpack-carousel-loading');
        if (loadingSlide) {
            loadingSlide.innerHTML = '<div class="jetpack-carousel-loading">Error loading carousel images</div>';
        }
    });
}

/**
 * Initialize Jetpack slideshow functionality
 */
function initializeJetpackSlideshow(overlay) {
    // console.log('Drinks Plugin (initializeJetpackSlideshow): Initializing Jetpack slideshow...');
    
    // Check if Jetpack slideshow scripts are loaded
    if (typeof window.jetpackSlideshowSettings !== 'undefined') {
        // console.log('Drinks Plugin (initializeJetpackSlideshow): Jetpack slideshow settings found, using native initialization');
        // Jetpack slideshow is available, initialize it
        const slideshow = overlay.querySelector('.wp-block-jetpack-slideshow');
        if (slideshow) {
            // console.log('Drinks Plugin (initializeJetpackSlideshow): Found slideshow element, initializing...');
            // Trigger Jetpack slideshow initialization
            if (window.jetpackSlideshowSettings && window.jetpackSlideshowSettings.init) {
                window.jetpackSlideshowSettings.init(slideshow);
                // console.log('Drinks Plugin (initializeJetpackSlideshow): Jetpack slideshow initialized successfully');
            } else {
                // console.log('Drinks Plugin (initializeJetpackSlideshow): Jetpack init function not found');
            }
        } else {
            // console.log('Drinks Plugin (initializeJetpackSlideshow): No slideshow element found');
        }
    } else {
        // console.log('Drinks Plugin (initializeJetpackSlideshow): Jetpack slideshow not available, using fallback');
    }
}

/**
 * Enhanced styling functions for dynamic category-based styling
 */

// Enhanced styling function with category detection
function styleImagesByPageID(variableID, targetContainer) {
	
	if(!targetContainer){
		targetContainer = '.entry-content';
	}

	// Get all images within target container
	const imageContainer = document.querySelector(targetContainer);
	if (!imageContainer) {    //  If no target, no action. 
		return;
	}

	const images = imageContainer.querySelectorAll('img');

	images.forEach(img => {
		// Extract category code from image title/alt
		const categoryCode = extractCategoryFromImage(img);
		let currentVariableID = variableID;
		
		// Override variableID with category-based styling if category detected
		if (categoryCode) {
			currentVariableID = mapCategoryCodeToVariable(categoryCode);
		}

		if(currentVariableID.includes("springtime")){
			currentVariableID = "summertime";
		}  //  (Else currentVariableID = currentVariableID as passed)

		// Compose variable names
		const borderVar = `var(--${currentVariableID}-border)`;
		const fontColorVar = `var(--${currentVariableID}-font-color)`;
		const shadowVar = `var(--${currentVariableID}-shadow)`;

		// 1. Apply border variable
		img.style.border = borderVar;

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

// Function to extract category code from image title/alt/filename
function extractCategoryFromImage(img) {
	console.log('Drinks Plugin (extractCategoryFromImage): Analyzing image:', img);
	console.log('Drinks Plugin (extractCategoryFromImage): Image src:', img.src);
	console.log('Drinks Plugin (extractCategoryFromImage): Image title:', img.title);
	console.log('Drinks Plugin (extractCategoryFromImage): Image alt:', img.alt);
	
	// Check title first
	const title = img.title || img.alt || '';
	console.log('Drinks Plugin (extractCategoryFromImage): Checking title/alt:', title);
	let categoryMatch = title.match(/_([A-Z]{2})/);
	if (categoryMatch) {
		console.log('Drinks Plugin (extractCategoryFromImage): Found category in title:', categoryMatch[1]);
		return categoryMatch[1]; // Returns "AU", "RO", etc.
	}
	
	// If not found in title/alt, check the filename (src)
	const src = img.src || '';
	console.log('Drinks Plugin (extractCategoryFromImage): Checking src:', src);
	categoryMatch = src.match(/_([A-Z]{2})/);
	if (categoryMatch) {
		console.log('Drinks Plugin (extractCategoryFromImage): Found category in src:', categoryMatch[1]);
		return categoryMatch[1]; // Returns "AU", "RO", etc.
	}
	
	console.log('Drinks Plugin (extractCategoryFromImage): No category found');
	return null;
}

// Function to map category codes to variable names
function mapCategoryCodeToVariable(categoryCode) {
	const categoryMap = {
		'AU': 'autumnal',
		'RO': 'romantic', 
		'EV': 'everyday',
		'SU': 'summertime',
		'SP': 'summertime', // springtime maps to summertime
		'FP': 'fireplace',
		'SO': 'special-occasion',
		'WI': 'winter'
	};
	
	return categoryMap[categoryCode] || 'std';
}

// Function to style lightboxes based on clicked image
function ucStyleLightBoxesByPageID(clickedImage) {
	console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Starting lightbox styling for image:', clickedImage.src);
	
	// Check if clicked image activates a Pop Out Lightbox
	if (clickedImage.closest('[data-cocktail-pop-out="true"]')) {
		console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Detected pop-out lightbox');
		
		// Wait a bit for the pop-out content to load, then find the image in the pop-out
		setTimeout(() => {
			const popoutImage = document.querySelector('.drinks-content-popout img');
			if (popoutImage) {
				console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found image in pop-out:', popoutImage.src);
				const categoryCode = extractCategoryFromImage(popoutImage);
				console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Extracted category code:', categoryCode);
				
				if (categoryCode) {
					const categoryVariable = mapCategoryCodeToVariable(categoryCode);
					console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Mapped to variable:', categoryVariable);
					styleImagesByPageID(categoryVariable, '.drinks-content-popout');
					
					// Also style the h1 element and list items
					const popoutContainer = document.querySelector('.drinks-content-popout');
					if (popoutContainer) {
						// Style the h1 element
						const h1Element = popoutContainer.querySelector('h1');
						if (h1Element) {
							h1Element.style.color = `var(--${categoryVariable}-font-color)`;
							h1Element.style.textShadow = `var(--${categoryVariable}-shadow)`;
							console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied color and shadow to h1:', h1Element.textContent);
						} else {
							console.log('Drinks Plugin (ucStyleLightBoxesByPageID): No h1 element found in pop-out');
						}
						
						// Style the list items with accent color
						const listItems = popoutContainer.querySelectorAll('li');
						console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found', listItems.length, 'list items to style');
						
						listItems.forEach((li, index) => {
							li.style.color = `var(--${categoryVariable}-accent-color)`;
							li.style.textShadow = `var(--${categoryVariable}-shadow)`;
							console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied accent color and shadow var(--' + categoryVariable + '-accent-color) to li', index + 1);
							
							// Style em elements within the li to be black
							const emElements = li.querySelectorAll('em');
							emElements.forEach((em, emIndex) => {
								em.style.color = "black";
								em.style.fontWeight = "bold";
								em.style.fontStyle = "normal";
								em.style.marginRight = "0.25em";
								em.style.textShadow = "none"; // Remove shadow from black text for better readability
								console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Styled em element', emIndex + 1, 'to black in li', index + 1);
							});
						});
					}
				}
			} else {
				console.log('Drinks Plugin (ucStyleLightBoxesByPageID): No image found in pop-out, using clicked image');
				const categoryCode = extractCategoryFromImage(clickedImage);
				console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Extracted category code from clicked image:', categoryCode);
				
				if (categoryCode) {
					const categoryVariable = mapCategoryCodeToVariable(categoryCode);
					console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Mapped to variable:', categoryVariable);
					styleImagesByPageID(categoryVariable, '.drinks-content-popout');
					
					// Also style the h1 element and list items
					const popoutContainer = document.querySelector('.drinks-content-popout');
					if (popoutContainer) {
						// Style the h1 element
						const h1Element = popoutContainer.querySelector('h1');
						if (h1Element) {
							h1Element.style.color = `var(--${categoryVariable}-font-color)`;
							h1Element.style.textShadow = `var(--${categoryVariable}-shadow)`;
							console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied color and shadow to h1:', h1Element.textContent);
						} else {
							console.log('Drinks Plugin (ucStyleLightBoxesByPageID): No h1 element found in pop-out');
						}
						
						// Style the list items with accent color
						const listItems = popoutContainer.querySelectorAll('li');
						console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found', listItems.length, 'list items to style');
						
						listItems.forEach((li, index) => {
							li.style.color = `var(--${categoryVariable}-accent-color)`;
							li.style.textShadow = `var(--${categoryVariable}-shadow)`;
							console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied accent color and shadow var(--' + categoryVariable + '-accent-color) to li', index + 1);
							
							// Style em elements within the li to be black
							const emElements = li.querySelectorAll('em');
							emElements.forEach((em, emIndex) => {
								em.style.color = "black";
								em.style.fontWeight = "bold";
								em.style.fontStyle = "normal";
								em.style.marginRight = "0.25em";
								em.style.textShadow = "none"; // Remove shadow from black text for better readability
								console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Styled em element', emIndex + 1, 'to black in li', index + 1);
							});
						});
					}
				}
			}
		}, 100); // Small delay to ensure content is loaded
	} 
	// Check if clicked image activates a Carousel Lightbox
	else if (clickedImage.closest('[data-cocktail-carousel="true"]')) {
		console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Detected carousel lightbox');
		
		// For each slide in the carousel
		const carouselSlides = document.querySelectorAll('.wp-block-jetpack-slideshow_slide');
		console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found', carouselSlides.length, 'carousel slides');
		
		carouselSlides.forEach((slide, slideIndex) => {
			const slideImage = slide.querySelector('img');
			if (slideImage) {
				console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Processing slide', slideIndex + 1, ':', slideImage.src);
				const categoryCode = extractCategoryFromImage(slideImage);
				console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Extracted category code for slide', slideIndex + 1, ':', categoryCode);
				
				if (categoryCode) {
					const categoryVariable = mapCategoryCodeToVariable(categoryCode);
					console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Mapped to variable for slide', slideIndex + 1, ':', categoryVariable);
					styleImagesByPageID(categoryVariable, slide);
				}
			}
		});
	}
	
	console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Lightbox styling complete');
}

// Make functions globally available
window.drinksPluginLightbox = {
    init: initLightbox,
    open: openLightbox,
    close: closeLightbox,
    setup: setupLightboxForImages
};

window.drinksPluginPopOut = {
    init: initLightbox,
    open: openCocktailPopOutLightbox,
    close: closeDrinksContentLightbox,
    test: testDrinksContent
};

// Add global test function for debugging
window.testDrinksContent = testDrinksContent;

// Make styling functions globally available
window.drinksPluginStyling = {
    styleImagesByPageID: styleImagesByPageID,
    extractCategoryFromImage: extractCategoryFromImage,
    mapCategoryCodeToVariable: mapCategoryCodeToVariable,
    ucStyleLightBoxesByPageID: ucStyleLightBoxesByPageID
};

/**
 * Image Orientation Detection and Classification
 * Automatically assigns .portrait or .landscape classes to Image blocks
 * based on their natural dimensions
 */
function ucPortraitLandscape(imageElement) {
     //console.log('🔍 ucPortraitLandscape: Analyzing dimensions for aspect ratio management:', imageElement?.src || 'unknown');
    
    if (!imageElement || imageElement.tagName !== 'IMG') {
        console.warn('⚠️ ucPortraitLandscape: Invalid image element:', imageElement);
        return;
    }

    // console.log('🔍 ucPortraitLandscape: Image element found:', {
    //     src: imageElement.src,
    //     alt: imageElement.alt,
    //     complete: imageElement.complete,
    //     naturalWidth: imageElement.naturalWidth,
    //     naturalHeight: imageElement.naturalHeight
    // });

    // Find the closest figure or container element
    const container = imageElement.closest('figure') || imageElement.closest('.wp-block-image') || imageElement.parentElement;
    
    if (!container) {
         console.warn('⚠️ ucPortraitLandscape: No container found for image:', imageElement.src);
        return;
    }

    // console.log('🔍 ucPortraitLandscape: Container found:', {
    //     tagName: container.tagName,
    //     className: container.className,
    //     id: container.id
    // });

    // Skip if already processed or if it's a special container
    if (container.classList.contains('pop-off') || 
        container.classList.contains('wp-block-gallery') ||
        container.classList.contains('portrait') ||
        container.classList.contains('landscape')) {
        // console.log('⏭️ ucPortraitLandscape: Skipping container - already processed or special type:', container.className);
        return;
    }

    function processImageDimensions() {
        // console.log('📐 ucPortraitLandscape: Analyzing longest dimension for:', imageElement.src);
        
        if (!imageElement.naturalWidth || !imageElement.naturalHeight) {
            // console.warn('⚠️ ucPortraitLandscape: No natural dimensions available:', {
            //     naturalWidth: imageElement.naturalWidth,
            //     naturalHeight: imageElement.naturalHeight
            // });
            return;
        }

        // console.log('📐 ucPortraitLandscape: Natural dimensions:', {
        //     width: imageElement.naturalWidth,
        //     height: imageElement.naturalHeight,
        //     ratio: (imageElement.naturalHeight / imageElement.naturalWidth).toFixed(2)
        // });

        // Remove existing dimension classes
        const hadPortrait = container.classList.contains('portrait');
        const hadLandscape = container.classList.contains('landscape');
        container.classList.remove('portrait', 'landscape');
        
        if (hadPortrait || hadLandscape) {
            // console.log('🔄 ucPortraitLandscape: Removed existing dimension classes:', {
            //     hadPortrait,
            //     hadLandscape
            // });
        }

        // Determine longest dimension for aspect ratio management
        if (imageElement.naturalHeight > imageElement.naturalWidth) {
            container.classList.add('portrait');
            // console.log('🖼️ ucPortraitLandscape: ✅ Height is longest - Added PORTRAIT class for aspect ratio management');
            // console.log('🖼️ ucPortraitLandscape: Updated container classes:', container.className);
        } else if (imageElement.naturalHeight < imageElement.naturalWidth) {
            container.classList.add('landscape');
            // console.log('🖼️ ucPortraitLandscape: ✅ Width is longest - Added LANDSCAPE class for aspect ratio management');
            // console.log('🖼️ ucPortraitLandscape: Updated container classes:', container.className);
        } else {
            // console.log('🖼️ ucPortraitLandscape: Image is square, no dimension class needed');
        }
    }

    // Process immediately if image is already loaded
    if (imageElement.complete && imageElement.naturalWidth && imageElement.naturalHeight) {
        // console.log('⚡ ucPortraitLandscape: Image already loaded, analyzing dimensions immediately');
        processImageDimensions();
    } else {
        // console.log('⏳ ucPortraitLandscape: Image not loaded yet, waiting for load event');
        imageElement.addEventListener('load', () => {
            // console.log('🔄 ucPortraitLandscape: Image load event fired, analyzing dimensions now');
            processImageDimensions();
        }, { once: true });
    }
}

/**
 * Initialize ucPortraitLandscape dimension analysis for all Image blocks
 */
function initImageOrientationDetection() {
   // console.log('🚀 Drinks Plugin: Initializing ucPortraitLandscape dimension analysis');

    // Process existing images
    const images = document.querySelectorAll('.wp-block-image img, figure img');
   // console.log('🔍 initImageOrientationDetection: Found', images.length, 'existing images to analyze');
    
    images.forEach((img, index) => {
   //     console.log(`🔍 initImageOrientationDetection: Analyzing image ${index + 1}/${images.length}:`, img.src);
        ucPortraitLandscape(img);
    });

    // Set up observer for dynamically added images
    const observer = new MutationObserver((mutations) => {
        let newImagesFound = 0;
        
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is an image
                        if (node.tagName === 'IMG') {
                            // console.log('🆕 initImageOrientationDetection: New image node detected:', node.src);
                            ucPortraitLandscape(node);
                            newImagesFound++;
                        }
                        // Check for images within the added node
                        const images = node.querySelectorAll ? node.querySelectorAll('img') : [];
                        if (images.length > 0) {
                            // console.log('🆕 initImageOrientationDetection: Found', images.length, 'images in new node');
                            images.forEach((img, index) => {
                                // console.log(`🆕 initImageOrientationDetection: Analyzing new image ${index + 1}/${images.length}:`, img.src);
                                ucPortraitLandscape(img);
                                newImagesFound++;
                            });
                        }
                    }
                });
            }
        });
        
        if (newImagesFound > 0) {
            // console.log(`🆕 initImageOrientationDetection: Analyzed ${newImagesFound} new images via observer`);
        }
    });

    // Observe the entire document for added nodes
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
  //  console.log('👁️ initImageOrientationDetection: MutationObserver set up to watch for new images');
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLightbox);
} else {
    initLightbox();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initImageOrientationDetection);
} else {
    initImageOrientationDetection();
}

// Also initialize on window load to catch any late-loading images
window.addEventListener('load', () => {
     //console.log('🌅 Drinks Plugin: Window load event fired, re-analyzing all images');
    // Re-analyze all images in case some loaded after DOMContentLoaded
    const images = document.querySelectorAll('.wp-block-image img, figure img');
    // console.log('🔍 Window load: Found', images.length, 'images to re-analyze');
    images.forEach((img, index) => {
        // console.log(`🔍 Window load: Re-analyzing image ${index + 1}/${images.length}:`, img.src);
        ucPortraitLandscape(img);
    });
});
