<?php
$pageTitle = 'Eduardo Desul Immobilier — Conseiller immobilier à Bordeaux';
$metaDesc  = 'Eduardo Desul, conseiller immobilier indépendant à Bordeaux. Estimation gratuite, accompagnement achat/vente, expertise locale. Contactez-nous dès aujourd\'hui.';
?>

<!-- ── Hero ────────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero__bg" style="background-image:url('/assets/images/hero-bordeaux.jpg')"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Conseiller immobilier à Bordeaux</span>
            <h1>Votre projet immobilier,<br>entre de bonnes mains</h1>
            <p class="hero__subtitle">
                Eduardo Desul vous accompagne de A à Z dans l'achat, la vente et l'estimation de votre bien à Bordeaux et alentours. Expertise locale, disponibilité et transparence.
            </p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Estimer mon bien gratuitement</a>
                <a href="/biens" class="btn btn--outline-white btn--lg">Voir les annonces</a>
            </div>

            <div class="hero__trust">
                <div class="trust-item">
                    <span class="value">+200</span>
                    <span class="label">Transactions réalisées</span>
                </div>
                <div class="trust-item">
                    <span class="value">4.9★</span>
                    <span class="label">Note Google</span>
                </div>
                <div class="trust-item">
                    <span class="value">15 ans</span>
                    <span class="label">D'expérience</span>
                </div>
                <div class="trust-item">
                    <span class="value">48h</span>
                    <span class="label">Délai de réponse moyen</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Services ────────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Ce que je fais pour vous</span>
            <h2 class="section-title">Des services sur mesure</h2>
            <p class="section-subtitle">Un accompagnement complet, de la première visite jusqu'à la remise des clés.</p>
        </div>
        <div class="grid-3" data-animate>
            <div class="service-card">
                <div class="service-card__icon">🏠</div>
                <h3 class="service-card__title">Vente de bien</h3>
                <p class="service-card__text">Estimation précise, mise en valeur professionnelle, diffusion multi-supports et accompagnement jusqu'à la signature.</p>
                <a href="/services" class="btn btn--outline btn--sm" style="margin-top:1rem">En savoir plus</a>
            </div>
            <div class="service-card">
                <div class="service-card__icon">🔑</div>
                <h3 class="service-card__title">Achat immobilier</h3>
                <p class="service-card__text">Recherche ciblée, visites accompagnées, négociation et sécurisation de votre acquisition.</p>
                <a href="/services" class="btn btn--outline btn--sm" style="margin-top:1rem">En savoir plus</a>
            </div>
            <div class="service-card">
                <div class="service-card__icon">📊</div>
                <h3 class="service-card__title">Estimation gratuite</h3>
                <p class="service-card__text">Une évaluation précise de votre bien, basée sur les données du marché bordelais et mon expertise terrain.</p>
                <a href="/estimation-gratuite" class="btn btn--accent btn--sm" style="margin-top:1rem">Estimer maintenant</a>
            </div>
        </div>
    </div>
</section>

