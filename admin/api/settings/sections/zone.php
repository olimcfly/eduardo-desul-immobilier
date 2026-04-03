<?php
$s = settings_group('zone');
$v = fn(string $k, string $d = '') => htmlspecialchars($s[$k] ?? $d);
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="zone">

    <div class="form-section-title">Localisation principale</div>

    <div class="form-row">
        <div class="form-group">
            <label>Ville principale</label>
            <input type="text" name="zone_ville" value="<?= $v('zone_ville', 'Bordeaux') ?>">
        </div>
        <div class="form-group">
            <label>Département</label>
            <input type="text" name="zone_departement" value="<?= $v('zone_departement', 'Gironde') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Région</label>
            <input type="text" name="zone_region" value="<?= $v('zone_region', 'Nouvelle-Aquitaine') ?>">
        </div>
        <div class="form-group">
            <label>Rayon (km)</label>
            <input type="number" name="zone_rayon_km" value="<?= $v('zone_rayon_km', '30') ?>" min="1" max="200">
        </div>
    </div>

    <div class="form-group">
        <label>Communes couvertes <span class="label-hint">Séparées par des virgules</span></label>
        <textarea name="zone_communes" rows="3"
            placeholder="Bordeaux, Mérignac, Pessac, Talence, Gradignan…"><?= $v('zone_communes') ?></textarea>
    </div>

    <div class="form-section-title">Coordonnées GPS <span class="label-hint">(pour la carte)</span></div>

    <div class="form-row">
        <div class="form-group">
            <label>Latitude</label>
            <input type="text" name="zone_lat" value="<?= $v('zone_lat', '44.8378') ?>" placeholder="44.8378">
        </div>
        <div class="form-group">
            <label>Longitude</label>
            <input type="text" name="zone_lng" value="<?= $v('zone_lng', '-0.5792') ?>" placeholder="-0.5792">
        </div>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
