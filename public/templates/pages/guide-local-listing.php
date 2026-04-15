<?php

declare(strict_types=1);

/**
 * Template page CMS : listing guide local
 *
 * Variables attendues si disponibles :
 * - $page : array
 * - $sections : array
 * - $guides : array
 * - $items : array
 * - $entries : array
 */

function guide_local_listing_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function guide_local_listing_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function guide_local_listing_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

function guide_local_listing_format_count(int $count, string $singular, string $plural): string
{
    return $count <= 1 ? $count . ' ' . $singular : $count . ' ' . $plural;
}

$pageData = guide_local_listing_safe_array($page ?? null);
$sectionsData = guide_local_listing_safe_array($sections ?? null);

$guidesData = guide_local_listing_safe_array(
    $guides
    ?? $items
    ?? $entries
    ?? null
);

$title = guide_local_listing_raw_string(
    $pageData['title']
    ?? 'Guide local'
);

$intro = guide_local_listing_raw_string(
    $pageData['meta_description']
    ?? $pageData['excerpt']
    ?? 'Retrouvez les bonnes adresses, services, commerces et repères utiles de votre secteur.'
);

$heroEyebrow = guide_local_listing_raw_string(
    $pageData['hero_eyebrow']
    ?? 'Vie locale'
);

$heroCtaLabel = guide_local_listing_raw_string(
    $pageData['hero_cta_label']
    ?? 'Nous contacter'
);

$heroCtaUrl = guide_local_listing_raw_string(
    $pageData['hero_cta_url']
    ?? '/contact'
);

$sectionRendererCallable = null;

if (isset($sectionRenderer) && is_callable($sectionRenderer)) {
    $sectionRendererCallable = $sectionRenderer;
} elseif (class_exists('\\App\\Support\\SectionRenderer') && method_exists('\\App\\Support\\SectionRenderer', 'render')) {
    $sectionRendererCallable = static fn (array $section): string => \App\Support\SectionRenderer::render($section);
} elseif (class_exists('\\App\\Cms\\SectionRenderer') && method_exists('\\App\\Cms\\SectionRenderer', 'render')) {
    $sectionRendererCallable = static fn (array $section): string => \App\Cms\SectionRenderer::render($section);
}

$hasSections = $sectionsData !== [];
$hasGuides = $guidesData !== [];
?>

