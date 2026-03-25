<?php
/**
 * Module Pages — Liste & Gestion
 * VERSION LIGHT THEME v3.1
 *
 * Chargé par dashboard.php via : ?page=pages
 * Hérite de $pdo ou $db depuis init.php
 *
 * CHANGELOG v3.1 (depuis v3.0) :
 *  ✅ Style v3.0 conservé intégralement (CSS/tableau/badges/stats/toolbar)
 *  ✅ Bouton "Nouvelle page" → modal (titre) → crée en DB → éditeur structuré
 *  ✅ Lien "Éditer" → ?page=pages&action=edit&id=X
 *  ✅ Actions delete/toggleStatus/duplicate restent en AJAX vers l'API
 *  ✅ Action create_page en POST natif (pas d'API externe, slug auto, redirect)
 *  ✅ init.php cherché à 2/3/4 niveaux pour portabilité
 */

// ─── Connexion DB ───
if (!isset($pdo) && !isset($db)) {
    $inits = [
        dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/init.php',
        dirname(dirname(dirname(__DIR__)))           . '/includes/init.php',
        dirname(dirname(__DIR__))                    . '/includes/init.php',
    ];
    foreach ($inits as $f) { if (file_exists($f)) { require_once $f; break; } }
}
if (isset($db)  && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db))  $db  = $pdo;

// ─── Mode éditeur structuré (comme les articles) ───
$requestedAction = $_GET['action'] ?? '';
if (in_array($requestedAction, ['create', 'edit'], true)) {
    require __DIR__ . '/action.php';
    return;
}

// ─── Créer table si absente ───
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `pages` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `content` LONGTEXT DEFAULT NULL,
        `html_content` LONGTEXT DEFAULT NULL,
        `custom_css` LONGTEXT DEFAULT NULL,
        `custom_js` LONGTEXT DEFAULT NULL,
        `meta_title` VARCHAR(160) DEFAULT NULL,
        `meta_description` VARCHAR(320) DEFAULT NULL,
        `meta_keywords` VARCHAR(255) DEFAULT NULL,
        `og_image` VARCHAR(500) DEFAULT NULL,
        `is_file_based` TINYINT(1) UNSIGNED DEFAULT 0,
        `file_path` VARCHAR(255) DEFAULT NULL,
        `template` VARCHAR(100) DEFAULT 'default',
        `parent_id` INT UNSIGNED DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        `status` ENUM('draft','published','archived') DEFAULT 'draft',
        `visibility` ENUM('public','private') DEFAULT 'public',
        `google_indexed` ENUM('yes','no','pending','unknown') DEFAULT 'unknown',
        `seo_score` INT DEFAULT 0,
        `semantic_score` INT DEFAULT 0,
        `word_count` INT DEFAULT 0,
        `author_id` INT UNSIGNED DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `published_at` DATETIME DEFAULT NULL,
        UNIQUE KEY `uk_slug` (`slug`),
        KEY `idx_status` (`status`),
        KEY `idx_parent` (`parent_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {}

// ─── Colonnes disponibles ───
$availCols = [];
try {
    $availCols = $pdo->query("SHOW COLUMNS FROM pages")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {}

// ─── Ajouter colonnes manquantes ───
$newCols = [
    'visibility'     => "ENUM('public','private') DEFAULT 'public' AFTER `status`",
    'google_indexed' => "ENUM('yes','no','pending','unknown') DEFAULT 'unknown' AFTER `visibility`",
    'semantic_score' => "INT DEFAULT 0 AFTER `seo_score`",
];
foreach ($newCols as $col => $def) {
    if (!in_array($col, $availCols)) {
        try { $pdo->exec("ALTER TABLE `pages` ADD COLUMN `{$col}` {$def}"); $availCols[] = $col; } catch (PDOException $e) {}
    }
}

// ─── Table seo_scores ───
$hasSeoScoresTable = false;
try {
    $pdo->query("SELECT 1 FROM seo_scores LIMIT 1");
    $hasSeoScoresTable = true;
} catch (PDOException $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `seo_scores` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `context` VARCHAR(50) NOT NULL,
            `entity_id` INT NOT NULL,
            `score_global` INT DEFAULT 0,
            `score_technique` INT DEFAULT 0,
            `score_contenu` INT DEFAULT 0,
            `score_semantique` INT DEFAULT 0,
            `focus_keyword` VARCHAR(255) DEFAULT NULL,
            `details` JSON DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `ctx_entity` (`context`, `entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $hasSeoScoresTable = true;
    } catch (PDOException $e2) {}
}

$hasSeoScore      = in_array('seo_score',      $availCols);
$hasSemanticScore = in_array('semantic_score',  $availCols);
$hasWordCount     = in_array('word_count',      $availCols);
$hasIsFileBased   = in_array('is_file_based',   $availCols);
$hasTemplate      = in_array('template',        $availCols);
$hasVisibility    = in_array('visibility',      $availCols);
$hasGoogleIndexed = in_array('google_indexed',  $availCols);
$hasPublishedAt   = in_array('published_at',    $availCols);

