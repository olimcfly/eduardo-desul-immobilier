<?php
$pageTitle = 'Agent immobilier Bordeaux & Expert estimation immobilière — Eduardo De Sul';
$metaDesc  = 'Vous souhaitez vendre votre bien au juste prix ou réaliser un achat immobilier en Gironde ? Eduardo De Sul, agent immobilier à Bordeaux, vous offre une estimation gratuite et un accompagnement sur-mesure.';

$heroLabel = (string) setting('site_home_hero_label', 'Agent immobilier à Bordeaux — Expert en évaluation immobilière');
$heroTitle = (string) setting('site_home_hero_title', 'Vendez au juste prix.<br>Achetez en toute sérénité.');
$heroSubtitle = (string) setting(
    'site_home_hero_subtitle',
    "Vous souhaitez <strong>vendre votre maison ou appartement</strong> au meilleur prix, ou concrétiser un <strong>achat immobilier</strong> à Bordeaux et en Gironde ?\nBénéficiez d'une <strong>estimation immobilière gratuite</strong> et d'un accompagnement personnalisé par Eduardo De Sul, certifié <strong>Expert en évaluation immobilière</strong>."
);
$ctaPrimaryLabel = (string) setting('site_home_cta_primary_label', 'Estimer mon bien gratuitement');
$ctaPrimaryUrl = (string) setting('site_home_cta_primary_url', '/estimation-gratuite');
$ctaSecondaryLabel = (string) setting('site_home_cta_secondary_label', 'Voir les annonces');
$ctaSecondaryUrl = (string) setting('site_home_cta_secondary_url', '/biens');
?>

<!-- ── Hero ────────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero__bg" style="background-image:url('/assets/images/hero-bordeaux.jpg')"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label"><?= e($heroLabel) ?></span>
            <h1><?= strip_tags($heroTitle, '<br>') ?></h1>
            <p class="hero__subtitle">
                <?= strip_tags($heroSubtitle, '<strong><br>') ?>
            </p>
            <div class="hero__actions">
                <a href="<?= e($ctaPrimaryUrl) ?>" class="btn btn--accent btn--lg"><?= e($ctaPrimaryLabel) ?></a>
                <a href="<?= e($ctaSecondaryUrl) ?>" class="btn btn--outline-white btn--lg"><?= e($ctaSecondaryLabel) ?></a>
            </div>

            <div class="hero__trust">
                <div class="trust-item">
                    <span class="value">Vente</span>
                    <span class="label">Au prix du marché</span>
                </div>
                <div class="trust-item">
                    <span class="value">Achat</span>
                    <span class="label">Accompagnement complet</span>
                </div>
                <div class="trust-item">
                    <span class="value">Expert</span>
                    <span class="label">Évaluation certifiée</span>
                </div>
                <div class="trust-item">
                    <span class="value">24h</span>
                    <span class="label">Délai de réponse</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Pourquoi Eduardo De Sul ──────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Pourquoi me choisir ?</span>
            <h2 class="section-title">Une expertise immobilière à votre service</h2>
            <p class="section-subtitle">
                Avec Eduardo De Sul, simplifiez votre <strong>vente immobilière</strong> ou votre <strong>achat</strong> grâce à une connaissance approfondie des <strong>prix du marché bordelais</strong> et une approche humaine, sans commission cachée.
            </p>
        </div>
        <div class="grid-3" data-animate>
            <div class="service-card">
                <div class="service-card__icon">📊</div>
                <h3 class="service-card__title">Estimation immobilière précise</h3>
                <p class="service-card__text">
                    Obtenez la <strong>valeur vénale</strong> réelle de votre bien, calculée sur les <strong>prix au mètre carré</strong> du quartier, les transactions récentes et les spécificités de votre maison ou appartement.
                </p>
                <a href="/estimation-gratuite" class="btn btn--accent btn--sm" style="margin-top:1rem">Estimer maintenant</a>
            </div>
            <div class="service-card">
                <div class="service-card__icon">🏠</div>
                <h3 class="service-card__title">Vente au meilleur prix</h3>
                <p class="service-card__text">
                    Une <strong>mise en vente</strong> optimisée : photos professionnelles, annonce percutante, diffusion multi-plateformes et négociation pour maximiser votre <strong>prix de vente</strong>.
                </p>
                <a href="/services" class="btn btn--outline btn--sm" style="margin-top:1rem">En savoir plus</a>
            </div>
            <div class="service-card">
                <div class="service-card__icon">🔑</div>
                <h3 class="service-card__title">Achat sécurisé en Gironde</h3>
                <p class="service-card__text">
                    Trouvez le <strong>bien immobilier</strong> idéal : recherche ciblée, visites accompagnées, comparatif des <strong>prix au m²</strong> par secteur et négociation avec les vendeurs en votre faveur.
                </p>
                <a href="/services" class="btn btn--outline btn--sm" style="margin-top:1rem">En savoir plus</a>
            </div>
        </div>
    </div>
