<?php
/**
 * ══════════════════════════════════════════════════════════════
 * MODULE BIENS IMMOBILIERS — Index Admin  v1.0
 * /admin/modules/immobilier/properties/index.php
 * ÉCOSYSTÈME IMMO LOCAL+
 * ══════════════════════════════════════════════════════════════
 */

// ─── DB ───
if (!isset($pdo) && !isset($db)) {
    if (!defined('ADMIN_ROUTER')) require_once dirname(dirname(dirname(__DIR__))) . '/includes/init.php';
}
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db  = $pdo;

// ─── Routing ───
$routeAction = $_GET['action'] ?? '';
if (in_array($routeAction, ['edit', 'create', 'delete'])) {
    $editFile = __DIR__ . '/edit.php';
    if (file_exists($editFile)) { require $editFile; return; }
}

// ─── Vérification table properties ───
$tableExists = false;
$availCols   = [];
try {
    $pdo->query("SELECT 1 FROM properties LIMIT 1");
    $tableExists = true;
    $availCols   = $pdo->query("SHOW COLUMNS FROM properties")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Table absente — on proposera de la créer
}

// ─── Colonnes disponibles ───
$colTitle      = in_array('titre',        $availCols) ? 'titre'        : 'title';
$colPrice      = in_array('prix',         $availCols) ? 'prix'         : 'price';
$colSurface    = in_array('surface',      $availCols) ? 'surface'      : 'area';
$colType       = in_array('type_bien',    $availCols) ? 'type_bien'    : 'type';
$colStatus     = in_array('statut',       $availCols) ? 'statut'       : (in_array('status', $availCols) ? 'status' : 'statut');
$colTrans      = in_array('transaction',  $availCols) ? 'transaction'  : 'transaction_type';
$colCity       = in_array('ville',        $availCols) ? 'ville'        : 'city';
$colRooms      = in_array('pieces',       $availCols) ? 'pieces'       : 'rooms';
$colRef        = in_array('reference',    $availCols) ? 'reference'    : 'ref';
$hasSlug       = in_array('slug',         $availCols);
$hasFeatured   = in_array('is_featured',  $availCols) || in_array('featured', $availCols);
$colFeatured   = in_array('is_featured',  $availCols) ? 'is_featured'  : 'featured';
$hasPhotos     = in_array('photos',       $availCols) || in_array('images', $availCols);
$colPhotos     = in_array('photos',       $availCols) ? 'photos'       : 'images';
$hasUpdatedAt  = in_array('updated_at',   $availCols);
$hasMandat     = in_array('mandat',       $availCols) || in_array('type_mandat', $availCols);
$colMandat     = in_array('mandat',       $availCols) ? 'mandat'       : 'type_mandat';
$hasDpe        = in_array('dpe',          $availCols) || in_array('classe_energie', $availCols);
$colDpe        = in_array('dpe',          $availCols) ? 'dpe'          : 'classe_energie';

// ─── Filtres URL ───
$filterStatus  = $_GET['status']      ?? 'all';
$filterType    = $_GET['type']        ?? 'all';
$filterTrans   = $_GET['transaction'] ?? 'all';
$searchQuery   = trim($_GET['q']      ?? '');
$currentPage   = max(1, (int)($_GET['p'] ?? 1));
$perPage       = 20;
$offset        = ($currentPage - 1) * $perPage;

