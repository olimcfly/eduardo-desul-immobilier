/**
 * ESTIMATION TUNNEL — Frontend logic
 * Gère la navigation multi-étapes, l'appel AJAX et la conversion.
 */
(function () {
  'use strict';

  // ── État global ──────────────────────────────────────────────
  let currentStep = 1;
  let requestId   = null;
  let currentAction = null;

  // ── Helpers DOM ──────────────────────────────────────────────
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  function show(el) { if (el) el.hidden = false; }
  function hide(el) { if (el) el.hidden = true; }

  function setError(fieldId, msg) {
    const el = document.getElementById(fieldId);
    if (!el) return;
    el.textContent = msg;
    el.hidden = !msg;
    const input = el.previousElementSibling || el.closest('.form-group')?.querySelector('.form-control');
    if (input) input.classList.toggle('has-error', !!msg);
  }
  function clearErrors() {
    $$('.field-error').forEach(el => { el.hidden = true; el.textContent = ''; });
    $$('.form-control').forEach(el => el.classList.remove('has-error'));
  }

  function getCsrfToken() {
    const field = document.querySelector('#convert-csrf input[name="csrf_token"]');
    return field ? field.value : '';
  }

  // ── Navigation ───────────────────────────────────────────────
  function goToStep(n) {
    $$('.tunnel-step').forEach(el => {
      el.hidden = true;
      el.classList.remove('tunnel-step--active');
    });

    const target = document.getElementById('step-' + n);
    if (!target) return;

    target.hidden = false;
    target.classList.add('tunnel-step--active');
    currentStep = n;

    // Progress bar
    const pct = { 1: 33, 2: 66, 3: 100, 4: 100 };
    const bar = document.getElementById('tunnel-progress-bar');
    if (bar) bar.style.width = (pct[n] || 33) + '%';

    // Label
    const label = document.getElementById('tunnel-step-label');
    if (label) label.textContent = n <= 3 ? 'Étape ' + n + ' / 3' : 'Vos coordonnées';

    window.scrollTo({ top: document.getElementById('tunnel-app')?.offsetTop - 80 || 0, behavior: 'smooth' });
  }

  // ── Étape 1 → Étape 2 ────────────────────────────────────────
  function validateStep1() {
    clearErrors();
    let ok = true;
    const type    = $('input[name="property_type"]:checked');
    const surface = document.getElementById('t-surface');

    if (!type) {
      setError('err-type', 'Veuillez sélectionner un type de bien.');
      ok = false;
    }
    const surfVal = parseFloat(surface?.value || '');
    if (!surface || isNaN(surfVal) || surfVal < 10 || surfVal > 5000) {
      setError('err-surface', 'Surface invalide (entre 10 et 5 000 m²).');
      ok = false;
    }
    return ok;
  }

  document.getElementById('btn-step1-next')?.addEventListener('click', () => {
    if (validateStep1()) goToStep(2);
  });

  // ── Étape 2 : Géolocalisation ─────────────────────────────────
  document.getElementById('btn-geolocate')?.addEventListener('click', () => {
    const btn    = document.getElementById('btn-geolocate');
    const txt    = document.getElementById('geo-btn-text');
    const status = document.getElementById('geo-status');

    if (!navigator.geolocation) {
      status.textContent = 'Géolocalisation non disponible.';
      status.className   = 'geo-status error';
      return;
    }

    btn.disabled  = true;
    txt.textContent = '⏳ Localisation…';
    status.textContent = '';
    status.className   = 'geo-status';

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        document.getElementById('t-lat').value = pos.coords.latitude.toFixed(6);
        document.getElementById('t-lng').value = pos.coords.longitude.toFixed(6);
        txt.textContent = '📍 Position obtenue';
        status.textContent = 'Coordonnées enregistrées.';
        status.className   = 'geo-status success';
        btn.disabled = false;
      },
      () => {
        txt.textContent    = '📍 Utiliser ma position';
        status.textContent = 'Impossible d\'accéder à la position.';
        status.className   = 'geo-status error';
        btn.disabled = false;
      },
      { timeout: 8000, maximumAge: 60000 }
    );
  });

  // ── Étape 2 → back ───────────────────────────────────────────
  document.getElementById('btn-step2-back')?.addEventListener('click', () => goToStep(1));

  // ── Étape 2 → Calcul ─────────────────────────────────────────
  function validateStep2() {
    clearErrors();
    let ok = true;
    const city   = document.getElementById('t-city');
    const postal = document.getElementById('t-postal');

    if ((!city?.value?.trim()) && (!postal?.value?.trim())) {
      setError('err-city', 'Veuillez indiquer une ville ou un code postal.');
      ok = false;
    }
    if (postal?.value?.trim() && !/^\d{4,5}$/.test(postal.value.trim())) {
      setError('err-postal', 'Code postal invalide.');
      ok = false;
    }
    return ok;
  }

  document.getElementById('btn-step2-next')?.addEventListener('click', () => {
    if (validateStep2()) {
      goToStep(3);
      doCalculate();
    }
  });

  // ── Calcul AJAX ───────────────────────────────────────────────
  async function doCalculate() {
    // Afficher le spinner
    show(document.getElementById('result-loading'));
    hide(document.getElementById('result-ok'));
    hide(document.getElementById('result-insufficient'));
    hide(document.getElementById('result-error'));

    const payload = {
      property_type:  $('input[name="property_type"]:checked')?.value || '',
      surface:        parseFloat(document.getElementById('t-surface')?.value || 0),
      valuation_mode: $('input[name="valuation_mode"]:checked')?.value || 'sold',
      ville:          document.getElementById('t-city')?.value?.trim() || '',
      postal_code:    document.getElementById('t-postal')?.value?.trim() || '',
      lat:            document.getElementById('t-lat')?.value || '',
      lng:            document.getElementById('t-lng')?.value || '',
      rooms:          document.getElementById('t-rooms')?.value || '',
      csrf_token:     getCsrfToken(),
    };

    try {
      const res  = await fetch('/api/estimation/calculate', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });
      const json = await res.json();

      hide(document.getElementById('result-loading'));

      if (!res.ok || !json.ok) {
        if (json.status === 'insufficient_data') {
          document.getElementById('result-insufficient-msg').textContent = json.message || '';
          show(document.getElementById('result-insufficient'));
        } else {
          show(document.getElementById('result-error'));
        }
        return;
      }

      // Stocker l'ID pour la conversion
      requestId = json.request_id;

      // Remplir la fourchette
      const d = json.data;
      document.getElementById('price-low').textContent  = d.low    || '—';
      document.getElementById('price-med').textContent  = d.median || '—';
      document.getElementById('price-high').textContent = d.high   || '—';

      // Récap
      const typeLabel = payload.property_type.charAt(0).toUpperCase() + payload.property_type.slice(1);
      const loc       = payload.ville || payload.postal_code;
      document.getElementById('result-recap').innerHTML =
        '<strong>' + typeLabel + '</strong> · ' + payload.surface + ' m²' + (loc ? ' · ' + loc : '');

      // Fiabilité
      const score = d.reliability_score || 0;
      const comp  = d.comparables_count || 0;
      document.getElementById('result-reliability').textContent =
        '📊 ' + comp + ' comparables · Score ' + score + '/100';

      // Pré-remplir l'ID de request dans le formulaire conversion
      document.getElementById('c-request-id').value = requestId;

      show(document.getElementById('result-ok'));

    } catch (err) {
      console.error('[tunnel] calculate error:', err);
      hide(document.getElementById('result-loading'));
      show(document.getElementById('result-error'));
    }
  }

  // ── CTAs de conversion ────────────────────────────────────────
  const _advisorName = (window.__APP_SETTINGS__ && window.__APP_SETTINGS__.advisorName)
    ? window.__APP_SETTINGS__.advisorName
    : 'votre conseiller';

  const ctaTitles = {
    email_report:    'Recevoir le rapport par email',
    contact_request: 'Être rappelé par ' + _advisorName,
    rdv_request:     'Prendre rendez-vous',
  };
  const ctaSubs = {
    email_report:    'Indiquez votre email pour recevoir votre rapport d\'estimation détaillé.',
    contact_request: 'Indiquez vos coordonnées et ' + _advisorName + ' vous contactera rapidement.',
    rdv_request:     'Indiquez vos coordonnées pour confirmer un créneau avec ' + _advisorName + '.',
  };
  const ctaSubmitLabels = {
    email_report:    'Envoyer le rapport',
    contact_request: 'Demander un rappel',
    rdv_request:     'Confirmer la demande de RDV',
  };
  const ctaSuccessTitles = {
    email_report:    'Rapport envoyé !',
    contact_request: 'Demande reçue !',
    rdv_request:     'Demande de RDV reçue !',
  };
  const ctaSuccessMessages = {
    email_report:    'Vérifiez votre boîte email (pensez aux spams si vous ne le voyez pas).',
    contact_request: _advisorName + ' vous contactera dans les meilleurs délais.',
    rdv_request:     _advisorName + ' reviendra vers vous pour confirmer un créneau.',
  };

  $$('.cta-trigger').forEach(btn => {
    btn.addEventListener('click', () => {
      const action = btn.dataset.action;
      openConvertStep(action);
    });
  });

  function openConvertStep(action) {
    currentAction = action;

    // Mettre à jour les textes
    document.getElementById('convert-title').textContent = ctaTitles[action] || 'Vos coordonnées';
    document.getElementById('convert-sub').textContent   = ctaSubs[action]   || '';
    document.getElementById('convert-submit-text').textContent = ctaSubmitLabels[action] || 'Envoyer';
    document.getElementById('c-action-type').value = action;

    // Afficher/masquer les champs optionnels
    const showPhone = action === 'contact_request' || action === 'rdv_request';
    const showMsg   = action === 'contact_request';
    document.getElementById('phone-group').hidden   = !showPhone;
    document.getElementById('message-group').hidden = !showMsg;

    // Réinitialiser le formulaire
    hide(document.getElementById('convert-success'));
    show(document.getElementById('convert-form'));
    document.getElementById('convert-form').reset();
    document.getElementById('c-action-type').value  = action;
    document.getElementById('c-request-id').value   = requestId;
    clearErrors();

    goToStep(4);
  }

  // ── Retour depuis étape 4 ─────────────────────────────────────
  document.getElementById('btn-convert-back')?.addEventListener('click', () => goToStep(3));

  // ── Submit conversion ─────────────────────────────────────────
  document.getElementById('convert-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();

    const firstName = document.getElementById('c-firstname')?.value?.trim() || '';
    const email     = document.getElementById('c-email')?.value?.trim() || '';
    let   ok        = true;

    if (!firstName) {
      setError('err-firstname', 'Veuillez indiquer votre prénom.');
      ok = false;
    }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setError('err-email', 'Adresse email invalide.');
      ok = false;
    }
    if (!ok) return;

    // Afficher spinner
    const submitBtn    = document.getElementById('btn-convert-submit');
    const submitText   = document.getElementById('convert-submit-text');
    const submitSpinner = document.getElementById('convert-spinner');
    submitBtn.disabled = true;
    hide(submitText);
    show(submitSpinner);

    const payload = {
      request_id:  requestId,
      action_type: document.getElementById('c-action-type')?.value || '',
      first_name:  firstName,
      last_name:   document.getElementById('c-lastname')?.value?.trim() || '',
      email:       email,
      phone:       document.getElementById('c-phone')?.value?.trim() || '',
      message:     document.getElementById('c-message')?.value?.trim() || '',
      csrf_token:  getCsrfToken(),
    };

    try {
      const res  = await fetch('/api/estimation/convert', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });
      const json = await res.json();

      if (!res.ok || !json.ok) {
        // Afficher l'erreur
        setError('err-email', json.message || 'Une erreur est survenue. Veuillez réessayer.');
        submitBtn.disabled = false;
        show(submitText);
        hide(submitSpinner);
        return;
      }

      // Succès
      hide(document.getElementById('convert-form'));
      const successEl = document.getElementById('convert-success');
      document.getElementById('convert-success-title').textContent  = ctaSuccessTitles[currentAction] || 'Envoyé !';
      document.getElementById('convert-success-msg').textContent    = json.message || ctaSuccessMessages[currentAction] || '';
      show(successEl);

    } catch (err) {
      console.error('[tunnel] convert error:', err);
      setError('err-email', 'Erreur réseau. Veuillez réessayer.');
      submitBtn.disabled = false;
      show(submitText);
      hide(submitSpinner);
    }
  });

  // ── Redémarrer le tunnel ──────────────────────────────────────
  function restartTunnel() {
    requestId     = null;
    currentAction = null;
    clearErrors();
    // Reset étape 1
    $$('input[name="property_type"]').forEach(r => r.checked = r.value === 'appartement');
    $$('input[name="valuation_mode"]').forEach(r => r.checked = r.value === 'sold');
    const surf = document.getElementById('t-surface');
    if (surf) surf.value = '';
    const rooms = document.getElementById('t-rooms');
    if (rooms) rooms.value = '';
    // Reset étape 2
    const city = document.getElementById('t-city');
    if (city) city.value = '';
    const postal = document.getElementById('t-postal');
    if (postal) postal.value = '';
    document.getElementById('t-lat').value = '';
    document.getElementById('t-lng').value = '';
    const geoStatus = document.getElementById('geo-status');
    if (geoStatus) { geoStatus.textContent = ''; geoStatus.className = 'geo-status'; }
    const geoText = document.getElementById('geo-btn-text');
    if (geoText) geoText.textContent = '📍 Utiliser ma position';
    goToStep(1);
  }

  ['btn-restart', 'btn-restart-2', 'btn-restart-3'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', restartTunnel);
  });

  // ── Type card accessibility ───────────────────────────────────
  $$('.type-card').forEach(card => {
    card.addEventListener('keydown', e => {
      if (e.key === ' ' || e.key === 'Enter') {
        e.preventDefault();
        card.querySelector('input')?.click();
      }
    });
    card.setAttribute('tabindex', '0');
  });

  // Init
  goToStep(1);

})();
