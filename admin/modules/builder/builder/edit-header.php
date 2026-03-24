<?php
// =============================================================
//  IMMO LOCAL+ — Éditeur Header v2.4
//  admin/modules/builder/builder/edit-header.php
//  Route : dashboard.php?page=headers-edit&id=X
//  v2.4 : Ajout nav_align (left/center/right)
//         Layout grid adaptatif selon alignement
// =============================================================
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
        if (!defined('DB_HOST')) require_once __DIR__ . '/../../../../config/config.php';
        $db = getDB();
    } catch (Exception $e) {
        echo '<div style="padding:20px;color:#dc2626;background:#fee2e2;border-radius:8px;margin:20px">DB : '.htmlspecialchars($e->getMessage()).'</div>';
        return;
    }
}

$msg = ''; $err = '';
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$header = null;

// ── Vérifier si colonne nav_align existe, sinon la créer ──
$hasNavAlign = false;
try {
    $cols = $db->query("SHOW COLUMNS FROM headers")->fetchAll(PDO::FETCH_COLUMN);
    $hasNavAlign = in_array('nav_align', $cols);
    if (!$hasNavAlign) {
        $db->exec("ALTER TABLE headers ADD COLUMN nav_align VARCHAR(10) NOT NULL DEFAULT 'center' AFTER menu_items");
        $hasNavAlign = true;
    }
} catch (Exception $e) {
    // Colonne non critique, on continue sans elle
}

if ($id > 0) {
    try {
        $st = $db->prepare("SELECT * FROM headers WHERE id=?");
        $st->execute([$id]);
        $header = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $err = 'Erreur DB : '.$e->getMessage(); }
}

if (!$header) {
    $header = [
        'id'=>0,'name'=>'','slug'=>'','type'=>'standard','status'=>'draft','is_default'=>0,
        'logo_type'=>'text','logo_text'=>'Eduardo Desul','logo_url'=>'','logo_alt'=>'','logo_width'=>150,'logo_link'=>'/',
        'menu_items'=>'[{"label":"Accueil","url":"/"},{"label":"Acheter","url":"/acheter"},{"label":"Vendre","url":"/vendre"},{"label":"Estimer","url":"/estimation"},{"label":"Blog","url":"/blog"}]',
        'nav_links'=>'',
        'nav_align'=>'center',
        'cta_enabled'=>1,'cta_text'=>'Contact','cta_link'=>'/contact','cta_style'=>'primary',
        'cta2_enabled'=>0,'cta2_text'=>'','cta2_link'=>'','cta2_style'=>'secondary',
        'phone_enabled'=>0,'phone_number'=>'06 24 10 58 16',
        'social_enabled'=>0,'social_links'=>'[]',
        'bg_color'=>'#ffffff','text_color'=>'#1e293b','hover_color'=>'#d4a574',
        'height'=>80,'sticky'=>1,'shadow'=>1,'border_bottom'=>0,
        'mobile_breakpoint'=>1024,'mobile_menu_style'=>'slide-right',
        'custom_html'=>'','custom_css'=>'','custom_js'=>'',
    ];
}

// Valeur par défaut si absent
if (empty($header['nav_align'])) $header['nav_align'] = 'center';

// ── Résoudre menu ──
function resolveMenuItems(array $h): array {
    foreach (['nav_links','menu_items'] as $col) {
        $raw = $h[$col] ?? '';
        if (!$raw) continue;
        $d = json_decode($raw, true);
        if (is_array($d) && !empty($d) && !isset($d['name'])) return $d;
    }
    return [];
}
$menuItems = resolveMenuItems($header);

// ── Nettoyer custom_html si c'est du JSON menu ──
if (!empty($header['custom_html'])) {
    $ch = trim($header['custom_html']);
    if (strlen($ch) > 1 && $ch[0] === '[' && $ch[1] === '{') {
        $header['custom_html'] = '';
        if ($id > 0) {
            try { $db->prepare("UPDATE headers SET custom_html='' WHERE id=?")->execute([$id]); } catch(Exception $e2){}
        }
    }
}

// ── Charger pages pour autocomplete ──
$allPages = [];
$groups = [
    'pages'    => ['Pages CMS',       '📄', '/'],
    'articles' => ['Articles Blog',   '📰', '/blog/'],
    'secteurs' => ['Quartiers',       '🗺️', '/'],
    'captures' => ['Pages capture',   '⚡', '/capture/'],
];
foreach ($groups as $table => [$gname, $icon, $prefix]) {
    try {
        $rows = $db->query("SELECT title, slug FROM `$table` WHERE status IN ('published','active') ORDER BY title LIMIT 200")->fetchAll();
        foreach ($rows as $r) $allPages[] = ['group'=>$gname,'icon'=>$icon,'title'=>$r['title'],'url'=>$prefix.ltrim($r['slug'],'/')];
    } catch(Exception $e) {
        try {
            $rows = $db->query("SELECT title, slug FROM `$table` ORDER BY title LIMIT 200")->fetchAll();
            foreach ($rows as $r) $allPages[] = ['group'=>$gname,'icon'=>$icon,'title'=>$r['title'],'url'=>$prefix.ltrim($r['slug'],'/')];
        } catch(Exception $e2) {}
    }
}
$fixedPages = [
    ['group'=>'Liens fixes','icon'=>'🏠','title'=>'Accueil','url'=>'/'],
    ['group'=>'Liens fixes','icon'=>'📩','title'=>'Contact','url'=>'/contact'],
    ['group'=>'Liens fixes','icon'=>'📝','title'=>'Blog','url'=>'/blog'],
    ['group'=>'Liens fixes','icon'=>'📊','title'=>'Estimation gratuite','url'=>'/estimation'],
    ['group'=>'Liens fixes','icon'=>'🏡','title'=>'Acheter','url'=>'/acheter'],
    ['group'=>'Liens fixes','icon'=>'💰','title'=>'Vendre','url'=>'/vendre'],
    ['group'=>'Liens fixes','icon'=>'💼','title'=>'Investir','url'=>'/investir'],
    ['group'=>'Liens fixes','icon'=>'⚖️','title'=>'Mentions légales','url'=>'/mentions-legales'],
];
$allPages = array_merge($fixedPages, $allPages);

