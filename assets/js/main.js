/**
 * main.js — general site interactions (non-chatbot)
 * - AOS (Animate On Scroll) initialization
 * - Mobile navigation toggle
 * - Cart quantity +/- buttons (progressive enhancement over the form)
 */

document.addEventListener('DOMContentLoaded', () => {

  // ---- Scroll reveal animations ----
  if (window.AOS) {
    AOS.init({
      duration: 700,
      easing: 'ease-out-cubic',
      once: true,
      offset: 60,
    });
  }

  // ---- Mobile hamburger menu ----
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
    // close menu when a link is tapped
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => navLinks.classList.remove('open'));
    });
  }

  // ---- Cart quantity +/- buttons ----
  document.querySelectorAll('.qty-form').forEach(form => {
    const minus = form.querySelector('.qty-minus');
    const plus  = form.querySelector('.qty-plus');
    const input = form.querySelector('.qty-input');

    if (minus) {
      minus.addEventListener('click', () => {
        input.value = Math.max(1, parseInt(input.value || '1', 10) - 1);
        form.requestSubmit();
      });
    }
    if (plus) {
      plus.addEventListener('click', () => {
        input.value = parseInt(input.value || '1', 10) + 1;
        form.requestSubmit();
      });
    }
  });

  // ---- Navbar subtle shadow on scroll ----
  const navbar = document.getElementById('navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.style.boxShadow = window.scrollY > 20
        ? '0 8px 24px rgba(0,0,0,0.35)'
        : 'none';
    });
  }
});
