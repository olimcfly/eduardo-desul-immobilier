<?php
/**
 * ══════════════════════════════════════════════════════════════
 * MODULE ARTICLES — Mon Blog  v2.2
 * /admin/modules/articles/index.php
 * PATCH : affichage garanti Score SEO, Sémantique, Mots
 * ══════════════════════════════════════════════════════════════
 */

// ─── Connexion DB ───
if (!isset($pdo) && !isset($db)) {
    if (!defined('ADMIN_ROUTER')) require_once dirname(dirname(__DIR__)) . '/includes/init.php';
}
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db  = $pdo;

// ─── Détecter table ───
$tableName   = 'articles';
$tableExists = true;
try {
    $pdo->query("SELECT 1 FROM articles LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->query("SELECT 1 FROM blog_articles LIMIT 1");
        $tableName = 'blog_articles';
    } catch (PDOException $e2) {
        $tableExists = false;
    }
}

// ─── Colonnes disponibles ───
$availCols = [];
if ($tableExists) {
    try {
        $availCols = $pdo->query("SHOW COLUMNS FROM `{$tableName}`")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {}
}

// ─── Mapping colonnes réelles ───
$colTitle    = in_array('titre',   $availCols) ? 'titre'   : (in_array('title',   $availCols) ? 'title'   : 'titre');
$colContent  = in_array('contenu', $availCols) ? 'contenu' : (in_array('content', $availCols) ? 'content' : 'contenu');
$hasStatut   = in_array('statut',  $availCols);
$hasStatus   = in_array('status',  $availCols);
$colKeyword  = in_array('focus_keyword', $availCols) ? 'focus_keyword'
             : (in_array('main_keyword', $availCols) ? 'main_keyword' : null);

// ─── Score SEO : accepte score_technique OU seo_score ───
$colSeoScore = null;
if      (in_array('seo_score',       $availCols)) $colSeoScore = 'seo_score';
elseif  (in_array('score_technique', $availCols)) $colSeoScore = 'score_technique';

// ─── Score Sémantique : accepte score_semantique OU semantic_score ───
$colSemantic = null;
if      (in_array('score_semantique', $availCols)) $colSemantic = 'score_semantique';
elseif  (in_array('semantic_score',   $availCols)) $colSemantic = 'semantic_score';

// ─── Autres flags ───
$hasWordCount     = in_array('word_count',     $availCols);
$hasGoogleIndexed = in_array('google_indexed', $availCols);
$hasIsIndexed     = in_array('is_indexed',     $availCols);
$hasCategory      = in_array('category',       $availCols);
$hasIsFeatured    = in_array('is_featured',    $availCols);
$hasUpdatedAt     = in_array('updated_at',     $availCols);

// ─── Table seo_scores externe : vérifier existence ET colonnes ───
$hasSeoScoresTable  = false;
$seoScoresHasSeo    = false;  // colonne seo_score dans seo_scores
$seoScoresHasSemant = false;  // colonne score_semantique dans seo_scores
try {
    $pdo->query("SELECT 1 FROM seo_scores LIMIT 1");
    $hasSeoScoresTable = true;
    // Colonnes réelles de seo_scores
    $ssCols = $pdo->query("SHOW COLUMNS FROM seo_scores")->fetchAll(PDO::FETCH_COLUMN);
    $seoScoresHasSeo    = in_array('seo_score',        $ssCols);
    $seoScoresHasSemant = in_array('score_semantique', $ssCols);
    // Si aucune colonne utile → inutile de joindre
    if (!$seoScoresHasSeo && !$seoScoresHasSemant) $hasSeoScoresTable = false;
} catch (PDOException $e) {}

// ══════════════════════════════════════════════════════════════
// ROUTING : edit / create / delete → edit.php
// ══════════════════════════════════════════════════════════════
$routeAction = $_GET['action'] ?? '';

if (in_array($routeAction, ['edit', 'create', 'delete'])) {
    $editFile = __DIR__ . '/edit.php';
    if (file_exists($editFile)) {
        require $editFile;
        return;
    } else {
        echo '<div style="background:#fee2e2;color:#991b1b;padding:20px;border-radius:10px;margin:20px;font-family:sans-serif;">
            <strong>⚠️ Fichier manquant :</strong> <code>/admin/modules/articles/edit.php</code>
        </div>';
        return;
    }
}

// ─── Filtres URL ───
$filterStatus  = $_GET['status']   ?? 'all';
$filterIndexed = $_GET['indexed']  ?? 'all';
$filterCat     = $_GET['category'] ?? 'all';
$searchQuery   = trim($_GET['q']   ?? '');
$currentPage   = max(1, (int)($_GET['p'] ?? 1));
$perPage       = 25;
$offset        = ($currentPage - 1) * $perPage;

// ─── Catégories ───
$categories = [];
if ($tableExists && $hasCategory) {
    try {
        $categories = $pdo->query(
            "SELECT DISTINCT category FROM `{$tableName}` WHERE category IS NOT NULL AND category != '' ORDER BY category"
        )->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {}
}

// ─── WHERE ───
$where  = [];
$params = [];

if ($filterStatus !== 'all') {
    if ($filterStatus === 'published') {
        $cond = [];
        if ($hasStatus)  { $cond[] = "a.status = ?";  $params[] = 'published'; }
        if ($hasStatut)  { $cond[] = "a.statut = ?";  $params[] = 'publie'; }
        if ($cond) $where[] = '(' . implode(' OR ', $cond) . ')';
    } elseif ($filterStatus === 'draft') {
        $cond = [];
        if ($hasStatus)  { $cond[] = "a.status = ?";  $params[] = 'draft'; }
        if ($hasStatut)  { $cond[] = "a.statut = ?";  $params[] = 'brouillon'; }
        if ($cond) $where[] = '(' . implode(' OR ', $cond) . ')';
    } elseif ($filterStatus === 'archived') {
        if ($hasStatus)  { $where[] = "a.status = ?"; $params[] = 'archived'; }
    }
}
if ($filterIndexed !== 'all' && $hasGoogleIndexed && in_array($filterIndexed, ['yes','no','pending','unknown'])) {
    $where[] = "a.google_indexed = ?"; $params[] = $filterIndexed;
} elseif ($filterIndexed === 'yes' && $hasIsIndexed && !$hasGoogleIndexed) {
    $where[] = "a.is_indexed = 1";
}
if ($filterCat !== 'all' && $hasCategory) {
    $where[] = "a.category = ?"; $params[] = $filterCat;
}
if ($searchQuery !== '') {
    $w  = "(a.`{$colTitle}` LIKE ?";  $params[] = "%{$searchQuery}%";
    $w .= " OR a.slug LIKE ?";        $params[] = "%{$searchQuery}%";
    if ($colKeyword) { $w .= " OR a.`{$colKeyword}` LIKE ?"; $params[] = "%{$searchQuery}%"; }
    $w .= ")";
    $where[] = $w;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ─── Stats globales ───
$stats = [
    'total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0,
    'avg_seo' => 0, 'avg_semantic' => 0, 'indexed_count' => 0,
];
if ($tableExists) {
    try {
        $stats['total'] = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}`")->fetchColumn();

        $pubCond = [];
        if ($hasStatus) $pubCond[] = "status = 'published'";
        if ($hasStatut) $pubCond[] = "statut = 'publie'";
        if ($pubCond) $stats['published'] = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}` WHERE " . implode(' OR ', $pubCond))->fetchColumn();

        $draftCond = [];
        if ($hasStatus) $draftCond[] = "status = 'draft'";
        if ($hasStatut) $draftCond[] = "statut = 'brouillon'";
        if ($draftCond) $stats['draft'] = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}` WHERE " . implode(' OR ', $draftCond))->fetchColumn();

        if ($hasStatus) $stats['archived'] = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}` WHERE status = 'archived'")->fetchColumn();

        if ($colSeoScore)
            $stats['avg_seo'] = (int)$pdo->query("SELECT ROUND(AVG(NULLIF(`{$colSeoScore}`, 0)), 0) FROM `{$tableName}`")->fetchColumn();

        if ($colSemantic)
            $stats['avg_semantic'] = (int)$pdo->query("SELECT ROUND(AVG(NULLIF(`{$colSemantic}`, 0)), 0) FROM `{$tableName}`")->fetchColumn();

        if ($hasGoogleIndexed)
            $stats['indexed_count'] = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}` WHERE google_indexed = 'yes'")->fetchColumn();
        elseif ($hasIsIndexed)
            $stats['indexed_count'] = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}` WHERE is_indexed = 1")->fetchColumn();

    } catch (PDOException $e) {}
}

