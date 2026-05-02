<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/header-bootstrap.php';
?>
    <header class="site-header" id="site-header">
        <div class="container header__inner">

            <a href="<?= htmlspecialchars(url('/')) ?>" class="header__logo" aria-label="<?= htmlspecialchars($advisorName) ?> — Accueil">
                <span class="logo__text">
                    <strong><?= htmlspecialchars($advisorName) ?></strong>
                    <em>Immobilier</em>
                </span>
            </a>

            <?php require dirname(__DIR__) . '/nav.php'; ?>

            <div class="header__actions">
                <a href="<?= htmlspecialchars(url('/avis-de-valeur')) ?>" class="btn btn--outline btn--header-cta">Avis de valeur</a>
                <a href="<?= htmlspecialchars(url('/prendre-rendez-vous')) ?>" class="btn btn--primary btn--header-cta">Prendre RDV</a>
            </div>

            <button class="burger" id="burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="nav-mobile">
                <span></span><span></span><span></span>
            </button>

        </div>
    </header>
