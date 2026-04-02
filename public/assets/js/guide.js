/* ── Guide / Blog JS ─────────────────────────────────────────── */

'use strict';

// ── Table des matières (TOC) auto-générée ─────────────────────
const article = document.querySelector('.article-content');
const tocEl   = document.getElementById('toc-list');

if (article && tocEl) {
  const headings = article.querySelectorAll('h2, h3');
  headings.forEach((h, i) => {
    const id = h.id || `section-${i}`;
    h.id = id;
    const li = document.createElement('li');
    li.className = h.tagName === 'H3' ? 'toc-sub' : '';
    li.innerHTML = `<a href="#${id}">${h.textContent}</a>`;
    tocEl.appendChild(li);
  });
}

// ── Lecture progressive (progress bar) ────────────────────────
const progressBar = document.getElementById('reading-progress');
if (progressBar && article) {
  window.addEventListener('scroll', () => {
    const rect  = article.getBoundingClientRect();
    const total = article.offsetHeight - window.innerHeight;
    const done  = Math.max(0, Math.min(100, ((window.scrollY - article.offsetTop) / total) * 100));
    progressBar.style.width = done + '%';
  }, { passive: true });
}

// ── Partage article ───────────────────────────────────────────
document.querySelectorAll('[data-share]').forEach(btn => {
  btn.addEventListener('click', () => {
    const network = btn.dataset.share;
    const url     = encodeURIComponent(window.location.href);
    const title   = encodeURIComponent(document.title);
    const urls = {
      facebook:  `https://www.facebook.com/sharer/sharer.php?u=${url}`,
      linkedin:  `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
      twitter:   `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
      copy:      null
    };
    if (network === 'copy') {
      navigator.clipboard?.writeText(window.location.href).then(() => {
        btn.textContent = 'Lien copié !';
        setTimeout(() => btn.textContent = 'Copier le lien', 2000);
      });
    } else if (urls[network]) {
      window.open(urls[network], '_blank', 'noopener,width=600,height=400');
    }
  });
});
