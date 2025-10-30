/**
 * Drinks Plugin - Frontend Lightbox Functionality
 */

// Lightbox state
let currentLightbox = null;
let isAnimating = false;
let currentDrinksContentLightbox = null;
let currentCarousel = null;
let currentCarouselFilterTerm = ''; // Track current filter term for "See More" button

/**
 * Initialize lightbox functionality
 */
function initLightbox() {
    ////console.log('Drinks Plugin: initLightbox');
    
    // Add click handlers to lightbox containers
    document.addEventListener('click', handleLightboxClick);
    
    // Add click handlers for cocktail-specific features
    document.addEventListener('click', handleCocktailPopOutClick);
    // Add carousel click handler (moved from PHP inline script)
    document.addEventListener('click', handleCocktailCarouselClick);
    // Add click handler for drink metadata filter links
    document.addEventListener('click', handleDrinkFilterLinkClick);
    
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
    
    // Setup pre-existing carousel overlay
    setupCarouselOverlay();
}



/**
 * Handle clicks on basic lightbox containers (data-wp-lightbox)
 */
function handleLightboxClick(event) {
    ////console.log('Drinks Plugin: handleLightboxClick');
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
    // ////console.log('Drinks Plugin: Opening cocktail pop-out for image:', img.src);
    openCocktailPopOutLightbox(img, container);
}

/**
 * Handle clicks on cocktail carousel containers (Jetpack slideshow)
 * Matches PHP handleJetpackCarouselImageClick detection logic
 */
function handleCocktailCarouselClick(event) {
    //console.log('Drinks Plugin: handleCocktailCarouselClick');
    // Look for both attribute and class (matches PHP version)
    const container = event.target.closest('[data-cocktail-carousel="true"], .cocktail-carousel, [data-carousel-enabled]');
    if (!container) return;
    
    // Check if this is actually a carousel container (not pop-out)
    if (container.getAttribute('data-cocktail-pop-out') === 'true') {
        // This is a pop-out, not a carousel - let pop-out handler deal with it
        return;
    }

    // Find the image - either the target itself or within the container
    let img = null;
    if (event.target.tagName === 'IMG') {
        img = event.target;
    } else {
        img = container.querySelector('img');
    }
    
    if (!img) return;

    event.preventDefault();
    event.stopPropagation();
    // ////console.log('Drinks Plugin (frontend.js): Opening cocktail carousel slideshow for image:', img.src);
    // ////console.log('Drinks Plugin (frontend.js): Container classes:', container.className);
    // ////console.log('Drinks Plugin (frontend.js): Container attributes:', container.getAttribute('data-cocktail-carousel'));
    openCocktailCarousel(img, container);
}

/**
 * Handle clicks on drink metadata filter links
 * Opens carousel filtered by the clicked metadata term
 */
function handleDrinkFilterLinkClick(event) {
    const link = event.target.closest('.drink-filter-link');
    if (!link) return;
    
    event.preventDefault();
    event.stopPropagation();
    
    // Get the filter term from data attribute
    const filterTerm = link.getAttribute('data-filter');
    
    if (!filterTerm) {
        console.error('Drinks Plugin: No filter term found on link');
        return;
    }
    
    // Close any existing pop-out lightbox before opening carousel
    if (currentDrinksContentLightbox) {
        closeDrinksContentLightbox();
    }
    
    // Use pre-existing carousel overlay
    const overlay = document.getElementById('drinks-carousel-overlay');
    if (!overlay) {
        console.error('Drinks Plugin: Carousel overlay not found in DOM');
        return;
    }
    
    // Load carousel with filter term (empty matchTerm, filterTerm = clicked value)
    loadCarouselImages(overlay, '', filterTerm, null);
    
    // Show carousel
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        overlay.style.pointerEvents = 'auto';
        overlay.classList.add('active');
        currentCarousel = overlay;
        document.body.style.overflow = 'hidden';
    });
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

        // ////console.log('Drinks Plugin: Opening lightbox for image:', img.src);

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
 * Modified for two-level pop-out system: pop-out -> carousel
 */
function openCocktailPopOutLightbox(img, container) {
    //console.log('Drinks Plugin: openCocktailPopOutLightbox');
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
    // Pass container so click handlers can be set up after content loads
    loadDrinksForContentLightbox(overlay, imageId, img, container);
    
    // Show pop-out
    requestAnimationFrame(() => {
        overlay.classList.add('active');
        currentDrinksContentLightbox = overlay;
        document.body.style.overflow = 'hidden';
    });
}

/**
 * Setup pop-out to carousel click functionality
 * Only image and h1 trigger random carousel; links trigger filtered carousel
 */
function setupPopOutToCarouselClick(overlay, img, container) {
    // Find the image and h1 in the pop-out
    const popoutImage = overlay.querySelector('.drinks-content-popout img');
    const popoutH1 = overlay.querySelector('.drinks-content-popout h1');
    
    // Add click handler to image
    if (popoutImage) {
        popoutImage.style.cursor = 'pointer';
        popoutImage.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Close the pop-out and open carousel with random drinks
            closeDrinksContentLightbox();
            
            // Small delay to ensure pop-out closes before carousel opens
            setTimeout(() => {
                // Use local carousel function for pop-out context (random drinks)
                openCocktailCarouselFromPopOut(img, container);
            }, 100);
        });
    }
    
    // Add click handler to h1
    if (popoutH1) {
        popoutH1.style.cursor = 'pointer';
        popoutH1.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Close the pop-out and open carousel with random drinks
            closeDrinksContentLightbox();
            
            // Small delay to ensure pop-out closes before carousel opens
            setTimeout(() => {
                // Use local carousel function for pop-out context (random drinks)
                openCocktailCarouselFromPopOut(img, container);
            }, 100);
        });
    }
}

