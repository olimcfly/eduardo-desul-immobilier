<?php
$pageTitle = 'Appartements à vendre — Bordeaux & Bordeaux Métropole';
$pageDescription = 'Découvrez notre sélection d’appartements à vendre à Bordeaux et dans la Métropole bordelaise.';
$extraCss = ['/assets/css/home.css'];

require_once __DIR__ . '/../../../core/Database.php';

$pdo = Database::getInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ⚠️ IMPORTANT : adapte selon ta base
// Si tu n'as pas "type", enlève le WHERE
$stmt = $pdo->query("SELECT * FROM biens ORDER BY created_at DESC");
$biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero hero--premium">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(10,25,47,.92), rgba(15,38,68,.90)), url('/assets/images/hero-appartements.jpg');"></div>
    <div class="container">
        <div class="hero__content">
            <span class="section-label">Appartements</span>
            <h1>Appartements à vendre dans Bordeaux Métropole</h1>
            <p class="hero__subtitle">
                Du studio au grand appartement familial, découvrez une sélection de biens adaptés à tous les projets de vie et d’investissement.
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <div class="section__header">
            <span class="section-label">Notre sélection</span>
            <h2 class="section-title">Des appartements pour tous vos projets</h2>
        </div>

        <div class="grid-3">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>
                    <article class="card property-card-premium">

                        <?php
                            $ph_appt = '/assets/images/placeholder.php?type=appartement&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                            $src_appt = (!empty($bien['photo_principale']) && strpos($bien['photo_principale'], 'default.jpg') === false)
                                ? e($bien['photo_principale'])
                                : ((!empty($bien['image'])) ? '/uploads/' . e($bien['image']) : $ph_appt);
                        ?>
                        <img
                            class="card__img"
                            src="<?= $src_appt ?>"
                            alt="<?= e($bien['titre'] ?? 'Appartement') ?>"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='<?= $ph_appt ?>'"
                        >

                        <div class="card__body">

                            <span class="property-badge">
                                <?= e($bien['badge'] ?? 'Appartement') ?>
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
                <p>Aucun appartement disponible actuellement.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Un projet d’achat d’appartement ?</h2>
        <p>Nous vous accompagnons pour trouver le bien idéal, en résidence principale ou en investissement.</p>
        <div class="cta-banner__actions">
            <a href="/contact" class="btn btn--accent btn--lg">Parler de votre projet</a>
            <a href="/estimation-gratuite" class="btn btn--outline-white btn--lg">Estimer un bien</a>
        </div>
    </div>
</section>
