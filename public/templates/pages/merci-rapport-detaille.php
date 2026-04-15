<?php

declare(strict_types=1);

/**
 * Template page CMS : merci après demande / accès au rapport détaillé
 *
 * Variables possibles :
 * - $page : array
 * - $sections : array
 */

function thank_report_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function thank_report_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function thank_report_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

$pageData = thank_report_safe_array($page ?? null);
$sectionsData = thank_report_safe_array($sections ?? null);

$title = thank_report_raw_string(
    $pageData['title']
    ?? 'Merci, votre rapport détaillé est en cours de préparation'
);

$description = thank_report_raw_string(
    $pageData['meta_description']
    ?? 'Votre demande a bien été prise en compte. Nous préparons un retour plus détaillé et plus utile que la simple estimation rapide.'
);

$eyebrow = thank_report_raw_string(
    $pageData['hero_eyebrow']
    ?? 'Demande confirmée'
);

$primaryLabel = thank_report_raw_string(
    $pageData['hero_primary_cta_label']
    ?? 'Prendre rendez-vous'
);

$primaryUrl = thank_report_raw_string(
    $pageData['hero_primary_cta_url']
    ?? '/rendez-vous'
);

$secondaryLabel = thank_report_raw_string(
    $pageData['hero_secondary_cta_label']
    ?? 'Retour à l’accueil'
);

$secondaryUrl = thank_report_raw_string(
    $pageData['hero_secondary_cta_url']
    ?? '/'
);

$reportLabel = thank_report_raw_string(
    $pageData['report_label']
    ?? 'Voir mon rapport'
);

$reportUrl = thank_report_raw_string(
    $pageData['report_url']
    ?? ''
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
$hasReportLink = $reportUrl !== '';
?>

<main class="page page-thank-report">
    <?php if ($hasSections && $sectionRendererCallable !== null): ?>
        <?php foreach ($sectionsData as $section): ?>
            <?php
            $sectionArray = thank_report_safe_array($section);
            if ($sectionArray === []) {
                continue;
            }

            echo (string) $sectionRendererCallable($sectionArray);
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="thank-report-hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= thank_report_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($eyebrow !== ''): ?>
                    <p class="thank-report-hero__eyebrow"><?= thank_report_safe_string($eyebrow) ?></p>
                <?php endif; ?>

                <div class="thank-report-hero__card card">
                    <div class="thank-report-hero__icon" aria-hidden="true">✓</div>

                    <h1 class="thank-report-hero__title"><?= thank_report_safe_string($title) ?></h1>

                    <p class="thank-report-hero__description"><?= thank_report_safe_string($description) ?></p>

                    <div class="thank-report-hero__info">
                        <p>Votre demande a bien été enregistrée.</p>
                        <p>
                            Un rapport détaillé demande plus qu’un simple calcul automatique :
                            il faut remettre le bien, le secteur et le contexte de vente dans une lecture cohérente.
                        </p>
                    </div>

                    <div class="thank-report-hero__actions">
                        <?php if ($hasReportLink): ?>
                            <a class="btn btn-primary" href="<?= thank_report_safe_string($reportUrl) ?>">
                                <?= thank_report_safe_string($reportLabel) ?>
                            </a>
                        <?php else: ?>
                            <a class="btn btn-primary" href="<?= thank_report_safe_string($primaryUrl) ?>">
                                <?= thank_report_safe_string($primaryLabel) ?>
                            </a>
                        <?php endif; ?>

                        <a class="btn btn-secondary" href="<?= thank_report_safe_string($secondaryUrl) ?>">
                            <?= thank_report_safe_string($secondaryLabel) ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="thank-report-content">
            <div class="container">
                <div class="thank-report-content__grid">
                    <section class="card thank-report-next">
                        <h2>Ce que comprend la suite</h2>
                        <ol class="thank-report-steps">
                            <li>Analyse des informations transmises sur le bien.</li>
                            <li>Lecture du marché local et du positionnement possible.</li>
                            <li>Retour plus détaillé pour vous aider à décider plus lucidement.</li>
                        </ol>
                    </section>

                    <section class="card thank-report-help">
                        <h2>Vous voulez un échange plus direct ?</h2>
                        <p>
                            Si vous préférez parler de votre situation avec un regard humain,
                            vous pouvez réserver un créneau dès maintenant.
                        </p>
                        <a class="btn btn-primary" href="/rendez-vous">Réserver un créneau</a>
                    </section>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>