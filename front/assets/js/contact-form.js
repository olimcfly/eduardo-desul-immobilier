document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const message = document.getElementById('message');
    const charCount = document.getElementById('charCount');

    if (!form || !submitBtn) return;

    if (message && charCount) {
        const updateCount = () => {
            const len = message.value.length;
            charCount.textContent = String(len);
            charCount.style.color = len > 900
                ? 'var(--danger)'
                : len > 700
                    ? '#d97706'
                    : 'var(--text-muted)';
        };
        message.addEventListener('input', updateCount);
        updateCount();
    }

    const rules = {
        name: v => v.length >= 2 ? '' : 'Nom trop court.',
        email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Email invalide.',
        message: v => v.length >= 10
            ? v.length <= 1000
                ? ''
                : 'Message trop long (1000 car. max).'
            : 'Message trop court (10 car. min).',
    };

    const showError = (name, msg) => {
        const errEl = document.querySelector(`.form-error[data-for="${name}"]`);
        const input = document.getElementById(name);
        if (errEl) errEl.textContent = msg;
        if (input) {
            input.classList.toggle('is-invalid', !!msg);
            input.classList.toggle('is-valid', !msg && input.value.trim().length > 0);
        }
    };

    Object.keys(rules).forEach(name => {
        const input = document.getElementById(name);
        if (!input) return;

        input.addEventListener('blur', () => {
            showError(name, rules[name](input.value.trim()));
        });

        input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid')) {
                showError(name, rules[name](input.value.trim()));
            }
        });
    });

    const validate = () => {
        let valid = true;

        Object.keys(rules).forEach(name => {
            const input = document.getElementById(name);
            if (!input) return;

            const err = rules[name](input.value.trim());
            showError(name, err);
            if (err) valid = false;
        });

        const rgpd = form.querySelector('[name="rgpd"]');
        const rgpdErr = document.querySelector('.form-error[data-for="rgpd"]');
        if (rgpd && !rgpd.checked) {
            if (rgpdErr) rgpdErr.textContent = 'Vous devez accepter la politique.';
            valid = false;
        } else if (rgpdErr) {
            rgpdErr.textContent = '';
        }

        return valid;
    };

    form.addEventListener('submit', (e) => {
        if (!validate()) {
            e.preventDefault();
            const firstError = form.querySelector('.is-invalid');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const btnText = submitBtn.querySelector('.btn-contact-submit__text');
        const btnLoading = submitBtn.querySelector('.btn-contact-submit__loading');
        if (btnText) btnText.hidden = true;
        if (btnLoading) btnLoading.hidden = false;
        submitBtn.disabled = true;
    });
});

function resetContactForm() {
    window.location.href = '/contact';
}
