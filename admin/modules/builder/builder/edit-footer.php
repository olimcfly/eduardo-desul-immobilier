<?php
// =============================================================
//  IMMO LOCAL+ — Éditeur Footer v1.0
//  admin/modules/builder/builder/edit-footer.php
//  Route : dashboard.php?page=footers-edit&id=X
// =============================================================
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit('Accès refusé'); }

// ── Connexion DB robuste ──
$db = null;
if (isset($pdo)) {
    if ($pdo instanceof PDO) $db = $pdo;
    elseif (method_exists($pdo, 'getConnection')) $db = $pdo->getConnection();
    elseif (method_exists($pdo, 'query')) $db = $pdo;
}
if (!$db) {
    try {
        require_once __DIR__ . '/../../../../config/config.php';
        $db = new PDO(
            'mysql:host='.(defined('DB_HOST')?DB_HOST:'localhost').';dbname='.(defined('DB_NAME')?DB_NAME:'mahe6420_cms-site-ed-bordeaux').';charset=utf8mb4',
            defined('DB_USER')?DB_USER:'mahe6420_edbordeaux',
            defined('DB_PASS')?DB_PASS:'',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
        );
    } catch (Exception $e) {
        echo '<div style="padding:20px;color:#dc2626;background:#fee2e2;border-radius:8px;margin:20px">❌ DB impossible : '.htmlspecialchars($e->getMessage()).'</div>';
        return;
    }
}

$msg = ''; $err = '';
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$footer = null;

if ($id > 0) {
    try {
        $st = $db->prepare("SELECT * FROM footers WHERE id = ?");
        $st->execute([$id]);
        $footer = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $err = 'Erreur DB : '.$e->getMessage(); }
}

if (!$footer) {
    $footer = [
        'id'=>0,'name'=>'','slug'=>'','type'=>'standard','status'=>'draft','is_default'=>0,
        'bg_color'=>'#1a2e4a','text_color'=>'#cbd5e1','accent_color'=>'#d4a574',
        'logo_text'=>'Eduardo Desul','logo_tagline'=>'Conseiller immobilier indépendant à Bordeaux',
        'col1_title'=>'Navigation','col1_links'=>'[{"label":"Accueil","url":"/"},{"label":"Acheter","url":"/acheter"},{"label":"Vendre","url":"/vendre"},{"label":"Estimer","url":"/estimation"}]',
        'col2_title'=>'Services','col2_links'=>'[{"label":"Estimation gratuite","url":"/estimation"},{"label":"Blog","url":"/blog"},{"label":"Secteurs","url":"/secteurs"}]',
        'col3_title'=>'Contact','col3_links'=>'[]',
        'phone'=>'06 24 10 58 16','email'=>'contact@eduardo-desul-immobilier.fr',
        'address'=>'12A rue du Commandant Charcot, 33290 Blanquefort',
        'social_enabled'=>1,'social_links'=>'[{"network":"facebook","url":"https://facebook.com"},{"network":"instagram","url":"https://instagram.com"},{"network":"linkedin","url":"https://linkedin.com"}]',
        'copyright_text'=>'© '.date('Y').' Eduardo Desul — Tous droits réservés',
        'legal_links'=>'[{"label":"Mentions légales","url":"/mentions-legales"},{"label":"Politique de confidentialité","url":"/politique-confidentialite"}]',
        'show_cpi'=>1,'cpi_number'=>'CPI 7501 2021 000 000 444',
        'custom_html'=>'','custom_css'=>'','custom_js'=>'',
        'columns'=>3,
    ];
}

