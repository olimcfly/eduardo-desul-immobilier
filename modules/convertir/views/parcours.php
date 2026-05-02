<?php

declare(strict_types=1);

$base = '/admin?module=convertir';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="conv-guide">
    <div class="conv-guide__hero">
        <div class="conv-guide__badge">Guide de conversion</div>
        <h1 class="conv-guide__title">Signer plus de mandats</h1>
        <p class="conv-guide__lead">Transformez vos prospects en clients avec un processus simple, répétable et mesurable.</p>
    </div>

    <div class="conv-guide__grid">
        <a class="conv-card" href="<?= htmlspecialchars($base . '&action=qualifier', ENT_QUOTES, 'UTF-8') ?>">
            <div class="conv-card__num">1</div>
            <h2 class="conv-card__title">Qualifier rapidement</h2>
            <p class="conv-card__text">Identifier en 3 minutes si le lead mérite une priorité immédiate.</p>
            <span class="conv-card__link">Ouvrir l'étape →</span>
        </a>
        <a class="conv-card" href="<?= htmlspecialchars($base . '&action=script-appel', ENT_QUOTES, 'UTF-8') ?>">
            <div class="conv-card__num">2</div>
            <h2 class="conv-card__title">Créer un script d'appel</h2>
            <p class="conv-card__text">Uniformiser le premier contact et limiter les trous dans l'échange.</p>
            <span class="conv-card__link">Ouvrir l'étape →</span>
        </a>
        <a class="conv-card" href="<?= htmlspecialchars($base . '&action=objections', ENT_QUOTES, 'UTF-8') ?>">
            <div class="conv-card__num">3</div>
            <h2 class="conv-card__title">Gérer les objections</h2>
            <p class="conv-card__text">Répondre sans se justifier, recadrer et conserver la confiance.</p>
            <span class="conv-card__link">Ouvrir l'étape →</span>
        </a>
        <a class="conv-card" href="<?= htmlspecialchars($base . '&action=rdv', ENT_QUOTES, 'UTF-8') ?>">
            <div class="conv-card__num">4</div>
            <h2 class="conv-card__title">Proposer un rendez-vous</h2>
            <p class="conv-card__text">Convertir l'intérêt en engagement concret avec une date précise.</p>
            <span class="conv-card__link">Ouvrir l'étape →</span>
        </a>
        <a class="conv-card" href="<?= htmlspecialchars($base . '&action=signature', ENT_QUOTES, 'UTF-8') ?>">
            <div class="conv-card__num">5</div>
            <h2 class="conv-card__title">Signer le mandat</h2>
            <p class="conv-card__text">Préparer la proposition finale et clarifier la prochaine action.</p>
            <span class="conv-card__link">Ouvrir l'étape →</span>
        </a>
    </div>

    <p class="conv-guide__footer">
        <a href="<?= htmlspecialchars($base . '&action=rdv', ENT_QUOTES, 'UTF-8') ?>">Accéder à l'agenda RDV</a>
        · <a href="<?= htmlspecialchars($base . '&action=suivi-post-rdv', ENT_QUOTES, 'UTF-8') ?>">Voir la séquence de relance</a>
    </p>
</div>
<style>
.conv-guide{font-family:system-ui,-apple-system,sans-serif;color:#0f2237}
.conv-guide__hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:#fff;border-radius:16px;padding:2rem 2.25rem;margin-bottom:1.75rem}
.conv-guide__badge{display:inline-block;background:rgba(201,168,76,.2);padding:.35rem .75rem;border-radius:999px;font-size:.75rem;font-weight:600;margin-bottom:1rem;border:1px solid rgba(201,168,76,.35);color:#f4d78f}
.conv-guide__title{margin:0 0 .75rem;font-size:1.65rem;font-weight:800;letter-spacing:-.02em}
.conv-guide__lead{margin:0;opacity:.95;font-size:1rem;line-height:1.55;max-width:52ch}
.conv-guide__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem}
.conv-card{display:block;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.35rem 1.25rem;text-decoration:none;color:inherit;transition:border-color .2s,box-shadow .2s}
.conv-card:hover{border-color:#f5d78f;box-shadow:0 10px 28px rgba(201,168,76,.12)}
.conv-card__num{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#c9a84c,#b8943d);color:#0f2237;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:.85rem;font-size:.95rem}
.conv-card__title{margin:0 0 .45rem;font-size:1.05rem;font-weight:700}
.conv-card__text{margin:0 0 .85rem;font-size:.88rem;color:#64748b;line-height:1.5}
.conv-card__link{font-size:.82rem;font-weight:600;color:#7c5d1d}
.conv-guide__footer{margin:1.5rem 0 0;font-size:.9rem;color:#64748b}
.conv-guide__footer a{color:#7c5d1d;font-weight:600;text-decoration:none}
.conv-guide__footer a:hover{text-decoration:underline}
</style>
