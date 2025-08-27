/**
 * Cocktail Images Plugin - WordPress Core Lightbox Implementation
 * Replicates WordPress core lightbox functionality
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
        // Add click handlers to lightbox containers
        document.addEventListener('click', handleLightboxClick);
        
        // Add keyboard support
        document.addEventListener('keydown', handleLightboxKeydown);
        
        // Add touch support for mobile
        document.addEventListener('touchstart', handleLightboxTouch, { passive: true });
        
        console.log('Cocktail Images: Lightbox initialized');
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

        // Calculate image position and size
        const rect = img.getBoundingClientRect();
        const scale = Math.min(window.innerWidth / rect.width, window.innerHeight / rect.height);
        
        // Create overlay
        const overlay = createLightboxOverlay(img, rect, scale);
        document.body.appendChild(overlay);

        // Set CSS variables for animation
        overlay.style.setProperty('--wp--lightbox-initial-left-position', rect.left + 'px');
        overlay.style.setProperty('--wp--lightbox-initial-top-position', rect.top + 'px');
        overlay.style.setProperty('--wp--lightbox-scale', scale.toString());
        overlay.style.setProperty('--wp--lightbox-scrollbar-width', getScrollbarWidth() + 'px');

        // Trigger animation
        requestAnimationFrame(() => {
            overlay.classList.add('active', 'zoom');
            currentLightbox = overlay;
        });
    }

    /**
     * Close lightbox
     */
    function closeLightbox() {
        if (!currentLightbox || isAnimating) return;

        isAnimating = true;
        currentLightbox.classList.add('show-closing-animation');
        currentLightbox.classList.remove('active');

        setTimeout(() => {
            if (currentLightbox && currentLightbox.parentNode) {
                currentLightbox.parentNode.removeChild(currentLightbox);
            }
            currentLightbox = null;
            isAnimating = false;
        }, 400);
    }

    /**
     * Create lightbox overlay
     */
    function createLightboxOverlay(img, rect, scale) {
        const overlay = document.createElement('div');
        overlay.className = 'wp-lightbox-overlay';
        overlay.innerHTML = `
            <div class="scrim"></div>
            <div class="lightbox-image-container">
                <figure class="wp-block-image">
                    <img src="${img.src}" alt="${img.alt || ''}" />
                </figure>
            </div>
            <button class="close-button" aria-label="Close lightbox">×</button>
        `;

        // Add close button handler
        const closeButton = overlay.querySelector('.close-button');
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
     * Get scrollbar width
     */
    function getScrollbarWidth() {
        return window.innerWidth - document.documentElement.clientWidth;
    }

    /**
     * Add lightbox container wrapper
     */
    function wrapImageInLightboxContainer(img) {
        const figure = img.closest('figure');
        if (!figure) return;

        // Check if already wrapped
        if (figure.parentNode.classList.contains('wp-lightbox-container')) {
            return;
        }

        // Create container
        const container = document.createElement('div');
        container.className = 'wp-lightbox-container';
        container.setAttribute('data-wp-lightbox', 'true');
        container.setAttribute('data-wp-lightbox-group', 'cocktail-images');

        // Create zoom button
        const button = document.createElement('button');
        button.innerHTML = '🔍';
        button.setAttribute('aria-label', 'Open lightbox');

        // Wrap the figure
        figure.parentNode.insertBefore(container, figure);
        container.appendChild(figure);
        container.appendChild(button);
    }

    /**
     * Setup lightbox for existing images
     */
    function setupLightboxForImages() {
        const images = document.querySelectorAll('figure.wp-block-image[data-wp-lightbox] img');
        images.forEach(img => {
            wrapImageInLightboxContainer(img);
        });
    }

    /**
     * Setup lightbox for dynamically added content
     */
    function setupLightboxObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1 && node.querySelector) {
                            const lightboxImages = node.querySelectorAll('figure.wp-block-image[data-wp-lightbox] img');
                            lightboxImages.forEach(img => {
                                wrapImageInLightboxContainer(img);
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
        document.addEventListener('DOMContentLoaded', () => {
            initLightbox();
            setupLightboxForImages();
            setupLightboxObserver();
        });
    } else {
        initLightbox();
        setupLightboxForImages();
        setupLightboxObserver();
    }

    // Make functions globally available
    window.cocktailImagesLightbox = {
        init: initLightbox,
        open: openLightbox,
        close: closeLightbox,
        setup: setupLightboxForImages
    };

})();
