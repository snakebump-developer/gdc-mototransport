// ===== ACTIVE NAV LINK ON SCROLL =====
(function () {
  'use strict';

  const sections = ['come-funziona', 'vantaggi', 'gallery', 'recensioni', 'chi-siamo'];
  const navLinks = document.querySelectorAll('.nav-menu a[data-section]');
  if (!navLinks.length) return;

  function setActive(id) {
    navLinks.forEach((a) => {
      a.classList.toggle('nav-active', a.dataset.section === id);
    });
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) setActive(entry.target.id);
      });
    },
    { rootMargin: '-40% 0px -55% 0px', threshold: 0 }
  );

  sections.forEach((id) => {
    const el = document.getElementById(id);
    if (el) observer.observe(el);
  });

  // Highlight on direct click
  navLinks.forEach((a) => {
    a.addEventListener('click', function () {
      setActive(this.dataset.section);
    });
  });
})();

// ===== DROPDOWN MENU =====
document.addEventListener('DOMContentLoaded', function () {
  const userButton = document.getElementById('userButton');
  const dropdownMenu = document.getElementById('dropdownMenu');

  if (userButton && dropdownMenu) {
    userButton.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
      if (!userButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
      }
    });
  }

  // ===== MOBILE MENU =====
  const hamburgerMenu = document.getElementById('hamburgerMenu');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
  const mobileMenuClose = document.getElementById('mobileMenuClose');

  if (hamburgerMenu && mobileMenu && mobileMenuOverlay) {
    hamburgerMenu.addEventListener('click', function () {
      hamburgerMenu.classList.toggle('active');
      mobileMenu.classList.toggle('active');
      mobileMenuOverlay.classList.toggle('active');
      document.body.style.overflow = mobileMenu.classList.contains('active')
        ? 'hidden'
        : '';
    });

    if (mobileMenuClose) {
      mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    mobileMenuOverlay.addEventListener('click', closeMobileMenu);

    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
    mobileMenuLinks.forEach((link) => {
      link.addEventListener('click', closeMobileMenu);
    });

    function closeMobileMenu() {
      hamburgerMenu.classList.remove('active');
      mobileMenu.classList.remove('active');
      mobileMenuOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }

    window.addEventListener('resize', function () {
      if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
        closeMobileMenu();
      }
    });
  }
});

// ===== SMOOTH SCROLL =====
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener('click', function (e) {
    const targetId = this.getAttribute('href');
    if (targetId !== '#' && targetId.startsWith('#')) {
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        targetElement.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        });
      }
    }
  });
});
