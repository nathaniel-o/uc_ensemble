/**
 * Drinks Plugin - Frontend Lightbox Functionality
 */

(function() {
    'use strict';

    // Lightbox state
    let currentLightbox = null;
    let isAnimating = false;

    /**
     * Initialize lightbox functionality
     */
    function initLightbox() {
        //debugger;
        
        // Add click handlers to lightbox containers
        document.addEventListener('click', handleLightboxClick);
        
        // Add keyboard support
        document.addEventListener('keydown', handleLightboxKeydown);
        
        // Add touch support for mobile
        document.addEventListener('touchstart', handleLightboxTouch, { passive: true });
        
        // Setup lightbox for existing images
        setupLightboxForImages();
        
        // Setup observer for dynamically added content
        setupLightboxObserver();
    }

    /**
     * Handle clicks on lightbox containers
     */
    function handleLightboxClick(event) {
        const container = event.target.closest('[data-wp-lightbox]');
        if (!container) return;

        event.preventDefault();
        event.stopPropagation();

        const img = container.querySelector('img');
        if (!img) return;

        openLightbox(img, container);
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

        //console.log('Drinks Plugin: Opening lightbox for image:', img.src);

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
     * Close lightbox
     */
    function closeLightbox() {
        if (!currentLightbox || isAnimating) return;

        //console.log('Drinks Plugin: Closing lightbox');

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
     * Setup lightbox for existing images
     */
    function setupLightboxForImages() {
        const images = document.querySelectorAll('[data-wp-lightbox] img');
        //debugger;
        
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

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLightbox);
    } else {
        initLightbox();
    }

    // Make functions globally available
    window.drinksPluginLightbox = {
        init: initLightbox,
        open: openLightbox,
        close: closeLightbox,
        setup: setupLightboxForImages
    };



    // New Jetpack-based Carousel Integration
    function initJetpackCarouselIntegration() {
        // Find all images with carousel functionality enabled
        const carouselImages = document.querySelectorAll('.cocktail-carousel');
        
        carouselImages.forEach(image => {
            // Make the entire image clickable for carousel
            const img = image.querySelector('img') || image;
            if (img) {
                img.style.cursor = 'pointer';
                img.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openJetpackCarouselForImage(image);
                });
                
                // Add hover effect to show it's clickable
                img.addEventListener('mouseenter', () => {
                    img.style.opacity = '0.9';
                    img.style.transform = 'scale(1.02)';
                });
                
                img.addEventListener('mouseleave', () => {
                    img.style.opacity = '1';
                    img.style.transform = 'scale(1)';
                });
            }
        });
    }

    function openJetpackCarouselForImage(imageElement) {
        // Get image data
        const img = imageElement.querySelector('img') || imageElement;
        const imageSrc = img.src;
        const imageAlt = img.alt || 'Drink Image';
        const imageId = img.dataset.id || img.getAttribute('data-id') || '';
        
        // Create Jetpack slideshow carousel
        const carouselModal = document.createElement('div');
        carouselModal.className = 'jetpack-carousel-modal-overlay';
        carouselModal.innerHTML = `
            <div class="jetpack-carousel-modal-content">
                <div class="jetpack-carousel-modal-header">
                    <h3>🎠 Drinks Carousel</h3>
                    <button class="jetpack-carousel-modal-close" aria-label="Close carousel">×</button>
                </div>
                <div class="jetpack-carousel-modal-body">
                    <div class="wp-block-jetpack-slideshow aligncenter" data-autoplay="false" data-delay="3" data-effect="slide">
                        <div class="wp-block-jetpack-slideshow_container swiper-container">
                            <ul class="wp-block-jetpack-slideshow_swiper-wrapper swiper-wrapper" id="drinks-carousel-slides">
                                <li class="wp-block-jetpack-slideshow_slide swiper-slide">
                                    <figure>
                                        <img src="${imageSrc}" alt="${imageAlt}" class="wp-block-jetpack-slideshow_image" />
                                        <figcaption>${imageAlt}</figcaption>
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
        
        // Add modal to page
        document.body.appendChild(carouselModal);
        
        // Load additional random drinks
        loadRandomDrinksForCarousel(carouselModal, imageId);
        
        // Close button functionality
        const closeBtn = carouselModal.querySelector('.jetpack-carousel-modal-close');
        closeBtn.addEventListener('click', () => {
            document.body.removeChild(carouselModal);
        });
        
        // Close on overlay click
        carouselModal.addEventListener('click', (e) => {
            if (e.target === carouselModal) {
                document.body.removeChild(carouselModal);
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape' && document.body.contains(carouselModal)) {
                document.body.removeChild(carouselModal);
                document.removeEventListener('keydown', closeOnEscape);
            }
        });
        
        // Show modal
        setTimeout(() => carouselModal.classList.add('active'), 10);
    }

    function loadRandomDrinksForCarousel(modal, excludeImageId) {
        const slidesContainer = modal.querySelector('#drinks-carousel-slides');
        if (!slidesContainer) return;

        // Show loading state
        slidesContainer.innerHTML += '<li class="wp-block-jetpack-slideshow_slide swiper-slide loading-slide"><div class="loading-spinner">Loading drinks...</div></li>';

        // Make AJAX call to get random drinks
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=filter_carousel&search_term=&exclude_id=${excludeImageId}`
        })
        .then(response => response.text())
        .then(html => {
            // Remove loading slide and add new slides
            const loadingSlide = slidesContainer.querySelector('.loading-slide');
            if (loadingSlide) {
                loadingSlide.remove();
            }
            
            // Parse the HTML and add slides
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const newSlides = tempDiv.querySelectorAll('li');
            
            newSlides.forEach(slide => {
                slidesContainer.appendChild(slide.cloneNode(true));
            });
            
            // Update pagination
            updateCarouselPagination(slidesContainer);
            
            //debugger;
        })
        .catch(error => {
            console.error('Drinks Plugin: Error loading random drinks:', error);
            const loadingSlide = slidesContainer.querySelector('.loading-slide');
            if (loadingSlide) {
                loadingSlide.innerHTML = '<div class="error-slide">Error loading drinks</div>';
            }
        });
    }

    function updateCarouselPagination(container) {
        const slides = container.querySelectorAll('.wp-block-jetpack-slideshow_slide');
        const pagination = container.parentElement.querySelector('.wp-block-jetpack-slideshow_pagination');
        
        if (pagination && slides.length > 1) {
            pagination.innerHTML = '';
            slides.forEach((slide, index) => {
                const bullet = document.createElement('button');
                bullet.className = `swiper-pagination-bullet ${index === 0 ? 'swiper-pagination-bullet-active' : ''}`;
                bullet.setAttribute('tab-index', '0');
                bullet.setAttribute('role', 'button');
                bullet.setAttribute('aria-label', `Go to slide ${index + 1}`);
                pagination.appendChild(bullet);
            });
        }
    }

    // Initialize Jetpack carousel integration when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initJetpackCarouselIntegration);
    } else {
        initJetpackCarouselIntegration();
    }

})();
