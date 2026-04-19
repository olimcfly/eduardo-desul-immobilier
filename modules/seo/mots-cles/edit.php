<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SeoKeywordPilotService.php';

$userId = (int)(Auth::user()['id'] ?? 0);
$keywordService = new SeoKeywordPilotService(db(), $userId);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$keyword = $isEdit ? $keywordService->findKeyword($id) : null;

if ($isEdit && !$keyword) {
    flash('error', 'Mot-clé introuvable.');
    redirect('/admin?module=seo&action=keywords');
}

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    try {
        $savedId = $keywordService->saveKeyword($_POST, $isEdit ? $id : null);
        flash('success', $isEdit ? 'Mot-clé mis à jour.' : 'Mot-clé ajouté.');
        redirect('/admin?module=seo&action=keyword_positions&id=' . $savedId);
    } catch (Throwable $exception) {
        $errorMessage = $exception->getMessage();
    }
}

$urlOptions = $keywordService->getTargetUrlOptions();

$formData = [
    'keyword' => (string)($_POST['keyword'] ?? $keyword['keyword'] ?? ''),
    'city_name' => (string)($_POST['city_name'] ?? $keyword['city_name'] ?? ''),
    'intent' => (string)($_POST['intent'] ?? $keyword['intent'] ?? 'estimation'),
    'status' => (string)($_POST['status'] ?? $keyword['status'] ?? 'active'),
    'target_url' => (string)($_POST['target_url'] ?? $keyword['target_url'] ?? ''),
];
?>
<section class="seo-section seo-keyword-edit">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; <a href="/admin?module=seo&action=keywords">Mots-clés</a> &gt; <?= $isEdit ? 'Éditer' : 'Ajouter' ?></div>
    <h2><?= $isEdit ? 'Modifier un mot-clé' : 'Ajouter un mot-clé' ?></h2>
    <p>Choisissez une requête locale utile à votre activité de prospection vendeur.</p>

    <?php if ($errorMessage !== ''): ?>
        <div class="seo-flash seo-flash-error"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <form method="post" class="city-form seo-form-stack">
        <?= csrfField() ?>

        <label>Mot-clé *</label>
        <input type="text" name="keyword" maxlength="190" required value="<?= e($formData['keyword']) ?>" placeholder="ex: estimation appartement Bordeaux">

        <label>Ville / zone</label>
        <input type="text" name="city_name" maxlength="160" value="<?= e($formData['city_name']) ?>" placeholder="ex: Bordeaux">

        <label>Intention</label>
        <select name="intent" required>
            <?php foreach (['estimation','vente','achat','quartier','commune','blog'] as $intent): ?>
                <option value="<?= e($intent) ?>" <?= $formData['intent'] === $intent ? 'selected' : '' ?>><?= e(ucfirst($intent)) ?></option>
            <?php endforeach; ?>
        </select>

        <label>URL cible</label>
        <input type="text" name="target_url" value="<?= e($formData['target_url']) ?>" list="target-url-options" placeholder="/ville/aix-en-provence">
        <datalist id="target-url-options">
            <?php foreach ($urlOptions as $option): ?>
                <option value="<?= e((string)$option['url']) ?>"><?= e((string)$option['source'] . ' — ' . (string)$option['label']) ?></option>
            <?php endforeach; ?>
        </datalist>

        <label>Statut</label>
        <select name="status" required>
            <?php foreach (['active', 'paused', 'archived'] as $status): ?>
                <option value="<?= e($status) ?>" <?= $formData['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="actions">
            <button type="submit"><?= $isEdit ? 'Enregistrer' : 'Créer le mot-clé' ?></button>
            <a class="seo-secondary" href="/admin?module=seo&action=keywords">Annuler</a>
        </div>
    </form>
</section>
