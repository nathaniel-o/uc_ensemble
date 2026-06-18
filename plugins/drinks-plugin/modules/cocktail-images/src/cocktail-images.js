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
        
       // //console.log('Cocktail Images: Randomizing image:', currentImageId, currentAlt);
        
        const ajaxUrl = cocktailImagesAjax.ajaxurl;
        const requestBody = `action=randomize_image&current_id=${encodeURIComponent(currentImageId)}&nonce=${cocktailImagesAjax.nonce}`;
        
        ////console.log('Cocktail Images: AJAX URL:', ajaxUrl);
        ////console.log('Cocktail Images: Request body:', requestBody);
        
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
                
                ////console.log('Cocktail Images: Image randomized to:', newImage.title);
                ////console.log('Cocktail Images: New image URL:', newImage.src);
                
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
                if (figcaption) {
                    // Get the original caption text (store it if not already stored)
                    if (!figcaption.getAttribute('data-original-caption')) {
                        figcaption.setAttribute('data-original-caption', figcaption.innerHTML);
                    }
                    
                    // Normalize the new image title for display
                    const newImageTitle = newImage.data_image_caption || newImage.title || '';
                    const normalizedTitle = ucNormalizeTitle(newImageTitle, true);
                    
                    if (normalizedTitle) {
                        // Show only the normalized title, but keep original in data attribute for SEO
                        figcaption.innerHTML = normalizedTitle;
                    }
                    
                    ////console.log('Cocktail Images: Updated figcaption with normalized title:', normalizedTitle);
                }
                
                // Force image reload - try a different approach
                clickedImage.onload = function() {
                   // //console.log('Cocktail Images: New image loaded successfully');
                    // Update figure classes if needed
                    // console.log(typeof window.drinksPluginStyling?.ucPortraitLandscape)
                    if (typeof window.drinksPluginStyling.ucPortraitLandscape === 'function') {
                        window.drinksPluginStyling.ucPortraitLandscape(clickedImage, figure);
                    }
                };
                
                // If onload doesn't fire, force it after a delay
                setTimeout(() => {
                    if (clickedImage.complete) {
                       // console.log('Cocktail Images: Image load completed');
                        if (typeof window.drinksPluginStyling.ucPortraitLandscape === 'function') {
                            window.drinksPluginStyling.ucPortraitLandscape(clickedImage, figure);
                        }
                    }
                }, 100);
                
            } else {
                //console.error('Failed to randomize image:', data.message);
            }
        })
        .catch(error => {
            //console.error('Error randomizing image:', error);
        });
    }



    // Determine whether this image is the Featured Image of a Post        
    function ucDoesImageHavePost(img) {
                // Get the image ID from the wp-image-{id} class
                const imgClasses = img.className.split(' ');
                const imgIdClass = imgClasses.find(cls => cls.startsWith('wp-image-'));
                const imgId = imgIdClass ? imgIdClass.replace('wp-image-', '') : null;

                

            if (imgId) {
                // Check if this image is featured in any posts via AJAX
                // Get the image title from the alt attribute or src
                const imageTitle = img.alt || img.getAttribute('data-image-title') || img.src.split('/').pop().replace(/\.[^/.]+$/, '');
                
                const requestData = {
                    action: 'check_featured_image',
                    nonce: cocktailImagesAjax.nonce,
                    image_title: imageTitle
                };
                
               // //console.log('Cocktail Images: Sending AJAX request:', requestData);
               // //console.log('Cocktail Images: AJAX URL:', cocktailImagesAjax.ajaxurl);
               // //console.log('Cocktail Images: Nonce:', cocktailImagesAjax.nonce);
                
                return fetch(cocktailImagesAjax.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(requestData)
                })
                .then(response => {
                    // Check if response is ok before trying to parse JSON
                    if (!response.ok) {
                        // Get the response text to see what the server is actually returning
                        return response.text().then(text => {
                            //console.error('Server response:', text);
                            throw new Error(`HTTP error! status: ${response.status} - Server returned: ${text.substring(0, 200)}`);
                        });
                    }
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                           // console.error('Non-JSON response:', text);
                            throw new Error('Response is not JSON');
                        });
                    }
                    return response.json();
                })
                .then(response => {
                    if (response.success) {
                        const data = response.data; // Extract the nested data
                       // //console.log("Cocktail Images: Full response data:", response);
                        if (data.post_id) {
                            if (data.exact_match) {
                                ////console.log(`✅ EXACT MATCH: Image "${data.image_title}" matches post: "${data.post_title}" (ID: ${data.post_id})`);
                                if (data.other_matches && data.other_matches.length > 0) {
                                   // //console.log(`Cocktail Images: 📋 Other matches found (${data.other_matches.length}):`, data.other_matches);
                                }
                            } else {
                                ////console.log(`⚠️ PARTIAL MATCH: Image "${data.image_title}" matches post: "${data.post_title}" (ID: ${data.post_id})`);
                                ////console.log(`Normalized title: "${data.normalized_image_title}"`);
                                if (data.all_matches && data.all_matches.length > 1) {
                                    ////console.log(`📋 All partial matches (${data.all_matches.length}):`, data.all_matches);
                                }
                            }
                            return data.post_id;
                        } else {
                            /////console.log(`❌ NO MATCH: Image "${data.image_title}" - no matching posts found`);
                            /////console.log(`Normalized title: "${data.normalized_image_title}"`);
                            return false;
                        }
                    }
                    return false;
                })
                .catch(error => {
                    //console.error('Error checking featured image status:', error);
                    return false;
                });
            }
        
        return Promise.resolve(false);
    }

    
    //  Not in Use 
    // Apply click event listeners to WordPress Image Blocks
    function ucSetupImageRandomization() {
        const imageBlocks = document.querySelectorAll('figure.wp-block-image img');
        

        

        imageBlocks.forEach(img => {


            
           
            // Set up automatic randomization for each image
            const randomDelay = Math.random() * (90000 - 10000) + 10000; // 10-90 seconds in milliseconds
            ////console.log(`Setting up auto-randomization for image in ${Math.round(randomDelay/1000)}s`);
            
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
                    ////console.log(`Cocktail Images: Next auto-randomization in ${Math.round(nextDelay/1000)}s`);
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
        
        // console.log(`Cocktail Images: Setup auto-randomization for ${imageBlocks.length} image blocks`);
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on the media library admin page
        if (window.location.href.includes('upload.php')) {
            // Do Nothing 
        } else {
            // Setup image randomization - DISABLED, using title matching instead
            // ucSetupImageRandomization();
            
            // Page-level image cycling disabled; fade is used on drink-detail pop-outs only.
            // ucSetupOneDrinkAllImages();
        }
    });


    // Helper function to trim dimension suffixes from image URLs
    function trimImageDimensions(url) {
        if (!url) return url;
        // Remove dimension patterns like -225x300, -768x1024, etc. from JPG, PNG, and WebP files
        return url.replace(/-\d+x\d+\.(jpg|jpeg|png|webp)$/i, '.$1');
    }

    function trimSrcsetDimensions(srcset) {
        if (!srcset) return srcset;
        // Since we're using the original full-resolution image, 
        // just return the first URL trimmed (most efficient approach)
        const firstEntry = srcset.split(',')[0].trim();
        const [url, descriptor] = firstEntry.split(' ');
        const trimmedUrl = trimImageDimensions(url);
        return descriptor ? `${trimmedUrl} ${descriptor}` : trimmedUrl;
    }

    function ucIsBannerImage(img) {
        const title = img.getAttribute('data-image-title') || img.getAttribute('alt') || '';
        return title.toLowerCase().includes('banner');
    }

    function resolveImageAttachmentId(img) {
        return img.getAttribute('data-id')
            || img.getAttribute('data-attachment-id')
            || (img.className.match(/wp-image-(\d+)/) || [])[1]
            || '';
    }

    function resolveImageFigure(img, options = {}) {
        return options.figure || img.closest('figure') || img.parentElement;
    }

    function getImageMatchContext(img, options = {}) {
        const currentImageId = resolveImageAttachmentId(img);
        const currentAlt = img.getAttribute('alt') || '';
        const currentTitle = img.getAttribute('data-image-title') || '';
        const baseTitle = options.baseTitle || ucNormalizeTitle(currentTitle || ucTitleSource(img, currentAlt));
        const queueKey = options.queueKey || `queue_${currentImageId}`;

        return { currentImageId, baseTitle, queueKey };
    }

    function fetchMatchingImages(currentImageId, baseTitle) {
        const ajaxUrl = cocktailImagesAjax.ajaxurl;
        const requestBody = `action=find_matching_image&current_id=${encodeURIComponent(currentImageId)}&base_title=${encodeURIComponent(baseTitle)}&is_new_search=true&nonce=${cocktailImagesAjax.nonce}`;

        return fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: requestBody
        }).then(response => response.json());
    }

    function cycleMatchedImage(img, options = {}) {
        if (!img || img.tagName !== 'IMG' || ucIsBannerImage(img)) {
            return Promise.resolve(false);
        }

        const figure = resolveImageFigure(img, options);
        const { currentImageId, baseTitle, queueKey } = getImageMatchContext(img, options);
        let queueData = window[queueKey] || { currentIndex: 0, totalMatches: 0, baseTitle: '', matches: [] };
        const needsNewSearch = queueData.baseTitle !== baseTitle || queueData.matches.length === 0;

        if (needsNewSearch) {
            queueData = { currentIndex: 0, totalMatches: 0, baseTitle, matches: [] };

            return fetchMatchingImages(currentImageId, baseTitle)
                .then(data => {
                    if (!data.success || !data.data?.all_matches?.length) {
                        return false;
                    }

                    queueData.matches = data.data.all_matches;
                    queueData.totalMatches = data.data.total_matches;
                    window[queueKey] = queueData;
                    cycleToNextMatch(img, figure, queueData, queueKey);
                    return true;
                })
                .catch(() => false);
        }

        cycleToNextMatch(img, figure, queueData, queueKey);
        return Promise.resolve(true);
    }

    const POPOUT_CYCLE_MS = 12000;

    function startMatchedImageCycle(img, options = {}) {
        const intervalMs = options.intervalMs ?? POPOUT_CYCLE_MS;
        const getImg = typeof options.getImg === 'function' ? options.getImg : () => img;
        let stopped = false;
        let cycling = false;
        let timerId = null;

        const tick = async () => {
            if (stopped || cycling) {
                return;
            }

            const activeImg = getImg();
            if (!activeImg || !document.body.contains(activeImg)) {
                stop();
                return;
            }

            cycling = true;
            try {
                await cycleMatchedImage(activeImg, {
                    ...options,
                    figure: activeImg.closest('figure') || options.figure
                });
            } finally {
                cycling = false;
            }
        };

        timerId = setInterval(tick, intervalMs);

        const stop = () => {
            stopped = true;
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
        };

        return stop;
    }

    function ucTriggerOneDrinkAllImages(img) {
        cycleMatchedImage(img, { figure: img.closest('figure.wp-block-image') });
    }

    // One Drink All Images - Title Matching Version
    function ucOneDrinkAllImages(e) {
        e.preventDefault();

        const clickedImage = e.target;
        if (clickedImage.tagName !== 'IMG') {
            return;
        }

        const figure = clickedImage.closest('figure.wp-block-image');
        if (!figure) {
            return;
        }

        cycleMatchedImage(clickedImage, { figure });
    }

    // Drink captions use "DrinkName: description"; fall back to filename when missing (outlier images).
    function ucTitleSource(img, text) {
        const value = text || '';
        if (value.includes(':')) {
            return value;
        }
        const stem = (img.src || '').split('/').pop().replace(/\.[^/.]+$/, '').replace(/-\d+x\d+$/, '');
        return stem || value;
    }

    /*  Helper function for JS title normalizations */
    function ucNormalizeTitle(title, preserveCapitalization = false) {
        let baseTitle = title
            .split(':')[0] // Truncate at colon if present
            .replace(/^T2-/, '') // Remove T2- prefix
            .replace(/[-_]/g, ' ') // Replace hyphens and underscores with spaces
            .replace(/\s+/g, ' ') // Normalize spaces
            .trim();
        
        // Remove category codes from the end (AU, SO, SU, SP, FP, EV, RO, WI)
        baseTitle = baseTitle.replace(/(AU|SO|SU|SP|FP|EV|RO|WI)$/, '');
        
        baseTitle = baseTitle
            .split(' ')
            .filter(word => word.length >= 3) // Remove words <3 letters
            .join(' ');
        
        // Only convert to lowercase if not preserving capitalization
        if (!preserveCapitalization) {
            baseTitle = baseTitle.toLowerCase();
        }
        
        return baseTitle;
        }



    
    
    // Helper function to cycle to next match using cached data
    function cycleToNextMatch(clickedImage, figure, queueData, queueKey) {
        if (queueData.matches.length === 0) {
          //  console.error('No cached matches available');
            return;
        }
        
        // Get next match from cache
        const nextIndex = queueData.currentIndex % queueData.totalMatches;
        const newImage = queueData.matches[nextIndex];
        
        // Update queue data
        queueData.currentIndex = (nextIndex + 1) % queueData.totalMatches;
        window[queueKey] = queueData;
        
        ////console.log(`Cocktail Images: Match ${nextIndex + 1}/${queueData.totalMatches}: "${newImage.title}"`);

        swapImageWithFade(clickedImage, (img) => {
            img.src = trimImageDimensions(newImage.src);
            img.alt = newImage.alt;
            img.setAttribute('data-id', newImage.id);

            if (newImage.attachment_id) {
                img.setAttribute('data-attachment-id', newImage.attachment_id);
            }
            if (newImage.srcset) {
                img.setAttribute('srcset', trimSrcsetDimensions(newImage.srcset));
            }
            if (newImage.sizes) {
                img.setAttribute('sizes', newImage.sizes);
            }
            if (newImage.data_orig_file) {
                img.setAttribute('data-orig-file', trimImageDimensions(newImage.data_orig_file));
            }
            if (newImage.data_orig_size) {
                img.setAttribute('data-orig-size', newImage.data_orig_size);
            }
            if (newImage.data_image_title) {
                img.setAttribute('data-image-title', newImage.data_image_title);
            }
            if (newImage.data_image_caption) {
                img.setAttribute('data-image-caption', newImage.data_image_caption);
            }
            if (newImage.data_medium_file) {
                img.setAttribute('data-medium-file', trimImageDimensions(newImage.data_medium_file));
            }
            if (newImage.data_large_file) {
                img.setAttribute('data-large-file', trimImageDimensions(newImage.data_large_file));
            }
            if (newImage.attachment_id) {
                img.className = img.className.replace(/wp-image-\d+/, `wp-image-${newImage.attachment_id}`);
            }
        }, {
            onComplete: () => {
                if (typeof window.drinksPluginStyling?.ucPortraitLandscape === 'function') {
                    window.drinksPluginStyling.ucPortraitLandscape(clickedImage, figure);
                }
            }
        });
    }

    const IMAGE_FADE_MS = 900;
    const IMAGE_HOLD_MS = 1800;

    function fadeImageToTransparent(img, durationMs = IMAGE_FADE_MS) {
        img.style.transition = `opacity ${durationMs}ms ease-in-out`;
        img.style.opacity = '0';
        return new Promise(resolve => setTimeout(resolve, durationMs));
    }

    function fadeImageIn(img, durationMs = IMAGE_FADE_MS, onComplete) {
        img.style.opacity = '0';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                img.style.transition = `opacity ${durationMs}ms ease-in-out`;
                img.style.opacity = '1';
                setTimeout(() => {
                    img.style.transition = '';
                    if (typeof onComplete === 'function') {
                        onComplete();
                    }
                }, durationMs);
            });
        });
    }

    function swapImageWithFade(img, applySwap, options = {}) {
        const fadeMs = options.fadeMs ?? IMAGE_FADE_MS;
        const holdMs = options.holdMs ?? IMAGE_HOLD_MS;
        let finished = false;

        const finishSwap = () => {
            if (finished) {
                return;
            }
            finished = true;
            fadeImageIn(img, fadeMs, () => {
                if (typeof options.onComplete === 'function') {
                    options.onComplete(img);
                }
            });
        };

        return fadeImageToTransparent(img, fadeMs).then(() => {
            return new Promise(resolve => {
                setTimeout(() => {
                    applySwap(img);
                    img.onload = () => {
                        finishSwap();
                        resolve(img);
                    };
                    setTimeout(() => {
                        if (img.complete) {
                            finishSwap();
                            resolve(img);
                        }
                    }, 100);
                }, holdMs);
            });
        });
    }



    // Test function to iterate over all images in media library and check featured image status
    function ucOneTimePostsTest() {
        // console.log('Cocktail Images: === Starting ucOneTimePostsTest ===');
        
        // Check if we're on the media library page
        if (!window.location.href.includes('upload.php')) {
            // console.log('Cocktail Images: Not on media library page. Navigate to /wp-admin/upload.php to run this test.');
            return;
        }
        
        // Get all image elements in the media library
        const mediaImages = document.querySelectorAll('.attachment-preview img, .attachment img, .wp-attachment img');
        
        if (mediaImages.length === 0) {
            // console.log('Cocktail Images: No images found in media library. Make sure you have images loaded.');
            return;
        }
        
        // console.log(`Cocktail Images: Found ${mediaImages.length} images in media library`);
        
        // Process images in batches to avoid overwhelming the server
        const batchSize = 5;
        let processedCount = 0;
        
        function processBatch(startIndex) {
            const endIndex = Math.min(startIndex + batchSize, mediaImages.length);
            
            for (let i = startIndex; i < endIndex; i++) {
                const img = mediaImages[i];
                const imgSrc = img.src;
                const imgAlt = img.alt || 'No alt text';
                
                ////console.log(`Cocktail Images: \n--- Testing Image ${i + 1}/${mediaImages.length} ---`);
                ////console.log(`Cocktail Images: Src: ${imgSrc}`);
              //  //console.log(`Cocktail Images: Alt: ${imgAlt}`);
                
                // Test ucDoesImageHavePost function
                ucDoesImageHavePost(img)
                    .then(result => {
                        if (result) {
                           // //console.log(`Cocktail Images: ✅ Image ${i + 1}: Featured in post ID ${result}`);
                        } else {
                            ////console.log(`Cocktail Images: ❌ Image ${i + 1}: Not featured in any post`);
                        }
                    })
                    .catch(error => {
                        //console.error(`❌ Image ${i + 1}: Error - ${error.message}`);
                    });
                
                processedCount++;
            }
            
            // Process next batch if there are more images
            if (endIndex < mediaImages.length) {
                setTimeout(() => {
                    processBatch(endIndex);
                }, 1000); // Wait 1 second between batches
            } else {
                // console.log(`Cocktail Images: \n=== Test Complete ===`);
                // console.log(`Cocktail Images: Processed ${processedCount} images total`);
            }
        }
        
        // Start processing
        processBatch(0);
    }
    
    
    
    function ucSetupOneDrinkAllImages() {
        // console.log("ucSetupOneDrinkAllImages FIRED ");
        
        const imageBlocks = document.querySelectorAll('figure.wp-block-image img');
        
        imageBlocks.forEach(img => {
            // Initialize normalized figcaption for existing images
            const figure = img.closest('figure.wp-block-image');
            if (figure) {
                const figcaption = figure.querySelector('figcaption');
                if (figcaption) {
                    // Get the original caption if not already stored
                    if (!figcaption.getAttribute('data-original-caption')) {
                        figcaption.setAttribute('data-original-caption', figcaption.innerHTML);
                    }
                    
                    // Normalize the original caption content (which may contain category codes)
                    const originalCaption = figcaption.getAttribute('data-original-caption') || figcaption.innerHTML;
                    const normalizedTitle = ucNormalizeTitle(ucTitleSource(img, originalCaption), true);
                    
                    if (normalizedTitle) {
                        // Show only the normalized title, but keep original in data attribute for ? SEO
                        figcaption.innerHTML = normalizedTitle;
                    }
                }
            }

            // Initial image is randomized at PHP render_block; timed cycles only below

            // Set up automatic title matching for each image
            const randomDelay = Math.random() * (40000 - 5000) + 5000; // 5-40 seconds in milliseconds
            
            setTimeout(() => {
                ucTriggerOneDrinkAllImages(img);
                
                const setupRecurringTitleMatching = () => {
                    const nextDelay = Math.random() * (40000 - 5000) + 5000;
                    setTimeout(() => {
                        ucTriggerOneDrinkAllImages(img);
                        setupRecurringTitleMatching();
                    }, nextDelay);
                };
                
                setupRecurringTitleMatching();
            }, randomDelay);
        });
        
        //console.log(`Cocktail Images: Setup auto-title-matching for ${imageBlocks.length} image blocks`);
    }
    
 


    // Make functions globally available for backward compatibility - Necessary for dom_content_loaded from PHP due to echo'd into page directly. 
    window.ucRandomizeImage = ucRandomizeImage;
    window.ucSetupImageRandomization = ucSetupImageRandomization;
    window.ucOneDrinkAllImages = ucOneDrinkAllImages;
    window.ucSetupOneDrinkAllImages = ucSetupOneDrinkAllImages;
    window.ucOneTimePostsTest = ucOneTimePostsTest;

    window.cocktailImagesFade = {
        FADE_MS: IMAGE_FADE_MS,
        HOLD_MS: IMAGE_HOLD_MS,
        fadeImageToTransparent,
        fadeImageIn,
        swapImageWithFade
    };

    window.cocktailImagesMatching = {
        POPOUT_CYCLE_MS,
        cycleMatchedImage,
        startMatchedImageCycle,
        getImageMatchContext,
        fetchMatchingImages
    };

})(); 


