<?php

declare(strict_types=1);

/**
 * Template page CMS : merci après demande d'estimation
 *
 * Variables possibles :
 * - $page : array
 * - $sections : array
 */

function thank_estimation_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function thank_estimation_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function thank_estimation_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

$pageData = thank_estimation_safe_array($page ?? null);
$sectionsData = thank_estimation_safe_array($sections ?? null);

$title = thank_estimation_raw_string(
    $pageData['title']
    ?? 'Merci pour votre demande d’estimation'
);

$description = thank_estimation_raw_string(
    $pageData['meta_description']
    ?? 'Votre demande a bien été envoyée. Nous allons analyser votre bien et revenir vers vous avec une estimation sérieuse.'
);

$eyebrow = thank_estimation_raw_string(
    $pageData['hero_eyebrow']
    ?? 'Demande envoyée'
);

$primaryLabel = thank_estimation_raw_string(
    $pageData['hero_primary_cta_label']
    ?? 'Retour à l’accueil'
);

$primaryUrl = thank_estimation_raw_string(
    $pageData['hero_primary_cta_url']
    ?? '/'
);

$secondaryLabel = thank_estimation_raw_string(
    $pageData['hero_secondary_cta_label']
    ?? 'Prendre rendez-vous'
);

$secondaryUrl = thank_estimation_raw_string(
    $pageData['hero_secondary_cta_url']
    ?? '/rendez-vous'
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
?>

<main class="page page-thank-estimation">
    <?php if ($hasSections && $sectionRendererCallable !== null): ?>
        <?php foreach ($sectionsData as $section): ?>
            <?php
            $sectionArray = thank_estimation_safe_array($section);
            if ($sectionArray === []) {
                continue;
            }

            echo (string) $sectionRendererCallable($sectionArray);
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="thank-estimation-hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= thank_estimation_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($eyebrow !== ''): ?>
                    <p class="thank-estimation-hero__eyebrow"><?= thank_estimation_safe_string($eyebrow) ?></p>
                <?php endif; ?>

                <div class="thank-estimation-hero__card card">
                    <div class="thank-estimation-hero__icon" aria-hidden="true">✓</div>

                    <h1 class="thank-estimation-hero__title"><?= thank_estimation_safe_string($title) ?></h1>

                    <p class="thank-estimation-hero__description"><?= thank_estimation_safe_string($description) ?></p>

                    <div class="thank-estimation-hero__info">
                        <p>Votre demande a bien été enregistrée.</p>
                        <p>Nous allons étudier les éléments transmis pour vous apporter un retour utile, cohérent et réaliste.</p>
                    </div>

                    <div class="thank-estimation-hero__actions">
                        <a class="btn btn-primary" href="<?= thank_estimation_safe_string($primaryUrl) ?>">
                            <?= thank_estimation_safe_string($primaryLabel) ?>
                        </a>
                        <a class="btn btn-secondary" href="<?= thank_estimation_safe_string($secondaryUrl) ?>">
                            <?= thank_estimation_safe_string($secondaryLabel) ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="thank-estimation-content">
            <div class="container">
                <div class="thank-estimation-content__grid">
                    <section class="card thank-estimation-next">
                        <h2>La suite</h2>
                        <ol class="thank-estimation-steps">
                            <li>Nous analysons votre bien et son contexte local.</li>
                            <li>Nous croisons les informations avec le marché du secteur.</li>
                            <li>Nous revenons vers vous avec une estimation ou un échange complémentaire si nécessaire.</li>
                        </ol>
                    </section>

                    <section class="card thank-estimation-help">
                        <h2>Besoin d’aller plus loin ?</h2>
                        <p>
                            Si vous souhaitez parler directement de votre projet de vente,
                            de votre délai ou de votre stratégie, vous pouvez réserver un créneau.
                        </p>
                        <a class="btn btn-primary" href="/rendez-vous">Réserver un créneau</a>
                    </section>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>