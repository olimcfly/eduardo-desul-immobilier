/**
 * Carte /guide-local — consomme /api/guide-local/villes.php et pois.php
 */
(function () {
  'use strict';

  var DEFAULT_CENTER = [44.85, -0.575];
  var DEFAULT_ZOOM = 11;

  function $(id) {
    return document.getElementById(id);
  }

  function apiUrl(path, params) {
    var u = new URL(path, window.location.origin);
    if (params) {
      Object.keys(params).forEach(function (k) {
        var v = params[k];
        if (v !== '' && v != null) u.searchParams.set(k, String(v));
      });
    }
    return u.toString();
  }

  function setStatus(el, text, isErr) {
    if (!el) return;
    el.textContent = text || '';
    el.style.color = isErr ? '#b91c1c' : '';
  }

  function parseCoord(p) {
    var lat = p.latitude != null && p.latitude !== '' ? parseFloat(p.latitude, 10) : NaN;
    var lng = p.longitude != null && p.longitude !== '' ? parseFloat(p.longitude, 10) : NaN;
    if (isNaN(lat) || isNaN(lng)) return null;
    return [lat, lng];
  }

  function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  function buildPopup(p) {
    var parts = ['<div class="gl-popup">', '<strong>', escapeHtml(p.name || ''), '</strong>'];
    if (p.category_name) parts.push('<br><span class="gl-popup__cat">', escapeHtml(p.category_name), '</span>');
    if (p.address) parts.push('<br>', escapeHtml(p.address));
    if (p.district_name || p.city_name) {
      parts.push('<br><small>', escapeHtml([p.district_name, p.city_name].filter(Boolean).join(' · ')), '</small>');
    }
    if (p.website) {
      var w = String(p.website).replace(/"/g, '&quot;');
      parts.push('<br><a href="', w, '" target="_blank" rel="noopener">Site web</a>');
    }
    parts.push('</div>');
    return parts.join('');
  }

  function uniqueCategories(pois) {
    var map = {};
    pois.forEach(function (p) {
      var slug = p.category_slug || '';
      if (!slug) return;
      if (!map[slug]) map[slug] = p.category_name || slug;
    });
    return Object.keys(map)
      .sort()
      .map(function (slug) {
        return { slug: slug, name: map[slug] };
      });
  }

  function filterByCategory(pois, catSlug) {
    if (!catSlug) return pois;
    return pois.filter(function (p) {
      return (p.category_slug || '') === catSlug;
    });
  }

  var mapEl = $('guideLocalMap');
  var citySel = $('gl-city');
  var distSel = $('gl-district');
  var catSel = $('gl-category');
  var btnApply = $('gl-apply');
  var statusEl = $('gl-status');
  var listEl = $('gl-list');

  if (!mapEl || typeof L === 'undefined' || !citySel || !distSel || !catSel) {
    return;
  }

  var map = L.map(mapEl, { scrollWheelZoom: false }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(map);

  var markersLayer = L.layerGroup().addTo(map);
  var lastPois = [];

  function fillCategoryOptions(categories, preserveSlug) {
    var prev = preserveSlug || catSel.value || '';
    catSel.innerHTML = '';
    var optAll = document.createElement('option');
    optAll.value = '';
    optAll.textContent = 'Toutes les catégories';
    catSel.appendChild(optAll);
    categories.forEach(function (c) {
      var o = document.createElement('option');
      o.value = c.slug;
      o.textContent = c.name;
      catSel.appendChild(o);
    });
    if (prev && Array.prototype.some.call(catSel.options, function (o) { return o.value === prev; })) {
      catSel.value = prev;
    }
  }

  function renderMarkersAndList(pois) {
    markersLayer.clearLayers();
    if (listEl) listEl.innerHTML = '';

    var shown = filterByCategory(pois, catSel.value);
    var bounds = [];
    var withGeo = 0;

    shown.forEach(function (p) {
      var ll = parseCoord(p);
      if (ll) {
        withGeo++;
        bounds.push(ll);
        var m = L.marker(ll).bindPopup(buildPopup(p));
        markersLayer.addLayer(m);
      }
      if (listEl) {
        var li = document.createElement('li');
        li.className = 'guide-local-sidebar__item';
        var title = document.createElement('strong');
        title.textContent = p.name || '';
        li.appendChild(title);
        if (p.category_name) {
          li.appendChild(document.createElement('br'));
          var sm = document.createElement('small');
          sm.textContent = p.category_name;
          li.appendChild(sm);
        }
        if (!ll) {
          li.appendChild(document.createElement('br'));
          var em = document.createElement('em');
          em.textContent = 'Pas de coordonnées GPS';
          li.appendChild(em);
        }
        listEl.appendChild(li);
      }
    });

    if (bounds.length) {
      map.fitBounds(bounds, { padding: [36, 36], maxZoom: 15 });
    } else {
      map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
    }

    var noGeo = shown.length - withGeo;
    var msg =
      shown.length === 0
        ? 'Aucun point pour ces filtres.'
        : shown.length +
          ' lieu(x) — ' +
          withGeo +
          ' sur la carte' +
          (noGeo > 0 ? ' · ' + noGeo + ' sans coordonnées' : '');
    setStatus(statusEl, msg, false);
  }

  function loadDistrictsForCity(slug) {
    distSel.innerHTML = '';
    var opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = 'Tous les quartiers';
    distSel.appendChild(opt0);
    if (!slug) {
      distSel.disabled = true;
      return Promise.resolve();
    }
    distSel.disabled = false;
    return fetch(apiUrl('/api/guide-local/villes.php', { slug: slug }))
      .then(function (r) {
        if (!r.ok) throw new Error('Ville introuvable');
        return r.json();
      })
      .then(function (data) {
        if (!data.ok || !data.city || !data.city.districts) return;
        data.city.districts.forEach(function (d) {
          var o = document.createElement('option');
          o.value = d.slug || '';
          o.textContent = d.nom || d.slug || '';
          distSel.appendChild(o);
        });
      })
      .catch(function () {
        distSel.disabled = true;
      });
  }

  function fetchPois(citySlug, districtSlug) {
    var params = {};
    if (citySlug) params.city_slug = citySlug;
    if (districtSlug) params.district_slug = districtSlug;
    setStatus(statusEl, 'Chargement…', false);
    return fetch(apiUrl('/api/guide-local/pois.php', params))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok) {
          setStatus(statusEl, data.error || 'API indisponible', true);
          lastPois = [];
          fillCategoryOptions([], '');
          renderMarkersAndList([]);
          return;
        }
        lastPois = data.pois || [];
        fillCategoryOptions(uniqueCategories(lastPois), catSel.value);
        renderMarkersAndList(lastPois);
      })
      .catch(function () {
        setStatus(statusEl, 'Impossible de joindre l’API des POI.', true);
        lastPois = [];
        renderMarkersAndList([]);
      });
  }

  function refresh() {
    var city = citySel.value || '';
    var dist = distSel.value || '';
    return fetchPois(city, dist);
  }

  fetch('/api/guide-local/villes.php')
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.ok || !data.cities || !data.cities.length) {
        setStatus(statusEl, 'Liste des villes indisponible.', true);
        return;
      }
      citySel.innerHTML = '';
      data.cities.forEach(function (c) {
        var o = document.createElement('option');
        o.value = c.slug || '';
        o.textContent = c.nom || c.slug || '';
        citySel.appendChild(o);
      });
      var preferred = ['bordeaux', 'merignac', 'pessac'];
      var pick = data.cities[0].slug || '';
      for (var i = 0; i < preferred.length; i++) {
        if (data.cities.some(function (c) { return (c.slug || '') === preferred[i]; })) {
          pick = preferred[i];
          break;
        }
      }
      citySel.value = pick;
      return loadDistrictsForCity(pick).then(function () {
        return refresh();
      });
    })
    .catch(function () {
      setStatus(statusEl, 'Erreur réseau (villes).', true);
    });

  citySel.addEventListener('change', function () {
    var slug = citySel.value || '';
    loadDistrictsForCity(slug).then(function () {
      return refresh();
    });
  });

  distSel.addEventListener('change', refresh);
  catSel.addEventListener('change', function () {
    renderMarkersAndList(lastPois);
  });
  if (btnApply) btnApply.addEventListener('click', refresh);

  setTimeout(function () {
    map.invalidateSize();
  }, 400);
})();
