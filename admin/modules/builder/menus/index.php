<?php
/**
 * MODULE MENUS v2 — IMMO LOCAL+
 * /admin/modules/builder/menus/index.php
 * Nouveauté : champ URL dynamique avec autocomplete pages DB
 */
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit('Accès refusé'); }

// ── DB ──
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
        $db = Database::getInstance();
    } catch (Exception $e) {
        echo '<div style="padding:20px;background:#fee2e2;color:#dc2626;border-radius:8px">DB: '.htmlspecialchars($e->getMessage()).'</div>';
        return;
    }
}

$msg = ''; $err = '';
$tab = $_GET['tab'] ?? 'menu';

// ══════════════════════════════════════════
// CHARGER TOUTES LES PAGES POUR AUTOCOMPLETE
// ══════════════════════════════════════════
$allPages = [];

// Pages CMS
try {
    $rows = $db->query("SELECT title, slug FROM pages WHERE status IN ('published','active') ORDER BY title LIMIT 200")->fetchAll();
    foreach ($rows as $r) $allPages[] = ['group'=>'Pages CMS','icon'=>'📄','title'=>$r['title'],'url'=>'/'.ltrim($r['slug'],'/')];
} catch(Exception $e) {
    try {
        $rows = $db->query("SELECT title, slug FROM pages ORDER BY title LIMIT 200")->fetchAll();
        foreach ($rows as $r) $allPages[] = ['group'=>'Pages CMS','icon'=>'📄','title'=>$r['title'],'url'=>'/'.ltrim($r['slug'],'/')];
    } catch(Exception $e2) {}
}

// Articles
try {
    $rows = $db->query("SELECT title, slug FROM articles WHERE status IN ('published','active') ORDER BY title LIMIT 200")->fetchAll();
    foreach ($rows as $r) $allPages[] = ['group'=>'Articles Blog','icon'=>'📰','title'=>$r['title'],'url'=>'/blog/'.ltrim($r['slug'],'/')];
} catch(Exception $e) {
    try {
        $rows = $db->query("SELECT title, slug FROM articles ORDER BY title LIMIT 200")->fetchAll();
        foreach ($rows as $r) $allPages[] = ['group'=>'Articles Blog','icon'=>'📰','title'=>$r['title'],'url'=>'/blog/'.ltrim($r['slug'],'/')];
    } catch(Exception $e2) {}
}

// Secteurs / Quartiers
try {
    $rows = $db->query("SELECT title, slug FROM secteurs WHERE status IN ('published','active') ORDER BY title LIMIT 200")->fetchAll();
    foreach ($rows as $r) $allPages[] = ['group'=>'Quartiers','icon'=>'🗺️','title'=>$r['title'],'url'=>'/'.ltrim($r['slug'],'/')];
} catch(Exception $e) {
    try {
        $rows = $db->query("SELECT title, slug FROM secteurs ORDER BY title LIMIT 200")->fetchAll();
        foreach ($rows as $r) $allPages[] = ['group'=>'Quartiers','icon'=>'🗺️','title'=>$r['title'],'url'=>'/'.ltrim($r['slug'],'/')];
    } catch(Exception $e2) {}
}

// Pages de capture
try {
    $rows = $db->query("SELECT title, slug FROM captures WHERE status IN ('published','active') ORDER BY title LIMIT 100")->fetchAll();
    foreach ($rows as $r) $allPages[] = ['group'=>'Pages capture','icon'=>'⚡','title'=>$r['title'],'url'=>'/capture/'.ltrim($r['slug'],'/')];
} catch(Exception $e) {
    try {
        $rows = $db->query("SELECT title, slug FROM captures ORDER BY title LIMIT 100")->fetchAll();
        foreach ($rows as $r) $allPages[] = ['group'=>'Pages capture','icon'=>'⚡','title'=>$r['title'],'url'=>'/capture/'.ltrim($r['slug'],'/')];
    } catch(Exception $e2) {}
}

// Liens fixes utiles
$fixed = [
    ['group'=>'Liens fixes','icon'=>'🏠','title'=>'Accueil',              'url'=>'/'],
    ['group'=>'Liens fixes','icon'=>'📩','title'=>'Contact',              'url'=>'/contact'],
    ['group'=>'Liens fixes','icon'=>'📝','title'=>'Blog',                 'url'=>'/blog'],
    ['group'=>'Liens fixes','icon'=>'📊','title'=>'Estimation gratuite',  'url'=>'/estimation'],
    ['group'=>'Liens fixes','icon'=>'🏡','title'=>'Acheter',              'url'=>'/acheter'],
    ['group'=>'Liens fixes','icon'=>'💰','title'=>'Vendre',               'url'=>'/vendre'],
    ['group'=>'Liens fixes','icon'=>'💼','title'=>'Investir',             'url'=>'/investir'],
    ['group'=>'Liens fixes','icon'=>'💳','title'=>'Financement',          'url'=>'/financement'],
    ['group'=>'Liens fixes','icon'=>'⚖️','title'=>'Mentions légales',     'url'=>'/mentions-legales'],
    ['group'=>'Liens fixes','icon'=>'🔒','title'=>'Politique confidentialité','url'=>'/politique-confidentialite'],
];
$allPages = array_merge($fixed, $allPages);

// ══════════════════════════════════════════
// HEADER & FOOTER ACTIFS
// ══════════════════════════════════════════
$activeHeader = null; $headerNavLinks = [];
try {
    $activeHeader = $db->query("SELECT * FROM headers WHERE status='active' AND is_default=1 ORDER BY id LIMIT 1")->fetch();
    if (!$activeHeader) $activeHeader = $db->query("SELECT * FROM headers WHERE status='active' ORDER BY id LIMIT 1")->fetch();
    if ($activeHeader) {
        foreach (['nav_links','menu_json'] as $col) {
            $raw = $activeHeader[$col] ?? null;
            if ($raw) { $d = json_decode($raw,true); if (is_array($d) && !isset($d['name'])) { $headerNavLinks=$d; break; } }
        }
    }
} catch(Exception $e){}

