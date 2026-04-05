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

    <div class="form-section-title">Homepage (Hero)</div>

    <div class="form-group">
        <label>Sur-titre Hero</label>
        <input type="text" name="site_home_hero_label"
               value="<?= $v('site_home_hero_label', 'Agent immobilier à Bordeaux — Expert en évaluation immobilière') ?>">
    </div>

    <div class="form-group">
        <label>Titre Hero <span class="label-hint">Utilisez &lt;br&gt; pour un saut de ligne</span></label>
        <input type="text" name="site_home_hero_title"
               value="<?= $v('site_home_hero_title', 'Vendez au juste prix.<br>Achetez en toute sérénité.') ?>">
    </div>

    <div class="form-group">
        <label>Texte Hero</label>
        <textarea name="site_home_hero_subtitle" rows="5"><?= $v('site_home_hero_subtitle', "Vous souhaitez <strong>vendre votre maison ou appartement</strong> au meilleur prix, ou concrétiser un <strong>achat immobilier</strong> à Bordeaux et en Gironde ?\nBénéficiez d'une <strong>estimation immobilière gratuite</strong> et d'un accompagnement personnalisé par Eduardo De Sul, certifié <strong>Expert en évaluation immobilière</strong>.") ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Texte bouton principal</label>
            <input type="text" name="site_home_cta_primary_label"
                   value="<?= $v('site_home_cta_primary_label', 'Estimer mon bien gratuitement') ?>">
        </div>
        <div class="form-group">
            <label>Lien bouton principal</label>
            <input type="text" name="site_home_cta_primary_url"
                   value="<?= $v('site_home_cta_primary_url', '/estimation-gratuite') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Texte bouton secondaire</label>
            <input type="text" name="site_home_cta_secondary_label"
                   value="<?= $v('site_home_cta_secondary_label', 'Voir les annonces') ?>">
        </div>
        <div class="form-group">
            <label>Lien bouton secondaire</label>
            <input type="text" name="site_home_cta_secondary_url"
                   value="<?= $v('site_home_cta_secondary_url', '/biens') ?>">
        </div>
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
