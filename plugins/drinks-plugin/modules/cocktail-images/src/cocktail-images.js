/**
 * Cocktail Images — legacy randomize, admin helpers, and global exports.
 * Shared fade/cycle/utils live in image-*.js (enqueued before this file).
 *
 * Lightbox UI is handled by drinks-plugin/src/frontend.js — not lightbox.js.
 */
(function() {
    'use strict';

    const utils = () => window.cocktailImagesUtils;
    const matching = () => window.cocktailImagesMatching;

    function applyPortraitLandscape(img, figure) {
        if (typeof window.drinksPluginStyling?.ucPortraitLandscape === 'function') {
            window.drinksPluginStyling.ucPortraitLandscape(img, figure);
        }
    }

    function ucRandomizeImage(e) {
        e.preventDefault();

        const clickedImage = e.target;
        if (clickedImage.tagName !== 'IMG') {
            return;
        }

        const figure = clickedImage.closest('figure.wp-block-image');
        if (!figure) {
            return;
        }

        const currentImageId = clickedImage.getAttribute('data-id') || clickedImage.getAttribute('data-attachment-id');
        const requestBody = `action=randomize_image&current_id=${encodeURIComponent(currentImageId)}&nonce=${cocktailImagesAjax.nonce}`;

        fetch(cocktailImagesAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: requestBody
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.data.image) {
                return;
            }

            const newImage = data.data.image;
            matching().applyMatchImageDataToImg(clickedImage, newImage);

            const figcaption = figure.querySelector('figcaption');
            if (figcaption) {
                ucNormalizeDrinkCaption(figcaption, clickedImage, newImage.data_image_caption || newImage.title || '');
            }

            clickedImage.onload = () => applyPortraitLandscape(clickedImage, figure);

            setTimeout(() => {
                if (clickedImage.complete) {
                    applyPortraitLandscape(clickedImage, figure);
                }
            }, 100);
        })
        .catch(() => {});
    }

    function ucDoesImageHavePost(img) {
        const imgClasses = img.className.split(' ');
        const imgIdClass = imgClasses.find(cls => cls.startsWith('wp-image-'));
        const imgId = imgIdClass ? imgIdClass.replace('wp-image-', '') : null;

        if (!imgId) {
            return Promise.resolve(false);
        }

        const imageTitle = img.alt || img.getAttribute('data-image-title') || img.src.split('/').pop().replace(/\.[^/.]+$/, '');
        const requestData = {
            action: 'check_featured_image',
            nonce: cocktailImagesAjax.nonce,
            image_title: imageTitle
        };

        return fetch(cocktailImagesAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(requestData)
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status} - Server returned: ${text.substring(0, 200)}`);
                });
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(() => {
                    throw new Error('Response is not JSON');
                });
            }
            return response.json();
        })
        .then(response => {
            if (response.success && response.data?.post_id) {
                return response.data.post_id;
            }
            return false;
        })
        .catch(() => false);
    }

    function ucSetupImageRandomization() {
        const imageBlocks = document.querySelectorAll('figure.wp-block-image img');

        imageBlocks.forEach(img => {
            const randomDelay = Math.random() * (90000 - 10000) + 10000;

            setTimeout(() => {
                ucRandomizeImage({ target: img, preventDefault: () => {} });

                const setupRecurringRandomization = () => {
                    const nextDelay = Math.random() * (90000 - 10000) + 10000;
                    setTimeout(() => {
                        ucRandomizeImage({ target: img, preventDefault: () => {} });
                        setupRecurringRandomization();
                    }, nextDelay);
                };

                setupRecurringRandomization();
            }, randomDelay);
        });
    }

    function ucTriggerOneDrinkAllImages(img) {
        matching().cycleMatchedImage(img, { figure: img.closest('figure.wp-block-image') });
    }

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

        matching().cycleMatchedImage(clickedImage, { figure });
    }

    function ucNormalizeDrinkCaption(figcaption, img, titleOverride = '') {
        if (!figcaption || !img) {
            return;
        }

        if (!figcaption.getAttribute('data-original-caption')) {
            figcaption.setAttribute('data-original-caption', figcaption.innerHTML);
        }

        const originalCaption = figcaption.getAttribute('data-original-caption') || figcaption.innerHTML;
        const source = titleOverride || utils().ucTitleSource(img, originalCaption);
        const normalizedTitle = utils().ucNormalizeTitle(source, true);

        if (normalizedTitle) {
            figcaption.innerHTML = normalizedTitle;
        }
    }

    function ucNormalizeDrinkCaptions(root) {
        const scope = (root && root.querySelectorAll) ? root : document;
        scope.querySelectorAll('figure figcaption').forEach((figcaption) => {
            const figure = figcaption.closest('figure');
            const img = figure ? figure.querySelector('img') : null;
            if (img) {
                ucNormalizeDrinkCaption(figcaption, img);
            }
        });
    }

    function ucSetupOneDrinkAllImages() {
        const imageBlocks = document.querySelectorAll('figure.wp-block-image img');

        imageBlocks.forEach(img => {
            const figure = img.closest('figure.wp-block-image');
            if (figure) {
                const figcaption = figure.querySelector('figcaption');
                if (figcaption) {
                    ucNormalizeDrinkCaption(figcaption, img);
                }
            }

            const randomDelay = Math.random() * (40000 - 5000) + 5000;

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
    }

    function ucOneTimePostsTest() {
        if (!window.location.href.includes('upload.php')) {
            return;
        }

        const mediaImages = document.querySelectorAll('.attachment-preview img, .attachment img, .wp-attachment img');
        if (mediaImages.length === 0) {
            return;
        }

        const batchSize = 5;

        function processBatch(startIndex) {
            const endIndex = Math.min(startIndex + batchSize, mediaImages.length);

            for (let i = startIndex; i < endIndex; i++) {
                ucDoesImageHavePost(mediaImages[i]);
            }

            if (endIndex < mediaImages.length) {
                setTimeout(() => processBatch(endIndex), 1000);
            }
        }

        processBatch(0);
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.href.includes('upload.php')) {
            return;
        }
        // Page-level cycling disabled; pop-out uses startMatchedImageCycle in drinks-plugin.
        ucNormalizeDrinkCaptions();
    });

    window.ucRandomizeImage = ucRandomizeImage;
    window.ucNormalizeDrinkCaption = ucNormalizeDrinkCaption;
    window.ucNormalizeDrinkCaptions = ucNormalizeDrinkCaptions;
    window.ucSetupImageRandomization = ucSetupImageRandomization;
    window.ucOneDrinkAllImages = ucOneDrinkAllImages;
    window.ucSetupOneDrinkAllImages = ucSetupOneDrinkAllImages;
    window.ucOneTimePostsTest = ucOneTimePostsTest;
    window.ucNormalizeTitle = utils().ucNormalizeTitle;
})();