// ════════════════════════════════════════════════════════════
//  ACTION POST NATIF : créer une page → redirect éditeur
// ════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create_page') {
    $newTitle = trim($_POST['new_title'] ?? '');
    if ($newTitle) {
        // Génération slug
        $slug = strtolower($newTitle);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-') ?: 'page';
        // Unicité du slug
        $base = $slug; $i = 1;
        while (true) {
            $c = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
            $c->execute([$slug]);
            if (!$c->fetchColumn()) break;
            $slug = $base . '-' . $i++;
        }
        try {
            $fields = array_filter(['title','slug','status','created_at','updated_at'], fn($c) => in_array($c, $availCols));
            $vals   = ['title'=>$newTitle,'slug'=>$slug,'status'=>'draft','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];
            $fv     = array_intersect_key($vals, array_flip($fields));
            $pdo->prepare(
                "INSERT INTO pages (`".implode('`,`',array_keys($fv))."`) VALUES (".implode(',',array_fill(0,count($fv),'?')).")"
            )->execute(array_values($fv));
            $newId = (int)$pdo->lastInsertId();
            // Redirection vers éditeur structuré
            header("Location: ?page=pages&action=edit&id={$newId}");
            exit;
        } catch (PDOException $e) {
            header("Location: ?page=pages&msg=error"); exit;
        }
    }
    header("Location: ?page=pages&msg=error"); exit;
}

// ─── Filtres ───
$filterStatus     = $_GET['status']     ?? 'all';
$filterIndexed    = $_GET['indexed']    ?? 'all';
$searchQuery      = trim($_GET['q']     ?? '');
$currentPage      = max(1, (int)($_GET['p'] ?? 1));
$perPage          = 25;
$offset           = ($currentPage - 1) * $perPage;

