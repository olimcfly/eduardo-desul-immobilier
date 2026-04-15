<?php

declare(strict_types=1);

/**
 * Template page CMS : merci après prise de contact
 *
 * Variables possibles :
 * - $page : array
 * - $sections : array
 */

function thank_contact_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function thank_contact_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function thank_contact_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

$pageData = thank_contact_safe_array($page ?? null);
$sectionsData = thank_contact_safe_array($sections ?? null);

$title = thank_contact_raw_string(
    $pageData['title']
    ?? 'Merci pour votre message'
);

$description = thank_contact_raw_string(
    $pageData['meta_description']
    ?? 'Votre demande a bien été envoyée. Nous revenons vers vous rapidement.'
);

$eyebrow = thank_contact_raw_string(
    $pageData['hero_eyebrow']
    ?? 'Message envoyé'
);

$primaryLabel = thank_contact_raw_string(
    $pageData['hero_primary_cta_label']
    ?? 'Retour à l’accueil'
);

$primaryUrl = thank_contact_raw_string(
    $pageData['hero_primary_cta_url']
    ?? '/'
);

$secondaryLabel = thank_contact_raw_string(
    $pageData['hero_secondary_cta_label']
    ?? 'Prendre rendez-vous'
);

$secondaryUrl = thank_contact_raw_string(
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

<main class="page page-thank-contact">
    <?php if ($hasSections && $sectionRendererCallable !== null): ?>
        <?php foreach ($sectionsData as $section): ?>
            <?php
            $sectionArray = thank_contact_safe_array($section);
            if ($sectionArray === []) {
                continue;
            }

            echo (string) $sectionRendererCallable($sectionArray);
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="thank-contact-hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= thank_contact_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($eyebrow !== ''): ?>
                    <p class="thank-contact-hero__eyebrow"><?= thank_contact_safe_string($eyebrow) ?></p>
                <?php endif; ?>

                <div class="thank-contact-hero__card card">
                    <div class="thank-contact-hero__icon" aria-hidden="true">✓</div>

                    <h1 class="thank-contact-hero__title"><?= thank_contact_safe_string($title) ?></h1>

                    <p class="thank-contact-hero__description"><?= thank_contact_safe_string($description) ?></p>

                    <div class="thank-contact-hero__info">
                        <p>Votre demande a bien été transmise.</p>
                        <p>Nous revenons vers vous dès que possible avec une réponse claire et adaptée à votre besoin.</p>
                    </div>

                    <div class="thank-contact-hero__actions">
                        <a class="btn btn-primary" href="<?= thank_contact_safe_string($primaryUrl) ?>">
                            <?= thank_contact_safe_string($primaryLabel) ?>
                        </a>
                        <a class="btn btn-secondary" href="<?= thank_contact_safe_string($secondaryUrl) ?>">
                            <?= thank_contact_safe_string($secondaryLabel) ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="thank-contact-content">
            <div class="container">
                <div class="thank-contact-content__grid">
                    <section class="card thank-contact-next">
                        <h2>Et maintenant ?</h2>
                        <ol class="thank-contact-steps">
                            <li>Nous lisons votre message.</li>
                            <li>Nous revenons vers vous avec la bonne orientation.</li>
                            <li>Si nécessaire, nous vous proposons un échange ou un rendez-vous.</li>
                        </ol>
                    </section>

                    <section class="card thank-contact-help">
                        <h2>Vous souhaitez aller plus vite ?</h2>
                        <p>
                            Si votre demande est urgente ou si vous préférez un échange direct,
                            vous pouvez réserver un créneau dès maintenant.
                        </p>
                        <a class="btn btn-primary" href="/rendez-vous">Réserver un créneau</a>
                    </section>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>