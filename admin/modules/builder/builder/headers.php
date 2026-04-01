<?php
// ================================================================
//  Headers — Liste & gestion
//  admin/modules/builder/builder/headers.php
//  Route : dashboard.php?page=headers
// ================================================================
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit('Accès refusé'); }

// ── Connexion DB — compatible dashboard v8.4 ──
// $pdo dans dashboard = Database::getInstance() (objet Database, pas PDO)
// On récupère la vraie connexion PDO
$db = null;
if (isset($pdo)) {
    if ($pdo instanceof PDO) {
        $db = $pdo;
    } elseif (method_exists($pdo, 'getConnection')) {
        $db = $pdo->getConnection();
    } elseif (method_exists($pdo, 'query')) {
        $db = $pdo; // utilise directement si ça supporte query/prepare
    }
}
// Fallback via Database::getInstance()
if (!$db) {
    try {
        if (!defined('DB_HOST')) require_once __DIR__ . '/../../../../config/config.php';
        if (!class_exists('Database')) require_once __DIR__ . '/../../../../includes/classes/Database.php';
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
            $db->prepare("DELETE FROM headers WHERE id = ? AND is_default = 0")->execute([(int)$_POST['id']]);
            $msg = '✅ Header supprimé.';
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }

    if ($action === 'duplicate' && !empty($_POST['id'])) {
        try {
            $st = $db->prepare("SELECT * FROM headers WHERE id = ?");
            $st->execute([(int)$_POST['id']]);
            $orig = $st->fetch(PDO::FETCH_ASSOC);
            if ($orig) {
                unset($orig['id'], $orig['created_at'], $orig['updated_at']);
                $orig['name']       = $orig['name'] . ' (copie)';
                $orig['slug']       = $orig['slug'] . '-' . time();
                $orig['is_default'] = 0;
                $orig['status']     = 'draft';
                $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($orig)));
                $vals = implode(',', array_map(fn($k) => ":$k", array_keys($orig)));
                $db->prepare("INSERT INTO headers ($cols) VALUES ($vals)")->execute($orig);
                $newId = $db->lastInsertId();
                $msg = '✅ Dupliqué. <a href="?page=headers-edit&id=' . $newId . '" style="text-decoration:underline">Éditer →</a>';
            }
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }

    if ($action === 'toggle_status' && !empty($_POST['id'])) {
        try {
            $st = $db->prepare("SELECT status FROM headers WHERE id = ?");
            $st->execute([(int)$_POST['id']]);
            $cur = $st->fetchColumn();
            $new = ($cur === 'active') ? 'inactive' : 'active';
            $db->prepare("UPDATE headers SET status = ? WHERE id = ?")->execute([$new, (int)$_POST['id']]);
            $msg = '✅ Statut : ' . $new;
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }

    if ($action === 'set_default' && !empty($_POST['id'])) {
        try {
            $db->exec("UPDATE headers SET is_default = 0");
            $db->prepare("UPDATE headers SET is_default = 1, status = 'active' WHERE id = ?")->execute([(int)$_POST['id']]);
            $msg = '✅ Header défini par défaut.';
        } catch (Exception $e) { $err = '❌ ' . $e->getMessage(); }
    }
}

