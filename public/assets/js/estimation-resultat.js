/* ── Estimation Résultat JS ────────────────────────────────── */
'use strict';

// ── Modal qualification ────────────────────────────────────────
(function () {
    const modal       = document.getElementById('qualifModal');
    const backdrop    = document.getElementById('qualifModalBackdrop');
    const closeBtn    = modal?.querySelector('.modal__close');
    const openers     = document.querySelectorAll('#openQualifForm, #openQualifFormSidebar, #openQualifFormSecond');

    if (!modal) return;

    function openModal() {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        // Focus sur le premier champ
        setTimeout(() => modal.querySelector('input:not([type="hidden"])')?.focus(), 50);
    }

    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    openers.forEach(btn => btn?.addEventListener('click', openModal));
    closeBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);

    // Fermer avec Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });

    // Trap focus dans le modal
    modal.addEventListener('keydown', e => {
        if (e.key !== 'Tab') return;
        const focusable = Array.from(
            modal.querySelectorAll('a,button,input,select,textarea,[tabindex]:not([tabindex="-1"])')
        ).filter(el => !el.disabled && !el.hidden);
        if (!focusable.length) return;
        const first = focusable[0];
        const last  = focusable[focusable.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
        } else {
            if (document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
}());

// ── Slider urgence ─────────────────────────────────────────────
(function () {
    const slider  = document.getElementById('urgence-slider');
    const display = document.getElementById('urgence-display');
    if (!slider || !display) return;
    function update() { display.textContent = slider.value + '/5'; }
    slider.addEventListener('input', update);
    update();
}());

// ── Projet-btn : active sur radio checked ──────────────────────
document.querySelectorAll('.projet-btn').forEach(btn => {
    const radio = btn.querySelector('input[type="radio"]');
    if (!radio) return;
    if (radio.checked) btn.classList.add('active');
    btn.addEventListener('click', () => {
        document.querySelectorAll('.projet-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// ── Validation formulaire qualification ───────────────────────
(function () {
    const form = document.getElementById('form-qualification');
    if (!form) return;

    form.addEventListener('submit', e => {
        const prenom = form.querySelector('#q-prenom');
        const email  = form.querySelector('#q-email');
        const rgpd   = form.querySelector('[name="rgpd"]');

        let valid = true;

        [prenom, email].forEach(field => {
            if (!field?.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (email?.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            email.classList.add('is-invalid');
            valid = false;
        }

        if (rgpd && !rgpd.checked) {
            rgpd.closest('.form-group, .checkbox-label')?.classList.add('is-invalid');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            form.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            const btn = form.querySelector('[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Envoi en cours…';
            }
        }
    });

    // Retirer is-invalid au premier input
    form.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', () => field.classList.remove('is-invalid'));
    });
}());
