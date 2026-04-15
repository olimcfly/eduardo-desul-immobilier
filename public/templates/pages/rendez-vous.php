<?php

declare(strict_types=1);

/**
 * Template page CMS : prise de rendez-vous
 *
 * Variables possibles :
 * - $page : array
 * - $sections : array
 */

function appointment_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function appointment_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function appointment_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

$pageData = appointment_safe_array($page ?? null);
$sectionsData = appointment_safe_array($sections ?? null);

$title = appointment_raw_string(
    $pageData['title']
    ?? 'Prendre rendez-vous'
);

$description = appointment_raw_string(
    $pageData['meta_description']
    ?? 'Réservez un échange pour parler de votre projet immobilier, de votre estimation ou de votre situation.'
);

$heroEyebrow = appointment_raw_string(
    $pageData['hero_eyebrow']
    ?? 'Échange personnalisé'
);

$heroPrimaryLabel = appointment_raw_string(
    $pageData['hero_primary_cta_label']
    ?? 'Réserver un rendez-vous'
);

$heroPrimaryUrl = appointment_raw_string(
    $pageData['hero_primary_cta_url']
    ?? '#form-rendez-vous'
);

$heroSecondaryLabel = appointment_raw_string(
    $pageData['hero_secondary_cta_label']
    ?? 'Nous contacter'
);

$heroSecondaryUrl = appointment_raw_string(
    $pageData['hero_secondary_cta_url']
    ?? '/contact'
);

$calendarEmbed = appointment_raw_string(
    $pageData['calendar_embed']
    ?? ''
);

$calendarUrl = appointment_raw_string(
    $pageData['calendar_url']
    ?? ''
);

$formAction = appointment_raw_string(
    $pageData['form_action']
    ?? ''
);

$formMethod = appointment_raw_string(
    $pageData['form_method']
    ?? 'post'
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

<main class="page page-appointment">
    <?php if ($hasSections && $sectionRendererCallable !== null): ?>
        <?php foreach ($sectionsData as $section): ?>
            <?php
            $sectionArray = appointment_safe_array($section);
            if ($sectionArray === []) {
                continue;
            }

            echo (string) $sectionRendererCallable($sectionArray);
            ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="appointment-hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= appointment_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($heroEyebrow !== ''): ?>
                    <p class="appointment-hero__eyebrow"><?= appointment_safe_string($heroEyebrow) ?></p>
                <?php endif; ?>

                <div class="appointment-hero__grid">
                    <div class="appointment-hero__content">
                        <h1 class="appointment-hero__title"><?= appointment_safe_string($title) ?></h1>
                        <p class="appointment-hero__description"><?= appointment_safe_string($description) ?></p>

                        <ul class="appointment-hero__points">
                            <li>Un échange clair sur votre situation et vos objectifs.</li>
                            <li>Des réponses concrètes, sans jargon inutile.</li>
                            <li>Une orientation adaptée à votre projet immobilier local.</li>
                        </ul>

                        <div class="appointment-hero__actions">
                            <a class="btn btn-primary" href="<?= appointment_safe_string($heroPrimaryUrl) ?>">
                                <?= appointment_safe_string($heroPrimaryLabel) ?>
                            </a>
                            <a class="btn btn-secondary" href="<?= appointment_safe_string($heroSecondaryUrl) ?>">
                                <?= appointment_safe_string($heroSecondaryLabel) ?>
                            </a>
                        </div>
                    </div>

                    <div class="appointment-hero__card card">
                        <h2>Ce rendez-vous est fait pour vous si…</h2>
                        <ul class="appointment-hero__checklist">
                            <li>Vous voulez vendre et vous avez besoin d’un plan clair.</li>
                            <li>Vous avez besoin d’une estimation ou d’un avis de valeur.</li>
                            <li>Vous voulez faire le point avant de vous engager.</li>
                            <li>Vous cherchez un accompagnement local plus humain.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="appointment-content">
            <div class="container">
                <div class="appointment-content__grid">
                    <div class="appointment-content__main">
                        <?php if ($calendarEmbed !== ''): ?>
                            <section class="card appointment-calendar">
                                <h2>Choisissez votre créneau</h2>
                                <div class="appointment-calendar__embed">
                                    <?= $calendarEmbed ?>
                                </div>
                            </section>
                        <?php elseif ($calendarUrl !== ''): ?>
                            <section class="card appointment-calendar">
                                <h2>Réserver directement en ligne</h2>
                                <p>
                                    Accédez à l’agenda en ligne pour choisir le créneau qui vous convient.
                                </p>
                                <a
                                    class="btn btn-primary"
                                    href="<?= appointment_safe_string($calendarUrl) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Ouvrir l’agenda
                                </a>
                            </section>
                        <?php else: ?>
                            <section class="card appointment-form" id="form-rendez-vous">
                                <h2>Demande de rendez-vous</h2>
                                <p>
                                    Laissez vos coordonnées et quelques informations sur votre besoin.
                                    Nous revenons vers vous rapidement pour confirmer le bon créneau.
                                </p>

                                <form
                                    method="<?= appointment_safe_string($formMethod) ?>"
                                    action="<?= appointment_safe_string($formAction) ?>"
                                    class="appointment-form__form"
                                >
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="appointment-name">Nom complet</label>
                                            <input id="appointment-name" name="name" type="text" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="appointment-email">Email</label>
                                            <input id="appointment-email" name="email" type="email" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="appointment-phone">Téléphone</label>
                                            <input id="appointment-phone" name="phone" type="tel">
                                        </div>

                                        <div class="form-group">
                                            <label for="appointment-subject">Objet</label>
                                            <select id="appointment-subject" name="subject">
                                                <option value="vente">Projet de vente</option>
                                                <option value="achat">Projet d’achat</option>
                                                <option value="estimation">Estimation immobilière</option>
                                                <option value="investissement">Investissement</option>
                                                <option value="autre">Autre demande</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="appointment-message">Votre message</label>
                                        <textarea id="appointment-message" name="message" rows="6" placeholder="Expliquez brièvement votre besoin..."></textarea>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            Envoyer ma demande
                                        </button>
                                    </div>
                                </form>
                            </section>
                        <?php endif; ?>
                    </div>

                    <aside class="appointment-content__sidebar">
                        <section class="card appointment-sidebar__card">
                            <h2>Comment ça se passe ?</h2>
                            <ol class="appointment-steps">
                                <li>Vous choisissez un créneau ou vous envoyez votre demande.</li>
                                <li>Nous validons le rendez-vous avec vous.</li>
                                <li>Nous faisons un point utile, concret et orienté solution.</li>
                            </ol>
                        </section>

                        <section class="card appointment-sidebar__card">
                            <h2>Bon à savoir</h2>
                            <ul class="appointment-notes">
                                <li>Premier échange sans pression.</li>
                                <li>Réponse adaptée à votre situation locale.</li>
                                <li>Possibilité d’orientation vers estimation, vente ou stratégie.</li>
                            </ul>
                        </section>
                    </aside>
                </div>
            </div>
        </section>

        <section class="appointment-cta">
            <div class="container">
                <div class="card appointment-cta__card">
                    <div>
                        <p class="appointment-cta__kicker">Vous préférez un contact direct ?</p>
                        <h2>Vous pouvez aussi nous écrire avant de réserver</h2>
                        <p>
                            Si vous avez une question simple ou si vous hésitez sur le bon type de rendez-vous,
                            passez d’abord par la page contact.
                        </p>
                    </div>
                    <div class="appointment-cta__actions">
                        <a class="btn btn-primary" href="/contact">Accéder au formulaire contact</a>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>