<?php
// ── Section : Mon profil ─────────────────────────────────────
$s = settings_group('profil');
$v = fn(string $k, string $d = '') => htmlspecialchars($s[$k] ?? $d);
?>
<form class="settings-form" method="post">
    <input type="hidden" name="section" value="profil">

    <div class="form-section-title">Identité</div>

    <div class="form-row">
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="profil_prenom" value="<?= $v('profil_prenom') ?>" placeholder="Eduardo">
        </div>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="profil_nom" value="<?= $v('profil_nom') ?>" placeholder="Desul">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Email professionnel</label>
            <input type="email" name="profil_email" value="<?= $v('profil_email') ?>">
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="profil_telephone" value="<?= $v('profil_telephone') ?>" placeholder="+33 6 …">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Ville</label>
            <input type="text" name="profil_ville" value="<?= $v('profil_ville', 'Bordeaux') ?>">
        </div>
        <div class="form-group">
            <label>Réseau / Enseigne</label>
            <input type="text" name="profil_reseau" value="<?= $v('profil_reseau') ?>" placeholder="IAD, Century21…">
        </div>
    </div>

    <div class="form-group">
        <label>Agence</label>
        <input type="text" name="profil_agence" value="<?= $v('profil_agence') ?>">
    </div>

    <div class="form-group">
        <label>N° SIRET <span class="label-hint">Optionnel</span></label>
        <input type="text" name="profil_siret" value="<?= $v('profil_siret') ?>" placeholder="XXX XXX XXX XXXXX">
    </div>

    <div class="form-group">
        <label>N° RSAC / RCS <span class="label-hint">Affiché dans le footer légal</span></label>
        <input type="text" name="profil_rsac" value="<?= $v('profil_rsac') ?>" placeholder="Ex: 811729276">
    </div>

    <div class="form-section-title">Présentation</div>

    <div class="form-group">
        <label>Bio <span class="label-hint">Texte court affiché sur le site</span></label>
        <textarea name="profil_bio" rows="4" placeholder="Expert immobilier à Bordeaux depuis…"><?= $v('profil_bio') ?></textarea>
    </div>

    <div class="form-section-title">Médias</div>

    <div class="form-group">
        <label>URL photo de profil</label>
        <input type="url" name="profil_photo" value="<?= $v('profil_photo') ?>" placeholder="https://…/photo.jpg">
    </div>

    <div class="form-group">
        <label>N° carte professionnelle</label>
        <input type="text" name="profil_carte_pro" value="<?= $v('profil_carte_pro') ?>" placeholder="CPI 3301 2015 000 012 345">
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