</section>

<!-- ── Biens en vedette ─────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section__header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem">
            <div data-animate>
                <span class="section-label">Annonces disponibles</span>
                <h2 class="section-title" style="margin-bottom:0">Biens à vendre en Gironde</h2>
            </div>
            <a href="/biens" class="btn btn--outline">Toutes les annonces →</a>
        </div>

        <div class="biens-grid" data-animate>
            <?php
            $biensVedette = [
                [
                    'titre'    => 'Maison Arsac — 5 pièces avec piscine',
                    'typeBien' => 'maison',
                    'type'     => 'Vente',
                    'prix'     => '420 000',
                    'loc'      => 'Arsac (33460)',
                    'surface'  => '136',
                    'pieces'   => '5',
                    'img'      => '/assets/images/bien-1.jpg',
                    'slug'     => 'maison-arsac-5p',
                ],
                [
                    'titre'    => 'Maison Pompignac — 6 pièces terrain 1 500 m²',
                    'typeBien' => 'maison',
                    'type'     => 'Vente',
                    'prix'     => '400 000',
                    'loc'      => 'Pompignac (33370)',
                    'surface'  => '156',
                    'pieces'   => '6',
                    'img'      => '/assets/images/bien-2.jpg',
                    'slug'     => 'maison-pompignac-6p',
                ],
                [
                    'titre'    => 'Maison Galgon — 6 pièces avec piscine',
                    'typeBien' => 'maison',
                    'type'     => 'Vente',
                    'prix'     => '398 000',
                    'loc'      => 'Galgon (33133)',
                    'surface'  => '165',
                    'pieces'   => '6',
                    'img'      => '/assets/images/bien-3.jpg',
                    'slug'     => 'maison-galgon-6p',
                ],
            ];
            foreach ($biensVedette as $b):
                $imgFile = defined('PUBLIC_PATH') ? PUBLIC_PATH . $b['img'] : __DIR__ . '/..' . $b['img'];
                $imgSrc = file_exists($imgFile)
                    ? e($b['img'])
                    : '/assets/images/placeholder.php?type=' . urlencode($b['typeBien'])
                      . '&pieces=' . $b['pieces'] . '&surface=' . $b['surface']
                      . '&label=' . urlencode($b['type']);
            ?>
            <article class="bien-card">
                <div class="bien-card__img">
                    <img src="<?= $imgSrc ?>" alt="<?= e($b['titre']) ?>" loading="lazy" width="400" height="300">
                    <span class="bien-card__badge badge--vente"><?= e($b['type']) ?></span>
                </div>
                <div class="bien-card__body">
                    <div class="bien-card__prix"><?= e($b['prix']) ?> €</div>
                    <h3 class="bien-card__titre"><?= e($b['titre']) ?></h3>
                    <p class="bien-card__loc"><?= e($b['loc']) ?></p>
                    <div class="bien-card__specs">
                        <span class="spec-item">🏠 <?= e($b['surface']) ?> m²</span>
                        <span class="spec-item">🚪 <?= e($b['pieces']) ?> pièces</span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── À propos ─────────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="grid-2" style="align-items:center;gap:4rem">
            <div data-animate>
                <span class="section-label">Votre agent immobilier à Bordeaux</span>
                <h2 class="section-title">Eduardo De Sul,<br>Expert en évaluation immobilière</h2>
                <p>
                    Passionné par l'immobilier et les relations humaines, j'accompagne vendeurs et acquéreurs avec le même engagement. Certifié <strong>Expert en évaluation immobilière</strong>, je détermine la <strong>valeur réelle de votre bien</strong> en croisant les données du marché local, le <strong>prix au mètre carré</strong> du secteur et les spécificités du bien.
                </p>
                <p>
                    Que vous souhaitiez <strong>vendre votre maison</strong>, <strong>acheter un appartement</strong> ou obtenir une <strong>estimation fiable</strong> avant de prendre une décision, je suis à vos côtés à Bordeaux et dans toute la Gironde.
                </p>
                <div style="display:flex;gap:2rem;flex-wrap:wrap;margin:1.5rem 0 2rem">
                    <div>
                        <strong style="font-family:var(--font-display);font-size:1.75rem;color:var(--clr-primary)">100%</strong>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)">Transparent sur les honoraires</div>
                    </div>
                    <div>
                        <strong style="font-family:var(--font-display);font-size:1.75rem;color:var(--clr-primary)">Gironde</strong>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)">Secteur d'intervention</div>
                    </div>
                    <div>
                        <strong style="font-family:var(--font-display);font-size:1.75rem;color:var(--clr-primary)">Expert</strong>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)">Évaluation certifiée</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:2rem">
                    <a href="tel:+33676592367" style="display:inline-flex;align-items:center;gap:.5rem;font-weight:600;color:var(--clr-primary)">
                        📞 +33 6 76 59 23 67
                    </a>
                    <a href="mailto:eduardo.desul@expfrance.fr" style="display:inline-flex;align-items:center;gap:.5rem;color:var(--clr-text-muted);font-size:.9rem">
                        ✉️ eduardo.desul@expfrance.fr
                    </a>
                </div>
                <a href="/a-propos" class="btn btn--primary">En savoir plus sur mon parcours</a>
            </div>
            <div data-animate style="position:relative">
                <div style="background:linear-gradient(135deg,var(--clr-primary),#0f2644);border-radius:var(--radius-xl);aspect-ratio:4/5;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative">
                    <img src="https://nhkxpqunzawllesgatth.supabase.co/storage/v1/object/public/agent-images/1773076139097-2w3xgeid3of.jpg" alt="Eduardo De Sul, agent immobilier Bordeaux Gironde" style="width:100%;height:100%;object-fit:cover">
                </div>
                <div style="position:absolute;bottom:-1rem;right:-1rem;background:var(--clr-accent);color:var(--clr-primary);border-radius:var(--radius-lg);padding:1.25rem;font-weight:700;text-align:center;box-shadow:var(--shadow-lg)">
                    <div style="font-size:1.1rem;font-family:var(--font-display)">Expert</div>
                    <div style="font-size:.75rem">Évaluation immo.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Témoignages ──────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Ils me font confiance</span>
            <h2 class="section-title">Ce que disent mes clients</h2>
        </div>
        <div class="grid-3" data-animate>
            <?php
            $avis = [
                ['nom' => 'Marie & Thomas L.', 'note' => 5, 'text' => 'Grâce à l\'expertise immobilière d\'Eduardo, nous avons trouvé notre appartement idéal en moins d\'un mois, négocié au bon prix. Professionnel et vraiment à l\'écoute.', 'date' => 'Il y a 2 semaines'],
                ['nom' => 'Jean-Pierre M.', 'note' => 5, 'text' => 'Vente de ma maison en 3 semaines au prix de vente demandé. Son estimation était parfaitement alignée avec le marché local. Suivi impeccable du début à la fin.', 'date' => 'Il y a 1 mois'],
                ['nom' => 'Sophie D.', 'note' => 5, 'text' => 'Première acquisition immobilière en Gironde. Eduardo m\'a guidée sur les prix au m² par quartier et m\'a évité plusieurs pièges. La transaction s\'est faite en 4 semaines.', 'date' => 'Il y a 2 mois'],
            ];
            foreach ($avis as $a): ?>
            <div class="testimonial">
                <div class="testimonial__stars"><?= str_repeat('★', $a['note']) ?></div>
                <p class="testimonial__text">"<?= e($a['text']) ?>"</p>
                <div class="testimonial__author"><?= e($a['nom']) ?></div>
                <div class="testimonial__date"><?= e($a['date']) ?> — Google</div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2rem">
            <a href="/avis" class="btn btn--outline">Voir tous les avis →</a>
        </div>
    </div>
