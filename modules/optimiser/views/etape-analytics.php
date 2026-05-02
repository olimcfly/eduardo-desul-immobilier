<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › <a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 1</p>
    <h1>Installer et fiabiliser les analytics</h1>
    <p class="opt-article__lead">Sans mesure cohérente, impossible d’optimiser. Cette étape pose les bases techniques et sémantiques.</p>
    <ul class="opt-article__list">
        <li><strong>Google Analytics 4</strong> : propriété dédiée au site, flux web + (si besoin) flux app. Vérifiez que les événements <code>generate_lead</code> ou équivalents remontent bien lors d’une prise de contact.</li>
        <li><strong>Pixel Meta</strong> (si campagnes social) : même définition d’événement « lead » pour comparer les coûts aux conversions CRM.</li>
        <li><strong>UTM systématiques</strong> sur les liens partagés (emailing, réseaux, partenaires) pour attribuer les leads aux bons canaux.</li>
    </ul>
    <p class="opt-article__next"><a href="<?= htmlspecialchars($base . '&view=etape-kpis', ENT_QUOTES, 'UTF-8') ?>">Étape suivante : définir les KPIs →</a></p>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">Ouvrir le tableau de bord Analytics</a> · <a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Retour au parcours</a></p>
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
.opt-article__tools{margin:1rem 0 0;font-size:.9rem;color:#64748b}
.opt-article__tools a{color:#6366f1;font-weight:600;text-decoration:none}
.opt-article__tools a:hover{text-decoration:underline}
</style>
