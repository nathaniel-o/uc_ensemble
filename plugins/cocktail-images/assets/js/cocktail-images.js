/**
 * Cocktail Images Plugin JavaScript
 * Handles image randomization functionality
 */

(function() {
    'use strict';

    // Image Randomization Functions for WordPress Image Blocks
    /*
     * FEATURE: Image Randomization on Click
     * 
     * This feature allows users to click on any WordPress Image Block to randomize
     * the image shown. The new image will be randomly selected from your drink posts.
     * 
     * HOW IT WORKS:
     * 1. Click any WordPress Image Block (figure.wp-block-image)
     * 2. AJAX call fetches a random drink post thumbnail
     * 3. Image source and attributes are updated
     * 4. Portrait/landscape classes are reapplied
     * 
     * REQUIREMENTS:
     * - WordPress Image Blocks (figure.wp-block-image)
     * - Drink posts with featured images
     * - AJAX handler in plugin
     * 
     * VISUAL INDICATORS:
     * - Images have pointer cursor on hover
     * - Hover effect shows 🔄 icon
     * - Console logs show randomization activity
     */

    function ucRandomizeImage(e) {
        e.preventDefault(); // Stop page refresh
        
        const clickedImage = e.target;
        if (clickedImage.tagName !== 'IMG') {
            return; // Only handle image clicks
        }
        
        // Check if this is a WordPress Image Block
        const figure = clickedImage.closest('figure.wp-block-image');
        if (!figure) {
            return; // Only handle WordPress Image Blocks
        }
        
        // Get current image data for reference
        const currentImageId = clickedImage.getAttribute('data-id') || clickedImage.getAttribute('data-attachment-id');
        const currentAlt = clickedImage.getAttribute('alt') || '';
        
        console.log('Randomizing image:', currentImageId, currentAlt);
        
        const ajaxUrl = cocktailImagesAjax.ajaxurl;
        const requestBody = `action=randomize_image&current_id=${encodeURIComponent(currentImageId)}&nonce=${cocktailImagesAjax.nonce}`;
        
        console.log('AJAX URL:', ajaxUrl);
        console.log('Request body:', requestBody);
        
        // Make AJAX call to get random image
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: requestBody
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.image) {
                const newImage = data.data.image;
                
                console.log('Image randomized to:', newImage.title);
                console.log('New image URL:', newImage.src);
                
                // Update the image source and attributes
                clickedImage.src = newImage.src;
                clickedImage.alt = newImage.alt;
                clickedImage.setAttribute('data-id', newImage.id);
                
                // Update WordPress-specific attributes
                if (newImage.attachment_id) {
                    clickedImage.setAttribute('data-attachment-id', newImage.attachment_id);
                }
                
                // Update srcset and sizes for responsive images
                if (newImage.srcset) {
                    clickedImage.setAttribute('srcset', newImage.srcset);
                }
                if (newImage.sizes) {
                    clickedImage.setAttribute('sizes', newImage.sizes);
                }
                
                // Update other WordPress data attributes
                if (newImage.data_orig_file) {
                    clickedImage.setAttribute('data-orig-file', newImage.data_orig_file);
                }
                if (newImage.data_orig_size) {
                    clickedImage.setAttribute('data-orig-size', newImage.data_orig_size);
                }
                if (newImage.data_image_title) {
                    clickedImage.setAttribute('data-image-title', newImage.data_image_title);
                }
                if (newImage.data_image_caption) {
                    clickedImage.setAttribute('data-image-caption', newImage.data_image_caption);
                }
                if (newImage.data_medium_file) {
                    clickedImage.setAttribute('data-medium-file', newImage.data_medium_file);
                }
                if (newImage.data_large_file) {
                    clickedImage.setAttribute('data-large-file', newImage.data_large_file);
                }
                
                // Update class to match new image
                if (newImage.attachment_id) {
                    clickedImage.className = clickedImage.className.replace(/wp-image-\d+/, `wp-image-${newImage.attachment_id}`);
                }
                
                // Update the figcaption if it exists
                const figcaption = figure.querySelector('figcaption');
                if (figcaption && newImage.data_image_caption) {
                    figcaption.innerHTML = newImage.data_image_caption;
                    console.log('Updated figcaption:', newImage.data_image_caption);
                }
                
                // Force image reload - try a different approach
                clickedImage.onload = function() {
                    console.log('New image loaded successfully');
                    // Update figure classes if needed
                    if (typeof ucPortraitLandscape === 'function') {
                        ucPortraitLandscape(clickedImage, figure);
                    }
                };
                
                // If onload doesn't fire, force it after a delay
                setTimeout(() => {
                    if (clickedImage.complete) {
                        console.log('Image load completed');
                        if (typeof ucPortraitLandscape === 'function') {
                            ucPortraitLandscape(clickedImage, figure);
                        }
                    }
                }, 100);
                
            } else {
                console.error('Failed to randomize image:', data.message);
            }
        })
        .catch(error => {
            console.error('Error randomizing image:', error);
        });
    }
    
    // Apply click event listeners to WordPress Image Blocks
    function ucSetupImageRandomization() {
        const imageBlocks = document.querySelectorAll('figure.wp-block-image img');
        
        imageBlocks.forEach(img => {
            // Set up automatic randomization for each image
            const randomDelay = Math.random() * (90000 - 10000) + 10000; // 10-90 seconds in milliseconds
            console.log(`Setting up auto-randomization for image in ${Math.round(randomDelay/1000)}s`);
            
            setTimeout(() => {
                // Create a fake click event to trigger randomization
                const fakeEvent = {
                    target: img,
                    preventDefault: () => {}
                };
                ucRandomizeImage(fakeEvent);
                
                // Set up recurring randomization every 10-90 seconds
                const setupRecurringRandomization = () => {
                    const nextDelay = Math.random() * (90000 - 10000) + 10000;
                    console.log(`Next auto-randomization in ${Math.round(nextDelay/1000)}s`);
                    setTimeout(() => {
                        const fakeEvent = {
                            target: img,
                            preventDefault: () => {}
                        };
                        ucRandomizeImage(fakeEvent);
                        setupRecurringRandomization(); // Schedule next randomization
                    }, nextDelay);
                };
                
                setupRecurringRandomization();
            }, randomDelay);
        });
        
        console.log(`Setup auto-randomization for ${imageBlocks.length} image blocks`);
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Setup image randomization - DISABLED, using title matching instead
        // ucSetupImageRandomization();
        
        // Add click event listeners for manual randomization - DISABLED, using title matching instead
        // document.addEventListener('click', function(e) {
        //     if (e.target.tagName === 'IMG' && e.target.closest('figure.wp-block-image')) {
        //         ucRandomizeImage(e);
        //     }
        // });
        
        // Setup one drink all images - title matching version
        ucSetupOneDrinkAllImages();
        
    });


    // One Drink All Images - Title Matching Version
    function ucOneDrinkAllImages(e) {
        e.preventDefault(); // Stop page refresh
        
        const clickedImage = e.target;
        if (clickedImage.tagName !== 'IMG') {
            return; // Only handle image clicks
        }
        
        // Check if this is a WordPress Image Block
        const figure = clickedImage.closest('figure.wp-block-image');
        if (!figure) {
            return; // Only handle WordPress Image Blocks
        }
        
        // Get current image data for reference
        const currentImageId = clickedImage.getAttribute('data-id') || clickedImage.getAttribute('data-attachment-id');
        const currentAlt = clickedImage.getAttribute('alt') || '';
        const currentTitle = clickedImage.getAttribute('data-image-title') || '';
        
        // Get or initialize queue tracking for this image
        const queueKey = `queue_${currentImageId}`;
        let queueData = window[queueKey] || { currentIndex: 0, totalMatches: 0, baseTitle: '', matches: [] };
        
        // Extract base title for matching
        let baseTitle = currentTitle || currentAlt;
        
        // Clean up the title for matching
        baseTitle = baseTitle
            .replace(/^T2-/, '') // Remove T2- prefix
            .replace(/[-_]/g, ' ') // Replace hyphens and underscores with spaces
            .replace(/\s+/g, ' ') // Normalize spaces
            .trim();
        
        // Check if we need to search for new matches
        const needsNewSearch = queueData.baseTitle !== baseTitle || queueData.matches.length === 0;
        
        if (needsNewSearch) {
            // Reset queue data for new search
            queueData = { currentIndex: 0, totalMatches: 0, baseTitle: baseTitle, matches: [] };
            console.log(`Searching for matches: "${baseTitle}"`);
            
            // Get all matches for new search
            const ajaxUrl = cocktailImagesAjax.ajaxurl;
            const requestBody = `action=find_matching_image&current_id=${encodeURIComponent(currentImageId)}&base_title=${encodeURIComponent(baseTitle)}&is_new_search=true&nonce=${cocktailImagesAjax.nonce}`;
            
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: requestBody
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.all_matches) {
                    // Cache all matches
                    queueData.matches = data.data.all_matches;
                    queueData.totalMatches = data.data.total_matches;
                    window[queueKey] = queueData;
                    
                    console.log(`Found ${queueData.totalMatches} matches, cached for future use`);
                    
                    // Now cycle to the first match
                    cycleToNextMatch(clickedImage, figure, queueData, queueKey);
                } else {
                    console.error('Failed to find matches:', data.message);
                }
            })
            .catch(error => {
                console.error('Error finding matches:', error);
            });
            
        } else {
            // Use cached matches - cycle to next match locally
            console.log(`Using cached matches: "${baseTitle}" (index: ${queueData.currentIndex}/${queueData.totalMatches})`);
            cycleToNextMatch(clickedImage, figure, queueData, queueKey);
        }
    }
    
    // Helper function to cycle to next match using cached data
    function cycleToNextMatch(clickedImage, figure, queueData, queueKey) {
        if (queueData.matches.length === 0) {
            console.error('No cached matches available');
            return;
        }
        
        // Get next match from cache
        const nextIndex = queueData.currentIndex % queueData.totalMatches;
        const newImage = queueData.matches[nextIndex];
        
        // Update queue data
        queueData.currentIndex = (nextIndex + 1) % queueData.totalMatches;
        window[queueKey] = queueData;
        
        console.log(`Match ${nextIndex + 1}/${queueData.totalMatches}: "${newImage.title}"`);
        
        // Create white placeholder effect
        const overlay = createWhitePlaceholder(figure);
        
        // Wait 1 second with white placeholder
        setTimeout(() => {
            // Update the image source and attributes
            clickedImage.src = newImage.src;
            clickedImage.alt = newImage.alt;
            clickedImage.setAttribute('data-id', newImage.id);
            
            // Update WordPress-specific attributes
            if (newImage.attachment_id) {
                clickedImage.setAttribute('data-attachment-id', newImage.attachment_id);
            }
            
            // Update srcset and sizes for responsive images
            if (newImage.srcset) {
                clickedImage.setAttribute('srcset', newImage.srcset);
            }
            if (newImage.sizes) {
                clickedImage.setAttribute('sizes', newImage.sizes);
            }
            
            // Update other WordPress data attributes
            if (newImage.data_orig_file) {
                clickedImage.setAttribute('data-orig-file', newImage.data_orig_file);
            }
            if (newImage.data_orig_size) {
                clickedImage.setAttribute('data-orig-size', newImage.data_orig_size);
            }
            if (newImage.data_image_title) {
                clickedImage.setAttribute('data-image-title', newImage.data_image_title);
            }
            if (newImage.data_image_caption) {
                clickedImage.setAttribute('data-image-caption', newImage.data_image_caption);
            }
            if (newImage.data_medium_file) {
                clickedImage.setAttribute('data-medium-file', newImage.data_medium_file);
            }
            if (newImage.data_large_file) {
                clickedImage.setAttribute('data-large-file', newImage.data_large_file);
            }
            
            // Update class to match new image
            if (newImage.attachment_id) {
                clickedImage.className = clickedImage.className.replace(/wp-image-\d+/, `wp-image-${newImage.attachment_id}`);
            }
            
            // Update the figcaption if it exists
            const figcaption = figure.querySelector('figcaption');
            if (figcaption && newImage.data_image_caption) {
                figcaption.innerHTML = newImage.data_image_caption;
                console.log('Updated figcaption:', newImage.data_image_caption);
            }
            
            // Fade out white overlay after new image loads
            clickedImage.onload = function() {
                console.log('New image loaded successfully');
                fadeOutPlaceholder(overlay);
                
                // Update figure classes if needed
                if (typeof ucPortraitLandscape === 'function') {
                    ucPortraitLandscape(clickedImage, figure);
                }
            };
            
            // If onload doesn't fire, force it after a delay
            setTimeout(() => {
                if (clickedImage.complete) {
                    console.log('Image load completed');
                    fadeOutPlaceholder(overlay);
                    
                    if (typeof ucPortraitLandscape === 'function') {
                        ucPortraitLandscape(clickedImage, figure);
                    }
                }
            }, 100);
            
        }, 1000); // 1 second delay
    }
    
    // Helper function to create white placeholder
    function createWhitePlaceholder(figure) {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        `;
        
        // Add overlay to figure
        figure.style.position = 'relative';
        figure.appendChild(overlay);
        
        // Fade in white overlay
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);
        
        return overlay;
    }
    
    // Helper function to fade out placeholder
    function fadeOutPlaceholder(overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
    
    function ucSetupOneDrinkAllImages() {
        const imageBlocks = document.querySelectorAll('figure.wp-block-image img');
        
        imageBlocks.forEach(img => {
            // Set up automatic title matching for each image
            const randomDelay = Math.random() * (40000 - 5000) + 5000; // 5-40 seconds in milliseconds
            console.log(`Setting up auto-title-matching for image in ${Math.round(randomDelay/1000)}s`);
            
            setTimeout(() => {
                // Create a fake click event to trigger title matching
                const fakeEvent = {
                    target: img,
                    preventDefault: () => {}
                };
                ucOneDrinkAllImages(fakeEvent);
                
                // Set up recurring title matching every 10-90 seconds
                const setupRecurringTitleMatching = () => {
                    const nextDelay = Math.random() * (40000 - 5000) + 5000;
                    console.log(`Next auto-title-matching in ${Math.round(nextDelay/1000)}s`);
                    setTimeout(() => {
                        const fakeEvent = {
                            target: img,
                            preventDefault: () => {}
                        };
                        ucOneDrinkAllImages(fakeEvent);
                        setupRecurringTitleMatching(); // Schedule next title matching
                    }, nextDelay);
                };
                
                setupRecurringTitleMatching();
            }, randomDelay);
        });
        
        console.log(`Setup auto-title-matching for ${imageBlocks.length} image blocks`);
    }



    // Make functions globally available for backward compatibility - Necessary for dom_content_loaded from PHP due to echo'd into page directly. 
    window.ucRandomizeImage = ucRandomizeImage;
    window.ucSetupImageRandomization = ucSetupImageRandomization;
    window.ucOneDrinkAllImages = ucOneDrinkAllImages;
    window.ucSetupOneDrinkAllImages = ucSetupOneDrinkAllImages;

})(); 


	// Initialize image randomization when page loads
	// CALLED BY functions.php DOM lstnr
	// document.addEventListener('DOMContentLoaded', ucSetupImageRandomization);
