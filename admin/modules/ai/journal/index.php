<?php
/**
 * ══════════════════════════════════════════════════════════════
 *  MODULE JOURNAL ÉDITORIAL v4.0
 *  /admin/modules/ai/journal/index.php
 *
 *  Design unifié IMMO LOCAL+ — même style que captures/articles
 *  4 onglets : Vue Globale · Matrice · Générateur IA · Performance
 *  Réseaux : article, gmb, tiktok, facebook, instagram, linkedin, email
 * ══════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/JournalController.php';

if (!isset($pdo)) {
    echo '<div style="padding:40px;text-align:center;color:#dc2626"><i class="fas fa-exclamation-circle"></i> Erreur : connexion $pdo non disponible</div>';
    return;
}

$jCtrl = new JournalController($pdo);

if (!$jCtrl->tableExists()) { ?>
    <div style="background:var(--surface);border:2px dashed var(--border);border-radius:var(--radius-xl);padding:60px 30px;text-align:center">
        <i class="fas fa-database" style="font-size:3rem;opacity:.2;color:#8b5cf6;margin-bottom:16px;display:block"></i>
        <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:8px">Table <code>editorial_journal</code> introuvable</h3>
        <p style="font-size:0.85rem;color:var(--text-2)">Importez <code>modules/ai/journal/sql/journal.sql</code> dans phpMyAdmin.</p>
    </div>
<?php return; }

// ─── Données ───
$statsGlobal    = $jCtrl->getStatsGlobal();
$statsByChannel = $jCtrl->getStatsByChannel();
$matrixData     = $jCtrl->getMatrixData();
$config         = $jCtrl->getConfig();
$secteurs       = $jCtrl->getSecteurs();
$currentWeek    = JournalController::getCurrentWeek();
$csrfToken      = $_SESSION['auth_csrf_token'] ?? '';

// Indexer stats par canal
$channelStatsMap = [];
foreach ($statsByChannel as $cs) $channelStatsMap[$cs['channel_id']] = $cs;

// Onglet actif
$tab = $_GET['tab'] ?? 'global';

// ─── Filtres vue globale ───
$filterCanal  = $_GET['canal']  ?? 'all';
$filterStatus = $_GET['status'] ?? 'all';
$filterSem    = (int)($_GET['sem'] ?? 0);
$searchQ      = trim($_GET['q'] ?? '');
$currentPage  = max(1, (int)($_GET['p'] ?? 1));
$perPage      = 40;
$offset       = ($currentPage - 1) * $perPage;

// ─── Config canaux ───
$canaux = JournalController::CHANNELS;

// ─── Statuts ───
$statuts = JournalController::STATUSES;

// ─── Récupérer toutes les idées (vue globale) ───
$allItems = []; $totalItems = 0; $totalPages = 1;
if ($tab === 'global') {
    $filters = [];
    if ($filterCanal  !== 'all') $filters['channel_id']     = $filterCanal;
    if ($filterStatus !== 'all') $filters['status']          = $filterStatus;
    if ($filterSem    > 0)       $filters['week_number']     = $filterSem;
    if ($searchQ !== '')         $filters['search']          = $searchQ;

    $allItems   = $jCtrl->getList($filters, $perPage, $offset);
    $totalItems = $jCtrl->countList($filters);
    $totalPages = max(1, ceil($totalItems / $perPage));
}

// ─── Stats globales ───
$total     = (int)($statsGlobal['total']     ?? 0);
$idees     = (int)($statsGlobal['ideas']     ?? 0) + (int)($statsGlobal['planned'] ?? 0);
$enCours   = (int)($statsGlobal['validated'] ?? 0) + (int)($statsGlobal['writing'] ?? 0);
$prets     = (int)($statsGlobal['ready']     ?? 0);
$publies   = (int)($statsGlobal['published'] ?? 0);

// Helper URL
function jUrl(array $overrides = []): string {
    $base = ['page' => 'journal'];
    foreach (['tab','canal','status','sem','q'] as $k) if (isset($_GET[$k])) $base[$k] = $_GET[$k];
    unset($base['p']);
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === 'all' || $v === '') unset($base[$k]);
        else $base[$k] = $v;
    }
    return '?' . http_build_query($base);
}
?>

<!-- ══════════════════════════════════════════════════════ CSS ══ -->
<style>
/* ── Variables locales ── */
.jnl { font-family: var(--font); }

