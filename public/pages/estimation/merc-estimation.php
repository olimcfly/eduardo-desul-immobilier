<?php
require_once __DIR__ . '/../../core/bootstrap.php';

$pageTitle = 'Merci — Votre demande a bien été reçue';
$metaDesc  = 'Votre demande d\'estimation a été transmise. ADVISOR_NAME vous recontactera sous 24h.';
$extraCss  = ['/assets/css/merci.css'];

// Articles blog récents pour ressources
$articlesStmt = $db->query("
    SELECT titre, slug, image, categorie, extrait
    FROM   articles
    WHERE  active = 1
    ORDER  BY created_at DESC
    LIMIT  4
");
$articles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="merci-page">

    <!-- ══ HERO MERCI ═══════════════════════════════════════════════════════ -->
    <section class="merci-hero">
        <div class="container">
            <div class="merci-hero__inner">
                <div class="merci-checkmark" aria-hidden="true">
                    <svg viewBox="0 0 52 52" class="checkmark-svg">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path   class="checkmark-check"  fill="none" d="M14 27 l7 7 l17-17"/>
                    </svg>
                </div>
                <h1>Demande bien reçue !</h1>
                <p class="merci-lead">
                    ADVISOR_NAME vous recontactera <strong>dans les 24 heures</strong>
                    pour confirmer votre rendez-vous et préparer votre estimation personnalisée.
                </p>
                <div class="merci-next-steps">
                    <div class="merci-step">
                        <span class="merci-step__num">1</span>
                        <span>Appel de confirmation sous 24h</span>
                    </div>
                    <div class="merci-step__arrow" aria-hidden="true">→</div>
                    <div class="merci-step">
                        <span class="merci-step__num">2</span>
                        <span>Visite et analyse de votre bien</span>
                    </div>
                    <div class="merci-step__arrow" aria-hidden="true">→</div>
                    <div class="merci-step">
                        <span class="merci-step__num">3</span>
                        <span>Rapport d'estimation personnalisé</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ RESSOURCES BLOG ══════════════════════════════════════════════════ -->
    <?php if (!empty($articles)): ?>
    <section class="section merci-ressources">
        <div class="container">
            <h2>En attendant, explorez nos ressources</h2>
            <p class="section-lead">
                Préparez votre projet avec nos guides et articles gratuits.
            </p>
            <div class="articles-grid articles-grid--4">
                <?php foreach ($articles as $art): ?>
                <a href="/blog/<?= e($art['slug']) ?>" class="article-card">
                    <?php if ($art['image']): ?>
                    <div class="article-card__img">
                        <img src="<?= e($art['image']) ?>"
                             alt="<?= e($art['titre']) ?>"
                             loading="lazy">
                    </div>
                    <?php endif; ?>
                    <div class="article-card__body">
                        <?php if ($art['categorie']): ?>
                        <span class="article-card__cat"><?= e($art['categorie']) ?></span>
                        <?php endif; ?>
                        <h3><?= e($art['titre']) ?></h3>
                        <?php if ($art['extrait']): ?>
                        <p><?= e($art['extrait']) ?></p>
                        <?php endif; ?>
                        <span class="article-card__cta">Lire →</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══ GUIDES ═══════════════════════════════════════════════════════════ -->
    <section class="section merci-guides">
        <div class="container">
            <h2>Nos guides complets gratuits</h2>
            <div class="guides-cta-grid">
                <a href="/guide-vendeur" class="guide-cta-card guide-cta-card--vendeur">
                    <span class="guide-cta-card__icon" aria-hidden="true">🏷️</span>
                    <div>
                        <h3>Guide Vendeur</h3>
                        <p>Toutes les étapes pour vendre au meilleur prix.</p>
                    </div>
                    <span class="guide-cta-card__arrow">→</span>
                </a>
                <a href="/guide-acheteur" class="guide-cta-card guide-cta-card--acheteur">
                    <span class="guide-cta-card__icon" aria-hidden="true">🔑</span>
                    <div>
                        <h3>Guide Acheteur</h3>
                        <p>De la recherche à la signature, pas à pas.</p>
                    </div>
                    <span class="guide-cta-card__arrow">→</span>
                </a>
            </div>
        </div>
    </section>

</div>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../templates/layout.php';
?>
