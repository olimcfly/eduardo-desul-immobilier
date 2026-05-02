<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/GmbService.php';

$user = Auth::user();
$service = new GmbService((int) ($user['id'] ?? 0));
$emailTemplates = $service->templates('email');
?>
<section class="gmb-panel">
    <div class="gmb-panel-head">
        <h2>Demande d'avis automatique</h2>
    </div>

    <form id="gmb-demande-form" class="gmb-form">
        <?= csrfField() ?>
        <label>Nom client<input name="client_nom" required></label>
        <label>Email client<input type="email" name="client_email"></label>
        <label>Téléphone client<input name="client_tel"></label>
        <label>Adresse du bien<input name="bien_adresse"></label>
        <label>Canal
            <select name="canal">
                <option value="email">Email</option>
                <option value="sms">SMS</option>
                <option value="both">Email + SMS</option>
            </select>
        </label>
        <label>Template
            <select name="template_id">
                <option value="">Template par défaut</option>
                <?php foreach ($emailTemplates as $tpl): ?>
                    <option value="<?= (int) $tpl['id'] ?>"><?= htmlspecialchars($tpl['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn-gmb">Envoyer la demande</button>
    </form>

    <hr>

    <h3>Nouveau template</h3>
    <form id="gmb-template-form" class="gmb-form">
        <?= csrfField() ?>
        <label>Nom<input name="nom" required></label>
        <label>Canal
            <select name="canal"><option value="email">Email</option><option value="sms">SMS</option></select>
        </label>
        <label>Sujet<input name="sujet"></label>
        <label>Contenu<textarea name="contenu" rows="5" required></textarea></label>
        <button type="submit" class="btn-gmb">Sauvegarder le template</button>
    </form>
</section>