$activeFooter = null; $footerCols = []; $footerMeta = [];
try {
    $activeFooter = $db->query("SELECT * FROM footers WHERE status='active' AND is_default=1 ORDER BY id LIMIT 1")->fetch();
    if (!$activeFooter) $activeFooter = $db->query("SELECT * FROM footers WHERE status='active' ORDER BY id LIMIT 1")->fetch();
    if ($activeFooter) {
        $d = json_decode($activeFooter['columns_json']??'[]',true);
        if (is_array($d) && !isset($d['name'])) $footerCols=$d;
        $footerMeta = ['copyright_text'=>$activeFooter['copyright_text']??'© '.date('Y').' Eduardo Desul Immobilier.','badge_text'=>$activeFooter['badge_text']??'CPI 7501 2021 000 000 444','phone'=>$activeFooter['phone']??'06 24 10 58 16','email'=>$activeFooter['email']??'contact@eduardo-desul-immobilier.fr','address'=>$activeFooter['address']??'12A rue du Commandant Charcot, 33290 Blanquefort','social_links'=>$activeFooter['social_links']??'[]'];
    }
} catch(Exception $e){}

if (empty($headerNavLinks)) $headerNavLinks = [
    ['label'=>'Accueil','url'=>'/','target'=>'_self'],
    ['label'=>'Acheter','url'=>'/acheter','target'=>'_self'],
    ['label'=>'Vendre','url'=>'/vendre','target'=>'_self'],
    ['label'=>'Estimation','url'=>'/estimation','target'=>'_self'],
    ['label'=>'Blog','url'=>'/blog','target'=>'_self'],
    ['label'=>'Contact','url'=>'/contact','target'=>'_self'],
];
if (empty($footerCols)) $footerCols = [
    ['title'=>'Services','links'=>[['label'=>'Acheter','url'=>'/acheter'],['label'=>'Vendre','url'=>'/vendre'],['label'=>'Estimation','url'=>'/estimation']]],
    ['title'=>'Secteurs','links'=>[['label'=>'Bordeaux','url'=>'/bordeaux'],['label'=>'Blanquefort','url'=>'/blanquefort']]],
    ['title'=>'Infos','links'=>[['label'=>'Blog','url'=>'/blog'],['label'=>'Contact','url'=>'/contact'],['label'=>'Mentions légales','url'=>'/mentions-legales']]],
];
if (empty($footerMeta)) $footerMeta=['copyright_text'=>'© '.date('Y').' Eduardo Desul Immobilier.','badge_text'=>'CPI 7501 2021 000 000 444','phone'=>'06 24 10 58 16','email'=>'contact@eduardo-desul-immobilier.fr','address'=>'12A rue du Commandant Charcot, 33290 Blanquefort','social_links'=>'[]'];
$socialLinks = json_decode($footerMeta['social_links']??'[]',true) ?: [];

// ══════════════════════════════════════════
// SAUVEGARDES
// ══════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_menu') {
        try {
            $links = json_decode($_POST['nav_links_json']??'[]',true) ?: [];
            $json  = json_encode($links, JSON_UNESCAPED_UNICODE);
            $saved = false;
            if ($activeHeader) {
                foreach (['nav_links','menu_json','custom_html'] as $col) {
                    try { $db->prepare("UPDATE headers SET `$col`=?, updated_at=NOW() WHERE id=?")->execute([$json,$activeHeader['id']]); $saved=true; break; } catch(Exception $e2){}
                }
            }
            $msg = $saved ? '✅ Menu principal sauvegardé.' : '⚠️ Aucun header actif.';
            $headerNavLinks = $links;
        } catch(Exception $e){ $err='❌ '.$e->getMessage(); }
    }
    if ($action === 'save_footer_links') {
        try {
            $cols = json_decode($_POST['footer_cols_json']??'[]',true) ?: [];
            $json = json_encode($cols, JSON_UNESCAPED_UNICODE);
            if ($activeFooter) { try { $db->prepare("UPDATE footers SET columns_json=?, updated_at=NOW() WHERE id=?")->execute([$json,$activeFooter['id']]); } catch(Exception $e2){} }
            $footerCols = $cols;
            $msg = '✅ Liens footer sauvegardés.';
        } catch(Exception $e){ $err='❌ '.$e->getMessage(); }
    }
    if ($action === 'save_footer_meta') {
        try {
            $socArr = json_decode($_POST['social_json']??'[]',true) ?: [];
            $upd = ['copyright_text'=>trim($_POST['copyright_text']??''),'badge_text'=>trim($_POST['badge_text']??''),'phone'=>trim($_POST['phone']??''),'email'=>trim($_POST['email']??''),'address'=>trim($_POST['address']??''),'social_links'=>json_encode($socArr,JSON_UNESCAPED_UNICODE)];
            if ($activeFooter) { foreach ($upd as $c=>$v) { try { $db->prepare("UPDATE footers SET `$c`=?, updated_at=NOW() WHERE id=?")->execute([$v,$activeFooter['id']]); } catch(Exception $e2){} } }
            $footerMeta = array_merge($footerMeta,$upd); $socialLinks=$socArr;
            $msg = '✅ Paramètres footer sauvegardés.';
        } catch(Exception $e){ $err='❌ '.$e->getMessage(); }
    }
}

