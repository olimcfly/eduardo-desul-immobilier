<?php
$pageTitle = 'Avis clients — Eduardo Desul Immobilier';
$metaDesc  = 'Découvrez les avis de nos clients satisfaits. Note moyenne 4.9/5 sur Google.';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Avis clients</span></nav>
        <h1>Avis de mes clients</h1>
        <p>La confiance de mes clients est ma plus belle récompense.</p>
    </div>
</div>

<section class="section">
    <div class="container">

        <!-- Note globale -->
        <div style="background:var(--clr-white);border-radius:var(--radius-xl);border:1px solid var(--clr-border);padding:2.5rem;text-align:center;max-width:600px;margin:0 auto 3rem;box-shadow:var(--shadow)" data-animate>
            <div style="font-size:4rem;margin-bottom:.5rem">⭐</div>
            <div style="font-family:var(--font-display);font-size:4rem;font-weight:700;color:var(--clr-primary);line-height:1">4.9</div>
            <div style="color:var(--clr-text-muted);margin-bottom:1rem">sur 5 — basé sur 87 avis Google</div>
            <div style="display:flex;justify-content:center;gap:.25rem;font-size:1.5rem;color:#f59e0b;margin-bottom:1.5rem">
                ★★★★★
            </div>
            <a href="https://g.page/r/" target="_blank" rel="noopener noreferrer" class="btn btn--outline">
                Laisser un avis Google →
            </a>
        </div>

        <!-- Grille avis -->
        <div class="grid-3" data-animate>
            <?php
            $avis = [
                ['nom' => 'Marie & Thomas L.', 'note' => 5, 'date' => 'Mars 2026', 'service' => 'Achat', 'text' => 'Eduardo nous a trouvé notre appartement idéal en moins d\'un mois. Professionnel, réactif et vraiment à l\'écoute. Nous le recommandons les yeux fermés !'],
                ['nom' => 'Jean-Pierre M.', 'note' => 5, 'date' => 'Février 2026', 'service' => 'Vente', 'text' => 'Vente de ma maison en 3 semaines au prix demandé. Un suivi impeccable du début à la fin. Merci Eduardo !'],
                ['nom' => 'Sophie D.', 'note' => 5, 'date' => 'Janvier 2026', 'service' => 'Achat', 'text' => 'Première acquisition immobilière, j\'avais des questions sur tout. Eduardo a pris le temps de tout m\'expliquer et m\'a évité plusieurs pièges.'],
                ['nom' => 'Laurent & Isabelle F.', 'note' => 5, 'date' => 'Décembre 2025', 'service' => 'Vente', 'text' => 'Après 2 mois sans résultat avec une agence classique, Eduardo a vendu notre bien en 15 jours. Sa connaissance du marché bordelais est impressionnante.'],
                ['nom' => 'Nathalie B.', 'note' => 5, 'date' => 'Novembre 2025', 'service' => 'Investissement', 'text' => 'Eduardo m\'a aidée à constituer un patrimoine locatif solide. Ses conseils sur les quartiers en développement étaient très pertinents.'],
                ['nom' => 'Ahmed K.', 'note' => 4, 'date' => 'Octobre 2025', 'service' => 'Achat', 'text' => 'Très bon accompagnement, Eduardo est disponible et professionnel. Quelques délais un peu longs mais le résultat est là. Appartement trouvé en 6 semaines.'],
            ];
            foreach ($avis as $a): ?>
            <div class="testimonial">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem">
                    <div class="testimonial__stars"><?= str_repeat('★', $a['note']) . str_repeat('☆', 5 - $a['note']) ?></div>
                    <span style="font-size:.75rem;background:var(--clr-bg);padding:.2rem .6rem;border-radius:20px;color:var(--clr-text-muted)"><?= e($a['service']) ?></span>
                </div>
                <p class="testimonial__text">"<?= e($a['text']) ?>"</p>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div class="testimonial__author"><?= e($a['nom']) ?></div>
                    <div class="testimonial__date"><?= e($a['date']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Vous aussi, faites confiance à Eduardo</h2>
        <p>Rejoignez les centaines de clients satisfaits qui ont concrétisé leur projet immobilier.</p>
        <div class="cta-banner__actions">
            <a href="/contact" class="btn btn--accent btn--lg">Démarrer mon projet</a>
        </div>
    </div>
</section>
