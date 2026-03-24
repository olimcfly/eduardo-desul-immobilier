<?php
/**
 * ══════════════════════════════════════════════════════════════
 * MODULE STRATÉGIE DIGITALE — Index
 * /admin/modules/strategy/strategy/index.php
 * ÉCOSYSTÈME IMMO LOCAL+
 *
 * Accès : dashboard.php?page=strategy
 * ══════════════════════════════════════════════════════════════
 */

defined('ADMIN_ROUTER') or define('ADMIN_ROUTER', true);

ob_start();

$rootPath = '/home/mahe6420/public_html';
if (!defined('DB_HOST'))      require_once $rootPath . '/config/config.php';
if (!class_exists('Database')) require_once $rootPath . '/includes/classes/Database.php';

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    ob_end_clean();
    die('<div style="padding:20px;color:#dc2626;font-family:monospace">❌ DB: ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// ─── Stats (try/catch — tables peuvent ne pas exister) ────────
$stats = ['personas_actifs' => 0, 'campagnes_actives' => 0, 'leads_mois' => 0, 'canaux' => 4];

try {
    $r = $db->query("SELECT COUNT(*) FROM neuropersona_config WHERE actif = 1")->fetchColumn();
    $stats['personas_actifs'] = (int)$r;
} catch (Exception $e) {}

try {
    $r = $db->query("SELECT COUNT(*) FROM neuropersona_campagnes WHERE statut = 'active'")->fetchColumn();
    $stats['campagnes_actives'] = (int)$r;
} catch (Exception $e) {}

try {
    $r = $db->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn();
    $stats['leads_mois'] = (int)$r;
} catch (Exception $e) {}

// ─── Modules définis ──────────────────────────────────────────
$modules = [
    [
        'slug'        => 'neuropersona',
        'title'       => 'NeuroPersona',
        'icon'        => '🧠',
        'icon_color'  => 'rgba(99,102,241,.12)',
        'status'      => 'active',
        'featured'    => true,
        'description' => 'Cartographiez vos personas acheteurs/vendeurs grâce aux neurosciences. Identifiez les motivations profondes et générez des campagnes ciblées.',
        'tags'        => ['10 Personas', 'Campagnes IA', 'Multi-canal'],
        'links'       => [['label' => 'Accéder →', 'url' => '?page=neuropersona', 'primary' => true]],
    ],
    [
        'slug'        => 'launchpad',
        'title'       => 'Launchpad',
        'icon'        => '🚀',
        'icon_color'  => 'rgba(16,185,129,.1)',
        'status'      => 'active',
        'featured'    => false,
        'description' => 'Parcours guidé en 5 étapes pour lancer votre activité : positionnement, offre, canaux, structure et passage à l\'échelle.',
        'tags'        => ['Positionnement', 'Offre', 'Canaux'],
        'links'       => [['label' => 'Accéder →', 'url' => '?page=launchpad', 'primary' => true]],
    ],
    [
        'slug'        => 'local-seo',
        'title'       => 'Traffic & SEO Local',
        'icon'        => '📍',
        'icon_color'  => 'rgba(245,158,11,.1)',
        'status'      => 'active',
        'featured'    => false,
        'description' => 'Optimisation Google My Business, SEO local et stratégie multi-canal pour attirer des prospects qualifiés sur votre zone géographique.',
        'tags'        => ['GMB', 'SEO Local', 'Citations'],
        'links'       => [
            ['label' => 'SEO →',    'url' => '?page=local-seo',  'primary' => false],
            ['label' => 'Social →', 'url' => '?page=reseaux-sociaux', 'primary' => false],
        ],
    ],
    [
        'slug'        => 'pages-capture',
        'title'       => 'Offres & Landing Pages',
        'icon'        => '🎁',
        'icon_color'  => 'rgba(6,182,212,.1)',
        'status'      => 'active',
        'featured'    => false,
        'description' => 'Créez des pages de capture et des offres irrésistibles pour convertir vos visiteurs en leads qualifiés.',
        'tags'        => ['Pages Capture', 'Lead Magnets', 'Conversion'],
        'links'       => [
            ['label' => 'Captures →', 'url' => '?page=pages-capture', 'primary' => false],
            ['label' => 'Builder →',  'url' => '?page=builder',       'primary' => false],
        ],
    ],
    [
        'slug'        => 'sequences',
        'title'       => 'Email Marketing',
        'icon'        => '📧',
        'icon_color'  => 'rgba(139,92,246,.1)',
        'status'      => 'active',
        'featured'    => false,
        'description' => 'Séquences email automatisées pour le nurturing de vos leads et relances intelligentes basées sur le comportement.',
        'tags'        => ['Séquences', 'Automation', 'Templates'],
        'links'       => [['label' => 'Accéder →', 'url' => '?page=sequences', 'primary' => false]],
    ],
    [
        'slug'        => 'analytics',
        'title'       => 'KPI & Métriques',
        'icon'        => '📊',
        'icon_color'  => 'rgba(236,72,153,.1)',
        'status'      => 'soon',
        'featured'    => false,
        'description' => 'Tableau de bord unifié pour suivre vos KPI en temps réel : trafic, conversions, ROI des campagnes et performance par canal.',
        'tags'        => ['Analytics', 'ROI', 'Rapports'],
        'links'       => [],
    ],
];

$statusLabels = [
    'active' => ['●&nbsp;Actif',    'background:#dcfce7;color:#16a34a'],
    'beta'   => ['Beta',            'background:#fef3c7;color:#b45309'],
    'soon'   => ['Bientôt',         'background:#f3f4f6;color:#6b7280'],
    'error'  => ['Erreur',          'background:#fee2e2;color:#dc2626'],
];

ob_end_clean();
?>

<style>
/* ════════════════════════════════════════════════════════════════
   MODULE STRATÉGIE — aligné sur dashboard IMMO LOCAL+
════════════════════════════════════════════════════════════════ */

/* Banner */
.strat-banner {
    background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
    border-radius: var(--radius-lg, 12px);
    padding: 28px 32px;
    margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between;
    position: relative; overflow: hidden; flex-wrap: wrap; gap: 16px;
}
.strat-banner::before {
    content: '';
    position: absolute; top: -50%; right: -5%;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,.08), transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.strat-banner::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, rgba(255,255,255,.3), transparent 60%);
}
.strat-banner-left { position: relative; z-index: 1; }
.strat-banner-left h2 {
    font-size: 1.4rem; font-weight: 800; color: #fff; margin: 0 0 5px;
    display: flex; align-items: center; gap: 10px;
}
.strat-banner-left p { color: rgba(255,255,255,.7); font-size: .85rem; margin: 0; }

