<?php

declare(strict_types=1);

function renderHelpIndexPage(HelpCenterService $service, string $context): void
{
    $query = trim((string) ($_GET['q'] ?? ''));
    $category = trim((string) ($_GET['category'] ?? ''));
    $articles = $service->searchArticles($query, $category, $context, 50);
    $categories = $service->getCategories();
    ?>
    <section class="help-hero">
        <h1><i class="fas fa-circle-question"></i> Centre d’aide intelligent</h1>
        <p>Trouvez rapidement la bonne action selon votre module actif.</p>
        <form method="get" class="help-search">
            <input type="hidden" name="module" value="aide">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Rechercher un article d’aide…">
            <input type="hidden" name="context" value="<?= htmlspecialchars($context) ?>">
            <button type="submit" class="help-btn">Rechercher</button>
        </form>
        <?php if ($context !== ''): ?>
            <p class="help-context">Contexte détecté : <strong><?= htmlspecialchars($context) ?></strong></p>
        <?php endif; ?>
    </section>

    <section class="help-categories" aria-label="Catégories">
        <a href="/admin?module=aide&amp;context=<?= urlencode($context) ?>" class="help-chip <?= $category === '' ? 'is-active' : '' ?>">Toutes</a>
        <?php foreach ($categories as $cat): ?>
            <a href="/admin?module=aide&amp;category=<?= urlencode($cat) ?>&amp;context=<?= urlencode($context) ?>" class="help-chip <?= $category === $cat ? 'is-active' : '' ?>"><?= htmlspecialchars(ucfirst($cat)) ?></a>
        <?php endforeach; ?>
    </section>

    <section class="help-grid" id="help-grid" aria-live="polite">
        <?php if ($articles === []): ?>
            <div class="help-empty">Aucun article trouvé pour cette recherche.</div>
        <?php endif; ?>

        <?php foreach ($articles as $article): ?>
            <article class="help-card" data-category="<?= htmlspecialchars((string) ($article['category'] ?? '')) ?>">
                <div class="help-card-head">
                    <span class="help-badge"><?= htmlspecialchars(strtoupper((string) ($article['category'] ?? 'general'))) ?></span>
                    <small><?= (int) ($article['views_count'] ?? 0) ?> vue(s)</small>
                </div>
                <h3><?= htmlspecialchars((string) ($article['title'] ?? 'Article d’aide')) ?></h3>
                <p><?= htmlspecialchars((string) ($article['excerpt'] ?? '')) ?></p>
                <div class="help-actions">
                    <a class="help-btn" href="/admin?module=aide&amp;action=article&amp;id=<?= urlencode((string) ($article['slug'] ?? $article['id'] ?? '')) ?>&amp;context=<?= urlencode($context) ?>">Lire</a>
                    <?php if (!empty($article['cta_url']) && !empty($article['cta_label'])): ?>
                        <a class="help-link" href="<?= htmlspecialchars((string) $article['cta_url']) ?>"><?= htmlspecialchars((string) $article['cta_label']) ?></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <style>
        .help-hero{background:#fff;border-radius:14px;padding:1.5rem;border:1px solid #e5e7eb;margin-bottom:1rem}
        .help-hero h1{margin:0 0 .5rem;color:#1f2937;font-size:1.45rem}
        .help-hero p{margin:.3rem 0;color:#6b7280}
        .help-search{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1rem}
        .help-search input{flex:1;min-width:220px;padding:.7rem .9rem;border:1px solid #d1d5db;border-radius:10px}
        .help-btn{display:inline-flex;align-items:center;justify-content:center;background:#1f3a5f;color:#fff;padding:.65rem .95rem;border-radius:10px;text-decoration:none;border:none;cursor:pointer}
        .help-context{margin-top:.8rem;font-size:.9rem}
        .help-categories{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem}
        .help-chip{padding:.4rem .8rem;background:#eef2ff;color:#1e3a8a;border-radius:999px;text-decoration:none;font-size:.86rem}
        .help-chip.is-active{background:#1e3a8a;color:#fff}
        .help-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
        .help-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem}
        .help-card h3{font-size:1.02rem;margin:.45rem 0;color:#111827}
        .help-card p{font-size:.9rem;color:#4b5563;line-height:1.45}
        .help-card-head{display:flex;justify-content:space-between;align-items:center}
        .help-badge{font-size:.68rem;background:#f3f4f6;color:#374151;padding:.2rem .55rem;border-radius:999px;letter-spacing:.04em}
        .help-actions{display:flex;justify-content:space-between;align-items:center;margin-top:.9rem}
        .help-link{font-size:.85rem;color:#1e40af;text-decoration:none}
        .help-empty{background:#fff;padding:1.2rem;border:1px dashed #d1d5db;border-radius:12px;color:#6b7280}
    </style>
    <?php
}
