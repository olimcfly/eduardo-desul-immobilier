<?php
$pageTitle = 'Guide de l\'acheteur immobilier — ' . ADVISOR_NAME . '';
$metaDesc  = 'Guide complet pour acheter votre premier bien immobilier à Aix-en-Provence : financement, recherche, offre, signature.';
$extraCss  = ['/assets/css/guide.css'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><a href="/ressources">Ressources</a><span>Guide acheteur</span></nav>
        <h1>Guide de l'acheteur</h1>
        <p>De la première visite à la remise des clés : tout ce qu'il faut savoir pour acheter sereinement.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="article-layout">
            <div>
                <div class="article-content">
                    <div class="guide-steps">
                        <?php
                        $etapes = [
                            ['Définir votre projet', 'Avant de vous lancer dans les visites, clarifiez votre projet : surface, quartier, type de bien, budget. Plus vous serez précis, plus la recherche sera efficace.'],
                            ['Valider votre capacité d\'emprunt', 'Consultez votre banque ou un courtier pour connaître votre capacité d\'emprunt. C\'est indispensable pour visiter des biens dans la bonne fourchette de prix. Comptez environ 30% max de vos revenus en mensualités.'],
                            ['Définir les bons critères', 'Distinguez vos critères "absolus" (obligatoires) de vos critères "souhaits" (négociables). Cela vous aidera à ne pas passer à côté du bien idéal par perfectionnisme.'],
                            ['Chercher et visiter', 'Portails immobiliers, réseau professionnel, biens off-market… Pascal a accès à des biens que vous ne trouverez pas en ligne. Lors des visites, soyez attentif à l\'état général, l\'exposition, les nuisances.'],
                            ['Analyser avant d\'offrir', 'Avant de formuler une offre, analysez le juste prix. Pascal vous donne son avis sur la valeur du bien et les marges de négociation possibles.'],
                            ['Faire une offre d\'achat', 'L\'offre d\'achat est un document engageant. Elle précise le prix proposé, les conditions (financement, travaux) et le délai de réponse du vendeur.'],
                            ['Signature du compromis', 'Vous disposez de 10 jours pour vous rétracter après la signature du compromis. C\'est la période pour finaliser votre dossier de financement.'],
                            ['Obtenir votre prêt', 'Vous avez généralement 45 à 60 jours pour obtenir votre accord de prêt. Préparez un dossier complet : 3 derniers bulletins de salaire, avis d\'imposition, relevés bancaires.'],
                            ['Signature de l\'acte définitif', 'Chez le notaire, vous réglez le prix et les frais (notaire ~7-8% dans l\'ancien), et récupérez les clés. C\'est la fin de votre parcours acheteur !'],
                        ];
                        foreach ($etapes as $et): ?>
                        <div class="guide-step">
                            <div class="guide-step__content">
                                <h3><?= htmlspecialchars($et[0]) ?></h3>
                                <p><?= htmlspecialchars($et[1]) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:2rem;margin-top:2rem;text-align:center">
                        <h3 style="color:white;margin-bottom:.75rem">Cherchez-vous un bien à Aix-en-Provence ?</h3>
                        <p style="opacity:.8;margin-bottom:1.5rem">Pascal vous aide à trouver le bien idéal, y compris des opportunités hors-marché.</p>
                        <a href="/contact" class="btn btn--accent">Décrire mon projet →</a>
                    </div>
                </div>
            </div>

            <aside class="blog-sidebar">
                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem">
                    <h4 style="color:white;margin-bottom:.75rem">Voir les annonces</h4>
                    <a href="/biens" class="btn btn--accent btn--sm btn--full">Voir les biens →</a>
                </div>
                <div class="sidebar-box">
                    <div class="sidebar-box__head">Ressources utiles</div>
                    <div class="sidebar-box__body">
                        <a href="/ressources/guide-vendeur" style="display:block;padding:.5rem 0;font-size:.875rem;border-bottom:1px solid var(--clr-border)">→ Guide vendeur</a>
                        <a href="/guide-local" style="display:block;padding:.5rem 0;font-size:.875rem;border-bottom:1px solid var(--clr-border)">→ Guide local Aix-en-Provence</a>
                        <a href="/blog/negocier-prix-achat" style="display:block;padding:.5rem 0;font-size:.875rem">→ Négocier le prix</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
