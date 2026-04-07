<?php
$pageTitle = 'Estimation instantanée — Eduardo Desul Immobilier';
$metaDesc = 'Estimation immobilière instantanée basée sur les données DVF.';
$extraJs = ['/assets/js/estimation-instantanee.js'];
$googleApiKey = (string) setting('api_google_maps', '');
?>
<section class="section">
    <div class="container" style="max-width:860px">
        <h1>Estimation instantanée</h1>
        <p>Indiquez l’adresse, le type de bien et la surface pour obtenir une fourchette immédiate.</p>

        <form id="instant-form" class="card" style="padding:1.25rem;display:grid;gap:1rem">
            <div class="form-group">
                <label class="form-label" for="instant-address">Lieu</label>
                <input type="text" id="instant-address" class="form-control" placeholder="Adresse du bien" required>
                <input type="hidden" id="instant-city">
                <input type="hidden" id="instant-postal-code">
                <input type="hidden" id="instant-lat">
                <input type="hidden" id="instant-lng">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="instant-type">Type de bien</label>
                    <select id="instant-type" class="form-control" required>
                        <option value="">Sélectionner</option>
                        <option value="appartement">Appartement</option>
                        <option value="maison">Maison</option>
                        <option value="local">Local commercial</option>
                        <option value="terrain">Terrain</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="instant-surface">Surface (m²)</label>
                    <input type="number" id="instant-surface" class="form-control" min="9" max="500" required>
                </div>
            </div>
            <button type="submit" class="btn btn--primary">Calculer l’estimation</button>
        </form>

        <div id="instant-result" class="card" style="padding:1.25rem;margin-top:1rem;display:none"></div>
    </div>
</section>
<script>
window.__ESTIMATION_CONFIG__ = {
    googleMapsKey: <?= json_encode($googleApiKey, JSON_UNESCAPED_UNICODE) ?>
};
</script>
<?php if ($googleApiKey !== ''): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= e($googleApiKey) ?>&libraries=places&loading=async" async defer></script>
<?php endif; ?>