/**
 * Open cocktail carousel from pop-out context (Jetpack slideshow with all random drinks)
 */
function openCocktailCarouselFromPopOut(img, container) {
    // Close any existing pop-out lightbox when opening carousel
    if (currentDrinksContentLightbox) {
        closeDrinksContentLightbox();
    }
    
    // Use pre-existing carousel overlay
    const overlay = document.getElementById('drinks-carousel-overlay');
    if (!overlay) {
        console.error('Drinks Plugin: Carousel overlay not found in DOM');
        return;
    }
    
    // Load carousel images: Random mode (both empty)
    loadCarouselImages(overlay, '', '', null);
    
    // Show carousel
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        overlay.style.pointerEvents = 'auto';
        overlay.classList.add('active');
        currentCarousel = overlay;
        document.body.style.overflow = 'hidden';
    });
}

/**
 * Open cocktail carousel (Jetpack slideshow)
 */
function openCocktailCarousel(img, container) {
    // Close any existing pop-out lightbox when opening carousel
    if (currentDrinksContentLightbox) {
        closeDrinksContentLightbox();
    }
    
    // Use pre-existing carousel overlay
    const overlay = document.getElementById('drinks-carousel-overlay');
    if (!overlay) {
        console.error('Drinks Plugin: Carousel overlay not found in DOM');
        return;
    }
    
    // Load carousel images: Clicked drink first (auto-extracts figcaption from container)
    loadCarouselImages(overlay, '', '', container);
    
    // Show carousel
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        overlay.style.pointerEvents = 'auto';
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

        // ////console.log('Drinks Plugin: Closing lightbox');

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
    // ////console.log('Drinks Plugin (closeDrinksContentLightbox): closeDrinksContentPopOut called');
    // ////console.log('Drinks Plugin (closeDrinksContentLightbox): Current pop-out lightbox:', currentDrinksContentLightbox);
    
    if (!currentDrinksContentLightbox) {
        // ////console.log('Drinks Plugin (closeDrinksContentLightbox): No current lightbox to close');
        return;
    }
    
    // ////console.log('Drinks Plugin (closeDrinksContentLightbox): Removing active class and closing pop-out');
    currentDrinksContentLightbox.classList.remove('active');
    document.body.style.overflow = '';
    
    setTimeout(() => {
        if (currentDrinksContentLightbox && currentDrinksContentLightbox.parentNode) {
            // ////console.log('Drinks Plugin (closeDrinksContentLightbox): Removing pop-out from DOM');
            currentDrinksContentLightbox.parentNode.removeChild(currentDrinksContentLightbox);
        }
        currentDrinksContentLightbox = null;
        // ////console.log('Drinks Plugin (closeDrinksContentLightbox): Pop-out closed successfully');
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
        // ////console.log('Drinks Plugin (setupLightboxForImages): Close button found, adding click listener');
        closeButton.addEventListener('click', (e) => {
            // ////console.log('Drinks Plugin (setupLightboxForImages): Close button clicked');
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
        // ////console.log('Drinks Plugin: Found', images.length, 'lightbox images');
    
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
        // ////console.log('Drinks Plugin (initLightbox): Set cursor pointer on cocktail pop-out container');
    });

    const cocktailCarouselContainers = document.querySelectorAll('[data-cocktail-carousel="true"]');
    cocktailCarouselContainers.forEach(container => {
        container.style.cursor = 'pointer';
        // ////console.log('Drinks Plugin (initLightbox): Set cursor pointer on cocktail carousel container');
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
function loadDrinksForContentLightbox(overlay, excludeImageId, img, container) {
    const contentContainer = overlay.querySelector('#drinks-content-popout');
    if (!contentContainer) {
        console.error('Drinks Plugin: No drinks content container found');
        return;
    }
    
    // ////console.log('Drinks Plugin (loadDrinksContent): Starting to load drinks content for pop-out...');
    // ////console.log('Drinks Plugin (loadDrinksContent): Exclude ID:', excludeImageId);
    
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
    // ////console.log('Drinks Plugin (loadDrinksContent): Using AJAX URL for pop-out:', ajaxUrl);
    
    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // ////console.log('Drinks Plugin (loadDrinksContent): AJAX response status:', response.status);
        return response.json();
    })
    .then(data => {
        // ////console.log('Drinks Plugin (loadDrinksContent): AJAX response data (pop-out):', data);
        
        // Remove loading state
        const loadingElement = contentContainer.querySelector('.drink-content-loading');
        if (loadingElement) {
            loadingElement.remove();
        }
        
        if (data.success && data.data) {
            // ////console.log('Drinks Plugin (loadDrinksContent): Found drink content, displaying in pop-out');
            contentContainer.innerHTML = data.data;
            
            // Apply dynamic styling based on drink category
            ucStyleLightBoxesByPageID(img);
            
            // Add navigation event listeners
            addDrinksContentNavigation(overlay);
            
            // Add click handler to pop-out content to open carousel
            // This must happen AFTER content is loaded and img/h1 elements exist
            setupPopOutToCarouselClick(overlay, img, container);
        } else {
            // ////console.log('Drinks Plugin (loadDrinksContent): No drink content found in pop-out response');
            contentContainer.innerHTML = '<div class="drink-content-error">No drink content available</div>';
        }
        
        // ////console.log('Drinks Plugin (loadDrinksContent): Drink content loaded successfully (pop-out)');
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
            // ////console.log('Drinks Plugin (addDrinksContentNavigation): Previous button clicked');
            // TODO: Implement previous drink navigation
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            // ////console.log('Drinks Plugin (addDrinksContentNavigation): Next button clicked');
            // TODO: Implement next drink navigation
        });
    }
}

/**
 * Test function for drinks content lightbox
 */
function testDrinksContent() {
    // ////console.log('Drinks Plugin (testDrinksContent): Testing drinks content lightbox system...');
    // ////console.log('Drinks Plugin (testDrinksContent): Global object available:', !!window.drinksPluginDrinksContent);
    // ////console.log('Drinks Plugin (testDrinksContent): Current drinks content lightbox:', currentDrinksContentLightbox);
    
    const containers = document.querySelectorAll('[data-cocktail-pop-out="true"]');
    // ////console.log('Drinks Plugin (testDrinksContent): Found', containers.length, 'cocktail-pop-out containers');
    
    if (containers.length > 0) {
        // ////console.log('Drinks Plugin (testDrinksContent): First container:', containers[0]);
        // ////console.log('Drinks Plugin (testDrinksContent): First container classes:', containers[0].className);
    }
    
    return {
        containers: containers.length,
        lightbox: !!currentDrinksContentLightbox,
        global: !!window.drinksPluginPopOut
    };
}

/**
 * Setup event listeners for pre-existing carousel overlay
 */
function setupCarouselOverlay() {
    const overlay = document.getElementById('drinks-carousel-overlay');
    if (!overlay) {
        console.error('Drinks Plugin: Carousel overlay not found in DOM');
        return;
    }
    
    // Add event listeners
    const closeButton = overlay.querySelector('.jetpack-carousel-lightbox-close');
    if (closeButton) {
        closeButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeCarousel();
        });
    }
    
    // Add "See More" button handler
    const seeMoreButton = overlay.querySelector('.drinks-carousel-see-more');
    if (seeMoreButton) {
        seeMoreButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            handleSeeMoreClick();
        });
    }
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeCarousel();
        }
    });
}