</section>

<!-- ── Guide local ──────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Marché immobilier local</span>
            <h2 class="section-title">Prix au m² par quartier à Bordeaux</h2>
            <p class="section-subtitle">
                Avant de <strong>vendre ou acheter</strong>, comparez les <strong>prix immobiliers</strong> par secteur. Mon guide local vous donne les tendances du marché, les délais de vente moyens et les atouts de chaque quartier bordelais.
            </p>
        </div>
        <div class="villes-grid" data-animate>
            <?php
            $villes = [
                ['slug' => 'bordeaux-centre',    'nom' => 'Bordeaux Centre', 'prix' => '4 800 €/m²', 'biens' => 12, 'img' => '/assets/images/bordeaux-centre.jpg'],
                ['slug' => 'bordeaux-chartrons', 'nom' => 'Chartrons',       'prix' => '4 600 €/m²', 'biens' => 8,  'img' => '/assets/images/chartrons.jpg'],
                ['slug' => 'merignac',           'nom' => 'Mérignac',        'prix' => '3 200 €/m²', 'biens' => 6,  'img' => '/assets/images/merignac.jpg'],
                ['slug' => 'pessac',             'nom' => 'Pessac',          'prix' => '2 900 €/m²', 'biens' => 5,  'img' => '/assets/images/pessac.jpg'],
            ];
            foreach ($villes as $v): ?>
            <a href="/guide-local/<?= e($v['slug']) ?>" class="ville-card">
                <img src="<?= e($v['img']) ?>" alt="Prix immobilier <?= e($v['nom']) ?>" loading="lazy" width="400" height="300">
                <div class="ville-card__overlay">
                    <div class="ville-card__name"><?= e($v['nom']) ?></div>
                    <div class="ville-card__price"><?= e($v['prix']) ?></div>
                    <div class="ville-card__count"><?= $v['biens'] ?> biens disponibles</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2rem">
            <a href="/guide-local" class="btn btn--outline">Voir tous les quartiers et leurs prix →</a>
        </div>
    </div>
