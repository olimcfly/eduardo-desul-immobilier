<?php
/**
 * Website Builder — Dashboard Hub
 * /admin/modules/builder/builder/index.php
 */

if (!isset($pdo) && !isset($db)) {
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch (PDOException $e) {
        echo '<div class="mod-flash mod-flash-error"><i class="fas fa-exclamation-circle"></i> '.$e->getMessage().'</div>';
        return;
    }
}
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db  = $pdo;

// ── Stats ──────────────────────────────────────────────────────
$stats = ['headers'=>0,'footers'=>0,'pages'=>0,'articles'=>0,'secteurs'=>0,'biens'=>0,'captures'=>0,'landings'=>0,'partenaires'=>0];
$countQ = [
    'headers'    => "SELECT COUNT(*) FROM headers",
    'footers'    => "SELECT COUNT(*) FROM footers",
    'pages'      => "SELECT COUNT(*) FROM pages WHERE type IN ('page','') OR type IS NULL",
    'articles'   => "SELECT COUNT(*) FROM articles",
    'secteurs'   => "SELECT COUNT(*) FROM secteurs",
    'biens'      => "SELECT COUNT(*) FROM properties",
    'captures'   => "SELECT COUNT(*) FROM captures",
    'landings'   => "SELECT COUNT(*) FROM pages WHERE type = 'landing'",
    'partenaires'=> "SELECT COUNT(*) FROM partenaires_locaux",
];
foreach ($countQ as $k => $sql) {
    try { $stats[$k] = (int)$pdo->query($sql)->fetchColumn(); } catch(Exception $e) {}
}

// ── Récents ────────────────────────────────────────────────────
$recent = [];
$rQ = [
    "SELECT id, name AS title, 'header'     AS content_type, status, updated_at FROM headers ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, name AS title, 'footer'     AS content_type, status, updated_at FROM footers ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, title,         'page'       AS content_type, status, updated_at FROM pages WHERE type IN ('page','') OR type IS NULL ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, titre AS title,'article'    AS content_type, status, updated_at FROM articles ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, nom AS title,  'secteur'    AS content_type, status, updated_at FROM secteurs ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, title,         'bien'       AS content_type, status, updated_at FROM properties ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, titre AS title,'capture'    AS content_type, status, updated_at FROM captures ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, title,         'landing'    AS content_type, status, updated_at FROM pages WHERE type = 'landing' ORDER BY updated_at DESC LIMIT 2",
    "SELECT id, nom AS title,  'partenaire' AS content_type, status, updated_at FROM partenaires_locaux ORDER BY updated_at DESC LIMIT 2",
];
foreach ($rQ as $sql) {
    try { $recent = array_merge($recent, $pdo->query($sql)->fetchAll()); } catch(Exception $e) {}
}
usort($recent, fn($a,$b) => strtotime($b['updated_at'] ?? '2000-01-01') - strtotime($a['updated_at'] ?? '2000-01-01'));
$recent = array_slice($recent, 0, 10);

