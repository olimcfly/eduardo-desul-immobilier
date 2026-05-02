<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › <a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 3</p>
    <h1>Tableau de bord hebdomadaire</h1>
    <p class="opt-article__lead">Un rituel court : chaque semaine, les mêmes chiffres, les mêmes décisions possibles.</p>
    <ul class="opt-article__list">
        <li><strong>Google Sheets ou Looker Studio</strong> : une page « synthèse » avec les KPIs de l’étape 2, alimentée manuellement au début puis par connecteurs si disponibles.</li>
        <li><strong>Fréquence</strong> : même jour, même durée (ex. lundi 20 min) pour éviter la dérive.</li>
        <li><strong>Sortie obligatoire</strong> : 1 à 3 actions concrètes notées (ex. « relancer 5 leads froids », « ajuster budget Meta »).</li>
    </ul>
    <p class="opt-article__next"><a href="<?= htmlspecialchars($base . '&view=etape-tests', ENT_QUOTES, 'UTF-8') ?>">Étape suivante : tests A/B →</a></p>
    <p class="opt-article__prev"><a href="<?= htmlspecialchars($base . '&view=etape-kpis', ENT_QUOTES, 'UTF-8') ?>">← Étape précédente</a></p>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">S’appuyer sur le tableau de bord admin</a></p>
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
.opt-article__next{margin:1.5rem 0 0}
.opt-article__next a{font-weight:700;color:#4f46e5;text-decoration:none}
.opt-article__next a:hover{text-decoration:underline}
.opt-article__prev{margin:.75rem 0 0;font-size:.9rem}
.opt-article__prev a{color:#64748b;text-decoration:none}
.opt-article__prev a:hover{color:#4f46e5;text-decoration:underline}
.opt-article__tools{margin:1rem 0 0;font-size:.9rem;color:#64748b}
.opt-article__tools a{color:#6366f1;font-weight:600;text-decoration:none}
.opt-article__tools a:hover{text-decoration:underline}
</style>
