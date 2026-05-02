<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › <a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 5</p>
    <h1>Revue mensuelle</h1>
    <p class="opt-article__lead">Passer de l’opérationnel à la stratégie : bilan, arbitrages, backlog pour le mois suivant.</p>
    <ul class="opt-article__list">
        <li><strong>Ordre du jour type</strong> : synthèse chiffres (vs mois précédent et vs objectif), canaux gagnants / perdants, retours terrain (agences, téléphonie).</li>
        <li><strong>Budget</strong> : réaffectation simple (stop / continue / augmenter) par ligne de dépense.</li>
        <li><strong>Backlog</strong> : 5 actions max notées, une responsable par action, échéance avant fin de mois.</li>
    </ul>
    <p class="opt-article__prev"><a href="<?= htmlspecialchars($base . '&view=etape-tests', ENT_QUOTES, 'UTF-8') ?>">← Étape précédente</a></p>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=rapport-mensuel', ENT_QUOTES, 'UTF-8') ?>">Générer le rapport mensuel (PDF / email)</a> · <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Retour à l’accueil Optimiser</a></p>
</div>
<style>
.opt-article{max-width:52rem;font-family:system-ui,-apple-system,sans-serif;color:#0f172a;line-height:1.6}
.opt-article__crumb{font-size:.88rem;color:#64748b;margin:0 0 1rem}
.opt-article__crumb a{color:#6366f1;text-decoration:none;font-weight:600}
.opt-article__crumb a:hover{text-decoration:underline}
.opt-article h1{margin:0 0 .75rem;font-size:1.45rem;font-weight:800;letter-spacing:-.02em}
.opt-article__lead{margin:0 0 1.25rem;color:#475569;font-size:1rem}
.opt-article__list{margin:0;padding-left:1.25rem}
.opt-article__list li{margin-bottom:.65rem}
.opt-article__prev{margin:1.5rem 0 0;font-size:.9rem}
.opt-article__prev a{color:#64748b;text-decoration:none}
.opt-article__prev a:hover{color:#4f46e5;text-decoration:underline}
.opt-article__tools{margin:1rem 0 0;font-size:.9rem;color:#64748b}
.opt-article__tools a{color:#6366f1;font-weight:600;text-decoration:none}
.opt-article__tools a:hover{text-decoration:underline}
</style>
