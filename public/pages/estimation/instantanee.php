<?php
require_once ROOT_PATH . '/core/services/InstantEstimationService.php';

$pageTitle = 'Estimation instantanée — Pascal Hamm Immobilier';
$metaDesc = 'Obtenez une estimation immobilière indicative instantanée basée sur des comparables DVF.';
$extraCss = ['/assets/css/estimation.css'];
$extraJs = ['/assets/js/estimation-instantanee.js'];

$googleApiKey = (string) setting('api_google_maps', '');

?>
<section class="section">
    <div class="container" style="max-width: 980px;">
        <h1>Estimation instantanée</h1>
        <p class="lead">Renseignez votre bien pour obtenir une fourchette indicative basée sur les données DVF.</p>

        <form id="instant-estimation-form" class="estimation-form" style="margin-top:1.5rem;">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label" for="ie-location">Lieu du bien</label>
                <input id="ie-location" name="location" class="form-control" type="text" required placeholder="Adresse, ville ou quartier">
                <input type="hidden" id="ie-place-id" name="place_id">
                <input type="hidden" id="ie-lat" name="lat">
                <input type="hidden" id="ie-lng" name="lng">
                <input type="hidden" id="ie-location-normalized" name="location_normalized">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="ie-property-type">Type de bien</label>
                    <select id="ie-property-type" name="property_type" class="form-control" required>
                        <option value="">Sélectionner</option>
                        <option value="appartement">Appartement</option>
                        <option value="maison">Maison</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ie-surface">Surface (m²)</label>
                    <input id="ie-surface" name="surface" class="form-control" type="number" min="10" max="1200" required>
                </div>
            </div>

            <button type="submit" class="btn btn--primary">Calculer l'estimation</button>
        </form>

        <div id="instant-estimation-result" style="display:none;margin-top:1.5rem;border:1px solid var(--clr-border);padding:1.25rem;border-radius:var(--radius-lg);background:#fff;">
            <h2 style="font-size:1.25rem;margin-bottom:1rem;">Votre estimation indicative</h2>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
                <div><strong>Basse</strong><div id="result-low">—</div></div>
                <div><strong>Médiane</strong><div id="result-med">—</div></div>
                <div><strong>Haute</strong><div id="result-high">—</div></div>
            </div>
            <p style="margin-top:1rem;"><strong>Comparables:</strong> <span id="result-comparables">0</span></p>
            <p style="font-size:.9rem;color:var(--clr-text-muted)">Cette estimation est strictement indicative et ne remplace pas une expertise sur place.</p>
            <a href="/prendre-rendez-vous" class="btn btn--accent" style="margin-top:.5rem;">Prendre rendez-vous avec un conseiller</a>
        </div>

        <div id="instant-estimation-error" style="display:none;margin-top:1rem;color:#b42318;font-weight:600;"></div>
    </div>
</section>
<?php if ($googleApiKey !== ''): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= e($googleApiKey) ?>&libraries=places&callback=initInstantEstimationAutocomplete" async defer></script>
<?php endif; ?>
