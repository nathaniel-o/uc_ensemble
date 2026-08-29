/**
 * Shared image URL and title helpers for cocktail-images + drinks-plugin.
 * URL trimming mirrors drinks_trim_image_dimensions() in includes/drink-image-matching.php.
 * ucNormalizeTitle is used for client display / AJAX keys — server matching uses drinks_extract_match_words().
 */
(function() {
    'use strict';

    function trimImageDimensions(url) {
        if (!url) {
            return url;
        }
        return url.replace(/-\d+x\d+\.(jpg|jpeg|png|webp)$/i, '.$1');
    }

    function trimSrcsetDimensions(srcset) {
        if (!srcset) {
            return srcset;
        }
        return srcset.split(',')
            .map((entry) => entry.trim())
            .filter(Boolean)
            .map((entry) => {
                const [url, ...rest] = entry.split(/\s+/);
                const trimmedUrl = trimImageDimensions(url);
                return rest.length ? `${trimmedUrl} ${rest.join(' ')}` : trimmedUrl;
            })
            .join(', ');
    }

    /** Keep srcset candidates that belong to this file, not sibling drink photos injected into srcset. */
    function srcsetForAttachment(srcset, src) {
        const trimmed = trimSrcsetDimensions(srcset);
        if (!trimmed) {
            return '';
        }
        if (!src) {
            return trimmed;
        }
        const stem = src.split('/').pop()
            .replace(/-\d+x\d+(?=\.[^.]+$)/, '')
            .replace(/\.[^.]+$/, '');
        if (!stem) {
            return trimmed;
        }
        const kept = trimmed.split(',').map((entry) => entry.trim()).filter((entry) => {
            const url = entry.split(/\s+/)[0] || '';
            return url.includes(stem);
        });
        return kept.join(', ');
    }

    function normalizeImageUrl(url) {
        if (!url) {
            return '';
        }
        return trimImageDimensions(url).split('?')[0];
    }

    function ucTitleSource(img, text) {
        const value = (text || '').trim();
        if (value) {
            return value;
        }
        const stem = (img.src || '').split('/').pop().replace(/\.[^/.]+$/, '').replace(/-\d+x\d+$/, '');
        return stem;
    }

    function ucNormalizeTitle(title, preserveCapitalization = false) {
        let baseTitle = title
            .split(':')[0]
            .replace(/^T2-/, '')
            .replace(/[-_]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        baseTitle = baseTitle.replace(/(AU|SO|SU|SP|FP|EV|RO|WI)$/, '');
        baseTitle = baseTitle
            .split(' ')
            .filter(word => word.length >= 3)
            .join(' ');

        if (!preserveCapitalization) {
            baseTitle = baseTitle.toLowerCase();
        }

        return baseTitle;
    }

    function resolveImageAttachmentId(img) {
        return img.getAttribute('data-attachment-id')
            || img.getAttribute('data-id')
            || (img.className.match(/wp-image-(\d+)/) || [])[1]
            || '';
    }

    function ucIsBannerImage(img) {
        const title = img.getAttribute('data-image-title') || img.getAttribute('alt') || '';
        return title.toLowerCase().includes('banner');
    }

    window.cocktailImagesUtils = {
        trimImageDimensions,
        trimSrcsetDimensions,
        srcsetForAttachment,
        normalizeImageUrl,
        ucTitleSource,
        ucNormalizeTitle,
        resolveImageAttachmentId,
        ucIsBannerImage
    };
})();
