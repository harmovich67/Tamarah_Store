/**
 * Tamrna Foundation - storefront interactivity
 *  - hero slider (CMS banners) with entrance animations
 *  - hero parallax
 *  - scroll reveal animations
 */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.documentElement.classList.add(reduceMotion ? 'no-motion' : 'js-reveal');

  // ---------------------------------------------------------------------------
  // Hero slider
  // ---------------------------------------------------------------------------
  function initHeroSlider() {
    var slider = document.getElementById('tumurna-hero-slider');
    if (!slider) return;

    var slides = slider.querySelectorAll('.tumurna-hero-slide');
    var dots = slider.querySelectorAll('.tumurna-hero-dot');
    var prevBtn = slider.querySelector('.tumurna-hero-prev');
    var nextBtn = slider.querySelector('.tumurna-hero-next');
    var current = 0;
    var timer = null;
    var rtl = document.documentElement.dir === 'rtl';

    function show(index) {
      if (!slides.length) return;
      var next = (index + slides.length) % slides.length;
      if (next === current && slides[current].classList.contains('is-active')) return;

      var leaving = slides[current];
      leaving.classList.add('is-leaving');
      leaving.classList.remove('is-active');
      setTimeout(function () { leaving.classList.remove('is-leaving'); }, 1000);

      current = next;
      slides[current].classList.add('is-active');

      dots.forEach(function (dot, i) { dot.classList.toggle('is-active', i === current); });
    }

    function start() {
      stop();
      if (slides.length > 1 && !reduceMotion) {
        timer = setInterval(function () { show(current + 1); }, 6500);
      }
    }
    function stop() { if (timer) clearInterval(timer); timer = null; }

    if (prevBtn) prevBtn.addEventListener('click', function () { show(current - 1); start(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { show(current + 1); start(); });
    dots.forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); start(); }); });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);

    // Swipe support
    var startX = null;
    slider.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    slider.addEventListener('touchend', function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      startX = null;
      if (Math.abs(dx) < 50) return;
      var forward = rtl ? dx > 0 : dx < 0;
      show(current + (forward ? 1 : -1));
      start();
    }, { passive: true });

    start();

    // Parallax on the hero imagery while scrolling
    if (!reduceMotion) {
      var stage = slider.querySelector('.home-hero-slides');
      var ticking = false;
      var update = function () {
        ticking = false;
        var h = slider.offsetHeight || 1;
        var progress = Math.min(Math.max(window.scrollY / h, 0), 1);
        if (stage) stage.style.transform = 'translate3d(0,' + (progress * 4).toFixed(2) + '%,0)';
      };
      window.addEventListener('scroll', function () {
        if (!ticking) { ticking = true; requestAnimationFrame(update); }
      }, { passive: true });
      update();
    }
  }

  // ---------------------------------------------------------------------------
  // Scroll reveal (equivalent of the foundation's GSAP fade-up reveals)
  // ---------------------------------------------------------------------------
  var REVEALS = [
    ['.benefit-strip li', 22, 0.08],
    ['.section-title', 26, 0],
    ['.promo-banner .promo-copy > *', 22, 0.09],
    ['.category-collection .category-card', 30, 0.09],
    ['.home-section-action', 18, 0],
    ['.home-product-grid .product-card', 34, 0.1],
    ['.home-feature-card', 26, 0.1],
    ['.home-review-card', 26, 0.1],
    ['.home-newsletter-box', 24, 0],
    ['.home-custom-section', 24, 0],
    ['.product-grid .product-card', 28, 0.07],
    ['.product-related-heading', 18, 0],
    ['.category-results-heading', 18, 0],
    ['.category-filter-panel', 24, 0],
    ['.commerce-heading', 20, 0],
    ['.cart-page-items', 22, 0],
    ['.commerce-summary', 22, 0],
    ['.checkout-card', 24, 0.08],
    ['.contact-channels, .contact-form', 28, 0.1],
    ['.about-story-copy', 24, 0],
    ['.about-story-media', 24, 0],
    ['.about-stats article', 20, 0.08],
    ['.about-values-grid article', 24, 0.1],
    ['.about-farms article', 24, 0],
    ['.about-sustainability article', 24, 0],
    ['.about-sustainability-media', 24, 0],
    ['.about-gift article > *', 20, 0.08],
    ['.gift-purchase-card', 24, 0],
    ['.gift-preview-col', 24, 0],
    ['.gift-benefits > div', 20, 0.08],
    ['.legal-hero, .legal-sections section', 22, 0.08],
    ['.account-card, .account-summary-card', 22, 0.07],
    ['.account-order-card', 22, 0.06],
    ['.account-address-grid article', 22, 0.06],
    ['.account-gate', 24, 0],
    ['.product-detail-info > *', 16, 0.05],
    ['.product-gallery', 24, 0],
    ['.catalog-empty', 20, 0],
  ];

  function initReveal() {
    if (reduceMotion) return;
    var els = [];
    REVEALS.forEach(function (rule) {
      var list = document.querySelectorAll(rule[0]);
      list.forEach(function (el, i) {
        if (el.classList.contains('reveal')) return;
        el.classList.add('reveal');
        el.style.setProperty('--reveal-y', rule[1] + 'px');
        el.style.setProperty('--reveal-delay', (Math.min(i, 8) * rule[2]).toFixed(2) + 's');
        els.push(el);
      });
    });

    if (!('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-revealed'); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed');
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    els.forEach(function (el) { io.observe(el); });
  }

  function boot() {
    initHeroSlider();
    initReveal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
