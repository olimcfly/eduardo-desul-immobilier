(function () {
  'use strict';

  var STORAGE_KEY = 'rgpd_cookie_consent_v1';
  var API_ENDPOINT = '/rgpd/consents/cookie';
  var banner = document.querySelector('[data-rgpd-banner]');

  if (!banner) {
    return;
  }

  var saved = readSavedConsent();
  if (saved) {
    applyConsent(saved.categories);
    hideBanner();
    return;
  }

  banner.classList.remove('is-hidden');

  var acceptBtn = banner.querySelector('[data-consent-action="accept"]');
  var refuseBtn = banner.querySelector('[data-consent-action="refuse"]');
  var customBtn = banner.querySelector('[data-consent-action="customize"]');
  var saveBtn = banner.querySelector('[data-consent-action="save-custom"]');

  acceptBtn && acceptBtn.addEventListener('click', function () {
    persistConsent({ necessary: true, analytics: true, marketing: true });
  });

  refuseBtn && refuseBtn.addEventListener('click', function () {
    persistConsent({ necessary: true, analytics: false, marketing: false });
  });

  customBtn && customBtn.addEventListener('click', function () {
    banner.querySelector('[data-consent-custom-panel]').classList.toggle('is-hidden');
  });

  saveBtn && saveBtn.addEventListener('click', function () {
    var categories = {
      necessary: true,
      analytics: !!banner.querySelector('input[name="analytics_optin"]:checked'),
      marketing: !!banner.querySelector('input[name="marketing_optin"]:checked')
    };
    persistConsent(categories);
  });

  function persistConsent(categories) {
    var payload = {
      fingerprint: getFingerprint(),
      version: banner.dataset.policyVersion || 'v1',
      categories: categories,
      user_agent: navigator.userAgent,
      ip: ''
    };

    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    applyConsent(categories);
    hideBanner();

    fetch(API_ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
      credentials: 'same-origin'
    }).catch(function () {
      // Non-blocking; local proof still available for UX continuity.
    });
  }

  function applyConsent(categories) {
    var scripts = document.querySelectorAll('script[data-consent-category]');
    scripts.forEach(function (script) {
      var category = script.dataset.consentCategory;
      if (!categories[category]) {
        return;
      }

      if (script.dataset.loaded === '1') {
        return;
      }

      var executable = document.createElement('script');
      if (script.dataset.src) {
        executable.src = script.dataset.src;
      } else {
        executable.text = script.textContent;
      }
      executable.async = true;
      executable.dataset.loadedByConsent = '1';
      document.head.appendChild(executable);
      script.dataset.loaded = '1';
    });
  }

  function readSavedConsent() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) {
        return null;
      }
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  }

  function hideBanner() {
    banner.classList.add('is-hidden');
  }

  function getFingerprint() {
    var seed = [navigator.language, navigator.platform, screen.width + 'x' + screen.height].join('|');
    var hash = 0;
    for (var i = 0; i < seed.length; i += 1) {
      hash = ((hash << 5) - hash) + seed.charCodeAt(i);
      hash |= 0;
    }
    return 'anon-' + Math.abs(hash);
  }
})();