// ── Sauvegarde POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'save_footer') {
    try {
        $name = trim($_POST['name']??'footer');
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$name),'-'));

        foreach(['col1_links','col2_links','col3_links','social_links','legal_links'] as $jf) {
            $v = $_POST[$jf.'_json'] ?? '[]';
            if (json_decode($v) === null) $v = '[]';
            $_POST[$jf] = $v;
        }

        $data = [
            'name'=>$name,'slug'=>$slug,
            'type'=>in_array($_POST['type']??'',['standard','minimal','columns','dark','light'])?$_POST['type']:'standard',
            'status'=>in_array($_POST['status']??'',['draft','active','inactive'])?$_POST['status']:'draft',
            'is_default'=>isset($_POST['is_default'])?1:0,
            'bg_color'=>preg_match('/^#[0-9a-f]{3,6}$/i',$_POST['bg_color']??'')?$_POST['bg_color']:'#1a2e4a',
            'text_color'=>preg_match('/^#[0-9a-f]{3,6}$/i',$_POST['text_color']??'')?$_POST['text_color']:'#cbd5e1',
            'accent_color'=>preg_match('/^#[0-9a-f]{3,6}$/i',$_POST['accent_color']??'')?$_POST['accent_color']:'#d4a574',
            'logo_text'=>trim($_POST['logo_text']??''),
            'logo_tagline'=>trim($_POST['logo_tagline']??''),
            'columns'=>max(1,min(4,(int)($_POST['columns']??3))),
            'col1_title'=>trim($_POST['col1_title']??''),
            'col1_links'=>$_POST['col1_links'],
            'col2_title'=>trim($_POST['col2_title']??''),
            'col2_links'=>$_POST['col2_links'],
            'col3_title'=>trim($_POST['col3_title']??''),
            'col3_links'=>$_POST['col3_links'],
            'phone'=>trim($_POST['phone']??''),
            'email'=>trim($_POST['email']??''),
            'address'=>trim($_POST['address']??''),
            'social_enabled'=>isset($_POST['social_enabled'])?1:0,
            'social_links'=>$_POST['social_links'],
            'copyright_text'=>trim($_POST['copyright_text']??''),
            'legal_links'=>$_POST['legal_links'],
            'show_cpi'=>isset($_POST['show_cpi'])?1:0,
            'cpi_number'=>trim($_POST['cpi_number']??''),
            'custom_html'=>$_POST['custom_html']??'',
            'custom_css'=>$_POST['custom_css']??'',
            'custom_js'=>$_POST['custom_js']??'',
        ];

        if ($data['is_default']) $db->exec("UPDATE footers SET is_default=0");

        // Vérifier colonnes existantes
        $cols_exist = [];
        try {
            $cols_res = $db->query("SHOW COLUMNS FROM footers")->fetchAll(PDO::FETCH_COLUMN);
            $cols_exist = $cols_res;
        } catch(Exception $e) {}

        $data_filtered = [];
        foreach($data as $k=>$v) {
            if(empty($cols_exist) || in_array($k, $cols_exist)) $data_filtered[$k] = $v;
        }

        if ($id > 0) {
            $sets = implode(',',array_map(fn($k)=>"`$k`=:$k",array_keys($data_filtered)));
            $st   = $db->prepare("UPDATE footers SET $sets,updated_at=NOW() WHERE id=:id");
            $data_filtered['id'] = $id; $st->execute($data_filtered);
            $msg = '✅ Footer mis à jour.';
        } else {
            $cols_q = implode(',',array_map(fn($k)=>"`$k`",array_keys($data_filtered)));
            $vals_q = implode(',',array_map(fn($k)=>":$k",array_keys($data_filtered)));
            $st = $db->prepare("INSERT INTO footers ($cols_q) VALUES ($vals_q)");
            $st->execute($data_filtered);
            $id  = (int)$db->lastInsertId();
            $msg = '✅ Footer créé.';
        }
        $st2 = $db->prepare("SELECT * FROM footers WHERE id=?");
        $st2->execute([$id]);
        $footer = $st2->fetch(PDO::FETCH_ASSOC) ?: $footer;
    } catch (Exception $e) { $err = '❌ '.$e->getMessage(); }
}

$col1Links  = json_decode($footer['col1_links']??'[]',true)?:[];
$col2Links  = json_decode($footer['col2_links']??'[]',true)?:[];
$col3Links  = json_decode($footer['col3_links']??'[]',true)?:[];
$socialLinks= json_decode($footer['social_links']??'[]',true)?:[];
$legalLinks = json_decode($footer['legal_links']??'[]',true)?:[];