/**
 * Close carousel
 */
function closeCarousel() {
    // ////console.log('Drinks Plugin (closeCarousel): closeCarousel called');
    // ////console.log('Drinks Plugin (closeCarousel): Current carousel:', currentCarousel);
    
    // If currentCarousel not tracked, find active overlay in DOM
    let carouselToClose = currentCarousel;
    if (!carouselToClose) {
        carouselToClose = document.querySelector('.jetpack-carousel-lightbox-overlay.active');
        // ////console.log('Drinks Plugin (closeCarousel): Found active overlay in DOM:', !!carouselToClose);
    }
    
    if (!carouselToClose) {
        // ////console.log('Drinks Plugin (closeCarousel): No current carousel to close');
        return;
    }
    
    // ////console.log('Drinks Plugin (closeCarousel): Removing active class and closing carousel');
    carouselToClose.classList.remove('active');
    document.body.style.overflow = '';
    
    setTimeout(() => {
        // Hide the overlay instead of removing it
        if (carouselToClose) {
            carouselToClose.style.opacity = '0';
            carouselToClose.style.pointerEvents = 'none';
            // Clear slides for next use
            const slidesContainer = carouselToClose.querySelector('#jetpack-carousel-slides');
            if (slidesContainer) {
                slidesContainer.innerHTML = '';
            }
        }
        currentCarousel = null;
        // ////console.log('Drinks Plugin (closeCarousel): Carousel closed successfully');
    }, 300);
}

/**
 * Handle "See More" button click
 * Redirects to search page with current filter term
 */
function handleSeeMoreClick() {
    // Close the carousel
    closeCarousel();
    
    // Build search URL with current filter term (always root-level search)
    const searchUrl = window.location.origin + '/?s=' + encodeURIComponent(currentCarouselFilterTerm || '');
    
    console.log('Drinks Plugin: Redirecting to search page:', searchUrl);
    
    // Redirect to search page
    window.location.href = searchUrl;
}

/**
 * Load carousel images (Jetpack slideshow)
 * Mirrors PHP uc_image_carousel($match_term, $filter_term, $options)
 * @param {HTMLElement} overlay - The carousel overlay element
 * @param {string} matchTerm - Text to match for first slide (empty = no priority)
 * @param {string} filterTerm - Search term to filter drinks (empty = no filter)
 * @param {HTMLElement} container - Optional container for auto-extracting figcaption
 * 
 * Behaviors:
 * - Both empty → Random carousel
 * - matchTerm only → Matched drink first, then random
 * - filterTerm only → Filtered drinks, no priority
 * - Both → Matched drink first, then filtered drinks
 */
