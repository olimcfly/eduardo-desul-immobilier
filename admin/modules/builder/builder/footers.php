<?php
// ================================================================
//  Footers — Liste & gestion
//  admin/modules/builder/builder/footers.php
//  Route : dashboard.php?page=footers
// ================================================================
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit('Accès refusé'); }

// ── Connexion DB robuste (même pattern que headers.php) ──
$db = null;
if (isset($pdo)) {
    if ($pdo instanceof PDO) $db = $pdo;
    elseif (method_exists($pdo, 'getConnection')) $db = $pdo->getConnection();
    elseif (method_exists($pdo, 'query')) $db = $pdo;
}
if (!$db) {
    try {
if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
        if (!defined('DB_HOST')) require_once __DIR__ . '/../../../../config/config.php';
    if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
        $db = Database::getInstance();
    } catch (Exception $e) {
        echo '<div style="padding:20px;color:#dc2626;background:#fee2e2;border-radius:8px;margin:20px">Connexion DB impossible : ' . htmlspecialchars($e->getMessage()) . '</div>';
        return;
    }
}

$msg = '';
$err = '';

// ── Actions POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && !empty($_POST['id'])) {
        try {
            $db->prepare("DELETE FROM footers WHERE id = ? AND is_default = 0")->execute([(int)$_POST['id']]);
            $msg = '✅ Footer supprimé.';
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }

    if ($action === 'duplicate' && !empty($_POST['id'])) {
        try {
            $st = $db->prepare("SELECT * FROM footers WHERE id = ?");
            $st->execute([(int)$_POST['id']]);
            $orig = $st->fetch(PDO::FETCH_ASSOC);
            if ($orig) {
                unset($orig['id'], $orig['created_at'], $orig['updated_at']);
                $orig['name']       = $orig['name'] . ' (copie)';
                $orig['slug']       = ($orig['slug'] ?? 'footer') . '-' . time();
                $orig['is_default'] = 0;
                $orig['status']     = 'draft';
                $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($orig)));
                $vals = implode(',', array_map(fn($k) => ":$k", array_keys($orig)));
                $db->prepare("INSERT INTO footers ($cols) VALUES ($vals)")->execute($orig);
                $newId = $db->lastInsertId();
                $msg = '✅ Dupliqué. <a href="?page=footers-edit&id=' . $newId . '" style="text-decoration:underline">Éditer →</a>';
            }
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }

    if ($action === 'toggle_status' && !empty($_POST['id'])) {
        try {
            $st = $db->prepare("SELECT status FROM footers WHERE id = ?");
            $st->execute([(int)$_POST['id']]);
            $cur = $st->fetchColumn();
            $new = ($cur === 'active') ? 'inactive' : 'active';
            $db->prepare("UPDATE footers SET status = ? WHERE id = ?")->execute([$new, (int)$_POST['id']]);
            $msg = '✅ Statut : ' . $new;
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }

    if ($action === 'set_default' && !empty($_POST['id'])) {
        try {
            $db->exec("UPDATE footers SET is_default = 0");
            $db->prepare("UPDATE footers SET is_default = 1, status = 'active' WHERE id = ?")->execute([(int)$_POST['id']]);
            $msg = '✅ Footer défini par défaut.';
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }
}

// ── Chargement liste ──
$footers = [];
try {
    $footers = $db->query(
        "SELECT id, name, slug, type, status, is_default,
                bg_color, text_color, custom_html, custom_css,
                created_at, updated_at
         FROM footers ORDER BY is_default DESC, updated_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $err = '❌ Erreur chargement : ' . $e->getMessage();
}
?>

<style>
.ftrs-wrap { max-width: 1100px; }
.ftrs-topbar { display:flex; align-items:center; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.ftrs-topbar h1 { font-family:'Playfair Display',serif; font-size:22px; font-weight:700; flex:1; margin:0; }
.ftrs-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
.ftr-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    overflow:hidden; transition:.15s; position:relative;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.ftr-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); transform:translateY(-2px); }
.ftr-card.is-default { border-color:#1a4d7a; box-shadow:0 0 0 2px rgba(26,77,122,.15); }

/* Preview footer — rendu réel en iframe réduite */
.ftr-preview {
    height:90px; overflow:hidden; position:relative;
    border-bottom:1px solid #e2e8f0; background:#1e293b;
}
.ftr-preview iframe {
    width:200%; height:200%; border:none;
    transform:scale(.5); transform-origin:top left;
    pointer-events:none;
}
.ftr-preview-placeholder {
    height:90px; display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,#1e293b,#334155);
    color:rgba(255,255,255,.15); font-size:32px;
}

.ftr-body { padding:14px 16px; }
.ftr-name { font-weight:700; font-size:14px; margin-bottom:4px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.ftr-meta { font-size:11px; color:#64748b; display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.ftr-actions { display:flex; gap:6px; flex-wrap:wrap; }
.act-btn {
    padding:5px 11px; border-radius:7px; font-size:11px; font-weight:700;
    cursor:pointer; border:1px solid #e2e8f0; background:#f8fafc;
    color:#1e293b; text-decoration:none; display:inline-flex; align-items:center; gap:5px;
    transition:.12s;
}
.act-btn:hover { background:#e2e8f0; }
.act-btn.primary { background:#1a4d7a; color:#fff; border-color:#1a4d7a; }
.act-btn.primary:hover { background:#1557a0; }
.act-btn.danger  { background:#fee2e2; color:#dc2626; border-color:#fecaca; }
.act-btn.danger:hover { background:#dc2626; color:#fff; }
.act-btn.gold    { background:#d4a574; color:#fff; border-color:#d4a574; }
.badge-s { padding:2px 7px; border-radius:5px; font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.4px; }
.badge-active   { background:#d1fae5; color:#065f46; }
.badge-draft    { background:#f1f5f9; color:#64748b; }
.badge-inactive { background:#fee2e2; color:#dc2626; }
.badge-default  { background:#eff6ff; color:#1a4d7a; border:1px solid #bfdbfe; }
.default-star { position:absolute; top:8px; right:8px; font-size:16px; z-index:2; }
.ftr-alert { padding:10px 14px; border-radius:8px; font-size:12px; font-weight:600; margin-bottom:16px; }
.ftr-alert.ok { background:#d1fae5; color:#065f46; }
.ftr-alert.er { background:#fee2e2; color:#dc2626; }
.empty-state { text-align:center; padding:60px 20px; color:#64748b; }
.empty-state .ico { font-size:48px; opacity:.2; margin-bottom:16px; }
</style>

<div class="ftrs-wrap">

  <div class="ftrs-topbar">
    <h1>🦶 Footers</h1>
    <?php if ($msg): ?><div class="ftr-alert ok"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="ftr-alert er"><?= $err ?></div><?php endif; ?>
    <a href="?page=footers-edit" class="act-btn primary">+ Nouveau footer</a>
  </div>

  <?php if (empty($footers)): ?>
  <div class="empty-state">
    <div class="ico">🦶</div>
    <h3 style="font-size:16px;font-weight:700;margin-bottom:8px">Aucun footer trouvé</h3>
    <p style="margin-bottom:16px">Créez votre premier footer pour personnaliser le bas de page.</p>
    <a href="?page=footers-edit" class="act-btn primary" style="display:inline-flex">+ Créer un footer</a>
  </div>
  <?php else: ?>
  <div class="ftrs-grid">
    <?php foreach ($footers as $f):
        $bg  = htmlspecialchars($f['bg_color'] ?? '#1e293b');
        $tc  = htmlspecialchars($f['text_color'] ?? '#94a3b8');
        $html = trim($f['custom_html'] ?? '');
        $css  = trim($f['custom_css'] ?? '');
        $date = date('d/m/Y', strtotime($f['updated_at'] ?: $f['created_at']));
    ?>
    <div class="ftr-card <?= $f['is_default'] ? 'is-default' : '' ?>">
      <?php if ($f['is_default']): ?><div class="default-star">⭐</div><?php endif; ?>

      <!-- Preview réelle si HTML dispo, sinon placeholder -->
      <?php if (!empty($html)): ?>
      <div class="ftr-preview">
        <iframe srcdoc="<!DOCTYPE html><html><head><meta charset='utf-8'><style>body{margin:0;font-family:'DM Sans',sans-serif;font-size:12px;overflow:hidden}<?= htmlspecialchars($css) ?></style></head><body><?= htmlspecialchars($html) ?></body></html>" sandbox="allow-same-origin" loading="lazy"></iframe>
      </div>
      <?php else: ?>
      <div class="ftr-preview-placeholder" style="background:<?= $bg ?>">
        <span style="color:<?= $tc ?>;font-size:13px;font-weight:700;opacity:.4"><?= htmlspecialchars($f['name']) ?></span>
      </div>
      <?php endif; ?>

      <div class="ftr-body">
        <div class="ftr-name">
          <?= htmlspecialchars($f['name']) ?>
          <span class="badge-s badge-<?= $f['status'] ?>"><?= $f['status'] ?></span>
          <?php if ($f['is_default']): ?><span class="badge-s badge-default">Défaut</span><?php endif; ?>
        </div>
        <div class="ftr-meta">
          <span>📐 <?= htmlspecialchars($f['type'] ?? 'standard') ?></span>
          <span style="background:<?= $bg ?>;color:<?= $tc ?>;padding:1px 6px;border-radius:4px;font-size:10px"><?= $bg ?></span>
          <span>🕐 <?= $date ?></span>
        </div>

        <div class="ftr-actions">
          <a href="?page=footers-edit&id=<?= $f['id'] ?>" class="act-btn primary">✏️ Éditer</a>

          <?php if (!$f['is_default']): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="set_default">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button class="act-btn gold">⭐ Défaut</button>
          </form>
          <?php endif; ?>

          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button class="act-btn"><?= $f['status']==='active' ? '⏸️ Désactiver' : '▶️ Activer' ?></button>
          </form>

          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="duplicate">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button class="act-btn">📋 Dupliquer</button>
          </form>

          <?php if (!$f['is_default']): ?>
          <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce footer ?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button class="act-btn danger">🗑️</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>