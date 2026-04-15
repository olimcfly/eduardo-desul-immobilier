<?php

declare(strict_types=1);

/**
 * Template page CMS : merci après téléchargement
 *
 * Variables possibles :
 * - $page : array
 * - $sections : array
 */

function thank_download_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function thank_download_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function thank_download_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

$pageData = thank_download_safe_array($page ?? null);
$sectionsData = thank_download_safe_array($sections ?? null);

$title = thank_download_raw_string(
    $pageData['title']
    ?? 'Merci pour votre téléchargement'
);

$description = thank_download_raw_string(
    $pageData['meta_description']
    ?? 'Votre ressource est prête. Vous pouvez la consulter maintenant ou poursuivre avec l’étape suivante.'
);

$eyebrow = thank_download_raw_string(
    $pageData['hero_eyebrow']
    ?? 'Téléchargement confirmé'
);

$downloadLabel = thank_download_raw_string(
    $pageData['download_label']
    ?? 'Télécharger la ressource'
);

$downloadUrl = thank_download_raw_string(
    $pageData['download_url']
    ?? '#'
);

$primaryLabel = thank_download_raw_string(
    $pageData['hero_primary_cta_label']
    ?? 'Prendre rendez-vous'
);

$primaryUrl = thank_download_raw_string(
    $pageData['hero_primary_cta_url']
    ?? '/rendez-vous'
);

$secondaryLabel = thank_download_raw_string(
    $pageData['hero_secondary_cta_label']
    ?? 'Retour à l’accueil'
);

$secondaryUrl = thank_download_raw_string(
    $pageData['hero_secondary_cta_url']
    ?? '/'
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
$hasDownloadLink = $downloadUrl !== '' && $downloadUrl !== '#';
?>

<main class="page page-thank-download">
    <?php if ($hasSections && $sectionRendererCallable !== null): ?>
        <?php foreach ($sectionsData as $section): ?>
            <?php
            $sectionArray = thank_download_safe_array($section);
            if ($sectionArray === []) {
                continue;
            }

            echo (string) $sectionRendererCallable($sectionArray);
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="thank-download-hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= thank_download_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($eyebrow !== ''): ?>
                    <p class="thank-download-hero__eyebrow"><?= thank_download_safe_string($eyebrow) ?></p>
                <?php endif; ?>

                <div class="thank-download-hero__card card">
                    <div class="thank-download-hero__icon" aria-hidden="true">✓</div>

                    <h1 class="thank-download-hero__title"><?= thank_download_safe_string($title) ?></h1>

                    <p class="thank-download-hero__description"><?= thank_download_safe_string($description) ?></p>

                    <div class="thank-download-hero__info">
                        <p>Votre ressource est prête.</p>
                        <p>Vous pouvez la télécharger maintenant et revenir dessus tranquillement ensuite.</p>
                    </div>

                    <div class="thank-download-hero__actions">
                        <?php if ($hasDownloadLink): ?>
                            <a class="btn btn-primary" href="<?= thank_download_safe_string($downloadUrl) ?>" target="_blank" rel="noopener noreferrer">
                                <?= thank_download_safe_string($downloadLabel) ?>
                            </a>
                        <?php endif; ?>

                        <a class="btn btn-secondary" href="<?= thank_download_safe_string($primaryUrl) ?>">
                            <?= thank_download_safe_string($primaryLabel) ?>
                        </a>

                        <a class="btn btn-secondary" href="<?= thank_download_safe_string($secondaryUrl) ?>">
                            <?= thank_download_safe_string($secondaryLabel) ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="thank-download-content">
            <div class="container">
                <div class="thank-download-content__grid">
                    <section class="card thank-download-next">
                        <h2>Ce que vous pouvez faire maintenant</h2>
                        <ol class="thank-download-steps">
                            <li>Télécharger et consulter la ressource.</li>
                            <li>Repérer les points qui concernent directement votre situation.</li>
                            <li>Prendre rendez-vous si vous voulez un avis plus personnalisé.</li>
                        </ol>
                    </section>

                    <section class="card thank-download-help">
                        <h2>Besoin d’un accompagnement plus concret ?</h2>
                        <p>
                            Une ressource vous aide à mieux comprendre. Un échange permet d’aller plus loin
                            et de transformer ça en plan d’action adapté à votre projet.
                        </p>
                        <a class="btn btn-primary" href="/rendez-vous">Réserver un créneau</a>
                    </section>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>