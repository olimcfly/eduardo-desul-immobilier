<?php

declare(strict_types=1);

$base = '/admin?module=convertir';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="conv-article">
    <p class="conv-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Convertir</a> › <a href="<?= htmlspecialchars($base . '&action=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 5</p>
    <h1>Signer le mandat</h1>
    <p class="conv-article__lead">Préparez la réunion finale pour lever les derniers blocages et conclure sereinement.</p>
    <ul class="conv-article__list">
        <li><strong>Avant le rendez-vous</strong> : dossier prêt (estimatif, stratégie de diffusion, planning de commercialisation).</li>
        <li><strong>Pendant</strong> : reformulation des enjeux client, présentation des engagements mutuels, validation des prochaines étapes.</li>
        <li><strong>Après</strong> : confirmation écrite le jour même avec date de lancement des actions.</li>
    </ul>
    <p class="conv-article__prev"><a href="<?= htmlspecialchars($base . '&action=rdv', ENT_QUOTES, 'UTF-8') ?>">← Étape précédente</a></p>
    <p class="conv-article__tools"><a href="<?= htmlspecialchars($base . '&action=suivi-post-rdv', ENT_QUOTES, 'UTF-8') ?>">Ouvrir la séquence de suivi post-RDV</a></p>
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
.conv-article__prev{margin:1.5rem 0 0;font-size:.9rem}
.conv-article__prev a{color:#64748b;text-decoration:none}
.conv-article__prev a:hover{color:#7c5d1d;text-decoration:underline}
.conv-article__tools{margin:1rem 0 0;font-size:.9rem;color:#64748b}
.conv-article__tools a{color:#7c5d1d;font-weight:600;text-decoration:none}
.conv-article__tools a:hover{text-decoration:underline}
</style>
