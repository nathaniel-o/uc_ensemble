/**
 * Title-matched image cycling — shared by pop-out lightboxes and core/image blocks.
 * AJAX: find_matching_image (cocktail-images.php).
 */
(function() {
    'use strict';

    const utils = () => window.cocktailImagesUtils;
    const fade = () => window.cocktailImagesFade;

    const SHUFFLE_FADE_MS = 300;
    const SHUFFLE_HOLD_MS = 600;
    const POPOUT_CYCLE_MS = 12000;

    function applyMatchImageDataToImg(img, newImage) {
        const u = utils();
        img.src = u.trimImageDimensions(newImage.src);
        img.alt = newImage.alt;
        img.setAttribute('data-id', newImage.id);

        if (newImage.attachment_id) {
            img.setAttribute('data-attachment-id', newImage.attachment_id);
        }
        if (newImage.srcset) {
            img.setAttribute('srcset', u.trimSrcsetDimensions(newImage.srcset));
        }
        if (newImage.sizes) {
            img.setAttribute('sizes', newImage.sizes);
        }
        if (newImage.data_orig_file) {
            img.setAttribute('data-orig-file', u.trimImageDimensions(newImage.data_orig_file));
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
            img.setAttribute('data-medium-file', u.trimImageDimensions(newImage.data_medium_file));
        }
        if (newImage.data_large_file) {
            img.setAttribute('data-large-file', u.trimImageDimensions(newImage.data_large_file));
        }
        if (newImage.attachment_id) {
            img.className = img.className.replace(/wp-image-\d+/, `wp-image-${newImage.attachment_id}`);
        }
    }

    function resolveImageFigure(img, options = {}) {
        return options.figure || img.closest('figure') || img.parentElement;
    }

    function getImageMatchContext(img, options = {}) {
        const u = utils();
        const currentImageId = u.resolveImageAttachmentId(img);
        const currentAlt = img.getAttribute('alt') || '';
        const currentTitle = img.getAttribute('data-image-title') || '';
        const baseTitle = options.baseTitle || u.ucNormalizeTitle(currentTitle || u.ucTitleSource(img, currentAlt));
        const queueKey = options.queueKey || `queue_${currentImageId}`;

        return { currentImageId, baseTitle, queueKey };
    }

    function fetchMatchingImages(currentImageId, baseTitle) {
        const requestBody = `action=find_matching_image&current_id=${encodeURIComponent(currentImageId)}&base_title=${encodeURIComponent(baseTitle)}&is_new_search=true&nonce=${cocktailImagesAjax.nonce}`;

        return fetch(cocktailImagesAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: requestBody
        }).then(response => response.json());
    }

    function cycleToNextMatch(clickedImage, figure, queueData, queueKey, options = {}) {
        if (queueData.matches.length === 0) {
            return;
        }

        const nextIndex = queueData.currentIndex % queueData.totalMatches;
        const newImage = queueData.matches[nextIndex];

        queueData.currentIndex = (nextIndex + 1) % queueData.totalMatches;
        window[queueKey] = queueData;

        fade().swapImageWithFade(clickedImage, (img) => {
            applyMatchImageDataToImg(img, newImage);
        }, {
            fadeMs: options.fadeMs,
            holdMs: options.holdMs,
            onComplete: () => {
                if (typeof window.drinksPluginStyling?.ucPortraitLandscape === 'function') {
                    window.drinksPluginStyling.ucPortraitLandscape(clickedImage, figure);
                }
            }
        });
    }

    function cycleMatchedImage(img, options = {}) {
        const u = utils();
        if (!img || img.tagName !== 'IMG' || u.ucIsBannerImage(img)) {
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
                    cycleToNextMatch(img, figure, queueData, queueKey, options);
                    return true;
                })
                .catch(() => false);
        }

        cycleToNextMatch(img, figure, queueData, queueKey, options);
        return Promise.resolve(true);
    }

    function startMatchedImageCycle(img, options = {}) {
        const intervalMs = options.intervalMs ?? POPOUT_CYCLE_MS;
        const getImg = typeof options.getImg === 'function' ? options.getImg : () => img;
        const guard = options.guard || { busy: false };
        let stopped = false;
        let timerId = null;

        const tick = async () => {
            if (stopped || guard.busy) {
                return;
            }

            const activeImg = getImg();
            if (!activeImg || !document.body.contains(activeImg)) {
                stop();
                return;
            }

            guard.busy = true;
            try {
                await cycleMatchedImage(activeImg, {
                    ...options,
                    figure: activeImg.closest('figure') || options.figure
                });
            } finally {
                guard.busy = false;
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

        stop.guard = guard;
        return stop;
    }

    window.cocktailImagesMatching = {
        POPOUT_CYCLE_MS,
        SHUFFLE_FADE_MS,
        SHUFFLE_HOLD_MS,
        applyMatchImageDataToImg,
        cycleMatchedImage,
        startMatchedImageCycle,
        getImageMatchContext,
        fetchMatchingImages
    };
})();