$where = []; $params = [];
// Scope du module: uniquement les pages publiques du site (hors contenus "secteur"/"article")
if ($hasVisibility) {
    $where[] = "p.visibility = ?";
    $params[] = 'public';
}
if (in_array('content_type', $availCols)) {
    $where[] = "(p.content_type IS NULL OR p.content_type NOT IN ('secteur','article'))";
}
if (in_array('type', $availCols)) {
    $where[] = "(p.type IS NULL OR p.type NOT IN ('secteur','article'))";
}
if ($filterStatus !== 'all' && in_array($filterStatus, ['draft','published','archived'])) {
    $where[] = "p.status = ?"; $params[] = $filterStatus;
}
if ($filterIndexed !== 'all' && $hasGoogleIndexed && in_array($filterIndexed, ['yes','no','pending','unknown'])) {
    $where[] = "p.google_indexed = ?"; $params[] = $filterIndexed;
}
if ($searchQuery !== '') {
    $where[] = "(p.title LIKE ? OR p.slug LIKE ?)";
    $params[] = "%{$searchQuery}%"; $params[] = "%{$searchQuery}%";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ─── Stats globales ───
$stats = ['total'=>0,'published'=>0,'draft'=>0,'archived'=>0,'avg_seo'=>0,'avg_semantic'=>0,'indexed_count'=>0];
try {
    $statsWhere = [];
    $statsParams = [];
    if ($hasVisibility) {
        $statsWhere[] = "visibility = ?";
        $statsParams[] = 'public';
    }
    if (in_array('content_type', $availCols)) {
        $statsWhere[] = "(content_type IS NULL OR content_type NOT IN ('secteur','article'))";
    }
    if (in_array('type', $availCols)) {
        $statsWhere[] = "(type IS NULL OR type NOT IN ('secteur','article'))";
    }
    $statsWhereSQL = $statsWhere ? (' WHERE ' . implode(' AND ', $statsWhere)) : '';
    $seoAvgCol      = $hasSeoScore      ? ", ROUND(AVG(NULLIF(seo_score,0)),0) AS avg_seo"           : "";
    $semanticAvgCol = $hasSemanticScore ? ", ROUND(AVG(NULLIF(semantic_score,0)),0) AS avg_semantic"  : "";
    $indexedCol     = $hasGoogleIndexed ? ", SUM(google_indexed='yes') AS indexed_count"              : "";
    $statsSql = "SELECT COUNT(*) AS total, SUM(status='published') AS published, SUM(status='draft') AS draft, SUM(status='archived') AS archived {$seoAvgCol}{$semanticAvgCol}{$indexedCol} FROM pages{$statsWhereSQL}";
    $r = $pdo->prepare($statsSql);
    $r->execute($statsParams);
    $stats = $r->fetch(PDO::FETCH_ASSOC) ?: $stats;
} catch (PDOException $e) {}

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM pages p {$whereSQL}");
$stmtCount->execute($params);
$totalFiltered = (int)$stmtCount->fetchColumn();
$totalPages    = max(1, ceil($totalFiltered / $perPage));

// ─── SELECT colonnes ───
$selectCols = ['p.id','p.title','p.slug','p.status','p.created_at','p.updated_at'];
if ($hasSeoScore)      $selectCols[] = 'p.seo_score';
if ($hasSemanticScore) $selectCols[] = 'p.semantic_score';
if ($hasWordCount)     $selectCols[] = 'p.word_count';
if ($hasIsFileBased)   $selectCols[] = 'p.is_file_based';
if ($hasTemplate)      $selectCols[] = 'p.template';
if ($hasVisibility)    $selectCols[] = 'p.visibility';
if ($hasGoogleIndexed) $selectCols[] = 'p.google_indexed';
if (in_array('file_path', $availCols)) $selectCols[] = 'p.file_path';
if ($hasSeoScoresTable) {
    $selectCols[] = 'ss.score_semantique AS ss_semantic';
    $selectCols[] = 'ss.score_global AS ss_global';
}
$colsSQL = implode(', ', $selectCols);
$joinSQL  = $hasSeoScoresTable ? "LEFT JOIN seo_scores ss ON ss.context='landing' AND ss.entity_id=p.id" : "";

$stmtList = $pdo->prepare("SELECT {$colsSQL} FROM pages p {$joinSQL} {$whereSQL} ORDER BY p.updated_at DESC LIMIT {$perPage} OFFSET {$offset}");
$stmtList->execute($params);
$pages = $stmtList->fetchAll(PDO::FETCH_ASSOC);

$flash = $_GET['msg'] ?? '';
?>

<!-- ═══════════════════════════════════════════════════════════ -->
<!--  MODULE PAGES — LISTING  (Light Theme v3.1)                 -->
<!-- ═══════════════════════════════════════════════════════════ -->
<style>
/* ─────────────────────────────────────────────
   PAGES MODULE — LIGHT THEME v3.1
   Style v3.0 conservé intégralement
   Ajout : modal créer page (pgm-modal-*)
   ───────────────────────────────────────────── */

.pgm-wrap { font-family: var(--font); }

/* ═══ BANNER ═══ */
.pgm-banner { background:var(--surface); border-radius:var(--radius-xl); padding:26px 30px; margin-bottom:22px; display:flex; align-items:center; justify-content:space-between; border:1px solid var(--border); position:relative; overflow:hidden; }
.pgm-banner::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--accent),var(--accent-l),var(--teal)); opacity:.7; }
.pgm-banner::after  { content:''; position:absolute; top:-40%; right:-5%; width:220px; height:220px; background:radial-gradient(circle,rgba(79,70,229,.04),transparent 70%); border-radius:50%; pointer-events:none; }
.pgm-banner-left { position:relative; z-index:1; }
.pgm-banner-left h2 { font-family:var(--font-display); font-size:1.35rem; font-weight:700; color:var(--text); margin:0 0 4px; display:flex; align-items:center; gap:10px; letter-spacing:-.02em; }
.pgm-banner-left h2 i { font-size:16px; color:var(--accent); }
.pgm-banner-left p { color:var(--text-2); font-size:.85rem; margin:0; line-height:1.5; }
.pgm-stats { display:flex; gap:8px; position:relative; z-index:1; flex-wrap:wrap; }
.pgm-stat { text-align:center; padding:10px 16px; background:var(--surface-2); border-radius:var(--radius-lg); border:1px solid var(--border); min-width:72px; transition:all .2s var(--ease); }
.pgm-stat:hover { border-color:var(--border-h); box-shadow:var(--shadow-xs); }
.pgm-stat .num { font-family:var(--font-display); font-size:1.45rem; font-weight:800; line-height:1; color:var(--text); letter-spacing:-.03em; }
.pgm-stat .num.blue   { color:var(--accent); }
.pgm-stat .num.green  { color:var(--green); }
.pgm-stat .num.amber  { color:var(--amber); }
.pgm-stat .num.teal   { color:var(--teal,#0d9488); }
.pgm-stat .num.violet { color:var(--violet,#7c3aed); }
.pgm-stat .lbl { font-size:.58rem; color:var(--text-3); text-transform:uppercase; letter-spacing:.06em; font-weight:600; margin-top:3px; }

/* ═══ TOOLBAR ═══ */
.pgm-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
.pgm-filters { display:flex; gap:3px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:3px; flex-wrap:wrap; }
.pgm-fbtn { padding:7px 15px; border:none; background:transparent; color:var(--text-2); font-size:.78rem; font-weight:600; border-radius:6px; cursor:pointer; transition:all .15s var(--ease); font-family:var(--font); display:flex; align-items:center; gap:5px; text-decoration:none; }
.pgm-fbtn:hover { color:var(--text); background:var(--surface-2); }
.pgm-fbtn.active { background:var(--accent); color:#fff; box-shadow:0 1px 4px rgba(79,70,229,.18); }
.pgm-fbtn .badge { font-size:.68rem; padding:1px 7px; border-radius:10px; background:var(--surface-2); font-weight:700; color:var(--text-3); }
.pgm-fbtn.active .badge { background:rgba(255,255,255,.2); color:#fff; }
.pgm-subfilters { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
.pgm-subfilter { display:flex; align-items:center; gap:5px; font-size:.75rem; color:var(--text-2); }
.pgm-subfilter select { padding:5px 10px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:.75rem; font-family:var(--font); cursor:pointer; transition:border-color .15s; }
.pgm-subfilter select:focus { outline:none; border-color:var(--accent); }
.pgm-subfilter i { font-size:.7rem; color:var(--text-3); }
.pgm-toolbar-r { display:flex; align-items:center; gap:10px; }
.pgm-search { position:relative; }
.pgm-search input { padding:8px 12px 8px 34px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); color:var(--text); font-size:.82rem; width:220px; font-family:var(--font); transition:all .2s var(--ease); }
.pgm-search input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-bg); width:250px; }
.pgm-search input::placeholder { color:var(--text-3); }
.pgm-search i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-3); font-size:.75rem; }

/* ═══ BUTTONS ═══ */
.pgm-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:var(--radius); font-size:.82rem; font-weight:600; cursor:pointer; border:none; transition:all .15s var(--ease); font-family:var(--font); text-decoration:none; line-height:1.3; }
.pgm-btn-primary { background:var(--accent); color:#fff; box-shadow:0 1px 4px rgba(79,70,229,.18); }
.pgm-btn-primary:hover { background:#4338ca; transform:translateY(-1px); box-shadow:0 3px 12px rgba(79,70,229,.22); color:#fff; }
.pgm-btn-outline { background:var(--surface); color:var(--text-2); border:1px solid var(--border); }
.pgm-btn-outline:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-bg); }
.pgm-btn-sm { padding:5px 12px; font-size:.75rem; }
.pgm-btn-danger { background:var(--red-bg); color:var(--red); border:1px solid rgba(220,38,38,.12); }
.pgm-btn-danger:hover { background:var(--red); color:#fff; }

/* ═══ TABLE ═══ */
.pgm-table-wrap { background:var(--surface); border-radius:var(--radius-lg); border:1px solid var(--border); overflow:hidden; }
.pgm-table { width:100%; border-collapse:collapse; }
.pgm-table thead th { padding:11px 14px; font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--text-3); background:var(--surface-2); border-bottom:1px solid var(--border); text-align:left; white-space:nowrap; }
.pgm-table tbody tr { border-bottom:1px solid var(--border); transition:background .1s; }
.pgm-table tbody tr:hover { background:var(--accent-bg); }
.pgm-table tbody tr:last-child { border-bottom:none; }
.pgm-table td { padding:12px 14px; font-size:.83rem; color:var(--text); vertical-align:middle; }
.pgm-page-title { font-weight:600; color:var(--text); display:flex; align-items:center; gap:8px; line-height:1.3; }
.pgm-page-title a { color:var(--text); text-decoration:none; transition:color .15s; }
.pgm-page-title a:hover { color:var(--accent); }
.pgm-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:5px; font-size:.58rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
.pgm-badge.file    { background:var(--violet-bg,#f5f3ff); color:var(--violet,#7c3aed); }
.pgm-badge.public  { background:#ecfdf5; color:#059669; }
.pgm-badge.private { background:#fef3c7; color:#d97706; }
.pgm-slug { font-family:var(--mono,monospace); font-size:.73rem; color:var(--text-3); }
.pgm-status { padding:3px 10px; border-radius:12px; font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; display:inline-block; }
.pgm-status.published { background:var(--green-bg); color:var(--green); }
.pgm-status.draft     { background:var(--amber-bg); color:var(--amber); }
.pgm-status.archived  { background:var(--surface-2); color:var(--text-3); }
.pgm-seo { font-weight:700; font-size:.83rem; font-family:var(--font-display); }
.pgm-seo.good { color:var(--green); }
.pgm-seo.ok   { color:var(--amber); }
.pgm-seo.bad  { color:var(--red); }
.pgm-seo.none { color:var(--text-3); }
.pgm-semantic { display:flex; align-items:center; gap:6px; }
.pgm-semantic-bar { width:48px; height:6px; background:var(--surface-2); border-radius:3px; overflow:hidden; flex-shrink:0; }
.pgm-semantic-fill { height:100%; border-radius:3px; transition:width .3s; }
.pgm-semantic-fill.good { background:var(--green); }
.pgm-semantic-fill.ok   { background:var(--amber); }
.pgm-semantic-fill.bad  { background:var(--red); }
.pgm-semantic-val { font-size:.75rem; font-weight:700; font-family:var(--font-display); min-width:28px; }
.pgm-indexed { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:10px; font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; white-space:nowrap; }
.pgm-indexed.yes     { background:#ecfdf5; color:#059669; }
.pgm-indexed.no      { background:var(--red-bg,#fef2f2); color:var(--red); }
.pgm-indexed.pending { background:#fff7ed; color:#ea580c; }
.pgm-indexed.unknown { background:var(--surface-2); color:var(--text-3); }
.pgm-actions { display:flex; gap:3px; justify-content:flex-end; }
.pgm-actions a, .pgm-actions button { width:30px; height:30px; border-radius:var(--radius); display:flex; align-items:center; justify-content:center; color:var(--text-3); background:transparent; border:1px solid transparent; cursor:pointer; transition:all .12s var(--ease); text-decoration:none; font-size:.78rem; }
.pgm-actions a:hover, .pgm-actions button:hover { color:var(--accent); border-color:var(--border); background:var(--accent-bg); }
.pgm-actions button.del:hover { color:var(--red); border-color:rgba(220,38,38,.2); background:var(--red-bg,#fef2f2); }
.pgm-date { font-size:.73rem; color:var(--text-3); white-space:nowrap; }
.pgm-pagination { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-top:1px solid var(--border); font-size:.78rem; color:var(--text-3); }
.pgm-pagination a { padding:6px 12px; border:1px solid var(--border); border-radius:var(--radius); color:var(--text-2); text-decoration:none; font-weight:600; transition:all .15s var(--ease); font-size:.78rem; }
.pgm-pagination a:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-bg); }
.pgm-pagination a.active { background:var(--accent); color:#fff; border-color:var(--accent); }
.pgm-empty { text-align:center; padding:60px 20px; color:var(--text-3); }
.pgm-empty i { font-size:2.5rem; opacity:.2; margin-bottom:12px; display:block; }
.pgm-empty h3 { font-family:var(--font-display); color:var(--text-2); font-size:1rem; font-weight:600; margin-bottom:6px; }
.pgm-empty p { font-size:.85rem; }
.pgm-empty a { color:var(--accent); }
.pgm-flash { padding:12px 18px; border-radius:var(--radius); font-size:.85rem; font-weight:600; margin-bottom:16px; display:flex; align-items:center; gap:8px; animation:pgmFlashIn .3s var(--ease); }
.pgm-flash.success { background:var(--green-bg); color:var(--green); border:1px solid rgba(5,150,105,.12); }
.pgm-flash.error   { background:var(--red-bg,#fef2f2); color:var(--red); border:1px solid rgba(220,38,38,.12); }
@keyframes pgmFlashIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:none} }
.pgm-bulk { display:none; align-items:center; gap:12px; padding:10px 16px; background:var(--accent-bg); border:1px solid rgba(79,70,229,.1); border-radius:var(--radius); margin-bottom:12px; font-size:.78rem; color:var(--accent); font-weight:600; }
.pgm-bulk.active { display:flex; }
.pgm-bulk select { padding:5px 10px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:.75rem; font-family:var(--font); }
.pgm-table input[type="checkbox"] { accent-color:var(--accent); width:14px; height:14px; cursor:pointer; }

/* ═══ MODAL NOUVELLE PAGE (ajout v3.1) ═══ */
.pgm-modal-overlay { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.45); backdrop-filter:blur(4px); align-items:center; justify-content:center; }
.pgm-modal-overlay.open { display:flex; }
.pgm-modal { background:var(--surface,#fff); border-radius:var(--radius-xl,16px); width:100%; max-width:460px; margin:16px; box-shadow:0 24px 60px rgba(0,0,0,.18); overflow:hidden; animation:pgmModalIn .25s cubic-bezier(.34,1.56,.64,1); }
@keyframes pgmModalIn { from{opacity:0;transform:scale(.88)} to{opacity:1;transform:scale(1)} }
.pgm-modal-head { padding:20px 26px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.pgm-modal-head h3 { font-family:var(--font-display); font-size:1.05rem; font-weight:800; color:var(--text); margin:0; display:flex; align-items:center; gap:9px; }
.pgm-modal-head h3 i { color:var(--accent); }
.pgm-modal-close { width:30px; height:30px; border-radius:var(--radius,8px); border:none; background:var(--surface-2); cursor:pointer; font-size:.85rem; color:var(--text-3); transition:all .15s; display:flex; align-items:center; justify-content:center; }
.pgm-modal-close:hover { background:var(--red-bg,#fef2f2); color:var(--red,#dc2626); }
.pgm-modal-body { padding:22px 26px; }
.pgm-modal-label { font-size:.72rem; font-weight:700; color:var(--text-2); text-transform:uppercase; letter-spacing:.05em; margin-bottom:7px; display:block; }
.pgm-modal-input { width:100%; padding:11px 14px; border:2px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text); font-size:.92rem; font-family:var(--font); transition:all .18s; box-sizing:border-box; }
.pgm-modal-input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-bg); }
.pgm-modal-hint { font-size:.72rem; color:var(--text-3); margin-top:7px; display:flex; align-items:flex-start; gap:5px; line-height:1.5; }
.pgm-modal-hint i { color:var(--accent); flex-shrink:0; margin-top:2px; }
.pgm-modal-foot { padding:14px 26px 20px; display:flex; gap:8px; justify-content:flex-end; }
.pgm-modal-cancel { padding:9px 18px; border-radius:var(--radius,8px); border:1px solid var(--border); background:transparent; cursor:pointer; font-size:.82rem; font-weight:600; color:var(--text-2); font-family:var(--font); transition:all .15s; }
.pgm-modal-cancel:hover { border-color:var(--text-2); }
.pgm-modal-submit { padding:9px 22px; border-radius:var(--radius,8px); border:none; cursor:pointer; background:var(--accent); color:#fff; font-size:.82rem; font-weight:700; font-family:var(--font); display:flex; align-items:center; gap:6px; transition:all .18s; box-shadow:0 2px 8px rgba(79,70,229,.25); }
.pgm-modal-submit:hover { background:#4338ca; transform:translateY(-1px); }

/* ═══ RESPONSIVE ═══ */
@media(max-width:1100px) { .pgm-table .col-semantic, .pgm-table .col-indexed { display:none; } }
@media(max-width:900px) { .pgm-banner{flex-direction:column;gap:16px;align-items:flex-start} .pgm-toolbar{flex-direction:column;align-items:flex-start} .pgm-table-wrap{overflow-x:auto} .pgm-actions a,.pgm-actions button{width:26px;height:26px} }
</style>

<div class="pgm-wrap">

    <!-- Flash -->
    <?php if ($flash === 'deleted'): ?>
        <div class="pgm-flash success"><i class="fas fa-check-circle"></i> Page supprimée avec succès</div>
    <?php elseif ($flash === 'created'): ?>
        <div class="pgm-flash success"><i class="fas fa-check-circle"></i> Page créée avec succès</div>
    <?php elseif ($flash === 'error'): ?>
        <div class="pgm-flash error"><i class="fas fa-exclamation-circle"></i> Une erreur est survenue</div>
    <?php endif; ?>

    <!-- Banner -->
    <div class="pgm-banner">
        <div class="pgm-banner-left">
            <h2><i class="fas fa-file-alt"></i> Mes Pages</h2>
            <p>Gérez toutes les pages de votre site — CMS hybride fichiers + base de données</p>
        </div>
        <div class="pgm-stats">
            <div class="pgm-stat">
                <div class="num blue"><?= (int)($stats['total']??0) ?></div>
                <div class="lbl">Total</div>
            </div>
            <div class="pgm-stat">
                <div class="num green"><?= (int)($stats['published']??0) ?></div>
                <div class="lbl">Publiées</div>
            </div>
            <div class="pgm-stat">
                <div class="num amber"><?= (int)($stats['draft']??0) ?></div>
                <div class="lbl">Brouillons</div>
            </div>
            <?php if ($hasSeoScore): ?>
            <div class="pgm-stat" title="Score SEO moyen des pages analysées">
                <div class="num teal"><?= (int)($stats['avg_seo']??0) ?><span style="font-size:.6em;opacity:.6">%</span></div>
                <div class="lbl">SEO Moy.</div>
            </div>
            <?php endif; ?>
            <?php if ($hasSemanticScore || $hasSeoScoresTable): ?>
            <div class="pgm-stat" title="Score sémantique moyen">
                <div class="num violet"><?= (int)($stats['avg_semantic']??0) ?><span style="font-size:.6em;opacity:.6">%</span></div>
                <div class="lbl">Sémantiqu.</div>
            </div>
            <?php endif; ?>
            <?php if ($hasGoogleIndexed): ?>
            <div class="pgm-stat" title="Pages indexées sur Google">
                <div class="num green"><?= (int)($stats['indexed_count']??0) ?></div>
                <div class="lbl">Indexées</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="pgm-toolbar">
        <div class="pgm-filters">
            <?php
            $filters = [
                'all'       => ['icon'=>'fa-layer-group', 'label'=>'Toutes',    'count'=>$stats['total']??0],
                'published' => ['icon'=>'fa-check-circle','label'=>'Publiées',  'count'=>$stats['published']??0],
                'draft'     => ['icon'=>'fa-pencil-alt',  'label'=>'Brouillons','count'=>$stats['draft']??0],
                'archived'  => ['icon'=>'fa-archive',     'label'=>'Archivées', 'count'=>$stats['archived']??0],
            ];
            foreach ($filters as $key => $f):
                $active = ($filterStatus === $key) ? ' active' : '';
                $url = '?page=pages' . ($key !== 'all' ? '&status='.$key : '');
                if ($searchQuery)              $url .= '&q='.urlencode($searchQuery);
                if ($filterIndexed    !== 'all') $url .= '&indexed='.$filterIndexed;
            ?>
            <a href="<?= $url ?>" class="pgm-fbtn<?= $active ?>">
                <i class="fas <?= $f['icon'] ?>"></i>
                <?= $f['label'] ?>
                <span class="badge"><?= (int)$f['count'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="pgm-toolbar-r">
            <form class="pgm-search" method="GET">
                <input type="hidden" name="page" value="pages">
                <?php if ($filterStatus !== 'all'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
                <i class="fas fa-search"></i>
                <input type="text" name="q" placeholder="Rechercher une page..."
                       value="<?= htmlspecialchars($searchQuery) ?>">
            </form>
            <!-- ✅ v3.1 : ouvre le modal au lieu de ?action=create -->
            <button type="button" class="pgm-btn pgm-btn-primary" onclick="PGM.openModal()">
                <i class="fas fa-plus"></i> Nouvelle page
            </button>
        </div>
    </div>

    <!-- Sub-filters -->
    <div class="pgm-subfilters">
        <?php if ($hasGoogleIndexed): ?>
        <div class="pgm-subfilter">
            <i class="fab fa-google"></i>
            <select onchange="PGM.filterBy('indexed',this.value)">
                <option value="all"     <?= $filterIndexed==='all'    ?'selected':'' ?>>Toutes indexations</option>
                <option value="yes"     <?= $filterIndexed==='yes'    ?'selected':'' ?>>✅ Indexée</option>
                <option value="no"      <?= $filterIndexed==='no'     ?'selected':'' ?>>❌ Non indexée</option>
                <option value="pending" <?= $filterIndexed==='pending'?'selected':'' ?>>⏳ En attente</option>
                <option value="unknown" <?= $filterIndexed==='unknown'?'selected':'' ?>>❓ Inconnue</option>
            </select>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bulk bar -->
    <div class="pgm-bulk" id="bulkBar">
        <input type="checkbox" id="bulkSelectAll" onchange="PGM.toggleAll(this.checked)">
        <span id="bulkCount">0</span> sélectionnée(s)
        <select id="bulkAction">
            <option value="">— Action groupée —</option>
            <option value="publish">Publier</option>
            <option value="draft">Mettre en brouillon</option>
            <option value="archive">Archiver</option>
            <option value="delete">Supprimer</option>
        </select>
        <button class="pgm-btn pgm-btn-sm pgm-btn-outline" onclick="PGM.bulkExecute()">
            <i class="fas fa-check"></i> Appliquer
        </button>
    </div>

    <!-- Table -->
    <div class="pgm-table-wrap">
        <?php if (empty($pages)): ?>
        <div class="pgm-empty">
            <i class="fas fa-file-alt"></i>
            <h3>Aucune page trouvée</h3>
            <p>
                <?php if ($searchQuery): ?>
                    Aucun résultat pour « <?= htmlspecialchars($searchQuery) ?> ».
                    <a href="?page=pages">Effacer la recherche</a>
                <?php else: ?>
                    Commencez par créer votre première page.
                <?php endif; ?>
            </p>
        </div>
        <?php else: ?>
        <table class="pgm-table">
            <thead>
                <tr>
                    <th style="width:32px"><input type="checkbox" onchange="PGM.toggleAll(this.checked)"></th>
                    <th>Page</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>SEO</th>
                    <th class="col-semantic">Sémantique</th>
                    <th class="col-indexed">Google</th>
                    <th>Modifié</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pages as $pg):
                $seo      = (int)($pg['seo_score']??0);
                $seoC     = $seo>=70?'good':($seo>=40?'ok':($seo>0?'bad':'none'));
                $semantic = (int)($pg['semantic_score']??0);
                if ($semantic===0 && isset($pg['ss_semantic'])) $semantic=(int)$pg['ss_semantic'];
                $semC     = $semantic>=50?'good':($semantic>=30?'ok':($semantic>0?'bad':'none'));
                $isFile   = !empty($pg['is_file_based']);
                $vis      = $pg['visibility']??'public';
                $idx      = $pg['google_indexed']??'unknown';
                $modDate  = !empty($pg['updated_at'])
                    ? date('d/m/Y',strtotime($pg['updated_at']))
                    : (!empty($pg['created_at']) ? date('d/m/Y',strtotime($pg['created_at'])) : '—');
                $editUrl = "?page=pages&action=edit&id={$pg['id']}";
                $viewUrl = "/{$pg['slug']}";
                $idxMap  = [
                    'yes'     => ['fa-check-circle',  'Indexée',    'yes'],
                    'no'      => ['fa-times-circle',  'Non indexée','no'],
                    'pending' => ['fa-clock',          'En attente', 'pending'],
                    'unknown' => ['fa-question-circle','Inconnue',   'unknown'],
                ];
                [$idxIco,$idxLbl,$idxCls] = $idxMap[$idx]??$idxMap['unknown'];
            ?>
            <tr data-id="<?= (int)$pg['id'] ?>">
                <td><input type="checkbox" class="pgm-cb" value="<?= (int)$pg['id'] ?>" onchange="PGM.updateBulk()"></td>
                <td>
                    <div class="pgm-page-title">
                        <a href="<?= htmlspecialchars($editUrl) ?>"><?= htmlspecialchars($pg['title']) ?></a>
                        <?php if ($isFile): ?>
                        <span class="pgm-badge file"><i class="fas fa-file-code"></i> FICHIER</span>
                        <?php endif; ?>
                    </div>
                    <span class="pgm-slug">/<?= htmlspecialchars($pg['slug']) ?></span>
                </td>
                <td>
                    <span class="pgm-badge <?= $vis ?>">
                        <?php if ($vis==='public'): ?><i class="fas fa-globe"></i> Publique<?php else: ?><i class="fas fa-lock"></i> Privée<?php endif; ?>
                    </span>
                </td>
                <td>
                    <span class="pgm-status <?= $pg['status'] ?>">
                        <?= $pg['status']==='published'?'Publiée':($pg['status']==='draft'?'Brouillon':'Archivée') ?>
                    </span>
                </td>
                <td><span class="pgm-seo <?= $seoC ?>"><?= $seo>0?$seo.'%':'—' ?></span></td>
                <td class="col-semantic">
                    <?php if ($semantic>0): ?>
                    <div class="pgm-semantic">
                        <div class="pgm-semantic-bar"><div class="pgm-semantic-fill <?= $semC ?>" style="width:<?= min(100,$semantic) ?>%"></div></div>
                        <span class="pgm-semantic-val pgm-seo <?= $semC ?>"><?= $semantic ?>%</span>
                    </div>
                    <?php else: ?><span class="pgm-seo none">—</span><?php endif; ?>
                </td>
                <td class="col-indexed">
                    <span class="pgm-indexed <?= $idxCls ?>">
                        <i class="fas <?= $idxIco ?>"></i> <?= $idxLbl ?>
                    </span>
                </td>
                <td><span class="pgm-date"><?= $modDate ?></span></td>
                <td>
                    <div class="pgm-actions">
                        <a href="<?= htmlspecialchars($editUrl) ?>" title="Éditer la page">
                            <i class="fas fa-edit"></i>
                        </a>
                        <!-- Dupliquer (AJAX) -->
                        <button onclick="PGM.duplicate(<?= (int)$pg['id'] ?>)" title="Dupliquer">
                            <i class="fas fa-copy"></i>
                        </button>
                        <!-- Toggle statut (AJAX) -->
                        <button onclick="PGM.toggleStatus(<?= (int)$pg['id'] ?>,'<?= $pg['status'] ?>')"
                                title="<?= $pg['status']==='published'?'Dépublier':'Publier' ?>">
                            <i class="fas <?= $pg['status']==='published'?'fa-eye-slash':'fa-eye' ?>"></i>
                        </button>
                        <!-- Voir live -->
                        <a href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" title="Voir la page">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <!-- Supprimer (AJAX) -->
                        <button class="del" onclick="PGM.deletePage(<?= (int)$pg['id'] ?>,'<?= addslashes(htmlspecialchars($pg['title'])) ?>')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages>1): ?>
        <div class="pgm-pagination">
            <span>Affichage <?= $offset+1 ?>–<?= min($offset+$perPage,$totalFiltered) ?> sur <?= $totalFiltered ?></span>
            <div style="display:flex;gap:4px">
                <?php for ($i=1;$i<=$totalPages;$i++):
                    $pUrl = '?page=pages&p='.$i;
                    if ($filterStatus!=='all')      $pUrl .= '&status='.$filterStatus;
                    if ($filterIndexed!=='all')     $pUrl .= '&indexed='.$filterIndexed;
                    if ($searchQuery)               $pUrl .= '&q='.urlencode($searchQuery);
                ?>
                <a href="<?= $pUrl ?>" class="<?= $i===$currentPage?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

</div><!-- /.pgm-wrap -->

<!-- ═══════════════════════════════════════════════════════════ -->
<!--  MODAL NOUVELLE PAGE (v3.1)                                 -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="pgm-modal-overlay" id="pgmModal" onclick="if(event.target===this)PGM.closeModal()">
    <div class="pgm-modal">
        <div class="pgm-modal-head">
            <h3><i class="fas fa-plus-circle"></i> Nouvelle page</h3>
            <button class="pgm-modal-close" onclick="PGM.closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="pgmCreateForm">
            <input type="hidden" name="_action" value="create_page">
            <div class="pgm-modal-body">
                <label class="pgm-modal-label" for="pgmNewTitle">Titre de la page</label>
                <input type="text" name="new_title" id="pgmNewTitle" class="pgm-modal-input"
                       placeholder="Ex : À propos, Contact, Estimation gratuite…"
                       autocomplete="off">
                <p class="pgm-modal-hint">
                    <i class="fas fa-info-circle"></i>
                    La page est créée en brouillon et vous êtes redirigé directement dans l'éditeur.
                </p>
            </div>
            <div class="pgm-modal-foot">
                <button type="button" class="pgm-modal-cancel" onclick="PGM.closeModal()">Annuler</button>
                <button type="submit" class="pgm-modal-submit">
                    <i class="fas fa-magic"></i> Créer &amp; Ouvrir l'éditeur
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!--  JAVASCRIPT — Actions CRUD v3.1                             -->
<!-- ═══════════════════════════════════════════════════════════ -->
<script>
const PGM = {
    // ✅ v3.1 : API URL corrigée (dossier api/ sous pages/)
    apiUrl: '/admin/modules/content/pages/api/pages.php',

    // ── Modal nouvelle page (v3.1) ──
    openModal() {
        document.getElementById('pgmModal').classList.add('open');
        setTimeout(() => document.getElementById('pgmNewTitle').focus(), 80);
    },
    closeModal() {
        document.getElementById('pgmModal').classList.remove('open');
    },

    // ── Sub-filter navigation ──
    filterBy(key, value) {
        const url = new URL(window.location.href);
        value === 'all' ? url.searchParams.delete(key) : url.searchParams.set(key, value);
        url.searchParams.delete('p');
        window.location.href = url.toString();
    },

    // ── Checkboxes bulk ──
    toggleAll(checked) {
        document.querySelectorAll('.pgm-cb').forEach(cb => cb.checked = checked);
        this.updateBulk();
    },
    updateBulk() {
        const checked = document.querySelectorAll('.pgm-cb:checked');
        const bar = document.getElementById('bulkBar');
        const cnt = document.getElementById('bulkCount');
        bar.classList.toggle('active', checked.length > 0);
        if (cnt) cnt.textContent = checked.length;
    },

    // ── Bulk execute ──
    async bulkExecute() {
        const action = document.getElementById('bulkAction').value;
        if (!action) return;
        const ids = [...document.querySelectorAll('.pgm-cb:checked')].map(cb => parseInt(cb.value));
        if (!ids.length) return;
        if (action === 'delete' && !confirm(`Supprimer ${ids.length} page(s) ?`)) return;
        const actionMap = { publish:'published', draft:'draft', archive:'archived', delete:'delete' };
        try {
            const fd = new FormData();
            if (action === 'delete') {
                fd.append('action', 'bulk_delete');
            } else {
                fd.append('action', 'bulk_status');
                fd.append('status', actionMap[action]);
            }
            fd.append('ids', JSON.stringify(ids));
            const r = await fetch(this.apiUrl, { method:'POST', body:fd });
            const d = await r.json();
            d.success ? location.reload() : alert(d.error || 'Erreur');
        } catch(e) { alert('Erreur réseau'); }
    },

    // ── Supprimer une page (AJAX) ──
    async deletePage(id, title) {
        if (!confirm(`Supprimer « ${title} » ?\nCette action est irréversible.`)) return;
        try {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const r = await fetch(this.apiUrl, { method:'POST', body:fd });
            const d = await r.json();
            if (d.success) {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) { row.style.cssText='opacity:0;transform:translateX(20px);transition:all .3s'; setTimeout(()=>row.remove(),300); }
            } else { alert(d.error || 'Erreur'); }
        } catch(e) { alert('Erreur réseau'); }
    },

    // ── Toggle statut (AJAX) ──
    async toggleStatus(id, currentStatus) {
        const newStatus = currentStatus === 'published' ? 'draft' : 'published';
        try {
            const fd = new FormData();
            fd.append('action', 'toggle_status');
            fd.append('id', id);
            fd.append('status', newStatus);
            const r = await fetch(this.apiUrl, { method:'POST', body:fd });
            const d = await r.json();
            d.success ? location.reload() : alert(d.error || 'Erreur');
        } catch(e) { alert('Erreur réseau'); }
    },

    // ── Dupliquer (AJAX) ──
    async duplicate(id) {
        if (!confirm('Dupliquer cette page ?')) return;
        try {
            const fd = new FormData();
            fd.append('action', 'duplicate');
            fd.append('id', id);
            const r = await fetch(this.apiUrl, { method:'POST', body:fd });
            const d = await r.json();
            d.success ? location.reload() : alert(d.error || 'Erreur');
        } catch(e) { alert('Erreur réseau'); }
    }
};

// Raccourcis clavier
document.addEventListener('keydown', e => { if (e.key === 'Escape') PGM.closeModal(); });
document.getElementById('pgmNewTitle')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('pgmCreateForm').submit(); }
});
</script>
