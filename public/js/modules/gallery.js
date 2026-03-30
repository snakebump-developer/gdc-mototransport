// ===== GALLERY SLIDER =====
(function () {
  'use strict';

  const slider = document.getElementById('gallerySlider');
  const prevBtn = document.getElementById('galleryPrev');
  const nextBtn = document.getElementById('galleryNext');

  if (!slider || !prevBtn || !nextBtn) return;

  let autoSlideTimer = null;
  const AUTO_INTERVAL = 3500;

  function getScrollStep() {
    const item = slider.querySelector('.gallery__item');
    if (!item) return 250;
    const sliderStyle = window.getComputedStyle(slider);
    const gap = parseFloat(sliderStyle.columnGap || sliderStyle.gap) || 24;
    return item.offsetWidth + gap;
  }

  function slideNext() {
    const maxScroll = slider.scrollWidth - slider.clientWidth;
    const target = slider.scrollLeft + getScrollStep();
    if (target >= maxScroll - 1) {
      slider.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
      slider.scrollTo({ left: target, behavior: 'smooth' });
    }
  }

  function slidePrev() {
    const target = slider.scrollLeft - getScrollStep();
    if (target <= 0) {
      const maxScroll = slider.scrollWidth - slider.clientWidth;
      slider.scrollTo({ left: maxScroll, behavior: 'smooth' });
    } else {
      slider.scrollTo({ left: target, behavior: 'smooth' });
    }
  }

  function startAuto() {
    stopAuto();
    autoSlideTimer = setInterval(slideNext, AUTO_INTERVAL);
  }

  function stopAuto() {
    clearInterval(autoSlideTimer);
  }

  nextBtn.addEventListener('click', () => { slideNext(); startAuto(); });
  prevBtn.addEventListener('click', () => { slidePrev(); startAuto(); });

  slider.addEventListener('mouseenter', stopAuto);
  slider.addEventListener('mouseleave', startAuto);

  slider.addEventListener('touchstart', stopAuto, { passive: true });
  slider.addEventListener('touchend', startAuto, { passive: true });

  startAuto();
})();
