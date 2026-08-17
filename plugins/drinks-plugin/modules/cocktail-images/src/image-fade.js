/**
 * Opacity fade transition for image swaps (pop-out cycling, page blocks).
 */
(function() {
    'use strict';

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

    window.cocktailImagesFade = {
        FADE_MS: IMAGE_FADE_MS,
        HOLD_MS: IMAGE_HOLD_MS,
        fadeImageToTransparent,
        fadeImageIn,
        swapImageWithFade
    };
})();
