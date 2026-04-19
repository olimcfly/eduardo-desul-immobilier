<?php
$pageTitle = 'Biens de prestige — Bordeaux & Bordeaux Métropole';
$pageDescription = 'Découvrez une sélection exclusive de biens immobiliers de prestige à Bordeaux et dans la Métropole bordelaise.';
$extraCss = ['/assets/css/home.css'];

require_once __DIR__ . '/../../../core/Database.php';

$pdo = Database::getInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT * FROM biens WHERE exclusif = 1 AND statut = 'actif' ORDER BY created_at DESC");
$biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero hero--premium">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(10,25,47,.92), rgba(15,38,68,.90)), url('/assets/images/hero-prestige.jpg');"></div>
    <div class="container">
        <div class="hero__content">
            <span class="section-label">Collection prestige</span>
            <h1>Biens d’exception à Bordeaux Métropole</h1>
            <p class="hero__subtitle">
                Villas contemporaines, propriétés de caractère et emplacements rares : une sélection confidentielle de biens haut de gamme.
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <div class="section__header">
            <span class="section-label">Sélection exclusive</span>
            <h2 class="section-title">Des biens rares pour des projets exigeants</h2>
        </div>

        <div class="grid-3">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>
                    <article class="card property-card-premium">

                        <?php
                            $ph_prestige = '/assets/images/placeholder.php?type=bien&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                            $src_prestige = (!empty($bien['photo_principale']) && strpos($bien['photo_principale'], 'default.jpg') === false)
                                ? e($bien['photo_principale'])
                                : ((!empty($bien['image'])) ? '/uploads/' . e($bien['image']) : $ph_prestige);
                        ?>
                        <img
                            class="card__img"
                            src="<?= $src_prestige ?>"
                            alt="<?= e($bien['titre'] ?? 'Bien immobilier de prestige') ?>"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='<?= $ph_prestige ?>'"
                        >

                        <div class="card__body">

                            <span class="property-badge">
                                <?= e($bien['badge'] ?? 'Prestige') ?>
                            </span>

                            <h3 class="card__title"><?= e($bien['titre'] ?? '') ?></h3>

                            <p class="card__text property-meta">
                                <?= e($bien['ville'] ?? '—') ?> · 
                                <?= e($bien['surface'] ?? '—') ?> m² · 
                                <?= e($bien['pieces'] ?? '—') ?> pièces
                            </p>

                            <p class="property-price">
                                <?= number_format((float)($bien['prix'] ?? 0), 0, ',', ' ') ?> €
                            </p>

                            <a href="/bien.php?id=<?= (int)$bien['id'] ?>" class="btn btn--primary btn--sm">
                                Découvrir
                            </a>

                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun bien de prestige disponible actuellement.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Un projet immobilier haut de gamme ?</h2>
        <p>Accédez à des biens confidentiels et bénéficiez d’un accompagnement sur mesure.</p>
        <div class="cta-banner__actions">
            <a href="/contact" class="btn btn--accent btn--lg">Parler de votre projet</a>
            <a href="/estimation-gratuite" class="btn btn--outline-white btn--lg">Estimer un bien</a>
        </div>
    </div>
</section>
