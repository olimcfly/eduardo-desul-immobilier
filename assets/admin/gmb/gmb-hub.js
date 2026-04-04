/*
 * HUB GMB (Google My Business)
 * Endpoints par défaut :
 * - GET  /admin/api/gmb/stats
 * - POST /admin/api/gmb/sync
 * - POST /admin/api/gmb/request-review/test
 */
(function () {
  'use strict';

  var ENDPOINTS = {
    stats: '/admin/api/gmb/stats',
    sync: '/admin/api/gmb/sync',
    requestReviewTest: '/admin/api/gmb/request-review/test'
  };

  function showToast(type, message) {
    var toast = document.createElement('div');
    toast.className = 'gmb-toast gmb-toast-' + (type || 'info');
    toast.textContent = message;
    document.body.appendChild(toast);
    window.setTimeout(function () {
      toast.classList.add('is-visible');
    }, 10);
    window.setTimeout(function () {
      toast.classList.remove('is-visible');
      window.setTimeout(function () {
        toast.remove();
      }, 200);
    }, 3200);
  }

  function setLoading(btn, isLoading) {
    if (!btn) return;
    btn.classList.toggle('disabled', !!isLoading);
    btn.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
    btn.style.pointerEvents = isLoading ? 'none' : '';
  }

  function filterModules(query) {
    var q = String(query || '').toLowerCase().trim();
    var cards = document.querySelectorAll('#gmb-modules-grid .seo-card');
    cards.forEach(function (card) {
      var haystack = (card.getAttribute('data-module') || '').toLowerCase();
      card.style.display = haystack.indexOf(q) !== -1 ? '' : 'none';
    });
  }

  window.filterModules = window.filterModules || filterModules;

  function updateStats(stats) {
    var listing = document.getElementById('gmb-listing-status');
    var reviews = document.getElementById('gmb-reviews-meta');
    var sync = document.getElementById('gmb-last-sync');
    var score = document.getElementById('gmb-crawl-score');

    if (listing) {
      listing.textContent = stats.listing_exists ? 'Fiche connectée' : 'Fiche non connectée';
    }
    if (reviews) {
      var count = Number(stats.reviews_count || 0);
      var rating = Number(stats.reviews_rating || 0).toFixed(1);
      reviews.textContent = count + ' avis · ' + rating + '/5';
    }
    if (sync) {
      sync.textContent = stats.last_sync || 'Jamais';
    }
    if (score) {
      var value = stats.last_crawl_score !== null && stats.last_crawl_score !== undefined
        ? String(Number(stats.last_crawl_score)) + '/100'
        : 'N/A';
      score.textContent = 'Dernier score : ' + value;
    }
  }

  function fetchJson(url, options) {
    return fetch(url, options).then(function (res) {
      if (!res.ok) {
        throw new Error('HTTP ' + res.status);
      }
      return res.json();
    });
  }

  function pollSyncJob(jobId, attemptsLeft) {
    if (!jobId || attemptsLeft <= 0) return;

    // TODO: brancher un endpoint de statut réel (ex: /admin/api/gmb/sync-status?job_id=...)
    window.setTimeout(function () {
      showToast('info', 'Synchronisation en cours… (job ' + jobId + ')');
    }, 1200);
  }

  function bindActions() {
    var syncBtn = document.getElementById('gmb-sync-now-btn');
    var requestBtn = document.getElementById('gmb-request-review-btn');

    if (syncBtn) {
      syncBtn.addEventListener('click', function (event) {
        event.preventDefault();
        setLoading(syncBtn, true);
        fetchJson(ENDPOINTS.sync, {
          method: 'POST',
          headers: { 'Accept': 'application/json' }
        })
          .then(function (payload) {
            showToast('success', payload.message || 'Synchronisation lancée.');
            if (payload.job_id) {
              pollSyncJob(payload.job_id, 3);
            }
            return fetchJson(ENDPOINTS.stats, { headers: { 'Accept': 'application/json' } });
          })
          .then(updateStats)
          .catch(function () {
            showToast('error', 'Impossible de lancer la synchronisation. Fallback lien classique activé.');
            window.location.href = syncBtn.getAttribute('href') || '/admin?module=gmb&action=stats';
          })
          .finally(function () {
            setLoading(syncBtn, false);
          });
      });
    }

    if (requestBtn) {
      requestBtn.addEventListener('click', function (event) {
        event.preventDefault();
        setLoading(requestBtn, true);
        fetchJson(ENDPOINTS.requestReviewTest, {
          method: 'POST',
          headers: { 'Accept': 'application/json' }
        })
          .then(function (payload) {
            showToast('success', payload.message || 'Demande d\'avis de test envoyée.');
          })
          .catch(function () {
            showToast('error', 'Échec envoi test. Fallback lien classique activé.');
            window.location.href = requestBtn.getAttribute('href') || '/admin?module=gmb&action=review-requests';
          })
          .finally(function () {
            setLoading(requestBtn, false);
          });
      });
    }
  }

  function initStats() {
    fetchJson(ENDPOINTS.stats, { headers: { 'Accept': 'application/json' } })
      .then(updateStats)
      .catch(function () {
        showToast('warning', 'Données temps réel indisponibles. Valeurs serveur conservées.');
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initStats();
    bindActions();
  });
})();
