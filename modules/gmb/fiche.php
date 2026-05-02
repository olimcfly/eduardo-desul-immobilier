<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/GmbService.php';

$user = Auth::user();
$service = new GmbService((int) ($user['id'] ?? 0));
$fiche = $service->getFiche();
?>
<section class="gmb-panel">
    <div class="gmb-panel-head">
        <h2>Ma fiche GMB</h2>
        <button class="btn-gmb" data-action="sync-fiche">Préremplir depuis mes paramètres</button>
    </div>

    <p class="gmb-mode-note">Mode manuel : ces informations préparent votre fiche locale. Elles ne sont pas encore synchronisées avec Google.</p>

    <form id="gmb-fiche-form" class="gmb-form">
        <?= csrfField() ?>
        <input type="hidden" name="gmb_location_id" value="<?= htmlspecialchars($fiche['gmb_location_id'] ?? '') ?>">
        <input type="hidden" name="gmb_account_id" value="<?= htmlspecialchars($fiche['gmb_account_id'] ?? '') ?>">

        <label>Nom établissement<input type="text" name="nom_etablissement" value="<?= htmlspecialchars($fiche['nom_etablissement'] ?? '') ?>"></label>
        <label>Catégorie<input type="text" name="categorie" value="<?= htmlspecialchars($fiche['categorie'] ?? 'Agence immobilière') ?>"></label>
        <label>Adresse<input type="text" name="adresse" value="<?= htmlspecialchars($fiche['adresse'] ?? '') ?>"></label>
        <label>Ville<input type="text" name="ville" value="<?= htmlspecialchars($fiche['ville'] ?? '') ?>"></label>
        <label>Code postal<input type="text" name="code_postal" value="<?= htmlspecialchars($fiche['code_postal'] ?? '') ?>"></label>
        <label>Téléphone<input type="text" name="telephone" value="<?= htmlspecialchars($fiche['telephone'] ?? '') ?>"></label>
        <label>Site web<input type="url" name="site_web" value="<?= htmlspecialchars($fiche['site_web'] ?? '') ?>"></label>
        <label>Description<textarea name="description" rows="4"><?= htmlspecialchars($fiche['description'] ?? '') ?></textarea></label>
        <label>Statut
            <select name="statut">
                <?php foreach (['actif' => 'Actif', 'suspendu' => 'Suspendu', 'non_verifie' => 'Non vérifié'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= (($fiche['statut'] ?? 'non_verifie') === $value) ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit" class="btn-gmb">Enregistrer la fiche</button>
    </form>
</section>
