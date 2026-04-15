/* ============================================================
   BIEN DETAIL JS — Pascal Hamm Immobilier
   ============================================================ */

(function () {
  'use strict';

  /* ── Galerie photos ────────────────────────────────────── */
  const photos     = window.GALLERY_PHOTOS || [];
  let   currentIdx = 0;

  const mainImg   = document.getElementById('galleryMainImg');
  const counter   = document.getElementById('galleryCurrentIdx');
  const prevBtn   = document.getElementById('galleryPrev');
  const nextBtn   = document.getElementById('galleryNext');
  const fullBtn   = document.getElementById('galleryFullscreen');
  const thumbs    = document.querySelectorAll('.bien-gallery__thumb');

  function setPhoto(idx) {
    if (!photos.length || !mainImg) return;
    currentIdx = (idx + photos.length) % photos.length;
    mainImg.src = photos[currentIdx];
    if (counter) counter.textContent = currentIdx + 1;
    thumbs.forEach((t, i) => t.classList.toggle('is-active', i === currentIdx));
  }

  if (prevBtn) prevBtn.addEventListener('click', () => setPhoto(currentIdx - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => setPhoto(currentIdx + 1));

  thumbs.forEach((thumb, i) => {
    thumb.addEventListener('click', () => setPhoto(i));
  });

  /* Touch/swipe galerie */
  if (mainImg) {
    let touchStartX = 0;
    mainImg.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
    mainImg.addEventListener('touchend',   e => {
      const dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 40) setPhoto(currentIdx + (dx < 0 ? 1 : -1));
    });
  }

  /* ── Lightbox ──────────────────────────────────────────── */
  const lightbox     = document.getElementById('bien-lightbox');
  const lbOverlay    = document.getElementById('lightboxOverlay');
  const lbClose      = document.getElementById('lightboxClose');
  const lbPrev       = document.getElementById('lightboxPrev');
  const lbNext       = document.getElementById('lightboxNext');
  const lbImg        = document.getElementById('lightboxImg');
  const lbIdx        = document.getElementById('lightboxIdx');

  function openLightbox(idx) {
    if (!lightbox || !photos.length) return;
    currentIdx = (idx + photos.length) % photos.length;
    lbImg.src  = photos[currentIdx];
    if (lbIdx) lbIdx.textContent = currentIdx + 1;
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function lbSetPhoto(idx) {
    currentIdx = (idx + photos.length) % photos.length;
    lbImg.src  = photos[currentIdx];
    if (lbIdx) lbIdx.textContent = currentIdx + 1;
    setPhoto(currentIdx);
  }

  if (fullBtn) fullBtn.addEventListener('click',   () => openLightbox(currentIdx));
  if (mainImg && photos.length > 0) mainImg.addEventListener('click', () => openLightbox(currentIdx));
  if (lbClose)   lbClose.addEventListener('click', closeLightbox);
  if (lbOverlay) lbOverlay.addEventListener('click', closeLightbox);
  if (lbPrev)    lbPrev.addEventListener('click',  () => lbSetPhoto(currentIdx - 1));
  if (lbNext)    lbNext.addEventListener('click',  () => lbSetPhoto(currentIdx + 1));

  /* Clavier lightbox */
  document.addEventListener('keydown', e => {
    if (!lightbox || !lightbox.classList.contains('is-open')) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  lbSetPhoto(currentIdx - 1);
    if (e.key === 'ArrowRight') lbSetPhoto(currentIdx + 1);
  });

  /* ── Toggle description ────────────────────────────────── */
  const descToggle = document.getElementById('descToggle');
  const descText   = document.getElementById('descText');

  if (descToggle && descText) {
    descToggle.addEventListener('click', () => {
      const open = descText.classList.toggle('is-expanded');
      descToggle.classList.toggle('is-open', open);
      descToggle.innerHTML = open
        ? '<i class="fas fa-chevron-up"></i> Réduire'
        : '<i class="fas fa-chevron-down"></i> Lire la suite';
    });
  }

  /* ── Favoris localStorage ─────────────────────────────── */
  const favBtn   = document.getElementById('favBtn');
  const favIcon  = document.getElementById('favIcon');
  const favLabel = document.getElementById('favLabel');

  if (favBtn) {
    const bienId  = favBtn.dataset.id;
    const STORAGE = 'ph_favoris';

    function getFavs() {
      try { return JSON.parse(localStorage.getItem(STORAGE) || '[]'); } catch { return []; }
    }

    function syncFavUI() {
      const isFav = getFavs().includes(bienId);
      favBtn.classList.toggle('is-fav', isFav);
      favIcon.className = isFav ? 'fas fa-heart' : 'far fa-heart';
      if (favLabel) favLabel.textContent = isFav ? 'Retirer des favoris' : 'Ajouter aux favoris';
    }

    favBtn.addEventListener('click', () => {
      let favs = getFavs();
      if (favs.includes(bienId)) {
        favs = favs.filter(id => id !== bienId);
      } else {
        favs.push(bienId);
      }
      localStorage.setItem(STORAGE, JSON.stringify(favs));
      syncFavUI();
    });

    syncFavUI();
  }

  /* ── Partage — copier lien ─────────────────────────────── */
  const shareCopy = document.getElementById('shareCopy');
  if (shareCopy) {
    shareCopy.addEventListener('click', async () => {
      const url = shareCopy.dataset.url || window.location.href;
      try {
        await navigator.clipboard.writeText(url);
        const orig = shareCopy.innerHTML;
        shareCopy.innerHTML = '<i class="fas fa-check"></i> Lien copié !';
        shareCopy.style.color = '#16a34a';
        setTimeout(() => {
          shareCopy.innerHTML = orig;
          shareCopy.style.color = '';
        }, 2000);
      } catch {
        prompt('Copiez ce lien :', url);
      }
    });
  }

  /* ── Carte Leaflet ─────────────────────────────────────── */
  const mapEl = document.getElementById('bienMap');
  if (mapEl && typeof L !== 'undefined') {
    const lat   = parseFloat(mapEl.dataset.lat);
    const lng   = parseFloat(mapEl.dataset.lng);
    const titre = mapEl.dataset.titre || '';

    if (!isNaN(lat) && !isNaN(lng)) {
      const map = L.map('bienMap', { scrollWheelZoom: false }).setView([lat, lng], 15);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
      }).addTo(map);

      const icon = L.divIcon({
        html: '<div style="width:36px;height:36px;background:var(--clr-primary);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid var(--clr-accent);box-shadow:0 4px 12px rgba(0,0,0,.3)"></div>',
        iconSize:   [36, 36],
        iconAnchor: [18, 36],
        className:  '',
      });

      L.marker([lat, lng], { icon })
        .addTo(map)
        .bindPopup(`<strong>${titre}</strong>`)
        .openPopup();
    }
  }

  /* ── Animations scroll ─────────────────────────────────── */
  if ('IntersectionObserver' in window) {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('animated'); obs.unobserve(e.target); } });
    }, { threshold: 0.1 });
    document.querySelectorAll('[data-animate]').forEach(el => obs.observe(el));
  }

})();