function loadCarouselImages(overlay, matchTerm = '', filterTerm = '', container = null) {
    const slidesContainer = overlay.querySelector('#jetpack-carousel-slides');
    if (!slidesContainer) {
        console.error('Drinks Plugin: No slides container found');
        return;
    }
    
    // Auto-extract figcaption if container provided and no explicit matchTerm
    if (!matchTerm && container) {
        const figcaption = container.querySelector('figcaption');
        matchTerm = figcaption ? figcaption.textContent.trim() : '';
    }
    
    // Store filter term for "See More" button
    currentCarouselFilterTerm = filterTerm;
    
    // Debug statement matching PHP format
    const matchDisplay = matchTerm || 'empty';
    const filterDisplay = filterTerm || 'empty';
    
    // Determine and log which MODE will be triggered
    if (filterTerm) {
        console.log(`Carousel MODE 1: Filter, Parameters: matchTerm="${matchDisplay}", filterTerm="${filterDisplay}"`);
    } else if (matchTerm) {
        console.log(`Carousel MODE 2: Match, Parameters: matchTerm="${matchDisplay}", filterTerm="${filterDisplay}"`);
    } else {
        console.log(`Carousel MODE 3: Random, Parameters: matchTerm="${matchDisplay}", filterTerm="${filterDisplay}"`);
    }
    
    
    // Show loading state
    slidesContainer.innerHTML = '<li class="wp-block-jetpack-slideshow_slide swiper-slide"><div class="jetpack-carousel-loading"><div class="jetpack-carousel-loading-spinner"></div>Loading carousel images...</div></li>';
    
    
    // Make AJAX call to get drinks for carousel
    const formData = new FormData();
    formData.append('action', 'drinks_filter_carousel');
    formData.append('search_term', filterTerm);
    formData.append('figcaption_text', matchTerm);
    
    // ////console.log('Drinks Plugin (frontend.js): AJAX params - search_term:', filterTerm, 'figcaption_text:', matchTerm);
    
    // Use localized WordPress AJAX URL
    const ajaxUrl = window.drinksPluginAjax ? window.drinksPluginAjax.ajaxurl : '/wp-admin/admin-ajax.php';
    // ////console.log('Drinks Plugin (loadCarouselImages): Using AJAX URL:', ajaxUrl);
    
    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // ////console.log('Drinks Plugin (loadCarouselImages): AJAX response status:', response.status);
        return response.text();
    })
    .then(html => {

        // Replace the loading slide with the new slides
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Extract and display the search results header
        const searchHeader = tempDiv.querySelector('.drinks-search-results-header');
        if (searchHeader) {
            //console.log('Drinks Plugin: Found search header:', searchHeader.textContent);
            const carouselHeader = overlay.querySelector('.jetpack-carousel-lightbox-header');
            if (carouselHeader) {
                const existingHeader = carouselHeader.querySelector('.drinks-search-results-header');
                if (existingHeader) existingHeader.remove();
                carouselHeader.insertBefore(searchHeader.cloneNode(true), carouselHeader.firstChild);
            }
        }
        
        const newSlides = tempDiv.querySelectorAll('li');
        
        // Error handling: No results found - show 404 content inside carousel
        if (newSlides.length === 0) {
            // Show 404 content inside the carousel (no redirect)
            slidesContainer.innerHTML = `
                <li class="wp-block-jetpack-slideshow_slide swiper-slide drinks-404-slide">
                    <div class="drinks-404-content">
                        <h1 class="wp-block-heading has-text-align-center">404</h1>
                        <p class="has-text-align-center">Content Missing</p>
                        ${filterTerm ? '<p class="has-text-align-center">No results found for: <strong>' + filterTerm + '</strong></p>' : ''}
                    </div>
                </li>
            `;
            
            // Update search header to show 0 results
            const carouselHeader = overlay.querySelector('.jetpack-carousel-lightbox-header');
            if (carouselHeader) {
                const existingHeader = carouselHeader.querySelector('.drinks-search-results-header');
                if (existingHeader) {
                    existingHeader.textContent = 'Search Results: 0';
                } else {
                    const newHeader = document.createElement('h5');
                    newHeader.className = 'drinks-search-results-header';
                    newHeader.textContent = 'Search Results: 0';
                    carouselHeader.insertBefore(newHeader, carouselHeader.firstChild);
                }
            }
            
            return;
        }
        
        // Get Swiper instance before clearing
        const slideshowContainer = overlay.querySelector('.wp-block-jetpack-slideshow_container');
        const swiper = slideshowContainer?.swiper;
        
        // If Swiper exists, use its API to remove all slides first
        if (swiper) {
            swiper.removeAllSlides();
        } else {
            // Fallback: Clear the container directly
            slidesContainer.innerHTML = '';
        }
        
        // Add all new slides to the DOM
        newSlides.forEach((slide, index) => {
            slidesContainer.appendChild(slide.cloneNode(true));
        });
        
        // Apply dynamic styling to carousel slides based on drink categories
        ucStyleLightBoxesByPageID(container?.querySelector('img') || document.querySelector('img'));
        
        // Initialize Jetpack slideshow functionality
        initializeJetpackSlideshow(overlay);
        
        // ////console.log('Drinks Plugin (loadCarouselImages): Jetpack carousel loaded with', slidesContainer.children.length, 'slides');
    })
    .catch(error => {
        console.error('Drinks Plugin: Error loading carousel images:', error);
        
        // If filterTerm was used (search mode), redirect to default search page
        if (filterTerm) {
            const loadingSlide = slidesContainer.querySelector('.jetpack-carousel-loading');
            if (loadingSlide) {
                loadingSlide.innerHTML = '<div class="jetpack-carousel-loading">Error loading drinks. Redirecting to search page...</div>';
            }
            setTimeout(() => {
                closeCarousel();
                window.location.href = '/?s=' + encodeURIComponent(filterTerm);
            }, 1500);
        } else {
            // No filterTerm - show error message only
            const loadingSlide = slidesContainer.querySelector('.jetpack-carousel-loading');
            if (loadingSlide) {
                loadingSlide.innerHTML = '<div class="jetpack-carousel-loading">Error loading carousel images</div>';
            }
        }
    });
}

/**
 * Initialize/Update Jetpack slideshow after loading new slides
 * Jetpack initializes the carousel automatically at page load
 * This function just updates Swiper when new slides are loaded
 */
