<?php
// ── Section : Mon profil ─────────────────────────────────────
$s = settings_group('profil');
$v = fn(string $k, string $d = '') => htmlspecialchars($s[$k] ?? $d);
$photoSrc = (string) ($s['profil_photo'] ?? ($s['advisor_photo'] ?? DEFAULT_ADVISOR_PHOTO_URL));
?>
<form class="settings-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="section" value="profil">

    <div class="form-section-title">Identité</div>

    <div class="form-row">
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="profil_prenom" value="<?= $v('profil_prenom') ?>" placeholder="Pascal">
        </div>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="profil_nom" value="<?= $v('profil_nom') ?>" placeholder="Hamm">
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
            <label>Fonction</label>
            <input type="text" name="profil_fonction" value="<?= $v('profil_fonction') ?>" placeholder="Conseiller immobilier indépendant">
        </div>
        <div class="form-group">
            <label>Statut</label>
            <input type="text" name="profil_statut" value="<?= $v('profil_statut') ?>" placeholder="Entrepreneur individuel / Agent commercial">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Site principal</label>
            <input type="url" name="profil_site_principal" value="<?= $v('profil_site_principal') ?>" placeholder="https://...">
        </div>
        <div class="form-group">
            <label>Email réseau eXp</label>
            <input type="email" name="profil_email_reseau" value="<?= $v('profil_email_reseau') ?>" placeholder="pascal.hamm@expfrance.fr">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Ville</label>
            <input type="text" name="profil_ville" value="<?= $v('profil_ville', 'Aix-en-Provence') ?>">
        </div>
        <div class="form-group">
            <label>Réseau / Enseigne</label>
            <input type="text" name="profil_reseau" value="<?= $v('profil_reseau') ?>" placeholder="EXP France, Century21…">
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

    <div class="form-row">
        <div class="form-group">
            <label>SIREN</label>
            <input type="text" name="profil_siren" value="<?= $v('profil_siren') ?>" placeholder="441 887 536">
        </div>
        <div class="form-group">
            <label>Code APE / NAF</label>
            <input type="text" name="profil_ape" value="<?= $v('profil_ape') ?>" placeholder="68.31Z — Agences immobilières">
        </div>
    </div>

    <div class="form-group">
        <label>N° RSAC / RCS <span class="label-hint">Affiché dans le footer légal</span></label>
        <input type="text" name="profil_rsac" value="<?= $v('profil_rsac') ?>" placeholder="Ex: 811729276">
    </div>

    <div class="form-section-title">Présentation</div>

    <div class="form-group">
        <label>Bio <span class="label-hint">Texte court affiché sur le site</span></label>
        <textarea name="profil_bio" rows="4" placeholder="Expert immobilier à Aix-en-Provence depuis…"><?= $v('profil_bio') ?></textarea>
    </div>

    <div class="form-section-title">Médias</div>

    <div class="form-group">
        <label>Photo de profil</label>
        <input type="file" name="profil_photo_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <div class="label-hint" style="margin-top:6px;">Importez un JPG, PNG ou WebP. La photo actuelle sera conservée si aucun fichier n’est choisi.</div>
        <div style="margin-top:10px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div style="width:72px;height:72px;border-radius:12px;overflow:hidden;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;">
                <?php if ($photoSrc !== ''): ?>
                    <img src="<?= htmlspecialchars($photoSrc) ?>" alt="Photo actuelle" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <span style="font-weight:800;color:#94a3b8;"><?= htmlspecialchars(strtoupper(substr((string)($s['profil_prenom'] ?? 'P'),0,1) . substr((string)($s['profil_nom'] ?? 'H'),0,1))) ?></span>
                <?php endif; ?>
            </div>
            <div class="label-hint">Image actuelle enregistrée.</div>
        </div>
        <button type="submit" class="btn-save" name="profil_delete_photo" value="1" style="margin-top:12px;background:#fee2e2;color:#b91c1c;">
            <i class="fas fa-trash-alt"></i> Supprimer l'image
        </button>
    </div>

    <div class="form-group">
        <label>N° carte professionnelle</label>
        <input type="text" name="profil_carte_pro" value="<?= $v('profil_carte_pro') ?>" placeholder="CPI 3301 2015 000 012 345">
    </div>

    <div class="form-group">
        <label>Marque locale possible</label>
        <input type="text" name="profil_marque_locale" value="<?= $v('profil_marque_locale') ?>" placeholder="BeckHamm Immobilier">
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>
