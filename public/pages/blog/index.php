<?php
require_once ROOT_PATH . '/core/helpers/articles.php';

$pageTitle = 'Blog immobilier — Conseils & Actualités | ' . ADVISOR_NAME;
$metaDesc  = 'Conseils, guides et actualités du marché immobilier à Bordeaux et dans le Bordeaux Métropole.';
$extraCss  = ['/assets/css/guide.css'];

try {
    $articles = get_articles_list(12);
} catch (\Throwable $e) {
    $articles = [];
}
?>

<section class="hero" aria-labelledby="blog-hero-title">
    <div class="container">
        <div class="hero__content" style="max-width:700px">
            <span class="section-label" style="color:rgba(255,255,255,0.75)">
                Blog immobilier
            </span>
            <h1 id="blog-hero-title" style="color:#ffffff">
                Conseils & Actualités immobilières
            </h1>
            <p class="hero__subtitle" style="color:rgba(255,255,255,0.85)">
                Guides pratiques, analyses de marché et conseils d'expert
                pour réussir votre projet immobilier sur Bordeaux Métropole.
            </p>
        </div>
    </div>
</section>


<section class="section">
    <div class="container">

        <?php if (empty($articles)): ?>
            <!-- État vide -->
            <div style="text-align:center;padding:4rem 1rem;max-width:560px;margin:0 auto">
                <div style="font-size:4rem;margin-bottom:1.5rem">📝</div>
                <h2 style="margin-bottom:1rem">Des articles arrivent bientôt</h2>
                <p style="color:var(--clr-text-muted);margin-bottom:2rem">
                    Notre équipe prépare des guides et analyses du marché
                    immobilier bordelais. En attendant, consultez nos ressources ci-dessous.
                </p>
                <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
                    <a href="<?= url('/guides/guide-vendeur') ?>"
                       class="btn btn--primary">Guide du vendeur</a>
                    <a href="<?= url('/guides/guide-acheteur') ?>"
                       class="btn btn--outline">Guide de l'acheteur</a>
                </div>
            </div>

            <!-- Ressources alternatives -->
            <div style="margin-top:4rem;padding-top:3rem;border-top:1px solid var(--clr-border)">
                <div class="section__header text-center">
                    <span class="section-label">Nos ressources</span>
                    <h2 class="section-title">Guides pratiques disponibles</h2>
                </div>
                <div class="grid-3" style="margin-top:2rem">

                    <a href="<?= url('/guides/guide-vendeur') ?>"
                       class="card" style="text-decoration:none;display:block">
                        <div style="padding:1.75rem">
                            <div style="font-size:2.5rem;margin-bottom:1rem">🏠</div>
                            <h3 class="card__title">Guide du vendeur</h3>
                            <p class="card__text">
                                Tout ce qu'il faut savoir pour vendre votre bien
                                au meilleur prix sur Bordeaux Métropole.
                            </p>
                            <span style="color:var(--clr-primary);font-weight:600;
                                         font-size:.875rem;margin-top:1rem;
                                         display:inline-block">
                                Lire le guide →
                            </span>
                        </div>
                    </a>

                    <a href="<?= url('/guides/guide-acheteur') ?>"
                       class="card" style="text-decoration:none;display:block">
                        <div style="padding:1.75rem">
                            <div style="font-size:2.5rem;margin-bottom:1rem">🔑</div>
                            <h3 class="card__title">Guide de l'acheteur</h3>
                            <p class="card__text">
                                Les étapes clés pour acheter sereinement et
                                sécuriser votre transaction immobilière.
                            </p>
                            <span style="color:var(--clr-primary);font-weight:600;
                                         font-size:.875rem;margin-top:1rem;
                                         display:inline-block">
                                Lire le guide →
                            </span>
                        </div>
                    </a>

                    <a href="<?= url('/financement') ?>"
                       class="card" style="text-decoration:none;display:block">
                        <div style="padding:1.75rem">
                            <div style="font-size:2.5rem;margin-bottom:1rem">💰</div>
                            <h3 class="card__title">Guide du financement</h3>
                            <p class="card__text">
                                Simulateur de prêt, conseils sur les taux et
                                les conditions d'emprunt en 2024.
                            </p>
                            <span style="color:var(--clr-primary);font-weight:600;
                                         font-size:.875rem;margin-top:1rem;
                                         display:inline-block">
                                Voir le financement →
                            </span>
                        </div>
                    </a>

                </div>
            </div>

        <?php else: ?>
            <!-- ✅ Grille d'articles SANS images -->
            <div class="grid-3">
                <?php foreach ($articles as $article):
                    $dateLabel = !empty($article['date'])
                        ? date('d/m/Y', strtotime($article['date']))
                        : '';
                    $articleUrl = url('/blog/' . rawurlencode((string) $article['slug']));
                ?>
                <article class="card"
                         style="display:flex;flex-direction:column;overflow:hidden">

                    <div style="padding:1.5rem;flex:1;display:flex;flex-direction:column">

                        <?php if ($dateLabel): ?>
                        <div style="font-size:.75rem;
                                    color:var(--clr-text-muted);
                                    margin-bottom:.5rem">
                            <?= e($dateLabel) ?>
                        </div>
                        <?php endif; ?>

                        <h2 class="card__title"
                            style="font-size:1.05rem;margin-bottom:.65rem">
                            <a href="<?= $articleUrl ?>"
                               style="color:inherit;text-decoration:none">
                                <?= e($article['title']) ?>
                            </a>
                        </h2>

                        <p class="card__text"
                           style="flex:1;margin-bottom:1.25rem;font-size:.875rem">
                            <?= e($article['excerpt']) ?>
                        </p>

                        <a href="<?= $articleUrl ?>"
                           class="btn btn--outline btn--sm"
                           style="align-self:flex-start">
                            Lire l'article →
                        </a>

                    </div>
                </article>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>

<!-- CTA -->
<section class="cta-banner">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">
                Une question sur le marché immobilier bordelais ?
            </h2>
            <p class="cta-banner__text">
                Contactez Pascal directement pour un conseil personnalisé et gratuit.
            </p>
            <div class="cta-banner__actions">
                <a href="<?= url('/contact') ?>"
                   class="btn btn--accent">Poser ma question</a>
                <a href="<?= url('/estimation-gratuite') ?>"
                   class="btn btn--outline-white">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>