function initializeJetpackSlideshow(overlay) {    
    const slideshowContainer = overlay.querySelector('.wp-block-jetpack-slideshow_container');
    
    // Jetpack initializes Swiper at page load
    // Just update it with the new slides
    if (slideshowContainer && slideshowContainer.swiper) {
        const swiper = slideshowContainer.swiper;
        
        // Important: Destroy loop before updating to prevent DOM manipulation issues
        if (swiper.params.loop) {
            swiper.loopDestroy();
        }
        
        // Update Swiper to recognize new slides
        swiper.update();
        
        // Enable loop mode for multiple slides (only if we have more than 1)
        if (swiper.slides.length > 1) {
            swiper.params.loop = true;
            swiper.loopCreate();
            swiper.update(); // Update again after creating loop
        }
        
        // Force Swiper to recalculate dimensions and rendering
        swiper.updateSize();
        swiper.updateSlides();
        swiper.updateProgress();
        swiper.updateSlidesClasses();
        
        // Configure custom pagination to show correct slide numbers (excluding loop clones)
        if (swiper.params.pagination && swiper.params.pagination.el) {
            // Helper function to update pagination display
            const updatePaginationDisplay = () => {
                const paginationEl = overlay.querySelector('.swiper-pagination-custom');
                if (!paginationEl) return;
                
                // Count only non-duplicate slides
                const nonDuplicateSlides = Array.from(swiper.slides).filter(
                    slide => !slide.classList.contains('swiper-slide-duplicate')
                );
                const realSlidesCount = nonDuplicateSlides.length;
                
                // Use realIndex which is 0-based and excludes duplicates
                const realCurrent = (swiper.realIndex % realSlidesCount) + 1;
                
                paginationEl.textContent = realCurrent + '/' + realSlidesCount;
            };
            
            // Set up custom pagination formatter (for Swiper's built-in pagination)
            // The 'current' parameter passed includes duplicates, so we use realIndex instead
            swiper.params.pagination.renderCustom = function(swiperInstance, current, total) {
                // Count only non-duplicate slides
                const nonDuplicateSlides = Array.from(swiperInstance.slides).filter(
                    slide => !slide.classList.contains('swiper-slide-duplicate')
                );
                const realSlidesCount = nonDuplicateSlides.length;
                
                // Use realIndex which is 0-based and excludes duplicates
                const realCurrent = (swiperInstance.realIndex % realSlidesCount) + 1;
                
                return realCurrent + '/' + realSlidesCount;
            };
            
            // Force pagination update on initialization
            swiper.params.pagination.type = 'custom';
            
            // Listen for slide changes to update pagination
            swiper.on('slideChange', updatePaginationDisplay);
            
            // Initialize pagination immediately
            if (swiper.pagination) {
                swiper.pagination.render();
                swiper.pagination.update();
            }
        }
        
        // Go to first real slide (not the loop duplicate)
        // For loop mode, slide index 1 is usually the first real slide
        const startIndex = swiper.params.loop ? 1 : 0;
        swiper.slideTo(startIndex, 0); // Go to slide with no animation
        
        // Final update to ensure everything is rendered
        requestAnimationFrame(() => {
            swiper.update();
            
            // Update pagination using the custom formatter
            if (swiper.pagination) {
                swiper.pagination.render();
                swiper.pagination.update();
            }
        });
    } else {
        // Swiper not initialized yet (e.g., search page) - manually initialize it
        console.log('Drinks Plugin: Swiper not found, initializing manually');
        
        if (!slideshowContainer) {
            console.error('Drinks Plugin: Slideshow container not found');
            return;
        }
        
        // Check if Swiper library is available
        if (typeof Swiper === 'undefined') {
            console.error('Drinks Plugin: Swiper library not loaded');
            return;
        }
        
        // Count slides to determine if we need loop mode
        const slidesWrapper = slideshowContainer.querySelector('.swiper-wrapper');
        const slidesCount = slidesWrapper ? slidesWrapper.children.length : 0;
        
        // Initialize Swiper with Jetpack-like configuration
        const swiper = new Swiper(slideshowContainer, {
            effect: 'slide',
            grabCursor: true,
            loop: slidesCount > 1, // Only enable loop if we have multiple slides
            navigation: {
                nextEl: slideshowContainer.querySelector('.swiper-button-next'),
                prevEl: slideshowContainer.querySelector('.swiper-button-prev'),
            },
            pagination: {
                el: slideshowContainer.querySelector('.swiper-pagination'),
                type: 'custom',
                clickable: true,
                renderCustom: function(swiperInstance, current, total) {
                    // Count only non-duplicate slides
                    const nonDuplicateSlides = Array.from(swiperInstance.slides).filter(
                        slide => !slide.classList.contains('swiper-slide-duplicate')
                    );
                    const realSlidesCount = nonDuplicateSlides.length;
                    const realCurrent = (swiperInstance.realIndex % realSlidesCount) + 1;
                    return realCurrent + '/' + realSlidesCount;
                }
            },
            keyboard: {
                enabled: true,
            },
            a11y: {
                enabled: true,
            },
        });
        
        // Update pagination on slide change
        swiper.on('slideChange', () => {
            if (swiper.pagination) {
                swiper.pagination.render();
                swiper.pagination.update();
            }
        });
        
        console.log('Drinks Plugin: Swiper initialized with', slidesCount, 'slides');
    }
}

/**
 * Add basic slideshow functionality if Jetpack is not available
 */