</section>

<!-- ── FAQ ──────────────────────────────────────────────────── -->
<section class="section">
    <div class="container" style="max-width:860px">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Vos questions</span>
            <h2 class="section-title">Questions fréquentes sur l'immobilier à Bordeaux</h2>
        </div>
        <div data-animate style="display:flex;flex-direction:column;gap:1rem">
            <?php
            $faq = [
                [
                    'q' => 'Comment est calculé le prix au mètre carré d\'un bien ?',
                    'r' => 'Le prix au m² dépend de la localisation, de la surface, de l\'état du bien et du marché local. Mon estimation croise les transactions récentes du secteur, les tendances de prix en Gironde et les spécificités de votre maison ou appartement pour vous donner une valeur vénale fiable.',
                ],
                [
                    'q' => 'Combien coûte une estimation immobilière ?',
                    'r' => 'L\'estimation est entièrement gratuite et sans engagement. En tant qu\'expert en évaluation immobilière, je vous fournis une analyse précise du prix de vente optimal, basée sur les données du marché bordelais et une visite de votre bien.',
                ],
                [
                    'q' => 'Quels sont vos honoraires en tant qu\'agent immobilier ?',
                    'r' => 'Mes honoraires sont transparents et vous sont communiqués dès le premier contact, sans surprise. Ils reflètent l\'accompagnement complet que je vous apporte : de l\'estimation à la signature chez le notaire, en passant par les visites et la négociation.',
                ],
                [
                    'q' => 'Combien de temps faut-il pour vendre un bien à Bordeaux ?',
                    'r' => 'Le délai moyen de vente varie selon le secteur : 35 jours aux Chartrons, 49 jours à Mérignac, 52 jours à Pessac. Un bien correctement estimé au prix du marché se vend significativement plus vite qu\'un bien surévalué.',
                ],
            ];
            foreach ($faq as $i => $item): ?>
            <details style="background:var(--clr-white);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;cursor:pointer" <?= $i === 0 ? 'open' : '' ?>>
                <summary style="font-weight:700;font-size:1rem;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:1rem">
                    <?= e($item['q']) ?>
                    <span style="font-size:1.25rem;flex-shrink:0;color:var(--clr-primary)">+</span>
                </summary>
                <p style="margin-top:1rem;color:var(--clr-text-muted);line-height:1.7;margin-bottom:0"><?= e($item['r']) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Contact rapide ───────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Me contacter</span>
            <h2 class="section-title">Parlons de votre projet immobilier</h2>
            <p class="section-subtitle">Disponible 7j/7 pour répondre à vos questions sur la vente, l'achat ou l'estimation de votre bien à Bordeaux et en Gironde.</p>
        </div>
        <div class="grid-3" data-animate>
            <a href="tel:+33676592367" class="service-card" style="text-decoration:none;text-align:center">
                <div class="service-card__icon">📞</div>
                <h3 class="service-card__title">Appel téléphonique</h3>
                <p class="service-card__text" style="font-weight:600;color:var(--clr-primary);font-size:1.05rem">+33 6 76 59 23 67</p>
                <p class="service-card__text">Du lundi au samedi, 9h–19h</p>
            </a>
            <a href="https://wa.me/33676592367" target="_blank" rel="noopener" class="service-card" style="text-decoration:none;text-align:center">
                <div class="service-card__icon">💬</div>
                <h3 class="service-card__title">WhatsApp</h3>
                <p class="service-card__text" style="font-weight:600;color:var(--clr-primary);font-size:1.05rem">Message rapide</p>
                <p class="service-card__text">Réponse sous quelques heures</p>
            </a>
            <a href="mailto:eduardo.desul@expfrance.fr" class="service-card" style="text-decoration:none;text-align:center">
                <div class="service-card__icon">✉️</div>
                <h3 class="service-card__title">Email</h3>
                <p class="service-card__text" style="font-weight:600;color:var(--clr-primary);font-size:.9rem">eduardo.desul@expfrance.fr</p>
                <p class="service-card__text">Réponse sous 24h</p>
            </a>
        </div>
    </div>
