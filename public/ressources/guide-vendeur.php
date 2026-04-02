<?php
$pageTitle = 'Guide du vendeur immobilier — Eduardo Desul';
$metaDesc  = 'Guide complet pour vendre votre bien immobilier à Bordeaux : estimation, diagnostics, home staging, négociation.';
$extraCss  = ['/assets/css/guide.css'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><a href="/ressources">Ressources</a><span>Guide vendeur</span></nav>
        <h1>Guide du vendeur</h1>
        <p>Toutes les étapes pour vendre votre bien au meilleur prix, avec sérénité.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="article-layout">
            <div>
                <div class="article-content">
                    <h2>Étape 1 — Faire estimer votre bien</h2>
                    <div class="guide-steps">
                        <?php
                        $etapes = [
                            ['Estimation au juste prix', 'La première étape est cruciale. Un bien surestimé reste sur le marché et perd de sa valeur. Faites appel à un professionnel pour une évaluation objective basée sur les transactions récentes de votre secteur.'],
                            ['Réaliser les diagnostics obligatoires', 'DPE (obligatoire et daté de moins de 10 ans), amiante, plomb, électricité, gaz, assainissement, ERP… Ces diagnostics doivent être annexés au compromis de vente. Mieux vaut les faire dès le départ.'],
                            ['Préparer votre bien (home staging)', 'Dépersonnalisez, désencombrez, nettoyez en profondeur. Faites les petites réparations visibles. L\'objectif : que l\'acheteur puisse se projeter. Un bien bien présenté se vend 15% plus vite.'],
                            ['Photos professionnelles & annonce', 'Des photos lumineuses et qualitatives sont indispensables. 95% des acheteurs débutent leur recherche sur internet. Une belle annonce génère 3 fois plus de contacts.'],
                            ['Sélectionner les acheteurs sérieux', 'Pas question de faire visiter à tout le monde. Un bon conseiller filtre les contacts pour ne vous présenter que des acquéreurs qualifiés : budget validé, projet cohérent.'],
                            ['Négocier les offres', 'Recevoir une offre d\'achat est une étape délicate. Eduardo vous conseille sur comment évaluer chaque offre et négocier les meilleures conditions (prix, conditions suspensives, délais).'],
                            ['Signature du compromis', 'Le compromis de vente engage les deux parties. Votre conseiller vérifie toutes les clauses et vous explique chaque point. L\'acheteur dispose ensuite d\'un délai de rétractation de 10 jours.'],
                            ['Obtention du financement acquéreur', 'Si la vente est sous condition suspensive d\'obtention de prêt, ce délai peut prendre 45 à 60 jours. Eduardo fait le point régulièrement avec les deux parties.'],
                            ['Signature de l\'acte authentique', 'Chez le notaire, vous remettez les clés et encaissez le prix de vente. C\'est la concrétisation de votre projet ! Eduardo est présent à vos côtés jusqu\'au bout.'],
                            ['Formalités post-vente', 'Résiliation des contrats liés au bien (assurance, énergie, eau), déclaration fiscale de la plus-value éventuelle. Eduardo vous guide dans ces démarches.'],
                        ];
                        foreach ($etapes as $e): ?>
                        <div class="guide-step">
                            <div class="guide-step__content">
                                <h3><?= htmlspecialchars($e[0]) ?></h3>
                                <p><?= htmlspecialchars($e[1]) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="background:var(--clr-bg);border-radius:var(--radius-lg);padding:2rem;border:1px solid var(--clr-border);margin-top:2rem">
                    <h3 style="margin-bottom:1rem">Check-list vendeur</h3>
                    <ul style="display:flex;flex-direction:column;gap:.6rem">
                        <?php foreach (['Demander une estimation gratuite', 'Commander les diagnostics obligatoires', 'Dépersonnaliser et ranger le bien', 'Faire des photos professionnelles', 'Valider le prix de mise en vente', 'Préparer les documents de copropriété (si applicable)', 'Anticiper les questions sur les charges et travaux'] as $item): ?>
                        <li style="display:flex;gap:.75rem;font-size:.9rem">
                            <span style="color:var(--clr-success);font-weight:700;flex-shrink:0">☐</span>
                            <?= e($item) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <aside class="blog-sidebar">
                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem">
                    <h4 style="color:white;margin-bottom:.75rem">Prêt à vendre ?</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Commencez par une estimation gratuite de votre bien.</p>
                    <a href="/estimation-gratuite" class="btn btn--accent btn--sm btn--full">Estimation gratuite</a>
                </div>
                <div class="sidebar-box">
                    <div class="sidebar-box__head">À lire aussi</div>
                    <div class="sidebar-box__body">
                        <a href="/ressources/guide-acheteur" style="display:block;padding:.5rem 0;font-size:.875rem;border-bottom:1px solid var(--clr-border)">→ Guide acheteur</a>
                        <a href="/blog/preparer-vente-bien" style="display:block;padding:.5rem 0;font-size:.875rem">→ Préparer sa vente</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