<!-- ── Biens en vedette ─────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section__header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem">
            <div data-animate>
                <span class="section-label">Dernières annonces</span>
                <h2 class="section-title" style="margin-bottom:0">Biens disponibles</h2>
            </div>
            <a href="/biens" class="btn btn--outline">Toutes les annonces →</a>
        </div>

        <div class="biens-grid" data-animate>
            <!-- Placeholder cards – remplacées dynamiquement -->
            <?php
            $biensVedette = [
                ['titre' => 'Appartement T3 lumineux', 'type' => 'Vente', 'prix' => '295 000', 'loc' => 'Bordeaux Chartrons', 'surface' => '72', 'pieces' => '3', 'img' => '/assets/images/placeholder-bien-1.jpg'],
                ['titre' => 'Maison avec jardin', 'type' => 'Vente', 'prix' => '485 000', 'loc' => 'Mérignac', 'surface' => '145', 'pieces' => '5', 'img' => '/assets/images/placeholder-bien-2.jpg'],
                ['titre' => 'Studio centre-ville', 'type' => 'Location', 'prix' => '750 /mois', 'loc' => 'Bordeaux Centre', 'surface' => '28', 'pieces' => '1', 'img' => '/assets/images/placeholder-bien-3.jpg'],
            ];
            foreach ($biensVedette as $b): ?>
            <article class="bien-card">
                <div class="bien-card__img">
                    <img src="<?= e($b['img']) ?>" alt="<?= e($b['titre']) ?>" loading="lazy" width="400" height="300">
                    <span class="bien-card__badge badge--<?= strtolower($b['type']) === 'vente' ? 'vente' : 'location' ?>"><?= e($b['type']) ?></span>
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
                <span class="section-label">Qui suis-je ?</span>
                <h2 class="section-title">Eduardo Desul,<br>votre conseiller de confiance</h2>
                <p>Fort de plus de 15 ans d'expérience sur le marché immobilier bordelais, je mets mon expertise à votre service pour vous aider à prendre les meilleures décisions.</p>
                <p>Mon approche : écoute, transparence et résultats concrets. Je travaille en toute indépendance pour défendre vos intérêts, pas ceux d'une enseigne.</p>
                <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin:1.5rem 0 2rem">
                    <div>
                        <strong style="font-family:var(--font-display);font-size:1.75rem;color:var(--clr-primary)">+200</strong>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)">Transactions</div>
                    </div>
                    <div>
                        <strong style="font-family:var(--font-display);font-size:1.75rem;color:var(--clr-primary)">4.9/5</strong>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)">Note clients</div>
                    </div>
                    <div>
                        <strong style="font-family:var(--font-display);font-size:1.75rem;color:var(--clr-primary)">15 ans</strong>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)">D'expérience</div>
                    </div>
                </div>
                <a href="/a-propos" class="btn btn--primary">En savoir plus sur moi</a>
            </div>
            <div data-animate style="position:relative">
                <div style="background:var(--clr-primary);border-radius:var(--radius-xl);aspect-ratio:4/5;display:flex;align-items:center;justify-content:center;font-size:6rem;overflow:hidden">
                    <!-- Remplacer par la vraie photo -->
                    <img src="/assets/images/eduardo-portrait.jpg" alt="Eduardo Desul, conseiller immobilier à Bordeaux" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display='none'">
                    <span style="position:absolute">👤</span>
                </div>
                <div style="position:absolute;bottom:-1rem;right:-1rem;background:var(--clr-accent);color:var(--clr-primary);border-radius:var(--radius-lg);padding:1.25rem;font-weight:700;text-align:center;box-shadow:var(--shadow-lg)">
                    <div style="font-size:1.5rem;font-family:var(--font-display)">N°1</div>
                    <div style="font-size:.75rem">à Bordeaux</div>
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
            <h2 class="section-title">Avis de mes clients</h2>
        </div>
        <div class="grid-3" data-animate>
            <?php
            $avis = [
                ['nom' => 'Marie & Thomas L.', 'note' => 5, 'text' => 'Eduardo nous a trouvé notre appartement idéal en moins d\'un mois. Professionnel, réactif et vraiment à l\'écoute. Nous le recommandons les yeux fermés !', 'date' => 'Il y a 2 semaines'],
                ['nom' => 'Jean-Pierre M.', 'note' => 5, 'text' => 'Vente de ma maison en 3 semaines au prix demandé. Un suivi impeccable du début à la fin. Merci Eduardo !', 'date' => 'Il y a 1 mois'],
                ['nom' => 'Sophie D.', 'note' => 5, 'text' => 'Première acquisition immobilière, j\'avais des questions sur tout. Eduardo a pris le temps de tout m\'expliquer et m\'a évité plusieurs pièges. Super expérience.', 'date' => 'Il y a 2 mois'],
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
            <span class="section-label">Bordeaux & environs</span>
            <h2 class="section-title">Découvrez les quartiers</h2>
            <p class="section-subtitle">Guides de quartiers, prix au m², ambiance locale — tout ce qu'il faut savoir pour bien s'installer.</p>
        </div>
        <div class="villes-grid" data-animate>
            <?php
            $villes = [
                ['nom' => 'Bordeaux Centre', 'biens' => 12, 'img' => '/assets/images/bordeaux-centre.jpg'],
                ['nom' => 'Chartrons', 'biens' => 8, 'img' => '/assets/images/chartrons.jpg'],
                ['nom' => 'Mérignac', 'biens' => 6, 'img' => '/assets/images/merignac.jpg'],
                ['nom' => 'Pessac', 'biens' => 5, 'img' => '/assets/images/pessac.jpg'],
            ];
            foreach ($villes as $v): ?>
            <a href="/guide-local/<?= e(slugify($v['nom'])) ?>" class="ville-card">
                <img src="<?= e($v['img']) ?>" alt="Immobilier <?= e($v['nom']) ?>" loading="lazy" width="400" height="300">
                <div class="ville-card__overlay">
                    <div class="ville-card__name"><?= e($v['nom']) ?></div>
                    <div class="ville-card__count"><?= $v['biens'] ?> biens disponibles</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2rem">
            <a href="/guide-local" class="btn btn--outline">Tous les quartiers →</a>
        </div>
    </div>
</section>

<!-- ── CTA ──────────────────────────────────────────────────── -->
<section class="cta-banner">
    <div class="container">
        <span class="section-label" style="color:var(--clr-accent)">Prêt à passer à l'action ?</span>
        <h2>Parlons de votre projet</h2>
        <p>Une question, un projet de vente ou d'achat ? Je vous réponds personnellement dans les 24h.</p>
        <div class="cta-banner__actions">
            <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Estimation gratuite</a>
            <a href="/contact" class="btn btn--outline-white btn--lg">Prendre contact</a>
        </div>
    </div>
</section>

<!-- ── Blog récent ──────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem">
            <div>
                <span class="section-label">Conseils & actualités</span>
                <h2 class="section-title" style="margin-bottom:0">Derniers articles</h2>
            </div>
            <a href="/blog" class="btn btn--outline">Voir tous les articles →</a>
        </div>
        <div class="blog-grid" data-animate>
            <?php
            $articles = [
                ['cat' => 'Vente', 'titre' => 'Comment bien préparer la vente de votre bien', 'excerpt' => 'Les 5 étapes essentielles pour maximiser la valeur de votre propriété avant de la mettre sur le marché.', 'date' => '28 mars 2026', 'img' => '/assets/images/blog-1.jpg', 'slug' => 'preparer-vente-bien'],
                ['cat' => 'Achat', 'titre' => 'Investir à Bordeaux en 2026 : les quartiers à suivre', 'excerpt' => 'Analyse des tendances du marché bordelais et des secteurs offrant le meilleur rapport rendement/risque.', 'date' => '15 mars 2026', 'img' => '/assets/images/blog-2.jpg', 'slug' => 'investir-bordeaux-2026'],
                ['cat' => 'Financement', 'titre' => 'Taux immobiliers : ce qui change en 2026', 'excerpt' => 'Comprendre l\'évolution des taux pour optimiser votre plan de financement et négocier avec les banques.', 'date' => '5 mars 2026', 'img' => '/assets/images/blog-3.jpg', 'slug' => 'taux-immobiliers-2026'],
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

<!-- ── Cookie banner ────────────────────────────────────────── -->
<div id="cookie-banner" style="display:none;position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:999;background:var(--clr-white);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.25rem 1.75rem;box-shadow:var(--shadow-lg);max-width:600px;width:calc(100% - 2rem);display:none">
    <p style="font-size:.875rem;margin-bottom:1rem">🍪 Ce site utilise des cookies pour améliorer votre expérience. <a href="/politique-cookies" style="color:var(--clr-primary);font-weight:600">En savoir plus</a></p>
    <div style="display:flex;gap:.75rem;justify-content:flex-end">
        <button id="cookie-refuse" class="btn btn--outline btn--sm">Refuser</button>
        <button id="cookie-accept" class="btn btn--primary btn--sm">Accepter</button>
    </div>
</div>
