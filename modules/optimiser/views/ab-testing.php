<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'ab-testing';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › A/B Testing</p>
    <h1>A/B Testing</h1>
    <p class="opt-article__lead">Cadre pratique pour tester vos contenus et parcours sans noyer l’équipe sous les variantes.</p>
    <h2 class="opt-article__h2">Avant de lancer un test</h2>
    <ul class="opt-article__list">
        <li>Volume minimal : si vous recevez peu de trafic, privilégiez des changements forts (une seule grande hypothèse) ou un test en série.</li>
        <li>Éviter les tests « fourre-tout » : une question du type « le CTA vert convertit-il mieux que le bleu ? » plutôt que tout mélanger.</li>
        <li>Documenter la période (soldes, vacances, pic saisonnier) pour interpréter les résultats.</li>
    </ul>
    <h2 class="opt-article__h2">Idées adaptées à l’immobilier</h2>
    <ul class="opt-article__list">
        <li>Titre d’annonce : surface + quartier vs bénéfice émotionnel (« lumineux », « calme »).</li>
        <li>Ordre des photos : façade d’abord vs pièce de vie.</li>
        <li>Formulaire court (email + téléphone) vs champs additionnels (budget, délai).</li>
    </ul>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=etape-tests', ENT_QUOTES, 'UTF-8') ?>">Retour à l’étape 4 du parcours</a> · <a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">Mesurer l’impact dans Analytics</a></p>
</div>
<style>
.opt-article{max-width:52rem;font-family:system-ui,-apple-system,sans-serif;color:#0f172a;line-height:1.6}
.opt-article__crumb{font-size:.88rem;color:#64748b;margin:0 0 1rem}
.opt-article__crumb a{color:#6366f1;text-decoration:none;font-weight:600}
.opt-article__crumb a:hover{text-decoration:underline}
.opt-article h1{margin:0 0 .75rem;font-size:1.45rem;font-weight:800;letter-spacing:-.02em}
.opt-article__lead{margin:0 0 1.25rem;color:#475569;font-size:1rem}
.opt-article__h2{margin:1.5rem 0 .65rem;font-size:1.05rem;font-weight:700}
.opt-article__list{margin:0;padding-left:1.25rem}
.opt-article__list li{margin-bottom:.65rem}
.opt-article__tools{margin:1.5rem 0 0;font-size:.9rem;color:#64748b}
.opt-article__tools a{color:#6366f1;font-weight:600;text-decoration:none}
.opt-article__tools a:hover{text-decoration:underline}
</style>
