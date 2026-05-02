<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'recommandations';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › Recommandations IA</p>
    <h1>Recommandations IA</h1>
    <p class="opt-article__lead">Les suggestions automatiques « tout-en-un » arriveront ici. En attendant, concentrez-vous sur les leviers à fort impact déjà disponibles dans l’admin.</p>
    <h2 class="opt-article__h2">Par où commencer</h2>
    <ul class="opt-article__list">
        <li><a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">Tableau de bord Analytics</a> : repérez la source de leads la plus productive sur 90 jours.</li>
        <li><a href="/admin?module=seo">Module SEO</a> : pages villes, contenus, et suivi des performances organiques.</li>
        <li><a href="<?= htmlspecialchars($base . '&view=rapport-mensuel', ENT_QUOTES, 'UTF-8') ?>">Rapport mensuel</a> : synthèse partageable avec votre équipe ou vos partenaires.</li>
    </ul>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours d’optimisation (5 étapes)</a></p>
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
.opt-article__list a{color:#6366f1;font-weight:600;text-decoration:none}
.opt-article__list a:hover{text-decoration:underline}
.opt-article__tools{margin:1.5rem 0 0;font-size:.9rem;color:#64748b}
.opt-article__tools a{color:#6366f1;font-weight:600;text-decoration:none}
.opt-article__tools a:hover{text-decoration:underline}
</style>