// ─── Stats globales ───
$stats = ['total'=>0,'active'=>0,'vendu'=>0,'loue'=>0,'brouillon'=>0,'avg_price'=>0,'featured'=>0];
if ($tableExists) {
    try {
        $stats['total']    = (int)$pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
        // Statuts multiples FR/EN
        $stats['active']   = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE `{$colStatus}` IN ('actif','active','disponible','available')")->fetchColumn();
        $stats['vendu']    = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE `{$colStatus}` IN ('vendu','sold','loue','rented')")->fetchColumn();
        $stats['brouillon']= (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE `{$colStatus}` IN ('brouillon','draft')")->fetchColumn();
        if ($hasFeatured)
            $stats['featured'] = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE `{$colFeatured}` = 1")->fetchColumn();
        $stats['avg_price']= (int)$pdo->query("SELECT ROUND(AVG(NULLIF(`{$colPrice}`,0)),0) FROM properties WHERE `{$colStatus}` NOT IN ('vendu','sold','loue','rented')")->fetchColumn();
    } catch (PDOException $e) {}
}

// ─── WHERE ───
$where  = [];
$params = [];
if ($filterStatus !== 'all') {
    $statusMap = [
        'active'   => ['actif','active','disponible','available'],
        'vendu'    => ['vendu','sold'],
        'loue'     => ['loue','rented'],
        'draft'    => ['brouillon','draft'],
        'archive'  => ['archive','archived'],
    ];
    if (isset($statusMap[$filterStatus])) {
        $placeholders = implode(',', array_fill(0, count($statusMap[$filterStatus]), '?'));
        $where[] = "`{$colStatus}` IN ({$placeholders})";
        foreach ($statusMap[$filterStatus] as $v) $params[] = $v;
    }
}
if ($filterType !== 'all') {
    $where[] = "`{$colType}` = ?"; $params[] = $filterType;
}
if ($filterTrans !== 'all') {
    $where[] = "`{$colTrans}` = ?"; $params[] = $filterTrans;
}
if ($searchQuery !== '') {
    $w  = "(`{$colTitle}` LIKE ?";  $params[] = "%{$searchQuery}%";
    $w .= " OR `{$colCity}` LIKE ?"; $params[] = "%{$searchQuery}%";
    if (in_array($colRef, $availCols)) { $w .= " OR `{$colRef}` LIKE ?"; $params[] = "%{$searchQuery}%"; }
    $w .= ")";
    $where[] = $w;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ─── Types disponibles ───
$types = [];
if ($tableExists && in_array($colType, $availCols)) {
    try {
        $types = $pdo->query("SELECT DISTINCT `{$colType}` FROM properties WHERE `{$colType}` IS NOT NULL AND `{$colType}` != '' ORDER BY `{$colType}`")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {}
}

// ─── Requête principale ───
$totalFiltered = 0;
$properties    = [];
$totalPages    = 1;
if ($tableExists) {
    try {
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM properties {$whereSQL}");
        $stmtCount->execute($params);
        $totalFiltered = (int)$stmtCount->fetchColumn();
        $totalPages    = max(1, ceil($totalFiltered / $perPage));

        $selectParts = [
            "id",
            "`{$colTitle}` AS display_title",
            "`{$colPrice}` AS display_price",
            "`{$colSurface}` AS display_surface",
            "`{$colType}` AS display_type",
            "`{$colStatus}` AS display_status",
            "`{$colTrans}` AS display_transaction",
            "`{$colCity}` AS display_city",
            "`{$colRooms}` AS display_rooms",
            "created_at",
        ];
        if (in_array($colRef, $availCols))   $selectParts[] = "`{$colRef}` AS display_ref";
        if ($hasSlug)      $selectParts[] = "slug";
        if ($hasFeatured)  $selectParts[] = "`{$colFeatured}` AS is_featured";
        if ($hasPhotos)    $selectParts[] = "`{$colPhotos}` AS display_photos";
        if ($hasUpdatedAt) $selectParts[] = "updated_at";
        if ($hasMandat)    $selectParts[] = "`{$colMandat}` AS display_mandat";
        if ($hasDpe)       $selectParts[] = "`{$colDpe}` AS display_dpe";

        $stmt = $pdo->prepare("SELECT " . implode(', ', $selectParts) . " FROM properties {$whereSQL} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("[Properties Index] SQL Error: " . $e->getMessage());
    }
}

// ─── Helpers ───
function normPropertyStatus(string $s): string {
    $s = strtolower(trim($s));
    if (in_array($s, ['actif','active','disponible','available'])) return 'active';
    if (in_array($s, ['vendu','sold']))      return 'vendu';
    if (in_array($s, ['loue','rented']))     return 'loue';
    if (in_array($s, ['brouillon','draft'])) return 'draft';
    if (in_array($s, ['archive','archived']))return 'archive';
    return 'draft';
}
function formatPrice(int $price, string $transaction): string {
    if ($price === 0) return '—';
    $formatted = number_format($price, 0, ',', ' ');
    return $transaction === 'location' ? $formatted . ' €/m.' : $formatted . ' €';
}
function dpeClass(string $dpe): string {
    $map = ['A'=>'dpe-a','B'=>'dpe-b','C'=>'dpe-c','D'=>'dpe-d','E'=>'dpe-e','F'=>'dpe-f','G'=>'dpe-g'];
    return $map[strtoupper($dpe)] ?? '';
}

$flash = $_GET['msg'] ?? '';
?>
<style>
/* ════════════════════════════════════════════════════════════════
   MODULE BIENS IMMOBILIERS — ADMIN  v1.0
   Design cohérent avec le système Eduardo/IMMO LOCAL+
   Palette : #1a4d7a (bleu) / #d4a574 (or) / #f9f6f3 (beige)
════════════════════════════════════════════════════════════════ */
:root {
    --immo-blue:  #1a4d7a;
    --immo-gold:  #d4a574;
    --immo-beige: #f9f6f3;
    --immo-bg:    var(--surface-2, #f9fafb);
    --immo-card:  var(--surface, #fff);
    --immo-border:var(--border, #e5e7eb);
    --immo-text:  var(--text, #111827);
    --immo-text2: var(--text-2, #6b7280);
    --immo-text3: var(--text-3, #9ca3af);
    --immo-r:     var(--radius, 10px);
    --immo-rl:    var(--radius-lg, 12px);
    --immo-rxl:   var(--radius-xl, 16px);
    --font-main:  var(--font, 'Inter', sans-serif);
}

.bim-wrap { font-family: var(--font-main); }

/* ── Banner ── */
.bim-banner {
    background: linear-gradient(135deg, var(--immo-blue) 0%, #0f3356 100%);
    border-radius: var(--immo-rxl);
    padding: 28px 32px;
    margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between;
    position: relative; overflow: hidden; flex-wrap: wrap; gap: 20px;
}
.bim-banner::before {
    content: '';
    position: absolute; top: -50%; right: -5%;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(212,165,116,.12), transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.bim-banner::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--immo-gold), transparent 60%);
}
.bim-banner-left { position: relative; z-index: 1; }
.bim-banner-left h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.45rem; font-weight: 700; color: #fff;
    margin: 0 0 5px; display: flex; align-items: center; gap: 10px;
    letter-spacing: -.01em;
}
.bim-banner-left h2 i { font-size: 1.1rem; color: var(--immo-gold); }
.bim-banner-left p { color: rgba(255,255,255,.6); font-size: 0.83rem; margin: 0; }

/* ── Stats banner ── */
.bim-stats { display: flex; gap: 8px; position: relative; z-index: 1; flex-wrap: wrap; }
.bim-stat {
    text-align: center; padding: 10px 18px;
    background: rgba(255,255,255,.1); border-radius: var(--immo-rl);
    border: 1px solid rgba(255,255,255,.12); min-width: 78px;
    backdrop-filter: blur(4px); transition: all .2s;
}
.bim-stat:hover { background: rgba(255,255,255,.16); }
.bim-stat .num {
    font-size: 1.5rem; font-weight: 800; line-height: 1; color: #fff;
    letter-spacing: -.04em; font-variant-numeric: tabular-nums;
}
.bim-stat .num.gold  { color: var(--immo-gold); }
.bim-stat .num.green { color: #6ee7b7; }
.bim-stat .num.rose  { color: #fca5a5; }
.bim-stat .num.sky   { color: #7dd3fc; }
.bim-stat .lbl { font-size: 0.58rem; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; margin-top: 3px; }

/* ── Toolbar ── */
.bim-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px; flex-wrap: wrap; gap: 10px;
}
.bim-filters {
    display: flex; gap: 3px;
    background: var(--immo-card); border: 1px solid var(--immo-border);
    border-radius: var(--immo-r); padding: 3px; flex-wrap: wrap;
}
.bim-fbtn {
    padding: 7px 14px; border: none; background: transparent;
    color: var(--immo-text2); font-size: 0.77rem; font-weight: 600;
    border-radius: 7px; cursor: pointer; transition: all .15s;
    font-family: var(--font-main);
    display: flex; align-items: center; gap: 5px; text-decoration: none;
}
.bim-fbtn:hover { color: var(--immo-text); background: var(--immo-bg); }
.bim-fbtn.active { background: var(--immo-blue); color: #fff; }
.bim-fbtn .cnt {
    font-size: 0.65rem; padding: 1px 6px; border-radius: 10px;
    background: var(--immo-bg); font-weight: 700; color: var(--immo-text3);
}
.bim-fbtn.active .cnt { background: rgba(255,255,255,.2); color: #fff; }

.bim-toolbar-r { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.bim-search { position: relative; }
.bim-search input {
    padding: 8px 12px 8px 34px; background: var(--immo-card);
    border: 1px solid var(--immo-border); border-radius: var(--immo-r);
    color: var(--immo-text); font-size: 0.82rem; width: 200px;
    font-family: var(--font-main); transition: all .2s;
}
.bim-search input:focus { outline: none; border-color: var(--immo-blue); box-shadow: 0 0 0 3px rgba(26,77,122,.08); width: 230px; }
.bim-search i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--immo-text3); font-size: 0.75rem; }

/* ── Sub-filters ── */
.bim-subfilters { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.bim-subfilter { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--immo-text2); }
.bim-subfilter select {
    padding: 5px 10px; border: 1px solid var(--immo-border); border-radius: 6px;
    background: var(--immo-card); color: var(--immo-text); font-size: 0.75rem;
    font-family: var(--font-main); cursor: pointer;
}
.bim-subfilter select:focus { outline: none; border-color: var(--immo-blue); }

/* ── Boutons ── */
.bim-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: var(--immo-r);
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    border: none; transition: all .15s;
    font-family: var(--font-main); text-decoration: none; line-height: 1.3;
}
.bim-btn-primary { background: var(--immo-gold); color: #fff; box-shadow: 0 1px 4px rgba(212,165,116,.3); }
.bim-btn-primary:hover { background: #c0936a; transform: translateY(-1px); color: #fff; }
.bim-btn-blue { background: var(--immo-blue); color: #fff; }
.bim-btn-blue:hover { background: #0f3356; color: #fff; }
.bim-btn-outline { background: var(--immo-card); color: var(--immo-text2); border: 1px solid var(--immo-border); }
.bim-btn-outline:hover { border-color: var(--immo-blue); color: var(--immo-blue); }
.bim-btn-sm { padding: 5px 12px; font-size: 0.73rem; }

/* ── Grid cartes ── */
.bim-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 16px;
    margin-bottom: 0;
}
.bim-card {
    background: var(--immo-card);
    border-radius: var(--immo-rl);
    border: 1px solid var(--immo-border);
    overflow: hidden; transition: all .2s;
    display: flex; flex-direction: column;
}
.bim-card:hover { border-color: var(--immo-blue); box-shadow: 0 4px 20px rgba(26,77,122,.1); transform: translateY(-2px); }

/* Photo */
.bim-card-photo {
    height: 180px; position: relative; background: var(--immo-bg);
    overflow: hidden; flex-shrink: 0;
}
.bim-card-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.bim-card:hover .bim-card-photo img { transform: scale(1.04); }
.bim-card-photo-placeholder {
    width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    background: linear-gradient(135deg, var(--immo-bg), #f0ebe5);
    color: var(--immo-text3);
}
.bim-card-photo-placeholder i { font-size: 2.5rem; opacity: .25; margin-bottom: 6px; }
.bim-card-photo-placeholder span { font-size: 0.72rem; opacity: .5; }
.bim-card-badges {
    position: absolute; top: 10px; left: 10px; right: 10px;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.bim-badge {
    padding: 3px 10px; border-radius: 20px; font-size: 0.6rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .05em; backdrop-filter: blur(6px);
}
.bim-badge.vente    { background: rgba(26,77,122,.85); color: #fff; }
.bim-badge.location { background: rgba(212,165,116,.9); color: #fff; }
.bim-badge.status-active  { background: rgba(16,185,129,.85); color: #fff; }
.bim-badge.status-vendu   { background: rgba(220,38,38,.85); color: #fff; }
.bim-badge.status-loue    { background: rgba(245,158,11,.85); color: #fff; }
.bim-badge.status-draft   { background: rgba(107,114,128,.85); color: #fff; }
.bim-badge.status-archive { background: rgba(107,114,128,.7); color: #fff; }
.bim-badge.featured { background: #f59e0b; color: #fff; padding: 3px 7px; }

.bim-photo-count {
    position: absolute; bottom: 8px; right: 8px;
    background: rgba(0,0,0,.55); color: #fff; border-radius: 6px;
    font-size: 0.65rem; font-weight: 700; padding: 2px 8px;
    display: flex; align-items: center; gap: 4px;
}

/* Corps carte */
.bim-card-body { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 6px; }

.bim-card-price {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.3rem; font-weight: 700; color: var(--immo-blue);
    letter-spacing: -.02em;
}
.bim-card-title {
    font-size: 0.88rem; font-weight: 600; color: var(--immo-text);
    line-height: 1.35; display: -webkit-box;
    -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.bim-card-ref {
    font-family: monospace; font-size: 0.68rem; color: var(--immo-text3);
}
.bim-card-meta {
    display: flex; flex-wrap: wrap; gap: 8px; margin-top: 2px;
}
.bim-card-meta-item {
    display: flex; align-items: center; gap: 4px;
    font-size: 0.73rem; color: var(--immo-text2); font-weight: 500;
}
.bim-card-meta-item i { font-size: 0.65rem; color: var(--immo-text3); }

/* DPE */
.bim-dpe {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 1px 7px; border-radius: 4px; font-size: 0.63rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .05em;
}
.dpe-a { background: #059669; color: #fff; }
.dpe-b { background: #34d399; color: #fff; }
.dpe-c { background: #86efac; color: #111; }
.dpe-d { background: #fde68a; color: #92400e; }
.dpe-e { background: #fed7aa; color: #92400e; }
.dpe-f { background: #fca5a5; color: #991b1b; }
.dpe-g { background: #ef4444; color: #fff; }

/* Footer carte */
.bim-card-footer {
    padding: 10px 16px;
    border-top: 1px solid var(--immo-border);
    display: flex; align-items: center; justify-content: space-between;
    gap: 6px;
}
.bim-card-date { font-size: 0.68rem; color: var(--immo-text3); }
.bim-card-actions { display: flex; gap: 4px; }
.bim-card-actions a, .bim-card-actions button {
    width: 30px; height: 30px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: var(--immo-text3); background: transparent; border: 1px solid transparent;
    cursor: pointer; transition: all .12s; text-decoration: none; font-size: 0.78rem;
}
.bim-card-actions a:hover, .bim-card-actions button:hover {
    color: var(--immo-blue); border-color: var(--immo-border); background: rgba(26,77,122,.05);
}
.bim-card-actions button.del:hover { color: #dc2626; border-color: rgba(220,38,38,.2); background: #fef2f2; }
.bim-card-actions a.pub { }
.bim-card-actions a.pub:hover { color: #10b981; border-color: rgba(16,185,129,.2); background: #ecfdf5; }

/* Vue liste ── */
.bim-table-wrap {
    background: var(--immo-card);
    border-radius: var(--immo-rl);
    border: 1px solid var(--immo-border); overflow: hidden;
}
.bim-table { width: 100%; border-collapse: collapse; }
.bim-table thead th {
    padding: 10px 14px; font-size: 0.62rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; color: var(--immo-text3);
    background: var(--immo-bg); border-bottom: 1px solid var(--immo-border);
    text-align: left; white-space: nowrap;
}
.bim-table thead th.center { text-align: center; }
.bim-table tbody tr { border-bottom: 1px solid var(--immo-border); transition: background .1s; }
.bim-table tbody tr:hover { background: rgba(26,77,122,.02); }
.bim-table tbody tr:last-child { border-bottom: none; }
.bim-table td { padding: 10px 14px; font-size: 0.82rem; color: var(--immo-text); vertical-align: middle; }
.bim-table td.center { text-align: center; }

/* Vue toggle */
.bim-view-toggle {
    display: flex; gap: 2px;
    background: var(--immo-bg); border: 1px solid var(--immo-border);
    border-radius: 8px; padding: 3px;
}
.bim-view-btn {
    width: 30px; height: 28px; border: none; background: transparent;
    border-radius: 5px; cursor: pointer; color: var(--immo-text3);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; transition: all .15s;
}
.bim-view-btn.active { background: var(--immo-card); color: var(--immo-blue); box-shadow: 0 1px 3px rgba(0,0,0,.08); }

/* Pagination */
.bim-pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; border-top: 1px solid var(--immo-border);
    font-size: 0.78rem; color: var(--immo-text3);
}
.bim-pagination a {
    padding: 5px 11px; border: 1px solid var(--immo-border); border-radius: 8px;
    color: var(--immo-text2); text-decoration: none; font-weight: 600; transition: all .15s; font-size: 0.78rem;
}
.bim-pagination a:hover { border-color: var(--immo-blue); color: var(--immo-blue); }
.bim-pagination a.active { background: var(--immo-blue); color: #fff; border-color: var(--immo-blue); }

/* Flash */
.bim-flash {
    padding: 12px 18px; border-radius: var(--immo-r); font-size: 0.85rem; font-weight: 600;
    margin-bottom: 16px; display: flex; align-items: center; gap: 8px; animation: bimFlashIn .3s;
}
.bim-flash.success { background: #d1fae5; color: #059669; border: 1px solid rgba(5,150,105,.12); }
.bim-flash.error   { background: #fef2f2; color: #dc2626; border: 1px solid rgba(220,38,38,.12); }
@keyframes bimFlashIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:none; } }

/* Empty */
.bim-empty { text-align: center; padding: 60px 20px; color: var(--immo-text3); }
.bim-empty i { font-size: 3rem; opacity: .15; margin-bottom: 14px; display: block; color: var(--immo-blue); }
.bim-empty h3 { color: var(--immo-text2); font-size: 1rem; font-weight: 600; margin-bottom: 6px; }

/* Erreur table manquante */
.bim-setup {
    background: linear-gradient(135deg, var(--immo-blue), #0f3356);
    border-radius: var(--immo-rxl); padding: 40px;
    text-align: center; color: #fff;
}
.bim-setup h3 { font-family: 'Playfair Display',Georgia,serif; font-size: 1.3rem; margin-bottom: 12px; }
.bim-setup p { color: rgba(255,255,255,.7); font-size: 0.85rem; margin-bottom: 20px; }
.bim-setup code { background: rgba(255,255,255,.1); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }

/* Bulk */
.bim-bulk {
    display: none; align-items: center; gap: 12px; padding: 10px 16px;
    background: rgba(26,77,122,.06); border: 1px solid rgba(26,77,122,.15);
    border-radius: var(--immo-r); margin-bottom: 12px;
    font-size: 0.78rem; color: var(--immo-blue); font-weight: 600;
}
.bim-bulk.active { display: flex; }
.bim-bulk select { padding: 5px 10px; border: 1px solid var(--immo-border); border-radius: 6px; background: var(--immo-card); color: var(--immo-text); font-size: 0.75rem; }

/* Mandat tag */
.bim-mandat { display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; border-radius: 5px; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
.bim-mandat.exclusif { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
.bim-mandat.simple   { background: var(--immo-bg); color: var(--immo-text3); border: 1px solid var(--immo-border); }

@media (max-width: 900px) {
    .bim-banner { flex-direction: column; align-items: flex-start; }
    .bim-toolbar { flex-direction: column; align-items: flex-start; }
    .bim-grid { grid-template-columns: 1fr; }
}
</style>

<div class="bim-wrap">

<?php if ($flash === 'created'): ?><div class="bim-flash success"><i class="fas fa-check-circle"></i> Bien ajouté avec succès</div><?php endif; ?>
<?php if ($flash === 'updated'): ?><div class="bim-flash success"><i class="fas fa-check-circle"></i> Bien mis à jour</div><?php endif; ?>
<?php if ($flash === 'deleted'): ?><div class="bim-flash success"><i class="fas fa-check-circle"></i> Bien supprimé</div><?php endif; ?>
<?php if ($flash === 'error'):   ?><div class="bim-flash error"><i class="fas fa-exclamation-circle"></i> Une erreur est survenue</div><?php endif; ?>

<?php if (!$tableExists): ?>
<!-- ── Pas de table → Setup ── -->
<div class="bim-setup">
    <i class="fas fa-home" style="font-size:3rem;opacity:.3;margin-bottom:16px;display:block;color:#d4a574"></i>
    <h3>Module Biens Immobiliers</h3>
    <p>La table <code>properties</code> n'existe pas encore dans votre base de données.<br>Créez-la via phpMyAdmin ou exécutez le script SQL ci-dessous.</p>
    <button onclick="BIM.createTable()" class="bim-btn bim-btn-primary"><i class="fas fa-database"></i> Créer la table automatiquement</button>
    <div style="margin-top:16px">
        <a href="?page=system/diagnostic" class="bim-btn bim-btn-outline" style="color:#fff;border-color:rgba(255,255,255,.3)"><i class="fas fa-stethoscope"></i> Diagnostic</a>
    </div>
</div>

<?php else: ?>

<!-- ── Banner ── -->
<div class="bim-banner">
    <div class="bim-banner-left">
        <h2><i class="fas fa-home"></i> Biens Immobiliers</h2>
        <p>Gérez votre portefeuille de biens — vente, location, mandats &amp; publications</p>
    </div>
    <div class="bim-stats">
        <div class="bim-stat"><div class="num"><?= $stats['total'] ?></div><div class="lbl">Total</div></div>
        <div class="bim-stat"><div class="num green"><?= $stats['active'] ?></div><div class="lbl">Disponibles</div></div>
        <div class="bim-stat"><div class="num rose"><?= $stats['vendu'] ?></div><div class="lbl">Vendus/Loués</div></div>
        <div class="bim-stat"><div class="num sky"><?= $stats['brouillon'] ?></div><div class="lbl">Brouillons</div></div>
        <?php if ($stats['avg_price'] > 0): ?>
        <div class="bim-stat">
            <div class="num gold"><?= number_format($stats['avg_price'], 0, ',', '&nbsp;') ?></div>
            <div class="lbl">Prix moy. €</div>
        </div>
        <?php endif; ?>
        <?php if ($hasFeatured && $stats['featured'] > 0): ?>
        <div class="bim-stat"><div class="num gold"><?= $stats['featured'] ?></div><div class="lbl">À la une</div></div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Toolbar ── -->
<div class="bim-toolbar">
    <div class="bim-filters">
        <?php
        $flist = [
            'all'     => ['Tous',        $stats['total']],
            'active'  => ['Disponibles', $stats['active']],
            'vendu'   => ['Vendus',      $stats['vendu']],
            'draft'   => ['Brouillons',  $stats['brouillon']],
        ];
        foreach ($flist as $key => [$label, $count]):
            $active = $filterStatus === $key ? ' active' : '';
            $url = '?page=properties&' . ($key !== 'all' ? 'status='.$key : '');
            if ($searchQuery) $url .= '&q='.urlencode($searchQuery);
            if ($filterType !== 'all') $url .= '&type='.urlencode($filterType);
        ?>
        <a href="<?= $url ?>" class="bim-fbtn<?= $active ?>">
            <?= $label ?> <span class="cnt"><?= $count ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="bim-toolbar-r">
        <!-- Recherche -->
        <form class="bim-search" method="GET">
            <input type="hidden" name="page" value="properties">
            <?php if ($filterStatus !== 'all'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
            <i class="fas fa-search"></i>
            <input type="text" name="q" placeholder="Titre, ville, réf..." value="<?= htmlspecialchars($searchQuery) ?>">
        </form>
        <!-- Vue toggle -->
        <div class="bim-view-toggle" id="bimViewToggle">
            <button class="bim-view-btn active" id="btnGrid" onclick="BIM.setView('grid')" title="Vue grille"><i class="fas fa-th-large"></i></button>
            <button class="bim-view-btn" id="btnList" onclick="BIM.setView('list')" title="Vue liste"><i class="fas fa-list"></i></button>
        </div>
        <!-- Nouveau bien -->
        <a href="?page=properties&action=create" class="bim-btn bim-btn-primary"><i class="fas fa-plus"></i> Nouveau bien</a>
        <!-- Lien public -->
        <a href="/biens-immobiliers" target="_blank" class="bim-btn bim-btn-outline" title="Voir la page publique"><i class="fas fa-external-link-alt"></i></a>
    </div>
</div>

<!-- ── Sub-filters ── -->
<div class="bim-subfilters">
    <?php if (!empty($types)): ?>
    <div class="bim-subfilter">
        <i class="fas fa-building"></i>
        <select onchange="BIM.filterBy('type', this.value)">
            <option value="all">Tous types</option>
            <?php foreach ($types as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= $filterType === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="bim-subfilter">
        <i class="fas fa-exchange-alt"></i>
        <select onchange="BIM.filterBy('transaction', this.value)">
            <option value="all">Vente &amp; Location</option>
            <option value="vente"    <?= $filterTrans === 'vente'    ? 'selected' : '' ?>>Vente</option>
            <option value="location" <?= $filterTrans === 'location' ? 'selected' : '' ?>>Location</option>
        </select>
    </div>
</div>

<!-- ── Bulk actions ── -->
<div class="bim-bulk" id="bimBulkBar">
    <span id="bimBulkCount">0</span> sélectionné(s)
    <select id="bimBulkAction">
        <option value="">— Action groupée —</option>
        <option value="publish">Publier</option>
        <option value="draft">Brouillon</option>
        <option value="archive">Archiver</option>
        <option value="delete">Supprimer</option>
    </select>
    <button class="bim-btn bim-btn-sm bim-btn-outline" onclick="BIM.bulkExecute()"><i class="fas fa-check"></i> Appliquer</button>
</div>

<!-- ══════════════════════════════════════════
     VUE GRILLE (défaut)
══════════════════════════════════════════ -->
<div id="bimViewGrid">
<?php if (empty($properties)): ?>
    <div class="bim-empty" style="background:var(--immo-card);border-radius:var(--immo-rl);border:1px solid var(--immo-border);padding:60px 20px">
        <i class="fas fa-home"></i>
        <h3>Aucun bien trouvé</h3>
        <p><?= $searchQuery ? 'Aucun résultat pour « '.htmlspecialchars($searchQuery).' »' : 'Ajoutez votre premier bien immobilier.' ?></p>
        <a href="?page=properties&action=create" class="bim-btn bim-btn-primary" style="margin-top:12px"><i class="fas fa-plus"></i> Ajouter un bien</a>
    </div>
<?php else: ?>
<div class="bim-grid">
<?php foreach ($properties as $p):
    $statusNorm = normPropertyStatus($p['display_status'] ?? '');
    $transaction = strtolower($p['display_transaction'] ?? 'vente');
    $price   = formatPrice((int)($p['display_price'] ?? 0), $transaction);
    $surface = $p['display_surface'] ?? 0;
    $rooms   = $p['display_rooms'] ?? 0;
    $city    = $p['display_city'] ?? '';
    $ref     = $p['display_ref'] ?? '';
    $type    = $p['display_type'] ?? '';
    $title   = $p['display_title'] ?? 'Sans titre';
    $dpe     = strtoupper($p['display_dpe'] ?? '');
    $mandat  = strtolower($p['display_mandat'] ?? '');
    $isFeatured = !empty($p['is_featured']);
    $date    = !empty($p['created_at']) ? date('d/m/Y', strtotime($p['created_at'])) : '—';
    $editUrl = "?page=properties&action=edit&id={$p['id']}";

    // Photo
    $firstPhoto = '';
    if (!empty($p['display_photos'])) {
        $photos = json_decode($p['display_photos'], true);
        if (is_array($photos) && !empty($photos)) $firstPhoto = $photos[0];
        elseif (is_string($p['display_photos']) && !empty($p['display_photos'])) $firstPhoto = $p['display_photos'];
        $photoCount = is_array($photos) ? count($photos) : 1;
    } else { $photoCount = 0; }

    $statusLabels = ['active'=>'Disponible','vendu'=>'Vendu','loue'=>'Loué','draft'=>'Brouillon','archive'=>'Archivé'];
    $statusLabel = $statusLabels[$statusNorm] ?? $statusNorm;
?>
<div class="bim-card" data-id="<?= (int)$p['id'] ?>">
    <!-- Photo -->
    <div class="bim-card-photo">
        <?php if ($firstPhoto): ?>
            <img src="<?= htmlspecialchars($firstPhoto) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy">
        <?php else: ?>
            <div class="bim-card-photo-placeholder">
                <i class="fas fa-home"></i>
                <span>Aucune photo</span>
            </div>
        <?php endif; ?>
        <div class="bim-card-badges">
            <div style="display:flex;gap:4px;flex-wrap:wrap">
                <span class="bim-badge <?= $transaction ?>"><?= $transaction === 'location' ? 'Location' : 'Vente' ?></span>
                <span class="bim-badge status-<?= $statusNorm ?>"><?= $statusLabel ?></span>
                <?php if ($isFeatured): ?><span class="bim-badge featured"><i class="fas fa-star"></i></span><?php endif; ?>
            </div>
            <?php if ($dpe && isset(${'dpe_'.strtolower($dpe)}) === false && strlen($dpe) === 1): ?>
            <span class="bim-dpe <?= dpeClass($dpe) ?>">DPE <?= htmlspecialchars($dpe) ?></span>
            <?php endif; ?>
        </div>
        <?php if ($photoCount > 1): ?>
        <div class="bim-photo-count"><i class="fas fa-images"></i> <?= $photoCount ?></div>
        <?php endif; ?>
    </div>

    <!-- Corps -->
    <div class="bim-card-body">
        <div class="bim-card-price"><?= $price ?></div>
        <div class="bim-card-title"><?= htmlspecialchars($title) ?></div>
        <?php if ($ref): ?><div class="bim-card-ref">Réf. <?= htmlspecialchars($ref) ?></div><?php endif; ?>
        <div class="bim-card-meta">
            <?php if ($surface): ?>
            <span class="bim-card-meta-item"><i class="fas fa-ruler-combined"></i> <?= $surface ?> m²</span>
            <?php endif; ?>
            <?php if ($rooms): ?>
            <span class="bim-card-meta-item"><i class="fas fa-door-open"></i> <?= $rooms ?> pièces</span>
            <?php endif; ?>
            <?php if ($city): ?>
            <span class="bim-card-meta-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($city) ?></span>
            <?php endif; ?>
            <?php if ($type): ?>
            <span class="bim-card-meta-item"><i class="fas fa-tag"></i> <?= htmlspecialchars($type) ?></span>
            <?php endif; ?>
        </div>
        <?php if ($mandat): ?>
        <div>
            <span class="bim-mandat <?= $mandat === 'exclusif' ? 'exclusif' : 'simple' ?>">
                <i class="fas <?= $mandat === 'exclusif' ? 'fa-shield-alt' : 'fa-file-alt' ?>"></i>
                Mandat <?= htmlspecialchars($mandat) ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="bim-card-footer">
        <span class="bim-card-date"><i class="fas fa-calendar-alt" style="margin-right:3px;opacity:.5"></i><?= $date ?></span>
        <div class="bim-card-actions">
            <a href="<?= htmlspecialchars($editUrl) ?>" title="Modifier"><i class="fas fa-edit"></i></a>
            <button onclick="BIM.toggleFeatured(<?= (int)$p['id'] ?>)" title="<?= $isFeatured ? 'Retirer de la une' : 'Mettre à la une' ?>"
                    style="color:<?= $isFeatured ? '#f59e0b' : '' ?>">
                <i class="fas fa-star"></i>
            </button>
            <?php if (!empty($p['slug'])): ?>
            <a href="/biens/<?= htmlspecialchars($p['slug'] ?? $p['id']) ?>" target="_blank" class="pub" title="Voir en ligne"><i class="fas fa-external-link-alt"></i></a>
            <?php endif; ?>
            <button onclick="BIM.toggleStatus(<?= (int)$p['id'] ?>, '<?= $statusNorm ?>')"
                    title="<?= $statusNorm === 'active' ? 'Dépublier' : 'Publier' ?>">
                <i class="fas <?= $statusNorm === 'active' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
            </button>
            <button class="del" onclick="BIM.deleteProp(<?= (int)$p['id'] ?>, '<?= addslashes(htmlspecialchars($title)) ?>')" title="Supprimer"><i class="fas fa-trash"></i></button>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div><!-- /grid -->

<?php if ($totalPages > 1): ?>
<div class="bim-pagination" style="background:var(--immo-card);border-radius:0 0 var(--immo-rl) var(--immo-rl);border:1px solid var(--immo-border);border-top:none;margin-top:-1px">
    <span>Affichage <?= $offset+1 ?>–<?= min($offset+$perPage,$totalFiltered) ?> sur <?= $totalFiltered ?> biens</span>
    <div style="display:flex;gap:4px">
    <?php for ($i=1;$i<=$totalPages;$i++):
        $pUrl = '?page=properties&p='.$i;
        if ($filterStatus !== 'all') $pUrl .= '&status='.$filterStatus;
        if ($filterType   !== 'all') $pUrl .= '&type='.$filterType;
        if ($filterTrans  !== 'all') $pUrl .= '&transaction='.$filterTrans;
        if ($searchQuery)             $pUrl .= '&q='.urlencode($searchQuery);
    ?>
    <a href="<?= $pUrl ?>" class="<?= $i===$currentPage ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
</div><!-- /bimViewGrid -->

<!-- ══════════════════════════════════════════
     VUE LISTE (cachée par défaut)
══════════════════════════════════════════ -->
<div id="bimViewList" style="display:none">
<?php if (!empty($properties)): ?>
<div class="bim-table-wrap">
    <table class="bim-table">
        <thead>
            <tr>
                <th><input type="checkbox" onchange="BIM.toggleAll(this.checked)" style="accent-color:var(--immo-blue);width:14px;height:14px;cursor:pointer"></th>
                <th>Bien</th>
                <th>Prix</th>
                <th>Type</th>
                <th>Surface</th>
                <th>Ville</th>
                <th>Statut</th>
                <?php if ($hasDpe): ?><th class="center">DPE</th><?php endif; ?>
                <th>Date</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($properties as $p):
            $statusNorm  = normPropertyStatus($p['display_status'] ?? '');
            $transaction = strtolower($p['display_transaction'] ?? 'vente');
            $price   = formatPrice((int)($p['display_price'] ?? 0), $transaction);
            $title   = $p['display_title'] ?? 'Sans titre';
            $dpe     = strtoupper($p['display_dpe'] ?? '');
            $date    = !empty($p['created_at']) ? date('d/m/Y', strtotime($p['created_at'])) : '—';
            $editUrl = "?page=properties&action=edit&id={$p['id']}";
            $isFeatured = !empty($p['is_featured']);
            $statusLabels = ['active'=>'Disponible','vendu'=>'Vendu','loue'=>'Loué','draft'=>'Brouillon','archive'=>'Archivé'];
            $statusColors = ['active'=>'#10b981','vendu'=>'#ef4444','loue'=>'#f59e0b','draft'=>'#9ca3af','archive'=>'#9ca3af'];
        ?>
        <tr data-id="<?= (int)$p['id'] ?>">
            <td><input type="checkbox" class="bim-cb" value="<?= (int)$p['id'] ?>" onchange="BIM.updateBulk()" style="accent-color:var(--immo-blue);width:14px;height:14px;cursor:pointer"></td>
            <td>
                <div style="font-weight:600;color:var(--immo-text);line-height:1.3"><?= htmlspecialchars($title) ?></div>
                <?php if (!empty($p['display_ref'])): ?>
                <div style="font-size:.68rem;color:var(--immo-text3);font-family:monospace">Réf. <?= htmlspecialchars($p['display_ref']) ?></div>
                <?php endif; ?>
                <?php if ($isFeatured): ?>
                <span style="font-size:.58rem;background:#fef9c3;color:#a16207;padding:1px 6px;border-radius:4px;font-weight:700"><i class="fas fa-star"></i> À la une</span>
                <?php endif; ?>
            </td>
            <td style="font-weight:700;color:var(--immo-blue);font-family:'Playfair Display',Georgia,serif"><?= $price ?></td>
            <td style="font-size:.78rem;color:var(--immo-text2)"><?= htmlspecialchars($p['display_type'] ?? '—') ?></td>
            <td style="font-size:.78rem"><?= $p['display_surface'] ? $p['display_surface'].' m²' : '—' ?></td>
            <td style="font-size:.78rem"><?= htmlspecialchars($p['display_city'] ?? '—') ?></td>
            <td>
                <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;background:<?= $statusColors[$statusNorm] ?? '#9ca3af' ?>20;color:<?= $statusColors[$statusNorm] ?? '#9ca3af' ?>">
                    <?= $statusLabels[$statusNorm] ?? $statusNorm ?>
                </span>
            </td>
            <?php if ($hasDpe): ?>
            <td class="center">
                <?php if ($dpe && strlen($dpe) === 1): ?>
                <span class="bim-dpe <?= dpeClass($dpe) ?>"><?= htmlspecialchars($dpe) ?></span>
                <?php else: ?><span style="color:var(--immo-text3)">—</span><?php endif; ?>
            </td>
            <?php endif; ?>
            <td style="font-size:.72rem;color:var(--immo-text3);white-space:nowrap"><?= $date ?></td>
            <td>
                <div class="bim-card-actions" style="justify-content:flex-end">
                    <a href="<?= htmlspecialchars($editUrl) ?>" title="Modifier"><i class="fas fa-edit"></i></a>
                    <button onclick="BIM.toggleStatus(<?= (int)$p['id'] ?>, '<?= $statusNorm ?>')" title="<?= $statusNorm==='active' ? 'Dépublier' : 'Publier' ?>"><i class="fas <?= $statusNorm==='active' ? 'fa-eye-slash' : 'fa-eye' ?>"></i></button>
                    <button class="del" onclick="BIM.deleteProp(<?= (int)$p['id'] ?>, '<?= addslashes(htmlspecialchars($title)) ?>')" title="Supprimer"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
</div><!-- /bimViewList -->

<?php endif; // tableExists ?>
</div><!-- /bim-wrap -->

<script>
const BIM = {
    apiUrl: '/admin/api/immobilier/properties.php',
    currentView: localStorage.getItem('bim_view') || 'grid',

    init() {
        this.setView(this.currentView, false);
    },

    setView(v, save = true) {
        this.currentView = v;
        if (save) localStorage.setItem('bim_view', v);
        document.getElementById('bimViewGrid').style.display = v === 'grid' ? '' : 'none';
        document.getElementById('bimViewList').style.display = v === 'list' ? '' : 'none';
        document.getElementById('btnGrid').classList.toggle('active', v === 'grid');
        document.getElementById('btnList').classList.toggle('active', v === 'list');
    },

    filterBy(key, value) {
        const url = new URL(window.location.href);
        value === 'all' ? url.searchParams.delete(key) : url.searchParams.set(key, value);
        url.searchParams.delete('p');
        window.location.href = url.toString();
    },

    toggleAll(checked) {
        document.querySelectorAll('.bim-cb').forEach(cb => cb.checked = checked);
        this.updateBulk();
    },

    updateBulk() {
        const checked = document.querySelectorAll('.bim-cb:checked');
        const bar = document.getElementById('bimBulkBar');
        document.getElementById('bimBulkCount').textContent = checked.length;
        bar.classList.toggle('active', checked.length > 0);
    },

    async bulkExecute() {
        const action = document.getElementById('bimBulkAction').value;
        if (!action) return;
        const ids = [...document.querySelectorAll('.bim-cb:checked')].map(cb => parseInt(cb.value));
        if (!ids.length) return;
        if (action === 'delete' && !confirm(`Supprimer ${ids.length} bien(s) ?`)) return;
        const fd = new FormData();
        fd.append('action', action === 'delete' ? 'bulk_delete' : 'bulk_status');
        if (action !== 'delete') {
            const map = {publish:'active',draft:'brouillon',archive:'archive'};
            fd.append('status', map[action] || action);
        }
        fd.append('ids', JSON.stringify(ids));
        const r = await fetch(this.apiUrl, {method:'POST', body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Erreur');
    },

    async deleteProp(id, title) {
        if (!confirm(`Supprimer « ${title} » ?`)) return;
        const fd = new FormData();
        fd.append('action', 'delete'); fd.append('id', id);
        const r = await fetch(this.apiUrl, {method:'POST', body:fd});
        const d = await r.json();
        if (d.success) {
            const el = document.querySelector(`[data-id="${id}"]`);
            if (el) { el.style.cssText = 'opacity:0;transform:scale(.95);transition:all .3s'; setTimeout(()=>el.remove(), 300); }
        } else { alert(d.error || 'Erreur'); }
    },

    async toggleStatus(id, current) {
        const newStatus = current === 'active' ? 'brouillon' : 'actif';
        const fd = new FormData();
        fd.append('action','toggle_status'); fd.append('id',id); fd.append('status',newStatus);
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Erreur');
    },

    async toggleFeatured(id) {
        const fd = new FormData();
        fd.append('action','toggle_featured'); fd.append('id',id);
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Erreur');
    },

    async createTable() {
        if (!confirm('Créer la table properties automatiquement ?')) return;
        const fd = new FormData();
        fd.append('action','create_table');
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error || 'Impossible de créer la table. Faites-le via phpMyAdmin.');
    }
};

document.addEventListener('DOMContentLoaded', () => BIM.init());
</script>