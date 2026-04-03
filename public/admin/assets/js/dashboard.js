document.addEventListener('DOMContentLoaded', function () {

    const container   = document.getElementById('dashboard-container');
    const toggleBtn   = document.getElementById('sidebar-toggle');
    const toggleIcon  = document.getElementById('toggle-icon');
    const menuItems   = document.querySelectorAll('.sidebar-menu .menu-item');
    const mainContent = document.getElementById('main-content');

    // ── Sidebar collapse ────────────────────────────────────────
    if (toggleBtn && container) {
        const STORAGE_KEY = 'sidebar_collapsed';

        if (localStorage.getItem(STORAGE_KEY) === '1') {
            container.classList.add('collapsed');
            if (toggleIcon) toggleIcon.className = 'fas fa-chevron-right';
        }

        toggleBtn.addEventListener('click', function () {
            const collapsed = container.classList.toggle('collapsed');
            if (toggleIcon) toggleIcon.className = collapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
            localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        });
    }

    // ── Menu utilisateur (dropdown) ─────────────────────────────
    const userMenu    = document.getElementById('user-menu');
    const userTrigger = document.getElementById('user-menu-trigger');

    if (userMenu && userTrigger) {
        userTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            userMenu.classList.toggle('open');
        });

        // Fermer en cliquant ailleurs
        document.addEventListener('click', function () {
            userMenu.classList.remove('open');
        });

        // Les liens du dropdown qui chargent un module
        userMenu.querySelectorAll('.dropdown-item[data-module]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                userMenu.classList.remove('open');
                const module = this.getAttribute('data-module');
                if (module) {
                    menuItems.forEach(function (i) { i.classList.remove('active'); });
                    loadModule(module);
                }
            });
        });
    }

    // ── Navigation modules ──────────────────────────────────────
    menuItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            menuItems.forEach(function (i) { i.classList.remove('active'); });
            this.classList.add('active');
            const module = this.getAttribute('data-module');
            if (module) loadModule(module);
        });
    });

    // ── Charger un module via AJAX ──────────────────────────────
    function loadModule(module) {
        if (!mainContent) return;

        mainContent.innerHTML = '<div class="loading-spinner"><div class="spinner"></div> Chargement…</div>';

        fetch('/admin?module=' + encodeURIComponent(module))
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(function (html) {
                const parser = new DOMParser();
                const doc    = parser.parseFromString(html, 'text/html');
                const inner  = doc.getElementById('main-content');
                if (inner) {
                    mainContent.innerHTML = inner.innerHTML;
                } else {
                    // Sécurité : ne jamais injecter le HTML complet (évite le layout imbriqué)
                    mainContent.innerHTML = '<div class="loading-spinner"><i class="fas fa-triangle-exclamation"></i>&nbsp;Impossible de charger ce module.</div>';
                    console.error('loadModule: #main-content introuvable dans la réponse pour le module "' + module + '"');
                }
            })
            .catch(function (err) {
                mainContent.innerHTML = '<div class="loading-spinner"><i class="fas fa-triangle-exclamation"></i>&nbsp;Impossible de charger ce module.</div>';
                console.error(err);
            });
    }

    // ── Module actif au démarrage ───────────────────────────────
    // Le PHP rend déjà le bon contenu initial — pas d'auto-chargement AJAX
    // pour éviter le layout imbriqué (doublon sidebar + topbar).
    // L'AJAX ne se déclenche qu'au clic sur un item de menu différent.

});
