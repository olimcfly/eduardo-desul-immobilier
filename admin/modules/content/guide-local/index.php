<?php
/**
 * MODULE GUIDE LOCAL — Partenaires & Points d'intérêt
 * /admin/modules/content/guide-local/index.php
 * v1.0 — Design unifié Articles/Secteurs
 *
 * Stratégie SEO : maillage de partenaires locaux par secteur/ville
 * pour booster le référencement local et enrichir l'expérience acheteur
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

define('ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))));
require_once ROOT_PATH . '/includes/classes/Database.php';

$db = Database::getInstance();

// ─── CATÉGORIES PARTENAIRES ───
// Chaque catégorie = intention d'achat ou de vie locale
$partnerCategories = [
    'ecole'         => ['icon' => 'fa-school',         'label' => 'Écoles & Crèches',     'color' => '#3b82f6', 'bg' => '#eff6ff'],
    'sante'         => ['icon' => 'fa-heartbeat',      'label' => 'Santé & Médecins',      'color' => '#ef4444', 'bg' => '#fef2f2'],
    'transport'     => ['icon' => 'fa-bus',            'label' => 'Transports',            'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
    'commerce'      => ['icon' => 'fa-shopping-bag',   'label' => 'Commerces & Marchés',   'color' => '#f59e0b', 'bg' => '#fffbeb'],
    'restaurant'    => ['icon' => 'fa-utensils',       'label' => 'Restaurants & Cafés',   'color' => '#f97316', 'bg' => '#fff7ed'],
    'sport'         => ['icon' => 'fa-dumbbell',       'label' => 'Sport & Loisirs',       'color' => '#10b981', 'bg' => '#ecfdf5'],
    'culture'       => ['icon' => 'fa-landmark',       'label' => 'Culture & Patrimoine',  'color' => '#6366f1', 'bg' => '#eef2ff'],
    'nature'        => ['icon' => 'fa-tree',           'label' => 'Parcs & Nature',        'color' => '#22c55e', 'bg' => '#f0fdf4'],
    'services'      => ['icon' => 'fa-concierge-bell', 'label' => 'Services de proximité', 'color' => '#0ea5e9', 'bg' => '#f0f9ff'],
    'securite'      => ['icon' => 'fa-shield-alt',     'label' => 'Sécurité & Mairie',     'color' => '#64748b', 'bg' => '#f8fafc'],
    'immobilier'    => ['icon' => 'fa-home',           'label' => 'Acteurs immobiliers',   'color' => '#ec4899', 'bg' => '#fdf2f8'],
    'autre'         => ['icon' => 'fa-map-pin',        'label' => 'Autres points',         'color' => '#94a3b8', 'bg' => '#f8fafc'],
];

// ─── ACTIONS POST ───
$postAction = $_POST['action'] ?? '';
$itemId     = intval($_POST['id'] ?? 0);
$error      = null;

// Vérifier table
$tableExists = false;
try {
    $db->query("SELECT 1 FROM guide_local LIMIT 1");
    $tableExists = true;
} catch (PDOException $e) {
    // Table n'existe pas encore
}

if ($tableExists && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($postAction === 'delete' && $itemId > 0) {
        try {
            $db->prepare("DELETE FROM guide_local WHERE id = ?")->execute([$itemId]);
            header("Location: /admin/dashboard.php?page=guide-local&msg=deleted"); exit;
        } catch (PDOException $e) { $error = $e->getMessage(); }
    }

    if ($postAction === 'toggle_status' && $itemId > 0) {
        try {
            $db->prepare("UPDATE guide_local SET status = IF(status='published','draft','published') WHERE id = ?")->execute([$itemId]);
            header("Location: /admin/dashboard.php?page=guide-local&msg=updated"); exit;
        } catch (PDOException $e) { $error = $e->getMessage(); }
    }

    if ($postAction === 'toggle_featured' && $itemId > 0) {
        try {
            $db->prepare("UPDATE guide_local SET is_featured = IF(is_featured=1,0,1) WHERE id = ?")->execute([$itemId]);
            header("Location: /admin/dashboard.php?page=guide-local&msg=updated"); exit;
        } catch (PDOException $e) { $error = $e->getMessage(); }
    }
}

// ─── FILTRES ───
$filterStatus   = $_GET['status']    ?? 'all';
$filterCat      = $_GET['categorie'] ?? 'all';
$filterVille    = $_GET['ville']     ?? 'all';
$filterSecteur  = $_GET['secteur']   ?? 'all';
$filterAudience = $_GET['audience']  ?? 'all'; // acheteur / habitant / tous
$searchQuery    = trim($_GET['q']    ?? '');
$currentPage    = max(1, (int)($_GET['p'] ?? 1));
$perPage        = 30;
$offset         = ($currentPage - 1) * $perPage;

// ─── WHERE ───
$where = []; $params = [];
if ($filterStatus   !== 'all') { $where[] = "g.status = ?";    $params[] = $filterStatus; }
if ($filterCat      !== 'all') { $where[] = "g.categorie = ?"; $params[] = $filterCat; }
if ($filterVille    !== 'all') { $where[] = "g.ville = ?";     $params[] = $filterVille; }
if ($filterSecteur  !== 'all') { $where[] = "g.secteur_id = ?";$params[] = $filterSecteur; }
if ($filterAudience !== 'all') { $where[] = "(g.audience = ? OR g.audience = 'tous')"; $params[] = $filterAudience; }
if ($searchQuery !== '') {
    $where[] = "(g.nom LIKE ? OR g.adresse LIKE ? OR g.description LIKE ? OR g.ville LIKE ?)";
    $t = "%$searchQuery%";
    $params = array_merge($params, [$t, $t, $t, $t]);
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ─── DATA ───
$stats = [
    'total' => 0, 'published' => 0, 'draft' => 0,
    'featured' => 0, 'with_gmb' => 0,
    'acheteurs' => 0, 'habitants' => 0,
];
$partners      = [];
$totalFiltered = 0;
$totalPages    = 1;
$villes        = [];
$secteurs      = [];
$catCounts     = [];

if ($tableExists) {
    try {
        // Stats globales
        $s = $db->query("SELECT
            COUNT(*) as total,
            SUM(status='published') as published,
            SUM(status='draft') as draft,
            SUM(is_featured=1) as featured,
            SUM(gmb_url IS NOT NULL AND gmb_url != '') as with_gmb,
            SUM(audience='acheteur') as acheteurs,
            SUM(audience='habitant') as habitants
            FROM guide_local")->fetch(PDO::FETCH_ASSOC);
        $stats = array_map('intval', $s);

        // Villes distinctes
        $villes = $db->query("SELECT DISTINCT ville FROM guide_local WHERE ville IS NOT NULL AND ville != '' ORDER BY ville")->fetchAll(PDO::FETCH_COLUMN);

        // Secteurs (jointure)
        $secteurs = $db->query("SELECT id, nom FROM secteurs ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

        // Comptes par catégorie
        $catRows = $db->query("SELECT categorie, COUNT(*) as cnt FROM guide_local GROUP BY categorie")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($catRows as $r) $catCounts[$r['categorie']] = (int)$r['cnt'];

        // Total filtré
        $stmtCount = $db->prepare("SELECT COUNT(*) FROM guide_local g $whereSQL");
        $stmtCount->execute($params);
        $totalFiltered = (int)$stmtCount->fetchColumn();
        $totalPages    = max(1, ceil($totalFiltered / $perPage));

        // Listing
        $stmtList = $db->prepare("
            SELECT g.*, s.nom as secteur_nom
            FROM guide_local g
            LEFT JOIN secteurs s ON s.id = g.secteur_id
            $whereSQL
            ORDER BY g.is_featured DESC, g.nom ASC
            LIMIT $perPage OFFSET $offset
        ");
        $stmtList->execute($params);
        $partners = $stmtList->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) { $error = $e->getMessage(); }
} else {
    // Données démo pour prévisualisation
    $partners = [];
}

$flash = $_GET['msg'] ?? '';
$flashMessages = [
    'deleted'  => ['type'=>'success','text'=>'Partenaire supprimé'],
    'updated'  => ['type'=>'success','text'=>'Mis à jour avec succès'],
    'created'  => ['type'=>'success','text'=>'Partenaire ajouté au guide'],
    'saved'    => ['type'=>'success','text'=>'Guide local sauvegardé'],
];
?>

<style>
/* ═══════════════════════════════════════════════════════════
   MODULE GUIDE LOCAL v1.0 — Design unifié Articles/Secteurs
   ═══════════════════════════════════════════════════════════ */
