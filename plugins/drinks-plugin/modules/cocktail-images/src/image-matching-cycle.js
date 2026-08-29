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
        const ownSrcset = newImage.srcset && u.srcsetForAttachment
            ? u.srcsetForAttachment(newImage.srcset, img.src)
            : '';
        if (ownSrcset) {
            img.setAttribute('srcset', ownSrcset);
        } else {
            img.removeAttribute('srcset');
            img.removeAttribute('sizes');
        }
        if (ownSrcset && newImage.sizes) {
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

    function getQueueStore() {
        if (!window.cocktailImageMatchQueues || typeof window.cocktailImageMatchQueues !== 'object') {
            window.cocktailImageMatchQueues = Object.create(null);
        }
        return window.cocktailImageMatchQueues;
    }

    function matchIdsEqual(match, imageId) {
        const id = String(imageId || '');
        if (!id) {
            return false;
        }
        return String(match.id) === id || String(match.attachment_id) === id;
    }

    function findQueueForImage(img, options = {}) {
        const u = utils();
        const currentImageId = String(u.resolveImageAttachmentId(img) || '');
        const currentAlt = img.getAttribute('alt') || '';
        const currentTitle = img.getAttribute('data-image-title') || '';
        const baseTitle = options.baseTitle || u.ucNormalizeTitle(currentTitle || u.ucTitleSource(img, currentAlt));
        const store = getQueueStore();

        if (options.queueKey && store[options.queueKey]?.matches?.length) {
            return {
                currentImageId,
                baseTitle: store[options.queueKey].baseTitle || baseTitle,
                queueKey: options.queueKey
            };
        }

        if (baseTitle && store[baseTitle]?.matches?.length) {
            return { currentImageId, baseTitle, queueKey: baseTitle };
        }

        if (currentImageId) {
            for (const key of Object.keys(store)) {
                const data = store[key];
                if (data?.matches?.some((match) => matchIdsEqual(match, currentImageId))) {
                    return { currentImageId, baseTitle: data.baseTitle || key, queueKey: key };
                }
            }
        }

        return { currentImageId, baseTitle, queueKey: options.queueKey || baseTitle || `queue_${currentImageId}` };
    }

    function getImageMatchContext(img, options = {}) {
        return findQueueForImage(img, options);
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

    function pickNextMatch(queueData, currentImageId) {
        const matches = queueData.matches;
        const total = matches.length;
        if (total === 0) {
            return null;
        }

        if (total === 1) {
            queueData.currentIndex = 0;
            return matches[0];
        }

        for (let i = 0; i < total; i++) {
            const idx = queueData.currentIndex % total;
            const candidate = matches[idx];
            queueData.currentIndex = (idx + 1) % total;
            if (!matchIdsEqual(candidate, currentImageId)) {
                return candidate;
            }
        }

        return matches[queueData.currentIndex % total];
    }

    function cycleToNextMatch(clickedImage, figure, queueData, queueKey, options = {}) {
        const currentImageId = utils().resolveImageAttachmentId(clickedImage);
        const newImage = pickNextMatch(queueData, currentImageId);
        if (!newImage) {
            return;
        }

        getQueueStore()[queueKey] = queueData;

        fade().swapImageWithFade(clickedImage, (img) => {
            applyMatchImageDataToImg(img, newImage);
        }, {
            fadeMs: options.fadeMs,
            holdMs: options.holdMs,
            onComplete: () => {
                if (typeof window.drinksPluginStyling?.syncImageAspectBox === 'function') {
                    window.drinksPluginStyling.syncImageAspectBox(clickedImage);
                }
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
        const { currentImageId, baseTitle, queueKey } = findQueueForImage(img, options);
        const store = getQueueStore();
        let queueData = store[queueKey] || { currentIndex: 0, totalMatches: 0, baseTitle: '', matches: [] };
        const needsNewSearch = queueData.matches.length === 0;

        if (needsNewSearch) {
            queueData = { currentIndex: 0, totalMatches: 0, baseTitle, matches: [] };

            return fetchMatchingImages(currentImageId, baseTitle)
                .then(data => {
                    if (!data.success || !data.data?.all_matches?.length) {
                        return false;
                    }

                    queueData.matches = data.data.all_matches;
                    queueData.totalMatches = data.data.total_matches;
                    queueData.baseTitle = baseTitle;
                    store[queueKey] = queueData;
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
