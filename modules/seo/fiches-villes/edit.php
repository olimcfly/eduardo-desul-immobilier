<?php

declare(strict_types=1);

$userId = (int)(Auth::user()['id'] ?? 0);
$id = (int)($_GET['id'] ?? 0);
$page = $id > 0 ? $cityPageService->findForUser($id, $userId) : null;

$values = [
    'city_name' => $page['city_name'] ?? '',
    'slug' => $page['slug'] ?? '',
    'seo_title' => $page['seo_title'] ?? '',
    'meta_description' => $page['meta_description'] ?? '',
    'h1' => $page['h1'] ?? '',
    'intro' => $page['intro'] ?? '',
    'market_block' => $page['market_block'] ?? '',
    'faq_json' => $page['faq_json'] ?? "[]",
    'internal_links_json' => $page['internal_links_json'] ?? "[]",
    'canonical_url' => $page['canonical_url'] ?? '',
    'status' => $page['status'] ?? 'draft',
];

$seoScore = (int)($page['seo_score'] ?? $cityPageService->calculateSeoScore($values));
$contentScore = (int)($page['content_score'] ?? $cityPageService->calculateContentScore($values));
$canPublish = $cityPageService->isPublishable($values);
?>
<section class="seo-section fiche-ville-module">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; <a href="/admin?module=seo&action=villes">Fiches villes</a> &gt; Édition</div>

    <div class="fv-head">
        <div>
            <h2><?= $id > 0 ? '✏️ Modifier la fiche ville' : '➕ Nouvelle fiche ville' ?></h2>
            <p class="seo-subtitle">Un champ à la fois : remplissez les sections puis passez la fiche en “Prêt” ou “Publié”.</p>
        </div>
        <?php if ($id > 0): ?>
            <a class="btn btn-sm" href="/admin?module=seo&action=ville-preview&id=<?= $id ?>">Prévisualiser</a>
        <?php endif; ?>
    </div>

    <div class="score-circles">
        <div class="score-circle"><strong>SEO</strong><div><?= $seoScore ?>/100</div></div>
        <div class="score-circle"><strong>Contenu</strong><div><?= $contentScore ?>/100</div></div>
        <div class="score-circle"><strong>Publication</strong><div><?= $canPublish ? '✅ Prête' : '⏳ Incomplète' ?></div></div>
    </div>

    <form method="post" action="/modules/seo/fiches-villes/api.php" class="city-form fv-form-grid">
        <?= csrfField() ?>
        <input type="hidden" name="mode" value="save">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="fv-block">
            <h3>1. Infos générales</h3>
            <input type="text" name="city_name" value="<?= e((string)$values['city_name']) ?>" placeholder="Commune (ex: Bordeaux)" required>
            <input type="text" name="slug" value="<?= e((string)$values['slug']) ?>" placeholder="slug-ville" required>
        </div>

        <div class="fv-block">
            <h3>2. SEO</h3>
            <input type="text" name="seo_title" maxlength="60" value="<?= e((string)$values['seo_title']) ?>" placeholder="Titre SEO (60 caractères max)">
            <textarea name="meta_description" maxlength="160" rows="2" placeholder="Meta description (160 caractères max)"><?= e((string)$values['meta_description']) ?></textarea>
            <input type="text" name="canonical_url" value="<?= e((string)$values['canonical_url']) ?>" placeholder="https://votre-site.fr/votre-page-canonique">
        </div>

        <div class="fv-block">
            <h3>3. Contenu local</h3>
            <input type="text" name="h1" value="<?= e((string)$values['h1']) ?>" placeholder="H1 de la page">
            <textarea name="intro" rows="4" placeholder="Introduction locale claire et utile"><?= e((string)$values['intro']) ?></textarea>
            <textarea name="market_block" rows="5" placeholder="Bloc marché local (prix, dynamique, conseils)"><?= e((string)$values['market_block']) ?></textarea>
        </div>

        <div class="fv-block">
            <h3>4. FAQ</h3>
            <p class="muted">Format JSON simplifié : [{"q":"Question","a":"Réponse"}]</p>
            <textarea name="faq_json" rows="5" placeholder='[{"q":"Quels quartiers cibler ?","a":"..."}]'><?= e((string)$values['faq_json']) ?></textarea>
        </div>

        <div class="fv-block">
            <h3>5. Liens internes</h3>
            <p class="muted">Format JSON : [{"label":"Estimation","url":"/estimation"}]</p>
            <textarea name="internal_links_json" rows="4" placeholder='[{"label":"Biens à vendre","url":"/biens"}]'><?= e((string)$values['internal_links_json']) ?></textarea>
        </div>

        <div class="fv-block">
            <h3>6. Publication</h3>
            <select name="status">
                <option value="draft" <?= $values['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                <option value="ready" <?= $values['status'] === 'ready' ? 'selected' : '' ?>>Prête</option>
                <option value="published" <?= $values['status'] === 'published' ? 'selected' : '' ?>>Publiée</option>
            </select>
            <p class="muted">Le statut “Publié” n'est accepté que si les champs minimum sont présents.</p>
        </div>

        <div class="actions">
            <button type="submit">Enregistrer la fiche</button>
            <a class="btn btn-sm" href="/admin?module=seo&action=villes">Retour liste</a>
        </div>
    </form>
</section>