</section>

<!-- ── CTA ──────────────────────────────────────────────────── -->
<section class="cta-banner">
    <div class="container">
        <span class="section-label" style="color:var(--clr-accent)">Prêt à passer à l'action ?</span>
        <h2>Votre estimation immobilière gratuite à Bordeaux</h2>
        <p>Connaître la valeur réelle de votre bien est la première étape d'une vente réussie. Eduardo De Sul vous répond personnellement dans les 24h.</p>
        <div class="cta-banner__actions">
            <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Estimation gratuite</a>
            <a href="tel:+33676592367" class="btn btn--outline-white btn--lg">📞 +33 6 76 59 23 67</a>
        </div>
    </div>
</section>

<!-- ── Blog récent ──────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem">
            <div>
                <span class="section-label">Conseils & marché immobilier</span>
                <h2 class="section-title" style="margin-bottom:0">Derniers articles</h2>
            </div>
            <a href="/blog" class="btn btn--outline">Voir tous les articles →</a>
        </div>
        <div class="blog-grid" data-animate>
            <?php
            $articles = [
                ['cat' => 'Vente', 'titre' => 'Comment préparer la vente de votre bien pour maximiser son prix', 'excerpt' => 'Les 5 étapes clés pour présenter votre maison ou appartement dans les meilleures conditions et vendre au prix du marché.', 'date' => '28 mars 2026', 'img' => '/assets/images/blog-1.jpg', 'slug' => 'preparer-vente-bien'],
                ['cat' => 'Achat', 'titre' => 'Investir à Bordeaux en 2026 : quels quartiers offrent le meilleur rendement ?', 'excerpt' => 'Analyse des prix au m², des délais de vente et des perspectives de plus-value dans la métropole bordelaise.', 'date' => '15 mars 2026', 'img' => '/assets/images/blog-2.jpg', 'slug' => 'investir-bordeaux-2026'],
                ['cat' => 'Financement', 'titre' => 'Taux immobiliers 2026 : ce qui change pour votre acquisition', 'excerpt' => 'Comprendre l\'évolution des taux d\'emprunt pour optimiser votre plan de financement et négocier avec les banques.', 'date' => '5 mars 2026', 'img' => '/assets/images/blog-3.jpg', 'slug' => 'taux-immobiliers-2026'],
            ];
            foreach ($articles as $art): ?>
            <article class="article-card">
                <div class="article-card__img">
                    <img src="<?= e($art['img']) ?>" alt="<?= e($art['titre']) ?>" loading="lazy" width="400" height="225">
                </div>
                <div class="article-card__body">
                    <span class="article-card__cat"><?= e($art['cat']) ?></span>
                    <h3 class="article-card__title"><a href="/blog/<?= e($art['slug']) ?>"><?= e($art['titre']) ?></a></h3>
                    <p class="article-card__excerpt"><?= e($art['excerpt']) ?></p>
                    <div class="article-card__meta">
                        <span>📅 <?= e($art['date']) ?></span>
                        <a href="/blog/<?= e($art['slug']) ?>" style="color:var(--clr-primary);font-weight:600">Lire l'article →</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── JSON-LD RealEstateAgent + FAQ ─────────────────────────── -->