<main class="page page-guide-local-listing">
    <?php if ($hasSections && $sectionRendererCallable !== null): ?>
        <?php foreach ($sectionsData as $section): ?>
            <?php
            $sectionArray = guide_local_listing_safe_array($section);
            if ($sectionArray === []) {
                continue;
            }

            echo (string) $sectionRendererCallable($sectionArray);
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="guide-local-listing__hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= guide_local_listing_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($heroEyebrow !== ''): ?>
                    <p class="guide-local-listing__eyebrow"><?= guide_local_listing_safe_string($heroEyebrow) ?></p>
                <?php endif; ?>

                <h1 class="guide-local-listing__title"><?= guide_local_listing_safe_string($title) ?></h1>

                <?php if ($intro !== ''): ?>
                    <p class="guide-local-listing__intro"><?= guide_local_listing_safe_string($intro) ?></p>
                <?php endif; ?>

                <div class="guide-local-listing__hero-actions">
                    <a class="btn btn-primary" href="<?= guide_local_listing_safe_string($heroCtaUrl) ?>">
                        <?= guide_local_listing_safe_string($heroCtaLabel) ?>
                    </a>
                    <a class="btn btn-secondary" href="/services">Découvrir nos services</a>
                </div>
            </div>
        </section>

        <section class="guide-local-listing__content">
            <div class="container">
                <div class="guide-local-listing__topbar">
                    <div>
                        <h2>Les adresses et repères du secteur</h2>
                        <p>
                            <?= guide_local_listing_safe_string(
                                guide_local_listing_format_count(count($guidesData), 'fiche disponible', 'fiches disponibles')
                            ) ?>
                        </p>
                    </div>

                    <form class="guide-local-listing__filters" method="get" action="">
                        <label class="sr-only" for="guide-search">Rechercher</label>
                        <input
                            id="guide-search"
                            type="search"
                            name="q"
                            value="<?= guide_local_listing_safe_string($_GET['q'] ?? '') ?>"
                            placeholder="Rechercher un commerce, un service, un lieu…"
                        >
                    </form>
                </div>

                <?php if ($hasGuides): ?>
                    <div class="guide-local-listing__grid">
                        <?php foreach ($guidesData as $item): ?>
                            <?php
                            $guide = guide_local_listing_safe_array($item);
                            if ($guide === []) {
                                continue;
                            }

                            $itemTitle = guide_local_listing_raw_string(
                                $guide['title']
                                ?? $guide['name']
                                ?? 'Fiche locale'
                            );

                            $itemUrl = guide_local_listing_raw_string(
                                $guide['url']
                                ?? $guide['slug']
                                ?? '#'
                            );

                            if ($itemUrl !== '#' && !str_starts_with($itemUrl, '/')) {
                                $itemUrl = '/' . ltrim($itemUrl, '/');
                            }

                            $itemExcerpt = guide_local_listing_raw_string(
                                $guide['excerpt']
                                ?? $guide['summary']
                                ?? $guide['description']
                                ?? ''
                            );

                            $itemCategory = guide_local_listing_raw_string(
                                $guide['category']
                                ?? $guide['category_name']
                                ?? ''
                            );

                            $itemAddress = guide_local_listing_raw_string(
                                $guide['address']
                                ?? $guide['city']
                                ?? ''
                            );

                            $itemImage = guide_local_listing_raw_string(
                                $guide['image']
                                ?? $guide['cover_image']
                                ?? $guide['thumbnail']
                                ?? ''
                            );
                            ?>
                            <article class="card guide-local-card">
                                <?php if ($itemImage !== ''): ?>
                                    <a class="guide-local-card__image-link" href="<?= guide_local_listing_safe_string($itemUrl) ?>">
                                        <img
                                            class="guide-local-card__image"
                                            src="<?= guide_local_listing_safe_string($itemImage) ?>"
                                            alt="<?= guide_local_listing_safe_string($itemTitle) ?>"
                                            loading="lazy"
                                        >
                                    </a>
                                <?php endif; ?>

                                <div class="guide-local-card__body">
                                    <?php if ($itemCategory !== ''): ?>
                                        <p class="guide-local-card__category"><?= guide_local_listing_safe_string($itemCategory) ?></p>
                                    <?php endif; ?>

                                    <h3 class="guide-local-card__title">
                                        <a href="<?= guide_local_listing_safe_string($itemUrl) ?>">
                                            <?= guide_local_listing_safe_string($itemTitle) ?>
                                        </a>
                                    </h3>

                                    <?php if ($itemAddress !== ''): ?>
                                        <p class="guide-local-card__address"><?= guide_local_listing_safe_string($itemAddress) ?></p>
                                    <?php endif; ?>

                                    <?php if ($itemExcerpt !== ''): ?>
                                        <p class="guide-local-card__excerpt"><?= guide_local_listing_safe_string($itemExcerpt) ?></p>
                                    <?php endif; ?>

                                    <div class="guide-local-card__actions">
                                        <a class="btn btn-secondary" href="<?= guide_local_listing_safe_string($itemUrl) ?>">
                                            Voir la fiche
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <section class="card guide-local-listing__empty">
                        <h3>Guide en cours de remplissage</h3>
                        <p>
                            Les fiches locales arrivent bientôt. Vous pourrez retrouver ici les commerces,
                            entreprises et lieux utiles du secteur.
                        </p>
                    </section>
                <?php endif; ?>
            </div>
        </section>

        <section class="guide-local-listing__cta">
            <div class="container">
                <div class="card guide-local-listing__cta-card">
                    <div>
                        <p class="guide-local-listing__cta-kicker">Besoin d’un regard local</p>
                        <h2>Vous cherchez un secteur, un service ou un professionnel de confiance ?</h2>
                        <p>
                            Nous pouvons aussi vous orienter selon votre projet immobilier,
                            votre quartier ou vos besoins du quotidien.
                        </p>
                    </div>
                    <div class="guide-local-listing__cta-actions">
                        <a class="btn btn-primary" href="/contact">Nous écrire</a>
                        <a class="btn btn-secondary" href="/rendez-vous">Prendre rendez-vous</a>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>