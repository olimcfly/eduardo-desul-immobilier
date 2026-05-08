<?php
$s = settings_group('tracking');
$v = fn(string $k, string $d = '') => htmlspecialchars((string) ($s[$k] ?? $d), ENT_QUOTES, 'UTF-8');

$headCode = trim((string) ($s['tracking_head_code'] ?? ''));
$bodyCode = trim((string) ($s['tracking_body_code'] ?? ''));
$gaMeasurementId = trim((string) ($_ENV['GA_MEASUREMENT_ID'] ?? ''));
?>

<div class="api-help-banner">
    <i class="fas fa-circle-info"></i>
    <div>
        <strong>Insertion dans le site public</strong><br>
        <span>
            Le code placé ici est injecté dans <code>&lt;head&gt;</code> et juste après <code>&lt;body&gt;</code> via
            <code>public/templates/layout.php</code>.<br>
            Pour <strong>GA4</strong>, la variable d’environnement <code>GA_MEASUREMENT_ID</code> reste disponible côté serveur.
        </span>
    </div>
</div>

<form class="settings-form" method="post">
    <input type="hidden" name="section" value="tracking">

    <div class="form-section-title">Google Tag Manager</div>

    <div class="form-group">
        <label>Balises de mesure du site public <span class="label-hint">Code injecté dans le head</span></label>
        <textarea name="tracking_head_code" rows="10" placeholder="Collez ici le script Google Tag Manager, Google Analytics ou toute balise de suivi..."><?= $v('tracking_head_code') ?></textarea>
    </div>

    <div class="form-section-title">Pixel Meta</div>

    <div class="form-group">
        <label>Code Pixel / noscript <span class="label-hint">Code injecté juste après l’ouverture du body</span></label>
        <textarea name="tracking_body_code" rows="8" placeholder="Collez ici le bloc noscript Meta Pixel ou un second script de mesure..."><?= $v('tracking_body_code') ?></textarea>
    </div>

    <div class="form-section-title">Etat actuel</div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Balise head</div>
            <div class="toggle-hint"><?= $headCode !== '' ? 'Code configuré' : 'Aucun code configuré' ?></div>
        </div>
        <span class="api-status-dot <?= $headCode !== '' ? 'dot-ok' : 'dot-off' ?>"></span>
    </div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">Balise body</div>
            <div class="toggle-hint"><?= $bodyCode !== '' ? 'Code configuré' : 'Aucun code configuré' ?></div>
        </div>
        <span class="api-status-dot <?= $bodyCode !== '' ? 'dot-ok' : 'dot-off' ?>"></span>
    </div>

    <div class="toggle-group">
        <div>
            <div class="toggle-label">GA4 via environnement</div>
            <div class="toggle-hint"><?= $gaMeasurementId !== '' ? 'GA_MEASUREMENT_ID actif' : 'Variable non définie' ?></div>
        </div>
        <span class="api-status-dot <?= $gaMeasurementId !== '' ? 'dot-ok' : 'dot-off' ?>"></span>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