/* ── Banner ── */
.jnl-banner {
    background:var(--surface); border-radius:var(--radius-xl);
    padding:26px 30px; margin-bottom:22px;
    display:flex; align-items:center; justify-content:space-between;
    border:1px solid var(--border); position:relative; overflow:hidden;
}
.jnl-banner::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#8b5cf6,#6366f1,#3b82f6,#0ea5e9,#10b981,#f59e0b,#ef4444);
}
.jnl-banner::after {
    content:''; position:absolute; top:-40%; right:-5%; width:240px; height:240px;
    background:radial-gradient(circle,rgba(139,92,246,.05),transparent 70%);
    border-radius:50%; pointer-events:none;
}
.jnl-banner-l { position:relative; z-index:1; }
.jnl-banner-l h2 {
    font-family:var(--font-display); font-size:1.35rem; font-weight:700;
    color:var(--text); margin:0 0 4px;
    display:flex; align-items:center; gap:10px; letter-spacing:-.02em;
}
.jnl-banner-l h2 i { color:#8b5cf6; font-size:16px; }
.jnl-banner-l p { color:var(--text-2); font-size:0.85rem; margin:0 0 12px; }
.jnl-canal-pills { display:flex; gap:5px; flex-wrap:wrap; }
.jnl-canal-pill {
    display:inline-flex; align-items:center; gap:5px; padding:3px 10px;
    border-radius:20px; font-size:.67rem; font-weight:600; border:1px solid transparent;
}

/* ── Stats ── */
.jnl-stats { display:flex; gap:8px; position:relative; z-index:1; flex-wrap:wrap; }
.jnl-stat {
    text-align:center; padding:10px 16px;
    background:var(--surface-2); border-radius:var(--radius-lg);
    border:1px solid var(--border); min-width:72px; transition:all .2s var(--ease);
}
.jnl-stat:hover { border-color:var(--border-h); box-shadow:var(--shadow-xs); }
.jnl-stat .num {
    font-family:var(--font-display); font-size:1.45rem;
    font-weight:800; line-height:1; letter-spacing:-.03em;
}
.jnl-stat .num.violet { color:#8b5cf6; }
.jnl-stat .num.blue   { color:var(--accent); }
.jnl-stat .num.green  { color:var(--green); }
.jnl-stat .num.amber  { color:#f59e0b; }
.jnl-stat .num.red    { color:#ef4444; }
.jnl-stat .lbl { font-size:.58rem; color:var(--text-3); text-transform:uppercase; letter-spacing:.06em; font-weight:600; margin-top:3px; }

/* ── Onglets ── */
.jnl-tabs {
    display:flex; gap:2px; background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius-lg); padding:4px; margin-bottom:20px; width:fit-content;
}
.jnl-tab {
    padding:8px 18px; border:none; background:transparent; color:var(--text-2);
    font-size:.82rem; font-weight:600; border-radius:var(--radius); cursor:pointer;
    transition:all .15s; font-family:var(--font); display:flex; align-items:center; gap:6px;
}
.jnl-tab:hover { color:var(--text); background:var(--surface-2); }
.jnl-tab.active { background:#8b5cf6; color:#fff; box-shadow:0 2px 8px rgba(139,92,246,.25); }
.jnl-tab .cnt {
    font-size:.65rem; padding:1px 7px; border-radius:10px;
    background:rgba(255,255,255,.2); font-weight:700;
}
.jnl-tab:not(.active) .cnt { background:var(--surface-2); color:var(--text-3); }

/* ── Panels ── */
.jnl-panel { display:none; }
.jnl-panel.active { display:block; }

/* ── Canal pills (filtres) ── */
.jnl-canal-filters { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:16px; }
.jnl-cf-pill {
    display:inline-flex; align-items:center; gap:5px; padding:5px 13px;
    border-radius:20px; font-size:.72rem; font-weight:600;
    border:1px solid var(--border); background:var(--surface);
    color:var(--text-2); text-decoration:none; transition:all .15s; white-space:nowrap;
}
.jnl-cf-pill:hover { border-color:var(--border-h); color:var(--text); box-shadow:var(--shadow-xs); }
.jnl-cf-pill.active { color:#fff!important; border-color:transparent!important; }
.jnl-cf-pill .cnt { font-size:.6rem; padding:1px 6px; border-radius:10px; font-weight:700; background:rgba(255,255,255,.25); }
.jnl-cf-pill:not(.active) .cnt { background:var(--surface-2); color:var(--text-3); }

/* ── Toolbar ── */
.jnl-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:10px; }
.jnl-toolbar-l { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.jnl-filters-bar { display:flex; gap:3px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:3px; }
.jnl-fbtn {
    padding:7px 14px; border:none; background:transparent; color:var(--text-2);
    font-size:.78rem; font-weight:600; border-radius:6px; cursor:pointer; transition:all .15s;
    font-family:var(--font); display:flex; align-items:center; gap:5px; text-decoration:none;
}
.jnl-fbtn:hover { color:var(--text); background:var(--surface-2); }
.jnl-fbtn.active { background:#8b5cf6; color:#fff; box-shadow:0 1px 4px rgba(139,92,246,.25); }
.jnl-fbtn .badge { font-size:.68rem; padding:1px 7px; border-radius:10px; background:var(--surface-2); font-weight:700; color:var(--text-3); }
.jnl-fbtn.active .badge { background:rgba(255,255,255,.22); color:#fff; }
.jnl-reset { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; background:rgba(139,92,246,.05); border:1px solid rgba(139,92,246,.15); border-radius:6px; font-size:.72rem; font-weight:600; color:#8b5cf6; text-decoration:none; }

/* Boutons */
.jnl-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:var(--radius); font-size:.82rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; font-family:var(--font); text-decoration:none; line-height:1.3; }
.jnl-btn-primary { background:#8b5cf6; color:#fff; box-shadow:0 1px 4px rgba(139,92,246,.22); }
.jnl-btn-primary:hover { background:#7c3aed; transform:translateY(-1px); color:#fff; }
.jnl-btn-outline { background:var(--surface); color:var(--text-2); border:1px solid var(--border); }
.jnl-btn-outline:hover { border-color:var(--border-h); background:var(--surface-2); }
.jnl-btn-sm { padding:5px 12px; font-size:.75rem; }
.jnl-btn-green  { background:var(--green); color:#fff; }
.jnl-btn-green:hover  { background:#047857; color:#fff; }

/* Search */
.jnl-toolbar-r { display:flex; align-items:center; gap:10px; }
.jnl-search { position:relative; }
.jnl-search input {
    padding:8px 12px 8px 34px; background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius); color:var(--text); font-size:.82rem; width:220px;
    font-family:var(--font); transition:all .2s;
}
.jnl-search input:focus { outline:none; border-color:#8b5cf6; box-shadow:0 0 0 3px rgba(139,92,246,.1); width:250px; }
.jnl-search input::placeholder { color:var(--text-3); }
.jnl-search i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-3); font-size:.75rem; }

/* ── Bulk bar ── */
.jnl-bulk {
    display:none; align-items:center; gap:12px; padding:10px 16px;
    background:rgba(139,92,246,.04); border:1px solid rgba(139,92,246,.15);
    border-radius:var(--radius); margin-bottom:12px; font-size:.78rem; color:#8b5cf6; font-weight:600;
}
.jnl-bulk.active { display:flex; }
.jnl-bulk select { padding:5px 10px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text); font-size:.75rem; font-family:var(--font); }
.jnl-table input[type="checkbox"] { accent-color:#8b5cf6; width:14px; height:14px; cursor:pointer; }

/* ── Table ── */
.jnl-table-wrap { background:var(--surface); border-radius:var(--radius-lg); border:1px solid var(--border); overflow:hidden; }
.jnl-table { width:100%; border-collapse:collapse; }
.jnl-table thead th {
    padding:11px 14px; font-size:.65rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.05em; color:var(--text-3); background:var(--surface-2);
    border-bottom:1px solid var(--border); text-align:left; white-space:nowrap;
}
.jnl-table tbody tr { border-bottom:1px solid var(--border); transition:background .1s; }
.jnl-table tbody tr:hover { background:rgba(139,92,246,.02); }
.jnl-table tbody tr.jnl-row-curweek { background:rgba(139,92,246,.03); }
.jnl-table tbody tr:last-child { border-bottom:none; }
.jnl-table td { padding:10px 14px; font-size:.83rem; color:var(--text); vertical-align:middle; }

/* Titre */
.jnl-titre { font-weight:600; max-width:300px; }
.jnl-titre a { color:var(--text); text-decoration:none; transition:color .15s; }
.jnl-titre a:hover { color:#8b5cf6; }
.jnl-meta { font-size:.68rem; color:var(--text-3); margin-top:2px; display:flex; align-items:center; gap:4px; }

/* Badges */
.jnl-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:.67rem; font-weight:600; white-space:nowrap; color:#fff; }
.jnl-badge-outline { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:20px; font-size:.67rem; font-weight:600; white-space:nowrap; border:1px solid; background:transparent; }
.jnl-badge-week { font-family:var(--mono); font-size:.7rem; font-weight:700; padding:2px 8px; border-radius:5px; background:var(--surface-2); color:var(--text-3); }
.jnl-badge-week.cur { background:rgba(139,92,246,.12); color:#8b5cf6; }
.jnl-badge-type { font-size:.63rem; font-weight:600; padding:2px 7px; border-radius:5px; background:var(--surface-2); color:var(--text-2); }

/* Icône canal */
.jnl-canal-icon { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; font-size:.75rem; }

/* Actions */
.jnl-actions { display:flex; gap:3px; }
.jnl-actions a, .jnl-actions button {
    width:30px; height:30px; border-radius:var(--radius); display:flex; align-items:center;
    justify-content:center; color:var(--text-3); background:transparent; border:1px solid transparent;
    cursor:pointer; transition:all .12s; text-decoration:none; font-size:.78rem;
}
.jnl-actions a:hover, .jnl-actions button:hover { color:#8b5cf6; border-color:var(--border); background:rgba(139,92,246,.07); }
.jnl-actions .btn-ok:hover   { color:#059669; border-color:rgba(5,150,105,.2);   background:rgba(5,150,105,.06); }
.jnl-actions .btn-go:hover   { color:#2563eb; border-color:rgba(37,99,235,.2);   background:rgba(37,99,235,.06); }
.jnl-actions .btn-del:hover  { color:#dc2626; border-color:rgba(220,38,38,.2);   background:rgba(220,38,38,.06); }

/* ── Canal cards (vue globale overview) ── */
.jnl-ch-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:10px; margin-bottom:22px; }
.jnl-ch-card {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg);
    padding:16px; cursor:pointer; transition:all .2s; position:relative; overflow:hidden;
    text-decoration:none; color:inherit; display:block;
}
.jnl-ch-card:hover { transform:translateY(-2px); box-shadow:var(--shadow); border-color:var(--border-h); }
.jnl-ch-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.jnl-ch-card-head { display:flex; align-items:center; gap:8px; margin-bottom:10px; }
.jnl-ch-card-head i { font-size:14px; }
.jnl-ch-card-head span { font-size:.88rem; font-weight:700; }
.jnl-ch-nums { display:grid; grid-template-columns:1fr 1fr 1fr; gap:4px; text-align:center; }
.jnl-ch-num .v { font-family:var(--font-display); font-size:1.1rem; font-weight:800; }
.jnl-ch-num .l { font-size:.6rem; color:var(--text-3); text-transform:uppercase; letter-spacing:.04em; }
.jnl-ch-bar { height:3px; background:var(--surface-2); border-radius:2px; margin-top:10px; overflow:hidden; }
.jnl-ch-bar-fill { height:100%; border-radius:2px; transition:width .5s; }

/* ── Matrice ── */
.jnl-matrix-wrap { overflow-x:auto; }
.jnl-matrix { width:100%; border-collapse:collapse; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; font-size:.82rem; }
.jnl-matrix th { padding:10px 12px; font-size:.68rem; text-transform:uppercase; font-weight:700; background:var(--surface-2); color:var(--text-3); border-bottom:1px solid var(--border); text-align:center; white-space:nowrap; }
.jnl-matrix th.jnl-matrix-th-left { text-align:left; font-size:.82rem; color:var(--text-2); font-weight:700; }
.jnl-matrix td { padding:12px; text-align:center; border-bottom:1px solid var(--border); border-right:1px solid var(--border-h,.5); }
.jnl-matrix td.jnl-matrix-td-profile { text-align:left; font-weight:600; padding-left:16px; background:var(--surface-2); white-space:nowrap; }
.jnl-matrix-cell { display:flex; flex-direction:column; align-items:center; gap:2px; }
.jnl-matrix-cnt { font-family:var(--font-display); font-size:1.2rem; font-weight:800; }
.jnl-matrix-cnt.empty  { color:var(--text-3); opacity:.4; }
.jnl-matrix-cnt.low    { color:#f59e0b; }
.jnl-matrix-cnt.ok     { color:#3b82f6; }
.jnl-matrix-cnt.good   { color:#10b981; }
.jnl-matrix-pub { font-size:.62rem; color:var(--text-3); }
.jnl-matrix-legend { display:flex; gap:16px; margin-top:12px; font-size:.75rem; color:var(--text-3); flex-wrap:wrap; }
.jnl-matrix-legend span::before { content:'●'; margin-right:4px; }

/* ── Générateur ── */
.jnl-gen-wrap { display:grid; grid-template-columns:380px 1fr; gap:20px; align-items:start; }
.jnl-gen-form {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl);
    padding:24px; position:relative; overflow:hidden;
}
.jnl-gen-form::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#8b5cf6,#6366f1,#3b82f6);
}
.jnl-gen-form h3 { font-family:var(--font-display); font-size:1rem; font-weight:700; margin:0 0 18px; display:flex; align-items:center; gap:8px; }
.jnl-gen-form h3 i { color:#8b5cf6; }
.jnl-gen-row { margin-bottom:14px; }
.jnl-gen-row label { display:block; font-size:.75rem; font-weight:700; color:var(--text-2); margin-bottom:5px; text-transform:uppercase; letter-spacing:.04em; }
.jnl-gen-row select, .jnl-gen-row input {
    width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:var(--radius);
    font-size:.85rem; font-family:var(--font); background:var(--surface); color:var(--text);
    transition:border-color .2s;
}
.jnl-gen-row select:focus, .jnl-gen-row input:focus { outline:none; border-color:#8b5cf6; box-shadow:0 0 0 3px rgba(139,92,246,.1); }
.jnl-gen-personas { display:flex; flex-direction:column; gap:8px; margin-top:4px; }
.jnl-gen-persona { display:flex; align-items:center; gap:8px; padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius); cursor:pointer; transition:all .15s; background:var(--surface); }
.jnl-gen-persona:hover { border-color:#8b5cf6; background:rgba(139,92,246,.04); }
.jnl-gen-persona.selected { border-color:#8b5cf6; background:rgba(139,92,246,.07); }
.jnl-gen-persona input[type="checkbox"] { accent-color:#8b5cf6; }
.jnl-gen-persona .p-avatar { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem; color:#fff; flex-shrink:0; }
.jnl-gen-persona .p-name { font-size:.82rem; font-weight:600; }
.jnl-gen-persona .p-sub { font-size:.7rem; color:var(--text-3); }
.jnl-gen-result-area {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl); padding:24px;
}
.jnl-gen-result-area h3 { font-family:var(--font-display); font-size:1rem; font-weight:700; margin:0 0 16px; display:flex; align-items:center; gap:8px; }
.jnl-gen-preview-item {
    background:var(--surface-2); border:1px solid var(--border); border-radius:var(--radius-lg);
    padding:14px 16px; margin-bottom:8px; transition:all .15s;
}
.jnl-gen-preview-item:hover { border-color:var(--border-h); }
.jnl-gen-preview-item .pi-head { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
.jnl-gen-preview-item .pi-title { font-size:.88rem; font-weight:600; flex:1; }
.jnl-gen-preview-item .pi-meta { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.jnl-gen-preview-item .pi-actions { display:flex; gap:6px; margin-top:8px; }
.jnl-gen-status-bar { padding:12px 16px; border-radius:var(--radius); font-size:.85rem; font-weight:600; display:none; margin-top:14px; }
.jnl-gen-status-bar.ok { display:block; background:var(--green-bg,#d1fae5); color:var(--green,#059669); border:1px solid rgba(5,150,105,.12); }
.jnl-gen-status-bar.err { display:block; background:rgba(220,38,38,.06); color:#dc2626; border:1px solid rgba(220,38,38,.12); }
.jnl-gen-status-bar.loading { display:block; background:rgba(139,92,246,.06); color:#8b5cf6; border:1px solid rgba(139,92,246,.12); }

/* ── Performance ── */
.jnl-perf-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
.jnl-perf-stat { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); padding:20px; text-align:center; }
.jnl-perf-stat .v { font-family:var(--font-display); font-size:2rem; font-weight:800; }
.jnl-perf-stat .l { font-size:.75rem; color:var(--text-3); margin-top:4px; font-weight:500; }
.jnl-pipeline { display:flex; height:32px; border-radius:var(--radius-lg); overflow:hidden; margin-bottom:8px; }
.jnl-pipeline-seg { display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700; color:#fff; transition:width .5s; min-width:0; overflow:hidden; }
.jnl-pipeline-legend { display:flex; gap:14px; font-size:.75rem; color:var(--text-3); flex-wrap:wrap; }
.jnl-perf-ch-table { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; }
.jnl-perf-ch-table table { width:100%; border-collapse:collapse; }
.jnl-perf-ch-table thead th { padding:10px 14px; font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--text-3); background:var(--surface-2); border-bottom:1px solid var(--border); }
.jnl-perf-ch-table tbody td { padding:12px 14px; border-bottom:1px solid var(--border); font-size:.83rem; }
.jnl-perf-ch-table tbody tr:last-child td { border-bottom:none; }
.jnl-perf-ch-table tbody tr:hover td { background:rgba(139,92,246,.02); }
.jnl-bar-inline { display:flex; align-items:center; gap:8px; }
.jnl-bar-track { flex:1; height:5px; background:var(--surface-2); border-radius:3px; overflow:hidden; }
.jnl-bar-fill { height:100%; border-radius:3px; }

/* ── Pagination ── */
.jnl-pagination { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-top:1px solid var(--border); font-size:.78rem; color:var(--text-3); }
.jnl-pagination a { padding:6px 12px; border:1px solid var(--border); border-radius:var(--radius); color:var(--text-2); text-decoration:none; font-weight:600; transition:all .15s; }
.jnl-pagination a:hover { border-color:#8b5cf6; color:#8b5cf6; background:rgba(139,92,246,.05); }
.jnl-pagination a.active { background:#8b5cf6; color:#fff; border-color:#8b5cf6; }

/* ── Empty ── */
.jnl-empty { text-align:center; padding:60px 20px; color:var(--text-3); }
.jnl-empty i { font-size:2.5rem; opacity:.2; margin-bottom:12px; display:block; }
.jnl-empty h3 { font-family:var(--font-display); color:var(--text-2); font-size:1rem; font-weight:600; margin-bottom:8px; }

/* ── Flash / Toast ── */
.jnl-flash { padding:12px 18px; border-radius:var(--radius); font-size:.85rem; font-weight:600; margin-bottom:16px; display:flex; align-items:center; gap:8px; animation:jnlFI .3s var(--ease); }
.jnl-flash.s { background:var(--green-bg,#d1fae5); color:var(--green,#059669); border:1px solid rgba(5,150,105,.12); }
.jnl-flash.e { background:rgba(220,38,38,.06); color:#dc2626; border:1px solid rgba(220,38,38,.12); }
@keyframes jnlFI { from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)} }

.jnl-toast { position:fixed; bottom:24px; right:24px; z-index:9999; padding:12px 20px; border-radius:10px; color:#fff; font-size:.88rem; font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,.15); transform:translateY(100px); opacity:0; transition:all .3s; }
.jnl-toast.active { transform:translateY(0); opacity:1; }
.jnl-toast.ok  { background:#059669; }
.jnl-toast.err { background:#dc2626; }

@media(max-width:1100px) { .jnl-gen-wrap { grid-template-columns:1fr; } .jnl-perf-stats { grid-template-columns:1fr 1fr; } }
@media(max-width:900px)  { .jnl-ch-grid { grid-template-columns:repeat(3,1fr); } }
@media(max-width:768px)  { .jnl-banner { flex-direction:column; gap:18px; align-items:flex-start; } .jnl-ch-grid { grid-template-columns:1fr 1fr; } .jnl-tabs { flex-wrap:wrap; } }
</style>

<div class="jnl" id="jnlRoot">

<!-- ══ BANNER ══ -->
<div class="jnl-banner">
    <div class="jnl-banner-l">
        <h2><i class="fas fa-newspaper"></i> Stratégie Contenu</h2>
        <p>Journal éditorial multi-canal — Semaine <strong><?= $currentWeek['week'] ?></strong> · <?= $currentWeek['year'] ?></p>
        <div class="jnl-canal-pills">
            <?php foreach ($canaux as $id => $ch): ?>
            <span class="jnl-canal-pill" style="background:<?= $ch['color'] ?>20;color:<?= $ch['color'] ?>;border-color:<?= $ch['color'] ?>30">
                <i class="<?= $ch['icon'] ?>" style="font-size:.6rem"></i> <?= $ch['label'] ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="jnl-stats">
        <div class="jnl-stat"><div class="num violet"><?= $total ?></div><div class="lbl">Total</div></div>
        <div class="jnl-stat"><div class="num amber"><?= $idees ?></div><div class="lbl">Idées</div></div>
        <div class="jnl-stat"><div class="num blue"><?= $enCours ?></div><div class="lbl">En cours</div></div>
        <div class="jnl-stat"><div class="num blue"><?= $prets ?></div><div class="lbl">Prêts</div></div>
        <div class="jnl-stat"><div class="num green"><?= $publies ?></div><div class="lbl">Publiés</div></div>
    </div>
</div>

<!-- ══ ONGLETS ══ -->
<div class="jnl-tabs">
    <button class="jnl-tab <?= $tab==='global'?'active':'' ?>" onclick="JNL.switchTab('global')">
        <i class="fas fa-list-ul"></i> Vue Globale <span class="cnt"><?= $total ?></span>
    </button>
    <button class="jnl-tab <?= $tab==='matrice'?'active':'' ?>" onclick="JNL.switchTab('matrice')">
        <i class="fas fa-border-all"></i> Matrice Stratégique
    </button>
    <button class="jnl-tab <?= $tab==='generate'?'active':'' ?>" onclick="JNL.switchTab('generate')">
        <i class="fas fa-wand-magic-sparkles"></i> Générateur IA
    </button>
    <button class="jnl-tab <?= $tab==='performance'?'active':'' ?>" onclick="JNL.switchTab('performance')">
        <i class="fas fa-chart-bar"></i> Performance
    </button>
</div>

<!-- ══════════════════════════════════════════════════
     ONGLET 1 — VUE GLOBALE
══════════════════════════════════════════════════ -->
<div class="jnl-panel <?= $tab==='global'?'active':'' ?>" id="jnl-panel-global">

    <!-- Cards réseau -->
    <div class="jnl-ch-grid">
        <?php foreach ($canaux as $chId => $ch):
            $cs      = $channelStatsMap[$chId] ?? [];
            $chTotal = max((int)($cs['total'] ?? 0), 1);
            $chPub   = (int)($cs['published'] ?? 0);
            $chPct   = round($chPub / $chTotal * 100);
            $chActifs= (int)($cs['ideas']??0)+(int)($cs['planned']??0);
            $chWIP   = (int)($cs['validated']??0)+(int)($cs['writing']??0)+(int)($cs['ready']??0);
            $journalLinks = [
                'blog'=>'articles-journal','gmb'=>'local-gmb-journal',
                'facebook'=>'facebook-journal','instagram'=>'instagram-journal',
                'tiktok'=>'tiktok-journal','linkedin'=>'linkedin-journal','email'=>'emails-journal',
            ];
            $linkPage = $journalLinks[$chId] ?? 'journal';
        ?>
        <a class="jnl-ch-card" href="?page=<?= $linkPage ?>" style="--ch-color:<?= $ch['color'] ?>">
            <div class="jnl-ch-card-head">
                <i class="<?= $ch['icon'] ?>" style="color:<?= $ch['color'] ?>"></i>
                <span><?= $ch['label'] ?></span>
            </div>
            <style>.jnl-ch-card:hover { border-color:var(--ch-color) !important; }</style>
            <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?= $ch['color'] ?>"></div>
            <div class="jnl-ch-nums">
                <div class="jnl-ch-num">
                    <div class="v" style="color:<?= $ch['color'] ?>"><?= $chActifs ?></div>
                    <div class="l">Idées</div>
                </div>
                <div class="jnl-ch-num">
                    <div class="v" style="color:#6366f1"><?= $chWIP ?></div>
                    <div class="l">WIP</div>
                </div>
                <div class="jnl-ch-num">
                    <div class="v" style="color:#059669"><?= $chPub ?></div>
                    <div class="l">Pub.</div>
                </div>
            </div>
            <div class="jnl-ch-bar"><div class="jnl-ch-bar-fill" style="width:<?= $chPct ?>%;background:<?= $ch['color'] ?>"></div></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Filtres canaux -->
    <div class="jnl-canal-filters">
        <a href="<?= jUrl(['canal'=>null,'p'=>null]) ?>" class="jnl-cf-pill <?= $filterCanal==='all'?'active':'' ?>"
           style="<?= $filterCanal==='all'?'background:#374151;':'' ?>">
            <i class="fas fa-layer-group"></i> Tous <span class="cnt"><?= $total ?></span>
        </a>
        <?php foreach ($canaux as $chId => $ch):
            $cnt = (int)($channelStatsMap[$chId]['total'] ?? 0);
            $isA = $filterCanal === $chId;
        ?>
        <a href="<?= jUrl(['canal'=>$chId,'p'=>null]) ?>" class="jnl-cf-pill <?= $isA?'active':'' ?>"
           style="<?= $isA?"background:{$ch['color']};":'' ?>">
            <i class="<?= $ch['icon'] ?>"></i> <?= $ch['label'] ?> <span class="cnt"><?= $cnt ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Toolbar statuts + search -->
    <div class="jnl-toolbar">
        <div class="jnl-toolbar-l">
            <div class="jnl-filters-bar">
                <?php foreach ([
                    'all'       => ['fa-layer-group','Tous',     $total],
                    'idea'      => ['fa-lightbulb',  'Idées',    $idees],
                    'validated' => ['fa-check-circle','Validés', $enCours],
                    'published' => ['fa-rocket',     'Publiés',  $publies],
                ] as $k => [$icon,$label,$cnt]):
                    $url = jUrl(['status'=>$k==='all'?null:$k,'p'=>null]);
                ?>
                <a href="<?= $url ?>" class="jnl-fbtn <?= $filterStatus===$k?'active':'' ?>">
                    <i class="fas <?= $icon ?>"></i> <?= $label ?> <span class="badge"><?= (int)$cnt ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php if ($filterCanal!=='all'||$filterStatus!=='all'||$filterSem||$searchQ): ?>
            <a href="?page=journal" class="jnl-reset"><i class="fas fa-times"></i> Réinitialiser</a>
            <?php endif; ?>

            <!-- Filtre semaine -->
            <form method="GET" style="display:flex;gap:6px;align-items:center">
                <input type="hidden" name="page" value="journal">
                <input type="hidden" name="tab"  value="global">
                <?php if ($filterCanal!=='all'): ?><input type="hidden" name="canal"  value="<?= htmlspecialchars($filterCanal) ?>"><?php endif; ?>
                <?php if ($filterStatus!=='all'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
                <select name="sem" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid var(--border);border-radius:var(--radius);font-size:.78rem;font-family:var(--font);background:var(--surface);color:var(--text)">
                    <option value="0">Toutes semaines</option>
                    <?php for ($w=1;$w<=52;$w++): ?>
                    <option value="<?= $w ?>" <?= $filterSem===$w?'selected':'' ?>>S<?= $w ?><?= $w===$currentWeek['week']?' ★':'' ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <div class="jnl-toolbar-r">
            <form class="jnl-search" method="GET">
                <input type="hidden" name="page" value="journal">
                <input type="hidden" name="tab"  value="global">
                <?php if ($filterCanal!=='all'): ?><input type="hidden" name="canal" value="<?= htmlspecialchars($filterCanal) ?>"><?php endif; ?>
                <i class="fas fa-search"></i>
                <input type="text" name="q" placeholder="Rechercher une idée…" value="<?= htmlspecialchars($searchQ) ?>">
            </form>
            <button class="jnl-btn jnl-btn-primary" onclick="JNL.openNewModal()">
                <i class="fas fa-plus"></i> Nouvelle idée
            </button>
        </div>
    </div>

    <!-- Bulk bar -->
    <div class="jnl-bulk" id="jnlBulkBar">
        <input type="checkbox" id="jnlSelAll" onchange="JNL.toggleAll(this.checked)">
        <span id="jnlBulkCnt">0</span> sélectionnée(s)
        <select id="jnlBulkAct">
            <option value="">— Action —</option>
            <option value="validate">Valider</option>
            <option value="publish">Publier</option>
            <option value="reject">Rejeter</option>
            <option value="delete">Supprimer</option>
        </select>
        <button class="jnl-btn jnl-btn-sm jnl-btn-outline" onclick="JNL.bulkExec()"><i class="fas fa-check"></i> Appliquer</button>
    </div>

    <!-- Table -->
    <div class="jnl-table-wrap">
    <?php if (empty($allItems)): ?>
        <div class="jnl-empty">
            <i class="fas fa-newspaper"></i>
            <h3><?= $searchQ||$filterCanal!=='all'||$filterStatus!=='all'?'Aucun résultat':'Aucune idée dans le journal' ?></h3>
            <?php if ($searchQ): ?>
            <p>Aucun résultat pour «&nbsp;<?= htmlspecialchars($searchQ) ?>&nbsp;». <a href="?page=journal">Effacer</a></p>
            <?php else: ?>
            <p>Utilisez le <button onclick="JNL.switchTab('generate')" style="background:none;border:none;color:#8b5cf6;font-weight:700;cursor:pointer;font-size:inherit">Générateur IA</button> pour démarrer votre stratégie.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="jnl-table">
            <thead><tr>
                <th style="width:32px"><input type="checkbox" onchange="JNL.toggleAll(this.checked)"></th>
                <th style="width:46px">Sem.</th>
                <th style="width:36px">Canal</th>
                <th>Titre / Contenu</th>
                <th style="width:90px">Profil</th>
                <th style="width:90px">Conscience</th>
                <th style="width:80px">Type</th>
                <th style="width:80px">Objectif</th>
                <th style="width:90px">Statut</th>
                <th style="text-align:right">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($allItems as $item):
                $chInfo   = $canaux[$item['channel_id']] ?? ['icon'=>'fa-file','color'=>'#999','label'=>'?'];
                $profile  = JournalController::PROFILES[$item['profile_id']]    ?? ['label'=>$item['profile_id']??'?','color'=>'#999'];
                $awareness= JournalController::AWARENESS[$item['awareness_level']] ?? ['short'=>'?','color'=>'#999'];
                $statusI  = JournalController::STATUSES[$item['status']]          ?? ['label'=>'?','color'=>'#999','icon'=>'fa-circle'];
                $typeL    = JournalController::CONTENT_TYPES[$item['content_type']] ?? $item['content_type'] ?? '—';
                $objI     = JournalController::OBJECTIVES[$item['objective_id']]   ?? ['label'=>$item['objective_id']??'—'];
                $isCurWeek= (int)$item['week_number'] === $currentWeek['week'] && (int)$item['year'] === $currentWeek['year'];
                $createUrl= $jCtrl->getCreateContentUrl($item);
            ?>
            <tr class="<?= $isCurWeek?'jnl-row-curweek':'' ?>" data-id="<?= (int)$item['id'] ?>">
                <td><input type="checkbox" class="jnl-cb" value="<?= (int)$item['id'] ?>" onchange="JNL.updateBulk()"></td>
                <td>
                    <span class="jnl-badge-week <?= $isCurWeek?'cur':'' ?>">S<?= (int)$item['week_number'] ?></span>
                </td>
                <td>
                    <div class="jnl-canal-icon" style="background:<?= $chInfo['color'] ?>20;color:<?= $chInfo['color'] ?>" title="<?= $chInfo['label'] ?>">
                        <i class="<?= $chInfo['icon'] ?>"></i>
                    </div>
                </td>
                <td class="jnl-titre">
                    <a href="javascript:void(0)" onclick="JNL.editItem(<?= (int)$item['id'] ?>)">
                        <?= htmlspecialchars($item['title'] ?? '—') ?>
                    </a>
                    <?php if (!empty($item['sector_id'])): ?>
                    <div class="jnl-meta"><i class="fas fa-map-pin" style="font-size:.55rem"></i> <?= htmlspecialchars($item['sector_id']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="jnl-badge" style="background:<?= $profile['color'] ?>; font-size:.62rem">
                        <?= htmlspecialchars($profile['label']) ?>
                    </span>
                </td>
                <td>
                    <span class="jnl-badge-outline" style="color:<?= $awareness['color'] ?>;border-color:<?= $awareness['color'] ?>">
                        <?= htmlspecialchars($awareness['short'] ?? '?') ?>
                    </span>
                </td>
                <td><span class="jnl-badge-type"><?= htmlspecialchars($typeL) ?></span></td>
                <td style="font-size:.72rem;color:var(--text-2)"><?= htmlspecialchars($objI['label'] ?? '—') ?></td>
                <td>
                    <span class="jnl-badge" style="background:<?= $statusI['color'] ?>">
                        <i class="fas <?= $statusI['icon'] ?>" style="font-size:.6rem"></i>
                        <?= $statusI['label'] ?>
                    </span>
                </td>
                <td>
                    <div class="jnl-actions">
                        <?php if (in_array($item['status'],['idea','planned'])): ?>
                        <button class="btn-ok" onclick="JNL.setStatus(<?= (int)$item['id'] ?>,'validated')" title="Valider"><i class="fas fa-check"></i></button>
                        <?php endif; ?>
                        <?php if (in_array($item['status'],['validated','writing','ready']) && $createUrl): ?>
                        <a href="<?= htmlspecialchars($createUrl) ?>" class="btn-go" title="Créer le contenu"><i class="fas fa-arrow-right"></i></a>
                        <?php endif; ?>
                        <?php if ($item['status']==='ready'): ?>
                        <button class="btn-ok" onclick="JNL.setStatus(<?= (int)$item['id'] ?>,'published')" title="Marquer publié"><i class="fas fa-rocket"></i></button>
                        <?php endif; ?>
                        <button onclick="JNL.editItem(<?= (int)$item['id'] ?>)" title="Modifier"><i class="fas fa-pen"></i></button>
                        <button class="btn-del" onclick="JNL.deleteItem(<?= (int)$item['id'] ?>)" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($totalPages > 1): ?>
        <div class="jnl-pagination">
            <span>Affichage <?= $offset+1 ?>–<?= min($offset+$perPage,$totalItems) ?> sur <strong><?= $totalItems ?></strong></span>
            <div style="display:flex;gap:4px">
                <?php for ($i=1;$i<=$totalPages;$i++): ?>
                <a href="<?= jUrl(['p'=>$i]) ?>" class="<?= $i===$currentPage?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     ONGLET 2 — MATRICE STRATÉGIQUE
══════════════════════════════════════════════════ -->
<div class="jnl-panel <?= $tab==='matrice'?'active':'' ?>" id="jnl-panel-matrice">
    <p style="font-size:.85rem;color:var(--text-2);margin-bottom:18px">
        <i class="fas fa-info-circle" style="color:#8b5cf6"></i>
        Identifiez les zones non couvertes de votre stratégie. Cases vides = opportunités manquées.
    </p>
    <div class="jnl-matrix-wrap">
        <table class="jnl-matrix">
            <thead><tr>
                <th class="jnl-matrix-th-left">Profil</th>
                <?php foreach (JournalController::AWARENESS as $aKey => $aInfo): ?>
                <th>
                    <span style="color:<?= $aInfo['color'] ?>"><?= $aInfo['short'] ?></span><br>
                    <span style="font-size:.6rem;font-weight:400;opacity:.7">Niv.<?= $aInfo['step'] ?></span>
                </th>
                <?php endforeach; ?>
                <th>Total</th>
            </tr></thead>
            <tbody>
            <?php foreach (JournalController::PROFILES as $pKey => $pInfo):
                $rowTotal = 0;
            ?>
            <tr>
                <td class="jnl-matrix-td-profile">
                    <span style="color:<?= $pInfo['color'] ?>">●</span> <?= $pInfo['label'] ?>
                </td>
                <?php foreach (JournalController::AWARENESS as $aKey => $aInfo):
                    $cell = $matrixData[$pKey][$aKey] ?? ['cnt'=>0,'published'=>0];
                    $cnt  = (int)$cell['cnt'];
                    $pub  = (int)$cell['published'];
                    $rowTotal += $cnt;
                    $cntClass = $cnt===0?'empty':($cnt<=2?'low':($cnt<=5?'ok':'good'));
                ?>
                <td>
                    <div class="jnl-matrix-cell">
                        <span class="jnl-matrix-cnt <?= $cntClass ?>"><?= $cnt===0?'—':$cnt ?></span>
                        <?php if ($pub > 0): ?><span class="jnl-matrix-pub"><?= $pub ?> pub.</span><?php endif; ?>
                    </div>
                </td>
                <?php endforeach; ?>
                <td style="font-weight:800;font-family:var(--font-display)"><?= $rowTotal ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="jnl-matrix-legend">
        <span style="color:var(--text-3)">— = manquant</span>
        <span style="color:#f59e0b">1-2 = à renforcer</span>
        <span style="color:#3b82f6">3-5 = correct</span>
        <span style="color:#10b981">6+ = bien couvert</span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     ONGLET 3 — GÉNÉRATEUR IA
══════════════════════════════════════════════════ -->
<div class="jnl-panel <?= $tab==='generate'?'active':'' ?>" id="jnl-panel-generate">
    <div class="jnl-gen-wrap">

        <!-- Formulaire paramètres -->
        <div class="jnl-gen-form">
            <h3><i class="fas fa-wand-magic-sparkles"></i> Paramètres de génération</h3>

            <div class="jnl-gen-row">
                <label>Canal</label>
                <select id="jnlGenCanal">
                    <option value="">Tous les canaux</option>
                    <?php foreach ($canaux as $chId => $ch): ?>
                    <option value="<?= $chId ?>"><i class="<?= $ch['icon'] ?>"></i> <?= $ch['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="jnl-gen-row">
                <label>Durée</label>
                <select id="jnlGenWeeks">
                    <option value="2">2 semaines</option>
                    <option value="4" selected>4 semaines (recommandé)</option>
                    <option value="8">8 semaines</option>
                    <option value="12">3 mois</option>
                </select>
            </div>

            <div class="jnl-gen-row">
                <label>Personas cibles</label>
                <div class="jnl-gen-personas" id="jnlGenPersonas">
                    <?php
                    $personas = JournalController::PROFILES;
                    $pColors  = ['#ef4444','#3b82f6','#8b5cf6','#10b981','#f59e0b'];
                    $pi = 0;
                    foreach ($personas as $pKey => $pInfo): $pc = $pColors[$pi % count($pColors)]; $pi++;
                    ?>
                    <label class="jnl-gen-persona selected" onclick="this.classList.toggle('selected');this.querySelector('input').click()">
                        <input type="checkbox" value="<?= $pKey ?>" checked style="display:none">
                        <div class="p-avatar" style="background:<?= $pInfo['color'] ?? $pc ?>"><i class="fas fa-user" style="font-size:.7rem"></i></div>
                        <div>
                            <div class="p-name"><?= $pInfo['label'] ?></div>
                            <div class="p-sub"><?= $pInfo['desc'] ?? '' ?></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-top:20px">
                <button class="jnl-btn jnl-btn-primary" style="width:100%;justify-content:center" id="jnlGenBtn" onclick="JNL.generate()">
                    <i class="fas fa-wand-magic-sparkles"></i> Générer les idées
                </button>
            </div>

            <div id="jnlGenStatus" class="jnl-gen-status-bar"></div>
        </div>

        <!-- Zone résultats -->
        <div class="jnl-gen-result-area">
            <h3><i class="fas fa-lightbulb" style="color:#f59e0b"></i> Idées générées</h3>
            <div id="jnlGenResults">
                <div class="jnl-empty">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <h3>Prêt à générer</h3>
                    <p>Sélectionnez vos paramètres et lancez la génération.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     ONGLET 4 — PERFORMANCE
══════════════════════════════════════════════════ -->
<div class="jnl-panel <?= $tab==='performance'?'active':'' ?>" id="jnl-panel-performance">
    <?php
    $totalAll = max($total, 1);
    $pctPub   = round($publies / $totalAll * 100);
    $pctRdy   = round($prets   / $totalAll * 100);
    $pctWIP   = round($enCours / $totalAll * 100);
    $pctIdee  = max(0, 100 - $pctPub - $pctRdy - $pctWIP);
    ?>
    <div class="jnl-perf-stats">
        <div class="jnl-perf-stat"><div class="v" style="color:#8b5cf6"><?= $total ?></div><div class="l">Total idées</div></div>
        <div class="jnl-perf-stat"><div class="v" style="color:#059669"><?= $publies ?></div><div class="l">Publiés</div></div>
        <div class="jnl-perf-stat"><div class="v" style="color:#3b82f6"><?= $pctPub ?>%</div><div class="l">Taux publication</div></div>
        <div class="jnl-perf-stat"><div class="v" style="color:#f59e0b"><?= $prets ?></div><div class="l">Prêts à publier</div></div>
    </div>

    <div style="margin-bottom:20px">
        <div style="font-family:var(--font-display);font-size:.88rem;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:6px"><i class="fas fa-stream" style="color:#8b5cf6;font-size:.8rem"></i> Pipeline éditorial</div>
        <div class="jnl-pipeline">
            <?php if ($pctIdee > 0): ?><div class="jnl-pipeline-seg" style="width:<?= $pctIdee ?>%;background:#94a3b8"><?= $pctIdee > 8 ? $pctIdee.'% Idées':'' ?></div><?php endif; ?>
            <?php if ($pctWIP > 0):  ?><div class="jnl-pipeline-seg" style="width:<?= $pctWIP ?>%;background:#8b5cf6"><?= $pctWIP > 8 ? $pctWIP.'% WIP':'' ?></div><?php endif; ?>
            <?php if ($pctRdy > 0):  ?><div class="jnl-pipeline-seg" style="width:<?= $pctRdy ?>%;background:#3b82f6"><?= $pctRdy > 8 ? $pctRdy.'% Prêts':'' ?></div><?php endif; ?>
            <?php if ($pctPub > 0):  ?><div class="jnl-pipeline-seg" style="width:<?= $pctPub ?>%;background:#059669"><?= $pctPub > 8 ? $pctPub.'% Pub.':'' ?></div><?php endif; ?>
        </div>
        <div class="jnl-pipeline-legend">
            <span style="color:#94a3b8">● Idées</span>
            <span style="color:#8b5cf6">● En cours</span>
            <span style="color:#3b82f6">● Prêts</span>
            <span style="color:#059669">● Publiés</span>
        </div>
    </div>

    <div style="font-family:var(--font-display);font-size:.88rem;font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:6px"><i class="fas fa-chart-bar" style="color:#8b5cf6;font-size:.8rem"></i> Détail par canal</div>
    <div class="jnl-perf-ch-table">
        <table>
            <thead><tr>
                <th>Canal</th><th>Idées</th><th>En cours</th><th>Prêts</th>
                <th>Publiés</th><th>Total</th><th>Taux</th>
            </tr></thead>
            <tbody>
            <?php foreach ($canaux as $chId => $ch):
                $cs     = $channelStatsMap[$chId] ?? [];
                $chTot  = max((int)($cs['total'] ?? 0), 1);
                $chPub  = (int)($cs['published'] ?? 0);
                $chPct2 = round($chPub / $chTot * 100);
            ?>
            <tr>
                <td><i class="<?= $ch['icon'] ?>" style="color:<?= $ch['color'] ?>;margin-right:6px"></i> <?= $ch['label'] ?></td>
                <td><?= (int)($cs['ideas']??0)+(int)($cs['planned']??0) ?></td>
                <td><?= (int)($cs['validated']??0)+(int)($cs['writing']??0) ?></td>
                <td><?= (int)($cs['ready']??0) ?></td>
                <td style="font-weight:700;color:#059669"><?= $chPub ?></td>
                <td><?= (int)($cs['total']??0) ?></td>
                <td>
                    <div class="jnl-bar-inline">
                        <div class="jnl-bar-track"><div class="jnl-bar-fill" style="width:<?= $chPct2 ?>%;background:<?= $ch['color'] ?>"></div></div>
                        <span style="font-size:.72rem;font-weight:700"><?= $chPct2 ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /jnlRoot -->

<!-- ══ TOAST ══ -->
<div class="jnl-toast" id="jnlToast"></div>

<!-- ══════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════ -->
<script>
const JNL = {
    API: window.location.pathname + window.location.search.replace(/[?&]page=[^&]*/g,'').replace(/[?&]tab=[^&]*/g,'').split('?')[0]
        + '?page=journal&_ajax=1',

    toast(msg, type='ok') {
        const t = document.getElementById('jnlToast');
        t.textContent = msg; t.className = 'jnl-toast ' + type + ' active';
        setTimeout(() => t.classList.remove('active'), 3000);
    },

    switchTab(tab) {
        document.querySelectorAll('.jnl-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.jnl-tab').forEach(b => b.classList.remove('active'));
        document.getElementById('jnl-panel-' + tab)?.classList.add('active');
        const btn = document.querySelector(`.jnl-tab[onclick*="'${tab}'"]`);
        if (btn) btn.classList.add('active');
        const url = new URL(window.location);
        url.searchParams.set('tab', tab); url.searchParams.delete('p');
        history.replaceState(null, '', url);
    },

    toggleAll(c) {
        document.querySelectorAll('.jnl-cb').forEach(cb => cb.checked = c);
        this.updateBulk();
    },

    updateBulk() {
        const n = document.querySelectorAll('.jnl-cb:checked').length;
        document.getElementById('jnlBulkCnt').textContent = n;
        document.getElementById('jnlBulkBar').classList.toggle('active', n > 0);
    },

    async _post(data) {
        const fd = new FormData();
        for (const [k,v] of Object.entries(data)) fd.append(k, v);
        fd.append('_ajax','1');
        const r = await fetch(window.location.href, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: fd
        });
        return r.json();
    },

    async setStatus(id, status) {
        const d = await this._post({action:'change_status', id, status});
        d.success ? (this.toast('Statut mis à jour'), location.reload()) : this.toast(d.error||'Erreur','err');
    },

    async deleteItem(id) {
        if (!confirm('Supprimer cette idée ?')) return;
        const d = await this._post({action:'delete', id});
        if (d.success) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) { row.style.cssText='opacity:0;transition:.3s'; setTimeout(()=>row.remove(),300); }
            this.toast('Supprimé');
        } else this.toast(d.error||'Erreur','err');
    },

    async bulkExec() {
        const act  = document.getElementById('jnlBulkAct').value; if (!act) return;
        const ids  = [...document.querySelectorAll('.jnl-cb:checked')].map(c => +c.value);
        if (!ids.length) return;
        if (act === 'delete' && !confirm(`Supprimer ${ids.length} idée(s) ?`)) return;
        const map  = {validate:'validated', publish:'published', reject:'rejete'};
        let data   = {ids: JSON.stringify(ids)};
        if (act === 'delete') data.action = 'bulk';
        else { data.action = 'bulk'; data.op = 'status_' + map[act]; }
        if (act === 'delete') data.op = 'delete';
        const d    = await this._post(data);
        d.success ? location.reload() : this.toast(d.error||'Erreur','err');
    },

    editItem(id) {
        // Ouvre le modal d'édition — à connecter avec le modal ci-dessous
        document.getElementById('jnlModalItemId').value = id;
        document.getElementById('jnlModalOverlay').classList.add('active');
        // Charger les données
        this._post({action:'get_item', id}).then(d => {
            if (!d.success) return;
            const it = d.data;
            ['titre','description','notes'].forEach(f => {
                const el = document.getElementById('jnlMf_'+f);
                if (el) el.value = it[f] || it['title'] || '';
            });
            const selMap = {canal:'channel_id',status:'status',persona:'profile_id',
                           conscience:'awareness_level',type:'content_type',objectif:'objective_id'};
            for (const [elId, field] of Object.entries(selMap)) {
                const el = document.getElementById('jnlMf_'+elId);
                if (el && it[field]) el.value = it[field];
            }
            const semEl = document.getElementById('jnlMf_semaine');
            if (semEl && it.week_number) semEl.value = it.week_number;
        });
    },

    openNewModal() {
        document.getElementById('jnlModalItemId').value = '0';
        document.getElementById('jnlModalOverlay').classList.add('active');
        document.getElementById('jnlModalTitle').textContent = 'Nouvelle idée';
        // Reset
        ['jnlMf_titre','jnlMf_description','jnlMf_notes'].forEach(id => {
            const el = document.getElementById(id); if (el) el.value = '';
        });
    },

    closeModal() {
        document.getElementById('jnlModalOverlay').classList.remove('active');
    },

    async saveModal() {
        const id = +(document.getElementById('jnlModalItemId').value || 0);
        const data = {
            action: id > 0 ? 'save_idea' : 'save_idea',
            id: id || '',
            canal:       document.getElementById('jnlMf_canal')?.value,
            titre:       document.getElementById('jnlMf_titre')?.value,
            description: document.getElementById('jnlMf_description')?.value,
            status:      document.getElementById('jnlMf_status')?.value,
            persona_cible: document.getElementById('jnlMf_persona')?.value,
            niveau_conscience: document.getElementById('jnlMf_conscience')?.value,
            type_contenu: document.getElementById('jnlMf_type')?.value,
            objectif:    document.getElementById('jnlMf_objectif')?.value,
            notes:       document.getElementById('jnlMf_notes')?.value,
        };
        // Semaine
        const semEl = document.getElementById('jnlMf_semaine');
        if (semEl) {
            const d = new Date(semEl.value||Date.now());
            data.date_planifiee = semEl.value;
        }
        const res = await this._post(data);
        if (res.success) { this.toast(id > 0 ? 'Mis à jour !' : 'Idée créée !'); this.closeModal(); location.reload(); }
        else this.toast(res.error||'Erreur','err');
    },

    async generate() {
        const canal  = document.getElementById('jnlGenCanal').value;
        const weeks  = +document.getElementById('jnlGenWeeks').value;
        const selP   = [...document.querySelectorAll('#jnlGenPersonas input:checked')].map(i => i.value);
        const btn    = document.getElementById('jnlGenBtn');
        const status = document.getElementById('jnlGenStatus');
        const results= document.getElementById('jnlGenResults');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération en cours…';
        status.className = 'jnl-gen-status-bar loading';
        status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération des idées pour ' + weeks + ' semaines…';

        const d = await this._post({
            action: 'generate_ideas',
            canal: canal || '',
            weeks: weeks,
            personas: JSON.stringify(selP),
        });

        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> Générer les idées';

        if (d.success) {
            status.className = 'jnl-gen-status-bar ok';
            status.innerHTML = '<i class="fas fa-check-circle"></i> ' + (d.message || d.count + ' idée(s) créées !');
            // Afficher les résultats
            if (d.items && d.items.length) {
                let html = '';
                d.items.slice(0,20).forEach(item => {
                    const chColors = <?= json_encode(array_combine(array_keys($canaux), array_column($canaux,'color'))) ?>;
                    const chLabels = <?= json_encode(array_combine(array_keys($canaux), array_column($canaux,'label'))) ?>;
                    const color = chColors[item.channel_id] || '#8b5cf6';
                    html += `<div class="jnl-gen-preview-item">
                        <div class="pi-head">
                            <div style="width:22px;height:22px;border-radius:5px;background:${color}20;color:${color};display:flex;align-items:center;justify-content:center;font-size:.7rem;flex-shrink:0">
                                <i class="fas fa-file"></i>
                            </div>
                            <span class="pi-title">${item.title}</span>
                            <span style="font-size:.65rem;padding:2px 7px;border-radius:10px;background:${color}20;color:${color};font-weight:700">${chLabels[item.channel_id]||item.channel_id}</span>
                        </div>
                        <div class="pi-meta">
                            <span style="font-size:.7rem;color:var(--text-3)">S${item.week_number} · ${item.profile_id||'—'} · ${item.awareness_level||'—'}</span>
                        </div>
                        <div class="pi-actions">
                            <button class="jnl-btn jnl-btn-sm jnl-btn-outline" onclick="JNL.setStatus(${item.id},'validated');this.closest('.jnl-gen-preview-item').style.opacity='.4'">
                                <i class="fas fa-check"></i> Valider
                            </button>
                        </div>
                    </div>`;
                });
                if (d.items.length > 20) html += `<p style="text-align:center;color:var(--text-3);font-size:.8rem;padding:10px">… et ${d.items.length-20} autres. Retrouvez-les dans la Vue Globale.</p>`;
                results.innerHTML = html;
            } else {
                results.innerHTML = '<div class="jnl-empty"><i class="fas fa-check-circle" style="color:#059669;opacity:1"></i><h3>Idées créées</h3><p>Retrouvez-les dans <button onclick="JNL.switchTab(\'global\')" style="background:none;border:none;color:#8b5cf6;cursor:pointer;font-weight:700;font-size:inherit">Vue Globale</button></p></div>';
            }
        } else {
            status.className = 'jnl-gen-status-bar err';
            status.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (d.error || 'Erreur lors de la génération');
        }
    },
};

// Auto-flash disparaît
document.querySelectorAll('.jnl-flash').forEach(el => {
    setTimeout(() => { el.style.transition='opacity .4s'; el.style.opacity='0'; setTimeout(()=>el.remove(),400); }, 4000);
});
</script>

<!-- ══ MODAL ÉDITION IDÉE ══ -->
<div style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9998;justify-content:center;align-items:center" id="jnlModalOverlay" onclick="if(event.target===this)JNL.closeModal()">
    <div style="background:var(--surface);border-radius:var(--radius-xl);padding:28px;width:560px;max-width:96vw;max-height:88vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 id="jnlModalTitle" style="font-family:var(--font-display);font-size:1.05rem;font-weight:700;margin:0;display:flex;align-items:center;gap:8px"><i class="fas fa-newspaper" style="color:#8b5cf6"></i> Nouvelle idée</h3>
            <button onclick="JNL.closeModal()" style="background:none;border:none;font-size:1.1rem;cursor:pointer;color:var(--text-3)"><i class="fas fa-times"></i></button>
        </div>
        <input type="hidden" id="jnlModalItemId" value="0">
        <?php
        $mRow = fn($id,$label,$type='text',$placeholder='') => "
        <div style='margin-bottom:14px'>
            <label style='display:block;font-size:.72rem;font-weight:700;color:var(--text-2);margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em'>$label</label>
            <$type id='$id' placeholder='$placeholder' style='width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:var(--radius);font-size:.88rem;font-family:var(--font);background:var(--surface);color:var(--text);box-sizing:border-box'".($type==='textarea'?' rows="2"':'')."></$type></div>";

        echo $mRow('jnlMf_titre','Titre *','input','Titre de l\'idée ou contenu…');
        echo $mRow('jnlMf_description','Description','textarea','Brief, angle éditorial, idées clés…');
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
            <?php
            $mSel = fn($id,$label,$opts) => "
            <div>
                <label style='display:block;font-size:.72rem;font-weight:700;color:var(--text-2);margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em'>$label</label>
                <select id='$id' style='width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:var(--radius);font-size:.85rem;font-family:var(--font);background:var(--surface);color:var(--text)'>$opts</select>
            </div>";

            $canalOpts = implode('', array_map(fn($k,$v) => "<option value='$k'>{$v['label']}</option>", array_keys($canaux), array_values($canaux)));
            $statusOpts = implode('', array_map(fn($k,$v) => "<option value='$k'>{$v['label']}</option>", array_keys($statuts), array_values($statuts)));
            $personaOpts = implode('', array_map(fn($k,$v) => "<option value='$k'>{$v['label']}</option>", array_keys(JournalController::PROFILES), array_values(JournalController::PROFILES)));
            $consOpts = implode('', array_map(fn($k,$v) => "<option value='$k'>{$v['short']} — {$v['label']}</option>", array_keys(JournalController::AWARENESS), array_values(JournalController::AWARENESS)));
            $typeOpts = implode('', array_map(fn($k,$v) => "<option value='$k'>$v</option>", array_keys(JournalController::CONTENT_TYPES), array_values(JournalController::CONTENT_TYPES)));
            $objOpts  = implode('', array_map(fn($k,$v) => "<option value='$k'>{$v['label']}</option>", array_keys(JournalController::OBJECTIVES), array_values(JournalController::OBJECTIVES)));

            echo $mSel('jnlMf_canal','Canal',$canalOpts);
            echo $mSel('jnlMf_status','Statut',$statusOpts);
            echo $mSel('jnlMf_persona','Persona',$personaOpts);
            echo $mSel('jnlMf_conscience','Conscience',$consOpts);
            echo $mSel('jnlMf_type','Type contenu',$typeOpts);
            echo $mSel('jnlMf_objectif','Objectif',$objOpts);
            ?>
        </div>
        <div style="margin-bottom:14px">
            <label style="display:block;font-size:.72rem;font-weight:700;color:var(--text-2);margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em">Date planifiée</label>
            <input type="date" id="jnlMf_semaine" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:var(--radius);font-size:.85rem;font-family:var(--font);background:var(--surface);color:var(--text)">
        </div>
        <?php echo $mRow('jnlMf_notes','Notes internes','textarea','Notes, sources, inspirations…'); ?>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
            <button class="jnl-btn jnl-btn-outline" onclick="JNL.closeModal()">Annuler</button>
            <button class="jnl-btn jnl-btn-primary" onclick="JNL.saveModal()"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
    </div>
</div>
<script>
// Afficher le modal (corrige display:none → flex)
const _jnlOvl = document.getElementById('jnlModalOverlay');
if (_jnlOvl) {
    const _orig = JNL.openNewModal.bind(JNL);
    JNL.openNewModal = function() { _jnlOvl.style.display = 'flex'; _orig(); };
    JNL.editItem = (function(orig){
        return function(id) { _jnlOvl.style.display = 'flex'; orig.call(JNL, id); };
    })(JNL.editItem.bind(JNL));
    JNL.closeModal = function() { _jnlOvl.style.display = 'none'; };
}
</script>