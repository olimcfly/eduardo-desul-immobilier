<?php

declare(strict_types=1);

$userId = (int)(Auth::user()['id'] ?? 0);
$id = (int)($_GET['id'] ?? 0);
$page = $id > 0 ? $cityPageService->findForUser($id, $userId) : null;

if ($page === null) {
    ?>
    <section class="seo-section fiche-ville-module">
        <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; <a href="/admin?module=seo&action=villes">Fiches villes</a> &gt; Prévisualisation</div>
        <h2>Prévisualisation indisponible</h2>
        <p>Cette fiche n'existe pas ou n'est plus accessible.</p>
        <a class="btn btn-sm" href="/admin?module=seo&action=villes">Retour à la liste</a>
    </section>
    <?php
    return;
}

$faqList = is_array($page['faq_list'] ?? null) ? $page['faq_list'] : [];
$links = is_array($page['internal_links_list'] ?? null) ? $page['internal_links_list'] : [];
?>
<section class="seo-section fiche-ville-module">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; <a href="/admin?module=seo&action=villes">Fiches villes</a> &gt; Prévisualisation</div>
    <div class="fv-head">
        <h2>👀 Preview — <?= e((string)$page['city_name']) ?></h2>
        <a class="btn btn-sm" href="/admin?module=seo&action=ville-edit&id=<?= $id ?>">Modifier</a>
    </div>

    <article class="fv-preview">
        <header>
            <h1><?= e((string)$page['h1']) ?></h1>
            <p class="muted">Slug : /<?= e((string)$page['slug']) ?>/ · Canonical : <?= e((string)($page['canonical_url'] ?: 'non définie')) ?></p>
        </header>

        <section>
            <h3>Introduction</h3>
            <p><?= nl2br(e((string)$page['intro'])) ?></p>
        </section>

        <section>
            <h3>Marché local</h3>
            <p><?= nl2br(e((string)$page['market_block'])) ?></p>
        </section>

        <section>
            <h3>FAQ</h3>
            <?php if ($faqList === []): ?>
                <p class="muted">Aucune question fréquente renseignée.</p>
            <?php else: ?>
                <?php foreach ($faqList as $faq): ?>
                    <details>
                        <summary><?= e((string)($faq['q'] ?? 'Question')) ?></summary>
                        <p><?= nl2br(e((string)($faq['a'] ?? ''))) ?></p>
                    </details>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section>
            <h3>Liens internes recommandés</h3>
            <?php if ($links === []): ?>
                <p class="muted">Aucun lien interne défini.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($links as $link): ?>
                        <li><?= e((string)($link['label'] ?? 'Lien')) ?> → <?= e((string)($link['url'] ?? '#')) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </article>
</section>