// ── Types de contenu ───────────────────────────────────────────
// create  → URL pour créer (ouvre editor.php)
// edit    → URL de base pour éditer (ouvre editor.php avec &id=X)
// list    → URL pour la liste
$contentTypes = [
    'header' => [
        'icon'   => 'fa-window-maximize',
        'label'  => 'Header',
        'desc'   => 'Entête du site : logo, navigation, menu mobile.',
        'color'  => '#6366f1',
        'create' => '?page=headers-edit',
        'edit'   => '?page=headers-edit&id=',
        'list'   => '?page=headers',
        'count'  => $stats['headers'],
        'group'  => 'structure',
    ],
    'footer' => [
        'icon'   => 'fa-window-minimize',
        'label'  => 'Footer',
        'desc'   => 'Pied de page : contact, liens, réseaux sociaux.',
        'color'  => '#ec4899',
        'create' => '?page=footers',
        'edit'   => '?page=footers',
        'list'   => '?page=footers',
        'count'  => $stats['footers'],
        'group'  => 'structure',
    ],
    'page' => [
        'icon'   => 'fa-file-alt',
        'label'  => 'Page',
        'desc'   => 'Pages du site : accueil, services, à propos...',
        'color'  => '#0ea5e9',
        'create' => '?page=builder-editor&type=page&action=new',
        'edit'   => '?page=builder-editor&type=page&id=',
        'list'   => '?page=pages',
        'count'  => $stats['pages'],
        'group'  => 'contenu',
    ],
    'article' => [
        'icon'   => 'fa-newspaper',
        'label'  => 'Article',
        'desc'   => 'Articles de blog pour le SEO et les réseaux.',
        'color'  => '#f59e0b',
        'create' => '?page=builder-editor&type=article&action=new',
        'edit'   => '?page=builder-editor&type=article&id=',
        'list'   => '?page=articles',
        'count'  => $stats['articles'],
        'group'  => 'contenu',
    ],
    'secteur' => [
        'icon'   => 'fa-map-marker-alt',
        'label'  => 'Quartier',
        'desc'   => 'Pages quartier pour dominer votre secteur local.',
        'color'  => '#10b981',
        'create' => '?page=builder-editor&type=secteur&action=new',
        'edit'   => '?page=builder-editor&type=secteur&id=',
        'list'   => '?page=secteurs',
        'count'  => $stats['secteurs'],
        'group'  => 'contenu',
    ],
    'bien' => [
        'icon'   => 'fa-home',
        'label'  => 'Bien Immobilier',
        'desc'   => 'Annonces de biens en vente ou location.',
        'color'  => '#8b5cf6',
        'create' => '?page=properties-edit&action=new',
        'edit'   => '?page=properties-edit&id=',
        'list'   => '?page=properties',
        'count'  => $stats['biens'],
        'group'  => 'immobilier',
    ],
    'capture' => [
        'icon'   => 'fa-magnet',
        'label'  => 'Page de Capture',
        'desc'   => 'Pages pour capter des leads qualifiés.',
        'color'  => '#ef4444',
        'create' => '?page=captures&action=create',
        'edit'   => '?page=captures&action=edit&id=',
        'list'   => '?page=captures',
        'count'  => $stats['captures'],
        'group'  => 'marketing',
    ],
    'landing' => [
        'icon'   => 'fa-rocket',
        'label'  => 'Landing Page',
        'desc'   => "Pages d'atterrissage pour campagnes pub.",
        'color'  => '#f472b6',
        'create' => '?page=builder-editor&type=landing&action=new',
        'edit'   => '?page=builder-editor&type=landing&id=',
        'list'   => '?page=pages',
        'count'  => $stats['landings'],
        'group'  => 'marketing',
    ],
    'partenaire' => [
        'icon'   => 'fa-handshake',
        'label'  => 'Partenaire Local',
        'desc'   => 'Annuaire partenaires pour le guide local.',
        'color'  => '#0891b2',
        'create' => '?page=builder-editor&type=partenaire&action=new',
        'edit'   => '?page=builder-editor&type=partenaire&id=',
        'list'   => '?page=websites',
        'count'  => $stats['partenaires'],
        'group'  => 'immobilier',
    ],
];

$groups = [
    'structure'  => ['label' => 'Structure du site',   'icon' => 'fa-layer-group'],
    'contenu'    => ['label' => 'Contenu éditorial',   'icon' => 'fa-pen-fancy'],
    'immobilier' => ['label' => 'Immobilier & Local',  'icon' => 'fa-home'],
    'marketing'  => ['label' => 'Marketing & Capture', 'icon' => 'fa-bullseye'],
];

$statusMap = [
    'active'     => ['l' => 'Actif',       'c' => 'active'],
    'draft'      => ['l' => 'Brouillon',   'c' => 'draft'],
    'published'  => ['l' => 'Publié',      'c' => 'active'],
    'inactive'   => ['l' => 'Inactif',     'c' => 'inactive'],
    'archived'   => ['l' => 'Archivé',     'c' => 'inactive'],
    'available'  => ['l' => 'Disponible',  'c' => 'active'],
    'under_offer'=> ['l' => 'Sous offre',  'c' => 'warning'],
    'sold'       => ['l' => 'Vendu',       'c' => 'inactive'],
    'rented'     => ['l' => 'Loué',        'c' => 'inactive'],
];

$totalItems    = array_sum($stats);
$publishedCount = 0;
foreach (["SELECT COUNT(*) FROM pages WHERE status='published'","SELECT COUNT(*) FROM articles WHERE status='published'","SELECT COUNT(*) FROM headers WHERE status='active'"] as $sq) {
    try { $publishedCount += (int)$pdo->query($sq)->fetchColumn(); } catch(Exception $e) {}
}
?>

<style>
/* ── Cartes ── */
.bld-card {
    background: var(--surface);
    border: 2px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px 18px 14px;
    transition: all .25s;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.bld-card:hover {
    border-color: transparent;
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,.09);
}
.bld-card-bar {
    position: absolute; top: 0; left: 0; right: 0;
    height: 3px; opacity: 0; transition: opacity .25s;
}
.bld-card:hover .bld-card-bar { opacity: 1; }