function buildFooterPreview(array $f, array $c1, array $c2, array $c3, array $soc, array $leg): string {
    $bg  = $f['bg_color']??'#1a2e4a';
    $tc  = $f['text_color']??'#cbd5e1';
    $ac  = $f['accent_color']??'#d4a574';
    $ch  = $f['custom_html']??'';
    if($ch && trim($ch)) return "<!DOCTYPE html><html><head><meta charset='utf-8'><style>*{margin:0;padding:0;box-sizing:border-box}".($f['custom_css']??'')."</style></head><body>".$ch."</body></html>";

    $logo = '<div style="margin-bottom:8px"><span style="font-weight:800;font-size:22px;color:'.$ac.';font-family:\'Playfair Display\',Georgia,serif">'.htmlspecialchars($f['logo_text']??'Logo').'</span></div>';
    $tagline = '<div style="font-size:13px;color:'.$tc.';opacity:.8;margin-bottom:14px">'.htmlspecialchars($f['logo_tagline']??'').'</div>';

    $contact = '';
    if(!empty($f['phone'])) $contact .= '<div style="font-size:13px;color:'.$tc.';margin-bottom:5px">📞 '.htmlspecialchars($f['phone']).'</div>';
    if(!empty($f['email'])) $contact .= '<div style="font-size:13px;color:'.$tc.';margin-bottom:5px">✉️ '.htmlspecialchars($f['email']).'</div>';
    if(!empty($f['address'])) $contact .= '<div style="font-size:12px;color:'.$tc.';opacity:.7;margin-bottom:5px">📍 '.htmlspecialchars($f['address']).'</div>';
    if(!empty($f['show_cpi'])&&!empty($f['cpi_number'])) $contact .= '<div style="font-size:11px;color:'.$ac.';margin-top:8px">🏛️ '.htmlspecialchars($f['cpi_number']).'</div>';

    $mkLinks = fn($links,$title) => '<div><div style="font-weight:700;font-size:13px;color:'.$ac.';margin-bottom:10px;text-transform:uppercase;letter-spacing:.06em">'.htmlspecialchars($title).'</div>'.implode('',array_map(fn($l)=>'<div style="margin-bottom:6px"><a href="'.htmlspecialchars($l['url']??'#').'" style="color:'.$tc.';text-decoration:none;font-size:13px;opacity:.85">'.htmlspecialchars($l['label']??'').'</a></div>',$links)).'</div>';

    $socIcons = ['facebook'=>'f','instagram'=>'📸','linkedin'=>'in','youtube'=>'▶','tiktok'=>'♪','twitter'=>'𝕏'];
    $socHtml = '';
    if(!empty($f['social_enabled'])) {
        foreach($soc as $s) {
            $n = $s['network']??'';
            $ic = ['facebook'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>',
                   'instagram'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>',
                   'linkedin'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
                   'youtube'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.97C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.97A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="white"/></svg>',
                   'tiktok'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.28 6.28 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.15 8.15 0 004.77 1.52V6.76a4.85 4.85 0 01-1-.07z"/></svg>',
                   'twitter'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            ][$n] ?? '🔗';
            $socHtml .= '<a href="'.htmlspecialchars($s['url']??'#').'" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.1);color:'.$tc.';text-decoration:none;margin-right:8px">'.$ic.'</a>';
        }
    }

    $legHtml = implode(' · ', array_map(fn($l)=>'<a href="'.htmlspecialchars($l['url']??'#').'" style="color:'.$tc.';text-decoration:none;font-size:12px;opacity:.7">'.htmlspecialchars($l['label']??'').'</a>', $leg));

    $cols = (int)($f['columns']??3);
    $gridCols = $cols === 4 ? '1fr 1fr 1fr 1fr' : ($cols === 3 ? '2fr 1fr 1fr' : ($cols === 2 ? '1fr 1fr' : '1fr'));

    $col1HTML = $logo.$tagline.$contact.($socHtml?'<div style="margin-top:14px">'.$socHtml.'</div>':'');
    $col2HTML = $mkLinks($c1, $f['col1_title']??'Navigation');
    $col3HTML = $mkLinks($c2, $f['col2_title']??'Services');
    $col4HTML = $cols >= 3 ? $mkLinks($c3, $f['col3_title']??'Contact') : '';

    $colsInner = '<div style="display:grid;grid-template-columns:'.$gridCols.';gap:32px;padding:40px 0 30px">'
        .'<div>'.$col1HTML.'</div>'
        .'<div>'.$col2HTML.'</div>'
        .'<div>'.$col3HTML.'</div>'
        .($cols>=4?'<div>'.$col4HTML.'</div>':'')
        .'</div>';

    $bottom = '<div style="border-top:1px solid rgba(255,255,255,.1);padding:18px 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">'
        .'<div style="font-size:12px;color:'.$tc.';opacity:.6">'.htmlspecialchars($f['copyright_text']??'').'</div>'
        .'<div style="display:flex;gap:16px">'.$legHtml.'</div>'
        .'</div>';

    return "<!DOCTYPE html><html><head><meta charset='utf-8'><link href='https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap' rel='stylesheet'><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',sans-serif}</style></head><body>"
        ."<footer style='background:{$bg};padding:0 40px'>{$colsInner}{$bottom}</footer>"
        ."</body></html>";
}

