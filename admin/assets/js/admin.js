/* ========================================
   ADMIN.JS - Utilitaires globaux
======================================== */

const Admin = {

    /* --- Notifications toast --- */
    toast(message, type = 'info', duration = 3500) {
        const colors = {
            success: '#16a34a',
            danger:  '#dc2626',
            warning: '#d97706',
            info:    '#2563eb'
        };
        const icons = {
            success: '✔',
            danger:  '✖',
            warning: '⚠',
            info:    'ℹ'
        };

        const t = document.createElement('div');
        t.style.cssText = `
            position:fixed; bottom:24px; right:24px; z-index:9999;
            background:#fff; border-left:4px solid ${colors[type]};
            box-shadow:0 4px 16px rgba(0,0,0,.12);
            border-radius:8px; padding:12px 18px;
            display:flex; align-items:center; gap:10px;
            font-size:14px; max-width:340px;
            animation: slideIn .25s ease;
        `;
        t.innerHTML = `
            <span style="color:${colors[type]};font-size:16px">${icons[type]}</span>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()"
                style="margin-left:auto;background:none;border:none;
                       cursor:pointer;color:#94a3b8;font-size:18px;line-height:1">×</button>
        `;

        document.body.appendChild(t);
        setTimeout(() => t.style.opacity = '0', duration);
        setTimeout(() => t.remove(), duration + 300);
    },

    /* --- Confirmation modale --- */
    confirm(message, onConfirm, onCancel = null) {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position:fixed; inset:0; background:rgba(0,0,0,.4);
            z-index:9998; display:flex; align-items:center; justify-content:center;
        `;
        overlay.innerHTML = `
            <div style="background:#fff;border-radius:12px;padding:28px 32px;
                        max-width:380px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.15)">
                <p style="font-size:15px;margin-bottom:20px;line-height:1.5">${message}</p>
                <div style="display:flex;gap:10px;justify-content:flex-end">
                    <button id="adm-cancel"
                        style="padding:8px 18px;border:1px solid #e2e8f0;
                               border-radius:6px;background:#fff;cursor:pointer;font-size:14px">
                        Annuler
                    </button>
                    <button id="adm-confirm"
                        style="padding:8px 18px;border:none;border-radius:6px;
                               background:#dc2626;color:#fff;cursor:pointer;font-size:14px;font-weight:600">
                        Confirmer
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        overlay.querySelector('#adm-confirm').onclick = () => {
            overlay.remove();
            onConfirm();
        };
        overlay.querySelector('#adm-cancel').onclick = () => {
            overlay.remove();
            if (onCancel) onCancel();
        };
        overlay.onclick = (e) => {
            if (e.target === overlay) overlay.remove();
        };
    },

    /* --- Loader global --- */
    showLoader() {
        if (document.getElementById('adm-loader')) return;
        const l = document.createElement('div');
        l.id = 'adm-loader';
        l.style.cssText = `
            position:fixed; inset:0; background:rgba(255,255,255,.6);
            z-index:9997; display:flex; align-items:center; justify-content:center;
        `;
        l.innerHTML = `
            <div style="width:40px;height:40px;border:3px solid #e2e8f0;
                        border-top-color:#2563eb;border-radius:50%;
                        animation:spin .7s linear infinite"></div>
        `;
        document.body.appendChild(l);
    },

    hideLoader() {
        document.getElementById('adm-loader')?.remove();
    },

    /* --- AJAX helper --- */
    async fetch(url, options = {}) {
        this.showLoader();
        try {
            const res = await fetch(url, {
                headers: { 'Content-Type': 'application/json', ...options.headers },
                ...options
            });
            const data = await res.json();
            return data;
        } catch (err) {
            this.toast('Erreur réseau', 'danger');
            console.error(err);
        } finally {
            this.hideLoader();
        }
    },

    /* --- Formatage prix --- */
    formatPrice(n) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency', currency: 'EUR', maximumFractionDigits: 0
        }).format(n);
    },

    /* --- Formatage date --- */
    formatDate(d) {
        return new Intl.DateTimeFormat('fr-FR', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        }).format(new Date(d));
    },

    /* --- Sidebar toggle (mobile) --- */
    initSidebar() {
        const btn = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.app-sidebar');
        if (!btn || !sidebar) return;

        btn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !btn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    },

    /* --- Tableaux triables --- */
    initSortableTables() {
        document.querySelectorAll('th[data-sort]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const table = th.closest('table');
                const tbody = table.querySelector('tbody');
                const col   = [...th.parentElement.children].indexOf(th);
                const asc   = th.dataset.order !== 'asc';
                th.dataset.order = asc ? 'asc' : 'desc';

                const rows = [...tbody.querySelectorAll('tr')];
                rows.sort((a, b) => {
                    const va = a.cells[col]?.innerText.trim() || '';
                    const vb = b.cells[col]?.innerText.trim() || '';
                    return asc ? va.localeCompare(vb, 'fr') : vb.localeCompare(va, 'fr');
                });
                rows.forEach(r => tbody.appendChild(r));

                document.querySelectorAll('th[data-sort]').forEach(t => {
                    t.textContent = t.textContent.replace(/ [▲▼]$/, '');
                });
                th.textContent += asc ? ' ▲' : ' ▼';
            });
        });
    },

    /* --- Confirmation suppression --- */
    initDeleteButtons() {
        document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const msg = btn.dataset.confirmDelete || 'Supprimer cet élément ?';
                const href = btn.href || btn.dataset.href;
                this.confirm(msg, () => {
                    window.location.href = href;
                });
            });
        });
    },

    /* --- Init global --- */
    init() {
        this.initSidebar();
        this.initSortableTables();
        this.initDeleteButtons();

        // Animation CSS keyframes injectées une seule fois
        if (!document.getElementById('adm-keyframes')) {
            const style = document.createElement('style');
            style.id = 'adm-keyframes';
            style.textContent = `
                @keyframes slideIn { from { transform: translateX(40px); opacity:0; } to { transform: translateX(0); opacity:1; } }
                @keyframes spin    { to { transform: rotate(360deg); } }
                .app-sidebar.open  { transform: translateX(0) !important; }
            `;
            document.head.appendChild(style);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => Admin.init());