.bld-card-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: #fff; margin-bottom: 11px;
    transition: transform .25s; flex-shrink: 0;
}
.bld-card:hover .bld-card-icon { transform: scale(1.08); }

.bld-card-label { font-size: .88rem; font-weight: 700; color: var(--text); margin-bottom: 3px; }
.bld-card-desc  { font-size: .73rem; color: var(--text-3); line-height: 1.5; margin-bottom: 12px; flex: 1; }
.bld-card-count { font-size: .67rem; color: var(--text-3); margin-bottom: 10px; }

/* ── Boutons Liste / Créer ── */
.bld-card-actions {
    display: flex;
    gap: 7px;
    margin-top: auto;
}
.bld-btn {
    flex: 1;
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 7px 10px;
    border-radius: 7px;
    font-size: .72rem; font-weight: 700;
    text-decoration: none;
    transition: all .2s;
    border: 1.5px solid transparent;
    white-space: nowrap;
}
.bld-btn-list {
    background: var(--surface-2);
    color: var(--text-2);
    border-color: var(--border);
}
.bld-btn-list:hover {
    background: var(--surface-3);
    color: var(--text);
}
.bld-btn-create {
    color: #fff;
}
.bld-btn-create:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,.18);
}

/* ── Récents ── */
.bld-recent-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 8px;
    border-bottom: 1px solid var(--surface-2);
    text-decoration: none;
    border-radius: var(--radius);
    margin: 0 -8px;
    transition: background .15s;
}
.bld-recent-item:last-child { border: 0; }
.bld-recent-item:hover { background: var(--surface-2); }
.bld-recent-icon {
    width: 30px; height: 30px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: #fff; flex-shrink: 0;
}
.bld-recent-name {
    font-size: .78rem; font-weight: 600; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.bld-recent-meta { font-size: .65rem; color: var(--text-3); margin-top: 1px; }

/* ── Accès rapides ── */
.bld-quick-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.bld-quick-link {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 11px; border-radius: var(--radius);
    text-decoration: none; color: var(--text-2);
    font-size: .74rem; font-weight: 500;
    transition: all .2s; border: 1px solid var(--surface-2);
}
.bld-quick-link:hover { background: var(--surface-2); border-color: var(--border); color: var(--text); }
.bld-quick-link i { font-size: .76rem; width: 15px; text-align: center; color: var(--text-3); }
.bld-quick-link:hover i { color: var(--accent); }

/* ── Layout bas ── */
.bld-bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 8px; }
@media(max-width: 900px) { .bld-bottom { grid-template-columns: 1fr; } }
@media(max-width: 768px) { .bld-quick-grid { grid-template-columns: 1fr; } }
</style>

<!-- Hero -->
<div class="mod-hero">
    <div class="mod-hero-content">
        <h1><i class="fas fa-bolt"></i> Website Builder</h1>
        <p>Choisissez un type de contenu. Chaque carte donne accès à la liste et à l'éditeur dédié.</p>
    </div>
    <div class="mod-stats">
        <div class="mod-stat"><div class="mod-stat-value"><?= $totalItems ?></div><div class="mod-stat-label">Contenus</div></div>
        <div class="mod-stat"><div class="mod-stat-value"><?= $publishedCount ?></div><div class="mod-stat-label">Publiés</div></div>
        <div class="mod-stat"><div class="mod-stat-value"><?= count($recent) ?></div><div class="mod-stat-label">Récents</div></div>
    </div>
</div>

<!-- Groupes de cartes -->
<?php foreach ($groups as $gk => $gi):
    $gTypes = array_filter($contentTypes, fn($t) => ($t['group'] ?? '') === $gk);
    if (empty($gTypes)) continue;
