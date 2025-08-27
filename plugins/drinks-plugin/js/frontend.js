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
        console.log('Drinks Plugin: Frontend lightbox initialized');
        
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

        console.log('Drinks Plugin: Opening lightbox for image:', img.src);

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

        console.log('Drinks Plugin: Closing lightbox');

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
        console.log('Drinks Plugin: Found', images.length, 'lightbox images');
        
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

})();
