<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › <a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 4</p>
    <h1>Tests A/B ciblés</h1>
    <p class="opt-article__lead">Un seul test à la fois, une hypothèse claire, une durée fixe.</p>
    <ul class="opt-article__list">
        <li><strong>Objets de test</strong> : titre d’annonce, photo principale, CTA du hero, longueur du formulaire, page ville (bloc confiance).</li>
        <li><strong>Méthode</strong> : trafic suffisant sur la variante ; sinon test séquentiel (2 semaines A puis 2 semaines B) en notant le contexte saisonnier.</li>
        <li><strong>Critère de succès</strong> : lié au KPI (ex. +15 % de demandes d’estimation), pas au « feeling ».</li>
    </ul>
    <p class="opt-article__next"><a href="<?= htmlspecialchars($base . '&view=etape-analyse', ENT_QUOTES, 'UTF-8') ?>">Étape suivante : revue mensuelle →</a></p>
    <p class="opt-article__prev"><a href="<?= htmlspecialchars($base . '&view=etape-dashboard', ENT_QUOTES, 'UTF-8') ?>">← Étape précédente</a></p>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=ab-testing', ENT_QUOTES, 'UTF-8') ?>">Page A/B Testing (cadre & idées)</a></p>
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
