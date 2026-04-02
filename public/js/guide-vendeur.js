/**
 * Guide Vendeur — Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    // ══════════════════════════════════════════════════
    // 1. READING PROGRESS BAR
    // ══════════════════════════════════════════════════
    const progressBar = document.getElementById('readingProgress');
    const sidebarProgress = document.getElementById('sidebarProgress');

    function updateReadingProgress() {
        const doc = document.documentElement;
        const total = doc.scrollHeight - doc.clientHeight;
        const current = window.scrollY;
        const pct = total > 0 ? Math.min((current / total) * 100, 100).toFixed(1) : '0.0';

        if (progressBar) progressBar.style.width = pct + '%';
        if (sidebarProgress) sidebarProgress.style.width = pct + '%';
    }

    window.addEventListener('scroll', updateReadingProgress, { passive: true });
    updateReadingProgress();

    // ══════════════════════════════════════════════════
    // 2. STICKY SIDEBAR
    // ══════════════════════════════════════════════════
    const sidebar = document.getElementById('guideSidebar');
    if (sidebar && window.innerWidth > 1100) {
        const sidebarTop = sidebar.getBoundingClientRect().top + window.scrollY;

        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            if (scrollY > sidebarTop - 100) {
                sidebar.style.top = '100px';
            }
        }, { passive: true });
    }

    // ══════════════════════════════════════════════════
    // 3. ACTIVE STEP TRACKING (IntersectionObserver)
    // ══════════════════════════════════════════════════
    const steps = document.querySelectorAll('.guide-step');
    const progressLinks = document.querySelectorAll('.progress-step');

    if (steps.length && progressLinks.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    const num = id.replace('etape-', '');

                    progressLinks.forEach((link) => {
                        link.classList.remove('progress-step--active', 'progress-step--done');
                        const linkStep = link.dataset.step;
                        if (linkStep === num) {
                            link.classList.add('progress-step--active');
                        } else if (parseInt(linkStep, 10) < parseInt(num, 10)) {
                            link.classList.add('progress-step--done');
                        }
                    });
                }
            });
        }, {
            rootMargin: '-30% 0px -60% 0px',
            threshold: 0,
        });

        steps.forEach((step) => observer.observe(step));
    }

    // ══════════════════════════════════════════════════
    // 4. SMOOTH SCROLL
    // ══════════════════════════════════════════════════
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', function onAnchorClick(e) {
            const href = this.getAttribute('href');
            if (!href || href === '#') return;

            const target = document.querySelector(href);
            if (!target) return;

            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // ══════════════════════════════════════════════════
    // 5. DOC TABS
    // ══════════════════════════════════════════════════
    const docTabBtns = document.querySelectorAll('.doc-tab-btn');

    docTabBtns.forEach((btn) => {
        btn.addEventListener('click', function onTabClick() {
            const target = this.dataset.tab;

            // Désactiver tous les tabs
            docTabBtns.forEach((b) => b.classList.remove('doc-tab-btn--active'));
            document.querySelectorAll('.doc-tab-content')
                .forEach((c) => c.classList.remove('doc-tab-content--active'));

            // Activer le tab cliqué
            this.classList.add('doc-tab-btn--active');
            const content = document.getElementById('tab-' + target);
            if (content) content.classList.add('doc-tab-content--active');
        });
    });

    // ══════════════════════════════════════════════════
    // 6. CHECKLIST INTERACTIVE
    // ══════════════════════════════════════════════════
    const checks = document.querySelectorAll('.checklist-check');
    const fillBar = document.getElementById('checklistFill');
    const countEl = document.getElementById('checklistCount');
    const STORAGE_KEY = 'guide_vendeur_checklist';

    // Restaurer depuis localStorage
    function loadChecklist() {
        let saved = {};
        try {
            saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        } catch (_) {
            saved = {};
        }

        checks.forEach((chk) => {
            if (saved[chk.dataset.id]) {
                chk.checked = true;
            }
        });
        updateChecklistProgress();
    }

    // Sauvegarder
    function saveChecklist() {
        const state = {};
        checks.forEach((chk) => {
            state[chk.dataset.id] = chk.checked;
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    }

    // Mettre à jour la barre
    function updateChecklistProgress() {
        const total = checks.length;
        const checked = [...checks].filter((c) => c.checked).length;
        const pct = total > 0 ? Math.round((checked / total) * 100) : 0;

        if (fillBar) fillBar.style.width = pct + '%';
        if (countEl) countEl.textContent = checked;
    }

    checks.forEach((chk) => {
        chk.addEventListener('change', () => {
            saveChecklist();
            updateChecklistProgress();

            // Animation de complétion
            if (chk.checked) {
                const label = chk.closest('.checklist-item');
                if (label) {
                    label.style.transform = 'scale(1.01)';
                    setTimeout(() => { label.style.transform = ''; }, 200);
                }
            }
        });
    });

    // Reset
    const resetBtn = document.getElementById('resetChecklist');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (!confirm('Réinitialiser toute la checklist ?')) return;
            checks.forEach((c) => { c.checked = false; });
            localStorage.removeItem(STORAGE_KEY);
            updateChecklistProgress();
        });
    }

    loadChecklist();

    // ══════════════════════════════════════════════════
    // 7. ANIMATION SCROLL REVEAL
    // ══════════════════════════════════════════════════
    const animateEls = document.querySelectorAll(
        '.guide-step, .stat-box, .method-card, .factor-item, '
        + '.timeline-item, .fiscal-box, .sidebar-card',
    );

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    animateEls.forEach((el) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        revealObserver.observe(el);
    });

    // ══════════════════════════════════════════════════
    // 8. STATS COUNTER ANIMATION
    // ══════════════════════════════════════════════════
    const statNumbers = document.querySelectorAll('.stat-box__number');

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            const el = entry.target;
            const text = el.textContent.trim();
            const numMatch = text.match(/[\d.]+/);
            if (!numMatch) return;

            const target = parseFloat(numMatch[0]);
            const prefix = text.replace(/[\d.]+.*/, '');
            const suffix = text.replace(/^[^0-9-]*[\d.]+/, '');
            const isFloat = text.includes('.');
            const duration = 1500;
            const start = performance.now();

            function animate(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = target * eased;

                el.textContent = prefix
                    + (isFloat ? current.toFixed(1) : Math.round(current))
                    + suffix;

                if (progress < 1) requestAnimationFrame(animate);
            }

            requestAnimationFrame(animate);
            counterObserver.unobserve(el);
        });
    }, { threshold: 0.5 });

    statNumbers.forEach((el) => counterObserver.observe(el));

    // ══════════════════════════════════════════════════
    // 9. SHARE GUIDE
    // ══════════════════════════════════════════════════
    const shareBtn = document.getElementById('shareGuide');
    if (shareBtn) {
        shareBtn.addEventListener('click', async () => {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'Guide Vendeur — Vendre au meilleur prix',
                        text: 'Guide complet pour vendre son bien immobilier',
                        url: window.location.href,
                    });
                } catch (_) {
                    // no-op
                }
            } else {
                await navigator.clipboard.writeText(window.location.href);
                shareBtn.innerHTML = '<i class="fas fa-check"></i> Lien copié !';
                setTimeout(() => {
                    shareBtn.innerHTML = '<i class="fas fa-share-alt"></i> Partager';
                }, 2000);
            }
        });
    }
});