<?php
$jsonLdAgent = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'RealEstateAgent',
    'name' => 'Eduardo De Sul Immobilier',
    'image' => 'https://nhkxpqunzawllesgatth.supabase.co/storage/v1/object/public/agent-images/1773076139097-2w3xgeid3of.jpg',
    'url' => APP_URL,
    'telephone' => '+33676592367',
    'email' => 'eduardo.desul@expfrance.fr',
    'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Bordeaux',
        'postalCode' => '33000',
        'addressCountry' => 'FR',
    ],
    'geo' => ['@type' => 'GeoCoordinates', 'latitude' => 44.8378, 'longitude' => -0.5792],
    'description' => 'Agent immobilier à Bordeaux & Expert en évaluation immobilière. Vente, achat et estimation de maisons et appartements en Gironde.',
    'areaServed' => ['Bordeaux', 'Mérignac', 'Pessac', 'Talence', 'Gironde'],
    'knowsAbout' => ['estimation immobilière', 'vente immobilière', 'achat immobilier', 'évaluation immobilière'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$jsonLdFaq = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Comment est calculé le prix au mètre carré d\'un bien ?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Le prix au m² dépend de la localisation, de la surface, de l\'état du bien et du marché local. L\'estimation croise les transactions récentes du secteur et les spécificités du bien pour donner une valeur vénale fiable.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Combien coûte une estimation immobilière ?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'L\'estimation est entièrement gratuite et sans engagement. En tant qu\'expert en évaluation immobilière, Eduardo De Sul vous fournit une analyse précise basée sur les données du marché bordelais.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Combien de temps faut-il pour vendre un bien à Bordeaux ?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Le délai moyen de vente varie selon le quartier : 35 jours aux Chartrons, 49 jours à Mérignac, 52 jours à Pessac. Un bien correctement estimé au prix du marché se vend significativement plus vite.'],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<script type="application/ld+json"><?= $jsonLdAgent ?></script>
<script type="application/ld+json"><?= $jsonLdFaq ?></script>

<!-- ── Cookie banner ────────────────────────────────────────── -->
<div id="cookie-banner" style="display:none;position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:999;background:var(--clr-white);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.25rem 1.75rem;box-shadow:var(--shadow-lg);max-width:600px;width:calc(100% - 2rem)">
    <p style="font-size:.875rem;margin-bottom:1rem">🍪 Ce site utilise des cookies pour améliorer votre expérience. <a href="/politique-cookies" style="color:var(--clr-primary);font-weight:600">En savoir plus</a></p>
    <div style="display:flex;gap:.75rem;justify-content:flex-end">
        <button id="cookie-refuse" class="btn btn--outline btn--sm">Refuser</button>
        <button id="cookie-accept" class="btn btn--primary btn--sm">Accepter</button>
    </div>
</div>