// ─── Total filtré + articles ───
$totalFiltered = 0;
$articles      = [];
$totalPages    = 1;

if ($tableExists) {
    try {
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM `{$tableName}` a {$whereSQL}");
        $stmtCount->execute($params);
        $totalFiltered = (int) $stmtCount->fetchColumn();
        $totalPages    = max(1, ceil($totalFiltered / $perPage));

        // ── SELECT — alias fixes pour éviter collision de noms ──
        $selectParts = [
            "a.id",
            "a.`{$colTitle}` AS display_title",
            "a.slug",
            "a.created_at",
        ];
        if ($hasUpdatedAt)     $selectParts[] = "a.updated_at";
        if ($hasStatus)        $selectParts[] = "a.status";
        if ($hasStatut)        $selectParts[] = "a.statut";

        // ── Score SEO — alias unifié 'col_seo' ──
        if ($colSeoScore)      $selectParts[] = "a.`{$colSeoScore}` AS col_seo";

        // ── Score Sémantique — alias unifié 'col_semantic' ──
        if ($colSemantic)      $selectParts[] = "a.`{$colSemantic}` AS col_semantic";

        if ($hasWordCount)     $selectParts[] = "a.word_count";
        if ($hasIsIndexed)     $selectParts[] = "a.is_indexed";
        if ($hasGoogleIndexed) $selectParts[] = "a.google_indexed";
        if ($hasCategory)      $selectParts[] = "a.category";
        if ($hasIsFeatured)    $selectParts[] = "a.is_featured";
        if ($colKeyword)       $selectParts[] = "a.`{$colKeyword}` AS main_keyword";

        // ── Table seo_scores externe : uniquement les colonnes vérifiées ──
        if ($hasSeoScoresTable) {
            if ($seoScoresHasSeo)    $selectParts[] = "ss.seo_score        AS ext_seo_score";
            if ($seoScoresHasSemant) $selectParts[] = "ss.score_semantique AS ext_semantic";
        }

        $colsSQL  = implode(', ', $selectParts);
        $joinSQL  = $hasSeoScoresTable
            ? "LEFT JOIN seo_scores ss ON ss.context = 'article' AND ss.entity_id = a.id"
            : "";
        $orderSQL = "ORDER BY a.created_at DESC";

        $stmt = $pdo->prepare("SELECT {$colsSQL} FROM `{$tableName}` a {$joinSQL} {$whereSQL} {$orderSQL} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("[Articles Index] SQL Error: " . $e->getMessage());
        // Décommentez pour debug : echo '<pre style="color:red">'.htmlspecialchars($e->getMessage()).'</pre>';
    }
}

// ─── GMB Counts (batch) ───
$gmbCounts = [];
if (!empty($articles)) {
    $gmbServiceFile = dirname(__DIR__, 3) . '/includes/classes/GmbArticlePostService.php';
    if (file_exists($gmbServiceFile)) {
        try {
            require_once $gmbServiceFile;
            $gmbSvc = new GmbArticlePostService($pdo);
            $articleIds = array_column($articles, 'id');
            $gmbCounts = $gmbSvc->countByArticles($articleIds);
        } catch (Throwable $e) {}
    }
}

// ─── CSRF ───
if (!isset($_SESSION['auth_csrf_token'])) {
    $_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));
}

// ─── Normaliser status ───
function normalizeArticleStatus(array $a): string {
    $s  = $a['status']  ?? '';
    $st = $a['statut']  ?? '';
    if ($s === 'published') return 'published';
    if ($s === 'archived')  return 'archived';
    if ($st === 'publie')   return 'published';
    if ($st === 'brouillon') return 'draft';
    return 'draft';
}

// ─── Récupérer score avec fallback table externe ───
function getScore(array $a, string $colAlias, string $extAlias): int {
    $v = (int)($a[$colAlias] ?? 0);
    if ($v === 0 && isset($a[$extAlias])) $v = (int)$a[$extAlias];
    return $v;
}

$flash = $_GET['msg'] ?? '';
?>

<style>
/* ════════════════════════════════════════════════════════
   ARTICLES MODULE — v2.2  Light Theme
   Corrections : colonnes SEO/Sémantique/Mots toujours visibles
════════════════════════════════════════════════════════ */
.arm-wrap { font-family: var(--font, 'Inter', sans-serif); }

