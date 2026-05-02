<?php

declare(strict_types=1);

$base = '/admin?module=convertir';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="conv-article">
    <p class="conv-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Convertir</a> › <a href="<?= htmlspecialchars($base . '&action=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 2</p>
    <h1>Créer un script d'appel</h1>
    <p class="conv-article__lead">Un script simple évite les oublis et améliore la constance commerciale.</p>
    <ul class="conv-article__list">
        <li><strong>Ouverture</strong> : se présenter + rappeler le motif en une phrase.</li>
        <li><strong>Questions clés</strong> : 2 à 3 questions de qualification maximum.</li>
        <li><strong>Valeur</strong> : expliquer ce que le rendez-vous apporte concrètement au prospect.</li>
        <li><strong>CTA</strong> : proposer deux créneaux précis plutôt qu'une question ouverte.</li>
    </ul>
    <p class="conv-article__next"><a href="<?= htmlspecialchars($base . '&action=objections', ENT_QUOTES, 'UTF-8') ?>">Étape suivante : objections →</a></p>
    <p class="conv-article__prev"><a href="<?= htmlspecialchars($base . '&action=qualifier', ENT_QUOTES, 'UTF-8') ?>">← Étape précédente</a></p>
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
.conv-article__prev{margin:.75rem 0 0;font-size:.9rem}
.conv-article__prev a{color:#64748b;text-decoration:none}
.conv-article__prev a:hover{color:#7c5d1d;text-decoration:underline}
</style>
