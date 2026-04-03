<?php
declare(strict_types=1);

$articleId = (int)($_GET['id'] ?? 0);
$article = $articleId > 0 ? $blogService->getArticleById($articleId) : null;
$content = $article['content'] ?? '<h1>Titre de l\'article</h1><p>Commencez la rédaction…</p>';
$analysis = $seoService->analyzeArticleContent($content);

require __DIR__ . '/_layout_top.php';
?>
<section class="editor-layout" data-view="editor">
    <article class="card">
        <h3>Éditeur d'article</h3>
        <div class="toolbar" role="toolbar" aria-label="Mise en forme">
            <button type="button" data-cmd="bold">Gras</button>
            <button type="button" data-cmd="italic">Italique</button>
            <button type="button" data-cmd="createLink">Lien</button>
            <button type="button" data-cmd="formatBlock" data-value="h2">H2</button>
            <button type="button" data-cmd="formatBlock" data-value="h3">H3</button>
        </div>
        <div class="editor-textarea" contenteditable="true" aria-label="Contenu de l'article"><?= $content ?></div>
        <form method="post" class="meta-grid">
            <input type="hidden" name="csrf_token" value="<?= seo_h($_SESSION['seo_csrf_token']) ?>">
            <input type="text" maxlength="60" placeholder="Titre SEO" value="<?= seo_h($article['seo_title'] ?? '') ?>">
            <textarea maxlength="160" placeholder="Meta description"><?= seo_h($article['meta_description'] ?? '') ?></textarea>
            <input type="text" placeholder="Slug" value="<?= seo_h($article['slug'] ?? '') ?>">
            <input type="text" placeholder="Mots-clés secondaires (séparés par des virgules)">
            <div class="actions-row">
                <button class="btn-secondary" type="submit">Enregistrer</button>
                <button class="btn-primary" type="button">Publier / Planifier</button>
                <button class="btn-ghost" type="button">Prévisualiser</button>
            </div>
        </form>
    </article>

    <aside class="card seo-panel">
        <h3>Analyse SEO temps réel</h3>
        <div class="progress-ring" data-score="<?= (int)$analysis['seo_score'] ?>">
            <span class="score-n"><?= (int)$analysis['seo_score'] ?></span>
            <small>/100</small>
        </div>
        <p>Mots : <span class="word-count-n"><?= (int)$analysis['word_count'] ?></span></p>
        <p>Lecture : <?= (int)$analysis['reading_time'] ?> min</p>
        <h4>Densité des mots-clés</h4>
        <?php foreach ($analysis['keyword_density'] as $density): ?>
            <div class="badge"><?= seo_h($density['keyword']) ?> : <?= seo_h((string)$density['density']) ?>%</div>
        <?php endforeach; ?>
        <h4>Aperçu SERP</h4>
        <div class="serp-preview">
            <div class="result-title"><?= seo_h($article['seo_title'] ?? 'Titre SEO de démonstration') ?></div>
            <div class="result-url">immo-local.plus/blog/<?= seo_h($article['slug'] ?? 'nouvel-article') ?></div>
            <div class="result-desc"><?= seo_h($article['meta_description'] ?? 'Meta description optimisée pour la recherche locale immobilière.') ?></div>
        </div>
    </aside>
</section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
