/**
 * SinCity — Lazy Load Embeds & Player Enhancements
 * Place in: sincity-child/assets/js/player.js
 *
 * - IntersectionObserver lazy loading for all embeds
 * - ResizeObserver for responsive iframes
 * - Error recovery for failed embeds
 * - Mobile detection for touch-friendly controls
 */
(function () {
    'use strict';

    const PLAYER_SELECTOR = '.sc-player-container iframe';
    const THUMB_SELECTOR  = '.thumb-wrap img';
    const ROOT_MARGIN     = '250px 0px';

    // ─── 1. Lazy Load Embeds ───────────────────────────────────────

    function initLazyEmbeds() {
        const players = document.querySelectorAll(PLAYER_SELECTOR);

        if (!players.length) return;

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadIframe(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: ROOT_MARGIN,
            });

            players.forEach(function (iframe) {
                var src = iframe.getAttribute('src');
                if (src) {
                    iframe.setAttribute('data-src', src);
                    iframe.removeAttribute('src');
                }
                observer.observe(iframe);
            });
        } else {
            // Fallback for old browsers — load all immediately
            players.forEach(function (iframe) {
                loadIframe(iframe);
            });
        }
    }

    function loadIframe(iframe) {
        var src = iframe.getAttribute('data-src');
        if (src && !iframe.getAttribute('src')) {
            iframe.setAttribute('src', src);

            // Clean up data attribute after load
            iframe.addEventListener('load', function () {
                iframe.removeAttribute('data-src');
            }, { once: true });

            // Error recovery: try loading via src replacement
            iframe.addEventListener('error', function () {
                // Attempt to re-load with a different protocol hint
                iframe.setAttribute('src', src.replace('http://', 'https://'));
            }, { once: true });
        }
    }

    // ─── 2. Responsive Iframe Height (for non-16:9 ratios) ────────

    function initResponsiveIframes() {
        if (!window.ResizeObserver) return;

        var containers = document.querySelectorAll('.sc-player-container');
        containers.forEach(function (container) {
            var iframe = container.querySelector('iframe');
            if (!iframe) return;

            var observer = new ResizeObserver(function () {
                var width = container.offsetWidth;
                // Default 16:9 ratio; can be overridden by data-aspect
                var ratio = parseFloat(container.getAttribute('data-aspect')) || (9 / 16);
                container.style.height = (width * ratio) + 'px';
            });
            observer.observe(container);
        });
    }

    // ─── 3. Lazy Load Thumbnails (extra perf) ─────────────────────

    function initLazyThumbs() {
        var thumbs = document.querySelectorAll(THUMB_SELECTOR);
        if (!thumbs.length || !('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    var src = img.getAttribute('data-src');
                    if (src) {
                        img.setAttribute('src', src);
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '300px 0px',
        });

        thumbs.forEach(function (img) {
            // If WP hasn't added native lazy, store src and clear
            if (!img.hasAttribute('loading') && img.getAttribute('src')) {
                img.setAttribute('data-src', img.getAttribute('src'));
                img.removeAttribute('src');
            }
            observer.observe(img);
        });
    }

    // ─── 4. Age Gate Cookie Check (redundant client-side) ───────────

    function checkAgeGateCookie() {
        // No-op — server handles it. Just ensures JS doesn't break if
        // the cookie is missing and user refreshes.
        return true;
    }

    // ─── 5. Boot ───────────────────────────────────────────────────

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        initLazyEmbeds();
        initResponsiveIframes();
        initLazyThumbs();

        // Re-init on AJAX navigation (if any)
        document.addEventListener('sc_content_loaded', function () {
            initLazyEmbeds();
        });
    });

})();