/* ─── Banner ─── */
.arm-banner {
    background: var(--surface, #fff);
    border-radius: var(--radius-xl, 16px);
    padding: 26px 30px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--border, #e5e7eb);
    position: relative;
    overflow: hidden;
}
.arm-banner::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #f59e0b, #ef4444, #8b5cf6);
    opacity: .75;
}
.arm-banner::after {
    content: '';
    position: absolute; top: -40%; right: -5%;
    width: 220px; height: 220px;
    background: radial-gradient(circle, rgba(245,158,11,.05), transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.arm-banner-left { position: relative; z-index: 1; }
.arm-banner-left h2 {
    font-family: var(--font-display, 'Inter', sans-serif);
    font-size: 1.35rem; font-weight: 700;
    color: var(--text, #111827); margin: 0 0 4px;
    display: flex; align-items: center; gap: 10px;
    letter-spacing: -.02em;
}
.arm-banner-left h2 i { font-size: 16px; color: #f59e0b; }
.arm-banner-left p { color: var(--text-2, #6b7280); font-size: 0.85rem; margin: 0; }

.arm-stats { display: flex; gap: 8px; position: relative; z-index: 1; flex-wrap: wrap; }
.arm-stat {
    text-align: center; padding: 10px 16px;
    background: var(--surface-2, #f9fafb); border-radius: var(--radius-lg, 12px);
    border: 1px solid var(--border, #e5e7eb); min-width: 72px;
    transition: all .2s;
}
.arm-stat:hover { border-color: var(--border-h, #d1d5db); box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.arm-stat .num {
    font-family: var(--font-display, 'Inter', sans-serif);
    font-size: 1.45rem; font-weight: 800; line-height: 1; color: var(--text, #111827);
    letter-spacing: -.03em;
}
.arm-stat .num.blue   { color: #3b82f6; }
.arm-stat .num.green  { color: #10b981; }
.arm-stat .num.amber  { color: #f59e0b; }
.arm-stat .num.teal   { color: #0d9488; }
.arm-stat .num.violet { color: #7c3aed; }
.arm-stat .num.rose   { color: #f43f5e; }
.arm-stat .lbl {
    font-size: 0.58rem; color: var(--text-3, #9ca3af);
    text-transform: uppercase; letter-spacing: .06em; font-weight: 600; margin-top: 3px;
}

/* ─── Toolbar ─── */
.arm-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
}
.arm-filters {
    display: flex; gap: 3px;
    background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius, 10px); padding: 3px; flex-wrap: wrap;
}
.arm-fbtn {
    padding: 7px 15px; border: none; background: transparent;
    color: var(--text-2, #6b7280); font-size: 0.78rem; font-weight: 600;
    border-radius: 6px; cursor: pointer; transition: all .15s;
    font-family: var(--font, 'Inter', sans-serif);
    display: flex; align-items: center; gap: 5px; text-decoration: none;
}
.arm-fbtn:hover { color: var(--text, #111827); background: var(--surface-2, #f9fafb); }
.arm-fbtn.active { background: #f59e0b; color: #fff; box-shadow: 0 1px 4px rgba(245,158,11,.25); }
.arm-fbtn .badge {
    font-size: 0.68rem; padding: 1px 7px; border-radius: 10px;
    background: var(--surface-2, #f3f4f6); font-weight: 700; color: var(--text-3, #9ca3af);
}
.arm-fbtn.active .badge { background: rgba(255,255,255,.22); color: #fff; }

.arm-subfilters { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.arm-subfilter { display: flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--text-2, #6b7280); }
.arm-subfilter select {
    padding: 5px 10px; border: 1px solid var(--border, #e5e7eb); border-radius: 6px;
    background: var(--surface, #fff); color: var(--text, #111827); font-size: 0.75rem;
    font-family: var(--font, 'Inter', sans-serif); cursor: pointer;
}
.arm-subfilter select:focus { outline: none; border-color: #f59e0b; }

.arm-toolbar-r { display: flex; align-items: center; gap: 10px; }
.arm-search { position: relative; }
.arm-search input {
    padding: 8px 12px 8px 34px; background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e7eb); border-radius: var(--radius, 10px);
    color: var(--text, #111827); font-size: 0.82rem; width: 220px;
    font-family: var(--font, 'Inter', sans-serif); transition: all .2s;
}
.arm-search input:focus {
    outline: none; border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,.1); width: 250px;
}
.arm-search input::placeholder { color: var(--text-3, #9ca3af); }
.arm-search i {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%); color: var(--text-3, #9ca3af); font-size: 0.75rem;
}

.arm-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: var(--radius, 10px);
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    border: none; transition: all .15s;
    font-family: var(--font, 'Inter', sans-serif); text-decoration: none; line-height: 1.3;
}
.arm-btn-primary { background: #f59e0b; color: #fff; box-shadow: 0 1px 4px rgba(245,158,11,.22); }
.arm-btn-primary:hover { background: #d97706; transform: translateY(-1px); color: #fff; }
.arm-btn-outline { background: var(--surface, #fff); color: var(--text-2, #6b7280); border: 1px solid var(--border, #e5e7eb); }
.arm-btn-outline:hover { border-color: #f59e0b; color: #f59e0b; }
.arm-btn-sm { padding: 5px 12px; font-size: 0.75rem; }

/* ─── Table ─── */
.arm-table-wrap {
    background: var(--surface, #fff);
    border-radius: var(--radius-lg, 12px);
    border: 1px solid var(--border, #e5e7eb); overflow: hidden;
}
.arm-table { width: 100%; border-collapse: collapse; }
.arm-table thead th {
    padding: 11px 14px; font-size: 0.65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; color: var(--text-3, #9ca3af);
    background: var(--surface-2, #f9fafb); border-bottom: 1px solid var(--border, #e5e7eb);
    text-align: left; white-space: nowrap;
}
.arm-table thead th.center { text-align: center; }
.arm-table tbody tr { border-bottom: 1px solid var(--border, #f3f4f6); transition: background .1s; }
.arm-table tbody tr:hover { background: rgba(245,158,11,.02); }
.arm-table tbody tr:last-child { border-bottom: none; }
.arm-table td { padding: 11px 14px; font-size: 0.83rem; color: var(--text, #111827); vertical-align: middle; }
.arm-table td.center { text-align: center; }

/* ─── Cellule Titre ─── */
.arm-article-title { font-weight: 600; color: var(--text, #111827); display: flex; align-items: center; gap: 8px; line-height: 1.3; }
.arm-article-title a { color: var(--text, #111827); text-decoration: none; transition: color .15s; }
.arm-article-title a:hover { color: #f59e0b; }
.arm-slug { font-family: monospace; font-size: 0.72rem; color: var(--text-3, #9ca3af); margin-top: 2px; }

.arm-keyword {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; background: var(--surface-2, #f9fafb);
    border: 1px solid var(--border, #e5e7eb); border-radius: 20px;
    font-size: 0.7rem; font-weight: 600; color: var(--text-2, #6b7280);
    max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.arm-featured {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 7px; background: #fef9c3; border: 1px solid #fde047;
    border-radius: 4px; font-size: 0.58rem; font-weight: 700; color: #a16207;
    text-transform: uppercase; letter-spacing: .04em;
}
.arm-category {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; background: rgba(99,102,241,.07); color: #6366f1;
    border-radius: 5px; font-size: 0.65rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .03em;
    max-width: 110px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.arm-status {
    padding: 3px 10px; border-radius: 12px; font-size: 0.63rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .04em; display: inline-block;
}
.arm-status.published { background: #d1fae5; color: #059669; }
.arm-status.draft     { background: #fef3c7; color: #d97706; }
.arm-status.archived  { background: var(--surface-2, #f3f4f6); color: var(--text-3, #9ca3af); }

/* ═══ SCORES — TOUJOURS AFFICHÉS ═══════════════════════════════
   Trois variantes :
   1. score-pill     → pastille avec valeur + barre dessous
   2. score-circle   → cercle coloré (petite version)
   3. score-dash     → tiret si aucune valeur
══════════════════════════════════════════════════════════════ */
.arm-score-wrap {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    min-width: 54px;
}

/* Cercle de score */
.arm-score-ring {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 800;
    border: 3px solid transparent;
    transition: transform .2s;
    position: relative;
}
.arm-score-ring:hover { transform: scale(1.08); }
.arm-score-ring.excellent { background: #ecfdf5; border-color: #10b981; color: #059669; }
.arm-score-ring.good      { background: #eff6ff; border-color: #3b82f6; color: #2563eb; }
.arm-score-ring.ok        { background: #fefce8; border-color: #f59e0b; color: #d97706; }
.arm-score-ring.bad       { background: #fef2f2; border-color: #ef4444; color: #dc2626; }
.arm-score-ring.none      {
    background: var(--surface-2, #f9fafb);
    border-color: var(--border, #e5e7eb);
    border-style: dashed; color: var(--text-3, #9ca3af);
}

/* Micro-barre sous le cercle */
.arm-score-bar {
    width: 38px; height: 3px; background: var(--border, #e5e7eb);
    border-radius: 2px; overflow: hidden;
}
.arm-score-bar-fill {
    height: 100%; border-radius: 2px;
    transition: width .5s cubic-bezier(.4,0,.2,1);
}
.arm-score-bar-fill.excellent { background: #10b981; }
.arm-score-bar-fill.good      { background: #3b82f6; }
.arm-score-bar-fill.ok        { background: #f59e0b; }
.arm-score-bar-fill.bad       { background: #ef4444; }
.arm-score-bar-fill.none      { background: var(--border, #e5e7eb); }

/* Variante inline (sémantique) */
.arm-semantic-row {
    display: flex; align-items: center; gap: 6px;
}
.arm-semantic-bar { width: 44px; height: 5px; background: var(--border, #e5e7eb); border-radius: 3px; overflow: hidden; flex-shrink: 0; }
.arm-semantic-fill { height: 100%; border-radius: 3px; }
.arm-semantic-fill.excellent { background: #10b981; }
.arm-semantic-fill.good      { background: #3b82f6; }
.arm-semantic-fill.ok        { background: #f59e0b; }
.arm-semantic-fill.bad       { background: #ef4444; }
.arm-semantic-fill.none      { background: var(--border, #e5e7eb); }
.arm-semantic-val {
    font-size: 0.75rem; font-weight: 700;
    min-width: 28px; font-variant-numeric: tabular-nums;
}
.arm-semantic-val.excellent { color: #10b981; }
.arm-semantic-val.good      { color: #3b82f6; }
.arm-semantic-val.ok        { color: #d97706; }
.arm-semantic-val.bad       { color: #dc2626; }
.arm-semantic-val.none      { color: var(--text-3, #9ca3af); }

/* Mots */
.arm-words-cell {
    display: flex; flex-direction: column; align-items: flex-start; gap: 2px;
    min-width: 68px;
}
.arm-words-val {
    font-size: 0.78rem; font-weight: 700; font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.arm-words-val.excellent { color: #10b981; }
.arm-words-val.good      { color: #3b82f6; }
.arm-words-val.ok        { color: #d97706; }
.arm-words-val.bad       { color: #dc2626; }
.arm-words-val.none      { color: var(--text-3, #9ca3af); }
.arm-words-prog {
    width: 100%; height: 3px; background: var(--border, #e5e7eb);
    border-radius: 2px; overflow: hidden;
}
.arm-words-prog-fill {
    height: 100%; border-radius: 2px;
    transition: width .5s;
}
.arm-words-prog-fill.excellent { background: #10b981; }
.arm-words-prog-fill.good      { background: #3b82f6; }
.arm-words-prog-fill.ok        { background: #f59e0b; }
.arm-words-prog-fill.bad       { background: #ef4444; }

/* Indexation */
.arm-indexed {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 10px; font-size: 0.6rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap;
}
.arm-indexed.yes     { background: #ecfdf5; color: #059669; }
.arm-indexed.no      { background: #fef2f2; color: #dc2626; }
.arm-indexed.pending { background: #fff7ed; color: #ea580c; }
.arm-indexed.unknown { background: var(--surface-2, #f3f4f6); color: var(--text-3, #9ca3af); }

.arm-date { font-size: 0.73rem; color: var(--text-3, #9ca3af); white-space: nowrap; }

/* Actions */
.arm-actions { display: flex; gap: 3px; justify-content: flex-end; }
.arm-actions a, .arm-actions button {
    width: 30px; height: 30px; border-radius: var(--radius, 10px);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-3, #9ca3af); background: transparent; border: 1px solid transparent;
    cursor: pointer; transition: all .12s; text-decoration: none; font-size: 0.78rem;
}
.arm-actions a:hover, .arm-actions button:hover { color: #f59e0b; border-color: var(--border, #e5e7eb); background: rgba(245,158,11,.07); }
.arm-actions button.del:hover { color: #dc2626; border-color: rgba(220,38,38,.2); background: #fef2f2; }

/* Bulk */
.arm-bulk {
    display: none; align-items: center; gap: 12px; padding: 10px 16px;
    background: rgba(245,158,11,.06); border: 1px solid rgba(245,158,11,.15);
    border-radius: var(--radius, 10px); margin-bottom: 12px;
    font-size: 0.78rem; color: #d97706; font-weight: 600;
}
.arm-bulk.active { display: flex; }
.arm-bulk select { padding: 5px 10px; border: 1px solid var(--border, #e5e7eb); border-radius: 6px; background: var(--surface, #fff); color: var(--text, #111827); font-size: 0.75rem; }
.arm-table input[type="checkbox"] { accent-color: #f59e0b; width: 14px; height: 14px; cursor: pointer; }

/* Pagination */
.arm-pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; border-top: 1px solid var(--border, #e5e7eb);
    font-size: 0.78rem; color: var(--text-3, #9ca3af);
}
.arm-pagination a {
    padding: 6px 12px; border: 1px solid var(--border, #e5e7eb); border-radius: var(--radius, 10px);
    color: var(--text-2, #6b7280); text-decoration: none; font-weight: 600; transition: all .15s; font-size: 0.78rem;
}
.arm-pagination a:hover { border-color: #f59e0b; color: #f59e0b; }
.arm-pagination a.active { background: #f59e0b; color: #fff; border-color: #f59e0b; }

/* Flash */
.arm-flash {
    padding: 12px 18px; border-radius: var(--radius, 10px); font-size: 0.85rem; font-weight: 600;
    margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
    animation: armFlashIn .3s;
}
.arm-flash.success { background: #d1fae5; color: #059669; border: 1px solid rgba(5,150,105,.12); }
.arm-flash.error   { background: #fef2f2; color: #dc2626; border: 1px solid rgba(220,38,38,.12); }
@keyframes armFlashIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }

/* Empty */
.arm-empty { text-align: center; padding: 60px 20px; color: var(--text-3, #9ca3af); }
.arm-empty i { font-size: 2.5rem; opacity: .2; margin-bottom: 12px; display: block; }
.arm-empty h3 { color: var(--text-2, #6b7280); font-size: 1rem; font-weight: 600; margin-bottom: 6px; }
.arm-empty a { color: #f59e0b; }

/* Responsive */
@media (max-width: 1200px) { .arm-table .col-indexed, .arm-table .col-date-upd { display: none; } }
@media (max-width: 960px)  {
    .arm-banner { flex-direction: column; gap: 16px; align-items: flex-start; }
    .arm-toolbar { flex-direction: column; align-items: flex-start; }
    .arm-table-wrap { overflow-x: auto; }
}
</style>

<div class="arm-wrap">

<?php if ($flash === 'deleted'): ?>
    <div class="arm-flash success"><i class="fas fa-check-circle"></i> Article supprimé avec succès</div>
<?php elseif ($flash === 'created'): ?>
    <div class="arm-flash success"><i class="fas fa-check-circle"></i> Article créé avec succès</div>
<?php elseif ($flash === 'updated'): ?>
    <div class="arm-flash success"><i class="fas fa-check-circle"></i> Article mis à jour</div>
<?php elseif ($flash === 'error'): ?>
    <div class="arm-flash error"><i class="fas fa-exclamation-circle"></i> Une erreur est survenue</div>
<?php endif; ?>

<?php if (!$tableExists): ?>
<div style="background:#fef2f2;border:1px solid rgba(220,38,38,.12);border-radius:12px;padding:28px;text-align:center;color:#dc2626">
    <i class="fas fa-database" style="font-size:2rem;margin-bottom:10px;display:block"></i>
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:6px">Table articles introuvable</h3>
    <p style="font-size:0.83rem;opacity:.75">Vérifiez que la table <code>articles</code> existe dans votre base de données.</p>
</div>
<?php else: ?>

<!-- Banner -->
<div class="arm-banner">
    <div class="arm-banner-left">
        <h2><i class="fas fa-pen-fancy"></i> Mon Blog</h2>
        <p>Articles, contenus SEO et stratégie de contenu pour votre site immobilier</p>
    </div>
    <div class="arm-stats">
        <div class="arm-stat"><div class="num blue"><?= $stats['total'] ?></div><div class="lbl">Total</div></div>
        <div class="arm-stat"><div class="num green"><?= $stats['published'] ?></div><div class="lbl">Publiés</div></div>
        <div class="arm-stat"><div class="num amber"><?= $stats['draft'] ?></div><div class="lbl">Brouillons</div></div>
        <?php if ($colSeoScore): ?>
        <div class="arm-stat" title="Score SEO moyen">
            <div class="num teal"><?= $stats['avg_seo'] ?><span style="font-size:.6em;opacity:.6">%</span></div>
            <div class="lbl">SEO Moy.</div>
        </div>
        <?php endif; ?>
        <?php if ($colSemantic): ?>
        <div class="arm-stat" title="Score sémantique moyen">
            <div class="num violet"><?= $stats['avg_semantic'] ?><span style="font-size:.6em;opacity:.6">%</span></div>
            <div class="lbl">Séma. Moy.</div>
        </div>
        <?php endif; ?>

        <?php if ($stats['indexed_count'] > 0): ?>
        <div class="arm-stat">
            <div class="num teal"><?= $stats['indexed_count'] ?></div>
            <div class="lbl">Indexés</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Toolbar -->
<div class="arm-toolbar">
    <div class="arm-filters">
        <?php
        $filters = [
            'all'       => ['icon' => 'fa-layer-group', 'label' => 'Tous',       'count' => $stats['total']],
            'published' => ['icon' => 'fa-check-circle','label' => 'Publiés',    'count' => $stats['published']],
            'draft'     => ['icon' => 'fa-pencil-alt',  'label' => 'Brouillons', 'count' => $stats['draft']],
            'archived'  => ['icon' => 'fa-archive',     'label' => 'Archivés',   'count' => $stats['archived']],
        ];
        foreach ($filters as $key => $f):
            $active = ($filterStatus === $key) ? ' active' : '';
            $url = '?page=articles' . ($key !== 'all' ? '&status=' . $key : '');
            if ($searchQuery)          $url .= '&q='        . urlencode($searchQuery);
            if ($filterIndexed !== 'all') $url .= '&indexed='  . $filterIndexed;
            if ($filterCat !== 'all')     $url .= '&category=' . urlencode($filterCat);
        ?>
            <a href="<?= $url ?>" class="arm-fbtn<?= $active ?>">
                <i class="fas <?= $f['icon'] ?>"></i> <?= $f['label'] ?>
                <span class="badge"><?= (int)$f['count'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="arm-toolbar-r">
        <form class="arm-search" method="GET">
            <input type="hidden" name="page" value="articles">
            <?php if ($filterStatus !== 'all'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
            <i class="fas fa-search"></i>
            <input type="text" name="q" placeholder="Titre, slug, mot-clé..." value="<?= htmlspecialchars($searchQuery) ?>">
        </form>
        <a href="?page=articles&action=create" class="arm-btn arm-btn-primary"><i class="fas fa-plus"></i> Nouvel article</a>
        <a href="?page=system/settings/ai" class="arm-btn" title="Paramètres IA" style="background:<?= ($aiAvailable??false)?'#faf5ff':'#fff1f1' ?>;border:1.5px solid <?= ($aiAvailable??false)?'#ddd6fe':'#fca5a5' ?>;color:<?= ($aiAvailable??false)?'#7c3aed':'#ef4444' ?>;font-weight:700;gap:6px;padding:9px 13px;">
            <i class="fas fa-robot"></i>
            <?php if (!($aiAvailable??false)): ?><span style="font-size:11px">Config IA</span><?php endif; ?>
            <i class="fas fa-cog" style="font-size:11px;opacity:.6;"></i>
        </a>
    </div>
</div>

<!-- Sub-filters -->
<?php if ($hasGoogleIndexed || ($hasCategory && !empty($categories))): ?>
<div class="arm-subfilters">
    <?php if ($hasGoogleIndexed): ?>
    <div class="arm-subfilter">
        <i class="fab fa-google"></i>
        <select onchange="ARM.filterBy('indexed', this.value)">
            <option value="all"     <?= $filterIndexed==='all'     ? 'selected':'' ?>>Toutes indexations</option>
            <option value="yes"     <?= $filterIndexed==='yes'     ? 'selected':'' ?>>✅ Indexé</option>
            <option value="no"      <?= $filterIndexed==='no'      ? 'selected':'' ?>>❌ Non indexé</option>
            <option value="pending" <?= $filterIndexed==='pending' ? 'selected':'' ?>>⏳ En attente</option>
            <option value="unknown" <?= $filterIndexed==='unknown' ? 'selected':'' ?>>❓ Inconnu</option>
        </select>
    </div>
    <?php endif; ?>
    <?php if ($hasCategory && !empty($categories)): ?>
    <div class="arm-subfilter">
        <i class="fas fa-tag"></i>
        <select onchange="ARM.filterBy('category', this.value)">
            <option value="all" <?= $filterCat==='all' ? 'selected':'' ?>>Toutes catégories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $filterCat===$cat ? 'selected':'' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Bulk actions -->
<div class="arm-bulk" id="armBulkBar">
    <input type="checkbox" id="armSelectAll" onchange="ARM.toggleAll(this.checked)">
    <span id="armBulkCount">0</span> sélectionné(s)
    <select id="armBulkAction">
        <option value="">— Action groupée —</option>
        <option value="publish">Publier</option>
        <option value="draft">Brouillon</option>
        <option value="archive">Archiver</option>
        <option value="delete">Supprimer</option>
    </select>
    <button class="arm-btn arm-btn-sm arm-btn-outline" onclick="ARM.bulkExecute()"><i class="fas fa-check"></i> Appliquer</button>
</div>

<!-- Table -->
<div class="arm-table-wrap">
    <?php if (empty($articles)): ?>
        <div class="arm-empty">
            <i class="fas fa-pen-fancy"></i>
            <h3>Aucun article trouvé</h3>
            <p>
                <?php if ($searchQuery): ?>
                    Aucun résultat pour « <?= htmlspecialchars($searchQuery) ?> ». <a href="?page=articles">Effacer</a>
                <?php else: ?>
                    Rédigez votre premier article de blog.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <table class="arm-table">
            <thead>
                <tr>
                    <th style="width:32px"><input type="checkbox" onchange="ARM.toggleAll(this.checked)"></th>
                    <th>Article</th>
                    <th>Mot-clé</th>
                    <th>Statut</th>
                    <th class="center" title="Score SEO technique">SEO</th>
                    <th class="center" title="Score sémantique">Sémantique</th>
                    <th title="Nombre de mots">Mots</th>
                    <th class="center" title="Posts Google My Business">GMB</th>
                    <?php if ($hasGoogleIndexed || $hasIsIndexed): ?>
                    <th class="col-indexed">Google</th>
                    <?php endif; ?>
                    <th>Date</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a):

                    // ── Status normalisé ──
                    $statusNorm = normalizeArticleStatus($a);

                    // ── Score SEO — utilise alias 'col_seo' + fallback ext ──
                    $seo     = getScore($a, 'col_seo', 'ext_seo_score');
                    $seoClass = $seo >= 80 ? 'excellent' : ($seo >= 60 ? 'good' : ($seo >= 40 ? 'ok' : ($seo > 0 ? 'bad' : 'none')));

                    // ── Score Sémantique — utilise alias 'col_semantic' + fallback ext ──
                    $semantic = getScore($a, 'col_semantic', 'ext_semantic');
                    $semClass = $semantic >= 70 ? 'excellent' : ($semantic >= 50 ? 'good' : ($semantic >= 30 ? 'ok' : ($semantic > 0 ? 'bad' : 'none')));

                    // ── Mots ──
                    $words     = (int)($a['word_count'] ?? 0);
                    // Barre progressive : 0→1500 mots max
                    $wordsMax  = 1500;
                    $wordsPct  = min(100, round($words / $wordsMax * 100));
                    $wordsClass = $words >= 1000 ? 'excellent' : ($words >= 800 ? 'good' : ($words >= 400 ? 'ok' : ($words > 0 ? 'bad' : 'none')));

                    // ── Indexation ──
                    $indexed = $a['google_indexed'] ?? (($a['is_indexed'] ?? false) ? 'yes' : 'unknown');
                    $idxLabels = [
                        'yes'     => ['icon'=>'fa-check-circle',    'label'=>'Indexé',     'cls'=>'yes'],
                        'no'      => ['icon'=>'fa-times-circle',    'label'=>'Non indexé', 'cls'=>'no'],
                        'pending' => ['icon'=>'fa-clock',           'label'=>'En attente', 'cls'=>'pending'],
                        'unknown' => ['icon'=>'fa-question-circle', 'label'=>'Inconnu',    'cls'=>'unknown'],
                    ];
                    $idxInfo = $idxLabels[$indexed] ?? $idxLabels['unknown'];

                    // ── Keyword / catégorie / featured ──
                    $keyword  = $a['main_keyword'] ?? '';
                    $category = $a['category'] ?? '';
                    $featured = !empty($a['is_featured']);

                    // ── Date ──
                    $date = !empty($a['created_at']) ? date('d/m/Y', strtotime($a['created_at'])) : '—';

                    // ── Titre ── (alias display_title)
                    $title   = $a['display_title'] ?? 'Sans titre';
                    $editUrl = "?page=articles&action=edit&id={$a['id']}";
                    $viewUrl = "/blog/" . htmlspecialchars($a['slug'] ?? '');
                    $statusLabels = ['published'=>'Publié','draft'=>'Brouillon','archived'=>'Archivé'];
                ?>
                <tr data-id="<?= (int)$a['id'] ?>">
                    <td><input type="checkbox" class="arm-cb" value="<?= (int)$a['id'] ?>" onchange="ARM.updateBulk()"></td>

                    <!-- Titre + slug + catégorie -->
                    <td>
                        <div class="arm-article-title">
                            <a href="<?= htmlspecialchars($editUrl) ?>"><?= htmlspecialchars($title) ?></a>
                            <?php if ($featured): ?><span class="arm-featured"><i class="fas fa-star"></i> Top</span><?php endif; ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:3px">
                            <span class="arm-slug">/blog/<?= htmlspecialchars($a['slug'] ?? '') ?></span>
                            <?php if ($category): ?>
                                <span class="arm-category"><i class="fas fa-tag"></i> <?= htmlspecialchars($category) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Mot-clé -->
                    <td>
                        <?php if ($keyword): ?>
                            <span class="arm-keyword"><i class="fas fa-key" style="font-size:.6rem;color:#9ca3af;"></i><?= htmlspecialchars($keyword) ?></span>
                        <?php else: ?>
                            <span style="color:var(--text-3,#9ca3af);font-size:.75rem;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Statut -->
                    <td><span class="arm-status <?= $statusNorm ?>"><?= $statusLabels[$statusNorm] ?? $statusNorm ?></span></td>

                    <!-- ═══ Score SEO ═══ -->
                    <td class="center">
                        <div class="arm-score-wrap">
                            <div class="arm-score-ring <?= $seoClass ?>"
                                 title="Score SEO : <?= $seo > 0 ? $seo.'%' : 'Non calculé' ?>">
                                <?= $seo > 0 ? $seo : '—' ?>
                            </div>
                            <div class="arm-score-bar">
                                <div class="arm-score-bar-fill <?= $seoClass ?>"
                                     style="width:<?= min(100, $seo) ?>%"></div>
                            </div>
                        </div>
                    </td>

                    <!-- ═══ Score Sémantique ═══ -->
                    <td class="center">
                        <div class="arm-score-wrap">
                            <div class="arm-semantic-row">
                                <div class="arm-semantic-bar">
                                    <div class="arm-semantic-fill <?= $semClass ?>"
                                         style="width:<?= min(100, $semantic) ?>%"></div>
                                </div>
                                <span class="arm-semantic-val <?= $semClass ?>">
                                    <?= $semantic > 0 ? $semantic.'%' : '—' ?>
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- ═══ Mots ═══ -->
                    <td>
                        <div class="arm-words-cell">
                            <span class="arm-words-val <?= $wordsClass ?>">
                                <?= $words > 0 ? number_format($words, 0, ',', '\u{202F}') . ' mots' : '—' ?>
                            </span>
                            <?php if ($words > 0): ?>
                            <div class="arm-words-prog">
                                <div class="arm-words-prog-fill <?= $wordsClass ?>"
                                     style="width:<?= $wordsPct ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- ═══ GMB Posts ═══ -->
                    <td class="center">
                        <?php
                        $gc = $gmbCounts[(int)$a['id']] ?? null;
                        if ($gc && $gc['total'] > 0):
                            $gmbBadgeBg    = $gc['published'] > 0 ? '#d1fae5' : ($gc['failed'] > 0 ? '#fee2e2' : '#e0e7ff');
                            $gmbBadgeColor = $gc['published'] > 0 ? '#065f46' : ($gc['failed'] > 0 ? '#991b1b' : '#3730a3');
                        ?>
                        <span class="arm-gmb-badge" data-article-id="<?= (int)$a['id'] ?>"
                              onclick="ARM.gmbPopover(this, <?= (int)$a['id'] ?>)"
                              style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:12px;
                                     font-size:11px;font-weight:600;cursor:pointer;transition:all .15s;
                                     background:<?= $gmbBadgeBg ?>;color:<?= $gmbBadgeColor ?>;">
                            <i class="fab fa-google" style="font-size:10px;"></i>
                            <?= $gc['published'] ?>/<?= $gc['total'] ?>
                        </span>
                        <?php else: ?>
                        <span style="color:var(--text-3,#9ca3af);font-size:11px;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Indexation -->
                    <?php if ($hasGoogleIndexed || $hasIsIndexed): ?>
                    <td class="col-indexed">
                        <span class="arm-indexed <?= $idxInfo['cls'] ?>">
                            <i class="fas <?= $idxInfo['icon'] ?>"></i> <?= $idxInfo['label'] ?>
                        </span>
                    </td>
                    <?php endif; ?>

                    <!-- Date -->
                    <td><span class="arm-date"><?= $date ?></span></td>

                    <!-- Actions -->
                    <td>
                        <div class="arm-actions">
                            <a href="<?= htmlspecialchars($editUrl) ?>" title="Modifier"><i class="fas fa-edit"></i></a>
                            <button onclick="ARM.duplicate(<?= (int)$a['id'] ?>)" title="Dupliquer"><i class="fas fa-copy"></i></button>
                            <button onclick="ARM.toggleStatus(<?= (int)$a['id'] ?>, '<?= $statusNorm ?>')"
                                    title="<?= $statusNorm==='published' ? 'Dépublier' : 'Publier' ?>">
                                <i class="fas <?= $statusNorm==='published' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                            </button>
                            <?php if (!empty($a['slug'])): ?>
                            <a href="<?= $viewUrl ?>" target="_blank" title="Voir"><i class="fas fa-external-link-alt"></i></a>
                            <?php endif; ?>
                            <button class="del" onclick="ARM.deleteArticle(<?= (int)$a['id'] ?>, '<?= addslashes(htmlspecialchars($title)) ?>')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="arm-pagination">
            <span>Affichage <?= $offset+1 ?>–<?= min($offset+$perPage,$totalFiltered) ?> sur <?= $totalFiltered ?> articles</span>
            <div style="display:flex;gap:4px">
                <?php for ($i=1; $i<=$totalPages; $i++):
                    $pUrl = '?page=articles&p='.$i;
                    if ($filterStatus!=='all')  $pUrl .= '&status='.$filterStatus;
                    if ($filterIndexed!=='all') $pUrl .= '&indexed='.$filterIndexed;
                    if ($filterCat!=='all')     $pUrl .= '&category='.urlencode($filterCat);
                    if ($searchQuery)            $pUrl .= '&q='.urlencode($searchQuery);
                ?>
                    <a href="<?= $pUrl ?>" class="<?= $i===$currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php endif; ?>
</div>

<script>
const ARM = {
    apiUrl: '/admin/modules/articles/api/articles.php',
    filterBy(key, value) {
        const url = new URL(window.location.href);
        value === 'all' ? url.searchParams.delete(key) : url.searchParams.set(key, value);
        url.searchParams.delete('p');
        window.location.href = url.toString();
    },
    toggleAll(checked) {
        document.querySelectorAll('.arm-cb').forEach(cb => cb.checked = checked);
        this.updateBulk();
    },
    updateBulk() {
        const checked = document.querySelectorAll('.arm-cb:checked');
        const bar = document.getElementById('armBulkBar');
        document.getElementById('armBulkCount').textContent = checked.length;
        bar.classList.toggle('active', checked.length > 0);
    },
    async bulkExecute() {
        const action = document.getElementById('armBulkAction').value;
        if (!action) return;
        const ids = [...document.querySelectorAll('.arm-cb:checked')].map(cb => parseInt(cb.value));
        if (!ids.length) return;
        if (action === 'delete' && !confirm(`Supprimer ${ids.length} article(s) ?`)) return;
        const fd = new FormData();
        fd.append('action', action === 'delete' ? 'bulk_delete' : 'bulk_status');
        if (action !== 'delete') fd.append('status', {publish:'published',draft:'draft',archive:'archived'}[action]);
        fd.append('ids', JSON.stringify(ids));
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Erreur');
    },
    async deleteArticle(id, title) {
        if (!confirm(`Supprimer « ${title} » ?`)) return;
        const fd = new FormData();
        fd.append('action','delete'); fd.append('id', id);
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        if (d.success) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) { row.style.cssText='opacity:0;transform:translateX(20px);transition:all .3s'; setTimeout(()=>row.remove(),300); }
        } else { alert(d.error || 'Erreur'); }
    },
    async toggleStatus(id, currentStatus) {
        const newStatus = currentStatus === 'published' ? 'draft' : 'published';
        const fd = new FormData();
        fd.append('action','toggle_status'); fd.append('id',id); fd.append('status',newStatus);
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Erreur');
    },
    async duplicate(id) {
        if (!confirm('Dupliquer cet article ?')) return;
        const fd = new FormData();
        fd.append('action','duplicate'); fd.append('id',id);
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Erreur');
    },

    // ─── GMB Popover ─────────────────────────────────────
    _gmbPopover: null,
    async gmbPopover(el, articleId) {
        // Fermer le popover existant
        if (this._gmbPopover) { this._gmbPopover.remove(); this._gmbPopover = null; }

        const pop = document.createElement('div');
        pop.className = 'arm-gmb-popover';
        pop.innerHTML = '<div style="padding:12px;text-align:center;"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';

        // Positionner
        const rect = el.getBoundingClientRect();
        pop.style.cssText = `position:fixed;top:${rect.bottom+6}px;left:${rect.left-100}px;z-index:9999;
            background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);
            min-width:280px;max-width:350px;font-size:12px;`;
        document.body.appendChild(pop);
        this._gmbPopover = pop;

        // Fermer au clic extérieur
        const closeHandler = (e) => {
            if (!pop.contains(e.target) && e.target !== el) {
                pop.remove(); this._gmbPopover = null;
                document.removeEventListener('click', closeHandler);
            }
        };
        setTimeout(() => document.addEventListener('click', closeHandler), 10);

        // Charger les données
        try {
            const r = await fetch(`/admin/api/router.php?module=gmb-posts&action=list&article_id=${articleId}`, {
                headers: {'X-Requested-With':'XMLHttpRequest'}
            });
            const d = await r.json();
            if (!d.success || !d.data?.length) {
                pop.innerHTML = '<div style="padding:16px;text-align:center;color:#9ca3af;">Aucun post GMB</div>';
                return;
            }

            const statusMap = {
                published: {bg:'#d1fae5',color:'#065f46',icon:'fa-check-circle',label:'Publié'},
                draft:     {bg:'#e0e7ff',color:'#3730a3',icon:'fa-pencil-alt',label:'Brouillon'},
                pending:   {bg:'#fef3c7',color:'#92400e',icon:'fa-clock',label:'En attente'},
                failed:    {bg:'#fee2e2',color:'#991b1b',icon:'fa-exclamation-triangle',label:'Échec'},
            };

            let html = `<div style="padding:12px 14px 8px;border-bottom:1px solid #f3f4f6;font-weight:600;display:flex;align-items:center;gap:6px;">
                <i class="fab fa-google" style="color:#4285f4;"></i> Posts GMB
                <span style="margin-left:auto;font-size:10px;color:#9ca3af;">${d.counts?.published||0}/${d.counts?.total||0} publiés</span>
            </div>`;

            d.data.forEach(p => {
                const sc = statusMap[p.status] || statusMap.draft;
                const date = p.created_at ? new Date(p.created_at).toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '';
                const preview = (p.post_text || '').substring(0, 120);
                html += `<div style="padding:10px 14px;border-bottom:1px solid #f9fafb;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="background:${sc.bg};color:${sc.color};font-size:10px;font-weight:600;padding:1px 7px;border-radius:8px;">
                            <i class="fas ${sc.icon}"></i> ${sc.label}
                        </span>
                        <span style="color:#9ca3af;font-size:10px;">${date}</span>
                    </div>
                    <div style="color:#6b7280;line-height:1.5;">${preview}${p.post_text?.length>120?'...':''}</div>
                </div>`;
            });

            html += `<div style="padding:8px 14px;">
                <a href="?page=articles&action=edit&id=${articleId}#ae5GmbPanel"
                   style="font-size:11px;color:#6366f1;text-decoration:none;font-weight:600;">
                    <i class="fas fa-arrow-right"></i> Gérer dans l'éditeur
                </a>
            </div>`;

            pop.innerHTML = html;
        } catch(err) {
            pop.innerHTML = '<div style="padding:16px;color:#991b1b;">Erreur chargement</div>';
        }
    }
};
</script>