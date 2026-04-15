<?php

declare(strict_types=1);

function renderHelpArticlePage(HelpCenterService $service, string $context): void
{
    $id = (string) ($_GET['id'] ?? '');
    $article = $service->getArticle($id);

    if ($article === null) {
        echo '<div class="help-article"><p>Article introuvable.</p><a href="/admin?module=aide">Retour au centre d\'aide</a></div>';
        return;
    }

    $user = Auth::user();
    $service->recordView((int) ($user['id'] ?? 0), $article, $context);
    ?>
    <article class="help-article">
        <a class="help-back" href="/admin?module=aide&amp;context=<?= urlencode($context) ?>">← Retour aux articles</a>
        <header>
            <span class="help-tag"><?= htmlspecialchars((string) ($article['category'] ?? 'general')) ?></span>
            <h1><?= htmlspecialchars((string) ($article['title'] ?? 'Article')) ?></h1>
            <p><?= htmlspecialchars((string) ($article['excerpt'] ?? '')) ?></p>
        </header>
        <div class="help-article-content"><?= nl2br(htmlspecialchars((string) ($article['content'] ?? ''))) ?></div>
        <?php if (!empty($article['cta_url']) && !empty($article['cta_label'])): ?>
            <a class="help-btn" href="<?= htmlspecialchars((string) $article['cta_url']) ?>"><?= htmlspecialchars((string) $article['cta_label']) ?></a>
        <?php endif; ?>
    </article>

    <style>
        .help-article{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:1.4rem;max-width:860px}
        .help-back{color:#1d4ed8;text-decoration:none;font-size:.92rem}
        .help-tag{display:inline-block;margin-top:.7rem;background:#eef2ff;color:#3730a3;padding:.2rem .6rem;border-radius:999px;font-size:.72rem}
        .help-article h1{margin:.7rem 0 .35rem;font-size:1.5rem;color:#111827}
        .help-article p{color:#4b5563}
        .help-article-content{margin:1rem 0 1.2rem;line-height:1.65;color:#1f2937}
        .help-btn{display:inline-flex;background:#1f3a5f;color:#fff;padding:.7rem 1rem;border-radius:10px;text-decoration:none}
    </style>
    <?php
}