.glm-wrap { font-family: var(--font); }

/* ═══ BANNER ═══ */
.glm-banner {
    background: var(--surface);
    border-radius: var(--radius-xl);
    padding: 26px 30px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--border);
    position: relative;
    overflow: hidden;
}
.glm-banner::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #10b981, #3b82f6, #f59e0b, #ef4444);
    opacity: .85;
}
.glm-banner::after {
    content: '';
    position: absolute; top: -40%; right: -5%;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(16,185,129,.05), transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.glm-banner-left { position: relative; z-index: 1; }
.glm-banner-left h2 {
    font-family: var(--font-display);
    font-size: 1.35rem; font-weight: 700;
    color: var(--text); margin: 0 0 4px;
    display: flex; align-items: center; gap: 10px;
    letter-spacing: -.02em;
}
.glm-banner-left h2 i { font-size: 16px; color: #10b981; }
.glm-banner-left p { color: var(--text-2); font-size: 0.85rem; margin: 0 0 8px; }
.glm-banner-seo-hint {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; background: rgba(16,185,129,.08);
    border: 1px solid rgba(16,185,129,.2); border-radius: 20px;
    font-size: 0.72rem; font-weight: 600; color: #059669;
}
.glm-banner-seo-hint i { font-size: 0.65rem; }

.glm-stats { display: flex; gap: 8px; position: relative; z-index: 1; flex-wrap: wrap; }
.glm-stat {
    text-align: center; padding: 10px 16px;
    background: var(--surface-2); border-radius: var(--radius-lg);
    border: 1px solid var(--border); min-width: 72px;
    transition: all .2s var(--ease);
}
.glm-stat:hover { border-color: var(--border-h); box-shadow: var(--shadow-xs); }
.glm-stat .num {
    font-family: var(--font-display); font-size: 1.45rem;
    font-weight: 800; line-height: 1; color: var(--text); letter-spacing: -.03em;
}
.glm-stat .num.blue   { color: var(--accent); }
.glm-stat .num.green  { color: var(--green, #059669); }
.glm-stat .num.amber  { color: #f59e0b; }
.glm-stat .num.violet { color: #7c3aed; }
.glm-stat .num.teal   { color: #0d9488; }
.glm-stat .num.rose   { color: #e11d48; }
.glm-stat .lbl {
    font-size: 0.58rem; color: var(--text-3);
    text-transform: uppercase; letter-spacing: .06em;
    font-weight: 600; margin-top: 3px;
}

/* ═══ CAT PILLS (rapides) ═══ */
.glm-cat-pills {
    display: flex; gap: 8px; flex-wrap: wrap;
    margin-bottom: 16px;
}
.glm-cat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 0.72rem; font-weight: 600; cursor: pointer;
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text-2); text-decoration: none; transition: all .15s var(--ease);
    position: relative;
}
.glm-cat-pill:hover { border-color: var(--border-h); color: var(--text); box-shadow: var(--shadow-xs); }
.glm-cat-pill.active { color: #fff; border-color: transparent; }
.glm-cat-pill .cnt {
    font-size: 0.6rem; padding: 1px 6px; border-radius: 10px;
    background: rgba(255,255,255,.25); font-weight: 700;
}
.glm-cat-pill:not(.active) .cnt { background: var(--surface-2); color: var(--text-3); }

/* ═══ TOOLBAR ═══ */
.glm-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px; flex-wrap: wrap; gap: 10px;
}
.glm-filters {
    display: flex; gap: 3px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 3px; flex-wrap: wrap;
}
.glm-fbtn {
    padding: 7px 15px; border: none; background: transparent;
    color: var(--text-2); font-size: 0.78rem; font-weight: 600;
    border-radius: 6px; cursor: pointer; transition: all .15s var(--ease);
    font-family: var(--font); display: flex; align-items: center; gap: 5px; text-decoration: none;
}
.glm-fbtn:hover { color: var(--text); background: var(--surface-2); }
.glm-fbtn.active { background: #10b981; color: #fff; box-shadow: 0 1px 4px rgba(16,185,129,.25); }
.glm-fbtn .badge {
    font-size: 0.68rem; padding: 1px 7px; border-radius: 10px;
    background: var(--surface-2); font-weight: 700; color: var(--text-3);
}
.glm-fbtn.active .badge { background: rgba(255,255,255,.22); color: #fff; }

/* Audience toggle */
.glm-audience {
    display: flex; gap: 3px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 3px;
}
.glm-aud-btn {
    padding: 6px 13px; border: none; background: transparent;
    color: var(--text-2); font-size: 0.75rem; font-weight: 600;
    border-radius: 5px; cursor: pointer; transition: all .15s;
    font-family: var(--font); display: flex; align-items: center; gap: 5px; text-decoration: none;
}
.glm-aud-btn:hover { color: var(--text); background: var(--surface-2); }
.glm-aud-btn.active-ach { background: #3b82f6; color: #fff; }
.glm-aud-btn.active-hab { background: #8b5cf6; color: #fff; }
.glm-aud-btn.active-all { background: #475569; color: #fff; }

/* Sub-filters */
.glm-subfilters { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; align-items: center; }
.glm-subfilter { display: flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--text-2); }
.glm-subfilter select {
    padding: 5px 10px; border: 1px solid var(--border); border-radius: 6px;
    background: var(--surface); color: var(--text); font-size: 0.75rem;
    font-family: var(--font); cursor: pointer;
}
.glm-subfilter select:focus { outline: none; border-color: #10b981; }
.glm-subfilter i { font-size: 0.7rem; color: var(--text-3); }

.glm-toolbar-r { display: flex; align-items: center; gap: 10px; }
.glm-search { position: relative; }
.glm-search input {
    padding: 8px 12px 8px 34px; background: var(--surface);
    border: 1px solid var(--border); border-radius: var(--radius);
    color: var(--text); font-size: 0.82rem; width: 220px;
    font-family: var(--font); transition: all .2s var(--ease);
}
.glm-search input:focus {
    outline: none; border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16,185,129,.1); width: 250px;
}
.glm-search i {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%); color: var(--text-3); font-size: 0.75rem;
}

/* ═══ BUTTONS ═══ */
.glm-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: var(--radius);
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    border: none; transition: all .15s var(--ease);
    font-family: var(--font); text-decoration: none; line-height: 1.3;
}
.glm-btn-primary { background: #10b981; color: #fff; box-shadow: 0 1px 4px rgba(16,185,129,.22); }
.glm-btn-primary:hover { background: #059669; transform: translateY(-1px); color: #fff; box-shadow: 0 3px 12px rgba(16,185,129,.28); }
.glm-btn-outline { background: var(--surface); color: var(--text-2); border: 1px solid var(--border); }
.glm-btn-outline:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,.06); }
.glm-btn-sm { padding: 5px 12px; font-size: 0.75rem; }
.glm-btn-guide {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff; box-shadow: 0 1px 4px rgba(59,130,246,.2);
}
.glm-btn-guide:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
.glm-btn-prospect {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: #fff; box-shadow: 0 1px 4px rgba(37,99,235,.2);
}
.glm-btn-prospect:hover { opacity: .92; transform: translateY(-1px); color: #fff; }

/* ═══ BULK ═══ */
.glm-bulk {
    display: none; align-items: center; gap: 12px; padding: 10px 16px;
    background: rgba(16,185,129,.06); border: 1px solid rgba(16,185,129,.15);
    border-radius: var(--radius); margin-bottom: 12px;
    font-size: 0.78rem; color: #059669; font-weight: 600;
}
.glm-bulk.active { display: flex; }
.glm-bulk select {
    padding: 5px 10px; border: 1px solid var(--border); border-radius: 6px;
    background: var(--surface); color: var(--text); font-size: 0.75rem; font-family: var(--font);
}

/* ═══ TABLE ═══ */
.glm-table-wrap {
    background: var(--surface); border-radius: var(--radius-lg);
    border: 1px solid var(--border); overflow: hidden;
}
.glm-table { width: 100%; border-collapse: collapse; }
.glm-table thead th {
    padding: 11px 14px; font-size: 0.65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; color: var(--text-3);
    background: var(--surface-2); border-bottom: 1px solid var(--border);
    text-align: left; white-space: nowrap;
}
.glm-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
.glm-table tbody tr:hover { background: rgba(16,185,129,.02); }
.glm-table tbody tr:last-child { border-bottom: none; }
.glm-table td { padding: 11px 14px; font-size: 0.83rem; color: var(--text); vertical-align: middle; }
.glm-table input[type="checkbox"] { accent-color: #10b981; width: 14px; height: 14px; cursor: pointer; }

/* Partenaire info */
.glm-partner-name {
    font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 7px;
}
.glm-partner-name a { color: var(--text); text-decoration: none; transition: color .15s; }
.glm-partner-name a:hover { color: #10b981; }
.glm-featured-star {
    color: #f59e0b; font-size: 0.7rem;
    cursor: pointer; transition: transform .15s;
}
.glm-featured-star:hover { transform: scale(1.3); }
.glm-partner-addr { font-size: 0.72rem; color: var(--text-3); margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.glm-partner-addr i { font-size: 0.6rem; flex-shrink: 0; }
.glm-partner-desc { font-size: 0.72rem; color: var(--text-2); margin-top: 2px; 
    max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Catégorie badge */
.glm-cat-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 20px;
    font-size: 0.68rem; font-weight: 600; white-space: nowrap;
    border: 1px solid transparent;
}

/* Secteur link */
.glm-secteur {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; background: #f0f9ff; color: #0369a1;
    border-radius: 20px; font-size: 0.68rem; font-weight: 600;
    border: 1px solid #bae6fd; max-width: 130px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.glm-ville {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; background: var(--surface-2);
    border-radius: 20px; font-size: 0.68rem; font-weight: 600;
    color: var(--text-2); border: 1px solid var(--border);
}

/* Audience */
.glm-audience-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 20px; font-size: 0.63rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
    white-space: nowrap;
}
.glm-audience-badge.acheteur { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.glm-audience-badge.habitant { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.glm-audience-badge.tous     { background: var(--surface-2); color: var(--text-2); border: 1px solid var(--border); }

/* GMB */
.glm-gmb {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 10px; font-size: 0.6rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .03em;
}
.glm-gmb.yes { background: #ecfdf5; color: #059669; }
.glm-gmb.no  { background: var(--surface-2); color: var(--text-3); }

/* Status */
.glm-status {
    padding: 3px 10px; border-radius: 12px; font-size: 0.63rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .04em; display: inline-block;
}
.glm-status.published { background: var(--green-bg, #d1fae5); color: var(--green, #059669); }
.glm-status.draft     { background: #fef3c7; color: #d97706; }

/* Note / score */
.glm-note { display: flex; align-items: center; gap: 4px; }
.glm-stars { color: #f59e0b; font-size: 0.65rem; letter-spacing: 1px; }
.glm-note-val { font-size: 0.72rem; font-weight: 700; color: var(--text-2); font-family: var(--font-display); }

/* Actions */
.glm-actions { display: flex; gap: 3px; justify-content: flex-end; }
.glm-actions a, .glm-actions button {
    width: 30px; height: 30px; border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-3); background: transparent; border: 1px solid transparent;
    cursor: pointer; transition: all .12s var(--ease); text-decoration: none; font-size: 0.78rem;
}
.glm-actions a:hover, .glm-actions button:hover { color: #10b981; border-color: var(--border); background: rgba(16,185,129,.07); }
.glm-actions button.del:hover { color: var(--red, #dc2626); border-color: rgba(220,38,38,.2); background: rgba(220,38,38,.06); }
.glm-actions button.gmb-btn:hover { color: #4285f4; border-color: rgba(66,133,244,.2); background: rgba(66,133,244,.06); }

/* ═══ SEO STRATEGY HINT ═══ */
.glm-seo-hint {
    display: flex; gap: 10px; flex-wrap: wrap;
    padding: 14px 18px; margin-bottom: 18px;
    background: linear-gradient(135deg, rgba(16,185,129,.04), rgba(59,130,246,.04));
    border: 1px solid rgba(16,185,129,.15); border-radius: var(--radius-lg);
    font-size: 0.78rem;
}
.glm-seo-hint-item {
    display: flex; align-items: center; gap: 7px;
    padding: 5px 12px; border-radius: 20px;
    background: var(--surface); border: 1px solid var(--border);
    color: var(--text-2); font-weight: 500;
}
.glm-seo-hint-item i { font-size: 0.72rem; color: #10b981; }
.glm-seo-hint-item strong { color: var(--text); }

/* Pagination */
.glm-pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; border-top: 1px solid var(--border);
    font-size: 0.78rem; color: var(--text-3);
}
.glm-pagination a {
    padding: 6px 12px; border: 1px solid var(--border); border-radius: var(--radius);
    color: var(--text-2); text-decoration: none; font-weight: 600;
    transition: all .15s var(--ease); font-size: 0.78rem;
}
.glm-pagination a:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,.06); }
.glm-pagination a.active { background: #10b981; color: #fff; border-color: #10b981; }

/* Flash */
.glm-flash {
    padding: 12px 18px; border-radius: var(--radius); font-size: 0.85rem; font-weight: 600;
    margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
    animation: glmFlashIn .3s var(--ease);
}
.glm-flash.success { background: var(--green-bg, #d1fae5); color: var(--green, #059669); border: 1px solid rgba(5,150,105,.12); }
.glm-flash.error   { background: rgba(220,38,38,.06); color: var(--red, #dc2626); border: 1px solid rgba(220,38,38,.12); }
@keyframes glmFlashIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

/* Install banner */
.glm-install {
    text-align: center; padding: 60px 30px;
    background: var(--surface); border-radius: var(--radius-xl);
    border: 2px dashed var(--border);
}
.glm-install i { font-size: 3rem; color: #10b981; opacity: .3; margin-bottom: 16px; display: block; }
.glm-install h3 { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.glm-install p { font-size: 0.85rem; color: var(--text-2); margin-bottom: 20px; max-width: 460px; margin-left: auto; margin-right: auto; }
.glm-install pre {
    display: inline-block; padding: 12px 20px;
    background: var(--surface-2); border: 1px solid var(--border);
    border-radius: var(--radius); font-size: 0.78rem; color: var(--text-2);
    text-align: left; margin-bottom: 20px; max-width: 100%; overflow-x: auto;
}

/* Empty */
.glm-empty { text-align: center; padding: 60px 20px; color: var(--text-3); }
.glm-empty i { font-size: 2.5rem; opacity: .2; margin-bottom: 12px; display: block; }
.glm-empty h3 { font-family: var(--font-display); color: var(--text-2); font-size: 1rem; font-weight: 600; margin-bottom: 6px; }
.glm-empty a { color: #10b981; }

/* Prospection modal */
.glp-overlay {
    position: fixed; inset: 0; background: rgba(15,23,42,.55);
    display: none; align-items: center; justify-content: center; z-index: 9999; padding: 20px;
}
.glp-overlay.open { display: flex; }
.glp-modal {
    width: min(980px, 100%); max-height: 90vh; overflow: auto;
    background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
    box-shadow: 0 16px 42px rgba(2,6,23,.25);
}
.glp-head, .glp-foot { padding: 14px 16px; border-bottom: 1px solid var(--border); }
.glp-foot { border-top: 1px solid var(--border); border-bottom: 0; display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap; }
.glp-title { margin: 0; font-size: 1rem; display:flex;align-items:center;gap:8px; }
.glp-body { padding: 16px; display: flex; flex-direction: column; gap: 14px; }
.glp-grid { display:grid; grid-template-columns: 1fr 1fr 120px auto; gap: 8px; align-items: end; }
.glp-grid input, .glp-grid select {
    width: 100%; padding: 8px 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface);
}
.glp-table { width: 100%; border-collapse: collapse; }
.glp-table th, .glp-table td { border-bottom: 1px solid var(--border); padding: 8px; font-size: .78rem; text-align: left; vertical-align: top; }
.glp-table th { font-size: .66rem; text-transform: uppercase; color: var(--text-3); letter-spacing: .05em; }
.glp-muted { color: var(--text-3); font-size: .74rem; }
.glp-tag { padding: 2px 8px; border-radius: 20px; background: #eff6ff; color: #1d4ed8; font-size: .66rem; font-weight: 700; }

@media (max-width: 1100px) { .glm-table .col-desc, .glm-table .col-gmb { display: none; } }
@media (max-width: 900px)  { .glm-table .col-secteur, .glm-table .col-note { display: none; } }
@media (max-width: 768px)  {
    .glm-banner { flex-direction: column; gap: 16px; align-items: flex-start; }
    .glm-toolbar { flex-direction: column; align-items: flex-start; }
    .glm-cat-pills { gap: 5px; }
    .glp-grid { grid-template-columns: 1fr; }
}
</style>

<div class="glm-wrap">

<!-- ══════ FLASH ══════ -->
<?php if ($flash && isset($flashMessages[$flash])): ?>
<div class="glm-flash <?= $flashMessages[$flash]['type'] ?>">
    <i class="fas fa-check-circle"></i> <?= $flashMessages[$flash]['text'] ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="glm-flash error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- ══════ BANNER ══════ -->
<div class="glm-banner">
    <div class="glm-banner-left">
        <h2><i class="fas fa-map-marked-alt"></i> Guide Local</h2>
        <p>Partenaires & points d'intérêt par secteur — maillage SEO local pour acheteurs et résidents</p>
        <span class="glm-banner-seo-hint">
            <i class="fas fa-search"></i>
            Stratégie SEO : chaque partenaire = backlink local potentiel + signal E-E-A-T de proximité
        </span>
    </div>
    <div class="glm-stats">
        <div class="glm-stat"><div class="num blue"><?= $stats['total'] ?></div><div class="lbl">Total</div></div>
        <div class="glm-stat"><div class="num green"><?= $stats['published'] ?></div><div class="lbl">Publiés</div></div>
        <div class="glm-stat"><div class="num amber"><?= $stats['draft'] ?></div><div class="lbl">Brouillons</div></div>
        <div class="glm-stat" title="Mis en avant dans le guide"><div class="num amber"><?= $stats['featured'] ?></div><div class="lbl">⭐ Top</div></div>
        <div class="glm-stat" title="Avec fiche Google My Business"><div class="num teal"><?= $stats['with_gmb'] ?></div><div class="lbl">GMB</div></div>
        <div class="glm-stat" title="Ciblés acheteurs"><div class="num blue"><?= $stats['acheteurs'] ?></div><div class="lbl">Acheteurs</div></div>
        <div class="glm-stat" title="Ciblés habitants"><div class="num violet"><?= $stats['habitants'] ?></div><div class="lbl">Résidents</div></div>
    </div>
</div>

<?php if (!$tableExists): ?>
<!-- ══════ TABLE MANQUANTE — INSTALL ══════ -->
<div class="glm-install">
    <i class="fas fa-database"></i>
    <h3>Table <code>guide_local</code> à créer</h3>
    <p>Exécutez le SQL ci-dessous dans votre base de données pour activer le module Guide Local.</p>
    <pre>CREATE TABLE IF NOT EXISTS `guide_local` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `nom`         VARCHAR(255) NOT NULL,
  `slug`        VARCHAR(255) NOT NULL,
  `categorie`   VARCHAR(60)  NOT NULL DEFAULT 'autre',
  `description` TEXT,
  `adresse`     VARCHAR(255),
  `ville`       VARCHAR(100),
  `code_postal` VARCHAR(10),
  `secteur_id`  INT NULL,
  `latitude`    DECIMAL(10,7),
  `longitude`   DECIMAL(10,7),
  `telephone`   VARCHAR(30),
  `site_web`    VARCHAR(255),
  `gmb_url`     VARCHAR(500),
  `note`        DECIMAL(2,1) DEFAULT NULL,
  `audience`    ENUM('acheteur','habitant','tous') DEFAULT 'tous',
  `is_featured` TINYINT(1) DEFAULT 0,
  `status`      ENUM('published','draft') DEFAULT 'draft',
  `meta_title`  VARCHAR(255),
  `meta_desc`   TEXT,
  `created_at`  DATETIME DEFAULT NOW(),
  `updated_at`  DATETIME ON UPDATE NOW(),
  INDEX idx_ville (ville),
  INDEX idx_secteur (secteur_id),
  INDEX idx_categorie (categorie),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>
    <a href="/admin/modules/content/guide-local/install.php" class="glm-btn glm-btn-primary">
        <i class="fas fa-magic"></i> Installer automatiquement
    </a>
</div>

<?php else: ?>

<!-- ══════ SEO STRATEGY HINT ══════ -->
<div class="glm-seo-hint">
    <div class="glm-seo-hint-item"><i class="fas fa-link"></i> <strong>Maillage interne</strong> : chaque partenaire lie vers sa page secteur</div>
    <div class="glm-seo-hint-item"><i class="fas fa-map-marker-alt"></i> <strong>NAP cohérent</strong> : adresse = signal local Google</div>
    <div class="glm-seo-hint-item"><i class="fab fa-google"></i> <strong>GMB</strong> : fiche Google = citation locale +1</div>
    <div class="glm-seo-hint-item"><i class="fas fa-users"></i> <strong>Intent acheteur</strong> : écoles, transports = requêtes "vivre à X"</div>
    <div class="glm-seo-hint-item"><i class="fas fa-star"></i> <strong>E-E-A-T</strong> : expertise locale reconnue par Google</div>
</div>

<!-- ══════ CATEGORIES PILLS ══════ -->
<div class="glm-cat-pills">
    <?php
    $allUrl = '?page=guide-local';
    if ($filterStatus !== 'all') $allUrl .= '&status='.$filterStatus;
    if ($filterVille  !== 'all') $allUrl .= '&ville='.urlencode($filterVille);
    if ($filterAudience !== 'all') $allUrl .= '&audience='.$filterAudience;
    ?>
    <a href="<?= $allUrl ?>" class="glm-cat-pill <?= $filterCat === 'all' ? 'active' : '' ?>"
       style="<?= $filterCat === 'all' ? 'background:#475569;' : '' ?>">
        <i class="fas fa-layer-group"></i> Toutes
        <span class="cnt"><?= $stats['total'] ?></span>
    </a>
    <?php foreach ($partnerCategories as $key => $cat):
        $cnt  = $catCounts[$key] ?? 0;
        if ($cnt === 0 && $filterCat !== $key) continue; // masquer vides sauf actif
        $isActive = $filterCat === $key;
        $catUrl = '?page=guide-local&categorie='.$key;
        if ($filterStatus  !== 'all') $catUrl .= '&status='.$filterStatus;
        if ($filterVille   !== 'all') $catUrl .= '&ville='.urlencode($filterVille);
        if ($filterAudience !== 'all') $catUrl .= '&audience='.$filterAudience;
    ?>
    <a href="<?= $catUrl ?>" class="glm-cat-pill <?= $isActive ? 'active' : '' ?>"
       style="<?= $isActive ? "background:{$cat['color']};" : '' ?>">
        <i class="fas <?= $cat['icon'] ?>"></i> <?= $cat['label'] ?>
        <span class="cnt"><?= $cnt ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ══════ TOOLBAR ══════ -->
<div class="glm-toolbar">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <!-- Filtre status -->
        <div class="glm-filters">
            <?php
            $statusTabs = [
                'all'       => ['icon'=>'fa-layer-group',  'label'=>'Tous',       'count'=>$stats['total']],
                'published' => ['icon'=>'fa-check-circle', 'label'=>'Publiés',    'count'=>$stats['published']],
                'draft'     => ['icon'=>'fa-pencil-alt',   'label'=>'Brouillons', 'count'=>$stats['draft']],
            ];
            foreach ($statusTabs as $key => $f):
                $active = $filterStatus === $key ? ' active' : '';
                $url = '?page=guide-local' . ($key !== 'all' ? '&status='.$key : '');
                if ($filterCat     !== 'all') $url .= '&categorie='.$filterCat;
                if ($filterVille   !== 'all') $url .= '&ville='.urlencode($filterVille);
                if ($filterAudience !== 'all') $url .= '&audience='.$filterAudience;
                if ($searchQuery)              $url .= '&q='.urlencode($searchQuery);
            ?>
            <a href="<?= $url ?>" class="glm-fbtn<?= $active ?>">
                <i class="fas <?= $f['icon'] ?>"></i> <?= $f['label'] ?>
                <span class="badge"><?= (int)$f['count'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Audience toggle -->
        <div class="glm-audience">
            <?php
            $audTabs = [
                'all'      => ['icon'=>'fa-users',          'label'=>'Tous',       'cls'=>'active-all'],
                'acheteur' => ['icon'=>'fa-home',           'label'=>'Acheteurs',  'cls'=>'active-ach'],
                'habitant' => ['icon'=>'fa-map-marker-alt', 'label'=>'Résidents',  'cls'=>'active-hab'],
            ];
            foreach ($audTabs as $key => $a):
                $isAud = $filterAudience === $key;
                $audUrl = '?page=guide-local';
                if ($key !== 'all') $audUrl .= '&audience='.$key;
                if ($filterStatus !== 'all') $audUrl .= '&status='.$filterStatus;
                if ($filterCat    !== 'all') $audUrl .= '&categorie='.$filterCat;
                if ($filterVille  !== 'all') $audUrl .= '&ville='.urlencode($filterVille);
            ?>
            <a href="<?= $audUrl ?>" class="glm-aud-btn <?= $isAud ? $a['cls'] : '' ?>">
                <i class="fas <?= $a['icon'] ?>"></i> <?= $a['label'] ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="glm-toolbar-r">
        <form class="glm-search" method="GET">
            <input type="hidden" name="page" value="guide-local">
            <?php if ($filterStatus !== 'all'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
            <?php if ($filterCat    !== 'all'): ?><input type="hidden" name="categorie" value="<?= htmlspecialchars($filterCat) ?>"><?php endif; ?>
            <i class="fas fa-search"></i>
            <input type="text" name="q" placeholder="Nom, adresse, ville..." value="<?= htmlspecialchars($searchQuery) ?>">
        </form>
        <a href="?page=guide-local&action=generate" class="glm-btn glm-btn-guide">
            <i class="fas fa-robot"></i> Générer avec IA
        </a>
        <button type="button" class="glm-btn glm-btn-prospect" onclick="GLP.open()">
            <i class="fas fa-search-location"></i> Trouver partenaires (Perplexity)
        </button>
        <a href="/admin/modules/content/guide-local/edit.php?action=create" class="glm-btn glm-btn-primary">
            <i class="fas fa-plus"></i> Ajouter partenaire
        </a>
    </div>
</div>

<!-- Sub-filters -->
<div class="glm-subfilters">
    <?php if (!empty($villes)): ?>
    <div class="glm-subfilter">
        <i class="fas fa-city"></i>
        <select onchange="GLM.filterBy('ville', this.value)">
            <option value="all" <?= $filterVille==='all' ? 'selected':'' ?>>Toutes les villes</option>
            <?php foreach ($villes as $v): ?>
            <option value="<?= htmlspecialchars($v) ?>" <?= $filterVille===$v ? 'selected':'' ?>><?= htmlspecialchars($v) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <?php if (!empty($secteurs)): ?>
    <div class="glm-subfilter">
        <i class="fas fa-map-pin"></i>
        <select onchange="GLM.filterBy('secteur', this.value)">
            <option value="all" <?= $filterSecteur==='all' ? 'selected':'' ?>>Tous les secteurs</option>
            <?php foreach ($secteurs as $sec): ?>
            <option value="<?= (int)$sec['id'] ?>" <?= $filterSecteur==(string)$sec['id'] ? 'selected':'' ?>><?= htmlspecialchars($sec['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <?php if ($filterCat !== 'all' || $filterVille !== 'all' || $filterSecteur !== 'all' || $filterAudience !== 'all' || $searchQuery): ?>
    <a href="?page=guide-local" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(220,38,38,.06);border:1px solid rgba(220,38,38,.15);border-radius:6px;font-size:0.72rem;font-weight:600;color:var(--red,#dc2626);text-decoration:none;">
        <i class="fas fa-times"></i> Réinitialiser
    </a>
    <?php endif; ?>
</div>

<!-- ══════ BULK ══════ -->
<div class="glm-bulk" id="glmBulkBar">
    <input type="checkbox" id="glmSelectAll" onchange="GLM.toggleAll(this.checked)">
    <span id="glmBulkCount">0</span> sélectionné(s)
    <select id="glmBulkAction">
        <option value="">— Action groupée —</option>
        <option value="publish">Publier</option>
        <option value="draft">Brouillon</option>
        <option value="feature">Mettre en avant</option>
        <option value="delete">Supprimer</option>
    </select>
    <button class="glm-btn glm-btn-sm glm-btn-outline" onclick="GLM.bulkExecute()">
        <i class="fas fa-check"></i> Appliquer
    </button>
</div>

<!-- ══════ TABLE ══════ -->
<div class="glm-table-wrap">
    <?php if (empty($partners)): ?>
    <div class="glm-empty">
        <i class="fas fa-map-marked-alt"></i>
        <h3><?= $searchQuery || $filterCat !== 'all' || $filterStatus !== 'all' ? 'Aucun résultat' : 'Guide local vide' ?></h3>
        <p>
            <?php if ($searchQuery): ?>
                Aucun résultat pour « <?= htmlspecialchars($searchQuery) ?> ». <a href="?page=guide-local">Effacer</a>
            <?php else: ?>
                Ajoutez vos premiers partenaires locaux pour booster votre SEO de proximité.
            <?php endif; ?>
        </p>
    </div>
    <?php else: ?>
    <table class="glm-table">
        <thead>
            <tr>
                <th style="width:32px"><input type="checkbox" onchange="GLM.toggleAll(this.checked)"></th>
                <th>Partenaire</th>
                <th>Catégorie</th>
                <th>Secteur / Ville</th>
                <th class="col-note">Note</th>
                <th class="col-gmb">GMB</th>
                <th>Audience</th>
                <th>Statut</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($partners as $p):
                $cat     = $p['categorie'] ?? 'autre';
                $catInfo = $partnerCategories[$cat] ?? $partnerCategories['autre'];
                $status  = $p['status'] ?? 'draft';
                $audience = $p['audience'] ?? 'tous';
                $note    = (float)($p['note'] ?? 0);
                $hasGmb  = !empty($p['gmb_url']);
                $isFeatured = !empty($p['is_featured']);
                $editUrl = "/admin/modules/content/guide-local/edit.php?id={$p['id']}";

                // Étoiles
                $stars = '';
                if ($note > 0) {
                    $full = floor($note);
                    $half = ($note - $full) >= 0.5 ? 1 : 0;
                    $stars = str_repeat('★', $full) . ($half ? '½' : '') . str_repeat('☆', 5 - $full - $half);
                }

                $audienceLabels = [
                    'acheteur' => ['label'=>'Acheteur',  'icon'=>'fa-home'],
                    'habitant' => ['label'=>'Résident',  'icon'=>'fa-map-marker-alt'],
                    'tous'     => ['label'=>'Universel', 'icon'=>'fa-users'],
                ];
                $audInfo = $audienceLabels[$audience] ?? $audienceLabels['tous'];
            ?>
            <tr data-id="<?= (int)$p['id'] ?>">
                <td><input type="checkbox" class="glm-cb" value="<?= (int)$p['id'] ?>" onchange="GLM.updateBulk()"></td>
                <td>
                    <div class="glm-partner-name">
                        <button class="glm-featured-star" onclick="GLM.toggleFeatured(<?= (int)$p['id'] ?>)"
                                title="<?= $isFeatured ? 'Retirer du top' : 'Mettre en avant' ?>"
                                style="<?= $isFeatured ? 'color:#f59e0b' : 'color:var(--border)' ?>; background:none; border:none; cursor:pointer; font-size:0.9rem; padding:0;">
                            <i class="fas fa-star"></i>
                        </button>
                        <a href="<?= htmlspecialchars($editUrl) ?>"><?= htmlspecialchars($p['nom'] ?? 'Sans nom') ?></a>
                    </div>
                    <?php if (!empty($p['adresse'])): ?>
                    <div class="glm-partner-addr">
                        <i class="fas fa-map-pin"></i>
                        <?= htmlspecialchars($p['adresse']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['description'])): ?>
                    <div class="glm-partner-desc col-desc"><?= htmlspecialchars($p['description']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="glm-cat-badge"
                          style="background:<?= $catInfo['bg'] ?>;color:<?= $catInfo['color'] ?>;border-color:<?= $catInfo['color'] ?>33;">
                        <i class="fas <?= $catInfo['icon'] ?>"></i>
                        <?= $catInfo['label'] ?>
                    </span>
                </td>
                <td class="col-secteur">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <?php if (!empty($p['secteur_nom'])): ?>
                        <span class="glm-secteur"><i class="fas fa-map-pin"></i><?= htmlspecialchars($p['secteur_nom']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($p['ville'])): ?>
                        <span class="glm-ville"><?= htmlspecialchars($p['ville']) ?><?= !empty($p['code_postal']) ? ' '.$p['code_postal'] : '' ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="col-note">
                    <?php if ($note > 0): ?>
                    <div class="glm-note">
                        <span class="glm-stars"><?= $stars ?></span>
                        <span class="glm-note-val"><?= number_format($note, 1) ?></span>
                    </div>
                    <?php else: ?>
                    <span style="color:var(--text-3);font-size:.75rem">—</span>
                    <?php endif; ?>
                </td>
                <td class="col-gmb">
                    <?php if ($hasGmb): ?>
                    <span class="glm-gmb yes"><i class="fab fa-google"></i> Lié</span>
                    <?php else: ?>
                    <span class="glm-gmb no"><i class="fas fa-times"></i> Non</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="glm-audience-badge <?= $audience ?>">
                        <i class="fas <?= $audInfo['icon'] ?>"></i>
                        <?= $audInfo['label'] ?>
                    </span>
                </td>
                <td>
                    <span class="glm-status <?= $status ?>">
                        <?= $status === 'published' ? 'Publié' : 'Brouillon' ?>
                    </span>
                </td>
                <td>
                    <div class="glm-actions">
                        <?php if (!empty($p['site_web'])): ?>
                        <a href="<?= htmlspecialchars($p['site_web']) ?>" target="_blank" title="Voir le site"><i class="fas fa-external-link-alt"></i></a>
                        <?php endif; ?>
                        <?php if ($hasGmb): ?>
                        <a href="<?= htmlspecialchars($p['gmb_url']) ?>" target="_blank" class="gmb-btn" title="Fiche Google"><i class="fab fa-google"></i></a>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($editUrl) ?>" title="Modifier"><i class="fas fa-edit"></i></a>
                        <button onclick="GLM.toggleStatus(<?= (int)$p['id'] ?>, '<?= $status ?>')"
                                title="<?= $status==='published' ? 'Dépublier':'Publier' ?>">
                            <i class="fas <?= $status==='published' ? 'fa-eye-slash':'fa-eye' ?>"></i>
                        </button>
                        <button class="del" onclick="GLM.deletePartner(<?= (int)$p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nom']??'')) ?>')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
    <div class="glm-pagination">
        <span>Affichage <?= $offset+1 ?>–<?= min($offset+$perPage,$totalFiltered) ?> sur <?= $totalFiltered ?> partenaires</span>
        <div style="display:flex;gap:4px">
            <?php for ($i=1; $i<=$totalPages; $i++):
                $pUrl = '?page=guide-local&p='.$i;
                if ($filterStatus  !== 'all') $pUrl .= '&status='.$filterStatus;
                if ($filterCat     !== 'all') $pUrl .= '&categorie='.$filterCat;
                if ($filterVille   !== 'all') $pUrl .= '&ville='.urlencode($filterVille);
                if ($filterAudience !== 'all') $pUrl .= '&audience='.$filterAudience;
                if ($searchQuery)              $pUrl .= '&q='.urlencode($searchQuery);
            ?>
            <a href="<?= $pUrl ?>" class="<?= $i===$currentPage ? 'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php endif; // tableExists ?>
</div>

<div class="glp-overlay" id="glpOverlay">
    <div class="glp-modal">
        <div class="glp-head" style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <h3 class="glp-title"><i class="fas fa-handshake"></i> Prospection partenaires locaux (Perplexity + CRM)</h3>
            <button type="button" class="glm-btn glm-btn-sm glm-btn-outline" onclick="GLP.close()"><i class="fas fa-times"></i></button>
        </div>
        <div class="glp-body">
            <div class="glp-grid">
                <label>Ville
                    <input type="text" id="glpCity" value="<?= htmlspecialchars($filterVille !== 'all' ? $filterVille : 'Bordeaux') ?>">
                </label>
                <label>Catégorie
                    <input type="text" id="glpCategory" value="<?= htmlspecialchars($filterCat !== 'all' ? ($partnerCategories[$filterCat]['label'] ?? 'Services locaux') : 'Services locaux') ?>">
                </label>
                <label>Nb
                    <input type="number" id="glpLimit" min="3" max="20" value="8">
                </label>
                <button type="button" class="glm-btn glm-btn-guide" onclick="GLP.search(this)"><i class="fas fa-bolt"></i> Rechercher</button>
            </div>
            <div class="glp-muted">L'IA récupère des partenaires locaux, leurs coordonnées (email/téléphone si disponibles) et la raison du partenariat.</div>
            <div id="glpResultsWrap" style="display:none;">
                <table class="glp-table" id="glpTable">
                    <thead>
                        <tr>
                            <th style="width:28px"><input type="checkbox" id="glpSelectAll" onchange="GLP.toggleAll(this.checked)"></th>
                            <th>Partenaire</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Raison</th>
                        </tr>
                    </thead>
                    <tbody id="glpRows"></tbody>
                </table>
            </div>
            <div id="glpEmpty" class="glp-muted">Aucun résultat pour le moment.</div>
        </div>
        <div class="glp-foot">
            <div style="display:flex;align-items:center;gap:6px;">
                <label class="glp-muted" for="glpMethod">Canal préféré :</label>
                <select id="glpMethod">
                    <option value="email">Email</option>
                    <option value="telephone">Téléphone</option>
                    <option value="both">Email + Téléphone</option>
                </select>
            </div>
            <button type="button" class="glm-btn glm-btn-primary" onclick="GLP.createActions()">
                <i class="fas fa-tasks"></i> Créer action CRM
            </button>
        </div>
    </div>
</div>

<script>
const GLM = {
    apiUrl: '/admin/api/content/guide-local.php',

    filterBy(key, value) {
        const url = new URL(window.location.href);
        value === 'all' ? url.searchParams.delete(key) : url.searchParams.set(key, value);
        url.searchParams.delete('p');
        window.location.href = url.toString();
    },

    toggleAll(checked) {
        document.querySelectorAll('.glm-cb').forEach(cb => cb.checked = checked);
        this.updateBulk();
    },

    updateBulk() {
        const checked = document.querySelectorAll('.glm-cb:checked');
        document.getElementById('glmBulkCount').textContent = checked.length;
        document.getElementById('glmBulkBar').classList.toggle('active', checked.length > 0);
    },

    async bulkExecute() {
        const action = document.getElementById('glmBulkAction').value;
        if (!action) return;
        const ids = [...document.querySelectorAll('.glm-cb:checked')].map(cb => parseInt(cb.value));
        if (!ids.length) return;
        if (action === 'delete' && !confirm(`Supprimer ${ids.length} partenaire(s) ?`)) return;
        const fd = new FormData();
        fd.append('action', action === 'delete' ? 'bulk_delete' : 'bulk_status');
        if (action === 'feature') fd.append('featured', '1');
        if (!['delete','feature'].includes(action)) fd.append('status', {publish:'published',draft:'draft'}[action]);
        fd.append('ids', JSON.stringify(ids));
        const r = await fetch(this.apiUrl, {method:'POST', body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Erreur');
    },

    async deletePartner(id, nom) {
        if (!confirm(`Supprimer « ${nom} » du guide local ?`)) return;
        const fd = new FormData();
        fd.append('action','delete'); fd.append('id', id);
        const r = await fetch(this.apiUrl, {method:'POST', body:fd});
        const d = await r.json();
        if (d.success) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) { row.style.cssText='opacity:0;transform:translateX(20px);transition:all .3s'; setTimeout(()=>row.remove(),300); }
        } else { alert(d.error||'Erreur'); }
    },

    async toggleStatus(id, currentStatus) {
        const fd = new FormData();
        fd.append('action','toggle_status'); fd.append('id', id);
        const r = await fetch(this.apiUrl, {method:'POST', body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error||'Erreur');
    },

    async toggleFeatured(id) {
        const fd = new FormData();
        fd.append('action','toggle_featured'); fd.append('id', id);
        const r = await fetch(this.apiUrl, {method:'POST', body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error||'Erreur');
    }
};

const GLP = {
    apiUrl: '/admin/api/content/guide-local-prospection.php',
    rows: [],

    open() {
        document.getElementById('glpOverlay').classList.add('open');
    },
    close() {
        document.getElementById('glpOverlay').classList.remove('open');
    },
    toggleAll(checked) {
        document.querySelectorAll('.glp-cb').forEach(cb => cb.checked = checked);
    },
    selected() {
        return this.rows.filter((_, idx) => {
            const cb = document.getElementById(`glp_cb_${idx}`);
            return cb && cb.checked;
        });
    },
    async search(btnEl) {
        const fd = new FormData();
        fd.append('action', 'search_partners');
        fd.append('city', document.getElementById('glpCity').value.trim());
        fd.append('category', document.getElementById('glpCategory').value.trim());
        fd.append('limit', document.getElementById('glpLimit').value);

        const btn = btnEl || null;
        if (btn) btn.disabled = true;
        try {
            const r = await fetch(this.apiUrl, { method: 'POST', body: fd });
            const d = await r.json();
            if (!d.success) return alert(d.error || 'Erreur de recherche');
            this.rows = d.partners || [];
            this.render();
        } catch (e) {
            alert('Erreur réseau');
        } finally {
            if (btn) btn.disabled = false;
        }
    },
    render() {
        const body = document.getElementById('glpRows');
        const wrap = document.getElementById('glpResultsWrap');
        const empty = document.getElementById('glpEmpty');
        if (!this.rows.length) {
            wrap.style.display = 'none';
            empty.textContent = 'Aucun résultat trouvé.';
            return;
        }
        wrap.style.display = '';
        empty.textContent = `${this.rows.length} partenaire(s) trouvé(s).`;
        body.innerHTML = this.rows.map((p, idx) => `
            <tr>
                <td><input id="glp_cb_${idx}" class="glp-cb" type="checkbox" checked></td>
                <td><strong>${GLP.escape(p.nom || '')}</strong><br><span class="glp-tag">${GLP.escape(p.categorie || '')}</span><div class="glp-muted">${GLP.escape(p.ville || '')}</div></td>
                <td>${p.email ? `<a href="mailto:${GLP.escape(p.email)}">${GLP.escape(p.email)}</a>` : '<span class="glp-muted">Non trouvé</span>'}</td>
                <td>${p.telephone ? `<a href="tel:${GLP.escape(p.telephone)}">${GLP.escape(p.telephone)}</a>` : '<span class="glp-muted">Non trouvé</span>'}</td>
                <td>${GLP.escape(p.raison || '')}</td>
            </tr>
        `).join('');
    },
    async createActions() {
        const selected = this.selected();
        if (!selected.length) return alert('Sélectionnez au moins un partenaire.');
        const fd = new FormData();
        fd.append('action', 'create_crm_actions');
        fd.append('preferred_contact_method', document.getElementById('glpMethod').value);
        fd.append('partners', JSON.stringify(selected));
        const r = await fetch(this.apiUrl, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.success) return alert(d.error || 'Erreur CRM');
        alert(d.message || 'Actions CRM créées');
        this.close();
        location.reload();
    },
    escape(v) {
        return String(v).replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }
};

// Auto-dismiss flash
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.glm-flash').forEach(el => {
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transition = 'opacity .3s';
            setTimeout(() => el.remove(), 300);
        }, 4000);
    });
});
</script>
