<?php
$pageTitle = 'Ressources immobilières — ' . ADVISOR_NAME . '';
$metaDesc  = 'Guides gratuits pour acheter ou vendre votre bien immobilier : guide vendeur, guide acheteur, check-lists, simulateurs.';
$extraCss  = ['/assets/css/guide.css'];
$guidesByPersona = require __DIR__ . '/guides-data.php';
$guidesCount = 0;
foreach ($guidesByPersona as $catalog) {
    $guidesCount += isset($catalog['guides']) && is_array($catalog['guides']) ? count($catalog['guides']) : 0;
}
?>

<section class="blog-hero">
    <div class="container blog-hero__grid">
        <div>
            <nav class="breadcrumb"><a href="/">Accueil</a><span>Ressources</span></nav>
            <span class="section-label">Guides & outils pratiques</span>
            <h1>Ressources gratuites</h1>
            <p>Guides pratiques, check-lists et conseils pour réussir votre projet immobilier.</p>
            <div class="blog-hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent">Estimer mon bien</a>
                <a href="/contact" class="btn btn--outline">Poser une question</a>
            </div>
        </div>
        <div class="blog-hero__card" aria-hidden="true">
            <div class="blog-hero__metric"><strong><?= $guidesCount ?>+</strong><span>guides disponibles</span></div>
            <div class="blog-hero__metric"><strong>100%</strong><span>accès gratuit</span></div>
            <div class="blog-hero__metric"><strong>Actionnable</strong><span>conseils terrain</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Ressources par persona</span>
            <h2 class="section-title">Choisissez votre profil</h2>
            <p>Chaque guide dispose d'une page dédiée avec formulaire de contact intégré.</p>
        </div>

        <?php foreach ($guidesByPersona as $persona => $catalog): ?>
        <div class="persona-guides" data-animate>
            <div class="persona-guides__head">
                <div class="ressource-card__icon"><?= e($catalog['icon']) ?></div>
                <div>
                    <h3><?= e($catalog['label']) ?></h3>
                    <p><?= e($catalog['description']) ?></p>
                </div>
            </div>

            <div class="ressources-grid">
                <?php foreach ($catalog['guides'] as $guide): ?>
                <a href="/ressources/guides/<?= e($persona) ?>/<?= e($guide['slug']) ?>" class="ressource-card" style="text-decoration:none;color:inherit">
                    <h3 class="ressource-card__title"><?= e($guide['title']) ?></h3>
                    <p class="ressource-card__desc"><?= e($guide['excerpt']) ?></p>
                    <span class="btn btn--primary btn--sm">Voir le guide →</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="ressources-grid" data-animate style="margin-top:2rem">
            <a href="/estimation-gratuite" class="ressource-card" style="text-decoration:none;color:inherit">
                <div class="ressource-card__icon">📊</div>
                <h3 class="ressource-card__title">Estimation gratuite</h3>
                <p class="ressource-card__desc">Découvrez la valeur réelle de votre bien grâce à une évaluation personnalisée par Pascal.</p>
                <span class="btn btn--accent btn--sm">Estimer maintenant →</span>
            </a>
            <a href="/guide-offert" class="ressource-card" style="text-decoration:none;color:inherit">
                <div class="ressource-card__icon">📧</div>
                <h3 class="ressource-card__title">Newsletter & alertes</h3>
                <p class="ressource-card__desc">Recevez les nouvelles annonces et les actualités du marché aixois directement dans votre boîte mail.</p>
                <span class="btn btn--outline btn--sm">S'inscrire →</span>
            </a>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center">
            <h2 class="section-title">Questions fréquentes</h2>
        </div>
        <div style="max-width:750px;margin:0 auto" data-animate>
            <?php
            $faqs = [
                ['Combien coûte une estimation immobilière ?', 'L\'estimation de votre bien par ' . ADVISOR_NAME . ' est entièrement gratuite et sans engagement. Vous recevrez un rapport détaillé sous 48h.'],
                ['Combien de temps prend une vente immobilière à Aix-en-Provence ?', 'En moyenne, une vente se conclut en 60 à 90 jours à Aix-en-Provence. Avec un bien bien préparé et au bon prix, certaines ventes se font en moins de 4 semaines.'],
                ['Quels sont les honoraires d\'un conseiller immobilier ?', 'Mes honoraires de vente représentent entre 4% et 6% du prix de vente TTC. Ils ne sont dus qu\'en cas de vente réussie. L\'estimation est toujours gratuite.'],
                ['Puis-je acheter et vendre en même temps ?', 'Oui, c\'est ce qu\'on appelle une "vente-achat" ou achat en chaîne. Pascal vous accompagne pour coordonner les deux transactions et éviter le prêt-relais si possible.'],
            ];
            foreach ($faqs as $i => $faq): ?>
            <details style="background:var(--clr-white);border:1px solid var(--clr-border);border-radius:var(--radius-lg);margin-bottom:.75rem;overflow:hidden">
                <summary style="padding:1.25rem 1.5rem;cursor:pointer;font-weight:600;list-style:none;display:flex;justify-content:space-between;align-items:center">
                    <?= e($faq[0]) ?>
                    <span style="font-size:1.25rem;color:var(--clr-text-muted);flex-shrink:0;margin-left:1rem">▾</span>
                </summary>
                <div style="padding:0 1.5rem 1.25rem;color:var(--clr-text-muted);font-size:.95rem;line-height:1.7">
                    <?= e($faq[1]) ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
