<?php

declare(strict_types=1);

/**
 * Template page CMS : fiche guide local
 *
 * Variables attendues si disponibles :
 * - $page : array
 * - $sections : array
 * - $guide : array
 * - $item : array
 * - $entry : array
 */

function guide_local_single_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function guide_local_single_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function guide_local_single_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

function guide_local_single_normalize_url(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (preg_match('~^https?://~i', $value) === 1) {
        return $value;
    }

    return 'https://' . ltrim($value, '/');
}

$pageData = guide_local_single_safe_array($page ?? null);
$sectionsData = guide_local_single_safe_array($sections ?? null);

$guideData = guide_local_single_safe_array(
    $guide
    ?? $item
    ?? $entry
    ?? null
);

$title = guide_local_single_raw_string(
    $guideData['title']
    ?? $guideData['name']
    ?? $pageData['title']
    ?? 'Fiche locale'
);

$excerpt = guide_local_single_raw_string(
    $guideData['excerpt']
    ?? $guideData['summary']
    ?? $guideData['description']
    ?? $pageData['meta_description']
    ?? ''
);

$content = (string) (
    $guideData['content_html']
    ?? $guideData['content']
    ?? $guideData['body']
    ?? ''
);

$category = guide_local_single_raw_string(
    $guideData['category']
    ?? $guideData['category_name']
    ?? ''
);

$image = guide_local_single_raw_string(
    $guideData['image']
    ?? $guideData['cover_image']
    ?? $guideData['featured_image']
    ?? $guideData['thumbnail']
    ?? ''
);

$address = guide_local_single_raw_string(
    $guideData['address']
    ?? ''
);

$city = guide_local_single_raw_string(
    $guideData['city']
    ?? ''
);

$postalCode = guide_local_single_raw_string(
    $guideData['postal_code']
    ?? $guideData['zipcode']
    ?? ''
);

$phone = guide_local_single_raw_string(
    $guideData['phone']
    ?? $guideData['telephone']
    ?? ''
);

$email = guide_local_single_raw_string(
    $guideData['email']
    ?? ''
);

$website = guide_local_single_normalize_url(
    guide_local_single_raw_string(
        $guideData['website']
        ?? $guideData['site_url']
        ?? $guideData['url']
        ?? ''
    )
);

$openingHours = guide_local_single_safe_array(
    $guideData['opening_hours']
    ?? $guideData['hours']
    ?? null
);

