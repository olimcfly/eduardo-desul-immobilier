/**
 * Estimateur Immobilier - Frontend Manager
 * Validation temps réel, AJAX, loading states, messages d'erreur
 */

(function () {
  // ═══════════════════════════════════════════════════════════
  // 🎯 ELEMENTS & CONFIG
  // ═══════════════════════════════════════════════════════════

  const form = document.getElementById('estimateur-form');
  if (!form) return;

  const submitBtn = form.querySelector('button[type="submit"]');
  const modeSelect = form.querySelector('[name="mode"]');
  const propertyTypeSelect = form.querySelector('[name="property_type"]');
  const surfaceInput = form.querySelector('[name="surface_m2"]');
  const roomsInput = form.querySelector('[name="rooms"]');
  const emailInput = form.querySelector('[name="contact_email"]');
  const phoneInput = form.querySelector('[name="phone"]');

  const API_ENDPOINT = '/admin/api/estimation/submit.php';
  const VALIDATION_DELAY = 500; // ms

  // ═══════════════════════════════════════════════════════════
  // 🔍 VALIDATORS
  // ═══════════════════════════════════════════════════════════

  const validators = {
    email: (value) => {
      const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return pattern.test(value);
    },

    phone: (value) => {
      if (!value) return true; // Optional field
      // French phone format: +33 or 0, followed by 9 digits
      const pattern = /^(?:(?:\+|00)33|0)[1-9](?:[0-9]{8})$/;
      return pattern.test(value.replace(/[\s\-\.]/g, ''));
    },

    surface: (value) => {
      const num = parseFloat(value);
      return !isNaN(num) && num >= 1 && num <= 10000;
    },

    rooms: (value) => {
      if (!value) return true; // Optional field
      const num = parseInt(value);
      return !isNaN(num) && num >= 1 && num <= 50;
    },
  };

  // ═══════════════════════════════════════════════════════════
  // 📝 ERROR MESSAGES
  // ═══════════════════════════════════════════════════════════

  const errorMessages = {
    email: 'Email invalide. Veuillez entrer une adresse email valide.',
    phone: 'Format téléphone invalide. Exemple: 06 12 34 56 78',
    surface: 'La surface doit être entre 1 et 10 000 m².',
    rooms: 'Le nombre de pièces doit être entre 1 et 50.',
    required: 'Ce champ est requis.',
    network: 'Erreur réseau. Veuillez vérifier votre connexion.',
    server: 'Erreur serveur. Veuillez réessayer plus tard.',
  };

  // ═══════════════════════════════════════════════════════════
  // 🎨 UI HELPERS
  // ═══════════════════════════════════════════════════════════

  /**
   * Show error/success message
   */
  function showMessage(type, message) {
    let messageEl = form.querySelector('.est-message');

    if (!messageEl) {
      messageEl = document.createElement('div');
      messageEl.className = 'est-message';
      form.insertBefore(messageEl, form.firstChild);
    }

    messageEl.className = `est-message est-message-${type}`;
    messageEl.textContent = message;
    messageEl.style.display = 'block';
    messageEl.setAttribute('role', 'alert');
    messageEl.setAttribute('aria-live', 'polite');

    // Auto-hide success messages after 5s
    if (type === 'success') {
      setTimeout(() => {
        messageEl.style.display = 'none';
      }, 5000);
    }

    // Scroll to message on mobile
    if (window.innerWidth < 768) {
      messageEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  /**
   * Clear all messages
   */
  function clearMessages() {
    const messageEl = form.querySelector('.est-message');
    if (messageEl) {
      messageEl.style.display = 'none';
    }
  }

  /**
   * Set loading state
   */
  function setLoading(isLoading) {
    if (isLoading) {
      submitBtn.disabled = true;
      submitBtn.classList.add('est-loading');
      submitBtn.innerHTML =
        '<span class="est-spinner"></span> Calcul en cours...';
      submitBtn.setAttribute('aria-busy', 'true');
    } else {
      submitBtn.disabled = false;
      submitBtn.classList.remove('est-loading');
      submitBtn.textContent = 'Calculer mon estimation';
      submitBtn.setAttribute('aria-busy', 'false');
    }
  }

  /**
   * Highlight field with error
   */
  function markFieldError(field, hasError) {
    const label = field.closest('label');
    if (!label) return;

    if (hasError) {
      label.classList.add('est-field-error');
      field.setAttribute('aria-invalid', 'true');
    } else {
      label.classList.remove('est-field-error');
      field.setAttribute('aria-invalid', 'false');
    }
  }

  // ═══════════════════════════════════════════════════════════
  // ✅ VALIDATION & REAL-TIME
  // ═══════════════════════════════════════════════════════════

  const validationTimers = {};

  /**
   * Validate single field
   */
  function validateField(field) {
    const name = field.name;
    const value = field.value.trim();

    // Check required
    if (field.hasAttribute('required') && !value) {
      markFieldError(field, true);
      return false;
    }

    // Run validator if exists
    if (validators[name] && value && !validators[name](value)) {
      markFieldError(field, true);
      return false;
    }

    markFieldError(field, false);
    return true;
  }

  /**
   * Validate entire form
   */
  function validateForm() {
    let isValid = true;
    const fieldsToValidate = [
      propertyTypeSelect,
      surfaceInput,
      emailInput,
      ...(phoneInput ? [phoneInput] : []),
    ];

    fieldsToValidate.forEach((field) => {
      if (!validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  /**
   * Set up real-time validation
   */
  function setupRealtimeValidation(field) {
    field.addEventListener('blur', () => {
      validateField(field);
    });

    field.addEventListener('input', () => {
      // Debounce validation while typing
      clearTimeout(validationTimers[field.name]);
      validationTimers[field.name] = setTimeout(() => {
        validateField(field);
      }, VALIDATION_DELAY);
    });
  }

  [emailInput, surfaceInput, roomsInput, phoneInput].forEach((field) => {
    if (field) {
      setupRealtimeValidation(field);
    }
  });

  // ═══════════════════════════════════════════════════════════
  // 🌐 MODE SWITCHING
  // ═══════════════════════════════════════════════════════════

  if (modeSelect) {
    modeSelect.addEventListener('change', function (e) {
      clearMessages();
      document.body.dataset.estimateurMode = e.target.value;

      // In quick mode, make rooms optional; in advanced, all fields matter
      if (roomsInput) {
        if (e.target.value === 'quick') {
          roomsInput.removeAttribute('required');
        } else {
          roomsInput.setAttribute('required', 'required');
        }
      }
    });
  }

  // ═══════════════════════════════════════════════════════════
  // 📤 AJAX SUBMISSION
  // ═══════════════════════════════════════════════════════════

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    clearMessages();

    // Validate form
    if (!validateForm()) {
      showMessage('error', 'Veuillez corriger les erreurs ci-dessus.');
      return;
    }

    // Prepare form data
    const formData = new FormData(form);

    // Normalize phone (remove spaces/dashes)
    if (formData.get('phone')) {
      formData.set('phone', formData.get('phone').replace(/[\s\-\.]/g, ''));
    }

    setLoading(true);

    try {
      const response = await fetch(API_ENDPOINT, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        const errorMsg =
          data.message ||
          errorMessages.server ||
          'Une erreur est survenue';
        showMessage('error', errorMsg);
        setLoading(false);
        return;
      }

      // Success: show estimation result
      showEstimationResult(data.data);
      showMessage('success', 'Estimation calculée ! Consultez le résultat ci-dessous.');

    } catch (error) {
      console.error('Submission error:', error);
      showMessage('error', errorMessages.network);
    } finally {
      setLoading(false);
    }
  });

  // ═══════════════════════════════════════════════════════════
  // 📊 DISPLAY ESTIMATION RESULT
  // ═══════════════════════════════════════════════════════════

  function showEstimationResult(data) {
    let resultEl = document.getElementById('estimateur-result');

    if (!resultEl) {
      resultEl = document.createElement('div');
      resultEl.id = 'estimateur-result';
      resultEl.className = 'est-card est-result-card';
      form.parentNode.insertBefore(resultEl, form.nextSibling);
    }

    const {
      property_type,
      surface_m2,
      price_per_sqm,
      estimated_price,
      min_price,
      max_price,
    } = data;

    const formatPrice = (price) =>
      new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
      }).format(price);

    resultEl.innerHTML = `
      <h3>✓ Votre estimation</h3>
      <div class="est-result-grid">
        <div class="est-result-item">
          <strong>Type de bien</strong>
          <span>${escapeHtml(property_type)}</span>
        </div>
        <div class="est-result-item">
          <strong>Surface</strong>
          <span>${parseInt(surface_m2)} m²</span>
        </div>
        <div class="est-result-item">
          <strong>Prix au m²</strong>
          <span>${formatPrice(price_per_sqm)}</span>
        </div>
        <div class="est-result-item est-result-highlight">
          <strong>Estimation</strong>
          <span class="est-result-price">${formatPrice(estimated_price)}</span>
        </div>
        <div class="est-result-item">
          <strong>Fourchette</strong>
          <span>${formatPrice(min_price)} - ${formatPrice(max_price)}</span>
        </div>
      </div>
      <p class="est-result-note">
        💡 Cette estimation est indicative et basée sur les données du marché.
        Pour une évaluation précise, contactez un conseiller.
      </p>
    `;

    // Scroll to result on mobile
    if (window.innerWidth < 768) {
      resultEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  // ═══════════════════════════════════════════════════════════
  // 🛡️ UTILITIES
  // ═══════════════════════════════════════════════════════════

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // ═══════════════════════════════════════════════════════════
  // 📱 RESPONSIVE BEHAVIOR
  // ═══════════════════════════════════════════════════════════

  // Adjust form layout on mobile
  function adjustMobileLayout() {
    const isMobile = window.innerWidth < 768;
    const grid = form.querySelector('.est-grid');

    if (grid) {
      if (isMobile) {
        grid.style.gridTemplateColumns = '1fr';
      } else {
        grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
      }
    }

    // Increase button padding on mobile for better touch targets
    if (isMobile) {
      submitBtn.style.padding = '14px 20px';
      submitBtn.style.fontSize = '16px'; // Prevent zoom on iOS
    }
  }

  adjustMobileLayout();
  window.addEventListener('resize', adjustMobileLayout);

  // ═══════════════════════════════════════════════════════════
  // 🔧 INITIALIZATION
  // ═══════════════════════════════════════════════════════════

  console.log('✓ Estimateur frontend loaded');
})();
