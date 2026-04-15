/* ============================================================
   ADMIN.JS — Pascal Hamm Immobilier
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar toggle (mobile) ──────────────────────────────
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        // Fermer en cliquant dehors
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 769 &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // ── Auto-dismiss alerts après 5s ────────────────────────
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // ── Confirmation avant suppression ──────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm || 'Confirmer cette action ?')) {
                e.preventDefault();
            }
        });
    });

});
