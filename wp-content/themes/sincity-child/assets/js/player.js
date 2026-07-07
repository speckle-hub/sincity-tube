/**
 * SinCity Player JS — lazy-load embeds, interaction tracking, UX enhancements
 * ~3KB gzipped, no dependencies.
 */
(function () {
  'use strict';

  // ─── LAZY LOAD EMBEDS ──────────────────────────────────
  function lazyLoadEmbeds() {
    document.querySelectorAll('.sc-player-container iframe[loading="lazy"]').forEach(function (iframe) {
      var src = iframe.getAttribute('src');
      if (src && !iframe.dataset.loaded) {
        // Replace PH embed viewkey trick for autoplay
        if (src.indexOf('pornhub.com/embed/') !== -1 && src.indexOf('autoplay') === -1) {
          src += (src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1';
        }
        // Clean tracking params from XVideos
        if (src.indexOf('xvideos.com/embedframe/') !== -1) {
          src = src.replace(/[?&](?:from_ad|utm_)[^&]+/g, '');
        }
        iframe.setAttribute('src', src);
        iframe.dataset.loaded = '1';
      }
    });
  }

  // Initial load
  document.addEventListener('DOMContentLoaded', lazyLoadEmbeds);

  // Re-observe if content changes (e.g., AJAX navigation)
  if (window.MutationObserver) {
    var observer = new MutationObserver(function () {
      lazyLoadEmbeds();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  // ─── VIEW TRACKING (clean) ─────────────────────────────
  // Server handles view tracking via cookie check on page load.
  // This fires an extra beacon to ensure count is registered
  // even if user navigates away before the PHP hook fires.
  var viewTracked = false;
  function trackView() {
    if (viewTracked) return;
    var body = document.body;
    if (body.classList.contains('single-sc_video')) {
      var pid = body.dataset.postId;
      if (pid && !navigator.cookieEnabled) {
        // Cookie-less fallback: ping an endpoint
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/wp-json/sincity/v1/track-view/' + pid, true);
        xhr.send();
      }
      viewTracked = true;
    }
  }

  // Track view on visibility (don't count if user never sees it)
  if ('IntersectionObserver' in window) {
    var hero = document.querySelector('.sc-player-wrapper');
    if (hero) {
      var viewObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            trackView();
            viewObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.3 });
      viewObserver.observe(hero);
    } else {
      trackView();
    }
  } else {
    trackView();
  }

  // ─── EMBED ERROR HANDLING ──────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sc-player-container iframe').forEach(function (iframe) {
      iframe.addEventListener('error', function () {
        var wrapper = this.closest('.sc-player-wrapper');
        if (wrapper) {
          wrapper.innerHTML =
            '<div class="sc-error">Video source unavailable. Try refreshing the page.</div>';
        }
      });
    });
  });

  // ─── KEYBOARD NAV ──────────────────────────────────────
  document.addEventListener('keydown', function (e) {
    // Escape closes modals / age gate (if any)
    if (e.key === 'Escape') {
      var gate = document.querySelector('.sc-age-gate');
      // Age gate is server-side rendered; this is for future overlay use.
    }
  });

})();
