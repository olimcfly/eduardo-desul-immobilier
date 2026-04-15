<?php
$pageTitle = 'Biens vendus — Aix-en-Provence & Pays d\'Aix';
$pageDescription = 'Découvrez les biens récemment vendus à Aix-en-Provence et dans le Pays d\'Aix.';
$extraCss = ['/assets/css/home.css'];

require_once __DIR__ . '/../../../core/Database.php';

$pdo = Database::getInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT * FROM biens WHERE statut = 'vendu' ORDER BY created_at DESC");
$biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HERO -->
<section class="hero hero--premium">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92), rgba(15,38,68,.86)), url('/assets/images/hero-bg.jpg');"></div>
    <div class="container">
        <div class="hero__content">
            <span class="section-label">Références</span>
            <h1>Biens récemment vendus dans le Pays d'Aix</h1>
            <p class="hero__subtitle">
                Découvrez des exemples concrets de ventes réalisées avec succès.
            </p>
        </div>
    </div>
</section>

<!-- LISTE -->
<section class="section">
    <div class="container">

        <div class="section__header">
            <span class="section-label">Transactions réalisées</span>
            <h2 class="section-title">Des ventes menées avec méthode et précision</h2>
        </div>

        <div class="grid-3">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>
                    <article class="card property-card-premium property-card--sold">

                        <div class="card__img-wrapper">
                            <?php
                                $ph_vendu = '/assets/images/placeholder.php?type=bien&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                                $src_vendu = (!empty($bien['photo_principale']) && strpos($bien['photo_principale'], 'default.jpg') === false)
                                    ? e($bien['photo_principale'])
                                    : ((!empty($bien['image'])) ? '/uploads/' . e($bien['image']) : $ph_vendu);
                            ?>
                            <img
                                class="card__img"
                                src="<?= $src_vendu ?>"
                                alt="<?= e($bien['titre'] ?? 'Bien immobilier') ?>"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='<?= $ph_vendu ?>'"
                            >
                            <span class="property-badge property-badge--sold">Vendu</span>
                        </div>

                        <div class="card__body">

                            <h3 class="card__title"><?= e($bien['titre'] ?? '') ?></h3>

                            <p class="card__text property-meta">
                                <?= e($bien['ville'] ?? '—') ?> ·
                                <?= e($bien['surface'] ?? '—') ?> m² ·
                                <?= e($bien['pieces'] ?? '—') ?> pièces
                            </p>

                            <p class="property-price property-price--sold">
                                Vendu
                            </p>

                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun bien vendu à afficher pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- CTA -->
<section class="cta-banner">
    <div class="container">
        <h2>Vous souhaitez vendre votre bien à Aix-en-Provence ?</h2>
        <p>Bénéficiez d'une stratégie efficace basée sur des résultats concrets.</p>
        <div class="cta-banner__actions">
            <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Obtenir une estimation</a>
            <a href="/contact" class="btn btn--outline-white btn--lg">Parler de votre projet</a>
        </div>
    </div>
</section>