$pvHtml = buildFooterPreview($footer, $col1Links, $col2Links, $col3Links, $socialLinks, $legalLinks);
?>
<style>
:root{--P:#1a4d7a;--A:#d4a574;--BG:#f9f6f3;--W:#fff;--BD:#e2e8f0;--TX:#1e293b;--MT:#64748b;--R:10px}
.ftr-ed{display:grid;grid-template-columns:320px 1fr;height:calc(100vh - 110px);overflow:hidden;border-radius:12px;border:1px solid var(--BD);box-shadow:0 2px 12px rgba(0,0,0,.07)}
.ftr-top{display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--W);border-bottom:1px solid var(--BD);margin-bottom:16px;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.ftr-top h1{flex:1;font-size:15px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0}
.ftr-sb{overflow-y:auto;background:var(--W);border-right:1px solid var(--BD)}
.ftr-pv{display:flex;flex-direction:column;background:#e5e7eb;overflow:hidden}
.stabs{display:flex;border-bottom:1px solid var(--BD);position:sticky;top:0;background:var(--W);z-index:10}
.stab{flex:1;padding:10px 2px;text-align:center;font-size:10px;font-weight:700;color:var(--MT);cursor:pointer;border-bottom:2px solid transparent;transition:.15s;text-transform:uppercase;letter-spacing:.4px;line-height:1.3}
.stab:hover{color:var(--P)}.stab.on{color:var(--P);border-bottom-color:var(--P)}
.sp{display:none;padding:14px 14px 80px}.sp.on{display:block}
.sr{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--MT);margin:16px 0 8px;padding-bottom:4px;border-bottom:1px solid var(--BD)}
.fr{margin-bottom:11px}.fr label{display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:var(--TX)}
.fr input,.fr select,.fr textarea{width:100%;padding:7px 10px;border:1px solid var(--BD);border-radius:8px;font-size:13px;color:var(--TX);background:var(--BG);transition:.15s}
.fr input:focus,.fr select:focus,.fr textarea:focus{outline:none;border-color:var(--P);background:var(--W);box-shadow:0 0 0 3px rgba(26,77,122,.08)}
.fr textarea{resize:vertical;min-height:90px;font-family:monospace;font-size:12px}
.fr.row{display:flex;align-items:center;gap:8px}.fr.row label{margin:0;flex:1}
.fr.g2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.tog{position:relative;width:36px;height:20px;flex-shrink:0}
.tog input{opacity:0;width:0;height:0;position:absolute}
.tog-sl{position:absolute;inset:0;background:#cbd5e1;border-radius:20px;cursor:pointer;transition:.25s}
.tog-sl::before{content:'';position:absolute;left:2px;top:2px;width:16px;height:16px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.tog input:checked+.tog-sl{background:var(--P)}.tog input:checked+.tog-sl::before{transform:translateX(16px)}
.mlist{list-style:none;min-height:30px}
.mi{display:flex;align-items:center;gap:5px;background:var(--BG);border:1px solid var(--BD);border-radius:8px;padding:5px 7px;margin-bottom:5px;cursor:grab}
.mi:active{cursor:grabbing;opacity:.7}.mi .dh{color:var(--MT);user-select:none;font-size:14px}
.mi input{padding:4px 7px;border:1px solid var(--BD);border-radius:6px;font-size:12px;background:var(--W)}
.mi .mi-l{max-width:100px}.mi .mi-u{flex:1}
.si{display:grid;grid-template-columns:100px 1fr 30px;gap:5px;align-items:center;margin-bottom:6px}
.si select,.si input{padding:6px 8px;border:1px solid var(--BD);border-radius:7px;font-size:12px}
.cg3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.cpair{display:flex;align-items:center;gap:6px}
.cpair input[type=color]{width:34px;height:34px;border:1px solid var(--BD);border-radius:7px;padding:2px;cursor:pointer;flex-shrink:0}
.cpair input[type=text]{flex:1}
.presets{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
.preset{padding:6px 10px;border:1px solid var(--BD);border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:.15s}
.preset:hover{transform:translateY(-1px);box-shadow:0 2px 8px rgba(0,0,0,.1)}
.btn-save{padding:9px 20px;background:var(--P);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:13px;cursor:pointer;transition:.15s;white-space:nowrap}
.btn-save:hover{background:#1557a0;transform:translateY(-1px)}
.btn-back{padding:8px 14px;background:var(--BG);color:var(--TX);border:1px solid var(--BD);border-radius:9px;font-size:12px;cursor:pointer;text-decoration:none;display:inline-block;white-space:nowrap}
.btn-back:hover{background:var(--BD)}
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
.pv-wrap.mobile{max-width:420px;margin:0 auto}.pv-wrap.tablet{max-width:800px;margin:0 auto}
#pvframe{width:100%;border:none;border-radius:var(--R);box-shadow:0 4px 20px rgba(0,0,0,.15);background:#fff}
.pv-info{font-size:11px;color:var(--MT);text-align:center;margin-top:6px}
.code-ta{font-family:monospace;font-size:12px;line-height:1.6;background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:8px;padding:10px;resize:vertical;min-height:100px;width:100%}
.hint{font-size:10.5px;color:var(--MT);margin-top:3px;font-style:italic}
.col-section{background:var(--BG);border:1px solid var(--BD);border-radius:10px;padding:12px;margin-bottom:12px}
.col-section h4{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--MT);margin-bottom:10px}
</style>

<form method="POST" id="ftrForm" autocomplete="off">
<input type="hidden" name="action" value="save_footer">
<input type="hidden" name="col1_links_json" id="c1json" value="<?= htmlspecialchars(json_encode($col1Links,JSON_UNESCAPED_UNICODE)) ?>">
<input type="hidden" name="col2_links_json" id="c2json" value="<?= htmlspecialchars(json_encode($col2Links,JSON_UNESCAPED_UNICODE)) ?>">
<input type="hidden" name="col3_links_json" id="c3json" value="<?= htmlspecialchars(json_encode($col3Links,JSON_UNESCAPED_UNICODE)) ?>">
<input type="hidden" name="social_links_json" id="sljson" value="<?= htmlspecialchars(json_encode($socialLinks,JSON_UNESCAPED_UNICODE)) ?>">
<input type="hidden" name="legal_links_json" id="lgjson" value="<?= htmlspecialchars(json_encode($legalLinks,JSON_UNESCAPED_UNICODE)) ?>">

<!-- TOP BAR -->
<div class="ftr-top">
  <a href="dashboard.php?page=footers" class="btn-back">← Footers</a>
  <h1 id="ttl">
    <?= $id ? htmlspecialchars($footer['name']) : 'Nouveau Footer' ?>
    <?php if($footer['status']): ?><span class="badge badge-<?=$footer['status']?>"><?=$footer['status']?></span><?php endif; ?>
  </h1>
  <?php if($msg): ?><div class="alert alert-ok"><?=$msg?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-er"><?=$err?></div><?php endif; ?>
  <button type="submit" class="btn-save">💾 Sauvegarder</button>
</div>

<div class="ftr-ed">

  <!-- SIDEBAR -->
  <aside class="ftr-sb">
    <div class="stabs">
      <div class="stab on" data-p="general">⚙️<br>Général</div>
      <div class="stab"    data-p="brand">🏷️<br>Identité</div>
      <div class="stab"    data-p="colonnes">☰<br>Colonnes</div>
      <div class="stab"    data-p="contact">📞<br>Contact</div>
      <div class="stab"    data-p="bas">⬇<br>Bas</div>
      <div class="stab"    data-p="code">&lt;/&gt;<br>Code</div>
    </div>

    <!-- GÉNÉRAL -->
    <div class="sp on" id="panel-general">
      <div class="fr">
        <label>Nom du footer *</label>
        <input type="text" name="name" id="inp-name" value="<?=htmlspecialchars($footer['name'])?>" placeholder="Ex: Footer principal" required>
      </div>
      <div class="fr g2">
        <div><label>Statut</label>
          <select name="status">
            <option value="draft"    <?=$footer['status']=='draft'   ?'selected':''?>>📝 Brouillon</option>
            <option value="active"   <?=$footer['status']=='active'  ?'selected':''?>>✅ Actif</option>
            <option value="inactive" <?=$footer['status']=='inactive'?'selected':''?>>⏸️ Inactif</option>
          </select>
        </div>
        <div><label>Nb colonnes</label>
          <select name="columns" id="nb-cols">
            <?php foreach([1,2,3,4] as $c): ?>
            <option value="<?=$c?>" <?=(int)($footer['columns']??3)==$c?'selected':''?>><?=$c?> col.</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="fr row"><label>Footer par défaut</label>
        <label class="tog"><input type="checkbox" name="is_default" <?=$footer['is_default']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div class="sr">Couleurs</div>
      <div class="cg3">
        <?php foreach(['bg_color'=>['Fond',$footer['bg_color']??'#1a2e4a'],'text_color'=>['Texte',$footer['text_color']??'#cbd5e1'],'accent_color'=>['Accent',$footer['accent_color']??'#d4a574']] as $f2=>[$l,$v]): ?>
        <div>
          <label style="font-size:11px;font-weight:700;color:var(--MT);display:block;margin-bottom:4px"><?=$l?></label>
          <div class="cpair">
            <input type="color" id="<?=$f2?>_p" value="<?=$v?>" oninput="syncC('<?=$f2?>',this.value)">
            <input type="text" name="<?=$f2?>" id="<?=$f2?>" value="<?=$v?>" oninput="syncP('<?=$f2?>_p',this.value)" maxlength="7">
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="presets" style="margin-top:12px">
        <div class="sr" style="width:100%;margin-top:4px">Présets</div>
        <?php foreach([
          ['Dark Bleu','#1a2e4a','#cbd5e1','#d4a574'],
          ['Noir','#0f172a','#f8fafc','#6366f1'],
          ['Sable','#1a4d7a','#f9f6f3','#d4a574'],
          ['Vert','#14532d','#f0fdf4','#4ade80'],
        ] as [$n,$bg,$tc,$ac]): ?>
        <button type="button" class="preset" onclick="applyP('<?=$bg?>','<?=$tc?>','<?=$ac?>')" style="background:<?=$bg?>;color:<?=$tc?>;border-color:<?=$ac?>"><?=$n?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- IDENTITÉ -->
    <div class="sp" id="panel-brand">
      <div class="fr"><label>Nom / Logo texte</label>
        <input type="text" name="logo_text" value="<?=htmlspecialchars($footer['logo_text']??'')?>" placeholder="Eduardo Desul">
      </div>
      <div class="fr"><label>Tagline / Description</label>
        <input type="text" name="logo_tagline" value="<?=htmlspecialchars($footer['logo_tagline']??'')?>" placeholder="Conseiller immobilier indépendant">
      </div>
      <div class="sr">Réseaux sociaux</div>
      <div class="fr row"><label>Afficher icônes sociales</label>
        <label class="tog"><input type="checkbox" name="social_enabled" <?=$footer['social_enabled']?'checked':''?>><span class="tog-sl"></span></label>
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

    <!-- COLONNES -->
    <div class="sp" id="panel-colonnes">
      <?php foreach([1=>[$col1Links,'col1'],2=>[$col2Links,'col2'],3=>[$col3Links,'col3']] as $num=>[$links,$prefix]): ?>
      <div class="col-section">
        <h4>Colonne <?=$num?></h4>
        <div class="fr"><label>Titre</label>
          <input type="text" name="<?=$prefix?>_title" value="<?=htmlspecialchars($footer[$prefix.'_title']??'')?>" placeholder="Navigation">
        </div>
        <ul class="mlist" id="<?=$prefix?>List">
          <?php foreach($links as $item): ?>
          <li class="mi" draggable="true">
            <span class="dh">⠿</span>
            <input type="text" class="mi-l" value="<?=htmlspecialchars($item['label']??'')?>" placeholder="Libellé">
            <input type="text" class="mi-u" value="<?=htmlspecialchars($item['url']??'')?>"   placeholder="/url">
            <button type="button" class="btn-del" onclick="delMI(this)">✕</button>
          </li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-add" onclick="addMI('<?=$prefix?>')">+ Lien</button>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- CONTACT -->
    <div class="sp" id="panel-contact">
      <div class="fr"><label>📞 Téléphone</label>
        <input type="text" name="phone" value="<?=htmlspecialchars($footer['phone']??'')?>" placeholder="06 24 10 58 16">
      </div>
      <div class="fr"><label>✉️ Email</label>
        <input type="email" name="email" value="<?=htmlspecialchars($footer['email']??'')?>" placeholder="contact@...">
      </div>
      <div class="fr"><label>📍 Adresse</label>
        <input type="text" name="address" value="<?=htmlspecialchars($footer['address']??'')?>" placeholder="12A rue...">
      </div>
      <div class="fr row"><label>🏛️ Afficher n° CPI</label>
        <label class="tog"><input type="checkbox" name="show_cpi" <?=$footer['show_cpi']?'checked':''?>><span class="tog-sl"></span></label>
      </div>
      <div class="fr"><label>N° Carte pro (CPI)</label>
        <input type="text" name="cpi_number" value="<?=htmlspecialchars($footer['cpi_number']??'')?>" placeholder="CPI 7501 2021 000 000 444">
      </div>
    </div>

    <!-- BAS DE PAGE -->
    <div class="sp" id="panel-bas">
      <div class="fr"><label>Copyright</label>
        <input type="text" name="copyright_text" value="<?=htmlspecialchars($footer['copyright_text']??'')?>" placeholder="© 2025 Eduardo Desul">
      </div>
      <div class="sr">Liens légaux</div>
      <div id="legalList">
        <?php foreach($legalLinks as $ll): ?>
        <div class="si">
          <input type="text" class="ll-l" value="<?=htmlspecialchars($ll['label']??'')?>" placeholder="Mentions légales" style="grid-column:1/3">
          <input type="text" class="ll-u" value="<?=htmlspecialchars($ll['url']??'')?>"   placeholder="/mentions-legales">
          <button type="button" class="btn-del" onclick="this.closest('.si').remove();buildLG()">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn-add" onclick="addLegal()">+ Lien légal</button>
    </div>

    <!-- CODE -->
    <div class="sp" id="panel-code">
      <div style="background:#fff3cd;border:1px solid #fbbf24;border-radius:8px;padding:10px;margin-bottom:14px;font-size:12px">⚠️ <strong>Mode avancé.</strong> Le HTML custom remplace entièrement le rendu automatique.</div>
      <div class="fr"><label>HTML personnalisé</label>
        <textarea name="custom_html" class="code-ta" rows="8" placeholder="<!-- HTML complet du footer -->"><?=htmlspecialchars($footer['custom_html']??'')?></textarea>
      </div>
      <div class="fr"><label>CSS additionnel</label>
        <textarea name="custom_css" class="code-ta" rows="5" placeholder="/* CSS */"><?=htmlspecialchars($footer['custom_css']??'')?></textarea>
      </div>
      <div class="fr"><label>JavaScript additionnel</label>
        <textarea name="custom_js" class="code-ta" rows="4" placeholder="// JS"><?=htmlspecialchars($footer['custom_js']??'')?></textarea>
      </div>
      <button type="button" onclick="clearCode()" style="padding:7px 14px;background:#fee2e2;color:#991b1b;border:none;border-radius:8px;font-size:12px;cursor:pointer;margin-top:8px">🗑️ Vider le code custom</button>
    </div>

  </aside>

  <!-- PREVIEW -->
  <div class="ftr-pv">
    <div class="pv-bar">
      <span>👁 Aperçu en direct</span>
      <button type="button" class="dev-btn on" onclick="setDev('desktop',this)">🖥 Desktop</button>
      <button type="button" class="dev-btn"    onclick="setDev('tablet',this)">📱 Tablette</button>
      <button type="button" class="dev-btn"    onclick="setDev('mobile',this)">📲 Mobile</button>
      <button type="button" style="padding:5px 12px;background:var(--A);color:#fff;border:none;border-radius:7px;cursor:pointer;font-size:12px;font-weight:700" onclick="refreshPV()">↻</button>
    </div>
    <div class="pv-wrap" id="pvwrap">
      <iframe id="pvframe" srcdoc="<?=htmlspecialchars($pvHtml)?>" scrolling="no"></iframe>
      <div class="pv-info" id="pvinfo">Desktop — 100%</div>
    </div>
  </div>

</div>
</form>

<script>
// TABS
document.querySelectorAll('.stab').forEach(t=>t.addEventListener('click',()=>{
  document.querySelectorAll('.stab,.sp').forEach(e=>e.classList.remove('on'));
  t.classList.add('on');document.getElementById('panel-'+t.dataset.p).classList.add('on');
}));

// MENU LISTS (col1, col2, col3)
let dragSrc=null;
function initDnD(){
  document.querySelectorAll('.mi').forEach(el=>{
    el.addEventListener('dragstart',e=>{dragSrc=el;el.style.opacity='.4';e.dataTransfer.effectAllowed='move';});
    el.addEventListener('dragend',()=>{dragSrc.style.opacity='1';buildAllCols();});
    el.addEventListener('dragover',e=>{e.preventDefault();if(dragSrc!==el){const mid=el.getBoundingClientRect().top+el.offsetHeight/2;el.parentNode.insertBefore(dragSrc,e.clientY<mid?el:el.nextSibling);}});
  });
}
initDnD();
function addMI(prefix){
  const li=document.createElement('li');li.className='mi';li.draggable=true;
  li.innerHTML='<span class="dh">⠿</span><input type="text" class="mi-l" placeholder="Libellé"><input type="text" class="mi-u" placeholder="/url"><button type="button" class="btn-del" onclick="delMI(this)">✕</button>';
  document.getElementById(prefix+'List').appendChild(li);initDnD();li.querySelector('.mi-l').focus();
}
function delMI(b){b.closest('.mi').remove();buildAllCols();}
function buildCol(prefix,jsonId){
  const items=[...document.querySelectorAll('#'+prefix+'List .mi')].map(li=>({label:li.querySelector('.mi-l').value.trim(),url:li.querySelector('.mi-u').value.trim()})).filter(i=>i.label);
  document.getElementById(jsonId).value=JSON.stringify(items);
  return items;
}
function buildAllCols(){buildCol('col1','c1json');buildCol('col2','c2json');buildCol('col3','c3json');}
document.getElementById('ftrForm').addEventListener('input',()=>{buildAllCols();sched();});

// SOCIAL
const SNETS={facebook:'Facebook',instagram:'Instagram',linkedin:'LinkedIn',youtube:'YouTube',tiktok:'TikTok',twitter:'Twitter/X'};
function addSoc(){
  const d=document.createElement('div');d.className='si';
  d.innerHTML='<select class="sn">'+Object.entries(SNETS).map(([v,l])=>`<option value="${v}">${l}</option>`).join('')+'</select><input type="text" class="su" placeholder="https://..."><button type="button" class="btn-del" onclick="this.closest(\'.si\').remove();buildSJ()">✕</button>';
  document.getElementById('socialList').appendChild(d);
  d.querySelector('.su').addEventListener('input',buildSJ);d.querySelector('.sn').addEventListener('change',buildSJ);
}
function buildSJ(){
  const items=[...document.querySelectorAll('#socialList .si')].map(d=>({network:d.querySelector('.sn').value,url:d.querySelector('.su').value}));
  document.getElementById('sljson').value=JSON.stringify(items);
}

// LIENS LEGAUX
function addLegal(){
  const d=document.createElement('div');d.className='si';
  d.style.gridTemplateColumns='1fr 1fr 30px';
  d.innerHTML='<input type="text" class="ll-l" placeholder="Mentions légales"><input type="text" class="ll-u" placeholder="/mentions-legales"><button type="button" class="btn-del" onclick="this.closest(\'.si\').remove();buildLG()">✕</button>';
  document.getElementById('legalList').appendChild(d);
  d.querySelector('.ll-l').addEventListener('input',buildLG);d.querySelector('.ll-u').addEventListener('input',buildLG);
}
function buildLG(){
  const items=[...document.querySelectorAll('#legalList .si')].map(d=>({label:d.querySelector('.ll-l').value,url:d.querySelector('.ll-u').value}));
  document.getElementById('lgjson').value=JSON.stringify(items);
}

// COULEURS
function syncC(id,v){document.getElementById(id).value=v;sched();}
function syncP(id,v){if(/^#[0-9a-f]{3,6}$/i.test(v)){document.getElementById(id).value=v;sched();}}
function applyP(bg,tc,ac){
  ['bg_color','text_color','accent_color'].forEach((f,i)=>{const v=[bg,tc,ac][i];document.getElementById(f).value=v;document.getElementById(f+'_p').value=v;});refreshPV();
}

// CODE
function clearCode(){if(!confirm('Vider le code custom ?'))return;['custom_html','custom_css','custom_js'].forEach(n=>{const e=document.querySelector('[name='+n+']');if(e)e.value='';});refreshPV();}

// LIVE PREVIEW
function gv(n){const e=document.querySelector('[name="'+n+'"]');if(!e)return'';return e.type==='checkbox'?e.checked:e.value;}
function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

function buildPV(){
  const bg=gv('bg_color')||'#1a2e4a',tc=gv('text_color')||'#cbd5e1',ac=gv('accent_color')||'#d4a574';
  const ch=gv('custom_html');
  if(ch&&ch.trim())return`<!DOCTYPE html><html><head><meta charset="utf-8"><style>*{margin:0;padding:0;box-sizing:border-box}${gv('custom_css')}</style></head><body>${ch}</body></html>`;

  const cols=parseInt(document.getElementById('nb-cols').value||3);
  const gridCols=cols===4?'1fr 1fr 1fr 1fr':cols===3?'2fr 1fr 1fr':cols===2?'1fr 1fr':'1fr';

  const c1=JSON.parse(document.getElementById('c1json').value||'[]');
  const c2=JSON.parse(document.getElementById('c2json').value||'[]');
  const c3=JSON.parse(document.getElementById('c3json').value||'[]');
  const soc=JSON.parse(document.getElementById('sljson').value||'[]');
  const leg=JSON.parse(document.getElementById('lgjson').value||'[]');

  const logo=`<div style="margin-bottom:8px"><span style="font-weight:800;font-size:22px;color:${ac};font-family:'Playfair Display',Georgia,serif">${esc(gv('logo_text')||'Logo')}</span></div>`;
  const tag=`<div style="font-size:13px;color:${tc};opacity:.8;margin-bottom:14px">${esc(gv('logo_tagline')||'')}</div>`;
  let contact='';
  if(gv('phone'))contact+=`<div style="font-size:13px;color:${tc};margin-bottom:5px">📞 ${esc(gv('phone'))}</div>`;
  if(gv('email'))contact+=`<div style="font-size:13px;color:${tc};margin-bottom:5px">✉️ ${esc(gv('email'))}</div>`;
  if(gv('address'))contact+=`<div style="font-size:12px;color:${tc};opacity:.7">📍 ${esc(gv('address'))}</div>`;
  if(gv('show_cpi')&&gv('cpi_number'))contact+=`<div style="font-size:11px;color:${ac};margin-top:8px">🏛️ ${esc(gv('cpi_number'))}</div>`;

  const socSVG={facebook:'<path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>',instagram:'<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.5"/>',linkedin:'<path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',youtube:'<path d="M22.54 6.42A2.78 2.78 0 0020.59 4.46C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.97C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="'+bg+'"/>',tiktok:'<path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.34 6.34 0 106.33 6.34V8.69a8.15 8.15 0 004.77 1.52V6.76a4.85 4.85 0 01-1-.07z"/>',twitter:'<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>'};
  let socHtml='';
  if(gv('social_enabled')){soc.forEach(s=>{const svg=socSVG[s.network]||'';socHtml+=`<a href="${esc(s.url||'#')}" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.1);color:${tc};text-decoration:none;margin-right:8px"><svg width="18" height="18" viewBox="0 0 24 24" fill="${tc}" stroke="${tc}" stroke-width="0">${svg}</svg></a>`;});}

  const mkCols=()=>{
    const mkLinkCol=(links,title)=>{if(!links.length&&!title)return'';return`<div><div style="font-weight:700;font-size:13px;color:${ac};margin-bottom:10px;text-transform:uppercase;letter-spacing:.06em">${esc(title)}</div>${links.map(l=>`<div style="margin-bottom:6px"><a href="${esc(l.url||'#')}" style="color:${tc};text-decoration:none;font-size:13px;opacity:.85">${esc(l.label||'')}</a></div>`).join('')}</div>`;};
    let html=`<div style="display:grid;grid-template-columns:${gridCols};gap:32px;padding:40px 0 30px">`;
    html+=`<div>${logo}${tag}${contact}${socHtml?`<div style="margin-top:14px">${socHtml}</div>`:''}</div>`;
    html+=mkLinkCol(c1,document.querySelector('[name=col1_title]')?.value||'');
    html+=mkLinkCol(c2,document.querySelector('[name=col2_title]')?.value||'');
    if(cols>=3)html+=mkLinkCol(c3,document.querySelector('[name=col3_title]')?.value||'');
    html+='</div>';
    return html;
  };

  const legHtml=leg.map(l=>`<a href="${esc(l.url||'#')}" style="color:${tc};text-decoration:none;font-size:12px;opacity:.7">${esc(l.label||'')}</a>`).join(' · ');
  const bottom=`<div style="border-top:1px solid rgba(255,255,255,.1);padding:18px 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px"><div style="font-size:12px;color:${tc};opacity:.6">${esc(gv('copyright_text')||'')}</div><div>${legHtml}</div></div>`;

  return`<!DOCTYPE html><html><head><meta charset="utf-8"><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',sans-serif}</style></head><body><footer style="background:${bg};padding:0 40px">${mkCols()}${bottom}</footer></body></html>`;
}

function refreshPV(){buildAllCols();buildSJ();buildLG();document.getElementById('pvframe').srcdoc=buildPV();}
document.getElementById('pvframe').addEventListener('load',function(){
  try{const h=this.contentDocument?.body?.scrollHeight;if(h>0)this.style.height=(h+4)+'px';}catch(e){}
});

let t;function sched(){clearTimeout(t);t=setTimeout(refreshPV,350);}
document.getElementById('ftrForm').addEventListener('change',()=>setTimeout(refreshPV,100));
document.getElementById('inp-name')?.addEventListener('input',function(){
  const el=document.getElementById('ttl');if(el)el.childNodes[0].textContent=this.value||'Nouveau Footer';
});
document.getElementById('nb-cols')?.addEventListener('change',refreshPV);

function setDev(d,btn){
  document.querySelectorAll('.dev-btn').forEach(b=>b.classList.remove('on'));btn.classList.add('on');
  const w=document.getElementById('pvwrap'),i=document.getElementById('pvinfo');
  if(d==='mobile'){w.className='pv-wrap mobile';i.textContent='Mobile — 375px';}
  else if(d==='tablet'){w.className='pv-wrap tablet';i.textContent='Tablette — 768px';}
  else{w.className='pv-wrap';i.textContent='Desktop — 100%';}
}

refreshPV();
</script>