/* function addBasicSlideshowFunctionality(overlay) {
    //console.log('Drinks Plugin: Setting up fallback slideshow functionality...A FallBack');
    
    const slidesContainer = overlay.querySelector('.wp-block-jetpack-slideshow_swiper-wrapper');
    const slides = slidesContainer.querySelectorAll('.wp-block-jetpack-slideshow_slide');
    const prevButton = overlay.querySelector('.wp-block-jetpack-slideshow_button-prev');
    const nextButton = overlay.querySelector('.wp-block-jetpack-slideshow_button-next');
    const pagination = overlay.querySelector('.wp-block-jetpack-slideshow_pagination');
    
    // ////console.log('Drinks Plugin: Fallback - Found', slides.length, 'slides');
    
    // Start at slide 1 (index 1) because index 0 is the clone of the last slide
    // This ensures the clicked image (which should be the first real slide) is shown
    let currentSlide = 1;
    
    // Show first slide
    showSlide(currentSlide);
    
    // Previous button
    if (prevButton) {
        prevButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        });
    }
    
    // Next button
    if (nextButton) {
        nextButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        });
    }
    
    // Pagination
    if (pagination && slides.length > 1) {
        // Only create bullets for the real slides (not the clones)
        // For 5 real slides + 2 clones = 7 total, we want 5 bullets
        const realSlidesCount = slides.length - 2; // Subtract 2 clones
        for (let i = 0; i < realSlidesCount; i++) {
            const bullet = document.createElement('button');
            bullet.className = 'swiper-pagination-bullet';
            bullet.setAttribute('aria-label', `Go to slide ${i + 1}`);
            bullet.addEventListener('click', () => {
                // Map bullet index to actual slide index (accounting for clone at start)
                currentSlide = i + 1; // +1 because slide 0 is a clone
                showSlide(currentSlide);
            });
            pagination.appendChild(bullet);
        }
    }
    
    function showSlide(index) {
        // ////console.log('Drinks Plugin: Fallback - Showing slide:', index);
        slides.forEach((slide, i) => {
            if (i === index) {
                slide.style.display = 'flex';
                slide.classList.add('active');
                // ////console.log('Drinks Plugin: Added active class to slide', i);
            } else {
                slide.style.display = 'none';
                slide.classList.remove('active');
            }
        });
        
        // Update pagination if it exists
        if (pagination) {
            const bullets = pagination.querySelectorAll('.swiper-pagination-bullet');
            bullets.forEach((bullet, i) => {
                // Map slide index to bullet index (slide index - 1 because slide 0 is a clone)
                bullet.classList.toggle('swiper-pagination-bullet-active', i === (index - 1));
            });
        }
    }
    
    // ////console.log('Drinks Plugin: Fallback slideshow functionality set up successfully');
} */

/**
 * Enhanced styling functions for dynamic category-based styling
 */