$services = guide_local_single_safe_array(
    $guideData['services']
    ?? $guideData['features']
    ?? null
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
$hasContent = trim($content) !== '';

$fullLocation = trim(implode(' ', array_filter([$postalCode, $city], static fn (string $value): bool => $value !== '')));
?>

<main class="page page-guide-local-single">
    <article class="guide-local-single">
        <header class="guide-local-single__hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item"><a href="/guide-local">Guide local</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= guide_local_single_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($category !== ''): ?>
                    <p class="guide-local-single__category"><?= guide_local_single_safe_string($category) ?></p>
                <?php endif; ?>

                <div class="guide-local-single__hero-grid">
                    <div class="guide-local-single__hero-content">
                        <h1 class="guide-local-single__title"><?= guide_local_single_safe_string($title) ?></h1>

                        <?php if ($excerpt !== ''): ?>
                            <p class="guide-local-single__excerpt"><?= guide_local_single_safe_string($excerpt) ?></p>
                        <?php endif; ?>

                        <div class="guide-local-single__meta">
                            <?php if ($address !== ''): ?>
                                <span><?= guide_local_single_safe_string($address) ?></span>
                            <?php endif; ?>

                            <?php if ($fullLocation !== ''): ?>
                                <span><?= guide_local_single_safe_string($fullLocation) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="guide-local-single__actions">
                            <?php if ($phone !== ''): ?>
                                <a class="btn btn-primary" href="tel:<?= guide_local_single_safe_string(preg_replace('/\s+/', '', $phone)) ?>">
                                    Appeler
                                </a>
                            <?php endif; ?>

                            <?php if ($website !== ''): ?>
                                <a class="btn btn-secondary" href="<?= guide_local_single_safe_string($website) ?>" target="_blank" rel="noopener noreferrer">
                                    Visiter le site
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($image !== ''): ?>
                        <figure class="guide-local-single__hero-media">
                            <img
                                src="<?= guide_local_single_safe_string($image) ?>"
                                alt="<?= guide_local_single_safe_string($title) ?>"
                                loading="lazy"
                            >
                        </figure>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <?php if ($hasSections && $sectionRendererCallable !== null): ?>
            <div class="guide-local-single__sections">
                <?php foreach ($sectionsData as $section): ?>
                    <?php
                    $sectionArray = guide_local_single_safe_array($section);
                    if ($sectionArray === []) {
                        continue;
                    }

                    echo (string) $sectionRendererCallable($sectionArray);
                    ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="container">
                <div class="guide-local-single__layout">
                    <div class="guide-local-single__main">
                        <?php if ($hasContent): ?>
                            <section class="card guide-local-single__content-card">
                                <div class="guide-local-single__content content-editor">
                                    <?= $content ?>
                                </div>
                            </section>
                        <?php else: ?>
                            <section class="card guide-local-single__content-card">
                                <h2>Présentation</h2>
                                <p>
                                    Cette fiche locale sera bientôt enrichie avec plus d’informations
                                    sur ce lieu, ses services et son intérêt dans le secteur.
                                </p>
                            </section>
                        <?php endif; ?>

                        <?php if ($services !== []): ?>
                            <section class="card guide-local-single__services-card">
                                <h2>Services / points forts</h2>
                                <ul class="guide-local-single__services-list">
                                    <?php foreach ($services as $service): ?>
                                        <?php $serviceLabel = trim((string) $service); ?>
                                        <?php if ($serviceLabel === '') continue; ?>
                                        <li><?= guide_local_single_safe_string($serviceLabel) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        <?php endif; ?>
                    </div>

                    <aside class="guide-local-single__sidebar">
                        <section class="card guide-local-single__info-card">
                            <h2>Informations pratiques</h2>

                            <dl class="guide-local-single__info-list">
                                <?php if ($address !== ''): ?>
                                    <div>
                                        <dt>Adresse</dt>
                                        <dd><?= guide_local_single_safe_string($address) ?></dd>
                                    </div>
                                <?php endif; ?>

                                <?php if ($fullLocation !== ''): ?>
                                    <div>
                                        <dt>Ville</dt>
                                        <dd><?= guide_local_single_safe_string($fullLocation) ?></dd>
                                    </div>
                                <?php endif; ?>

                                <?php if ($phone !== ''): ?>
                                    <div>
                                        <dt>Téléphone</dt>
                                        <dd>
                                            <a href="tel:<?= guide_local_single_safe_string(preg_replace('/\s+/', '', $phone)) ?>">
                                                <?= guide_local_single_safe_string($phone) ?>
                                            </a>
                                        </dd>
                                    </div>
                                <?php endif; ?>

                                <?php if ($email !== ''): ?>
                                    <div>
                                        <dt>Email</dt>
                                        <dd>
                                            <a href="mailto:<?= guide_local_single_safe_string($email) ?>">
                                                <?= guide_local_single_safe_string($email) ?>
                                            </a>
                                        </dd>
                                    </div>
                                <?php endif; ?>

                                <?php if ($website !== ''): ?>
                                    <div>
                                        <dt>Site web</dt>
                                        <dd>
                                            <a href="<?= guide_local_single_safe_string($website) ?>" target="_blank" rel="noopener noreferrer">
                                                Consulter
                                            </a>
                                        </dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        </section>

                        <?php if ($openingHours !== []): ?>
                            <section class="card guide-local-single__hours-card">
                                <h2>Horaires</h2>
                                <ul class="guide-local-single__hours-list">
                                    <?php foreach ($openingHours as $key => $value): ?>
                                        <?php
                                        $day = trim((string) $key);
                                        $hours = trim((string) $value);

                                        if ($day === '' && $hours === '') {
                                            continue;
                                        }
                                        ?>
                                        <li>
                                            <?php if ($day !== ''): ?>
                                                <strong><?= guide_local_single_safe_string($day) ?> :</strong>
                                            <?php endif; ?>
                                            <span><?= guide_local_single_safe_string($hours) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        <?php endif; ?>

                        <section class="card guide-local-single__cta-card">
                            <p class="guide-local-single__cta-kicker">Projet immobilier local</p>
                            <h2>Besoin d’un conseil sur le secteur ?</h2>
                            <p>
                                Nous pouvons aussi vous aider à mieux comprendre le quartier,
                                ses services, son cadre de vie et son attractivité immobilière.
                            </p>
                            <div class="guide-local-single__cta-actions">
                                <a class="btn btn-primary" href="/contact">Nous contacter</a>
                                <a class="btn btn-secondary" href="/rendez-vous">Prendre rendez-vous</a>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        <?php endif; ?>
    </article>
</main>