<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-guide">
    <div class="opt-guide__hero">
        <div class="opt-guide__badge">Parcours en 5 étapes</div>
        <h1 class="opt-guide__title">Optimisez votre acquisition immobilière</h1>
        <p class="opt-guide__lead">Un parcours clair pour mesurer, prioriser et améliorer vos canaux (SEO, réseaux, portails, partenaires).</p>
    </div>
    <div class="opt-guide__grid">
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-analytics', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">1</div>
            <h2 class="opt-card__title">Installer les analytics</h2>
            <p class="opt-card__text">GA4, pixel Meta, événements clés (lead, visite, estimation).</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-kpis', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">2</div>
            <h2 class="opt-card__title">Définir 3–5 KPIs</h2>
            <p class="opt-card__text">Coût par lead, taux de qualification, délai de réponse, part canal.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-dashboard', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">3</div>
            <h2 class="opt-card__title">Tableau de bord hebdo</h2>
            <p class="opt-card__text">Google Sheets ou Looker Studio, mis à jour automatiquement si possible.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-tests', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">4</div>
            <h2 class="opt-card__title">Tests A/B ciblés</h2>
            <p class="opt-card__text">Titres d’annonces, CTA, pages ville, formulaires courts vs longs.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-analyse', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">5</div>
            <h2 class="opt-card__title">Revue mensuelle</h2>
            <p class="opt-card__text">Synthèse des gains, décisions budget, backlog d’actions.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
    </div>
    <p class="opt-guide__footer">
        <a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">Voir le tableau de bord Analytics</a>
        · <a href="<?= htmlspecialchars($base . '&view=rapport-mensuel', ENT_QUOTES, 'UTF-8') ?>">Générer un rapport mensuel</a>
    </p>
</div>
<style>
.opt-guide{font-family:system-ui,-apple-system,sans-serif;color:#0f172a}
.opt-guide__hero{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;border-radius:16px;padding:2rem 2.25rem;margin-bottom:1.75rem}
.opt-guide__badge{display:inline-block;background:rgba(255,255,255,.2);padding:.35rem .75rem;border-radius:999px;font-size:.75rem;font-weight:600;margin-bottom:1rem}
.opt-guide__title{margin:0 0 .75rem;font-size:1.65rem;font-weight:800;letter-spacing:-.02em}
.opt-guide__lead{margin:0;opacity:.95;font-size:1rem;line-height:1.55;max-width:52ch}
.opt-guide__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem}
.opt-card{display:block;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.35rem 1.25rem;text-decoration:none;color:inherit;transition:border-color .2s,box-shadow .2s}
.opt-card:hover{border-color:#c7d2fe;box-shadow:0 10px 28px rgba(99,102,241,.12)}
.opt-card__num{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:.85rem;font-size:.95rem}
.opt-card__title{margin:0 0 .45rem;font-size:1.05rem;font-weight:700}
.opt-card__text{margin:0 0 .85rem;font-size:.88rem;color:#64748b;line-height:1.5}
.opt-card__link{font-size:.82rem;font-weight:600;color:#6366f1}
.opt-guide__footer{margin:1.5rem 0 0;font-size:.9rem;color:#64748b}
.opt-guide__footer a{color:#6366f1;font-weight:600;text-decoration:none}
.opt-guide__footer a:hover{text-decoration:underline}
</style>
