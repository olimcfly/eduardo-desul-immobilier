<?php
$pageTitle = 'Biens à vendre — Bordeaux & Bordeaux Métropole';
$pageDescription = 'Découvrez les biens immobiliers à vendre à Bordeaux et dans la Métropole bordelaise.';
$extraCss = ['/assets/css/home.css'];

require_once __DIR__ . '/../../../core/Database.php';

$pdo = Database::getInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT * FROM biens ORDER BY created_at DESC");
$biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero hero--premium">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92), rgba(15,38,68,.86)), url('/assets/images/hero-bg.jpg');"></div>
    <div class="container">
        <div class="hero__content">
            <span class="section-label">Biens immobiliers</span>
            <h1>Maisons et appartements à vendre dans Bordeaux Métropole</h1>
            <p class="hero__subtitle">Des biens sélectionnés avec précision pour répondre à votre projet immobilier.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <div class="section__header">
            <span class="section-label">Biens disponibles</span>
            <h2 class="section-title">Toutes les opportunités actuellement sur le marché</h2>
        </div>

        <div class="grid-3">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>
                    <article class="card property-card-premium">

                        <?php
                            $ph_maison = '/assets/images/placeholder.php?type=maison&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                            $src_maison = (!empty($bien['photo_principale']) && strpos($bien['photo_principale'], 'default.jpg') === false)
                                ? e($bien['photo_principale'])
                                : ((!empty($bien['image'])) ? '/uploads/' . e($bien['image']) : $ph_maison);
                        ?>
                        <img
                            class="card__img"
                            src="<?= $src_maison ?>"
                            alt="<?= e($bien['titre'] ?? 'Bien immobilier') ?>"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='<?= $ph_maison ?>'"
                        >

                        <div class="card__body">

                            <?php if (!empty($bien['badge'])): ?>
                                <span class="property-badge"><?= e($bien['badge']) ?></span>
                            <?php endif; ?>

                            <h3 class="card__title"><?= e($bien['titre'] ?? '') ?></h3>

                            <p class="card__text property-meta">
                                <?= e($bien['surface'] ?? '—') ?> m² • 
                                <?= e($bien['pieces'] ?? '—') ?> pièces
                            </p>

                            <p class="property-price">
                                <?= number_format((float)($bien['prix'] ?? 0), 0, ',', ' ') ?> €
                            </p>

                            <a href="/bien.php?id=<?= $bien['id'] ?>" class="btn btn--primary btn--sm">
                                Voir le bien
                            </a>

                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun bien disponible pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Vous avez un projet immobilier à Bordeaux ?</h2>
        <p>Parlons ensemble de votre achat ou de votre vente.</p>
        <div class="cta-banner__actions">
            <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Estimation gratuite</a>
            <a href="/contact" class="btn btn--outline-white btn--lg">Prendre contact</a>
        </div>
    </div>
</section>
