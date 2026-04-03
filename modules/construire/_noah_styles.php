<style>
.noah-tool-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e8ecf0;
}
.noah-tool-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.noah-tool-title { font-size: 20px; font-weight: 700; color: #2c3e50; margin: 0 0 4px; }
.noah-tool-sub   { font-size: 13px; color: #7f8c8d; margin: 0; }
.noah-tool-badge {
    margin-left: auto;
    font-size: 11px; font-weight: 700;
    background: #fef9e7; color: #f39c12;
    border: 1px solid #f9d56e;
    border-radius: 20px; padding: 4px 12px;
    white-space: nowrap;
}

.noah-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #7f8c8d;
    text-decoration: none;
    margin-bottom: 20px;
    transition: color .15s;
}
.noah-back:hover { color: #3498db; }

.noah-form-card {
    background: white;
    border-radius: 10px;
    border: 1px solid #e8ecf0;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    padding: 28px;
    max-width: 680px;
}
.noah-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}
.noah-form-grid .full { grid-column: 1 / -1; }

.noah-field { display: flex; flex-direction: column; gap: 5px; }
.noah-label {
    font-size: 12px; font-weight: 600;
    color: #5a6a7a; text-transform: uppercase; letter-spacing: .04em;
}
.noah-input {
    padding: .6rem .85rem;
    background: #f8fafc; border: 1.5px solid #dde1e7;
    border-radius: 8px; color: #2c3e50; font-size: 14px;
    outline: none; transition: border-color .15s;
    font-family: inherit;
}
.noah-input:focus { border-color: var(--tool-color, #3498db); background: white; box-shadow: 0 0 0 3px rgba(52,152,219,.1); }

.noah-submit {
    width: 100%; padding: .8rem;
    background: var(--tool-color, #3498db); color: white;
    border: none; border-radius: 8px;
    font-weight: 700; font-size: 15px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: opacity .15s;
}
.noah-submit:hover { opacity: .9; }
.noah-submit:disabled { opacity: .6; cursor: not-allowed; }

.noah-result-box {
    margin-top: 24px;
    background: #f8fafc;
    border: 1px solid #e8ecf0;
    border-left: 4px solid var(--tool-color, #3498db);
    border-radius: 10px;
    padding: 20px;
    font-size: 14px; color: #2c3e50;
    white-space: pre-wrap; line-height: 1.8;
    display: none;
}
.noah-result-box.visible { display: block; }
.noah-result-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--tool-color, #3498db);
    margin-bottom: 10px;
}

.noah-error-box {
    margin-top: 16px; padding: 12px 16px;
    background: #fdedec; border: 1px solid #fadbd8;
    border-radius: 8px; font-size: 13px; color: #e74c3c;
    display: none;
}
.noah-error-box.visible { display: block; }

.noah-spinner { animation: _spin .8s linear infinite; }
@keyframes _spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
    .noah-form-grid { grid-template-columns: 1fr; }
}
</style>

<script>
function initNoahForm(formId, toolColor) {
    const form    = document.getElementById(formId);
    if (!form) return;
    const btn     = form.querySelector('.noah-submit');
    const result  = form.querySelector('.noah-result-box');
    const errBox  = form.querySelector('.noah-error-box');
    const icon    = btn.querySelector('i');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        btn.disabled = true;
        icon.className = 'fas fa-spinner noah-spinner';
        result.classList.remove('visible');
        errBox.classList.remove('visible');

        try {
            const res  = await fetch('/admin/api/noah', { method: 'POST', body: new FormData(form) });
            const json = await res.json();
            if (json.success) {
                result.querySelector('.noah-result-content').textContent = json.result;
                result.classList.add('visible');
                result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                errBox.textContent = json.error || 'Une erreur est survenue.';
                errBox.classList.add('visible');
            }
        } catch (err) {
            errBox.textContent = 'Impossible de contacter le serveur.';
            errBox.classList.add('visible');
        } finally {
            btn.disabled = false;
            icon.className = 'fas fa-wand-magic-sparkles';
        }
    });
}
</script>