// ── Sauvegarde ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'save_header') {
    try {
        $name = trim($_POST['name']??'header');
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$name),'-'));
        $menuJson   = $_POST['menu_items_json']??'[]';
        $socialJson = $_POST['social_links_json']??'[]';
        if (json_decode($menuJson)===null)   $menuJson='[]';
        if (json_decode($socialJson)===null) $socialJson='[]';

        $navAlign = in_array($_POST['nav_align']??'center', ['left','center','right']) ? $_POST['nav_align'] : 'center';

        $data = [
            'name'=>$name,'slug'=>$slug,
            'type'=>in_array($_POST['type']??'',['standard','sticky','transparent','minimal','mega-menu'])?$_POST['type']:'standard',
            'status'=>in_array($_POST['status']??'',['draft','active','inactive'])?$_POST['status']:'draft',
            'is_default'=>isset($_POST['is_default'])?1:0,
            'logo_type'=>in_array($_POST['logo_type']??'',['text','image'])?$_POST['logo_type']:'text',
            'logo_text'=>trim($_POST['logo_text']??''),
            'logo_url'=>trim($_POST['logo_url']??''),
            'logo_alt'=>trim($_POST['logo_alt']??''),
            'logo_width'=>max(50,min(400,(int)($_POST['logo_width']??150))),
            'logo_link'=>trim($_POST['logo_link']??'/') ?: '/',
            'menu_items'=>$menuJson,
            'nav_links' =>$menuJson,
            'nav_align' =>$navAlign,
            'cta_enabled'=>isset($_POST['cta_enabled'])?1:0,
            'cta_text'=>trim($_POST['cta_text']??'Contact'),
            'cta_link'=>trim($_POST['cta_link']??'/contact'),
            'cta_style'=>in_array($_POST['cta_style']??'',['primary','secondary','outline','gradient'])?$_POST['cta_style']:'primary',
            'cta2_enabled'=>isset($_POST['cta2_enabled'])?1:0,
            'cta2_text'=>trim($_POST['cta2_text']??''),
            'cta2_link'=>trim($_POST['cta2_link']??''),
            'cta2_style'=>in_array($_POST['cta2_style']??'',['primary','secondary','outline','gradient'])?$_POST['cta2_style']:'secondary',
            'phone_enabled'=>isset($_POST['phone_enabled'])?1:0,
            'phone_number'=>trim($_POST['phone_number']??''),
            'social_enabled'=>isset($_POST['social_enabled'])?1:0,
            'social_links'=>$socialJson,
            'bg_color'=>preg_match('/^#[0-9a-f]{3,6}$/i',$_POST['bg_color']??'')?$_POST['bg_color']:'#ffffff',
            'text_color'=>preg_match('/^#[0-9a-f]{3,6}$/i',$_POST['text_color']??'')?$_POST['text_color']:'#1e293b',
            'hover_color'=>preg_match('/^#[0-9a-f]{3,6}$/i',$_POST['hover_color']??'')?$_POST['hover_color']:'#d4a574',
            'height'=>max(40,min(200,(int)($_POST['height']??80))),
            'sticky'=>isset($_POST['sticky'])?1:0,
            'shadow'=>isset($_POST['shadow'])?1:0,
            'border_bottom'=>isset($_POST['border_bottom'])?1:0,
            'mobile_breakpoint'=>max(480,min(1280,(int)($_POST['mobile_breakpoint']??1024))),
            'mobile_menu_style'=>in_array($_POST['mobile_menu_style']??'',['slide-left','slide-right','fullscreen','dropdown'])?$_POST['mobile_menu_style']:'slide-right',
            'custom_html'=>$_POST['custom_html']??'',
            'custom_css'=>$_POST['custom_css']??'',
            'custom_js'=>$_POST['custom_js']??'',
        ];

        if ($data['is_default']) $db->exec("UPDATE headers SET is_default=0");

        $existingCols = [];
        try {
            $cols2 = $db->query("SHOW COLUMNS FROM headers")->fetchAll(PDO::FETCH_COLUMN);
            $existingCols = array_flip($cols2);
        } catch(Exception $e2) { $existingCols = array_flip(array_keys($data)); }
        $data = array_intersect_key($data, $existingCols);

        if ($id > 0) {
            $sets = implode(',', array_map(fn($k)=>"`$k`=:$k", array_keys($data)));
            $st   = $db->prepare("UPDATE headers SET $sets, updated_at=NOW() WHERE id=:id");
            $data['id'] = $id; $st->execute($data);
            $msg = '✅ Header mis à jour.';
        } else {
            $cols3 = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
            $vals = implode(',', array_map(fn($k)=>":$k", array_keys($data)));
            $st   = $db->prepare("INSERT INTO headers ($cols3) VALUES ($vals)");
            $st->execute($data);
            $id  = (int)$db->lastInsertId();
            $msg = '✅ Header créé.';
        }
        $st2 = $db->prepare("SELECT * FROM headers WHERE id=?");
        $st2->execute([$id]);
        $header = $st2->fetch(PDO::FETCH_ASSOC) ?: $header;
        if (empty($header['nav_align'])) $header['nav_align'] = 'center';
        $menuItems = resolveMenuItems($header);
    } catch (Exception $e) { $err = '❌ '.$e->getMessage(); }
}

$socialLinks = json_decode($header['social_links']??'[]',true)?:[];
$pagesJson   = json_encode($allPages, JSON_UNESCAPED_UNICODE);

// ══════════════════════════════════════════════════════════════
//  buildGridLayout() — génère le CSS grid + HTML selon nav_align
//  3 modes :
//    left   → nav à gauche, logo à gauche, CTA à droite
//             grid : auto 1fr auto
//    center → logo gauche, nav centre, CTA droite
//             grid : 1fr auto 1fr
//    right  → logo gauche, CTA+nav à droite groupés
//             grid : 1fr auto
// ══════════════════════════════════════════════════════════════
function buildHeaderLayout(string $align, string $logoHtml, string $logoLink, string $nav, string $rightCol): string {
    switch ($align) {
        case 'left':
            // Logo | Nav (gauche) | CTA (droite)
            return "
  <div style='max-width:1260px;margin:0 auto;padding:0 32px;height:100%;
              display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px'>
    <a href='$logoLink' style='text-decoration:none;display:flex;align-items:center'>$logoHtml</a>
    <nav style='display:flex;align-items:center;gap:2px;justify-self:start'>$nav</nav>
    <div style='display:flex;align-items:center;gap:12px;justify-self:end'>$rightCol</div>
  </div>";

        case 'right':
            // Logo (gauche) | CTA+Nav (droite groupés)
            return "
  <div style='max-width:1260px;margin:0 auto;padding:0 32px;height:100%;
              display:grid;grid-template-columns:1fr auto;align-items:center;gap:24px'>
    <a href='$logoLink' style='justify-self:start;text-decoration:none;display:flex;align-items:center'>$logoHtml</a>
    <div style='display:flex;align-items:center;gap:16px;justify-self:end'>
      <nav style='display:flex;align-items:center;gap:2px'>$nav</nav>
      ".($rightCol ? "<div style='display:flex;align-items:center;gap:10px'>$rightCol</div>" : "")."
    </div>
  </div>";

        case 'center':
        default:
            // Logo (gauche) | Nav (centre) | CTA (droite)
            return "
  <div style='max-width:1260px;margin:0 auto;padding:0 32px;height:100%;
              display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:16px'>
    <a href='$logoLink' style='justify-self:start;text-decoration:none;display:flex;align-items:center'>$logoHtml</a>
    <nav style='display:flex;align-items:center;gap:2px;justify-self:center'>$nav</nav>
    <div style='display:flex;align-items:center;gap:12px;justify-self:end'>$rightCol</div>
  </div>";
    }
}

function bldPreview(array $h, array $items): string {
    $bg  = $h['bg_color']    ?? '#ffffff';
    $tc  = $h['text_color']  ?? '#1e293b';
    $hv  = $h['hover_color'] ?? '#d4a574';
    $ht  = (int)($h['height'] ?? 80);
    $shad = ($h['shadow'] ?? 1) ? '0 2px 12px rgba(0,0,0,.10)' : 'none';
    $brd  = ($h['border_bottom'] ?? 0) ? "border-bottom:1px solid #e2d9ce;" : '';
    $align = $h['nav_align'] ?? 'center';

    // Logo
    $logoLink = htmlspecialchars($h['logo_link'] ?? '/');
    if (($h['logo_type'] ?? 'text') === 'image' && !empty($h['logo_url'])) {
        $logoHtml = '<img src="'.htmlspecialchars($h['logo_url']).'" style="height:'.min($ht-20,52).'px;width:auto" alt="'.htmlspecialchars($h['logo_alt']??'').'">';
    } else {
        $logoHtml = '<span style="font-weight:800;font-size:20px;color:#1a4d7a;font-family:\'Playfair Display\',Georgia,serif">'.htmlspecialchars($h['logo_text']??($h['name']??'Logo')).'</span>';
    }

    // Nav
    $nav = '';
    foreach ($items as $it) {
        $nav .= '<a href="'.htmlspecialchars($it['url']??'#').'" style="color:'.$tc.';text-decoration:none;font-size:14px;font-weight:500;padding:6px 13px;border-radius:6px;white-space:nowrap;transition:color .2s">'
              . htmlspecialchars($it['label']??'') . '</a>';
    }

    // Téléphone
    $ph = '';
    if (!empty($h['phone_enabled']) && !empty($h['phone_number'])) {
        $p = htmlspecialchars($h['phone_number']);
        $ph = "<a href='tel:$p' style='color:#1a4d7a;text-decoration:none;font-size:13px;font-weight:600;white-space:nowrap'>$p</a>";
    }

    // CTA
    $ST = [
        'primary'  => "background:#1a4d7a;color:#fff;border:none;",
        'secondary'=> "background:transparent;color:$hv;border:2px solid $hv;",
        'outline'  => "background:transparent;color:$tc;border:2px solid $tc;",
        'gradient' => "background:linear-gradient(135deg,$hv,#b8844f);color:#fff;border:none;",
    ];
    $ctas = '';
    if (!empty($h['cta_enabled']) && !empty($h['cta_text'])) {
        $s = $ST[$h['cta_style']??'primary'] ?? $ST['primary'];
        $ctas .= '<a href="'.htmlspecialchars($h['cta_link']??'#').'" style="'.$s.'padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap">'.htmlspecialchars($h['cta_text']).'</a>';
    }
    if (!empty($h['cta2_enabled']) && !empty($h['cta2_text'])) {
        $s = $ST[$h['cta2_style']??'secondary'] ?? $ST['secondary'];
        $ctas .= '<a href="'.htmlspecialchars($h['cta2_link']??'#').'" style="'.$s.'padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;margin-left:8px;white-space:nowrap">'.htmlspecialchars($h['cta2_text']).'</a>';
    }

    // Custom HTML override
    if (!empty($h['custom_html'])) {
        $ch = trim($h['custom_html']);
        if ($ch && $ch[0] !== '[') {
            return "<!DOCTYPE html><html><head><meta charset='utf-8'><style>*{margin:0;padding:0;box-sizing:border-box}".($h['custom_css']??'')."</style></head><body>$ch</body></html>";
        }
    }

    $rightCol = trim($ph . ($ph && $ctas ? '&nbsp;' : '') . $ctas);
    $layout = buildHeaderLayout($align, $logoHtml, $logoLink, $nav, $rightCol);

    // Badge visuel de l'alignement courant
    $alignLabels = ['left'=>'◀ Nav gauche', 'center'=>'◈ Nav centrée', 'right'=>'▶ Nav droite'];
    $alignBadge = $alignLabels[$align] ?? $align;

    return "<!DOCTYPE html><html><head><meta charset='utf-8'>
<link href='https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap' rel='stylesheet'>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;overflow:hidden;background:$bg}
a:hover{color:$hv !important}
.align-badge{position:fixed;bottom:8px;right:8px;background:rgba(26,77,122,.85);color:#fff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;letter-spacing:.3px}
</style></head>
<body>
<header style='background:$bg;height:{$ht}px;box-shadow:$shad;{$brd}position:relative'>
  $layout
</header>
<div class='align-badge'>$alignBadge</div>
</body></html>";
}

