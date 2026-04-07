'use strict';

(function () {
  const form = document.getElementById('instant-form');
  const result = document.getElementById('instant-result');
  if (!form || !result) return;

  const addressInput = document.getElementById('instant-address');
  const cityInput = document.getElementById('instant-city');
  const postalInput = document.getElementById('instant-postal-code');
  const latInput = document.getElementById('instant-lat');
  const lngInput = document.getElementById('instant-lng');

  function euro(v) {
    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(v) + ' €';
  }

  function setResult(html) {
    result.style.display = 'block';
    result.innerHTML = html;
  }

  function bindPlaces() {
    if (!window.google || !google.maps || !google.maps.places || !addressInput) return;
    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
      fields: ['formatted_address', 'geometry', 'address_components'],
      componentRestrictions: { country: ['fr'] },
      types: ['address']
    });

    autocomplete.addListener('place_changed', () => {
      const place = autocomplete.getPlace();
      if (!place || !place.geometry) return;
      latInput.value = place.geometry.location.lat();
      lngInput.value = place.geometry.location.lng();

      const comps = place.address_components || [];
      let city = '';
      let postal = '';
      comps.forEach(c => {
        if (c.types.includes('locality')) city = c.long_name;
        if (c.types.includes('postal_code')) postal = c.long_name;
      });
      cityInput.value = city;
      postalInput.value = postal;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindPlaces);
  } else {
    bindPlaces();
  }
  window.addEventListener('load', bindPlaces);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const payload = {
      address: addressInput.value.trim(),
      city: cityInput.value.trim(),
      postal_code: postalInput.value.trim(),
      lat: latInput.value ? parseFloat(latInput.value) : null,
      lng: lngInput.value ? parseFloat(lngInput.value) : null,
      property_type: document.getElementById('instant-type').value,
      surface: parseFloat(document.getElementById('instant-surface').value || '0')
    };

    if (!payload.address || !payload.property_type || !payload.surface) {
      setResult('<p>Veuillez remplir tous les champs requis.</p>');
      return;
    }

    setResult('<p>Analyse en cours…</p>');

    try {
      const response = await fetch('/api/estimate/instant', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await response.json();
      if (!data.ok) {
        setResult(`<h3>Estimation non fiable</h3><p>${data.message || 'Données insuffisantes.'}</p><a class="btn btn--accent" href="/prendre-rendez-vous">Prendre rendez-vous conseiller</a>`);
        return;
      }

      setResult(`
        <h3>Votre estimation indicative</h3>
        <p><strong>Basse :</strong> ${euro(data.estimate_low)}</p>
        <p><strong>Médiane :</strong> ${euro(data.estimate_median)}</p>
        <p><strong>Haute :</strong> ${euro(data.estimate_high)}</p>
        <p><strong>Comparables :</strong> ${data.comparables_count}</p>
        <p style="color:#6b7280">${data.message}</p>
        <a class="btn btn--accent" href="/prendre-rendez-vous">Prendre rendez-vous conseiller</a>
      `);
    } catch (err) {
      setResult('<p>Erreur technique. Merci de réessayer.</p>');
    }
  });
})();
