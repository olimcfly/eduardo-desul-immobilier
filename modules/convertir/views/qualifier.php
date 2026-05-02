<?php

declare(strict_types=1);

$base = '/admin?module=convertir';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="conv-article">
    <p class="conv-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Convertir</a> › <a href="<?= htmlspecialchars($base . '&action=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 1</p>
    <h1>Qualifier rapidement</h1>
    <p class="conv-article__lead">Le premier échange doit confirmer le besoin, le timing et la faisabilité du projet.</p>
    <ul class="conv-article__list">
        <li><strong>Objectif du contact</strong> : vente, achat, estimation, information simple.</li>
        <li><strong>Échéance</strong> : quand la personne veut avancer (immédiat, 1-3 mois, +6 mois).</li>
        <li><strong>Contexte</strong> : décisionnaire unique ou couple, contraintes de disponibilité, historique avec une agence.</li>
    </ul>
    <p class="conv-article__next"><a href="<?= htmlspecialchars($base . '&action=script-appel', ENT_QUOTES, 'UTF-8') ?>">Étape suivante : script d'appel →</a></p>
</div>
<style>
.conv-article{max-width:52rem;font-family:system-ui,-apple-system,sans-serif;color:#0f2237;line-height:1.6}
.conv-article__crumb{font-size:.88rem;color:#64748b;margin:0 0 1rem}
.conv-article__crumb a{color:#7c5d1d;text-decoration:none;font-weight:600}
.conv-article__crumb a:hover{text-decoration:underline}
.conv-article h1{margin:0 0 .75rem;font-size:1.45rem;font-weight:800;letter-spacing:-.02em}
.conv-article__lead{margin:0 0 1.25rem;color:#475569;font-size:1rem}
.conv-article__list{margin:0;padding-left:1.25rem}
.conv-article__list li{margin-bottom:.65rem}
.conv-article__next{margin:1.5rem 0 0}
.conv-article__next a{font-weight:700;color:#7c5d1d;text-decoration:none}
.conv-article__next a:hover{text-decoration:underline}
</style>
