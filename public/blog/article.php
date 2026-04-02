<?php
// Récupérer le slug depuis les données passées par le routeur
$slug      = $slug ?? '';
$pageTitle = 'Article — Eduardo Desul Immobilier';
$metaDesc  = 'Article du blog immobilier par Eduardo Desul.';
$extraCss  = ['/assets/css/guide.css'];
$extraJs   = ['/assets/js/guide.js'];

// Données de démonstration
$article = [
    'titre'     => 'Comment bien préparer la vente de votre bien',
    'cat'       => 'Vente',
    'date'      => '28 mars 2026',
    'lecture'   => '5 min',
    'auteur'    => 'Eduardo Desul',
    'img'       => '/assets/images/blog-1.jpg',
    'contenu'   => null, // Remplacer par contenu BD
];
$pageTitle = e($article['titre']) . ' — Eduardo Desul';
$metaDesc  = 'Conseils d\'Eduardo Desul : ' . mb_strimwidth($article['titre'], 0, 100);
?>

<div class="page-header" style="padding-bottom:2rem">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a>
            <a href="/blog">Blog</a>
            <span><?= e($article['cat']) ?></span>
        </nav>
    </div>
</div>

<?php
$jsonLdArticle = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $article['titre'],
    'author' => ['@type' => 'Person', 'name' => $article['auteur']],
    'datePublished' => $article['date'],
    'publisher' => ['@type' => 'Organization', 'name' => APP_NAME, 'url' => APP_URL],
    'image' => APP_URL . $article['img'],
    'url' => APP_URL . '/blog/' . $slug,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<script type="application/ld+json"><?= $jsonLdArticle ?></script>

<section class="section" style="padding-top:2rem">
    <div class="container">
        <!-- Progress bar -->
        <div id="reading-progress" style="position:fixed;top:var(--header-h);left:0;height:3px;background:var(--clr-accent);z-index:99;transition:width .1s;width:0"></div>

        <div class="article-layout">
            <!-- Contenu principal -->
            <div>
                <div class="article-header">
                    <span class="article-card__cat"><?= e($article['cat']) ?></span>
                    <h1><?= e($article['titre']) ?></h1>
                    <div class="article-meta">
                        <span>✍️ <?= e($article['auteur']) ?></span>
                        <span>📅 <?= e($article['date']) ?></span>
                        <span>⏱ <?= e($article['lecture']) ?> de lecture</span>
                    </div>
                </div>

                <div class="article-cover">
                    <img src="<?= e($article['img']) ?>" alt="<?= e($article['titre']) ?>" width="800" height="450">
                </div>

                <div class="article-content">
                    <?php if ($article['contenu']): ?>
                        <?= $article['contenu'] ?>
                    <?php else: ?>
                    <p>La préparation d'une vente immobilière est une étape cruciale qui conditionne largement le succès de la transaction. Voici les 5 points essentiels à prendre en compte.</p>
                    <h2>1. Faire estimer votre bien au prix juste</h2>
                    <p>Le prix est la première chose que les acheteurs regardent. Un bien surestimé reste sur le marché et finit par se vendre moins cher qu'il n'aurait pu. Une estimation professionnelle vous permet de partir sur de bonnes bases.</p>
                    <blockquote>Un bien au bon prix se vend 3 fois plus vite et 8% plus cher qu'un bien surestimé.</blockquote>
                    <h2>2. Réaliser les diagnostics obligatoires</h2>
                    <p>DPE, amiante, plomb, électricité, gaz… Ces diagnostics sont obligatoires et doivent être réalisés avant la mise en vente. Ils rassurent les acheteurs et évitent les mauvaises surprises.</p>
                    <h2>3. Valoriser votre bien (home staging)</h2>
                    <p>Un intérieur dépersonnalisé et mis en valeur se vend plus vite. Quelques heures de préparation peuvent faire une vraie différence : nettoyage en profondeur, désencombrement, petites réparations.</p>
                    <h2>4. Des photos de qualité professionnelle</h2>
                    <p>95% des acheteurs commencent leur recherche sur internet. Des photos professionnelles génèrent 3 fois plus de contacts qu'un simple smartphone.</p>
                    <h2>5. Choisir le bon moment pour vendre</h2>
                    <p>Le marché bordelais a ses saisonnalités. Printemps et automne sont généralement les meilleures périodes. Votre conseiller local saura vous guider selon les conditions du moment.</p>
                    <?php endif; ?>
                </div>

                <!-- Partage -->
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding:1.5rem 0;border-top:1px solid var(--clr-border);margin-top:2rem">
                    <span style="font-weight:600;font-size:.875rem">Partager cet article :</span>
                    <button data-share="facebook" class="btn btn--outline btn--sm">Facebook</button>
                    <button data-share="linkedin" class="btn btn--outline btn--sm">LinkedIn</button>
                    <button data-share="copy"     class="btn btn--outline btn--sm">Copier le lien</button>
                </div>

                <!-- CTA article -->
                <div style="background:linear-gradient(135deg,var(--clr-primary),#0f2644);color:white;border-radius:var(--radius-lg);padding:2rem;text-align:center;margin-top:2rem">
                    <h3 style="color:white;margin-bottom:.75rem">Prêt à vendre votre bien ?</h3>
                    <p style="opacity:.8;margin-bottom:1.5rem">Obtenez une estimation gratuite de votre bien par Eduardo Desul en moins de 48h.</p>
                    <a href="/estimation-gratuite" class="btn btn--accent">Estimation gratuite →</a>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <div class="sidebar-box">
                    <div class="sidebar-box__head">Table des matières</div>
                    <div class="sidebar-box__body">
                        <ul id="toc-list" style="list-style:none;display:flex;flex-direction:column;gap:.5rem;font-size:.875rem"></ul>
                    </div>
                </div>

                <div class="sidebar-box">
                    <div class="sidebar-box__head">Articles récents</div>
                    <div class="sidebar-box__body">
                        <?php foreach ([
                            ['Investir à Bordeaux en 2026', '15 mars 2026', '/assets/images/blog-2.jpg', '/blog/investir-bordeaux-2026'],
                            ['Taux immobiliers 2026', '5 mars 2026', '/assets/images/blog-3.jpg', '/blog/taux-immobiliers-2026'],
                        ] as [$titre, $date, $img, $href]): ?>
                        <a href="<?= $href ?>" class="recent-post" style="text-decoration:none">
                            <div class="recent-post__img"><img src="<?= e($img) ?>" alt="<?= e($titre) ?>" width="64" height="48"></div>
                            <div>
                                <div class="recent-post__title"><?= e($titre) ?></div>
                                <div class="recent-post__date"><?= e($date) ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem;text-align:center">
                    <h4 style="color:white;margin-bottom:.75rem">Estimation gratuite</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Découvrez la valeur de votre bien en 48h.</p>
                    <a href="/estimation-gratuite" class="btn btn--accent btn--sm btn--full">Estimer mon bien</a>
                </div>
            </aside>
        </div>
    </div>
</section>
