document.addEventListener('DOMContentLoaded', function () {

    const container   = document.getElementById('dashboard-container');
    const toggleBtn   = document.getElementById('sidebar-toggle');
    const toggleIcon  = document.getElementById('toggle-icon');
    const menuItems   = document.querySelectorAll('.sidebar-menu .menu-item');
    const mainContent = document.getElementById('main-content');
    let lastLoadedUrl = null;

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
                userMenu.classList.remove('open');
                const module = this.getAttribute('data-module');
                if (module) {
                    e.preventDefault();
                    menuItems.forEach(function (i) { i.classList.remove('active'); });
                    this.classList.add('active');
                    loadModule(module, { push: true });
                }
            });
        });
    }

    // ── Navigation modules ──────────────────────────────────────
    menuItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
            const module = this.getAttribute('data-module');
            if (!module) return;
            e.preventDefault();
            menuItems.forEach(function (i) { i.classList.remove('active'); });
            this.classList.add('active');
            loadModule(module, { push: true });
        });
    });

    // ── Charger un module via AJAX ──────────────────────────────
    function loadModule(module, options) {
        if (!mainContent) return;
        const opts = options || {};
        const url = '/admin?module=' + encodeURIComponent(module);

        mainContent.innerHTML = '<div class="loading-spinner"><div class="spinner"></div> Chargement…</div>';

        fetch(url)
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

                    // Mettre à jour le breadcrumb de la topbar avec le titre du module chargé
                    const pageTitle = doc.title ? doc.title.split(' — ')[0].trim() : module;
                    const breadcrumbCurrent = document.querySelector('.breadcrumb-current');
                    if (breadcrumbCurrent) breadcrumbCurrent.textContent = pageTitle;

                    // Mettre à jour le titre de la page
                    document.title = doc.title || document.title;

                    if (opts.push === true && window.history && typeof window.history.pushState === 'function') {
                        const nextState = { module: module, url: url };
                        if (lastLoadedUrl !== url && window.location.href !== new URL(url, window.location.origin).href) {
                            window.history.pushState(nextState, '', url);
                        }
                    }
                    lastLoadedUrl = url;
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

    window.addEventListener('popstate', function () {
        const params = new URLSearchParams(window.location.search);
        const module = params.get('module');
        if (!module) return;

        const activeLink = document.querySelector('.sidebar-menu .menu-item[data-module="' + module + '"]');
        if (activeLink) {
            menuItems.forEach(function (i) { i.classList.remove('active'); });
            activeLink.classList.add('active');
        }
        loadModule(module, { push: false });
    });

    // ── Module actif au démarrage ───────────────────────────────
    // Le PHP rend déjà le bon contenu initial — pas d'auto-chargement AJAX
    // pour éviter le layout imbriqué (doublon sidebar + topbar).
    // L'AJAX ne se déclenche qu'au clic sur un item de menu différent.

});
