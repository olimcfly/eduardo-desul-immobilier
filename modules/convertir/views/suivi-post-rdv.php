<?php

declare(strict_types=1);

$base = '/admin?module=convertir';
$current = 'suivi-post-rdv';
require __DIR__ . '/_subnav.php';
?>
<div class="conv-followup">
    <div class="page-header" style="margin-bottom:18px;">
        <h1><i class="fas fa-reply page-icon"></i> Convertir <span class="page-title-accent">Suivi post-RDV</span></h1>
        <p>Séquence courte de relance pour transformer un rendez-vous en décision.</p>
    </div>

    <section class="conv-followup__panel">
        <h2>Cadence recommandée</h2>
        <ol>
            <li><strong>Sous 2h</strong> : message récapitulatif + bénéfice principal évoqué en rendez-vous.</li>
            <li><strong>J+2</strong> : relance orientée décision avec un choix simple ("on avance" / "on ajuste").</li>
            <li><strong>J+7</strong> : dernier contact courtois + date de validité de votre proposition.</li>
        </ol>
    </section>

    <section class="conv-followup__panel">
        <h2>Modèle de message (J+2)</h2>
        <div class="conv-followup__template">Bonjour {{prenom}}, suite à notre rendez-vous, je vous propose de valider le plan de commercialisation cette semaine afin de publier dans les meilleures conditions. Préférez-vous jeudi 14h ou vendredi 10h pour finaliser ?</div>
    </section>

    <p class="conv-followup__links">
        <a href="<?= htmlspecialchars($base . '&action=rdv', ENT_QUOTES, 'UTF-8') ?>">Voir les RDV en cours</a>
        · <a href="<?= htmlspecialchars($base . '&action=parcours', ENT_QUOTES, 'UTF-8') ?>">Retour au parcours</a>
    </p>
</div>
<style>
.conv-followup{font-family:system-ui,-apple-system,sans-serif;color:#0f2237}
.conv-followup__panel{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1rem 1.1rem;margin-bottom:1rem;max-width:58rem}
.conv-followup__panel h2{margin:0 0 .6rem;font-size:1.05rem}
.conv-followup__panel ol{margin:0;padding-left:1.2rem}
.conv-followup__panel li{margin:.45rem 0}
.conv-followup__template{padding:.75rem .85rem;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;font-size:.92rem;line-height:1.6}
.conv-followup__links{font-size:.9rem;color:#64748b}
.conv-followup__links a{color:#7c5d1d;font-weight:600;text-decoration:none}
.conv-followup__links a:hover{text-decoration:underline}
</style>
