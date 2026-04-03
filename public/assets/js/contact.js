const APP_SETTINGS = window.__APP_SETTINGS__ || {};
const ADVISOR_NAME = APP_SETTINGS.advisorName || 'votre conseiller';

/* ── Contact JS ──────────────────────────────────────────────── */

'use strict';

const contactForm = document.getElementById('contact-form');
contactForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = contactForm.querySelector('[type="submit"]');
  const original = btn.textContent;
  btn.disabled = true;
  btn.textContent = 'Envoi en cours…';

  // Validation basique côté client
  let valid = true;
  contactForm.querySelectorAll('[required]').forEach(field => {
    const err = field.closest('.form-group')?.querySelector('.form-error');
    if (!field.value.trim()) {
      valid = false;
      if (err) err.textContent = 'Ce champ est obligatoire.';
      field.style.borderColor = 'var(--clr-danger)';
    } else {
      if (err) err.textContent = '';
      field.style.borderColor = '';
    }
  });

  if (!valid) {
    btn.disabled = false;
    btn.textContent = original;
    return;
  }

  try {
    const res = await fetch(contactForm.action || window.location.href, {
      method: 'POST',
      body: new FormData(contactForm),
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const json = await res.json();
    if (json.success) {
      contactForm.closest('.contact-form-box').innerHTML = `
        <div style="text-align:center;padding:3rem 1rem">
          <div style="font-size:3rem;margin-bottom:1rem">✅</div>
          <h2>Message envoyé !</h2>
          <p style="color:var(--clr-text-muted);margin-top:.75rem">${ADVISOR_NAME} vous répondra dans les plus brefs délais, généralement sous 24h.</p>
        </div>`;
    } else {
      btn.disabled = false;
      btn.textContent = original;
      alert(json.message || 'Une erreur est survenue. Veuillez réessayer.');
    }
  } catch {
    btn.disabled = false;
    btn.textContent = original;
  }
});