// Enhanced styling function with category detection
function styleImagesByPageID(variableID, targetContainer) {
	
	if(!targetContainer){
		targetContainer = '.entry-content';
	}

	// Get all images within target container
	// Handle both string selectors AND HTML elements
	let imageContainer;
	if (typeof targetContainer === 'string') {
		imageContainer = document.querySelector(targetContainer);
	} else if (targetContainer instanceof HTMLElement) {
		imageContainer = targetContainer; // Already an element
	} else {
		return; // Invalid input
	}
	
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
	////console.log('Drinks Plugin (extractCategoryFromImage): Analyzing image:', img);
	////console.log('Drinks Plugin (extractCategoryFromImage): Image src:', img.src);
	////console.log('Drinks Plugin (extractCategoryFromImage): Image title:', img.title);
	////console.log('Drinks Plugin (extractCategoryFromImage): Image alt:', img.alt);
	
	// Check title first
	const title = img.title || img.alt || '';
	////console.log('Drinks Plugin (extractCategoryFromImage): Checking title/alt:', title);
	let categoryMatch = title.match(/_([A-Z]{2})/);
	if (categoryMatch) {
		////console.log('Drinks Plugin (extractCategoryFromImage): Found category in title:', categoryMatch[1]);
		return categoryMatch[1]; // Returns "AU", "RO", etc.
	}
	
	// If not found in title/alt, check the filename (src)
	const src = img.src || '';
	////console.log('Drinks Plugin (extractCategoryFromImage): Checking src:', src);
	categoryMatch = src.match(/_([A-Z]{2})/);
	if (categoryMatch) {
		////console.log('Drinks Plugin (extractCategoryFromImage): Found category in src:', categoryMatch[1]);
		return categoryMatch[1]; // Returns "AU", "RO", etc.
	}
	
	////console.log('Drinks Plugin (extractCategoryFromImage): No category found');
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
	////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Starting lightbox styling for image:', clickedImage.src);
	
	// Check if clicked image activates a Pop Out Lightbox
	if (clickedImage.closest('[data-cocktail-pop-out="true"]')) {
		////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Detected pop-out lightbox');
		
		// Wait a bit for the pop-out content to load, then find the image in the pop-out
		setTimeout(() => {
			const popoutImage = document.querySelector('.drinks-content-popout img');
			if (popoutImage) {
				////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found image in pop-out:', popoutImage.src);
				const categoryCode = extractCategoryFromImage(popoutImage);
				////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Extracted category code:', categoryCode);
				
				if (categoryCode) {
					const categoryVariable = mapCategoryCodeToVariable(categoryCode);
					////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Mapped to variable:', categoryVariable);
					styleImagesByPageID(categoryVariable, '.drinks-content-popout');
					
					// Also style the h1 element and list items
					const popoutContainer = document.querySelector('.drinks-content-popout');
					if (popoutContainer) {
						// Style the h1 element
						const h1Element = popoutContainer.querySelector('h1');
						if (h1Element) {
							h1Element.style.color = '#241547';
							h1Element.style.textShadow = 'none';
							////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied color and shadow to h1:', h1Element.textContent);
						} else {
							////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): No h1 element found in pop-out');
						}
						
						// Style the list items with accent color
						const listItems = popoutContainer.querySelectorAll('li');
						////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found', listItems.length, 'list items to style');
						
						listItems.forEach((li, index) => {
							li.style.color = `var(--${categoryVariable}-accent-color)`;
							li.style.textShadow = `var(--${categoryVariable}-shadow)`;
							////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied accent color and shadow var(--' + categoryVariable + '-accent-color) to li', index + 1);
							
							// Style em elements within the li to be black
							const emElements = li.querySelectorAll('em');
							emElements.forEach((em, emIndex) => {
								em.style.color = "black";
								em.style.fontWeight = "bold";
								em.style.fontStyle = "normal";
								em.style.marginRight = "0.25em";
								em.style.textShadow = "none"; // Remove shadow from black text for better readability
								////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Styled em element', emIndex + 1, 'to black in li', index + 1);
							});
						});
					}
				}
			} else {
				////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): No image found in pop-out, using clicked image');
				const categoryCode = extractCategoryFromImage(clickedImage);
				////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Extracted category code from clicked image:', categoryCode);
				
				if (categoryCode) {
					const categoryVariable = mapCategoryCodeToVariable(categoryCode);
					////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Mapped to variable:', categoryVariable);
					styleImagesByPageID(categoryVariable, '.drinks-content-popout');
					
					// Also style the h1 element and list items
					const popoutContainer = document.querySelector('.drinks-content-popout');
					if (popoutContainer) {
						// Style the h1 element
						const h1Element = popoutContainer.querySelector('h1');
						if (h1Element) {
							h1Element.style.color = '#241547';
							h1Element.style.textShadow = 'none';
							////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied color and shadow to h1:', h1Element.textContent);
						} else {
							////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): No h1 element found in pop-out');
						}
						
						// Style the list items with accent color
						const listItems = popoutContainer.querySelectorAll('li');
						////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found', listItems.length, 'list items to style');
						
						listItems.forEach((li, index) => {
							li.style.color = `var(--${categoryVariable}-accent-color)`;
							li.style.textShadow = `var(--${categoryVariable}-shadow)`;
							////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Applied accent color and shadow var(--' + categoryVariable + '-accent-color) to li', index + 1);
							
							// Style em elements within the li to be black
							const emElements = li.querySelectorAll('em');
							emElements.forEach((em, emIndex) => {
								em.style.color = "black";
								em.style.fontWeight = "bold";
								em.style.fontStyle = "normal";
								em.style.marginRight = "0.25em";
								em.style.textShadow = "none"; // Remove shadow from black text for better readability
								////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Styled em element', emIndex + 1, 'to black in li', index + 1);
							});
						});
					}
				}
			}
		}, 100); // Small delay to ensure content is loaded
	} 
	
	// Check if carousel overlay exists in DOM (regardless of how it was opened)
	const carouselOverlay = document.querySelector('.jetpack-carousel-lightbox-overlay.active');
	if (carouselOverlay) {
		////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Detected active carousel lightbox');
		
		// For each slide in the carousel
		const carouselSlides = carouselOverlay.querySelectorAll('.wp-block-jetpack-slideshow_slide');
		////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Found', carouselSlides.length, 'carousel slides');
		
		carouselSlides.forEach((slide, slideIndex) => {
			const slideImage = slide.querySelector('img');
			if (slideImage) {
				////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Processing slide', slideIndex + 1, ':', slideImage.src);
				const categoryCode = extractCategoryFromImage(slideImage);
				////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Extracted category code for slide', slideIndex + 1, ':', categoryCode);
				
				if (categoryCode) {
					const categoryVariable = mapCategoryCodeToVariable(categoryCode);
					////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Mapped to variable for slide', slideIndex + 1, ':', categoryVariable);
					styleImagesByPageID(categoryVariable, slide);
				}
			}
		});
	}
	
	////console.log('Drinks Plugin (ucStyleLightBoxesByPageID): Lightbox styling complete');
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

/*
*   Share Carousel fns for global acces (so theme can Custom search)
*/
window.drinksPluginCarousel = {
    loadImages: loadCarouselImages,
    close: closeCarousel,
    open: openCocktailCarousel
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
     //////console.log('🔍 ucPortraitLandscape: Analyzing dimensions for aspect ratio management:', imageElement?.src || 'unknown');
    
    if (!imageElement || imageElement.tagName !== 'IMG') {
        console.warn('⚠️ ucPortraitLandscape: Invalid image element:', imageElement);
        return;
    }

    // ////console.log('🔍 ucPortraitLandscape: Image element found:', {
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

    // ////console.log('🔍 ucPortraitLandscape: Container found:', {
    //     tagName: container.tagName,
    //     className: container.className,
    //     id: container.id
    // });

    // Skip if already processed or if it's a special container
    if (container.classList.contains('pop-off') || 
        container.classList.contains('wp-block-gallery') ||
        container.classList.contains('portrait') ||
        container.classList.contains('landscape')) {
        // ////console.log('⏭️ ucPortraitLandscape: Skipping container - already processed or special type:', container.className);
        return;
    }

    function processImageDimensions() {
        // ////console.log('📐 ucPortraitLandscape: Analyzing longest dimension for:', imageElement.src);
        
        if (!imageElement.naturalWidth || !imageElement.naturalHeight) {
            // console.warn('⚠️ ucPortraitLandscape: No natural dimensions available:', {
            //     naturalWidth: imageElement.naturalWidth,
            //     naturalHeight: imageElement.naturalHeight
            // });
            return;
        }

        // ////console.log('📐 ucPortraitLandscape: Natural dimensions:', {
        //     width: imageElement.naturalWidth,
        //     height: imageElement.naturalHeight,
        //     ratio: (imageElement.naturalHeight / imageElement.naturalWidth).toFixed(2)
        // });

        // Remove existing dimension classes
        const hadPortrait = container.classList.contains('portrait');
        const hadLandscape = container.classList.contains('landscape');
        container.classList.remove('portrait', 'landscape');
        
        if (hadPortrait || hadLandscape) {
            // ////console.log('🔄 ucPortraitLandscape: Removed existing dimension classes:', {
            //     hadPortrait,
            //     hadLandscape
            // });
        }

        // Determine longest dimension for aspect ratio management
        if (imageElement.naturalHeight > imageElement.naturalWidth) {
            container.classList.add('portrait');
            // ////console.log('🖼️ ucPortraitLandscape: ✅ Height is longest - Added PORTRAIT class for aspect ratio management');
            // ////console.log('🖼️ ucPortraitLandscape: Updated container classes:', container.className);
        } else if (imageElement.naturalHeight < imageElement.naturalWidth) {
            container.classList.add('landscape');
            // ////console.log('🖼️ ucPortraitLandscape: ✅ Width is longest - Added LANDSCAPE class for aspect ratio management');
            // ////console.log('🖼️ ucPortraitLandscape: Updated container classes:', container.className);
        } else {
            // ////console.log('🖼️ ucPortraitLandscape: Image is square, no dimension class needed');
        }
    }

    // Process immediately if image is already loaded
    if (imageElement.complete && imageElement.naturalWidth && imageElement.naturalHeight) {
        // ////console.log('⚡ ucPortraitLandscape: Image already loaded, analyzing dimensions immediately');
        processImageDimensions();
    } else {
        // ////console.log('⏳ ucPortraitLandscape: Image not loaded yet, waiting for load event');
        imageElement.addEventListener('load', () => {
            // ////console.log('🔄 ucPortraitLandscape: Image load event fired, analyzing dimensions now');
            processImageDimensions();
        }, { once: true });
    }
}

/**
 * Initialize ucPortraitLandscape dimension analysis for all Image blocks
 */
function initImageOrientationDetection() {
   // ////console.log('🚀 Drinks Plugin: Initializing ucPortraitLandscape dimension analysis');

    // Process existing images
    const images = document.querySelectorAll('.wp-block-image img, figure img');
   // ////console.log('🔍 initImageOrientationDetection: Found', images.length, 'existing images to analyze');
    
    images.forEach((img, index) => {
   //     // ////console.log(`🔍 initImageOrientationDetection: Analyzing image ${index + 1}/${images.length}:`, img.src);
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
                            // ////console.log('🆕 initImageOrientationDetection: New image node detected:', node.src);
                            ucPortraitLandscape(node);
                            newImagesFound++;
                        }
                        // Check for images within the added node
                        const images = node.querySelectorAll ? node.querySelectorAll('img') : [];
                        if (images.length > 0) {
                            // ////console.log('🆕 initImageOrientationDetection: Found', images.length, 'images in new node');
                            images.forEach((img, index) => {
                                // ////console.log(`🆕 initImageOrientationDetection: Analyzing new image ${index + 1}/${images.length}:`, img.src);
                                ucPortraitLandscape(img);
                                newImagesFound++;
                            });
                        }
                    }
                });
            }
        });
        
        if (newImagesFound > 0) {
            // ////console.log(`🆕 initImageOrientationDetection: Analyzed ${newImagesFound} new images via observer`);
        }
    });

    // Observe the entire document for added nodes
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
  //  ////console.log('👁️ initImageOrientationDetection: MutationObserver set up to watch for new images');
}

