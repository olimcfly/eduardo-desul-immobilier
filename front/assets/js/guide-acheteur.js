(function () {
  const progressBar = document.getElementById('readingProgress');
  const sideReadProgress = document.getElementById('sideReadProgress');
  const sections = Array.from(document.querySelectorAll('.step'));
  const activeStepLabel = document.getElementById('activeStepLabel');

  function updateReadingProgress() {
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const ratio = maxScroll > 0 ? (window.scrollY / maxScroll) : 0;
    const value = Math.max(0, Math.min(100, ratio * 100));
    if (progressBar) progressBar.style.width = value + '%';
    if (sideReadProgress) sideReadProgress.style.width = value + '%';
  }

  function updateActiveStep() {
    let current = 'Introduction';
    sections.forEach((section) => {
      const rect = section.getBoundingClientRect();
      if (rect.top < 180) {
        current = `Étape ${section.dataset.step}`;
      }
    });
    if (activeStepLabel) activeStepLabel.textContent = `Étape active : ${current}`;
  }

  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) entry.target.classList.add('is-visible');
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));

  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const strong = entry.target.querySelector('strong');
      if (!strong || strong.dataset.done) return;
      strong.dataset.done = '1';
      const text = strong.textContent.replace(/[^0-9]/g, '');
      const end = Number(text || 0);
      let n = 0;
      const timer = setInterval(() => {
        n += Math.max(1, Math.ceil(end / 20));
        if (n >= end) {
          n = end;
          clearInterval(timer);
        }
        strong.textContent = strong.textContent.replace(/[0-9]+/, String(n));
      }, 25);
    });
  }, { threshold: 0.4 });
  document.querySelectorAll('.hero-stats div').forEach((box) => counterObserver.observe(box));

  const groups = [
    ['Préparation', 5],
    ['Budget & banque', 5],
    ['Recherche', 5],
    ['Visites', 5],
    ['Offre', 4],
    ['Financement', 4],
    ['Signature', 4]
  ];
  const totalItems = 32;
  const storageKey = 'guideAcheteurChecklist';
  const checkGroups = document.getElementById('checkGroups');
  const checkProgress = document.getElementById('checkProgress');
  const checkProgressText = document.getElementById('checkProgressText');
  const completionMessage = document.getElementById('completionMessage');

  function readState() {
    try {
      return JSON.parse(localStorage.getItem(storageKey) || '{}');
    } catch (e) {
      return {};
    }
  }

  function saveState(state) {
    localStorage.setItem(storageKey, JSON.stringify(state));
  }

  function confettiBurst() {
    for (let i = 0; i < 18; i += 1) {
      const dot = document.createElement('span');
      dot.style.position = 'fixed';
      dot.style.left = `${50 + (Math.random() * 20 - 10)}vw`;
      dot.style.top = `${20 + Math.random() * 30}vh`;
      dot.style.width = '8px';
      dot.style.height = '8px';
      dot.style.borderRadius = '50%';
      dot.style.background = ['#0f4bd8', '#1fa971', '#f4b000'][i % 3];
      dot.style.zIndex = '9999';
      dot.style.transition = 'transform 900ms ease, opacity 900ms ease';
      document.body.appendChild(dot);
      requestAnimationFrame(() => {
        dot.style.transform = `translate(${(Math.random() * 220) - 110}px, ${200 + Math.random() * 120}px)`;
        dot.style.opacity = '0';
      });
      setTimeout(() => dot.remove(), 920);
    }
  }

  function updateChecklistUI() {
    const checked = document.querySelectorAll('.check-item input:checked').length;
    const ratio = checked / totalItems;
    const pct = Math.round(ratio * 100);
    if (checkProgress) {
      checkProgress.style.width = pct + '%';
      checkProgress.style.background = pct < 35 ? '#e5484d' : (pct < 70 ? '#f4b000' : '#1fa971');
    }
    if (checkProgressText) checkProgressText.textContent = `${checked} / ${totalItems}`;
    const done = checked === totalItems;
    if (completionMessage) completionMessage.hidden = !done;
    if (done) confettiBurst();
  }

  function renderChecklist() {
    if (!checkGroups) return;
    const state = readState();
    let idx = 0;
    groups.forEach(([groupName, size]) => {
      const group = document.createElement('section');
      group.className = 'check-group';
      const title = document.createElement('h4');
      title.textContent = groupName;
      group.appendChild(title);
      for (let i = 0; i < size; i += 1) {
        idx += 1;
        const id = `item-${idx}`;
        const label = document.createElement('label');
        label.className = 'check-item';
        const input = document.createElement('input');
        input.type = 'checkbox';
        input.checked = Boolean(state[id]);
        input.addEventListener('change', () => {
          const next = readState();
          next[id] = input.checked;
          saveState(next);
          updateChecklistUI();
        });
        const span = document.createElement('span');
        span.textContent = `Point ${idx}`;
        label.append(input, span);
        group.appendChild(label);
      }
      checkGroups.appendChild(group);
    });
    updateChecklistUI();
  }

  const resetBtn = document.getElementById('resetChecklist');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      localStorage.removeItem(storageKey);
      window.location.reload();
    });
  }

  const shareBtn = document.getElementById('shareBtn');
  if (shareBtn) {
    shareBtn.addEventListener('click', async () => {
      const data = { title: document.title, text: 'Guide acheteur immobilier', url: window.location.href };
      if (navigator.share) {
        await navigator.share(data);
        return;
      }
      await navigator.clipboard.writeText(window.location.href);
      shareBtn.textContent = 'Lien copié ✓';
      setTimeout(() => { shareBtn.textContent = 'Partager ce guide'; }, 1400);
    });
  }

  window.addEventListener('scroll', () => {
    updateReadingProgress();
    updateActiveStep();
  }, { passive: true });

  updateReadingProgress();
  updateActiveStep();
  renderChecklist();
}());
