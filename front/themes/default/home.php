<?php
ob_start();
$advisor = $advisor ?? [];
$advisorId = (int)($advisor['id'] ?? 0);
?>
<section class="services" id="services">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Mes services</span>
            <h2>Tout pour réussir <span>votre projet</span></h2>
            <p>Un accompagnement complet, de l'estimation à la signature</p>
        </div>
        <div class="services__grid">
            <div class="service-card service-card--featured">
                <div class="service-card__icon"><i class="fas fa-home"></i></div>
                <h3>Vendre votre bien</h3>
                <p>Estimation précise, photos professionnelles, diffusion sur +30 portails, suivi des visites et négociation.</p>
                <ul class="service-card__list">
                    <li><i class="fas fa-check"></i> Estimation offerte</li>
                    <li><i class="fas fa-check"></i> Photos HDR incluses</li>
                    <li><i class="fas fa-check"></i> Diffusion maximale</li>
                    <li><i class="fas fa-check"></i> Suivi hebdomadaire</li>
                </ul>
                <a href="/estimation" class="btn btn--primary">Estimer mon bien <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="service-card">
                <div class="service-card__icon"><i class="fas fa-key"></i></div>
                <h3>Acheter un bien</h3>
                <p>Recherche personnalisée, alertes en temps réel, visites organisées et accompagnement jusqu'à la signature.</p>
                <ul class="service-card__list">
                    <li><i class="fas fa-check"></i> Recherche sur mesure</li>
                    <li><i class="fas fa-check"></i> Alertes instantanées</li>
                    <li><i class="fas fa-check"></i> Conseil financement</li>
                    <li><i class="fas fa-check"></i> Aide à la négociation</li>
                </ul>
                <a href="/contact" class="btn btn--outline">Déposer ma recherche <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="service-card">
                <div class="service-card__icon"><i class="fas fa-chart-line"></i></div>
                <h3>Investir</h3>
                <p>Analyse de rentabilité, sélection des meilleures opportunités locatives et gestion de votre patrimoine.</p>
                <ul class="service-card__list">
                    <li><i class="fas fa-check"></i> Analyse rendement</li>
                    <li><i class="fas fa-check"></i> Défiscalisation</li>
                    <li><i class="fas fa-check"></i> Gestion locative</li>
                    <li><i class="fas fa-check"></i> Suivi patrimonial</li>
                </ul>
                <a href="/contact" class="btn btn--outline">Étudier mon projet <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="about" id="about">
    <div class="container"><div class="about__grid">
        <div class="about__image">
            <?php if (!empty($advisor['photo'])): ?>
            <img src="<?= htmlspecialchars($advisor['photo']) ?>" alt="<?= htmlspecialchars($advisor['full_name'] ?? 'Conseiller immobilier') ?>" loading="lazy">
            <?php else: ?><div class="about__image-placeholder"><i class="fas fa-user-tie"></i></div><?php endif; ?>
            <div class="about__badge"><span class="about__badge-number"><?= htmlspecialchars((string)($advisor['experience_years'] ?? '10')) ?></span><span class="about__badge-text">ans d'expérience</span></div>
        </div>
        <div class="about__content">
            <span class="section-tag">À propos</span>
            <h2><?= htmlspecialchars($advisor['full_name'] ?? 'Votre conseiller') ?> <span>à votre service</span></h2>
            <p class="about__intro"><?= nl2br(htmlspecialchars($advisor['bio'] ?? 'Conseiller immobilier indépendant, je mets mon expertise locale au service de vos projets.')) ?></p>
            <a href="/contact" class="btn btn--primary"><i class="fas fa-calendar-alt"></i> Prendre rendez-vous</a>
        </div>
    </div></div>
</section>

<section class="reviews" id="avis">
    <div class="container">
        <div class="section-header section-header--light">
            <span class="section-tag section-tag--white">Avis clients</span>
            <h2>Ils me font <span>confiance</span></h2>
        </div>
        <div class="reviews__slider" id="reviewsSlider">
            <?php
            $reviews = [];
            if (isset($db) && $db instanceof PDO && $advisorId > 0) {
                $stmt = $db->prepare("SELECT r.* FROM google_reviews r WHERE r.advisor_id = :id AND r.status = 'published' ORDER BY r.review_date DESC LIMIT 9");
                $stmt->execute([':id' => $advisorId]);
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
            if (empty($reviews)) {
                $reviews = [
                    ['reviewer_name'=>'Marie D.', 'rating'=>5, 'comment'=>'Accompagnement remarquable du début à la fin.', 'review_date'=>'2024-11-15'],
                    ['reviewer_name'=>'Thomas R.', 'rating'=>5, 'comment'=>'Très professionnel, réactif et de bon conseil.', 'review_date'=>'2024-10-28'],
                    ['reviewer_name'=>'Sophie M.', 'rating'=>5, 'comment'=>'Expertise locale indéniable.', 'review_date'=>'2024-10-10'],
                ];
            }
            foreach ($reviews as $review): ?>
            <div class="review-card">
                <strong><?= htmlspecialchars($review['reviewer_name']) ?></strong>
                <p class="review-card__text">"<?= htmlspecialchars($review['comment']) ?>"</p>
                <span class="review-card__date"><?= date('d/m/Y', strtotime((string)$review['review_date'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-final">
    <div class="container">
        <div class="cta-final__card">
            <div class="cta-final__content">
                <h2>Prêt à concrétiser<br><span>votre projet immobilier ?</span></h2>
                <p>Estimation gratuite, réponse sous 2h. Sans engagement, sans frais cachés.</p>
                <div class="cta-final__actions">
                    <a href="/estimation" class="btn btn--white btn--lg"><i class="fas fa-calculator"></i> Estimer gratuitement</a>
                    <a href="tel:<?= htmlspecialchars($advisor['phone'] ?? '') ?>" class="btn btn--outline btn--lg"><i class="fas fa-phone"></i> <?= htmlspecialchars($advisor['phone'] ?? 'Nous appeler') ?></a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/layout.php';