?>
<div style="margin-bottom: 28px;">
    <div style="display:flex;align-items:center;gap:7px;margin-bottom:12px;">
        <i class="fas <?= $gi['icon'] ?>" style="color:var(--text-3);font-size:.73rem;"></i>
        <strong style="font-size:.76rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;">
            <?= $gi['label'] ?>
        </strong>
    </div>
    <div class="mod-grid mod-grid-4">
        <?php foreach ($gTypes as $ck => $ct): ?>
        <div class="bld-card">
            <div class="bld-card-bar" style="background:<?= $ct['color'] ?>"></div>
            <div class="bld-card-icon" style="background:<?= $ct['color'] ?>">
                <i class="fas <?= $ct['icon'] ?>"></i>
            </div>
            <div class="bld-card-label"><?= htmlspecialchars($ct['label']) ?></div>
            <div class="bld-card-desc"><?= htmlspecialchars($ct['desc']) ?></div>
            <div class="bld-card-count">
                <?= $ct['count'] ?> existant<?= $ct['count'] > 1 ? 's' : '' ?>
            </div>
            <div class="bld-card-actions">
                <!-- Bouton Liste -->
                <a href="<?= htmlspecialchars($ct['list']) ?>" class="bld-btn bld-btn-list">
                    <i class="fas fa-list" style="font-size:.65rem;"></i> Liste
                </a>
                <!-- Bouton Créer -->
                <a href="<?= htmlspecialchars($ct['create']) ?>"
                   class="bld-btn bld-btn-create"
                   style="background:<?= $ct['color'] ?>;">
                    <i class="fas fa-plus" style="font-size:.65rem;"></i> Créer
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Bas de page : Récents + Accès rapides -->
<div class="bld-bottom">

    <!-- Dernières modifications -->
    <div class="mod-card">
        <div class="mod-card-header">
            <h3><i class="fas fa-clock" style="color:var(--accent);margin-right:6px;"></i> Dernières modifications</h3>
        </div>
        <div class="mod-card-body">
            <?php if (empty($recent)): ?>
            <div class="mod-empty" style="padding:30px;">
                <i class="fas fa-inbox"></i>
                <p>Aucune création pour l'instant.</p>
            </div>
            <?php else: foreach ($recent as $item):
                $ct  = $item['content_type'] ?? 'page';
                $cfg = $contentTypes[$ct] ?? $contentTypes['page'];
                $sm  = $statusMap[$item['status'] ?? 'draft'] ?? $statusMap['draft'];

                // URL d'édition correcte selon le type
                switch ($ct) {
                    case 'header':
                        $editUrl = '?page=headers-edit&id=' . $item['id'];
                        break;
                    case 'footer':
                        $editUrl = '?page=footers';
                        break;
                    case 'capture':
                        $editUrl = '?page=captures&action=edit&id=' . $item['id'];
                        break;
                    case 'bien':
                        $editUrl = '?page=properties-edit&id=' . $item['id'];
                        break;
                    default:
                        // page, article, secteur, landing, partenaire → builder-editor
                        $editUrl = '?page=builder-editor&type=' . urlencode($ct) . '&id=' . $item['id'];
                        break;
                }

                $date = !empty($item['updated_at']) ? date('d/m H:i', strtotime($item['updated_at'])) : '—';
            ?>
            <a href="<?= htmlspecialchars($editUrl) ?>" class="bld-recent-item">
                <div class="bld-recent-icon" style="background:<?= $cfg['color'] ?>;">
                    <i class="fas <?= $cfg['icon'] ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="bld-recent-name"><?= htmlspecialchars($item['title'] ?? 'Sans titre') ?></div>
                    <div class="bld-recent-meta">
                        <?= htmlspecialchars($cfg['label']) ?> · <?= $date ?>
                        · <i class="fas fa-pencil" style="font-size:.6rem;opacity:.6;"></i> Éditer
                    </div>
                </div>
                <span class="mod-badge mod-badge-<?= $sm['c'] ?>"><?= $sm['l'] ?></span>
            </a>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Accès rapides -->
    <div class="mod-card">
        <div class="mod-card-header">
            <h3><i class="fas fa-link" style="color:var(--accent);margin-right:6px;"></i> Accès rapides</h3>
        </div>
        <div class="mod-card-body">
            <div class="bld-quick-grid">
                <a href="?page=headers"    class="bld-quick-link"><i class="fas fa-window-maximize"></i> Headers</a>
                <a href="?page=footers"    class="bld-quick-link"><i class="fas fa-window-minimize"></i> Footers</a>
                <a href="?page=pages"      class="bld-quick-link"><i class="fas fa-file-alt"></i> Pages</a>
                <a href="?page=articles"   class="bld-quick-link"><i class="fas fa-newspaper"></i> Articles</a>
                <a href="?page=secteurs"   class="bld-quick-link"><i class="fas fa-map-marker-alt"></i> Quartiers</a>
                <a href="?page=properties" class="bld-quick-link"><i class="fas fa-home"></i> Biens</a>
                <a href="?page=captures"   class="bld-quick-link"><i class="fas fa-magnet"></i> Captures</a>
                <a href="?page=templates"  class="bld-quick-link"><i class="fas fa-palette"></i> Templates</a>
                <a href="?page=menus"      class="bld-quick-link"><i class="fas fa-bars"></i> Menus</a>
                <a href="?page=design"     class="bld-quick-link"><i class="fas fa-swatchbook"></i> Charte</a>
            </div>
        </div>
    </div>

</div>