$pvHtml = bldPreview($header, $menuItems);
?>
<style>
:root{--P:#1a4d7a;--A:#d4a574;--BG:#f9f6f3;--W:#fff;--BD:#e2e8f0;--TX:#1e293b;--MT:#64748b;--R:10px}
.hdr-ed{display:grid;grid-template-columns:340px 1fr;height:calc(100vh - 110px);overflow:hidden;border-radius:12px;border:1px solid var(--BD);box-shadow:0 2px 12px rgba(0,0,0,.07)}
.hdr-top{display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--W);border-bottom:1px solid var(--BD);margin-bottom:16px;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.hdr-top h1{flex:1;font-size:15px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0}
.hdr-sb{overflow-y:auto;background:var(--W);border-right:1px solid var(--BD)}
.hdr-pv{display:flex;flex-direction:column;background:#e5e7eb;overflow:hidden}
.stabs{display:flex;border-bottom:1px solid var(--BD);position:sticky;top:0;background:var(--W);z-index:10}
.stab{flex:1;padding:10px 2px;text-align:center;font-size:10px;font-weight:700;color:var(--MT);cursor:pointer;border-bottom:2px solid transparent;transition:.15s;text-transform:uppercase;letter-spacing:.4px;line-height:1.3}
.stab:hover{color:var(--P)}.stab.on{color:var(--P);border-bottom-color:var(--P)}
.sp{display:none;padding:14px 14px 80px}.sp.on{display:block}
.sr{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--MT);margin:16px 0 8px;padding-bottom:4px;border-bottom:1px solid var(--BD)}
.fr{margin-bottom:11px}.fr label{display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:var(--TX)}
.fr input,.fr select,.fr textarea{width:100%;padding:7px 10px;border:1px solid var(--BD);border-radius:8px;font-size:13px;color:var(--TX);background:var(--BG);transition:.15s;box-sizing:border-box}
.fr input:focus,.fr select:focus,.fr textarea:focus{outline:none;border-color:var(--P);background:var(--W);box-shadow:0 0 0 3px rgba(26,77,122,.08)}
.fr textarea{resize:vertical;min-height:90px;font-family:monospace;font-size:12px}
.fr.row{display:flex;align-items:center;gap:8px}.fr.row label{margin:0;flex:1}
.fr.g2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.tog{position:relative;width:36px;height:20px;flex-shrink:0}
.tog input{opacity:0;width:0;height:0;position:absolute}
.tog-sl{position:absolute;inset:0;background:#cbd5e1;border-radius:20px;cursor:pointer;transition:.25s}
.tog-sl::before{content:'';position:absolute;left:2px;top:2px;width:16px;height:16px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.tog input:checked+.tog-sl{background:var(--P)}.tog input:checked+.tog-sl::before{transform:translateX(16px)}
.rcg{display:grid;grid-template-columns:1fr 1fr;gap:6px}.rcg.g3{grid-template-columns:1fr 1fr 1fr}
.rc{position:relative}.rc input{position:absolute;opacity:0}
.rc label{display:flex;flex-direction:column;align-items:center;gap:2px;padding:8px 4px;border:2px solid var(--BD);border-radius:8px;cursor:pointer;font-size:11px;font-weight:700;transition:.15s;background:var(--BG);color:var(--MT);text-align:center}
.rc label .ico{font-size:16px}.rc input:checked+label{border-color:var(--P);background:#eff6ff;color:var(--P)}
.btn-save{padding:9px 20px;background:var(--P);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:13px;cursor:pointer;transition:.15s;white-space:nowrap}
.btn-save:hover{background:#1557a0}
.btn-back{padding:8px 14px;background:var(--BG);color:var(--TX);border:1px solid var(--BD);border-radius:9px;font-size:12px;cursor:pointer;text-decoration:none;display:inline-block;white-space:nowrap}
.btn-add{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;background:var(--P);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:700;transition:.15s;margin-top:5px}
.btn-add:hover{background:#1557a0}
.btn-del{width:26px;height:26px;display:flex;align-items:center;justify-content:center;background:#fee2e2;color:#ef4444;border:none;border-radius:6px;cursor:pointer;font-size:12px;flex-shrink:0;transition:.15s}
.btn-del:hover{background:#ef4444;color:#fff}
.alert{padding:9px 13px;border-radius:8px;font-size:12px;font-weight:700}
.alert-ok{background:#d1fae5;color:#065f46}.alert-er{background:#fee2e2;color:#991b1b}
.badge{padding:2px 8px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase}
.badge-active{background:#d1fae5;color:#065f46}.badge-draft{background:#f1f5f9;color:var(--MT)}.badge-inactive{background:#fee2e2;color:#991b1b}
.pv-bar{display:flex;align-items:center;gap:8px;padding:8px 14px;background:var(--W);border-bottom:1px solid var(--BD)}
.pv-bar span{font-size:11px;color:var(--MT);flex:1}
.dev-btn{padding:5px 11px;border:1px solid var(--BD);border-radius:6px;background:var(--BG);cursor:pointer;font-size:11px;font-weight:600;transition:.15s}
.dev-btn.on,.dev-btn:hover{background:var(--P);color:#fff;border-color:var(--P)}
.pv-wrap{flex:1;padding:16px;overflow:auto}
#pvframe{width:100%;border:none;border-radius:var(--R);box-shadow:0 4px 20px rgba(0,0,0,.15);background:#fff}
.cg3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.cpair{display:flex;align-items:center;gap:6px}
.cpair input[type=color]{width:34px;height:34px;border:1px solid var(--BD);border-radius:7px;padding:2px;cursor:pointer;flex-shrink:0}
.cpair input[type=text]{flex:1}
.presets{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
.preset{padding:6px 10px;border:1px solid var(--BD);border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:.15s}
.preset:hover{transform:translateY(-1px);box-shadow:0 2px 8px rgba(0,0,0,.1)}
.si{display:grid;grid-template-columns:100px 1fr 30px;gap:5px;align-items:center;margin-bottom:6px}
.si select,.si input{padding:6px 8px;border:1px solid var(--BD);border-radius:7px;font-size:12px}
.code-ta{font-family:monospace;font-size:12px;line-height:1.6;background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:8px;padding:10px;resize:vertical;min-height:100px;width:100%;box-sizing:border-box}
.hint{font-size:10.5px;color:var(--MT);margin-top:3px;font-style:italic}
.warn-box{background:#fff3cd;border:1px solid #fbbf24;border-radius:8px;padding:10px;margin-bottom:14px;font-size:12px}
.mlist{list-style:none;min-height:30px}
.mi{display:flex;align-items:center;gap:5px;background:var(--BG);border:1px solid var(--BD);border-radius:8px;padding:5px 7px;margin-bottom:5px;cursor:grab}
.mi.dragging{opacity:.4;cursor:grabbing}
.mi .dh{color:var(--MT);user-select:none;font-size:14px;flex-shrink:0}
.mi .mi-l{width:90px;padding:4px 7px;border:1px solid var(--BD);border-radius:6px;font-size:12px;font-weight:600;background:var(--W);flex-shrink:0}
.mi-uw{flex:1;position:relative}
.mi-ui{display:flex;align-items:center;background:var(--W);border:1px solid var(--BD);border-radius:6px;overflow:hidden}
.mi-ui:focus-within{border-color:var(--P)}
.mi-uic{padding:0 6px;font-size:12px;flex-shrink:0;pointer-events:none}
.mi-utxt{flex:1;padding:4px 3px;border:none;font-size:12px;color:var(--TX);background:transparent;min-width:0}
.mi-utxt:focus{outline:none}
.mi-ucl{padding:0 6px;color:#94a3b8;cursor:pointer;background:none;border:none;font-size:11px}
.mi-ucl:hover{color:#ef4444}
.mi-dd{position:absolute;top:calc(100% + 3px);left:0;right:0;background:#fff;border:1px solid var(--BD);border-radius:9px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:99999;max-height:220px;overflow-y:auto;display:none}
.mi-dd.open{display:block}
.mi-dg{padding:4px 10px 2px;font-size:9px;font-weight:800;color:#94a3b8;text-transform:uppercase;background:#f8fafc;border-bottom:1px solid #f1f5f9;position:sticky;top:0}
.mi-do{display:flex;align-items:center;gap:7px;padding:7px 10px;cursor:pointer;font-size:12px;transition:.1s}
.mi-do:hover,.mi-do.hi{background:#eff6ff;color:var(--P)}
.mi-do .dio{font-size:12px;flex-shrink:0;width:14px}
.mi-do .dit{font-weight:600;flex:1}
.mi-do .diu{font-size:10px;color:#94a3b8;font-family:monospace}
.pages-panel{background:var(--BG);border:1px solid var(--BD);border-radius:9px;margin-top:10px;overflow:hidden}
.pages-panel-hd{padding:8px 12px;font-size:11px;font-weight:800;color:var(--MT);background:#f1f5f9;border-bottom:1px solid var(--BD);display:flex;align-items:center;justify-content:space-between;cursor:pointer}
.pages-panel-body{max-height:0;overflow:hidden;transition:.3s}
.pages-panel-body.open{max-height:320px;overflow-y:auto}
.pg-grp{font-size:9px;font-weight:800;color:#94a3b8;text-transform:uppercase;padding:6px 10px 2px;background:#f8fafc;border-bottom:1px solid #f1f5f9}
.pg-btn{width:100%;text-align:left;padding:6px 10px;border:none;border-radius:0;background:none;cursor:pointer;font-size:11px;color:var(--TX);display:flex;align-items:center;gap:7px;transition:.1s}
.pg-btn:hover{background:#eff6ff;color:var(--P)}
.pg-btn .pt{font-weight:600;flex:1}.pg-btn .pu{font-size:10px;color:#94a3b8;font-family:monospace}

/* ── Alignement nav : sélecteur visuel ── */
.nav-align-group{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:4px}
.na-btn{border:2px solid var(--BD);border-radius:10px;padding:10px 6px;cursor:pointer;background:var(--BG);transition:.15s;text-align:center}
.na-btn:hover{border-color:var(--A)}
.na-btn.active{border-color:var(--P);background:#eff6ff}
.na-btn input{display:none}
.na-btn .na-ico{font-size:18px;display:block;margin-bottom:4px}
.na-btn .na-lbl{font-size:10px;font-weight:700;color:var(--MT)}
.na-btn.active .na-lbl{color:var(--P)}
/* Schéma visuel dans chaque bouton */
.na-schema{display:flex;align-items:center;gap:3px;justify-content:center;margin-bottom:6px;height:16px}
.ns-logo{width:14px;height:8px;background:#1a4d7a;border-radius:2px;flex-shrink:0}
.ns-nav{display:flex;gap:2px}
.ns-nav span{width:8px;height:4px;background:#94a3b8;border-radius:1px;display:block}
.ns-cta{width:18px;height:8px;background:#d4a574;border-radius:2px;flex-shrink:0}
.ns-spacer{flex:1}
</style>

<form method="POST" id="hdrForm" autocomplete="off">
<input type="hidden" name="action" value="save_header">
<input type="hidden" name="menu_items_json" id="mijson" value="<?=htmlspecialchars(json_encode($menuItems,JSON_UNESCAPED_UNICODE))?>">
<input type="hidden" name="social_links_json" id="sljson" value="<?=htmlspecialchars(json_encode($socialLinks,JSON_UNESCAPED_UNICODE))?>">

<!-- TOP BAR -->
<div class="hdr-top">
  <a href="dashboard.php?page=headers" class="btn-back">← Headers</a>
  <h1 id="ttl">
    <?=$id ? htmlspecialchars($header['name']) : 'Nouveau Header'?>
    <?php if($header['status']): ?><span class="badge badge-<?=$header['status']?>"><?=$header['status']?></span><?php endif; ?>
  </h1>
  <?php if($msg): ?><div class="alert alert-ok"><?=$msg?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-er"><?=$err?></div><?php endif; ?>
  <button type="submit" class="btn-save">💾 Sauvegarder</button>
</div>

<div class="hdr-ed">

  <!-- SIDEBAR -->
  <aside class="hdr-sb">
    <div class="stabs">
      <div class="stab on" data-p="general">⚙️<br>Général</div>
      <div class="stab"    data-p="logo">🏷️<br>Logo</div>
      <div class="stab"    data-p="menu">☰<br>Menu</div>
      <div class="stab"    data-p="cta">🎯<br>CTA</div>
      <div class="stab"    data-p="design">🎨<br>Style</div>
      <div class="stab"    data-p="code">&lt;/&gt;<br>Code</div>
    </div>

    <!-- GÉNÉRAL -->
    <div class="sp on" id="panel-general">

      <div class="fr"><label>Nom du header *</label>
        <input type="text" name="name" id="inp-name" value="<?=htmlspecialchars($header['name'])?>" placeholder="Ex: Header principal" required>
      </div>

      <div class="fr"><label>Type</label>
        <div class="rcg">
          <?php foreach(['standard'=>['🏠','Standard'],'sticky'=>['📌','Sticky'],'transparent'=>['🔍','Transparent'],'minimal'=>['▪️','Minimal'],'mega-menu'=>['🗂️','Mega']] as $v=>[$ic,$l]): ?>
          <div class="rc"><input type="radio" name="type" id="tp_<?=$v?>" value="<?=$v?>" <?=$header['type']==$v?'checked':''?>>
          <label for="tp_<?=$v?>"><span class="ico"><?=$ic?></span><?=$l?></label></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="fr g2">
        <div><label>Statut</label>
          <select name="status">
            <option value="draft"    <?=$header['status']=='draft'   ?'selected':''?>>📝 Brouillon</option>
            <option value="active"   <?=$header['status']=='active'  ?'selected':''?>>✅ Actif</option>
            <option value="inactive" <?=$header['status']=='inactive'?'selected':''?>>⏸️ Inactif</option>
          </select>
        </div>
        <div><label>Hauteur (px)</label><input type="number" name="height" value="<?=(int)$header['height']?>" min="40" max="200"></div>
      </div>

      <div class="fr row"><label>Header par défaut</label>
        <label class="tog"><input type="checkbox" name="is_default" <?=$header['is_default']?'checked':''?>><span class="tog-sl"></span></label>
      </div>

      <!-- ══ ALIGNEMENT NAV ═══════════════════════════════════ -->
      <div class="sr">📐 Disposition de la navigation</div>
      <div class="nav-align-group" id="nav-align-group">

        <!-- Gauche -->
        <label class="na-btn <?=$header['nav_align']==='left'?'active':''?>" for="na_left" onclick="setNavAlign('left',this)">
          <input type="radio" name="nav_align" id="na_left" value="left" <?=$header['nav_align']==='left'?'checked':''?>>
          <div class="na-schema">
            <div class="ns-logo"></div>
            <div class="ns-nav" style="margin-left:3px"><span></span><span></span><span></span></div>
            <div class="ns-spacer"></div>
            <div class="ns-cta"></div>
          </div>
          <div class="na-lbl">◀ Gauche</div>
        </label>

        <!-- Centre -->
        <label class="na-btn <?=($header['nav_align']==='center'||$header['nav_align']==='')?'active':''?>" for="na_center" onclick="setNavAlign('center',this)">
          <input type="radio" name="nav_align" id="na_center" value="center" <?=($header['nav_align']==='center'||$header['nav_align']==='')?'checked':''?>>
          <div class="na-schema">
            <div class="ns-logo"></div>
            <div class="ns-spacer"></div>
            <div class="ns-nav"><span></span><span></span><span></span></div>
            <div class="ns-spacer"></div>
            <div class="ns-cta"></div>
          </div>
          <div class="na-lbl">◈ Centré</div>
        </label>

        <!-- Droite -->
        <label class="na-btn <?=$header['nav_align']==='right'?'active':''?>" for="na_right" onclick="setNavAlign('right',this)">
          <input type="radio" name="nav_align" id="na_right" value="right" <?=$header['nav_align']==='right'?'checked':''?>>
          <div class="na-schema">
            <div class="ns-logo"></div>
            <div class="ns-spacer"></div>
            <div class="ns-nav"><span></span><span></span><span></span></div>
            <div style="margin-left:2px"><div class="ns-cta"></div></div>
          </div>
          <div class="na-lbl">▶ Droite</div>
        </label>

      </div>
      <p class="hint" style="margin-bottom:12px">
        <strong>Gauche</strong> = logo · nav · CTA &nbsp;|&nbsp;
        <strong>Centre</strong> = logo · [nav] · CTA &nbsp;|&nbsp;
        <strong>Droite</strong> = logo · [nav + CTA]
      </p>
      <!-- ══════════════════════════════════════════════════════ -->

      <div class="sr">Comportement</div>
      <div class="fr row"><label>🧲 Sticky</label>
        <label class="tog"><input type="checkbox" name="sticky" <?=$header['sticky']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div class="fr row"><label>🌑 Ombre portée</label>
        <label class="tog"><input type="checkbox" name="shadow" <?=$header['shadow']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div class="fr row"><label>📏 Bordure inférieure</label>
        <label class="tog"><input type="checkbox" name="border_bottom" <?=$header['border_bottom']?'checked':''?>><span class="tog-sl"></span></label>
      </div>

      <div class="sr">Mobile</div>
      <div class="fr g2">
        <div><label>Breakpoint (px)</label><input type="number" name="mobile_breakpoint" value="<?=(int)$header['mobile_breakpoint']?>" min="480" max="1280"></div>
        <div><label>Style menu</label>
          <select name="mobile_menu_style">
            <?php foreach(['slide-right'=>'▶ Droite','slide-left'=>'◀ Gauche','fullscreen'=>'⬛ Plein','dropdown'=>'⬇ Drop'] as $v=>$l): ?>
            <option value="<?=$v?>" <?=$header['mobile_menu_style']==$v?'selected':''?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- LOGO -->
    <div class="sp" id="panel-logo">
      <div class="fr"><label>Type de logo</label>
        <div class="rcg">
          <div class="rc"><input type="radio" name="logo_type" id="lt_txt" value="text"  <?=($header['logo_type']??'text')==='text' ?'checked':''?> onchange="toggleLogo()">
          <label for="lt_txt"><span class="ico">✏️</span>Texte</label></div>
          <div class="rc"><input type="radio" name="logo_type" id="lt_img" value="image" <?=($header['logo_type']??'text')==='image'?'checked':''?> onchange="toggleLogo()">
          <label for="lt_img"><span class="ico">🖼️</span>Image</label></div>
        </div>
      </div>
      <div id="logo-txt"><div class="fr"><label>Texte du logo</label>
        <input type="text" name="logo_text" value="<?=htmlspecialchars($header['logo_text']??'')?>" placeholder="Eduardo Desul">
        <div class="hint">Rendu en Playfair Display Bold</div>
      </div></div>
      <div id="logo-img">
        <div class="fr"><label>URL image</label>
          <input type="text" name="logo_url" id="logo-url" value="<?=htmlspecialchars($header['logo_url']??'')?>" placeholder="/front/assets/images/logo.svg">
        </div>
        <div id="logo-prev" style="margin:6px 0;<?=empty($header['logo_url'])?'display:none':''?>">
          <img id="logo-pimg" src="<?=htmlspecialchars($header['logo_url']??'')?>" style="max-height:50px;max-width:180px;border:1px solid var(--BD);border-radius:6px;padding:4px;background:#fff">
        </div>
        <div class="fr g2">
          <div><label>Alt</label><input type="text" name="logo_alt" value="<?=htmlspecialchars($header['logo_alt']??'')?>"></div>
          <div><label>Largeur (px)</label><input type="number" name="logo_width" value="<?=(int)($header['logo_width']??150)?>" min="50" max="400"></div>
        </div>
      </div>
      <div class="fr"><label>Lien du logo</label><input type="text" name="logo_link" value="<?=htmlspecialchars($header['logo_link']??'/')?>"></div>
    </div>

    <!-- MENU -->
    <div class="sp" id="panel-menu">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
        <div class="sr" style="margin:0;border:none">Navigation</div>
        <a href="dashboard.php?page=menus" style="font-size:11px;color:var(--P);font-weight:700;text-decoration:none">↗ Menus complet</a>
      </div>
      <p class="hint" style="margin-bottom:8px">⠿ Glissez · Cliquez l'URL pour choisir une page</p>

      <ul class="mlist" id="menuList">
        <?php foreach($menuItems as $item): ?>
        <li class="mi" draggable="true">
          <span class="dh">⠿</span>
          <input type="text" class="mi-l" value="<?=htmlspecialchars($item['label']??'')?>" placeholder="Libellé">
          <div class="mi-uw">
            <div class="mi-ui">
              <span class="mi-uic"><?=($item['url']??'/')[0]==='/'?'📄':'🌐'?></span>
              <input type="text" class="mi-utxt" value="<?=htmlspecialchars($item['url']??'')?>" placeholder="/url…" autocomplete="off">
              <button type="button" class="mi-ucl" title="Effacer">✕</button>
            </div>
            <div class="mi-dd"></div>
          </div>
          <button type="button" class="btn-del" onclick="this.closest('.mi').remove();buildMJ();sched()">✕</button>
        </li>
        <?php endforeach; ?>
      </ul>

      <button type="button" class="btn-add" onclick="addMI()">+ Lien</button>

      <div class="pages-panel" style="margin-top:14px">
        <div class="pages-panel-hd" onclick="togglePP(this)">
          📂 Pages disponibles <span id="ppArrow">▼</span>
        </div>
        <div class="pages-panel-body" id="ppBody">
          <?php
          $lastGrp='';
          foreach($allPages as $p):
              if($p['group']!==$lastGrp):$lastGrp=$p['group'];?>
          <div class="pg-grp"><?=htmlspecialchars($p['group'])?></div>
          <?php endif; ?>
          <button type="button" class="pg-btn" onclick="addMI(<?=json_encode($p['title'])?>,<?=json_encode($p['url'])?>)">
            <span><?=$p['icon']?></span>
            <span class="pt"><?=htmlspecialchars($p['title'])?></span>
            <span class="pu"><?=htmlspecialchars($p['url'])?></span>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="sr">📞 Téléphone</div>
      <div class="fr row"><label>Afficher téléphone</label>
        <label class="tog"><input type="checkbox" name="phone_enabled" <?=$header['phone_enabled']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div class="fr"><label>Numéro</label>
        <input type="text" name="phone_number" value="<?=htmlspecialchars($header['phone_number']??'')?>" placeholder="06 24 10 58 16">
      </div>

      <div class="sr">🌐 Réseaux sociaux</div>
      <div class="fr row"><label>Afficher icônes</label>
        <label class="tog"><input type="checkbox" name="social_enabled" <?=$header['social_enabled']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div id="socialList">
        <?php foreach($socialLinks as $sl): ?>
        <div class="si">
          <select class="sn"><?php foreach(['facebook'=>'Facebook','instagram'=>'Instagram','linkedin'=>'LinkedIn','youtube'=>'YouTube','tiktok'=>'TikTok','twitter'=>'Twitter/X'] as $n=>$l): ?>
            <option value="<?=$n?>" <?=($sl['network']??'')==$n?'selected':''?>><?=$l?></option>
          <?php endforeach; ?></select>
          <input type="text" class="su" value="<?=htmlspecialchars($sl['url']??'')?>" placeholder="https://...">
          <button type="button" class="btn-del" onclick="this.closest('.si').remove();buildSJ()">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn-add" onclick="addSoc()">+ Réseau</button>
    </div>

    <!-- CTA -->
    <div class="sp" id="panel-cta">
      <div class="sr">🎯 CTA Principal</div>
      <div class="fr row"><label>Activer CTA 1</label>
        <label class="tog"><input type="checkbox" name="cta_enabled" <?=$header['cta_enabled']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div class="fr g2">
        <div><label>Texte</label><input type="text" name="cta_text" value="<?=htmlspecialchars($header['cta_text']??'Contact')?>"></div>
        <div><label>Lien</label><input type="text" name="cta_link" value="<?=htmlspecialchars($header['cta_link']??'/contact')?>"></div>
      </div>
      <div class="fr"><label>Style</label><div class="rcg g3">
        <?php foreach(['primary'=>['🔵','Primaire'],'secondary'=>['🟡','Second'],'outline'=>['⬜','Outline'],'gradient'=>['🌈','Gradient']] as $v=>[$ic,$l]): ?>
        <div class="rc"><input type="radio" name="cta_style" id="c1s_<?=$v?>" value="<?=$v?>" <?=($header['cta_style']??'primary')==$v?'checked':''?>>
        <label for="c1s_<?=$v?>"><span class="ico"><?=$ic?></span><?=$l?></label></div>
        <?php endforeach; ?>
      </div></div>
      <div class="sr" style="margin-top:20px">🎯 CTA Secondaire</div>
      <div class="fr row"><label>Activer CTA 2</label>
        <label class="tog"><input type="checkbox" name="cta2_enabled" <?=$header['cta2_enabled']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div class="fr g2">
        <div><label>Texte</label><input type="text" name="cta2_text" value="<?=htmlspecialchars($header['cta2_text']??'')?>"></div>
        <div><label>Lien</label><input type="text" name="cta2_link" value="<?=htmlspecialchars($header['cta2_link']??'')?>"></div>
      </div>
      <div class="fr"><label>Style</label><div class="rcg g3">
        <?php foreach(['primary'=>['🔵','Primaire'],'secondary'=>['🟡','Second'],'outline'=>['⬜','Outline'],'gradient'=>['🌈','Gradient']] as $v=>[$ic,$l]): ?>
        <div class="rc"><input type="radio" name="cta2_style" id="c2s_<?=$v?>" value="<?=$v?>" <?=($header['cta2_style']??'secondary')==$v?'checked':''?>>
        <label for="c2s_<?=$v?>"><span class="ico"><?=$ic?></span><?=$l?></label></div>
        <?php endforeach; ?>
      </div></div>
    </div>

    <!-- DESIGN -->
    <div class="sp" id="panel-design">
      <div class="sr">Couleurs</div>
      <div class="cg3">
        <?php foreach(['bg_color'=>['Fond',$header['bg_color']??'#ffffff'],'text_color'=>['Texte',$header['text_color']??'#1e293b'],'hover_color'=>['Accent hover',$header['hover_color']??'#d4a574']] as $f=>[$l,$v]): ?>
        <div><label style="font-size:11px;font-weight:700;color:var(--MT);display:block;margin-bottom:4px"><?=$l?></label>
          <div class="cpair">
            <input type="color" id="<?=$f?>_p" value="<?=$v?>" oninput="syncC('<?=$f?>',this.value)">
            <input type="text" name="<?=$f?>" id="<?=$f?>" value="<?=$v?>" oninput="syncP('<?=$f?>_p',this.value)" maxlength="7" placeholder="#000000">
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="sr" style="margin-top:16px">Présets rapides</div>
      <div class="presets">
        <?php foreach([
          ['Blanc',   '#ffffff','#1e293b','#d4a574','#fff','#1e293b'],
          ['Eduardo', '#1a4d7a','#ffffff','#d4a574','#1a4d7a','#fff'],
          ['Sable',   '#f9f6f3','#1a4d7a','#d4a574','#f9f6f3','#1a4d7a'],
          ['Noir',    '#0f172a','#f8fafc','#d4a574','#0f172a','#f8fafc'],
          ['Vert',    '#f0fdf4','#14532d','#16a34a','#f0fdf4','#14532d'],
        ] as [$n,$bg,$tc,$hv,$bb,$bt]): ?>
        <button type="button" class="preset" onclick="applyP('<?=$bg?>','<?=$tc?>','<?=$hv?>')" style="background:<?=$bb?>;color:<?=$bt?>;border-color:<?=$hv?>"><?=$n?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- CODE -->
    <div class="sp" id="panel-code">
      <div class="warn-box">⚠️ <strong>Mode avancé.</strong> Le HTML custom remplace entièrement le rendu automatique.</div>
      <div class="fr"><label>HTML personnalisé</label>
        <textarea name="custom_html" class="code-ta" rows="8" placeholder="<!-- HTML complet -->"><?=htmlspecialchars($header['custom_html']??'')?></textarea>
      </div>
      <div class="fr"><label>CSS additionnel</label>
        <textarea name="custom_css" class="code-ta" rows="5" placeholder="/* CSS */"><?=htmlspecialchars($header['custom_css']??'')?></textarea>
      </div>
      <div class="fr"><label>JS additionnel</label>
        <textarea name="custom_js" class="code-ta" rows="4" placeholder="// JS"><?=htmlspecialchars($header['custom_js']??'')?></textarea>
      </div>
      <button type="button" onclick="clearCode()" style="padding:7px 14px;background:#fee2e2;color:#991b1b;border:none;border-radius:8px;font-size:12px;cursor:pointer">🗑️ Vider le code custom</button>
    </div>
  </aside>

  <!-- PREVIEW -->
  <div class="hdr-pv">
    <div class="pv-bar">
      <span>👁 Aperçu en direct</span>
      <button type="button" class="dev-btn on" onclick="setDev('desktop',this)">🖥 Desktop</button>
      <button type="button" class="dev-btn"    onclick="setDev('tablet',this)">📱 Tablette</button>
      <button type="button" class="dev-btn"    onclick="setDev('mobile',this)">📲 Mobile</button>
      <button type="button" style="padding:5px 12px;background:var(--A);color:#fff;border:none;border-radius:7px;cursor:pointer;font-size:12px;font-weight:700" onclick="refreshPV()">↻</button>
    </div>
    <div class="pv-wrap" id="pvwrap">
      <iframe id="pvframe" srcdoc="<?=htmlspecialchars($pvHtml)?>" scrolling="no"></iframe>
      <div style="font-size:11px;color:var(--MT);text-align:center;margin-top:6px" id="pvinfo">Desktop — 100%</div>
    </div>
  </div>
</div>
</form>

<script>
// ════ PAGES DATA ════
const PAGES = <?= $pagesJson ?>;
const GROUPS = {};
PAGES.forEach(p => { (GROUPS[p.group]=GROUPS[p.group]||[]).push(p); });
function iconForUrl(u){ const p=PAGES.find(x=>x.url===u); return p?(p.icon||'📄'):(u&&u.startsWith('http')?'🌐':'🔗'); }

// ════ NAV ALIGN ════
let currentNavAlign = '<?= htmlspecialchars($header['nav_align']) ?>';
function setNavAlign(val, clickedLabel) {
    currentNavAlign = val;
    document.querySelectorAll('.na-btn').forEach(b => b.classList.remove('active'));
    if (clickedLabel) clickedLabel.classList.add('active');
    // Mettre à jour le radio
    const radio = document.getElementById('na_' + val);
    if (radio) radio.checked = true;
    sched();
}

// ════ DROPDOWN AUTOCOMPLETE ════
function bindMiUrl(row) {
    const txt  = row.querySelector('.mi-utxt');
    const ico  = row.querySelector('.mi-uic');
    const clr  = row.querySelector('.mi-ucl');
    const dd   = row.querySelector('.mi-dd');
    if (!txt||!dd) return;

    function render(q) {
        q=(q||'').toLowerCase().trim();
        let html='',total=0;
        Object.entries(GROUPS).forEach(([grp,pages])=>{
            const list=pages.filter(p=>!q||p.title.toLowerCase().includes(q)||p.url.toLowerCase().includes(q));
            if(!list.length)return;
            html+=`<div class="mi-dg">${esc(grp)}</div>`;
            list.forEach(p=>{
                const t=q?hlMatch(esc(p.title),q):esc(p.title);
                html+=`<div class="mi-do" data-url="${esc(p.url)}" data-title="${esc(p.title)}"><span class="dio">${p.icon||'🔗'}</span><span class="dit">${t}</span><span class="diu">${esc(p.url)}</span></div>`;
                total++;
            });
        });
        if(!total) html='<div style="padding:8px 10px;font-size:11px;color:#94a3b8">Saisie libre acceptée</div>';
        dd.innerHTML=html;
        dd.querySelectorAll('.mi-do').forEach(opt=>{
            opt.addEventListener('mousedown',e=>{
                e.preventDefault();
                txt.value=opt.dataset.url;
                if(ico)ico.textContent=iconForUrl(opt.dataset.url);
                const lbl=row.querySelector('.mi-l');
                if(lbl&&!lbl.value.trim())lbl.value=opt.dataset.title;
                dd.classList.remove('open');
                buildMJ();sched();
            });
        });
    }

    txt.addEventListener('focus',()=>{render(txt.value);dd.classList.add('open');});
    txt.addEventListener('input',()=>{render(txt.value);dd.classList.add('open');if(ico)ico.textContent=iconForUrl(txt.value);buildMJ();sched();});
    txt.addEventListener('blur', ()=>setTimeout(()=>dd.classList.remove('open'),160));
    txt.addEventListener('keydown',e=>{
        if(!dd.classList.contains('open'))return;
        const opts=[...dd.querySelectorAll('.mi-do')];
        const cur=dd.querySelector('.mi-do.hi');
        const idx=cur?opts.indexOf(cur):-1;
        if(e.key==='ArrowDown'){e.preventDefault();if(cur)cur.classList.remove('hi');const n=opts[Math.min(idx+1,opts.length-1)];if(n){n.classList.add('hi');n.scrollIntoView({block:'nearest'});}}
        if(e.key==='ArrowUp')  {e.preventDefault();if(cur)cur.classList.remove('hi');const n=opts[Math.max(idx-1,0)];if(n){n.classList.add('hi');n.scrollIntoView({block:'nearest'});}}
        if(e.key==='Enter'||e.key==='Tab'){const h=dd.querySelector('.mi-do.hi');if(h){e.preventDefault();txt.value=h.dataset.url;if(ico)ico.textContent=iconForUrl(h.dataset.url);const lbl=row.querySelector('.mi-l');if(lbl&&!lbl.value.trim())lbl.value=h.dataset.title;dd.classList.remove('open');buildMJ();sched();}}
        if(e.key==='Escape')dd.classList.remove('open');
    });
    if(clr)clr.addEventListener('click',()=>{txt.value='';if(ico)ico.textContent='🔗';render('');dd.classList.add('open');txt.focus();buildMJ();sched();});
    if(ico&&txt.value)ico.textContent=iconForUrl(txt.value);
}
document.querySelectorAll('.mi').forEach(bindMiUrl);

// ════ BUILD JSON MENU ════
function buildMJ(){
    const items=[...document.querySelectorAll('.mi')].map(li=>({
        label:li.querySelector('.mi-l')?.value.trim()||'',
        url:  li.querySelector('.mi-utxt')?.value.trim()||'',
    })).filter(i=>i.label||i.url);
    document.getElementById('mijson').value=JSON.stringify(items);
}

// ════ AJOUTER LIGNE ════
function addMI(label='',url=''){
    const li=document.createElement('li');li.className='mi';li.draggable=true;
    li.innerHTML=`<span class="dh">⠿</span>
<input type="text" class="mi-l" value="${esc(label)}" placeholder="Libellé">
<div class="mi-uw">
  <div class="mi-ui">
    <span class="mi-uic">${iconForUrl(url)}</span>
    <input type="text" class="mi-utxt" value="${esc(url)}" placeholder="/url…" autocomplete="off">
    <button type="button" class="mi-ucl" title="Effacer">✕</button>
  </div>
  <div class="mi-dd"></div>
</div>
<button type="button" class="btn-del" onclick="this.closest('.mi').remove();buildMJ();sched()">✕</button>`;
    document.getElementById('menuList').appendChild(li);
    bindMiUrl(li);
    initDnD();
    li.querySelector('.mi-l').focus();
    buildMJ();sched();
}

// ════ ACCORDÉON PAGES ════
function togglePP(hd){
    const body=document.getElementById('ppBody');
    const arrow=document.getElementById('ppArrow');
    const open=body.classList.toggle('open');
    if(arrow)arrow.textContent=open?'▲':'▼';
}

// ════ DRAG & DROP ════
let dragSrc=null;
function initDnD(){
    document.querySelectorAll('#menuList .mi').forEach(el=>{
        el.addEventListener('dragstart',e=>{dragSrc=el;el.classList.add('dragging');e.dataTransfer.effectAllowed='move';});
        el.addEventListener('dragend',()=>{dragSrc?.classList.remove('dragging');buildMJ();});
        el.addEventListener('dragover',e=>{
            e.preventDefault();
            if(dragSrc&&dragSrc!==el){
                const mid=el.getBoundingClientRect().top+el.offsetHeight/2;
                el.parentNode.insertBefore(dragSrc,e.clientY<mid?el:el.nextSibling);
            }
        });
    });
}
initDnD();
document.getElementById('menuList').addEventListener('input',()=>{buildMJ();sched();});

// ════ SOCIAL ════
const SNETS={facebook:'Facebook',instagram:'Instagram',linkedin:'LinkedIn',youtube:'YouTube',tiktok:'TikTok',twitter:'Twitter/X'};
function addSoc(){
    const d=document.createElement('div');d.className='si';
    d.innerHTML='<select class="sn">'+Object.entries(SNETS).map(([v,l])=>`<option value="${v}">${l}</option>`).join('')+'</select><input type="text" class="su" placeholder="https://..."><button type="button" class="btn-del" onclick="this.closest(\'.si\').remove();buildSJ()">✕</button>';
    document.getElementById('socialList').appendChild(d);
    d.querySelector('.su').addEventListener('input',buildSJ);
    d.querySelector('.sn').addEventListener('change',buildSJ);
}
function buildSJ(){
    const items=[...document.querySelectorAll('.si')].map(d=>({network:d.querySelector('.sn').value,url:d.querySelector('.su').value}));
    document.getElementById('sljson').value=JSON.stringify(items);
}

// ════ TABS ════
document.querySelectorAll('.stab').forEach(t=>t.addEventListener('click',()=>{
    document.querySelectorAll('.stab,.sp').forEach(e=>e.classList.remove('on'));
    t.classList.add('on');document.getElementById('panel-'+t.dataset.p).classList.add('on');
}));

// ════ LOGO ════
function toggleLogo(){
    const img=document.getElementById('lt_img').checked;
    document.getElementById('logo-txt').style.display=img?'none':'block';
    document.getElementById('logo-img').style.display=img?'block':'none';
}
toggleLogo();
document.getElementById('logo-url').addEventListener('input',function(){
    const p=document.getElementById('logo-prev'),i=document.getElementById('logo-pimg');
    if(this.value){i.src=this.value;p.style.display='block';}else p.style.display='none';
});

// ════ COULEURS ════
function syncC(id,v){document.getElementById(id).value=v;sched();}
function syncP(id,v){if(/^#[0-9a-f]{3,6}$/i.test(v)){document.getElementById(id).value=v;sched();}}
function applyP(bg,tc,hv){
    ['bg_color','text_color','hover_color'].forEach((f,i)=>{const v=[bg,tc,hv][i];document.getElementById(f).value=v;document.getElementById(f+'_p').value=v;});
    refreshPV();
}

// ════ CODE ════
function clearCode(){if(!confirm('Vider le code custom ?'))return;['custom_html','custom_css','custom_js'].forEach(n=>{const e=document.querySelector('[name='+n+']');if(e)e.value='';});refreshPV();}

// ════════════════════════════════════════════════════════════════
//  buildPV() — PREVIEW JS — miroir exact du PHP bldPreview()
//  Supporte les 3 alignements : left / center / right
// ════════════════════════════════════════════════════════════════
function gv(n){const e=document.querySelector('[name="'+n+'"]');if(!e)return'';return e.type==='checkbox'?e.checked:e.value;}
function gr(n){const e=document.querySelector('[name="'+n+'"]:checked');return e?e.value:'';}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function hlMatch(str,q){try{return str.replace(new RegExp('('+q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi'),'<mark style="background:#fef9c3;padding:0">$1</mark>');}catch(e){return str;}}

function buildGridLayout(align, logoHtml, logoLink, nav, rightCol) {
    switch(align) {
        case 'left':
            return `<div style="max-width:1260px;margin:0 auto;padding:0 32px;height:100%;
                        display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px">
              <a href="${logoLink}" style="text-decoration:none;display:flex;align-items:center">${logoHtml}</a>
              <nav style="display:flex;align-items:center;gap:2px;justify-self:start">${nav}</nav>
              <div style="display:flex;align-items:center;gap:12px;justify-self:end">${rightCol}</div>
            </div>`;
        case 'right':
            return `<div style="max-width:1260px;margin:0 auto;padding:0 32px;height:100%;
                        display:grid;grid-template-columns:1fr auto;align-items:center;gap:24px">
              <a href="${logoLink}" style="justify-self:start;text-decoration:none;display:flex;align-items:center">${logoHtml}</a>
              <div style="display:flex;align-items:center;gap:16px;justify-self:end">
                <nav style="display:flex;align-items:center;gap:2px">${nav}</nav>
                ${rightCol ? `<div style="display:flex;align-items:center;gap:10px">${rightCol}</div>` : ''}
              </div>
            </div>`;
        case 'center':
        default:
            return `<div style="max-width:1260px;margin:0 auto;padding:0 32px;height:100%;
                        display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:16px">
              <a href="${logoLink}" style="justify-self:start;text-decoration:none;display:flex;align-items:center">${logoHtml}</a>
              <nav style="display:flex;align-items:center;gap:2px;justify-self:center">${nav}</nav>
              <div style="display:flex;align-items:center;gap:12px;justify-self:end">${rightCol}</div>
            </div>`;
    }
}

function buildPV(){
    const bg  = gv('bg_color')||'#ffffff';
    const tc  = gv('text_color')||'#1e293b';
    const hv  = gv('hover_color')||'#d4a574';
    const ht  = parseInt(gv('height')||80);
    const shad = gv('shadow') ? '0 2px 12px rgba(0,0,0,.10)' : 'none';
    const brd  = gv('border_bottom') ? 'border-bottom:1px solid #e2d9ce;' : '';
    const align = currentNavAlign || 'center';

    // Logo
    const logoLink = esc(gv('logo_link')||'/');
    let logo = '';
    if(gr('logo_type')==='image'){
        const src=gv('logo_url');
        logo = src
            ? `<img src="${esc(src)}" style="height:${Math.min(ht-20,52)}px;width:auto" alt="">`
            : `<span style="font-weight:800;font-size:20px;color:#1a4d7a;font-family:'Playfair Display',Georgia,serif">${esc(gv('name')||'Logo')}</span>`;
    } else {
        logo = `<span style="font-weight:800;font-size:20px;color:#1a4d7a;font-family:'Playfair Display',Georgia,serif">${esc(gv('logo_text')||gv('name')||'Logo')}</span>`;
    }

    // Nav
    const items = JSON.parse(document.getElementById('mijson').value||'[]');
    const nav = items.map(it =>
        `<a href="${esc(it.url||'#')}" style="color:${tc};text-decoration:none;font-size:14px;font-weight:500;padding:6px 13px;border-radius:6px;white-space:nowrap">${esc(it.label||'')}</a>`
    ).join('');

    // Téléphone
    let ph='';
    if(gv('phone_enabled')&&gv('phone_number')){
        const p=esc(gv('phone_number'));
        ph=`<a href="tel:${p}" style="color:#1a4d7a;text-decoration:none;font-size:13px;font-weight:600;white-space:nowrap">${p}</a>`;
    }

    // CTA
    const ST={
        primary:  `background:#1a4d7a;color:#fff;border:none;`,
        secondary:`background:transparent;color:${hv};border:2px solid ${hv};`,
        outline:  `background:transparent;color:${tc};border:2px solid ${tc};`,
        gradient: `background:linear-gradient(135deg,${hv},#b8844f);color:#fff;border:none;`,
    };
    let ctas='';
    if(gv('cta_enabled')&&gv('cta_text')){
        const s=ST[gr('cta_style')]||ST.primary;
        ctas+=`<a href="${esc(gv('cta_link')||'#')}" style="${s}padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap">${esc(gv('cta_text'))}</a>`;
    }
    if(gv('cta2_enabled')&&gv('cta2_text')){
        const s=ST[gr('cta2_style')]||ST.secondary;
        ctas+=`<a href="${esc(gv('cta2_link')||'#')}" style="${s}padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;margin-left:8px;white-space:nowrap">${esc(gv('cta2_text'))}</a>`;
    }

    // Custom HTML override
    const ch = gv('custom_html');
    const chClean = ch && ch.trim();
    const isJsonMenu = chClean && chClean.startsWith('[{');
    if(chClean && !isJsonMenu){
        return `<!DOCTYPE html><html><head><meta charset="utf-8"><style>*{margin:0;padding:0;box-sizing:border-box}${gv('custom_css')}</style></head><body>${ch}</body></html>`;
    }

    const rightCol = [ph, ctas].filter(Boolean).join('');
    const layout = buildGridLayout(align, logo, logoLink, nav, rightCol);

    // Badge indicateur d'alignement
    const alignLabels = {left:'◀ Nav gauche', center:'◈ Nav centrée', right:'▶ Nav droite'};
    const badge = alignLabels[align] || align;

    return `<!DOCTYPE html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;overflow:hidden;background:${bg}}
a:hover{color:${hv} !important}
.align-badge{position:fixed;bottom:8px;right:8px;background:rgba(26,77,122,.85);color:#fff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;letter-spacing:.3px}
</style></head>
<body>
<header style="background:${bg};height:${ht}px;box-shadow:${shad};${brd}position:relative">
  ${layout}
</header>
<div class="align-badge">${badge}</div>
</body></html>`;
}

function refreshPV(){buildMJ();buildSJ();document.getElementById('pvframe').srcdoc=buildPV();}
document.getElementById('pvframe').addEventListener('load',function(){
    try{const h=this.contentDocument?.body?.scrollHeight;if(h>0)this.style.height=(h+4)+'px';}catch(e){}
});

let _t;function sched(){clearTimeout(_t);_t=setTimeout(refreshPV,350);}
document.getElementById('hdrForm').addEventListener('input',sched);
document.getElementById('hdrForm').addEventListener('change',()=>setTimeout(refreshPV,100));
document.getElementById('inp-name')?.addEventListener('input',function(){
    const el=document.getElementById('ttl');if(el)el.childNodes[0].textContent=this.value||'Nouveau Header';
});

function setDev(d,btn){
    document.querySelectorAll('.dev-btn').forEach(b=>b.classList.remove('on'));btn.classList.add('on');
    const w=document.getElementById('pvwrap'),i=document.getElementById('pvinfo');
    if(d==='mobile'){w.className='pv-wrap mobile';i.textContent='Mobile — 375px';}
    else if(d==='tablet'){w.className='pv-wrap tablet';i.textContent='Tablette — 768px';}
    else{w.className='pv-wrap';i.textContent='Desktop — 100%';}
}
refreshPV();
buildMJ();
</script>