$pagesJson = json_encode($allPages, JSON_UNESCAPED_UNICODE);
?>
<style>
:root{--P:#1a4d7a;--A:#d4a574;--BG:#f9f6f3;--W:#fff;--BD:#e2e8f0;--TX:#1e293b;--MT:#64748b}
.mn-tabs{display:flex;border-bottom:2px solid var(--BD);margin-bottom:24px}
.mn-tab{padding:10px 22px;font-size:13px;font-weight:700;color:var(--MT);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:.15s}
.mn-tab:hover{color:var(--P)}.mn-tab.on{color:var(--P);border-bottom-color:var(--P)}
.mn-grid{display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start}
@media(max-width:860px){.mn-grid{grid-template-columns:1fr}}
.mn-card{background:var(--W);border:1px solid var(--BD);border-radius:12px;overflow:visible}
.mn-card-hd{display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-bottom:1px solid var(--BD);background:#fafbfc;border-radius:12px 12px 0 0}
.mn-card-hd h3{font-size:14px;font-weight:700;color:var(--TX);display:flex;align-items:center;gap:7px;margin:0}
.mn-card-body{padding:16px}
.alert{padding:10px 14px;border-radius:8px;font-size:12px;font-weight:700;margin-bottom:16px}
.alert-ok{background:#d1fae5;color:#065f46}.alert-er{background:#fee2e2;color:#991b1b}
.info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:11px 14px;font-size:12px;color:#1d4ed8;margin-bottom:16px}
.btn-add{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;background:var(--P);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:700;transition:.15s}
.btn-add:hover{background:#1557a0}.btn-add.sm{padding:4px 9px;font-size:11px}
.btn-save{padding:10px 24px;background:var(--P);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:13px;cursor:pointer;transition:.15s}
.btn-save:hover{background:#1557a0}
.btn-del{width:28px;height:28px;border:none;background:#fee2e2;color:#ef4444;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;transition:.15s}
.btn-del:hover{background:#ef4444;color:#fff}

/* ── Ligne de lien ── */
.lnk-row{display:flex;align-items:center;gap:7px;padding:8px 10px;background:var(--BG);border:1px solid var(--BD);border-radius:10px;margin-bottom:7px;cursor:grab}
.lnk-row.dragging{opacity:.4;cursor:grabbing}
.drag-h{color:#cbd5e1;font-size:16px;flex-shrink:0;cursor:grab}
.lnk-lbl{width:125px;padding:6px 9px;border:1px solid var(--BD);border-radius:7px;font-size:13px;font-weight:600;background:#fff;color:var(--TX);flex-shrink:0}
.lnk-lbl:focus{outline:none;border-color:var(--P)}

/* ── URL autocomplete ── */
.url-wrap{flex:1;position:relative}
.url-inner{display:flex;align-items:center;background:#fff;border:1px solid var(--BD);border-radius:7px;overflow:hidden;transition:.15s}
.url-inner:focus-within{border-color:var(--P);box-shadow:0 0 0 3px rgba(26,77,122,.08)}
.url-pg-icon{padding:0 8px;font-size:13px;flex-shrink:0;line-height:1;pointer-events:none}
.url-txt{flex:1;padding:6px 4px;border:none;font-size:13px;color:var(--TX);background:transparent;min-width:0}
.url-txt:focus{outline:none}
.url-clear{padding:0 8px;color:#94a3b8;cursor:pointer;font-size:13px;flex-shrink:0;background:none;border:none;line-height:1}
.url-clear:hover{color:#ef4444}

/* Dropdown */
.url-dd{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid var(--BD);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);z-index:99999;max-height:270px;overflow-y:auto;display:none}
.url-dd.open{display:block}
.dd-group{padding:5px 12px 3px;font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;background:#f8fafc;border-bottom:1px solid #f1f5f9;position:sticky;top:0}
.dd-opt{display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;transition:.1s}
.dd-opt:hover,.dd-opt.hi{background:#eff6ff}
.dd-opt .do-icon{font-size:14px;flex-shrink:0;width:18px;text-align:center}
.dd-opt .do-title{font-size:13px;font-weight:600;color:var(--TX);flex:1}
.dd-opt .do-url{font-size:11px;color:#94a3b8;font-family:monospace;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px}
.dd-empty{padding:12px;text-align:center;font-size:12px;color:#94a3b8}

.lnk-tgt{padding:6px 7px;border:1px solid var(--BD);border-radius:7px;font-size:12px;background:#fff;width:115px;flex-shrink:0}

/* Preview navbar */
.prev-bar{background:var(--P);border-radius:10px;padding:13px 18px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;min-height:46px}
.prev-item{padding:5px 13px;background:rgba(255,255,255,.13);border-radius:6px;color:#fff;font-size:12px;font-weight:600;white-space:nowrap}

/* Footer colonnes */
.col-block{background:var(--BG);border:1px solid var(--BD);border-radius:10px;padding:12px;margin-bottom:10px}
.col-hd{display:flex;align-items:center;gap:8px;margin-bottom:10px}
.col-hd input{flex:1;padding:6px 9px;border:1px solid var(--BD);border-radius:7px;font-size:13px;font-weight:700;background:#fff}
.fl-row{display:grid;grid-template-columns:130px 1fr 28px;gap:6px;margin-bottom:5px;align-items:center}
.fl-row input.fl-l{padding:5px 8px;border:1px solid var(--BD);border-radius:6px;font-size:12px}
/* URL footer (même composant, version compacte) */
.fl-uw{position:relative}
.fl-ui-wrap{display:flex;align-items:center;background:#fff;border:1px solid var(--BD);border-radius:6px;overflow:hidden}
.fl-ui-wrap:focus-within{border-color:var(--P)}
.fl-icon{padding:0 6px;font-size:11px;flex-shrink:0}
.fl-url-txt{flex:1;padding:5px 3px;border:none;font-size:12px;color:var(--TX);background:transparent;min-width:0}
.fl-url-txt:focus{outline:none}
.fl-dd{position:absolute;top:calc(100% + 3px);left:0;right:0;background:#fff;border:1px solid var(--BD);border-radius:9px;box-shadow:0 6px 20px rgba(0,0,0,.1);z-index:99999;max-height:210px;overflow-y:auto;display:none}
.fl-dd.open{display:block}
.fl-group{padding:4px 10px 2px;font-size:9px;font-weight:800;color:#94a3b8;text-transform:uppercase;background:#f8fafc;border-bottom:1px solid #f1f5f9}
.fl-opt{display:flex;align-items:center;gap:7px;padding:7px 10px;cursor:pointer;font-size:12px;transition:.1s}
.fl-opt:hover{background:#eff6ff;color:var(--P)}
.fl-opt .fo-ic{font-size:11px;flex-shrink:0;width:14px}
.fl-opt .fo-t{font-weight:600;flex:1}
.fl-opt .fo-u{font-size:10px;color:#94a3b8;font-family:monospace}

/* Misc */
.soc-row{display:grid;grid-template-columns:120px 1fr 30px;gap:6px;align-items:center;margin-bottom:6px}
.soc-row select,.soc-row input{padding:7px 9px;border:1px solid var(--BD);border-radius:7px;font-size:12px}
.fr{margin-bottom:12px}.fr label{display:block;font-size:11px;font-weight:700;margin-bottom:4px;color:var(--MT)}
.fr input,.fr textarea{width:100%;padding:8px 11px;border:1px solid var(--BD);border-radius:8px;font-size:13px;color:var(--TX);background:var(--BG);box-sizing:border-box}
.fr input:focus,.fr textarea:focus{outline:none;border-color:var(--P)}
mark{background:#fef9c3;padding:0;border-radius:2px}
</style>

<?php if($msg): ?><div class="alert alert-ok"><?=$msg?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-er"><?=$err?></div><?php endif; ?>

<div class="mn-tabs">
    <a href="?page=menus&tab=menu"   class="mn-tab <?=$tab==='menu'  ?'on':''?>">🔗 Menu Principal</a>
    <a href="?page=menus&tab=footer" class="mn-tab <?=$tab==='footer'?'on':''?>">📋 Liens Footer</a>
    <a href="?page=menus&tab=meta"   class="mn-tab <?=$tab==='meta'  ?'on':''?>">⚙️ Paramètres Footer</a>
</div>

<?php if ($tab === 'menu'): ?>
<!-- ════════════════════════════════ MENU PRINCIPAL ════════════════════════════════ -->
<div class="info-box">
    ℹ️ Injecté dans le <strong>header actif</strong>
    <?= $activeHeader ? '(«'.htmlspecialchars($activeHeader['name']).'» — id='.$activeHeader['id'].')' : '<span style="color:#dc2626">⚠ Aucun header actif</span>' ?>.
    Les modifications s'affichent immédiatement sur le site.
</div>

<form method="POST" id="fMenu">
<input type="hidden" name="action" value="save_menu">
<input type="hidden" name="nav_links_json" id="navjson">

<div class="mn-grid">
  <!-- Gauche : liste liens -->
  <div>
    <div class="mn-card">
      <div class="mn-card-hd">
        <h3>☰ Liens du menu</h3>
        <button type="button" class="btn-add sm" onclick="addNavRow()">+ Ajouter un lien</button>
      </div>
      <div class="mn-card-body">
        <div id="navList">
          <?php foreach ($headerNavLinks as $lnk): ?>
          <div class="lnk-row" draggable="true">
            <span class="drag-h">⠿</span>
            <input class="lnk-lbl" type="text" value="<?=htmlspecialchars($lnk['label']??'')?>" placeholder="Libellé">
            <div class="url-wrap">
              <div class="url-inner">
                <span class="url-pg-icon">🔗</span>
                <input class="url-txt" type="text" value="<?=htmlspecialchars($lnk['url']??'')?>" placeholder="Choisir une page…" autocomplete="off">
                <button type="button" class="url-clear" title="Effacer">✕</button>
              </div>
              <div class="url-dd"></div>
            </div>
            <select class="lnk-tgt">
              <option value="_self"  <?=($lnk['target']??'_self')==='_self' ?'selected':''?>>Même fenêtre</option>
              <option value="_blank" <?=($lnk['target']??'_self')==='_blank'?'selected':''?>>Nouvel onglet</option>
            </select>
            <button type="button" class="btn-del" onclick="this.closest('.lnk-row').remove();buildNJ();pvMenu()">✕</button>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:7px;flex-wrap:wrap;margin-top:12px">
          <button type="button" class="btn-add sm" onclick="addNavRow()">+ Lien vide</button>
          <button type="button" class="btn-add sm" style="background:#0da271" onclick="addNavRow('Accueil','/')">🏠 Accueil</button>
          <button type="button" class="btn-add sm" style="background:#d97706" onclick="addNavRow('Estimation','/estimation')">📊 Estimation</button>
          <button type="button" class="btn-add sm" style="background:#8b5cf6" onclick="addNavRow('Contact','/contact')">📩 Contact</button>
        </div>
      </div>
    </div>
    <div style="margin-top:14px;display:flex;justify-content:flex-end">
      <button type="submit" class="btn-save">💾 Sauvegarder le menu</button>
    </div>
  </div>

  <!-- Droite : aperçu + pages dispo -->
  <div style="display:flex;flex-direction:column;gap:14px">
    <div class="mn-card">
      <div class="mn-card-hd"><h3>👁 Aperçu navbar</h3></div>
      <div class="mn-card-body">
        <div class="prev-bar" id="pvBar"></div>
        <div style="font-size:11px;color:#94a3b8;margin-top:7px">Rendu dans la navbar du site</div>
      </div>
    </div>

    <div class="mn-card" style="overflow:hidden">
      <div class="mn-card-hd">
        <h3>📂 Pages du site</h3>
        <span style="font-size:11px;color:#94a3b8"><?=count($allPages)?> pages</span>
      </div>
      <div class="mn-card-body" style="padding:8px;max-height:340px;overflow-y:auto">
        <?php
        $lastGrp = '';
        foreach ($allPages as $p):
            if ($p['group'] !== $lastGrp):
                $lastGrp = $p['group'];
        ?>
        <div style="font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;padding:8px 8px 3px;margin-top:4px"><?=htmlspecialchars($p['group'])?></div>
        <?php endif; ?>
        <button type="button"
          onclick="addNavRow(<?=json_encode($p['title'])?>,<?=json_encode($p['url'])?>)"
          style="width:100%;text-align:left;padding:6px 8px;border:none;border-radius:7px;background:none;cursor:pointer;font-size:12px;color:var(--TX);display:flex;align-items:center;gap:7px;transition:.1s"
          onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
          <span><?=$p['icon']?></span>
          <span style="font-weight:600;flex:1"><?=htmlspecialchars($p['title'])?></span>
          <span style="font-size:10px;color:#94a3b8;font-family:monospace"><?=htmlspecialchars($p['url'])?></span>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($activeHeader): ?>
    <a href="?page=headers-edit&id=<?=$activeHeader['id']?>" style="display:block;padding:10px;border:1px solid #c7d2fe;border-radius:9px;background:#eff6ff;font-size:12px;font-weight:700;color:#1a4d7a;text-decoration:none;text-align:center">
      ✏️ Éditeur complet du header →
    </a>
    <?php endif; ?>
  </div>
</div>
</form>

<?php elseif ($tab === 'footer'): ?>
<!-- ════════════════════════════════ LIENS FOOTER ════════════════════════════════ -->
<div class="info-box">
    📋 Footer actif : <?= $activeFooter ? '«'.htmlspecialchars($activeFooter['name']).'» — id='.$activeFooter['id'] : '<span style="color:#dc2626">⚠ Aucun footer actif</span>' ?>.
</div>

<form method="POST" id="fFooter">
<input type="hidden" name="action" value="save_footer_links">
<input type="hidden" name="footer_cols_json" id="colsjson">

<div class="mn-grid">
  <div>
    <div class="mn-card">
      <div class="mn-card-hd">
        <h3>📋 Colonnes de liens</h3>
        <button type="button" class="btn-add sm" onclick="addCol()">+ Colonne</button>
      </div>
      <div class="mn-card-body">
        <div id="colsList">
          <?php foreach($footerCols as $col): ?>
          <div class="col-block">
            <div class="col-hd">
              <span style="font-size:14px;color:#cbd5e1;cursor:grab">⠿</span>
              <input type="text" class="col-title" value="<?=htmlspecialchars($col['title']??'')?>" placeholder="Titre colonne">
              <button type="button" class="btn-del" onclick="this.closest('.col-block').remove();buildCJ()">✕</button>
            </div>
            <div class="flinks">
              <?php foreach($col['links']??[] as $fl): ?>
              <div class="fl-row">
                <input type="text" class="fl-l" value="<?=htmlspecialchars($fl['label']??'')?>" placeholder="Libellé">
                <div class="fl-uw">
                  <div class="fl-ui-wrap">
                    <span class="fl-icon">🔗</span>
                    <input type="text" class="fl-url-txt" value="<?=htmlspecialchars($fl['url']??'')?>" placeholder="/url…" autocomplete="off">
                  </div>
                  <div class="fl-dd"></div>
                </div>
                <button type="button" class="btn-del" style="width:28px;height:26px" onclick="this.closest('.fl-row').remove();buildCJ()">✕</button>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add sm" onclick="addFLink(this)" style="margin-top:6px">+ Lien</button>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn-add" onclick="addCol()" style="margin-top:10px">+ Colonne</button>
      </div>
    </div>
    <div style="margin-top:14px;display:flex;justify-content:flex-end;gap:10px">
      <?php if ($activeFooter): ?>
      <a href="?page=footers-edit&id=<?=$activeFooter['id']?>" style="padding:10px 18px;border:1px solid var(--BD);border-radius:9px;font-size:13px;font-weight:700;color:var(--TX);text-decoration:none;background:#f9f6f3">✏️ Éditeur complet →</a>
      <?php endif; ?>
      <button type="submit" class="btn-save">💾 Sauvegarder les liens</button>
    </div>
  </div>
  <div class="mn-card">
    <div class="mn-card-hd"><h3>👁 Aperçu colonnes</h3></div>
    <div class="mn-card-body" style="background:#1e293b;border-radius:8px;padding:16px">
      <div id="pvFoot" style="display:flex;gap:20px;flex-wrap:wrap"></div>
    </div>
  </div>
</div>
</form>

<?php elseif ($tab === 'meta'): ?>
<!-- ════════════════════════════════ PARAMÈTRES FOOTER ════════════════════════════════ -->
<form method="POST">
<input type="hidden" name="action" value="save_footer_meta">
<input type="hidden" name="social_json" id="socjson" value="<?=htmlspecialchars(json_encode($socialLinks,JSON_UNESCAPED_UNICODE))?>">
<div style="max-width:800px">
  <div class="mn-card" style="margin-bottom:16px">
    <div class="mn-card-hd"><h3>📞 Contact & Identité</h3></div>
    <div class="mn-card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="fr"><label>Téléphone</label><input name="phone" value="<?=htmlspecialchars($footerMeta['phone']??'')?>"></div>
        <div class="fr"><label>Email</label><input name="email" value="<?=htmlspecialchars($footerMeta['email']??'')?>"></div>
      </div>
      <div class="fr"><label>Adresse</label><textarea name="address" rows="2"><?=htmlspecialchars($footerMeta['address']??'')?></textarea></div>
    </div>
  </div>
  <div class="mn-card" style="margin-bottom:16px">
    <div class="mn-card-hd"><h3>©️ Copyright & Badge</h3></div>
    <div class="mn-card-body">
      <div class="fr"><label>Texte copyright</label><input name="copyright_text" value="<?=htmlspecialchars($footerMeta['copyright_text']??'')?>"></div>
      <div class="fr"><label>Badge CPI</label><input name="badge_text" value="<?=htmlspecialchars($footerMeta['badge_text']??'')?>"></div>
    </div>
  </div>
  <div class="mn-card" style="margin-bottom:16px">
    <div class="mn-card-hd"><h3>🌐 Réseaux Sociaux</h3><button type="button" class="btn-add sm" onclick="addSoc()">+ Réseau</button></div>
    <div class="mn-card-body">
      <div id="socList">
        <?php foreach($socialLinks as $sl): ?>
        <div class="soc-row">
          <select class="sn"><?php foreach(['facebook'=>'Facebook','instagram'=>'Instagram','linkedin'=>'LinkedIn','youtube'=>'YouTube','tiktok'=>'TikTok','twitter'=>'Twitter/X'] as $n=>$l): ?>
            <option value="<?=$n?>" <?=($sl['network']??'')===$n?'selected':''?>><?=$l?></option>
          <?php endforeach; ?></select>
          <input class="su" type="text" value="<?=htmlspecialchars($sl['url']??'')?>" placeholder="https://...">
          <button type="button" class="btn-del" onclick="this.closest('.soc-row').remove();buildSJ()">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div style="display:flex;justify-content:flex-end;gap:10px">
    <?php if ($activeFooter): ?>
    <a href="?page=footers-edit&id=<?=$activeFooter['id']?>" style="padding:10px 18px;border:1px solid var(--BD);border-radius:9px;font-size:13px;font-weight:700;color:var(--TX);text-decoration:none;background:#f9f6f3">✏️ Éditeur complet →</a>
    <?php endif; ?>
    <button type="submit" class="btn-save">💾 Sauvegarder</button>
  </div>
</div>
</form>
<?php endif; ?>

<script>
// ════════════════════════════════════════════════════════════════════════
//  DONNÉES PAGES
// ════════════════════════════════════════════════════════════════════════
const PAGES = <?= $pagesJson ?>;

// Grouper
const GROUPS = {};
PAGES.forEach(p => {
    (GROUPS[p.group] = GROUPS[p.group]||[]).push(p);
});

// Trouver l'icône d'une URL
function iconForUrl(url) {
    const p = PAGES.find(p => p.url === url);
    return p ? (p.icon||'🔗') : (url.startsWith('http') ? '🌐' : '🔗');
}

// ════════════════════════════════════════════════════════════════════════
//  DROPDOWN AUTOCOMPLETE — composant réutilisable
// ════════════════════════════════════════════════════════════════════════
function makeDropdown(dd, opts = {}) {
    const { onSelect, compact = false } = opts;
    let timer;

    function render(q) {
        q = (q||'').toLowerCase().trim();
        let html = '', total = 0;
        Object.entries(GROUPS).forEach(([grp, pages]) => {
            const list = pages.filter(p => !q || p.title.toLowerCase().includes(q) || p.url.toLowerCase().includes(q));
            if (!list.length) return;
            html += compact
                ? `<div class="fl-group">${esc(grp)}</div>`
                : `<div class="dd-group">${esc(grp)}</div>`;
            list.forEach(p => {
                const titleHl = q ? hlMatch(esc(p.title), q) : esc(p.title);
                html += compact
                    ? `<div class="fl-opt" data-url="${esc(p.url)}" data-title="${esc(p.title)}"><span class="fo-ic">${p.icon||'🔗'}</span><span class="fo-t">${titleHl}</span><span class="fo-u">${esc(p.url)}</span></div>`
                    : `<div class="dd-opt" data-url="${esc(p.url)}" data-title="${esc(p.title)}"><span class="do-icon">${p.icon||'🔗'}</span><span class="do-title">${titleHl}</span><span class="do-url">${esc(p.url)}</span></div>`;
                total++;
            });
        });
        if (!total) html = compact
            ? `<div style="padding:8px 10px;font-size:11px;color:#94a3b8">Saisie libre acceptée</div>`
            : `<div class="dd-empty">Aucune page trouvée — saisie libre acceptée</div>`;
        dd.innerHTML = html;
        dd.querySelectorAll(compact?'.fl-opt':'.dd-opt').forEach(opt => {
            opt.addEventListener('mousedown', e => { e.preventDefault(); onSelect(opt.dataset.url, opt.dataset.title); close(); });
        });
    }

    function open(q) { render(q); dd.classList.add('open'); }
    function close() { dd.classList.remove('open'); }
    function isOpen() { return dd.classList.contains('open'); }

    function navigate(dir) {
        const cls = compact?'.fl-opt':'.dd-opt', hiCls='hi';
        const opts = [...dd.querySelectorAll(cls)];
        if (!opts.length) return;
        const cur = dd.querySelector(`.${hiCls}`);
        let idx = cur ? opts.indexOf(cur) : -1;
        if (cur) cur.classList.remove(hiCls);
        idx = (idx + dir + opts.length) % opts.length;
        opts[idx].classList.add(hiCls);
        opts[idx].scrollIntoView({ block:'nearest' });
        return opts[idx];
    }

    function selectHighlighted() {
        const cls = compact?'.fl-opt.hi':'.dd-opt.hi';
        const el = dd.querySelector(cls);
        if (el) { onSelect(el.dataset.url, el.dataset.title); close(); return true; }
        return false;
    }

    return { open, close, isOpen, navigate, selectHighlighted, render };
}

// ════════════════════════════════════════════════════════════════════════
//  INIT URL INPUT (menu principal)
// ════════════════════════════════════════════════════════════════════════
function initUrlInput(row) {
    const wrap  = row.querySelector('.url-wrap');
    const inner = row.querySelector('.url-inner');
    const txt   = row.querySelector('.url-txt');
    const icon  = row.querySelector('.url-pg-icon');
    const clear = row.querySelector('.url-clear');
    const dd    = row.querySelector('.url-dd');
    if (!txt || !dd) return;

    function setVal(url, title) {
        txt.value = url;
        if (icon) icon.textContent = iconForUrl(url);
        // Auto-fill libellé si vide
        const lbl = row.querySelector('.lnk-lbl');
        if (lbl && !lbl.value.trim() && title) lbl.value = title;
        buildNJ(); pvMenu();
    }

    const ctrl = makeDropdown(dd, { onSelect: setVal });

    txt.addEventListener('focus', () => ctrl.open(txt.value));
    txt.addEventListener('input', () => { ctrl.open(txt.value); setVal(txt.value, ''); });
    txt.addEventListener('blur',  () => setTimeout(()=>ctrl.close(), 160));
    txt.addEventListener('keydown', e => {
        if (!ctrl.isOpen()) return;
        if (e.key==='ArrowDown'){ e.preventDefault(); ctrl.navigate(1); }
        if (e.key==='ArrowUp')  { e.preventDefault(); ctrl.navigate(-1); }
        if (e.key==='Enter')    { e.preventDefault(); if(!ctrl.selectHighlighted()) ctrl.close(); }
        if (e.key==='Escape')   { ctrl.close(); }
    });
    if (clear) clear.addEventListener('click', () => { txt.value=''; icon.textContent='🔗'; ctrl.open(''); txt.focus(); buildNJ(); pvMenu(); });
    if (icon && txt.value) icon.textContent = iconForUrl(txt.value);
}

// ════════════════════════════════════════════════════════════════════════
//  INIT URL FOOTER (compact)
// ════════════════════════════════════════════════════════════════════════
function initFlUrl(row) {
    const wrap = row.querySelector('.fl-uw');
    const txt  = row.querySelector('.fl-url-txt');
    const icon = row.querySelector('.fl-icon');
    const dd   = row.querySelector('.fl-dd');
    if (!txt || !dd) return;

    function setVal(url, title) {
        txt.value = url;
        if (icon) icon.textContent = iconForUrl(url);
        const lbl = row.querySelector('.fl-l');
        if (lbl && !lbl.value.trim() && title) lbl.value = title;
        buildCJ();
    }

    const ctrl = makeDropdown(dd, { onSelect: setVal, compact: true });

    txt.addEventListener('focus', () => ctrl.open(txt.value));
    txt.addEventListener('input', () => { ctrl.open(txt.value); buildCJ(); });
    txt.addEventListener('blur',  () => setTimeout(()=>ctrl.close(), 160));
    txt.addEventListener('keydown', e => {
        if (!ctrl.isOpen()) return;
        if (e.key==='ArrowDown'){ e.preventDefault(); ctrl.navigate(1); }
        if (e.key==='ArrowUp')  { e.preventDefault(); ctrl.navigate(-1); }
        if (e.key==='Enter')    { e.preventDefault(); if(!ctrl.selectHighlighted()) ctrl.close(); }
        if (e.key==='Escape')   { ctrl.close(); }
    });
    if (icon && txt.value) icon.textContent = iconForUrl(txt.value);
}

// ════════════════════════════════════════════════════════════════════════
//  BUILD JSON — MENU
// ════════════════════════════════════════════════════════════════════════
function buildNJ() {
    const rows = [...document.querySelectorAll('#navList .lnk-row')];
    const data = rows.map(r=>({
        label:  r.querySelector('.lnk-lbl')?.value.trim()||'',
        url:    r.querySelector('.url-txt')?.value.trim()||'',
        target: r.querySelector('.lnk-tgt')?.value||'_self',
    })).filter(l=>l.label||l.url);
    const el = document.getElementById('navjson');
    if (el) el.value = JSON.stringify(data);
}

function addNavRow(label='', url='') {
    const list = document.getElementById('navList');
    const d = document.createElement('div');
    d.className = 'lnk-row'; d.draggable = true;
    d.innerHTML = `<span class="drag-h">⠿</span>
<input class="lnk-lbl" type="text" value="${esc(label)}" placeholder="Libellé">
<div class="url-wrap">
  <div class="url-inner">
    <span class="url-pg-icon">${iconForUrl(url)}</span>
    <input class="url-txt" type="text" value="${esc(url)}" placeholder="Choisir une page…" autocomplete="off">
    <button type="button" class="url-clear" title="Effacer">✕</button>
  </div>
  <div class="url-dd"></div>
</div>
<select class="lnk-tgt"><option value="_self">Même fenêtre</option><option value="_blank">Nouvel onglet</option></select>
<button type="button" class="btn-del" onclick="this.closest('.lnk-row').remove();buildNJ();pvMenu()">✕</button>`;
    list.appendChild(d);
    initUrlInput(d);
    d.querySelector('.lnk-lbl').focus();
    buildNJ(); pvMenu();
}

// Aperçu
function pvMenu() {
    const pv = document.getElementById('pvBar'); if (!pv) return;
    const items = [...document.querySelectorAll('#navList .lnk-row')]
        .map(r => r.querySelector('.lnk-lbl')?.value.trim())
        .filter(Boolean);
    pv.innerHTML = items.map(l=>`<span class="prev-item">${esc(l)}</span>`).join('')
        || '<span style="color:rgba(255,255,255,.4);font-size:12px">Menu vide</span>';
}

// Init rows existantes
document.querySelectorAll('#navList .lnk-row').forEach(initUrlInput);
document.getElementById('navList')?.addEventListener('input', ()=>{ buildNJ(); pvMenu(); });
buildNJ(); pvMenu();

// ════════════════════════════════════════════════════════════════════════
//  BUILD JSON — FOOTER COLONNES
// ════════════════════════════════════════════════════════════════════════
function buildCJ() {
    const data = [...document.querySelectorAll('.col-block')].map(b=>({
        title: b.querySelector('.col-title')?.value.trim()||'',
        links: [...b.querySelectorAll('.fl-row')].map(r=>({
            label: r.querySelector('.fl-l')?.value.trim()||'',
            url:   r.querySelector('.fl-url-txt')?.value.trim()||'',
        })).filter(l=>l.label)
    }));
    const el = document.getElementById('colsjson');
    if (el) el.value = JSON.stringify(data);
    pvFoot();
}

function addCol() {
    if (document.querySelectorAll('.col-block').length >= 4) { alert('Max 4 colonnes'); return; }
    const d = document.createElement('div'); d.className='col-block';
    d.innerHTML=`<div class="col-hd"><span style="font-size:14px;color:#cbd5e1;cursor:grab">⠿</span><input type="text" class="col-title" placeholder="Titre colonne"><button type="button" class="btn-del" onclick="this.closest('.col-block').remove();buildCJ()">✕</button></div><div class="flinks"></div><button type="button" class="btn-add sm" onclick="addFLink(this)" style="margin-top:6px">+ Lien</button>`;
    document.getElementById('colsList')?.appendChild(d);
    d.querySelector('.col-title')?.focus();
}

function addFLink(btn) {
    const list = btn.closest('.col-block').querySelector('.flinks');
    const d = document.createElement('div'); d.className='fl-row';
    d.innerHTML=`<input type="text" class="fl-l" placeholder="Libellé"><div class="fl-uw"><div class="fl-ui-wrap"><span class="fl-icon">🔗</span><input type="text" class="fl-url-txt" placeholder="/url…" autocomplete="off"></div><div class="fl-dd"></div></div><button type="button" class="btn-del" style="width:28px;height:26px" onclick="this.closest('.fl-row').remove();buildCJ()">✕</button>`;
    list.appendChild(d);
    initFlUrl(d);
    d.querySelector('.fl-l')?.focus();
}

document.querySelectorAll('.fl-row').forEach(initFlUrl);
document.getElementById('colsList')?.addEventListener('input', buildCJ);
buildCJ();

function pvFoot() {
    const pv = document.getElementById('pvFoot'); if(!pv) return;
    const blocks = [...document.querySelectorAll('.col-block')];
    pv.innerHTML = blocks.map(b=>{
        const t = b.querySelector('.col-title')?.value||'';
        const links = [...b.querySelectorAll('.fl-row')].map(r=>{ const l=r.querySelector('.fl-l')?.value.trim(); return l?`<div style="color:#94a3b8;font-size:11px;margin-bottom:3px">${esc(l)}</div>`:'' }).join('');
        return `<div style="min-width:100px"><div style="color:#fff;font-size:10px;font-weight:800;margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">${esc(t)}</div>${links}</div>`;
    }).join('') || '<span style="color:#64748b;font-size:12px">Aucune colonne</span>';
}

// ════════════════════════════════════════════════════════════════════════
//  RÉSEAUX SOCIAUX
// ════════════════════════════════════════════════════════════════════════
const SNETS={facebook:'Facebook',instagram:'Instagram',linkedin:'LinkedIn',youtube:'YouTube',tiktok:'TikTok',twitter:'Twitter/X'};
function addSoc(){
    const d=document.createElement('div');d.className='soc-row';
    d.innerHTML=`<select class="sn">${Object.entries(SNETS).map(([v,l])=>`<option value="${v}">${l}</option>`).join('')}</select><input class="su" type="text" placeholder="https://..."><button type="button" class="btn-del" onclick="this.closest('.soc-row').remove();buildSJ()">✕</button>`;
    document.getElementById('socList')?.querySelector('p')?.remove();
    document.getElementById('socList')?.appendChild(d);
    d.querySelector('.su')?.addEventListener('input',buildSJ);
}
function buildSJ(){
    const data=[...document.querySelectorAll('.soc-row')].map(d=>({network:d.querySelector('.sn')?.value||'',url:d.querySelector('.su')?.value||''}));
    const el=document.getElementById('socjson');if(el)el.value=JSON.stringify(data);
}
document.getElementById('socList')?.addEventListener('change',buildSJ);

// ════════════════════════════════════════════════════════════════════════
//  DRAG & DROP (menu)
// ════════════════════════════════════════════════════════════════════════
let _drag = null;
const navList = document.getElementById('navList');
if (navList) {
    navList.addEventListener('dragstart', e => { _drag=e.target.closest('.lnk-row'); if(_drag)_drag.classList.add('dragging'); });
    navList.addEventListener('dragend',   e => { if(_drag)_drag.classList.remove('dragging'); _drag=null; buildNJ(); });
    navList.addEventListener('dragover',  e => {
        e.preventDefault();
        const t = e.target.closest('.lnk-row');
        if (t && _drag && t!==_drag) {
            const after=[...navList.children].indexOf(t)>[...navList.children].indexOf(_drag);
            navList.insertBefore(_drag, after ? t.nextSibling : t);
        }
    });
}

// ════════════════════════════════════════════════════════════════════════
//  UTILS
// ════════════════════════════════════════════════════════════════════════
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function hlMatch(str,q){ if(!q)return str; try{ return str.replace(new RegExp('('+q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi'),'<mark>$1</mark>'); }catch(e){return str;} }
</script>