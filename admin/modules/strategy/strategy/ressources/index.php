<?php
/**
 * MODULE RESSOURCES — index.php
 * /admin/modules/strategy/strategy/ressources/index.php
 * Route : dashboard.php?page=ressources
 */
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit('Accès refusé'); }
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db  = $pdo;

// ── Paramètres ──
$selected_persona = $_GET['persona'] ?? 'vendeur';
$personas_valid   = ['vendeur','acheteur','proprietaire'];
if (!in_array($selected_persona, $personas_valid)) $selected_persona = 'vendeur';

$flash = $_GET['flash'] ?? '';

// ── Charger toutes les ressources depuis DB ──
$all_ressources = [];
$stats_db       = ['vendeur'=>0,'acheteur'=>0,'proprietaire'=>0];
try {
    $rows = $pdo->query("
        SELECT r.*, c.id AS cap_id, c.slug AS cap_slug, c.status AS cap_status, c.vues, c.conversions
        FROM ressources r
        LEFT JOIN captures c ON c.id = r.capture_id
        ORDER BY r.persona, r.sort_order, r.id
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $p = $row['persona'];
        if (isset($stats_db[$p])) $stats_db[$p]++;
        $all_ressources[] = $row;
    }
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

$filtered = array_filter($all_ressources, fn($r) => $r['persona'] === $selected_persona);
$filtered = array_values($filtered);

$personas = [
    'vendeur'      => '🏷️ Vendeurs',
    'acheteur'     => '🛒 Acheteurs',
    'proprietaire' => '🏠 Propriétaires',
];
$persona_colors = [
    'vendeur'      => ['from'=>'#d4a574','to'=>'#c9913b','light'=>'#fdf6ee'],
    'acheteur'     => ['from'=>'#1a4d7a','to'=>'#2d7dd2','light'=>'#eef4fb'],
    'proprietaire' => ['from'=>'#059669','to'=>'#34d399','light'=>'#ecfdf5'],
];
$color = $persona_colors[$selected_persona];

$hero_texts = [
    'vendeur'      => ['title'=>'Guides & Ressources — Vendeurs',      'sub'=>'Accompagnez vos prospects vendeurs à chaque étape de leur projet.'],
    'acheteur'     => ['title'=>'Guides & Ressources — Acheteurs',     'sub'=>'Rassurez et guidez vos prospects acheteurs vers la décision.'],
    'proprietaire' => ['title'=>'Guides & Ressources — Propriétaires', 'sub'=>'Valorisez votre expertise auprès des propriétaires bailleurs et investisseurs.'],
];
$hero = $hero_texts[$selected_persona];

$type_labels = ['guide'=>'📄 Guide PDF','article'=>'📝 Article'];
$status_styles = [
    'active'   => ['bg'=>'#f0fdf4','border'=>'#bbf7d0','color'=>'#16a34a','dot'=>'#22c55e','label'=>'Actif'],
    'inactive' => ['bg'=>'#fef9c3','border'=>'#fde047','color'=>'#a16207','dot'=>'#eab308','label'=>'Inactif'],
    'draft'    => ['bg'=>'#f1f5f9','border'=>'#cbd5e1','color'=>'#64748b','dot'=>'#94a3b8','label'=>'Brouillon'],
];
?>
<style>
/* ═══════════════════════════════════════════
   RESSOURCES — Catalogue
   ═══════════════════════════════════════════ */
.res-wrap { max-width:100%; }

/* Flash */
.res-flash { padding:12px 18px; border-radius:10px; font-size:13px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
.res-flash.success { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; }
.res-flash.error   { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }

/* Header bar */
.res-header-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.res-header-title { font-size:22px; font-weight:800; color:#1e293b; }
.res-btn-new { display:inline-flex; align-items:center; gap:7px; padding:10px 20px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:white; border:none; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; text-decoration:none; transition:.15s; }
.res-btn-new:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(99,102,241,.35); color:white; }

/* Tabs */
.res-tabs { display:flex; gap:10px; margin-bottom:22px; flex-wrap:wrap; }
.res-tab { padding:9px 20px; border:2px solid #e2e8f0; background:white; border-radius:10px; cursor:pointer; font-weight:700; font-size:13px; color:#64748b; transition:.15s; text-decoration:none; display:flex; align-items:center; gap:8px; }
.res-tab:hover { border-color:#6366f1; color:#6366f1; }
.res-tab.active { background:linear-gradient(135deg,<?= $color['from'] ?>,<?= $color['to'] ?>); color:white; border-color:transparent; box-shadow:0 4px 14px rgba(0,0,0,.15); }
.res-tab-count { background:rgba(255,255,255,.25); border-radius:20px; padding:1px 8px; font-size:11px; font-weight:700; }
.res-tab:not(.active) .res-tab-count { background:#f1f5f9; color:#94a3b8; }

/* Hero */
.res-hero { background:linear-gradient(135deg,<?= $color['from'] ?>,<?= $color['to'] ?>); border-radius:14px; padding:22px 30px; margin-bottom:22px; display:flex; align-items:center; justify-content:space-between; gap:20px; color:white; }
.res-hero-title { font-size:20px; font-weight:800; margin-bottom:4px; }
.res-hero-sub   { font-size:13px; opacity:.85; }
.res-hero-badge { background:rgba(255,255,255,.2); border-radius:12px; padding:12px 20px; text-align:center; white-space:nowrap; flex-shrink:0; }
.res-hero-badge strong { display:block; font-size:26px; font-weight:800; }
.res-hero-badge span   { font-size:11px; opacity:.85; }

/* Toolbar */
.res-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; gap:12px; flex-wrap:wrap; }
.res-toolbar-left { display:flex; align-items:center; gap:10px; }
.res-view-toggle { display:flex; border:2px solid #e2e8f0; border-radius:9px; overflow:hidden; }
.res-view-btn { padding:7px 13px; background:white; border:none; cursor:pointer; font-size:16px; color:#94a3b8; transition:.15s; line-height:1; }
.res-view-btn:hover { background:#f8fafc; color:#64748b; }
.res-view-btn.active { background:<?= $color['from'] ?>; color:white; }
.res-count-label { font-size:13px; color:#94a3b8; font-weight:600; }

/* ═══ VUE LISTE ═══ */
.res-list { display:flex; flex-direction:column; gap:8px; }
.res-list-item {
    background:white; border:1px solid #e2e8f0; border-radius:12px;
    display:flex; align-items:center; gap:14px; padding:13px 16px;
    transition:.2s; position:relative; overflow:hidden; width:100%; box-sizing:border-box;
}
.res-list-item:hover { border-color:<?= $color['from'] ?>; box-shadow:0 4px 14px rgba(0,0,0,.07); }
.res-list-item::before { content:''; position:absolute; left:0; top:0; bottom:0; width:4px; background:linear-gradient(180deg,<?= $color['from'] ?>,<?= $color['to'] ?>); border-radius:4px 0 0 4px; }
.res-list-icon { font-size:24px; width:42px; height:42px; flex-shrink:0; background:<?= $color['light'] ?>; border-radius:10px; display:flex; align-items:center; justify-content:center; }
.res-list-body { flex:1; min-width:0; }
.res-list-top { display:flex; align-items:center; gap:7px; margin-bottom:2px; flex-wrap:wrap; }
.res-list-name { font-size:14px; font-weight:800; color:#1e293b; }
.res-list-tag { background:<?= $color['light'] ?>; color:<?= $color['from'] ?>; font-size:10px; font-weight:800; padding:2px 8px; border-radius:20px; text-transform:uppercase; white-space:nowrap; }
.res-list-type { background:#f1f5f9; color:#475569; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; }
.res-list-popular { background:linear-gradient(135deg,<?= $color['from'] ?>,<?= $color['to'] ?>); color:white; font-size:9px; font-weight:800; padding:2px 8px; border-radius:20px; white-space:nowrap; }
.res-status-pill { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; }
.res-cap-pill { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; }
.res-cap-pill.active   { background:#dcfce7; color:#16a34a; }
.res-cap-pill.inactive { background:#fef9c3; color:#a16207; }
.res-cap-pill.none     { background:#f1f5f9; color:#94a3b8; }
.res-list-desc { font-size:12px; color:#64748b; line-height:1.5; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-bottom:3px; }
.res-list-meta { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.res-list-meta span { font-size:11px; color:#94a3b8; font-weight:600; }
.res-list-actions { display:flex; gap:5px; flex-shrink:0; align-items:center; }
.rl-btn { padding:7px 11px; border:none; border-radius:8px; font-weight:700; font-size:11px; cursor:pointer; transition:.15s; text-decoration:none; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; }
.rl-btn-edit    { background:#eef2ff; color:#6366f1; border:1px solid #c7d2fe; }
.rl-btn-edit:hover { background:#e0e7ff; color:#6366f1; }
.rl-btn-view    { background:linear-gradient(135deg,#0ea5e9,#2563eb); color:white; }
.rl-btn-view:hover { transform:translateY(-1px); color:white; }
.rl-btn-capture { background:linear-gradient(135deg,<?= $color['from'] ?>,<?= $color['to'] ?>); color:white; }
.rl-btn-capture:hover { transform:translateY(-1px); color:white; }
.rl-btn-delete  { background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; }
.rl-btn-delete:hover { background:#ffe4e6; }

/* ═══ VUE CARTES ═══ */
.res-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.res-card { background:white; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; transition:.2s; position:relative; }
.res-card:hover { border-color:<?= $color['from'] ?>; transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,.09); }
.res-card-popular { position:absolute; top:10px; right:10px; background:linear-gradient(135deg,<?= $color['from'] ?>,<?= $color['to'] ?>); color:white; font-size:9px; font-weight:800; padding:3px 9px; border-radius:20px; text-transform:uppercase; }
.res-card-icon { background:<?= $color['light'] ?>; padding:28px; text-align:center; font-size:46px; display:flex; align-items:center; justify-content:center; min-height:100px; }
.res-card-body { padding:14px; }
.res-card-badges { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:7px; }
.res-card-tag { background:<?= $color['light'] ?>; color:<?= $color['from'] ?>; font-size:10px; font-weight:800; padding:2px 9px; border-radius:20px; text-transform:uppercase; }
.res-card-type { background:#f1f5f9; color:#475569; font-size:10px; font-weight:700; padding:2px 9px; border-radius:20px; }
.res-card-name { font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; line-height:1.4; }
.res-card-desc { font-size:11px; color:#64748b; margin-bottom:10px; line-height:1.55; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.res-card-meta { display:flex; gap:8px; margin-bottom:8px; flex-wrap:wrap; }
.res-card-meta span { font-size:10px; color:#94a3b8; font-weight:600; }
.res-card-status { display:flex; align-items:center; gap:5px; border-radius:7px; padding:5px 9px; margin-bottom:10px; font-size:11px; font-weight:600; }
.res-card-btns { display:grid; grid-template-columns:1fr 1fr; gap:6px; }
.rc-btn { padding:7px 9px; border:none; border-radius:8px; font-weight:700; font-size:11px; cursor:pointer; transition:.15s; text-align:center; text-decoration:none; display:block; white-space:nowrap; }
.rc-btn-edit    { background:#eef2ff; color:#6366f1; border:1px solid #c7d2fe; }
.rc-btn-edit:hover { background:#e0e7ff; }
.rc-btn-capture { background:linear-gradient(135deg,<?= $color['from'] ?>,<?= $color['to'] ?>); color:white; }
.rc-btn-capture:hover { transform:translateY(-1px); color:white; }
.rc-btn-view    { background:linear-gradient(135deg,#0ea5e9,#2563eb); color:white; }
.rc-btn-view:hover { transform:translateY(-1px); color:white; }
.rc-btn-delete  { background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; font-size:10px; }
.rc-btn-delete:hover { background:#ffe4e6; }

/* Vide */
.res-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
.res-empty-icon { font-size:52px; margin-bottom:14px; }
.res-empty h3 { font-size:16px; font-weight:700; color:#64748b; margin-bottom:8px; }
.res-empty p  { font-size:13px; }

.res-hidden { display:none !important; }

/* Modal confirm suppression */
.res-confirm-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:3000; align-items:center; justify-content:center; }
.res-confirm-overlay.open { display:flex; }
.res-confirm-box { background:white; border-radius:14px; padding:32px; max-width:420px; width:94%; text-align:center; box-shadow:0 16px 50px rgba(0,0,0,.25); }
.res-confirm-box h3 { font-size:18px; font-weight:800; color:#1e293b; margin-bottom:10px; }
.res-confirm-box p  { font-size:13px; color:#64748b; margin-bottom:24px; line-height:1.6; }
.res-confirm-btns { display:flex; gap:10px; justify-content:center; }
.res-confirm-cancel { padding:10px 24px; background:#f1f5f9; border:none; border-radius:9px; font-weight:700; font-size:13px; cursor:pointer; color:#64748b; }
.res-confirm-ok { padding:10px 24px; background:linear-gradient(135deg,#e11d48,#be123c); border:none; border-radius:9px; font-weight:700; font-size:13px; cursor:pointer; color:white; }
</style>

<div class="res-wrap">

<?php if ($flash === 'created'): ?>
<div class="res-flash success">✅ Ressource créée avec succès.</div>
<?php elseif ($flash === 'updated'): ?>
<div class="res-flash success">✅ Ressource mise à jour.</div>
<?php elseif ($flash === 'deleted'): ?>
<div class="res-flash success">🗑️ Ressource supprimée.</div>
<?php elseif ($flash === 'error'): ?>
<div class="res-flash error">❌ Une erreur est survenue.</div>
<?php endif; ?>

<?php if (isset($db_error)): ?>
<div class="res-flash error">❌ DB : <?= htmlspecialchars($db_error) ?> — Avez-vous exécuté la migration SQL ?</div>
<?php endif; ?>

<!-- Header -->
<div class="res-header-bar">
  <div class="res-header-title">📚 Ressources & Guides</div>
  <a href="?page=ressources&action=edit&persona=<?= $selected_persona ?>" class="res-btn-new">
    ✨ Nouvelle ressource
  </a>
</div>

<!-- Tabs personas -->
<div class="res-tabs">
  <?php foreach ($personas as $key => $label): ?>
  <a href="?page=ressources&persona=<?= $key ?>" class="res-tab <?= $selected_persona === $key ? 'active' : '' ?>">
    <?= $label ?> <span class="res-tab-count"><?= $stats_db[$key] ?></span>
  </a>
  <?php endforeach; ?>
</div>

<!-- Hero -->
<div class="res-hero">
  <div>
    <div class="res-hero-title"><?= $hero['title'] ?></div>
    <div class="res-hero-sub"><?= $hero['sub'] ?></div>
  </div>
  <div class="res-hero-badge">
    <strong><?= count($filtered) ?></strong>
    <span>ressources</span>
  </div>
</div>

<!-- Toolbar -->
<div class="res-toolbar">
  <div class="res-toolbar-left">
    <div class="res-view-toggle">
      <button class="res-view-btn" id="btnViewList" onclick="setView('list')" title="Vue liste">☰</button>
      <button class="res-view-btn" id="btnViewGrid" onclick="setView('grid')" title="Vue cartes">⊞</button>
    </div>
    <span class="res-count-label"><?= count($filtered) ?> ressource<?= count($filtered)>1?'s':'' ?></span>
  </div>
</div>

<?php if (empty($filtered)): ?>
<!-- Vide -->
<div class="res-empty">
  <div class="res-empty-icon">📭</div>
  <h3>Aucune ressource pour ce persona</h3>
  <p>Créez votre première ressource en cliquant sur <strong>Nouvelle ressource</strong>.</p>
</div>

<?php else: ?>

<!-- ══ VUE LISTE ══ -->
<div class="res-list" id="viewList">
<?php foreach ($filtered as $r):
  $ss   = $status_styles[$r['status']] ?? $status_styles['draft'];
  $cap  = $r['cap_id'] ? true : false;
  $cs   = $r['cap_status'] ?? null;
  $chap = json_decode($r['chapitres'] ?? '[]', true);
?>
<div class="res-list-item" id="res-row-<?= $r['id'] ?>">
  <div class="res-list-icon"><?= htmlspecialchars($r['icon'] ?? '📄') ?></div>
  <div class="res-list-body">
    <div class="res-list-top">
      <span class="res-list-name"><?= htmlspecialchars($r['name']) ?></span>
      <?php if ($r['tag']): ?><span class="res-list-tag"><?= htmlspecialchars($r['tag']) ?></span><?php endif; ?>
      <span class="res-list-type"><?= $type_labels[$r['type']] ?? $r['type'] ?></span>
      <?php if ($r['popular']): ?><span class="res-list-popular">⭐ Populaire</span><?php endif; ?>
      <span class="res-status-pill" style="background:<?= $ss['bg'] ?>;border:1px solid <?= $ss['border'] ?>;color:<?= $ss['color'] ?>;">
        <span style="width:6px;height:6px;border-radius:50%;background:<?= $ss['dot'] ?>;display:inline-block;"></span>
        <?= $ss['label'] ?>
      </span>
      <?php if ($cap && $cs === 'active'): ?>
        <span class="res-cap-pill active">🟢 Capture active</span>
      <?php elseif ($cap && $cs === 'inactive'): ?>
        <span class="res-cap-pill inactive">🟡 Capture inactive</span>
      <?php else: ?>
        <span class="res-cap-pill none">⚪ Sans capture</span>
      <?php endif; ?>
    </div>
    <div class="res-list-desc"><?= htmlspecialchars($r['description'] ?? '') ?></div>
    <div class="res-list-meta">
      <?php if ($r['pages']): ?><span>📄 <?= htmlspecialchars($r['pages']) ?></span><?php endif; ?>
      <?php if ($r['format']): ?><span>📥 <?= htmlspecialchars($r['format']) ?></span><?php endif; ?>
      <?php if ($chap): ?><span>📋 <?= count($chap) ?> chapitres</span><?php endif; ?>
      <?php if ($cap): ?>
        <span style="font-family:monospace;font-size:10px;color:<?= $color['from'] ?>;background:<?= $color['light'] ?>;padding:2px 7px;border-radius:5px;">
          🔗 /capture/<?= htmlspecialchars($r['cap_slug'] ?: $r['slug']) ?>
        </span>
        <span>👁 <?= (int)($r['vues']??0) ?> vues</span>
        <span>🎯 <?= (int)($r['conversions']??0) ?> leads</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="res-list-actions">
    <a class="rl-btn rl-btn-edit" href="<?= $cap ? '?page=captures&action=edit&id=' . (int)$r['cap_id'] : '#'; ?>" <?= $cap ? '' : 'onclick="createCapture(' . (int)$r['id'] . ', \'' . htmlspecialchars(addslashes($r['slug'])) . '\', \'' . htmlspecialchars(addslashes($r['name'])) . '\', \'' . htmlspecialchars(addslashes($r['icon']??'📄')) . '\', \'' . htmlspecialchars(addslashes($r['description']??'')) . '\'); return false;"' ?>>✏️ Éditer</a>
    <?php if ($cap): ?>
      <a class="rl-btn rl-btn-view" href="/capture/<?= htmlspecialchars($r['cap_slug'] ?: $r['slug']) ?>" target="_blank">👁️ Voir</a>
      <a class="rl-btn rl-btn-capture" href="?page=captures&action=edit&id=<?= (int)$r['cap_id'] ?>">📄 Capture</a>
    <?php else: ?>
      <button class="rl-btn rl-btn-capture" onclick="createCapture(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['slug'])) ?>', '<?= htmlspecialchars(addslashes($r['name'])) ?>', '<?= htmlspecialchars(addslashes($r['icon']??'📄')) ?>', '<?= htmlspecialchars(addslashes($r['description']??'')) ?>')">
        ➕ Capture
      </button>
    <?php endif; ?>
    <button class="rl-btn rl-btn-delete" onclick="confirmDelete(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>')">🗑️</button>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- ══ VUE CARTES ══ -->
<div class="res-grid res-hidden" id="viewGrid">
<?php foreach ($filtered as $r):
  $ss  = $status_styles[$r['status']] ?? $status_styles['draft'];
  $cap = $r['cap_id'] ? true : false;
  $cs  = $r['cap_status'] ?? null;
?>
<div class="res-card" id="res-card-<?= $r['id'] ?>">
  <?php if ($r['popular']): ?><div class="res-card-popular">⭐ Populaire</div><?php endif; ?>
  <div class="res-card-icon"><?= htmlspecialchars($r['icon'] ?? '📄') ?></div>
  <div class="res-card-body">
    <div class="res-card-badges">
      <?php if ($r['tag']): ?><span class="res-card-tag"><?= htmlspecialchars($r['tag']) ?></span><?php endif; ?>
      <span class="res-card-type"><?= $type_labels[$r['type']] ?? $r['type'] ?></span>
    </div>
    <div class="res-card-name"><?= htmlspecialchars($r['name']) ?></div>
    <div class="res-card-desc"><?= htmlspecialchars($r['description'] ?? '') ?></div>
    <div class="res-card-meta">
      <?php if ($r['pages']): ?><span>📄 <?= htmlspecialchars($r['pages']) ?></span><?php endif; ?>
      <?php if ($r['format']): ?><span>📥 <?= htmlspecialchars($r['format']) ?></span><?php endif; ?>
    </div>
    <div class="res-card-status" style="background:<?= $ss['bg'] ?>;border:1px solid <?= $ss['border'] ?>;color:<?= $ss['color'] ?>;">
      <span style="width:7px;height:7px;border-radius:50%;background:<?= $ss['dot'] ?>;"></span>
      <?= $ss['label'] ?>
      <?php if ($cap && $cs === 'active'): ?> · 🟢 Capture active<?php elseif ($cap): ?> · 🟡 Capture inactive<?php else: ?> · ⚪ Sans capture<?php endif; ?>
    </div>
    <div class="res-card-btns">
      <a class="rc-btn rc-btn-edit" href="<?= $cap ? '?page=captures&action=edit&id=' . (int)$r['cap_id'] : '#'; ?>" <?= $cap ? '' : 'onclick="createCapture(' . (int)$r['id'] . ', \'' . htmlspecialchars(addslashes($r['slug'])) . '\', \'' . htmlspecialchars(addslashes($r['name'])) . '\', \'' . htmlspecialchars(addslashes($r['icon']??'📄')) . '\', \'' . htmlspecialchars(addslashes($r['description']??'')) . '\'); return false;"' ?>>✏️ Éditer</a>
      <?php if ($cap): ?>
        <a class="rc-btn rc-btn-capture" href="?page=captures&action=edit&id=<?= (int)$r['cap_id'] ?>">📄 Capture</a>
      <?php else: ?>
        <button class="rc-btn rc-btn-capture" onclick="createCapture(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['slug'])) ?>', '<?= htmlspecialchars(addslashes($r['name'])) ?>', '<?= htmlspecialchars(addslashes($r['icon']??'📄')) ?>', '<?= htmlspecialchars(addslashes($r['description']??'')) ?>')">
          ➕ Capture
        </button>
      <?php endif; ?>
      <?php if ($cap): ?>
        <a class="rc-btn rc-btn-view" href="/capture/<?= htmlspecialchars($r['cap_slug'] ?: $r['slug']) ?>" target="_blank">👁️ Voir</a>
      <?php endif; ?>
      <button class="rc-btn rc-btn-delete" onclick="confirmDelete(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>')">🗑️ Supprimer</button>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>
</div><!-- /res-wrap -->

<!-- Modal confirmation suppression -->
<div id="resConfirmOverlay" class="res-confirm-overlay">
  <div class="res-confirm-box">
    <h3>🗑️ Supprimer cette ressource ?</h3>
    <p id="resConfirmMsg">Cette action est irréversible.</p>
    <div class="res-confirm-btns">
      <button class="res-confirm-cancel" onclick="closeConfirm()">Annuler</button>
      <button class="res-confirm-ok" id="resConfirmOkBtn">Supprimer</button>
    </div>
  </div>
</div>

<script>
// ── Vue liste / cartes ──
function setView(v) {
    const isList = v === 'list';
    document.getElementById('viewList').classList.toggle('res-hidden', !isList);
    document.getElementById('viewGrid').classList.toggle('res-hidden',  isList);
    document.getElementById('btnViewList').classList.toggle('active',  isList);
    document.getElementById('btnViewGrid').classList.toggle('active', !isList);
    localStorage.setItem('res_view', v);
}
(function(){ setView(localStorage.getItem('res_view') || 'list'); })();

// ── Suppression ──
let _deleteId = null;
function confirmDelete(id, name) {
    _deleteId = id;
    document.getElementById('resConfirmMsg').textContent = '« ' + name + ' » sera définitivement supprimée.';
    document.getElementById('resConfirmOverlay').classList.add('open');
}
function closeConfirm() {
    document.getElementById('resConfirmOverlay').classList.remove('open');
    _deleteId = null;
}
document.getElementById('resConfirmOkBtn').addEventListener('click', async () => {
    if (!_deleteId) return;
    const btn = document.getElementById('resConfirmOkBtn');
    btn.textContent = '⏳ Suppression…'; btn.disabled = true;
    try {
        const fd = new FormData();
        fd.append('res_action', 'delete');
        fd.append('id', _deleteId);
        const resp = await fetch('?page=ressources&api=1', { method:'POST', body:fd });
        const data = await resp.json();
        if (data.success) {
            // Retirer la ligne de l'UI
            ['res-row-','res-card-'].forEach(pfx => {
                const el = document.getElementById(pfx + _deleteId);
                if (el) el.remove();
            });
            closeConfirm();
        } else {
            alert('Erreur : ' + (data.error || 'inconnue'));
            btn.textContent = 'Supprimer'; btn.disabled = false;
        }
    } catch(e) {
        alert('Erreur réseau');
        btn.textContent = 'Supprimer'; btn.disabled = false;
    }
});
document.getElementById('resConfirmOverlay').addEventListener('click', e => {
    if (e.target.id === 'resConfirmOverlay') closeConfirm();
});

// ── Créer une capture depuis le catalogue ──
async function createCapture(resId, slug, name, icon, desc) {
    const btn = event.currentTarget;
    btn.textContent = '⏳…'; btn.disabled = true;
    try {
        const fd = new FormData();
        fd.append('res_action', 'create_capture');
        fd.append('res_id',     resId);
        fd.append('guide_id',   slug);
        fd.append('guide_name', name);
        fd.append('guide_icon', icon);
        fd.append('guide_desc', desc);
        const resp = await fetch('?page=ressources&api=1', { method:'POST', body:fd });
        const data = await resp.json();
        if (data.success) {
            window.location.href = '?page=captures&action=edit&id=' + data.capture_id;
        } else {
            alert('Erreur : ' + (data.error || 'inconnue'));
            btn.textContent = '➕ Capture'; btn.disabled = false;
        }
    } catch(e) {
        alert('Erreur réseau');
        btn.textContent = '➕ Capture'; btn.disabled = false;
    }
}
</script>
