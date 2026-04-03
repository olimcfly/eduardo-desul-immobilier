<?php
$s = settings_group('site');
$v = fn(string $k, string $d = '') => htmlspecialchars($s[$k] ?? $d);
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="site">

    <div class="form-section-title">Identité du site</div>

    <div class="form-group">
        <label>Nom du site</label>
        <input type="text" name="site_nom" value="<?= $v('site_nom', 'Eduardo Desul Immobilier') ?>">
    </div>

    <div class="form-group">
        <label>URL du site</label>
        <input type="url" name="site_url" value="<?= $v('site_url') ?>" placeholder="https://…">
    </div>

    <div class="form-group">
        <label>Slogan <span class="label-hint">Affiché dans le header</span></label>
        <input type="text" name="site_slogan" value="<?= $v('site_slogan') ?>" placeholder="Votre expert immobilier local">
    </div>

    <div class="form-group">
        <label>Description courte <span class="label-hint">Meta description par défaut</span></label>
        <textarea name="site_description" rows="3"><?= $v('site_description') ?></textarea>
    </div>

    <div class="form-section-title">Branding</div>

    <div class="form-row">
        <div class="form-group">
            <label>URL Logo</label>
            <input type="url" name="site_logo" value="<?= $v('site_logo') ?>" placeholder="https://…/logo.svg">
        </div>
        <div class="form-group">
            <label>URL Favicon</label>
            <input type="url" name="site_favicon" value="<?= $v('site_favicon') ?>" placeholder="https://…/favicon.ico">
        </div>
    </div>

    <div class="form-group">
        <label>Couleur principale</label>
        <div style="display:flex;align-items:center;gap:10px">
            <input type="color" name="site_couleur_primaire"
                   value="<?= $v('site_couleur_primaire', '#3498db') ?>"
                   style="width:48px;height:40px;border:1px solid #dde1e7;border-radius:8px;padding:2px;cursor:pointer">
            <input type="text" name="site_couleur_hex"
                   value="<?= $v('site_couleur_primaire', '#3498db') ?>"
                   placeholder="#3498db" style="width:120px">
        </div>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
