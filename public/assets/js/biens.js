const APP_SETTINGS = window.__APP_SETTINGS__ || {};
const ADVISOR_NAME = APP_SETTINGS.advisorName || 'votre conseiller';

/* ── Biens JS ────────────────────────────────────────────────── */

'use strict';

// ── Filtres auto-submit ───────────────────────────────────────
const filterForm = document.getElementById('filter-form');
filterForm?.querySelectorAll('select').forEach(sel => {
  sel.addEventListener('change', () => filterForm.submit());
});

// ── Galerie photo (page détail bien) ─────────────────────────
const mainImg   = document.querySelector('.bien-gallery__main img');
const thumbs    = document.querySelectorAll('.bien-gallery__thumbs img');

thumbs.forEach(thumb => {
  thumb.addEventListener('click', () => {
    if (mainImg) {
      mainImg.src = thumb.dataset.full || thumb.src;
      mainImg.alt = thumb.alt;
    }
    thumbs.forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
  });
});

// ── Formulaire contact bien ────────────────────────────────────
const contactBien = document.getElementById('contact-bien-form');
contactBien?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = contactBien.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.textContent = 'Envoi…';
  try {
    const res = await fetch(contactBien.action, {
      method: 'POST',
      body: new FormData(contactBien),
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const json = await res.json();
    if (json.success) {
      contactBien.innerHTML = `<p class="text-center" style="padding:2rem;color:var(--clr-success)">✅ Message envoyé ! ${ADVISOR_NAME} vous contactera sous 24h.</p>`;
    } else {
      btn.disabled = false;
      btn.textContent = 'Envoyer';
      alert(json.message || 'Une erreur est survenue.');
    }
  } catch {
    btn.disabled = false;
    btn.textContent = 'Envoyer';
  }
});
