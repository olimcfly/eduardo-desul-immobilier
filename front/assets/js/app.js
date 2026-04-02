/* ═══════════════════════════════════════════════════════════════
   APP.JS — Interactions globales
   ═══════════════════════════════════════════════════════════════ */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // ── 1. BURGER MENU ─────────────────────────────────────────
    const burger = document.getElementById('burger');
    const nav    = document.getElementById('mainNav');

    if (burger && nav) {
        burger.addEventListener('click', () => {
            const open = burger.classList.toggle('is-open');
            nav.classList.toggle('is-open', open);
            burger.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        });

        document.addEventListener('click', e => {
            if (!burger.contains(e.target) && !nav.contains(e.target)) {
                burger.classList.remove('is-open');
                nav.classList.remove('is-open');
                document.body.style.overflow = '';
            }
        });
    }

    // ── 2. HEADER SCROLL ───────────────────────────────────────
    const header = document.getElementById('siteHeader');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 50);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // ── 3. SLIDER AVIS ─────────────────────────────────────────
    const slider   = document.getElementById('reviewsSlider');
    const btnPrev  = document.getElementById('reviewPrev');
    const btnNext  = document.getElementById('reviewNext');

    if (slider && btnPrev && btnNext) {
        const cards      = slider.querySelectorAll('.review-card');
        const visible    = () => window.innerWidth < 768 ? 1 : window.innerWidth < 1024 ? 2 : 3;
        let current = 0;

        const updateSlider = () => {
            const v    = visible();
            const max  = Math.max(0, cards.length - v);
            current    = Math.min(Math.max(0, current), max);
            const pct  = current * (100 / v);
            slider.style.transform = `translateX(-${pct}%)`;
            btnPrev.disabled = current === 0;
            btnNext.disabled = current >= max;
        };

        btnPrev.addEventListener('click', () => { current--; updateSlider(); });
        btnNext.addEventListener('click', () => { current++; updateSlider(); });
        window.addEventListener('resize', updateSlider, { passive: true });
        updateSlider();
    }

    // ── 4. GOOGLE MAP ──────────────────────────────────────────
    window.initMap = function () {
        const mapEl = document.getElementById('googleMap');
        if (!mapEl || typeof google === 'undefined' || !google.maps) return;

        const lat = parseFloat(mapEl.dataset.lat || '48.8566');
        const lng = parseFloat(mapEl.dataset.lng || '2.3522');

        const map = new google.maps.Map(mapEl, {
            center: { lat, lng },
            zoom:   13,
            styles: [
                { featureType: 'all', elementType: 'geometry.fill', stylers: [{ color: '#f5f5f5' }] },
                { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#c9e4f0' }] },
                { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
            ],
            disableDefaultUI: true,
            zoomControl:      true,
        });

        new google.maps.Marker({
            position:  { lat, lng },
            map,
            title:     mapEl.dataset.name || 'Conseiller immobilier',
            icon: {
                path:         google.maps.SymbolPath.CIRCLE,
                scale:        10,
                fillColor:    '#2563eb',
                fillOpacity:  1,
                strokeColor:  '#ffffff',
                strokeWeight: 2,
            },
        });
    };

    // ── 5. ANIMATIONS SCROLL (IntersectionObserver) ────────────
    const animEls = document.querySelectorAll('.service-card, .blog-card, .review-card, .about__value, .zone-item');

    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 });

        animEls.forEach((el, i) => {
            el.style.transitionDelay = `${i * 0.05}s`;
            obs.observe(el);
        });
    } else {
        animEls.forEach(el => el.classList.add('is-visible'));
    }

    // ── 6. FORMULAIRE NEWSLETTER ───────────────────────────────
    const nlForm = document.getElementById('newsletterForm') || document.getElementById('footer-newsletter-form');
    if (nlForm) {
        nlForm.addEventListener('submit', async e => {
            e.preventDefault();
            const btn   = nlForm.querySelector('button[type="submit"]');
            const input = nlForm.querySelector('input[type="email"]');
            const email = input ? input.value : '';
            if (!btn || !email) return;

            btn.disabled = true;
            btn.textContent = 'Inscription...';

            try {
                const csrfToken = nlForm.querySelector('[name="csrf_token"]');
                const res = await fetch('/api/newsletter-subscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email,
                        csrf_token: csrfToken ? csrfToken.value : undefined,
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    nlForm.innerHTML = '<div class="nl-success"><i class="fas fa-check-circle"></i><strong>Merci !</strong> Vous êtes bien inscrit(e).</div>';
                } else {
                    btn.disabled = false;
                    btn.textContent = "S'inscrire";
                    showAlert(nlForm, data.message || 'Une erreur est survenue.', 'error');
                }
            } catch {
                btn.disabled = false;
                btn.textContent = "S'inscrire";
                showAlert(nlForm, 'Erreur réseau. Réessayez.', 'error');
            }
        });
    }

    // ── 7. SMOOTH SCROLL ───────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const href = a.getAttribute('href');
            if (!href || href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    function showAlert(parent, msg, type = 'info') {
        const div = document.createElement('div');
        div.className = `alert alert--${type}`;
        div.textContent = msg;
        parent.prepend(div);
        setTimeout(() => div.remove(), 4000);
    }

    // ── 8. BARRE DE PROGRESSION DE LECTURE ─────────────────────
    const article = document.querySelector('.article-content');
    if (article) {
        const progressBar = document.createElement('div');
        progressBar.className = 'reading-progress';
        progressBar.style.width = '0%';
        document.body.prepend(progressBar);

        const updateProgress = () => {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight;
            const winHeight = window.innerHeight;
            const scrolled = (scrollTop / (docHeight - winHeight)) * 100;
            progressBar.style.width = Math.min(scrolled, 100) + '%';
        };

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    // ── 9. SOMMAIRE : HIGHLIGHT SECTION ACTIVE ─────────────────
    const tocLinks = document.querySelectorAll('.toc-list a');
    if (tocLinks.length && 'IntersectionObserver' in window) {
        const headings = document.querySelectorAll('.article-content h2[id], .article-content h3[id]');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    tocLinks.forEach(link => link.classList.remove('toc-active'));
                    const activeLink = document.querySelector(`.toc-list a[href="#${entry.target.id}"]`);
                    if (activeLink) activeLink.classList.add('toc-active');
                }
            });
        }, { rootMargin: '-20% 0% -70% 0%' });

        headings.forEach(heading => observer.observe(heading));
    }

    // ── 10. BOUTON COPIER LE LIEN ──────────────────────────────
    const copyBtn = document.querySelector('.share-btn--copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                const originalHtml = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copié !';
                copyBtn.style.background = '#16a34a';
                setTimeout(() => {
                    copyBtn.innerHTML = originalHtml;
                    copyBtn.style.background = '';
                }, 2000);
            } catch {
                showAlert(copyBtn.parentElement || document.body, 'Impossible de copier le lien.', 'error');
            }
        });
    }

    // ── 11. SIDEBAR STICKY SUR ARTICLE ─────────────────────────
    const sidebar = document.querySelector('.article-sidebar');
    if (sidebar) {
        const sidebarTop = sidebar.getBoundingClientRect().top + window.scrollY;
        window.addEventListener('scroll', () => {
            if (window.scrollY > sidebarTop - 80) {
                sidebar.style.position = 'sticky';
                sidebar.style.top = '80px';
                sidebar.style.alignSelf = 'start';
            }
        }, { passive: true });
    }

    // ── 12. LAZY-LOAD IMAGE AVEC data-src ──────────────────────
    if ('IntersectionObserver' in window) {
        const lazyImgs = document.querySelectorAll('img[loading="lazy"]');
        const imgObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) img.src = img.dataset.src;
                    imgObserver.unobserve(img);
                }
            });
        });
        lazyImgs.forEach(img => imgObserver.observe(img));
    }

    // ── 13. HOVER CARDS ARTICLE ────────────────────────────────
    document.querySelectorAll('.article-card').forEach(card => {
        card.addEventListener('mouseenter', function onMouseEnter() {
            this.style.boxShadow = '0 20px 40px rgba(37,99,235,.12)';
        });
        card.addEventListener('mouseleave', function onMouseLeave() {
            this.style.boxShadow = '';
        });
    });

    // ── 14. TAB ACTIVE (MOBILE) ────────────────────────────────
    const activeTab = document.querySelector('.filter-tab--active');
    if (activeTab) {
        activeTab.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center',
        });
    }
});
