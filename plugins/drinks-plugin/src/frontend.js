/**
 * Drinks Plugin - Frontend Lightbox Functionality
 */

// Lightbox state
let currentLightbox = null;
let isAnimating = false;

/**
 * Initialize lightbox functionality
 */
    function initLightbox() {
        // console.log('Drinks Plugin: Frontend lightbox initialized');
    
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
