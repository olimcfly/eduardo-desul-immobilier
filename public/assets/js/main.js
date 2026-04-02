/* ============================================================
   MAIN JS — Eduardo Desul Immobilier
   ============================================================ */

'use strict';

// ── Header scroll effect ──────────────────────────────────────
const header = document.getElementById('site-header');
if (header) {
  const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 50);
  window.addEventListener('scroll', onScroll, { passive: true });
}

// ── Mobile nav ────────────────────────────────────────────────
const burger    = document.getElementById('burger');
const navMobile = document.getElementById('nav-mobile');
const navClose  = document.getElementById('nav-close');
const overlay   = document.getElementById('nav-overlay');

function openNav() {
  burger?.classList.add('open');
  navMobile?.classList.add('open');
  overlay?.classList.add('open');
  navMobile?.setAttribute('aria-hidden', 'false');
  burger?.setAttribute('aria-expanded', 'true');
  document.body.style.overflow = 'hidden';
}

function closeNav() {
  burger?.classList.remove('open');
  navMobile?.classList.remove('open');
  overlay?.classList.remove('open');
  navMobile?.setAttribute('aria-hidden', 'true');
  burger?.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
  burger?.focus();
}

burger?.addEventListener('click', openNav);
navClose?.addEventListener('click', closeNav);
overlay?.addEventListener('click', closeNav);

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeNav();
});

// ── Flash auto-dismiss ────────────────────────────────────────
document.querySelectorAll('.flash').forEach(el => {
  setTimeout(() => el.remove(), 6000);
});

// ── Smooth anchor scroll ──────────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', e => {
    const target = document.querySelector(link.getAttribute('href'));
    if (target) {
      e.preventDefault();
      const hVal = getComputedStyle(document.documentElement).getPropertyValue('--header-h').trim();
      const offset = (parseInt(hVal) || 72) + 16;
      window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - offset, behavior: 'smooth' });
    }
  });
});

// ── Animate on scroll (IntersectionObserver) ──────────────────
const animateEls = document.querySelectorAll('[data-animate]');
if (animateEls.length && 'IntersectionObserver' in window) {
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('animated');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  animateEls.forEach(el => io.observe(el));
}

// ── Cookie banner ─────────────────────────────────────────────
const COOKIE_KEY = 'edo_cookies_accepted';
const cookieBanner = document.getElementById('cookie-banner');
const cookieAccept = document.getElementById('cookie-accept');
const cookieRefuse = document.getElementById('cookie-refuse');

// Initialiser cookie banner correctement
if (cookieBanner) {
  if (!localStorage.getItem(COOKIE_KEY)) {
    cookieBanner.removeAttribute('style'); // enlever le style="display:none" inline
    cookieBanner.style.display = 'flex';
  } else {
    cookieBanner.style.display = 'none';
  }
}
cookieAccept?.addEventListener('click', () => {
  localStorage.setItem(COOKIE_KEY, '1');
  cookieBanner.style.display = 'none';
});
cookieRefuse?.addEventListener('click', () => {
  localStorage.setItem(COOKIE_KEY, '0');
  cookieBanner.style.display = 'none';
});

// ── Partage social ────────────────────────────────────────────
document.querySelectorAll('[data-share]').forEach(btn => {
  btn.addEventListener('click', () => {
    const network = btn.dataset.share;
    const url   = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    const urls = {
      facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
      linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
      twitter:  `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
    };
    if (network === 'copy') {
      navigator.clipboard?.writeText(window.location.href).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Lien copié !';
        setTimeout(() => btn.textContent = orig, 2000);
      });
    } else if (urls[network]) {
      window.open(urls[network], '_blank', 'noopener,width=600,height=400');
    }
  });
});