// ── Chargement liste ──
$headers = [];
try {
    $headers = $db->query(
        "SELECT id, name, slug, type, status, is_default, logo_type, logo_text, logo_url,
                bg_color, text_color, height, sticky, cta_enabled, cta_text,
                created_at, updated_at
         FROM headers ORDER BY is_default DESC, updated_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $err = '❌ Erreur chargement : ' . $e->getMessage();
}
?>

<style>
.hdrs-wrap { max-width: 1100px; }
.hdrs-topbar { display:flex; align-items:center; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.hdrs-topbar h1 { font-family:'Playfair Display',serif; font-size:22px; font-weight:700; flex:1; margin:0; }
.hdrs-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
.hdr-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    overflow:hidden; transition:.15s; position:relative;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.hdr-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); transform:translateY(-2px); }
.hdr-card.is-default { border-color:#1a4d7a; box-shadow:0 0 0 2px rgba(26,77,122,.15); }
.hdr-preview {
    height:72px; display:flex; align-items:center; padding:0 20px;
    overflow:hidden; gap:12px; border-bottom:1px solid #e2e8f0;
}
.hdr-preview-logo { font-weight:800; font-size:15px; font-family:'Playfair Display',serif; white-space:nowrap; }
.hdr-preview-nav { display:flex; gap:12px; flex:1; overflow:hidden; }
.hdr-preview-nav span { font-size:11px; font-weight:500; white-space:nowrap; opacity:.7; }
.hdr-preview-cta { font-size:11px; font-weight:700; padding:5px 12px; border-radius:6px; white-space:nowrap; flex-shrink:0; }
.hdr-body { padding:14px 16px; }
.hdr-name { font-weight:700; font-size:14px; margin-bottom:4px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.hdr-meta { font-size:11px; color:#64748b; display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.hdr-actions { display:flex; gap:6px; flex-wrap:wrap; }
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
.default-star { position:absolute; top:8px; right:8px; font-size:16px; }
.hdr-alert { padding:10px 14px; border-radius:8px; font-size:12px; font-weight:600; margin-bottom:16px; }
.hdr-alert.ok { background:#d1fae5; color:#065f46; }
.hdr-alert.er { background:#fee2e2; color:#dc2626; }
.empty-state { text-align:center; padding:60px 20px; color:#64748b; }
.empty-state .ico { font-size:48px; opacity:.2; margin-bottom:16px; }
</style>

<div class="hdrs-wrap">

  <div class="hdrs-topbar">
    <h1>🏗️ Headers</h1>
    <?php if ($msg): ?><div class="hdr-alert ok"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="hdr-alert er"><?= $err ?></div><?php endif; ?>
    <a href="?page=headers-edit" class="act-btn primary">+ Nouveau header</a>
  </div>

  <?php if (empty($headers)): ?>
  <div class="empty-state">
    <div class="ico">🏗️</div>
    <h3 style="font-size:16px;font-weight:700;margin-bottom:8px">Aucun header trouvé</h3>
    <p style="margin-bottom:16px">Créez votre premier header pour personnaliser la navigation.</p>
    <a href="?page=headers-edit" class="act-btn primary" style="display:inline-flex">+ Créer un header</a>
  </div>
  <?php else: ?>
  <div class="hdrs-grid">
    <?php foreach ($headers as $h):
        $bg      = htmlspecialchars($h['bg_color'] ?? '#ffffff');
        $tc      = htmlspecialchars($h['text_color'] ?? '#1e293b');
        $ht      = (int)($h['height'] ?? 80);
        $logoHtml = ($h['logo_type'] === 'image' && !empty($h['logo_url']))
            ? '<img src="' . htmlspecialchars($h['logo_url']) . '" style="height:32px;width:auto;" alt="">'
            : '<span class="hdr-preview-logo" style="color:' . $tc . '">' . htmlspecialchars($h['logo_text'] ?: $h['name']) . '</span>';
    ?>
    <div class="hdr-card <?= $h['is_default'] ? 'is-default' : '' ?>">
      <?php if ($h['is_default']): ?><div class="default-star">⭐</div><?php endif; ?>

      <div class="hdr-preview" style="background:<?= $bg ?>;min-height:<?= min($ht,72) ?>px">
        <?= $logoHtml ?>
        <div class="hdr-preview-nav">
          <span style="color:<?= $tc ?>">Accueil</span>
          <span style="color:<?= $tc ?>">Acheter</span>
          <span style="color:<?= $tc ?>">Vendre</span>
          <span style="color:<?= $tc ?>">Blog</span>
        </div>
        <?php if ($h['cta_enabled'] && $h['cta_text']): ?>
        <span class="hdr-preview-cta" style="background:#1a4d7a;color:#fff"><?= htmlspecialchars($h['cta_text']) ?></span>
        <?php endif; ?>
      </div>

      <div class="hdr-body">
        <div class="hdr-name">
          <?= htmlspecialchars($h['name']) ?>
          <span class="badge-s badge-<?= $h['status'] ?>"><?= $h['status'] ?></span>
          <?php if ($h['is_default']): ?><span class="badge-s badge-default">Défaut</span><?php endif; ?>
        </div>
        <div class="hdr-meta">
          <span>📐 <?= htmlspecialchars($h['type']) ?></span>
          <span>📏 <?= $h['height'] ?>px</span>
          <?php if ($h['sticky']): ?><span>📌 Sticky</span><?php endif; ?>
          <span>🕐 <?= date('d/m/Y', strtotime($h['updated_at'] ?: $h['created_at'])) ?></span>
        </div>

        <div class="hdr-actions">
          <a href="?page=headers-edit&id=<?= $h['id'] ?>" class="act-btn primary">✏️ Éditer</a>

          <?php if (!$h['is_default']): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="set_default">
            <input type="hidden" name="id" value="<?= $h['id'] ?>">
            <button class="act-btn gold">⭐ Défaut</button>
          </form>
          <?php endif; ?>

          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="id" value="<?= $h['id'] ?>">
            <button class="act-btn"><?= $h['status']==='active' ? '⏸️ Désactiver' : '▶️ Activer' ?></button>
          </form>

          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="duplicate">
            <input type="hidden" name="id" value="<?= $h['id'] ?>">
            <button class="act-btn">📋 Dupliquer</button>
          </form>

          <?php if (!$h['is_default']): ?>
          <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $h['id'] ?>">
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