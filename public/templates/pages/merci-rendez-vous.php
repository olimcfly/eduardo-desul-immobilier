<?php

declare(strict_types=1);

/**
 * Template page CMS : merci après prise de rendez-vous
 *
 * Variables possibles :
 * - $page : array
 * - $sections : array
 */

function thank_appointment_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function thank_appointment_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function thank_appointment_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

$pageData = thank_appointment_safe_array($page ?? null);
$sectionsData = thank_appointment_safe_array($sections ?? null);

$title = thank_appointment_raw_string(
    $pageData['title']
    ?? 'Merci, votre rendez-vous est bien pris en compte'
);

$description = thank_appointment_raw_string(
    $pageData['meta_description']
    ?? 'Votre demande de rendez-vous a bien été enregistrée. Vous allez recevoir la suite des informations utiles.'
);

$eyebrow = thank_appointment_raw_string(
    $pageData['hero_eyebrow']
    ?? 'Rendez-vous confirmé'
);

$primaryLabel = thank_appointment_raw_string(
    $pageData['hero_primary_cta_label']
    ?? 'Retour à l’accueil'
);

$primaryUrl = thank_appointment_raw_string(
    $pageData['hero_primary_cta_url']
    ?? '/'
);

$secondaryLabel = thank_appointment_raw_string(
    $pageData['hero_secondary_cta_label']
    ?? 'Nous contacter'
);

$secondaryUrl = thank_appointment_raw_string(
    $pageData['hero_secondary_cta_url']
    ?? '/contact'
);

$calendarLabel = thank_appointment_raw_string(
    $pageData['calendar_label']
    ?? 'Ajouter à mon agenda'
);

$calendarUrl = thank_appointment_raw_string(
    $pageData['calendar_url']
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
$hasCalendarLink = $calendarUrl !== '';
?>

<main class="page page-thank-appointment">
    <?php if ($hasSections && $sectionRendererCallable !== null): ?>
        <?php foreach ($sectionsData as $section): ?>
            <?php
            $sectionArray = thank_appointment_safe_array($section);
            if ($sectionArray === []) {
                continue;
            }

            echo (string) $sectionRendererCallable($sectionArray);
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="thank-appointment-hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= thank_appointment_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($eyebrow !== ''): ?>
                    <p class="thank-appointment-hero__eyebrow"><?= thank_appointment_safe_string($eyebrow) ?></p>
                <?php endif; ?>

                <div class="thank-appointment-hero__card card">
                    <div class="thank-appointment-hero__icon" aria-hidden="true">✓</div>

                    <h1 class="thank-appointment-hero__title"><?= thank_appointment_safe_string($title) ?></h1>

                    <p class="thank-appointment-hero__description"><?= thank_appointment_safe_string($description) ?></p>

                    <div class="thank-appointment-hero__info">
                        <p>Votre créneau a bien été demandé ou réservé.</p>
                        <p>
                            Pensez à vérifier vos emails et vos messages si une confirmation,
                            un lien de visio ou un rappel doit vous être envoyé.
                        </p>
                    </div>

                    <div class="thank-appointment-hero__actions">
                        <?php if ($hasCalendarLink): ?>
                            <a class="btn btn-primary" href="<?= thank_appointment_safe_string($calendarUrl) ?>" target="_blank" rel="noopener noreferrer">
                                <?= thank_appointment_safe_string($calendarLabel) ?>
                            </a>
                        <?php else: ?>
                            <a class="btn btn-primary" href="<?= thank_appointment_safe_string($primaryUrl) ?>">
                                <?= thank_appointment_safe_string($primaryLabel) ?>
                            </a>
                        <?php endif; ?>

                        <a class="btn btn-secondary" href="<?= thank_appointment_safe_string($secondaryUrl) ?>">
                            <?= thank_appointment_safe_string($secondaryLabel) ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="thank-appointment-content">
            <div class="container">
                <div class="thank-appointment-content__grid">
                    <section class="card thank-appointment-next">
                        <h2>Avant le rendez-vous</h2>
                        <ol class="thank-appointment-steps">
                            <li>Gardez un œil sur votre boîte email et vos notifications.</li>
                            <li>Préparez les points que vous souhaitez aborder.</li>
                            <li>Rassemblez les informations utiles si votre projet concerne un bien précis.</li>
                        </ol>
                    </section>

                    <section class="card thank-appointment-help">
                        <h2>Un changement ou une question ?</h2>
                        <p>
                            Si vous devez ajuster votre demande ou transmettre une précision importante,
                            passez par la page contact.
                        </p>
                        <a class="btn btn-primary" href="/contact">Nous écrire</a>
                    </section>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>