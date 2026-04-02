document.addEventListener('DOMContentLoaded', () => {
    // ── LOGIN FORM ────────────────────────────────────────────
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', () => {
            const btnText = submitBtn.querySelector('.btn-auth__text');
            const btnLoading = submitBtn.querySelector('.btn-auth__loading');
            if (btnText) btnText.hidden = true;
            if (btnLoading) btnLoading.hidden = false;
            submitBtn.disabled = true;
        });
    }

    // ── OTP INPUTS ────────────────────────────────────────────
    const otpInputs = document.querySelectorAll('.otp-input');
    const verifyBtn = document.getElementById('verifyBtn');
    const verifyForm = document.getElementById('verifyForm');

    if (otpInputs.length) {
        const isComplete = () => [...otpInputs].every((i) => /^\d$/.test(i.value));

        const checkComplete = () => {
            const allFilled = isComplete();
            if (verifyBtn) verifyBtn.disabled = !allFilled;
            otpInputs.forEach((input) => input.classList.toggle('is-filled', !!input.value));
        };

        otpInputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                // Colle multi-caractères (ex: coller le code)
                if (e.target.value.length > 1) {
                    const digits = e.target.value.replace(/\D/g, '').slice(0, otpInputs.length).split('');
                    otpInputs.forEach((inp, i) => {
                        inp.value = digits[i] || '';
                        inp.classList.toggle('is-filled', !!inp.value);
                    });
                    const next = otpInputs[Math.min(digits.length, otpInputs.length - 1)];
                    next?.focus();
                    checkComplete();
                    return;
                }

                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 1);

                // Avance au suivant
                if (e.target.value && idx < otpInputs.length - 1) {
                    otpInputs[idx + 1].focus();
                }

                checkComplete();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    otpInputs[idx - 1].focus();
                    otpInputs[idx - 1].value = '';
                    otpInputs[idx - 1].classList.remove('is-filled');
                    checkComplete();
                }

                // Flèches
                if (e.key === 'ArrowLeft' && idx > 0) {
                    e.preventDefault();
                    otpInputs[idx - 1].focus();
                }
                if (e.key === 'ArrowRight' && idx < otpInputs.length - 1) {
                    e.preventDefault();
                    otpInputs[idx + 1].focus();
                }
            });

            // Paste depuis n'importe quel input
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)
                    .getData('text')
                    .replace(/\D/g, '')
                    .slice(0, otpInputs.length)
                    .split('');

                otpInputs.forEach((inp, i) => {
                    inp.value = pasted[i] || '';
                    inp.classList.toggle('is-filled', !!inp.value);
                });

                const nextEmpty = [...otpInputs].find((i) => !i.value);
                (nextEmpty || otpInputs[otpInputs.length - 1]).focus();
                checkComplete();
            });

            input.addEventListener('focus', () => input.select());
        });

        // Auto-submit quand le code est complet
        const autoSubmit = () => {
            if (isComplete() && verifyForm) {
                setTimeout(() => {
                    if (verifyBtn) {
                        const btnText = verifyBtn.querySelector('.btn-auth__text');
                        const btnLoading = verifyBtn.querySelector('.btn-auth__loading');
                        if (btnText) btnText.hidden = true;
                        if (btnLoading) btnLoading.hidden = false;
                        verifyBtn.disabled = true;
                    }
                    verifyForm.submit();
                }, 300);
            }
        };

        otpInputs.forEach((i) => i.addEventListener('input', autoSubmit));
        checkComplete();
    }

    // Shake si erreur
    const alertError = document.getElementById('alertError');
    if (alertError) {
        const otpContainer = document.querySelector('.otp-inputs');
        if (otpContainer) {
            otpContainer.classList.add('is-shaking');
            setTimeout(() => otpContainer.classList.remove('is-shaking'), 450);
        }
    }
});