/**
 * Search Results Page - Show overlay carousel with search results
 */
function initSearchPageCarousel() {
	// Check if we're on the search results page
	const isSearchPage = document.body.classList.contains('search') || 
	                     document.body.classList.contains('search-results');
	
	if (!isSearchPage) return;
	
	// Get search term from URL
	const urlParams = new URLSearchParams(window.location.search);
	const searchTerm = urlParams.get('s');
	
	if (!searchTerm) return;
	
	// Check if drinks plugin carousel is available
	if (!window.drinksPluginCarousel || !window.drinksPluginCarousel.loadImages) {
		console.error('Drinks plugin carousel not available');
		return;
	}
	
	// Get the overlay carousel (created by drinks plugin)
	const overlay = document.getElementById('drinks-carousel-overlay');
	if (!overlay) {
		console.error('Carousel overlay not found');
		return;
	}
	
	// Move overlay into main element to make it inline instead of overlay
	const mainElement = document.querySelector('body.search main');
	if (mainElement) {
		mainElement.appendChild(overlay);
	}
	
	// Load carousel images with search term as filter
	window.drinksPluginCarousel.loadImages(overlay, '', searchTerm, null);
	
	// Show carousel inline (not as overlay)
	requestAnimationFrame(() => {
		overlay.style.opacity = '1';
		overlay.style.pointerEvents = 'auto';
		overlay.classList.add('active');
		// Don't hide body overflow - allow page scrolling
	});
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLightbox);
} else {
    initLightbox();
}

// ESC key closes lightbox/carousel
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeLightbox();
        closeDrinksContentLightbox();
        closeCarousel();
    }
});

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initImageOrientationDetection);
} else {
    initImageOrientationDetection();
}

// Initialize search page carousel when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSearchPageCarousel);
} else {
    initSearchPageCarousel();
}

// Also initialize on window load to catch any late-loading images
window.addEventListener('load', () => {
     //////console.log('🌅 Drinks Plugin: Window load event fired, re-analyzing all images');
    // Re-analyze all images in case some loaded after DOMContentLoaded
    const images = document.querySelectorAll('.wp-block-image img, figure img');
    // ////console.log('🔍 Window load: Found', images.length, 'images to re-analyze');
    images.forEach((img, index) => {
        // ////console.log(`🔍 Window load: Re-analyzing image ${index + 1}/${images.length}:`, img.src);
        ucPortraitLandscape(img);
    });
});
