<?php

declare(strict_types=1);

/**
 * Template page 404
 *
 * Variables possibles :
 * - $page
 * - $sections
 */

function error404_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$pageTitle = error404_safe_string($page['title'] ?? 'Page introuvable');
$pageDescription = error404_safe_string(
    $page['meta_description']
    ?? 'La page que vous recherchez n’existe pas ou a été déplacée.'
);
?>

<main class="page page-404">
    <section class="error-404">
        <div class="container">

            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="Fil d’Ariane">
                <ol class="breadcrumb__list">
                    <li class="breadcrumb__item">
                        <a href="/">Accueil</a>
                    </li>
                    <li class="breadcrumb__item" aria-current="page">
                        404
                    </li>
                </ol>
            </nav>

            <!-- Contenu principal -->
            <div class="error-404__content card">

                <div class="error-404__code">
                    404
                </div>

                <h1 class="error-404__title">
                    <?= $pageTitle ?>
                </h1>

                <p class="error-404__description">
                    <?= $pageDescription ?>
                </p>

                <div class="error-404__actions">
                    <a href="/" class="btn btn-primary">
                        Retour à l’accueil
                    </a>

                    <a href="/contact" class="btn btn-secondary">
                        Nous contacter
                    </a>
                </div>

            </div>

            <!-- Aide utilisateur -->
            <div class="error-404__help">

                <div class="card error-404__help-card">
                    <h2>Vous cherchiez quelque chose de précis ?</h2>
                    <p>
                        Accédez rapidement aux sections principales du site.
                    </p>

                    <div class="error-404__links">
                        <a href="/services">Nos services</a>
                        <a href="/estimation">Estimation immobilière</a>
                        <a href="/blog">Articles & conseils</a>
                        <a href="/guide-local">Guide local</a>
                    </div>
                </div>

            </div>

        </div>
    </section>
</main>