/* Stats row */
.strat-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}
.strat-stat {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius-lg, 12px);
    padding: 16px;
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: all .15s;
}
.strat-stat:hover { border-color: #6366f1; box-shadow: 0 4px 12px rgba(99,102,241,.08); }
.strat-stat .num {
    font-size: 2rem; font-weight: 900; line-height: 1;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    font-variant-numeric: tabular-nums;
}
.strat-stat .lbl { font-size: .7rem; color: var(--text-3, #9ca3af); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; margin-top: 4px; }

/* Grid modules */
.strat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}

/* Carte module */
.strat-card {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius-lg, 12px);
    padding: 20px;
    transition: all .2s;
    position: relative; overflow: hidden;
    display: flex; flex-direction: column; gap: 10px;
}
.strat-card:hover {
    border-color: #6366f1;
    box-shadow: 0 6px 20px rgba(99,102,241,.12);
    transform: translateY(-2px);
}
.strat-card.featured {
    border: 2px solid #6366f1;
    background: linear-gradient(135deg, rgba(99,102,241,.03), rgba(124,58,237,.03));
}
.strat-card.featured::after {
    content: '⭐ Recommandé';
    position: absolute; top: 14px; right: -28px;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff; padding: 4px 40px;
    font-size: .6rem; font-weight: 700; letter-spacing: .05em;
    transform: rotate(45deg);
}
.strat-card.disabled { opacity: .55; pointer-events: none; }

.strat-card-icon {
    width: 48px; height: 48px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; flex-shrink: 0;
}
.strat-card-hd {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.strat-card-title { font-size: .95rem; font-weight: 700; color: var(--text, #111827); }
.strat-status {
    display: inline-flex; align-items: center;
    padding: 2px 8px; border-radius: 20px;
    font-size: .62rem; font-weight: 600;
}
.strat-card-desc { font-size: .82rem; color: var(--text-2, #6b7280); line-height: 1.6; flex: 1; }

.strat-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.strat-tag {
    padding: 2px 8px; background: var(--surface-2, #f9fafb);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 4px; font-size: .68rem; color: var(--text-3, #9ca3af);
    font-weight: 600;
}

.strat-card-btns { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
.strat-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 16px; border-radius: var(--radius, 8px);
    font-size: .78rem; font-weight: 700; cursor: pointer;
    text-decoration: none; transition: all .15s; border: none;
}
.strat-btn.primary {
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff; box-shadow: 0 2px 6px rgba(99,102,241,.25);
}
.strat-btn.primary:hover { box-shadow: 0 4px 12px rgba(99,102,241,.35); transform: scale(1.02); color: #fff; }
.strat-btn.secondary {
    background: var(--surface, #fff); color: #6366f1;
    border: 1px solid #6366f1;
}
.strat-btn.secondary:hover { background: rgba(99,102,241,.05); color: #6366f1; }
.strat-btn:disabled { opacity: .5; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

/* Conseil box */
.strat-conseil {
    background: linear-gradient(135deg, rgba(99,102,241,.06), rgba(124,58,237,.06));
    border: 1px solid rgba(99,102,241,.2);
    border-radius: var(--radius-lg, 12px);
    padding: 22px 24px;
}
.strat-conseil h3 {
    font-size: .9rem; font-weight: 700; color: var(--text, #111827);
    margin-bottom: 8px; display: flex; align-items: center; gap: 7px;
}
.strat-conseil p { font-size: .82rem; color: var(--text-2, #6b7280); line-height: 1.6; margin: 0 0 14px; }
.strat-steps { display: flex; flex-wrap: wrap; gap: 8px; }
.strat-step {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px; background: var(--surface, #fff);
    border-radius: var(--radius, 8px); font-size: .8rem;
    color: var(--text, #374151); border: 1px solid var(--border, #e5e7eb);
}
.strat-step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: .68rem; font-weight: 800; flex-shrink: 0;
}

@media (max-width: 860px) {
    .strat-stats { grid-template-columns: repeat(2, 1fr); }
    .strat-grid  { grid-template-columns: 1fr; }
    .strat-steps { flex-direction: column; }
}
</style>

<!-- Banner -->
<div class="strat-banner anim">
    <div class="strat-banner-left">
        <h2>🎯 Stratégie Digitale</h2>
        <p>Méthodologie complète pour devenir le leader de votre zone : Persona → Offre → Canaux → Conversion</p>
    </div>
    <a href="?page=launchpad" class="strat-btn primary" style="position:relative;z-index:1">
        <i class="fas fa-rocket"></i> Lancer le Launchpad
    </a>
</div>

<!-- Stats -->
<div class="strat-stats anim">
    <div class="strat-stat">
        <div class="num"><?= $stats['personas_actifs'] ?></div>
        <div class="lbl">Personas actifs</div>
    </div>
    <div class="strat-stat">
        <div class="num"><?= $stats['campagnes_actives'] ?></div>
        <div class="lbl">Campagnes actives</div>
    </div>
    <div class="strat-stat">
        <div class="num"><?= $stats['leads_mois'] ?></div>
        <div class="lbl">Leads ce mois</div>
    </div>
    <div class="strat-stat">
        <div class="num"><?= $stats['canaux'] ?></div>
        <div class="lbl">Canaux disponibles</div>
    </div>
</div>

<!-- Modules -->
<div class="strat-grid anim">
<?php foreach ($modules as $mod):
    $st  = $mod['status'];
    [$stLabel, $stStyle] = $statusLabels[$st] ?? ['', ''];
    $disabled = $st === 'soon' ? ' disabled' : '';
?>
<div class="strat-card<?= $mod['featured'] ? ' featured' : '' ?><?= $disabled ?>">
    <div class="strat-card-icon" style="background:<?= $mod['icon_color'] ?>"><?= $mod['icon'] ?></div>
    <div class="strat-card-hd">
        <span class="strat-card-title"><?= htmlspecialchars($mod['title']) ?></span>
        <?php if ($stLabel): ?>
        <span class="strat-status" style="<?= $stStyle ?>"><?= $stLabel ?></span>
        <?php endif; ?>
    </div>
    <div class="strat-card-desc"><?= htmlspecialchars($mod['description']) ?></div>
    <div class="strat-tags">
        <?php foreach ($mod['tags'] as $tag): ?>
        <span class="strat-tag"><?= htmlspecialchars($tag) ?></span>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($mod['links'])): ?>
    <div class="strat-card-btns">
        <?php foreach ($mod['links'] as $link): ?>
        <a href="<?= htmlspecialchars($link['url']) ?>" class="strat-btn <?= $link['primary'] ? 'primary' : 'secondary' ?>">
            <?= htmlspecialchars($link['label']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="strat-card-btns">
        <button class="strat-btn secondary" disabled>Bientôt disponible</button>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<!-- Conseil -->
<div class="strat-conseil anim">
    <h3>💡 Méthodologie recommandée</h3>
    <p>Suivez cette approche étape par étape pour maximiser vos résultats et devenir le leader de votre zone géographique.</p>
    <div class="strat-steps">
        <?php
        $steps = [
            'Définir vos Personas prioritaires',
            'Crafter vos messages (Méthode MERE)',
            'Activer vos canaux de trafic',
            'Capturer & nurturer vos leads',
            'Analyser vos KPI régulièrement',
        ];
        foreach ($steps as $i => $step): ?>
        <div class="strat-step">
            <span class="strat-step-num"><?= $i + 1 ?></span>
            <?= htmlspecialchars($step) ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>