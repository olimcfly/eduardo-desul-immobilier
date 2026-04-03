(() => {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  // Reading progress bar
  const readingBar = $('#readingProgress');
  const articleBar = $('#articleProgressBar');
  const onScrollProgress = () => {
    const doc = document.documentElement;
    const ratio = ((doc.scrollTop || document.body.scrollTop) / (doc.scrollHeight - doc.clientHeight)) * 100;
    if (readingBar) readingBar.style.width = `${Math.max(0, Math.min(100, ratio))}%`;
    if (articleBar) articleBar.style.width = `${Math.max(0, Math.min(100, ratio))}%`;
  };
  document.addEventListener('scroll', onScrollProgress, { passive: true });
  onScrollProgress();

  // Active step tracking
  const steps = $$('.step');
  const activeStepLabel = $('#activeStepLabel');
  const stepObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const title = entry.target.getAttribute('data-step-title') || 'Intro';
      if (activeStepLabel) activeStepLabel.textContent = `Section en cours : ${title}`;
    });
  }, { rootMargin: '-40% 0px -45% 0px', threshold: [0.2] });
  steps.forEach(step => stepObserver.observe(step));

  // Checklist localStorage + progress + reset
  const listConfig = [
    ['Estimation', ['Définir objectif délai', 'Analyser 3 ventes comparables', 'Vérifier tension du secteur', 'Lister points forts', 'Lister points faibles', 'Préparer stratégie prix']],
    ['Documents', ['Titre propriété', 'Diagnostics obligatoires', 'Taxe foncière', 'Charges copropriété', 'PV AG récents', 'Factures travaux']],
    ['Valorisation', ['Désencombrer', 'Nettoyage pro', 'Réparations mineures', 'Photos pro', 'Texte annonce impactant', 'Plan de diffusion']],
    ['Visites & offres', ['Planifier créneaux', 'Préparer réponses FAQ', 'Recueillir feedback', 'Relancer acquéreurs', 'Comparer solidité dossiers', 'Négocier net vendeur']],
    ['Signature', ['Choisir notaire', 'Signer compromis', 'Suivre financement', 'Valider conditions suspensives', 'Préparer déménagement']]
  ];
  const allItems = listConfig.flatMap(([g, items], gi) => items.map((it, ii) => ({ id: `c_${gi}_${ii}`, label: it, group: g })));
  const KEY = 'guide_vendeur_checklist_v1';
  const checklistRoot = $('#checklistGroups');
  const checkCount = $('#checkCount');
  const checkBar = $('#checkProgressBar');

  const state = (() => {
    try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch { return {}; }
  })();

  if (checklistRoot) {
    listConfig.forEach(([group, items], gi) => {
      const wrap = document.createElement('section');
      wrap.className = 'check-group';
      wrap.innerHTML = `<h3>${group}</h3><div class="check-grid"></div>`;
      const grid = $('.check-grid', wrap);
      items.forEach((label, ii) => {
        const id = `c_${gi}_${ii}`;
        const row = document.createElement('label');
        row.className = 'check-item';
        row.innerHTML = `<input type="checkbox" data-id="${id}" ${state[id] ? 'checked' : ''}> <span>${label}</span>`;
        grid.appendChild(row);
      });
      checklistRoot.appendChild(wrap);
    });
  }

  const updateChecklistUi = () => {
    const checked = $$('input[type="checkbox"][data-id]:checked').length;
    const total = allItems.length;
    const pct = total ? (checked / total) * 100 : 0;
    if (checkCount) checkCount.textContent = String(checked);
    if (checkBar) checkBar.style.width = `${pct}%`;
  };

  document.addEventListener('change', (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement) || !t.matches('input[type="checkbox"][data-id]')) return;
    state[t.dataset.id] = t.checked;
    localStorage.setItem(KEY, JSON.stringify(state));
    updateChecklistUi();
  });

  $('#resetChecklist')?.addEventListener('click', () => {
    Object.keys(state).forEach(k => delete state[k]);
    localStorage.removeItem(KEY);
    $$('input[type="checkbox"][data-id]').forEach((cb) => { cb.checked = false; });
    updateChecklistUi();
  });
  updateChecklistUi();

  // Doc tabs
  $$('[data-tabs]').forEach((tabsRoot) => {
    const buttons = $$('.tab-btn', tabsRoot);
    const panels = $$('.tab-panel', tabsRoot);
    buttons.forEach((btn) => btn.addEventListener('click', () => {
      const tab = btn.getAttribute('data-tab');
      buttons.forEach(b => b.classList.toggle('is-active', b === btn));
      panels.forEach(p => p.classList.toggle('is-active', p.getAttribute('data-panel') === tab));
    }));
  });

  // Scroll reveal
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) entry.target.classList.add('is-visible');
    });
  }, { threshold: 0.1 });
  $$('.reveal').forEach(el => revealObs.observe(el));

  // Counter animation
  const counters = $$('[data-counter]');
  const animateCounter = (el) => {
    const target = Number(el.getAttribute('data-counter') || '0');
    const start = performance.now();
    const duration = 900;
    const step = (now) => {
      const p = Math.min((now - start) / duration, 1);
      el.textContent = String(Math.round(target * p));
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  const counterObs = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      animateCounter(entry.target);
      obs.unobserve(entry.target);
    });
  }, { threshold: 0.4 });
  counters.forEach(c => counterObs.observe(c));

  // Share API
  const shareBtn = document.createElement('button');
  shareBtn.type = 'button';
  shareBtn.className = 'btn btn-light';
  shareBtn.textContent = 'Partager ce guide';
  shareBtn.addEventListener('click', async () => {
    const payload = { title: document.title, text: 'Guide vendeur immobilier', url: location.href };
    try {
      if (navigator.share) await navigator.share(payload);
      else {
        await navigator.clipboard.writeText(location.href);
        shareBtn.textContent = 'Lien copié ✓';
        setTimeout(() => { shareBtn.textContent = 'Partager ce guide'; }, 1500);
      }
    } catch (_) {}
  });
  document.querySelector('#final-cta')?.appendChild(shareBtn);
})();
