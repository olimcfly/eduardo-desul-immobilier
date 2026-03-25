<?php
/**
 * BUILDER PRO v3.9 — editor.php
 * Layout : Preview prioritaire (gauche) + Code secondaire (droite)
 * + Clone Design via Claude AI
 * + Panel Données DB contextuel (articles/secteurs/guides/captures/pages)
 */

define('ADMIN_ROUTER', true);

$_initPath = dirname(__DIR__, 3) . '/includes/init.php';
if (!file_exists($_initPath)) $_initPath = $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/init.php';
require_once $_initPath;

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$_isAuth = !empty($_SESSION['admin_logged_in'])
        || !empty($_SESSION['user_id'])
        || !empty($_SESSION['admin_id'])
        || !empty($_SESSION['logged_in'])
        || !empty($_SESSION['is_admin']);

if (!$_isAuth) {
    if (headers_sent()) { echo '<script>window.location="/admin/login.php";</script>'; exit; }
    header('Location: /admin/login.php'); exit;
}

$context  = trim($_GET['context']   ?? 'page');
$entityId = (int)($_GET['entity_id'] ?? ($_GET['id'] ?? 0));

$dynamicContexts = ['article', 'secteur', 'guide', 'capture'];
$isDynamic = in_array($context, $dynamicContexts);

$CTX = [
    'page'    => ['table'=>'pages',        'col_title'=>'title', 'col_content'=>'content',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status','col_php'=>null,         'list'=>'dashboard.php?page=pages'],
    'secteur' => ['table'=>'secteurs',     'col_title'=>'nom',   'col_content'=>'content',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status','col_php'=>'php_content','list'=>'dashboard.php?page=secteurs'],
    'article' => ['table'=>'articles',     'col_title'=>'title', 'col_content'=>'content',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status','col_php'=>'php_content','list'=>'dashboard.php?page=articles'],
    'guide'   => ['table'=>'guide_local',  'col_title'=>'titre', 'col_content'=>'contenu',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'statut','col_php'=>'php_content','list'=>'dashboard.php?page=guide-local'],
    'header'  => ['table'=>'headers',      'col_title'=>'name',  'col_content'=>'custom_html','col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'name','col_status'=>'status','col_php'=>null,         'list'=>'dashboard.php?page=builder&sub=headers'],
    'footer'  => ['table'=>'footers',      'col_title'=>'name',  'col_content'=>'custom_html','col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'name','col_status'=>'status','col_php'=>null,         'list'=>'dashboard.php?page=builder&sub=footers'],
    'capture' => ['table'=>'captures','col_title'=>'titre',  'col_content'=>'contenu',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status','col_php'=>'php_content','list'=>'dashboard.php?page=captures'],
];

if (!isset($CTX[$context])) $context = 'page';
$C = $CTX[$context];

$entity  = null; $errMsg = '';
if ($entityId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$C['table']}` WHERE id = ? LIMIT 1");
        $stmt->execute([$entityId]);
        $entity = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$entity) $errMsg = "Entité #$entityId introuvable.";
    } catch (Exception $e) { $errMsg = $e->getMessage(); }
}

$title     = htmlspecialchars($entity[$C['col_title']]   ?? 'Nouvelle page', ENT_QUOTES);
$content   = $entity[$C['col_content']] ?? '';
$customCss = $entity[$C['col_css']]     ?? '';
$customJs  = $entity[$C['col_js']]      ?? '';
$customPhp = ($C['col_php'] && isset($entity[$C['col_php']])) ? ($entity[$C['col_php']] ?? '') : '';
$slug      = htmlspecialchars($entity[$C['col_slug']]    ?? '', ENT_QUOTES);
$status    = $entity[$C['col_status']]  ?? 'draft';
$metaTitle = htmlspecialchars($entity['meta_title']       ?? '', ENT_QUOTES);
$metaDesc  = htmlspecialchars($entity['meta_description'] ?? '', ENT_QUOTES);

$ctxLabels = ['page'=>'Page','secteur'=>'Secteur','article'=>'Article','guide'=>'Guide Local','header'=>'Header','footer'=>'Footer','capture'=>'Page Capture'];
$ctxLabel  = $ctxLabels[$context] ?? 'Page';

$previewUrls = ['page'=>"/front/page.php?preview=1&slug={$slug}",'secteur'=>"/{$slug}",'article'=>"/blog/{$slug}",'header'=>'','footer'=>'','guide'=>"/guide/{$slug}",'capture'=>"/capture/{$slug}"];
$frontUrl = $previewUrls[$context] ?? '';
$aiProxyUrl = '/admin/api/ai/generate.php';

// ── Connecteur ────────────────────────────────────────────────────────────────
$connectorData = []; $connectorLabel = ''; $connectorIcon = ''; $connectorEditBase = '';
if ($isDynamic) {
    try {
        switch ($context) {
            case 'article':
                $rs = $pdo->query("SELECT id, title as name, slug, status FROM articles ORDER BY created_at DESC LIMIT 60");
                $connectorData=$rs?$rs->fetchAll(PDO::FETCH_ASSOC):[];
                $connectorLabel='Articles';$connectorIcon='fa-newspaper';$connectorEditBase='/admin/modules/content/articles/edit.php?id='; break;
            case 'secteur':
                $rs = $pdo->query("SELECT id, nom as name, slug, status FROM secteurs ORDER BY nom LIMIT 60");
                $connectorData=$rs?$rs->fetchAll(PDO::FETCH_ASSOC):[];
                $connectorLabel='Secteurs';$connectorIcon='fa-map-marker-alt';$connectorEditBase='/admin/modules/content/secteurs/edit.php?id='; break;
            case 'guide':
                $rs = $pdo->query("SELECT id, titre as name, slug, statut as status FROM guide_local ORDER BY titre LIMIT 60");
                $connectorData=$rs?$rs->fetchAll(PDO::FETCH_ASSOC):[];
                $connectorLabel='Guides';$connectorIcon='fa-book';$connectorEditBase='/admin/modules/content/guide-local/edit.php?id='; break;
            case 'capture':
                $rs = $pdo->query("SELECT id, titre as name, slug, status FROM captures ORDER BY created_at DESC LIMIT 60");
                $connectorData=$rs?$rs->fetchAll(PDO::FETCH_ASSOC):[];
                $connectorLabel='Ressources';$connectorIcon='fa-magnet';$connectorEditBase='/admin/modules/content/pages-capture/edit.php?id='; break;
        }
    } catch (Exception $e) {}
}

// ── Identité site ─────────────────────────────────────────────────────────────
$siteIdentity = [];
try {
    $rs = $pdo->query("SELECT setting_key,setting_value FROM settings WHERE setting_key IN ('advisor_name','advisor_city','advisor_phone','advisor_email','primary_color','site_name') LIMIT 10");
    if ($rs) foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $r) $siteIdentity[$r['setting_key']]=$r['setting_value'];
} catch (Exception $e) {}

// ── Données DB selon contexte (pour panel DB) ─────────────────────────────────
$dbData = []; $dbStats = [];
try {
    switch ($context) {
        case 'article':
            $r=$pdo->query("SELECT COUNT(*) FROM articles"); $r2=$pdo->query("SELECT COUNT(*) FROM articles WHERE status='published'");
            $dbStats=['Total articles'=>$r?(int)$r->fetchColumn():0,'Articles publiés'=>$r2?(int)$r2->fetchColumn():0];
            try{$r3=$pdo->query("SELECT COUNT(DISTINCT category) FROM articles WHERE category IS NOT NULL AND category!=''");if($r3)$dbStats['Catégories']=(int)$r3->fetchColumn();}catch(Exception $e){}
            $dbData=['vars'=>[
                ['key'=>'{{article.title}}','desc'=>"Titre de l'article"],['key'=>'{{article.content}}','desc'=>'Contenu HTML'],
                ['key'=>'{{article.slug}}','desc'=>'Slug URL'],['key'=>'{{article.author}}','desc'=>'Auteur'],
                ['key'=>'{{article.created_at}}','desc'=>'Date publication'],['key'=>'{{article.category}}','desc'=>'Catégorie'],
                ['key'=>'{{article.tags}}','desc'=>'Tags'],['key'=>'{{article.image}}','desc'=>'Image principale'],
                ['key'=>'{{article.meta_title}}','desc'=>'Titre SEO'],['key'=>'{{article.meta_desc}}','desc'=>'Description SEO'],
                ['key'=>'{{article.read_time}}','desc'=>'Temps de lecture'],['key'=>'{{articles.count}}','desc'=>'Nb total articles'],
            ],'loop'=>'<?php foreach($articles as $a): ?>'."\n".'<article class="blog-card">'."\n".'  <a href="/blog/<?= $a[\'slug\'] ?>">'."\n".'    <h2><?= htmlspecialchars($a[\'title\']) ?></h2>'."\n".'    <p><?= substr(strip_tags($a[\'content\']),0,150) ?>...</p>'."\n".'    <time><?= date(\'d/m/Y\',strtotime($a[\'created_at\'])) ?></time>'."\n".'  </a>'."\n".'</article>'."\n".'<?php endforeach; ?>','table'=>'articles'];
            break;
        case 'secteur':
            $r=$pdo->query("SELECT COUNT(*) FROM secteurs"); $r2=$pdo->query("SELECT COUNT(*) FROM secteurs WHERE status='published'");
            $dbStats=['Total secteurs'=>$r?(int)$r->fetchColumn():0,'Secteurs publiés'=>$r2?(int)$r2->fetchColumn():0];
            try{$r3=$pdo->query("SELECT COUNT(DISTINCT ville) FROM secteurs WHERE ville IS NOT NULL AND ville!=''");if($r3)$dbStats['Villes']=(int)$r3->fetchColumn();}catch(Exception $e){}
            $dbData=['vars'=>[
                ['key'=>'{{secteur.nom}}','desc'=>'Nom du secteur'],['key'=>'{{secteur.content}}','desc'=>'Contenu HTML'],
                ['key'=>'{{secteur.description}}','desc'=>'Description courte'],['key'=>'{{secteur.slug}}','desc'=>'Slug URL'],
                ['key'=>'{{secteur.ville}}','desc'=>'Ville'],['key'=>'{{secteur.code_postal}}','desc'=>'Code postal'],
                ['key'=>'{{secteur.prix_moyen}}','desc'=>'Prix moyen m²'],['key'=>'{{secteur.nb_biens}}','desc'=>'Nb biens dispo'],
                ['key'=>'{{secteur.image}}','desc'=>'Image du secteur'],['key'=>'{{secteur.meta_title}}','desc'=>'Titre SEO'],
                ['key'=>'{{secteur.transport}}','desc'=>'Transports'],['key'=>'{{secteurs.count}}','desc'=>'Nb total secteurs'],
            ],'loop'=>'<?php foreach($secteurs as $s): ?>'."\n".'<div class="secteur-card">'."\n".'  <a href="/<?= $s[\'slug\'] ?>">'."\n".'    <h3><?= htmlspecialchars($s[\'nom\']) ?></h3>'."\n".'    <?php if(!empty($s[\'prix_moyen\'])): ?>'."\n".'    <span>~<?= number_format($s[\'prix_moyen\'],0,\',\',\' \') ?> €/m²</span>'."\n".'    <?php endif; ?>'."\n".'  </a>'."\n".'</div>'."\n".'<?php endforeach; ?>','table'=>'secteurs'];
            break;
        case 'guide':
            $r=$pdo->query("SELECT COUNT(*) FROM guide_local"); $r2=$pdo->query("SELECT COUNT(*) FROM guide_local WHERE statut='published'");
            $dbStats=['Total guides'=>$r?(int)$r->fetchColumn():0,'Guides publiés'=>$r2?(int)$r2->fetchColumn():0];
            $dbData=['vars'=>[
                ['key'=>'{{guide.titre}}','desc'=>'Titre du guide'],['key'=>'{{guide.contenu}}','desc'=>'Contenu HTML'],
                ['key'=>'{{guide.slug}}','desc'=>'Slug URL'],['key'=>'{{guide.ville}}','desc'=>'Ville/zone'],
                ['key'=>'{{guide.categorie}}','desc'=>'Catégorie'],['key'=>'{{guide.image}}','desc'=>'Image couverture'],
                ['key'=>'{{guide.auteur}}','desc'=>'Auteur'],['key'=>'{{guide.date}}','desc'=>'Date publication'],
                ['key'=>'{{guide.meta_title}}','desc'=>'Titre SEO'],['key'=>'{{guide.meta_desc}}','desc'=>'Description SEO'],
                ['key'=>'{{guides.count}}','desc'=>'Nb total guides'],
            ],'loop'=>'<?php foreach($guides as $g): ?>'."\n".'<div class="guide-card">'."\n".'  <a href="/guide/<?= $g[\'slug\'] ?>">'."\n".'    <h3><?= htmlspecialchars($g[\'titre\']) ?></h3>'."\n".'    <p><?= htmlspecialchars($g[\'ville\']??\'\')?></p>'."\n".'  </a>'."\n".'</div>'."\n".'<?php endforeach; ?>','table'=>'guide_local'];
            break;
        case 'capture':
            $r=$pdo->query("SELECT COUNT(*) FROM captures"); $dbStats=['Pages capture'=>$r?(int)$r->fetchColumn():0];
            try{$r2=$pdo->query("SELECT COUNT(*) FROM leads");$dbStats['Total leads']=$r2?(int)$r2->fetchColumn():0;
                $r3=$pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at)=CURDATE()");$dbStats["Leads auj."]=$r3?(int)$r3->fetchColumn():0;}catch(Exception $e){}
            $dbData=['vars'=>[
                ['key'=>'{{capture.name}}','desc'=>'Nom page capture'],['key'=>'{{capture.slug}}','desc'=>'Slug URL'],
                ['key'=>'{{capture.headline}}','desc'=>'Titre accroche'],['key'=>'{{capture.cta_text}}','desc'=>'Texte CTA'],
                ['key'=>'{{lead.nom}}','desc'=>'Nom du lead'],['key'=>'{{lead.email}}','desc'=>'Email lead'],
                ['key'=>'{{lead.telephone}}','desc'=>'Téléphone lead'],['key'=>'{{lead.source}}','desc'=>'Source lead'],
                ['key'=>'{{leads.count}}','desc'=>'Nb total leads'],
            ],'loop'=>'<?php foreach($leads as $l): ?>'."\n".'<tr>'."\n".'  <td><?= htmlspecialchars($l[\'nom\']??\'\')?></td>'."\n".'  <td><?= htmlspecialchars($l[\'email\']??\'\')?></td>'."\n".'  <td><?= date(\'d/m/Y\',strtotime($l[\'created_at\']))?></td>'."\n".'</tr>'."\n".'<?php endforeach; ?>','table'=>'capture_pages + leads'];
            break;
        default:
            $r=$pdo->query("SELECT COUNT(*) FROM pages"); $r2=$pdo->query("SELECT COUNT(*) FROM pages WHERE status='published'");
            $dbStats=['Total pages'=>$r?(int)$r->fetchColumn():0,'Pages publiées'=>$r2?(int)$r2->fetchColumn():0];
            $dbData=['vars'=>[
                ['key'=>'{{page.title}}','desc'=>'Titre de la page'],['key'=>'{{page.content}}','desc'=>'Contenu HTML'],
                ['key'=>'{{page.slug}}','desc'=>'Slug URL'],['key'=>'{{page.meta_title}}','desc'=>'Titre SEO'],
                ['key'=>'{{site.name}}','desc'=>'Nom du site'],['key'=>'{{site.phone}}','desc'=>'Téléphone'],
                ['key'=>'{{site.email}}','desc'=>'Email contact'],['key'=>'{{advisor.name}}','desc'=>'Nom conseiller'],
                ['key'=>'{{advisor.city}}','desc'=>'Ville conseiller'],['key'=>'{{year}}','desc'=>'Année courante'],
            ],'table'=>'pages'];
            break;
    }
} catch (Exception $e) { $dbData['error']=$e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Builder Pro v3.9 — <?= $title ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#f1f5f9;--bg2:#fff;--bg3:#f8fafc;--bg4:#e2e8f0;
  --border:#e2e8f0;--border2:#cbd5e1;
  --text:#1e293b;--text2:#475569;--text3:#94a3b8;
  --accent:#1a4d7a;--accent2:#2563a8;
  --green:#16a34a;--yellow:#d97706;--red:#dc2626;--purple:#7c3aed;
  --radius:8px;
  --sidebar-w:52px;
  --tools-w:300px;
  --topbar-h:50px;
  --tab-h:38px;
  --font:'Segoe UI',system-ui,sans-serif;
  --font-mono:'Cascadia Code','Fira Code','Consolas',monospace;
}
html,body{height:100%;overflow:hidden;background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px}
#app{display:flex;flex-direction:column;height:100vh}
/* TOPBAR */
#topbar{height:var(--topbar-h);min-height:var(--topbar-h);background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;padding:0 12px;flex-shrink:0;z-index:100}
.tb-back{display:flex;align-items:center;gap:6px;color:var(--text2);text-decoration:none;font-size:12px;padding:6px 10px;border-radius:var(--radius);transition:all .15s}
.tb-back:hover{background:var(--bg3);color:var(--text)}
.tb-sep{width:1px;height:24px;background:var(--border);flex-shrink:0}
.tb-title{display:flex;align-items:center;gap:8px;flex:1;min-width:0}
.tb-badge{font-size:10px;padding:2px 8px;border-radius:20px;font-weight:600;letter-spacing:.5px;text-transform:uppercase}
.tb-badge.page{background:rgba(26,77,122,.1);color:#1a4d7a;border:1px solid rgba(26,77,122,.25)}
.tb-badge.secteur{background:rgba(34,197,94,.2);color:#16a34a;border:1px solid rgba(34,197,94,.3)}
.tb-badge.article{background:rgba(245,158,11,.2);color:#d97706;border:1px solid rgba(245,158,11,.3)}
.tb-badge.guide{background:rgba(168,85,247,.2);color:#7c3aed;border:1px solid rgba(168,85,247,.3)}
.tb-badge.header{background:rgba(239,68,68,.2);color:#dc2626;border:1px solid rgba(239,68,68,.3)}
.tb-badge.footer{background:rgba(249,115,22,.2);color:#ea580c;border:1px solid rgba(249,115,22,.3)}
.tb-badge.capture{background:rgba(6,182,212,.2);color:#0891b2;border:1px solid rgba(6,182,212,.3)}
.tb-name{font-weight:600;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#tb-status-badge{font-size:11px;padding:2px 8px;border-radius:20px;cursor:pointer;transition:all .2s}
#tb-status-badge.published{background:rgba(34,197,94,.2);color:#16a34a;border:1px solid rgba(34,197,94,.4)}
#tb-status-badge.draft{background:rgba(245,158,11,.2);color:#d97706;border:1px solid rgba(245,158,11,.4)}
.tb-actions{display:flex;align-items:center;gap:6px;flex-shrink:0}
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--radius);font-size:12px;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all .15s;text-decoration:none;white-space:nowrap}
.btn-ghost{background:transparent;color:var(--text2);border:1px solid var(--border2)}.btn-ghost:hover{background:var(--bg3);color:var(--text)}
.btn-primary{background:var(--accent);color:#fff}.btn-primary:hover{background:var(--accent2)}
.btn-success{background:var(--green);color:#fff}.btn-success:hover{filter:brightness(1.1)}
.btn-sm{padding:5px 10px;font-size:11px}.btn-icon{padding:7px}
#save-status{font-size:11px;color:var(--text3)}
/* MAIN */
#main{display:flex;flex:1;overflow:hidden}
/* SIDEBAR */
#sidebar{width:var(--sidebar-w);background:#fff;border-right:1px solid var(--border);display:flex;flex-direction:column;align-items:center;padding:8px 0;gap:2px;flex-shrink:0;z-index:50;order:1}
.sb-btn{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:var(--radius);cursor:pointer;color:var(--text3);border:none;background:transparent;font-size:15px;transition:all .15s;position:relative;flex-shrink:0}
.sb-btn:hover{background:var(--bg);color:var(--text2)}.sb-btn.active{background:rgba(26,77,122,.12);color:var(--accent)}
.sb-btn .tooltip{position:absolute;left:calc(100% + 10px);top:50%;transform:translateY(-50%);background:#1e293b;color:#f8fafc;font-size:11px;padding:4px 8px;border-radius:4px;white-space:nowrap;pointer-events:none;opacity:0;transition:opacity .15s;border:1px solid #334155;z-index:999}
.sb-btn:hover .tooltip{opacity:1}
.sb-divider{width:28px;height:1px;background:var(--border);margin:4px 0}
.sb-badge-count{position:absolute;top:-2px;right:-2px;background:var(--accent);color:#fff;border-radius:8px;font-size:9px;font-weight:700;min-width:14px;height:14px;display:flex;align-items:center;justify-content:center;padding:0 2px}
/* PREVIEW */
#preview-zone{flex:1;display:flex;flex-direction:column;overflow:hidden;order:2;background:#e2e8f0;min-width:0}
.preview-topbar{height:40px;min-height:40px;background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px;padding:0 14px;flex-shrink:0}
.preview-topbar-title{font-size:12px;font-weight:600;color:var(--text2);margin-right:auto;display:flex;align-items:center;gap:6px}
.dev-btn{padding:4px 10px;border-radius:20px;border:1px solid var(--border2);background:transparent;color:var(--text3);font-size:11px;cursor:pointer;font-family:inherit;transition:all .15s}
.dev-btn.active,.dev-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
.preview-area{flex:1;overflow:auto;padding:16px;display:flex;align-items:flex-start;justify-content:center}
.preview-wrap{background:#fff;border-radius:var(--radius);overflow:hidden;box-shadow:0 4px 32px rgba(0,0,0,.25);transition:all .3s;width:100%;max-width:100%}
.preview-wrap.tablet{max-width:768px;width:768px}
.preview-wrap.mobile{max-width:390px;width:390px}
#preview-iframe{width:100%;border:none;display:block;min-height:400px}
.preview-bottombar{height:30px;background:#fff;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px;padding:0 14px;flex-shrink:0}
.preview-url{flex:1;font-size:10px;color:var(--text3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.preview-bottombar a{color:var(--accent2);font-size:11px;text-decoration:none}
/* CODE ZONE */
#code-zone{width:38%;min-width:280px;max-width:600px;display:flex;flex-direction:column;overflow:hidden;border-left:1px solid var(--border);order:3;transition:width .25s ease}
#code-zone.collapsed{width:0;min-width:0;border:none;overflow:hidden}
#editor-tabs{height:var(--tab-h);min-height:var(--tab-h);background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 8px;gap:2px;flex-shrink:0;overflow-x:auto}
#editor-tabs::-webkit-scrollbar{height:0}
.etab{display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px 6px 0 0;cursor:pointer;font-size:12px;color:var(--text3);border:none;background:transparent;font-family:inherit;white-space:nowrap;border-bottom:2px solid transparent;transition:all .15s}
.etab:hover{color:var(--text2);background:var(--bg3)}.etab.active{color:var(--text);border-bottom-color:var(--accent);background:var(--bg3)}
.etab .lang-dot{width:6px;height:6px;border-radius:50%}
.dot-html{background:#f97316}.dot-css{background:#3b82f6}.dot-js{background:#f59e0b}.dot-php{background:#8892be}
.code-zone-toggle{margin-left:auto;padding:4px 8px;border-radius:var(--radius);border:1px solid var(--border2);background:transparent;color:var(--text3);font-size:11px;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:4px;transition:all .15s;white-space:nowrap}
.code-zone-toggle:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
#editor-area{flex:1;display:flex;flex-direction:column;overflow:hidden;position:relative}
.code-panel{display:none;flex:1;flex-direction:column;overflow:hidden}.code-panel.active{display:flex}
.code-editor-wrap{flex:1;display:flex;overflow:hidden;position:relative}
.line-numbers{width:44px;background:#f8fafc;padding:12px 8px;font-family:var(--font-mono);font-size:12px;line-height:1.6;color:#94a3b8;text-align:right;overflow:hidden;user-select:none;flex-shrink:0;border-right:1px solid var(--border)}
.code-editor{flex:1;background:#fff;color:#1e293b;border:none;outline:none;resize:none;padding:12px;font-family:var(--font-mono);font-size:12px;line-height:1.6;tab-size:2;overflow-y:auto;white-space:pre}
.code-editor::-webkit-scrollbar{width:6px;height:6px}.code-editor::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}
.code-status-bar{height:22px;background:#f1f5f9;display:flex;align-items:center;gap:16px;padding:0 12px;font-size:10px;color:var(--text3);flex-shrink:0;border-top:1px solid var(--border)}
/* RIGHT PANEL */
#right-panel{width:0;background:var(--bg2);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;transition:width .25s ease;flex-shrink:0;order:4}
#right-panel.open{width:var(--tools-w);min-width:var(--tools-w)}
.panel-head{height:40px;min-height:40px;display:flex;align-items:center;justify-content:space-between;padding:0 12px;border-bottom:1px solid var(--border);flex-shrink:0}
.panel-head-title{font-size:12px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.5px}
.panel-close{background:none;border:none;color:var(--text3);cursor:pointer;font-size:14px;padding:4px}.panel-close:hover{color:var(--text)}
.panel-body{flex:1;overflow-y:auto;padding:12px}.panel-body::-webkit-scrollbar{width:4px}.panel-body::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}
.block-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.block-item{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:8px 6px;cursor:pointer;text-align:center;transition:all .15s;font-size:10px;color:var(--text2)}
.block-item:hover{border-color:var(--accent);color:var(--accent);background:rgba(26,77,122,.06)}
.block-item i{display:block;font-size:18px;margin-bottom:4px;color:var(--accent)}
.block-cat-title{font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin:10px 0 6px}
.block-item.hero i{color:#f97316}.block-item.text i{color:#3b82f6}.block-item.cta i{color:#22c55e}
.block-item.media i{color:#a855f7}.block-item.form i{color:#ec4899}.block-item.grid i{color:#06b6d4}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:11px;color:var(--text2);font-weight:600;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px}
.form-input,.form-textarea,.form-select{width:100%;background:var(--bg3);border:1px solid var(--border2);color:var(--text);border-radius:var(--radius);padding:8px 10px;font-size:13px;font-family:inherit;outline:none;transition:border .15s}
.form-input:focus,.form-textarea:focus,.form-select:focus{border-color:var(--accent)}
.form-textarea{resize:vertical;min-height:80px}
.char-count{font-size:10px;color:var(--text3);text-align:right;margin-top:3px}
.char-count.warn{color:var(--yellow)}.char-count.danger{color:var(--red)}
.meta-section-title{font-size:11px;font-weight:700;color:var(--accent2);text-transform:uppercase;letter-spacing:.8px;margin:20px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border)}
/* IA */
.ia-body{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px}
.ia-mode-btns{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.ia-mode-btn{padding:8px;border-radius:var(--radius);border:1px solid var(--border2);background:var(--bg3);color:var(--text2);cursor:pointer;font-size:11px;font-weight:600;text-align:center;transition:all .15s;font-family:inherit}
.ia-mode-btn.active,.ia-mode-btn:hover{border-color:var(--accent);color:var(--accent);background:rgba(26,77,122,.08)}
#ia-prompt{width:100%;min-height:110px;background:var(--bg3);border:1px solid var(--border2);color:var(--text);border-radius:var(--radius);padding:10px;font-size:12px;font-family:inherit;outline:none;resize:vertical;transition:border .15s}
#ia-prompt:focus{border-color:var(--accent)}
.ia-dispatch-box{background:rgba(124,58,237,.05);border:1px solid rgba(124,58,237,.2);border-radius:var(--radius);padding:10px}
.ia-dispatch-title{font-size:11px;font-weight:700;color:var(--purple);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px}
.ia-dispatch-opts{display:flex;flex-wrap:wrap;gap:8px}
.ia-dispatch-opt{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text2);cursor:pointer}
.ia-dispatch-opt input{accent-color:var(--purple)}
.dot-s{display:inline-block;width:6px;height:6px;border-radius:50%}
.ia-dispatch-status{display:none;margin-top:6px;padding:7px 9px;background:rgba(22,163,74,.07);border:1px solid rgba(22,163,74,.25);border-radius:var(--radius);font-size:11px;color:var(--green);line-height:1.6}
.ia-dispatch-status.show{display:block}
.idb{display:inline-flex;align-items:center;padding:1px 6px;border-radius:8px;margin:1px;font-size:10px;font-weight:600}
.idb-html{background:rgba(249,115,22,.15);color:#ea580c}
.idb-css{background:rgba(59,130,246,.15);color:#3b82f6}
.idb-js{background:rgba(245,158,11,.15);color:#d97706}
.idb-php{background:rgba(136,146,190,.2);color:#5563a8}
.idb-meta{background:rgba(22,163,74,.15);color:#16a34a}
#ia-generate-btn{width:100%;padding:10px;background:linear-gradient(135deg,#1a4d7a,#2563a8);color:#fff;border:none;border-radius:var(--radius);cursor:pointer;font-size:13px;font-weight:700;font-family:inherit;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px}
#ia-generate-btn:hover{filter:brightness(1.1);transform:translateY(-1px);box-shadow:0 4px 20px rgba(26,77,122,.3)}
#ia-generate-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
#ia-output{background:#f8fafc;border:1px solid var(--border);border-radius:var(--radius);padding:12px;font-size:12px;color:var(--text2);min-height:60px;max-height:160px;overflow-y:auto;display:none;line-height:1.6}
.ia-apply-row{display:flex;gap:6px}
.ia-quick-row{display:flex;flex-direction:column;gap:6px}
.ia-quick-btn{padding:6px 10px;background:var(--bg3);border:1px solid var(--border2);border-radius:var(--radius);color:var(--text2);font-size:11px;cursor:pointer;text-align:left;font-family:inherit;transition:all .15s}
.ia-quick-btn:hover{border-color:var(--accent);color:var(--text)}
.connector-search{width:100%;background:var(--bg3);border:1px solid var(--border2);border-radius:var(--radius);padding:7px 10px;font-size:12px;font-family:inherit;outline:none;margin-bottom:8px;color:var(--text)}
.connector-search:focus{border-color:var(--accent)}
.connector-list{display:flex;flex-direction:column;gap:4px}
.connector-item{display:flex;align-items:center;gap:8px;padding:7px 10px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:all .15s;font-size:12px}
.connector-item:hover{border-color:var(--accent2);background:rgba(26,77,122,.04)}
.connector-item.ci-active{border-color:var(--accent);background:rgba(26,77,122,.08)}
.ci-name{flex:1;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ci-status{font-size:10px;padding:1px 6px;border-radius:8px;flex-shrink:0}
.ci-status.published{background:rgba(22,163,74,.15);color:#16a34a}
.ci-status.draft,.ci-status.brouillon{background:rgba(245,158,11,.15);color:#d97706}
.ci-edit{color:var(--text3);text-decoration:none;font-size:11px;padding:3px 5px;border-radius:4px;transition:all .15s;flex-shrink:0}
.ci-edit:hover{background:var(--bg4);color:var(--accent)}
.snippet-row{display:flex;flex-wrap:wrap;gap:4px}
.snippet-btn{font-size:10px;padding:3px 8px;border-radius:4px;border:1px solid var(--border2);background:var(--bg3);color:var(--text3);cursor:pointer;transition:all .15s;font-family:var(--font-mono)}
.snippet-btn:hover{border-color:var(--accent);color:var(--text)}
/* ACTION BAR */
#action-bar{height:44px;background:var(--bg2);border-top:1px solid var(--border);display:flex;align-items:center;gap:8px;padding:0 16px;flex-shrink:0}
#action-bar .ab-left,#action-bar .ab-right{display:flex;align-items:center;gap:8px}
#action-bar .ab-center{flex:1;display:flex;align-items:center;justify-content:center;gap:8px}
.ab-info{font-size:11px;color:var(--text3)}
/* TOAST / MODAL */
#toast-container{position:fixed;bottom:60px;right:20px;z-index:9999;display:flex;flex-direction:column-reverse;gap:8px}
.toast{display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:var(--radius);font-size:13px;font-weight:500;min-width:240px;max-width:380px;box-shadow:0 4px 20px rgba(0,0,0,.4);animation:slideIn .3s ease;border-left:3px solid transparent}
.toast.success{background:#f0fdf4;color:#15803d;border-color:#16a34a}
.toast.error{background:#fef2f2;color:#b91c1c;border-color:#dc2626}
.toast.info{background:#eff6ff;color:#1d4ed8;border-color:#1a4d7a}
.toast.warning{background:#fffbeb;color:#b45309;border-color:#d97706}
@keyframes slideIn{from{transform:translateX(30px);opacity:0}to{transform:none;opacity:1}}
#loading-overlay{display:none;position:fixed;inset:0;z-index:9998;background:rgba(241,245,249,.85);backdrop-filter:blur(4px);align-items:center;justify-content:center;flex-direction:column;gap:16px}
#loading-overlay.show{display:flex}
.loading-spinner{width:40px;height:40px;border:3px solid var(--border2);border-top-color:var(--accent);border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.modal-overlay{display:none;position:fixed;inset:0;z-index:9990;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);align-items:center;justify-content:center}
.modal-overlay.show{display:flex}
.modal-box{background:#fff;border:1px solid var(--border2);border-radius:12px;padding:24px;max-width:560px;width:90%;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.15)}
.modal-title{font-size:16px;font-weight:700;margin-bottom:16px}
.modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}
/* CLONE PANEL */
.clone-url-input{width:100%;background:var(--bg3);border:1px solid var(--border2);color:var(--text);border-radius:var(--radius);padding:8px 10px;font-size:12px;font-family:inherit;outline:none;transition:border .15s}
.clone-url-input:focus{border-color:var(--accent)}
.clone-mode-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin:10px 0}
.clone-mode-btn{padding:8px 4px;border-radius:var(--radius);border:1px solid var(--border2);background:var(--bg3);color:var(--text2);cursor:pointer;font-size:10px;font-weight:700;text-align:center;transition:all .15s;font-family:inherit;line-height:1.4}
.clone-mode-btn.active,.clone-mode-btn:hover{border-color:var(--accent);color:var(--accent);background:rgba(26,77,122,.08)}
.clone-progress{display:none;background:rgba(26,77,122,.05);border:1px solid rgba(26,77,122,.2);border-radius:var(--radius);padding:12px;margin-top:8px}
.clone-progress.show{display:block}
.clone-steps{display:flex;flex-direction:column;gap:6px;margin-top:8px}
.clone-step{display:flex;align-items:center;gap:8px;font-size:11px;color:var(--text3)}
.clone-step.active{color:var(--accent);font-weight:600}
.clone-step.done{color:var(--green)}
.clone-step-icon{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;background:var(--border)}
.clone-step.active .clone-step-icon{background:rgba(26,77,122,.15);color:var(--accent)}
.clone-step.done .clone-step-icon{background:rgba(22,163,74,.15);color:var(--green)}
.clone-summary{background:rgba(22,163,74,.06);border:1px solid rgba(22,163,74,.25);border-radius:var(--radius);padding:10px;font-size:11px;color:var(--text2);margin-top:8px;display:none;line-height:1.6}
.clone-summary.show{display:block}
.clone-dispatch-info{display:flex;flex-wrap:wrap;gap:4px;margin-top:6px}
#clone-generate-btn{width:100%;padding:10px;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;border:none;border-radius:var(--radius);cursor:pointer;font-size:13px;font-weight:700;font-family:inherit;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:8px}
#clone-generate-btn:hover{filter:brightness(1.1);transform:translateY(-1px);box-shadow:0 4px 20px rgba(124,58,237,.3)}
#clone-generate-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.clone-recent-title{font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin:12px 0 6px}
.clone-recent-item{display:flex;align-items:center;gap:6px;padding:6px 8px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;font-size:11px;transition:all .15s;margin-bottom:4px}
.clone-recent-item:hover{border-color:var(--accent2)}
.cri-url{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text2)}
.cri-mode{font-size:9px;padding:1px 5px;border-radius:8px;background:rgba(124,58,237,.12);color:#7c3aed;font-weight:600}
.clone-example-title{font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin:10px 0 6px}
.clone-example-btn{width:100%;padding:6px 10px;background:var(--bg3);border:1px solid var(--border2);border-radius:var(--radius);color:var(--text2);font-size:11px;cursor:pointer;text-align:left;font-family:inherit;transition:all .15s;margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.clone-example-btn:hover{border-color:#7c3aed;color:var(--text)}
/* DB PANEL */
.db-context-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;margin-bottom:12px}
.db-context-badge.page{background:rgba(26,77,122,.1);color:#1a4d7a}
.db-context-badge.article{background:rgba(245,158,11,.15);color:#d97706}
.db-context-badge.secteur{background:rgba(34,197,94,.15);color:#16a34a}
.db-context-badge.guide{background:rgba(168,85,247,.15);color:#7c3aed}
.db-context-badge.capture{background:rgba(6,182,212,.15);color:#0891b2}
.db-context-badge.header,.db-context-badge.footer{background:rgba(239,68,68,.1);color:#dc2626}
.db-section{margin-bottom:18px}
.db-section-title{font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px;padding-bottom:5px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px}
.db-var-list{display:flex;flex-direction:column;gap:3px}
.db-var-item{display:flex;align-items:center;justify-content:space-between;padding:6px 8px;background:var(--bg3);border:1px solid var(--border);border-radius:6px;font-size:11px;cursor:pointer;transition:all .15s}
.db-var-item:hover{border-color:var(--accent);background:rgba(26,77,122,.04)}
.db-var-key{font-family:var(--font-mono);color:var(--accent);font-size:10px;font-weight:600}
.db-var-desc{color:var(--text2);font-size:10px;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dv-copy{color:var(--text3);font-size:10px;opacity:0;transition:opacity .15s}
.db-var-item:hover .dv-copy{opacity:1}
.db-loop-box{background:rgba(26,77,122,.04);border:1px solid rgba(26,77,122,.15);border-radius:var(--radius);padding:10px;margin-top:6px}
.db-loop-title{font-size:10px;font-weight:700;color:var(--accent);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.db-loop-code{font-family:var(--font-mono);font-size:10px;color:var(--text2);line-height:1.8;background:#fff;padding:8px;border-radius:6px;border:1px solid var(--border);cursor:pointer;transition:border .15s;white-space:pre-wrap;max-height:200px;overflow-y:auto}
.db-loop-code:hover{border-color:var(--accent)}
.db-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px}
.db-stat-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:10px;text-align:center}
.db-stat-num{font-size:20px;font-weight:800;color:var(--accent)}
.db-stat-label{font-size:10px;color:var(--text3);margin-top:2px}
.db-refresh-btn{width:100%;padding:7px;background:transparent;border:1px solid var(--border2);border-radius:var(--radius);color:var(--text2);font-size:11px;cursor:pointer;font-family:inherit;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:6px;margin-top:8px}
.db-refresh-btn:hover{border-color:var(--accent);color:var(--accent);background:rgba(26,77,122,.04)}
::-webkit-scrollbar{width:6px;height:6px}::-webkit-scrollbar-track{background:var(--bg3)}::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}
</style>
</head>
<body>
<div id="app">

<!-- TOPBAR -->
<div id="topbar">
  <a href="/admin/<?= htmlspecialchars($C['list']) ?>" class="tb-back"><i class="fas fa-arrow-left"></i> Retour</a>
  <div class="tb-sep"></div>
  <div class="tb-title">
    <span class="tb-badge <?= $context ?>"><?= $ctxLabel ?></span>
    <span class="tb-name"><?= $title ?></span>
    <span id="tb-status-badge" class="<?= $status ?>" onclick="toggleStatus()"><?= $status==='published'?'● Publié':'○ Brouillon' ?></span>
  </div>
  <div class="tb-sep"></div>
  <div class="tb-actions">
    <span id="save-status"></span>
    <button class="btn btn-ghost btn-sm" onclick="toggleCodeZone()"><i class="fas fa-code" id="code-toggle-icon"></i> Code</button>
    <button class="btn btn-ghost btn-sm" onclick="saveContent('draft')"><i class="fas fa-save"></i> Sauvegarder</button>
    <button class="btn btn-success btn-sm" onclick="saveContent('published')"><i class="fas fa-rocket"></i> Publier</button>
    <?php if($frontUrl): ?><a href="<?= $frontUrl ?>" target="_blank" class="btn btn-ghost btn-sm btn-icon" title="Voir en ligne"><i class="fas fa-external-link-alt"></i></a><?php endif; ?>
  </div>
</div>

<!-- MAIN -->
<div id="main">

  <!-- SIDEBAR -->
  <div id="sidebar">
    <button class="sb-btn" id="sb-code"      onclick="openToolPanel('code')"    ><i class="fas fa-code"></i><span class="tooltip">Éditeur</span></button>
    <button class="sb-btn" id="sb-blocks"    onclick="openToolPanel('blocks')"  ><i class="fas fa-th-large"></i><span class="tooltip">Blocs</span></button>
    <button class="sb-btn" id="sb-ia"        onclick="openToolPanel('ia')"      ><i class="fas fa-robot"></i><span class="tooltip">IA Claude</span></button>
    <button class="sb-btn" id="sb-meta"      onclick="openToolPanel('meta')"    ><i class="fas fa-tags"></i><span class="tooltip">SEO</span></button>
    <button class="sb-btn" id="sb-clone"     onclick="openToolPanel('clone')"   ><i class="fas fa-clone" style="color:#7c3aed"></i><span class="tooltip">Cloner un design</span></button>
    <button class="sb-btn" id="sb-db"        onclick="openToolPanel('db')"      ><i class="fas fa-database" style="color:#0891b2"></i><span class="tooltip">Données DB</span></button>
    <?php if ($isDynamic && !empty($connectorData)): ?>
    <button class="sb-btn" id="sb-connector" onclick="openToolPanel('connector')" style="position:relative">
      <i class="fas <?= $connectorIcon ?>"></i>
      <span class="sb-badge-count"><?= min(count($connectorData),99) ?></span>
      <span class="tooltip"><?= $connectorLabel ?></span>
    </button>
    <?php endif; ?>
    <div class="sb-divider"></div>
    <button class="sb-btn" onclick="refreshPreview()"><i class="fas fa-sync-alt"></i><span class="tooltip">Actualiser</span></button>
    <button class="sb-btn" onclick="formatCode()"><i class="fas fa-magic"></i><span class="tooltip">Formater HTML</span></button>
    <button class="sb-btn" onclick="openHistory()"><i class="fas fa-history"></i><span class="tooltip">Historique</span></button>
    <button class="sb-btn" onclick="copyCode()"><i class="fas fa-copy"></i><span class="tooltip">Copier onglet actif</span></button>
    <div class="sb-divider"></div>
    <button class="sb-btn" onclick="clearAll()"><i class="fas fa-trash"></i><span class="tooltip">Vider éditeur</span></button>
    <button class="sb-btn" onclick="openImport()"><i class="fas fa-file-import"></i><span class="tooltip">Importer HTML</span></button>
  </div>

  <!-- PREVIEW ZONE -->
  <div id="preview-zone">
    <div class="preview-topbar">
      <span class="preview-topbar-title"><i class="fas fa-eye" style="color:var(--accent2)"></i> Aperçu live</span>
      <button class="dev-btn active" id="pv-desktop" onclick="setDevice('desktop')">🖥 Bureau</button>
      <button class="dev-btn"        id="pv-tablet"  onclick="setDevice('tablet')">📱 Tablette</button>
      <button class="dev-btn"        id="pv-mobile"  onclick="setDevice('mobile')">📲 Mobile</button>
      <button class="btn btn-ghost btn-sm btn-icon" onclick="refreshPreview()"><i class="fas fa-sync-alt"></i></button>
    </div>
    <div class="preview-area">
      <div class="preview-wrap" id="preview-wrap">
        <iframe id="preview-iframe" srcdoc="" scrolling="yes"></iframe>
      </div>
    </div>
    <div class="preview-bottombar">
      <span class="preview-url" id="preview-url-display"></span>
      <?php if($frontUrl): ?><a href="<?= $frontUrl ?>" target="_blank"><i class="fas fa-external-link-alt"></i> Voir en ligne</a><?php endif; ?>
    </div>
  </div>

  <!-- CODE ZONE -->
  <div id="code-zone">
    <div id="editor-tabs">
      <button class="etab active" id="etab-html" onclick="switchTab('html')"><span class="lang-dot dot-html"></span> HTML</button>
      <button class="etab"        id="etab-css"  onclick="switchTab('css')"><span class="lang-dot dot-css"></span> CSS</button>
      <button class="etab"        id="etab-js"   onclick="switchTab('js')"><span class="lang-dot dot-js"></span> JS</button>
      <?php if ($isDynamic): ?>
      <button class="etab"        id="etab-php"  onclick="switchTab('php')"><span class="lang-dot dot-php"></span> PHP</button>
      <?php endif; ?>
      <button class="code-zone-toggle" onclick="toggleCodeZone()" id="code-collapse-btn"><i class="fas fa-chevron-right"></i> Masquer</button>
    </div>
    <div id="editor-area">
      <div class="code-panel active" id="pane-html">
        <div class="code-editor-wrap"><div class="line-numbers" id="ln-html"></div><textarea class="code-editor" id="editor-html" spellcheck="false" oninput="onEditorInput('html')" onscroll="syncScroll('html')" onkeydown="handleKey(event,'html')"><?= htmlspecialchars($content,ENT_QUOTES|ENT_HTML5) ?></textarea></div>
        <div class="code-status-bar"><span id="cursor-html">Ligne 1, Col 1</span><span id="size-html">0 o</span><span>HTML5</span><span>UTF-8</span></div>
      </div>
      <div class="code-panel" id="pane-css">
        <div class="code-editor-wrap"><div class="line-numbers" id="ln-css"></div><textarea class="code-editor" id="editor-css" spellcheck="false" oninput="onEditorInput('css')" onscroll="syncScroll('css')" onkeydown="handleKey(event,'css')"><?= htmlspecialchars($customCss,ENT_QUOTES|ENT_HTML5) ?></textarea></div>
        <div class="code-status-bar"><span id="cursor-css">Ligne 1, Col 1</span><span id="size-css">0 o</span><span>CSS3</span><span>UTF-8</span></div>
      </div>
      <div class="code-panel" id="pane-js">
        <div class="code-editor-wrap"><div class="line-numbers" id="ln-js"></div><textarea class="code-editor" id="editor-js" spellcheck="false" oninput="onEditorInput('js')" onscroll="syncScroll('js')" onkeydown="handleKey(event,'js')"><?= htmlspecialchars($customJs,ENT_QUOTES|ENT_HTML5) ?></textarea></div>
        <div class="code-status-bar"><span id="cursor-js">Ligne 1, Col 1</span><span id="size-js">0 o</span><span>JavaScript ES6</span><span>UTF-8</span></div>
      </div>
      <?php if ($isDynamic): ?>
      <div class="code-panel" id="pane-php">
        <div class="code-editor-wrap"><div class="line-numbers" id="ln-php"></div><textarea class="code-editor" id="editor-php" spellcheck="false" oninput="onEditorInput('php')" onscroll="syncScroll('php')" onkeydown="handleKey(event,'php')"><?= htmlspecialchars($customPhp,ENT_QUOTES|ENT_HTML5) ?></textarea></div>
        <div class="code-status-bar"><span id="cursor-php">Ligne 1, Col 1</span><span id="size-php">0 o</span><span style="color:#8892be;font-weight:600">PHP 8+</span><span style="color:var(--yellow);font-size:10px">⚠ Serveur uniquement</span></div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div id="right-panel">

    <!-- PANEL CODE -->
    <div id="panel-code" style="display:none;flex-direction:column;height:100%">
      <div class="panel-head"><span class="panel-head-title">Éditeur</span><button class="panel-close" onclick="closeToolPanel()"><i class="fas fa-times"></i></button></div>
      <div class="panel-body">
        <div class="form-group"><label>Titre</label><input type="text" class="form-input" id="field-title" value="<?= $title ?>"></div>
        <div class="form-group"><label>Slug</label><input type="text" class="form-input" id="field-slug" value="<?= $slug ?>"></div>
        <div class="block-cat-title">Snippets HTML</div>
        <div class="snippet-row">
          <button class="snippet-btn" onclick="insertSnippet('section')">section</button>
          <button class="snippet-btn" onclick="insertSnippet('div')">div</button>
          <button class="snippet-btn" onclick="insertSnippet('h2')">h2</button>
          <button class="snippet-btn" onclick="insertSnippet('p')">p</button>
          <button class="snippet-btn" onclick="insertSnippet('a')">a</button>
          <button class="snippet-btn" onclick="insertSnippet('img')">img</button>
          <button class="snippet-btn" onclick="insertSnippet('form')">form</button>
          <button class="snippet-btn" onclick="insertSnippet('btn')">bouton</button>
          <button class="snippet-btn" onclick="insertSnippet('grid')">grid</button>
          <button class="snippet-btn" onclick="insertSnippet('flex')">flex</button>
        </div>
        <div class="block-cat-title" style="margin-top:14px">Variables</div>
        <div class="snippet-row">
          <button class="snippet-btn" onclick="insertVar('nom_conseiller')">{{nom_conseiller}}</button>
          <button class="snippet-btn" onclick="insertVar('ville')">{{ville}}</button>
          <button class="snippet-btn" onclick="insertVar('telephone')">{{telephone}}</button>
          <button class="snippet-btn" onclick="insertVar('email')">{{email}}</button>
        </div>
      </div>
    </div>

    <!-- PANEL BLOCS -->
    <div id="panel-blocks" style="display:none;flex-direction:column;height:100%">
      <div class="panel-head"><span class="panel-head-title">Blocs</span><button class="panel-close" onclick="closeToolPanel()"><i class="fas fa-times"></i></button></div>
      <div class="panel-body">
        <div class="block-cat-title">Structure</div>
        <div class="block-grid">
          <div class="block-item hero" onclick="insertBlock('hero')"><i class="fas fa-crown"></i>Hero</div>
          <div class="block-item grid" onclick="insertBlock('2col')"><i class="fas fa-columns"></i>2 Colonnes</div>
          <div class="block-item grid" onclick="insertBlock('3col')"><i class="fas fa-th"></i>3 Colonnes</div>
          <div class="block-item grid" onclick="insertBlock('features')"><i class="fas fa-star"></i>Features</div>
        </div>
        <div class="block-cat-title">Contenu</div>
        <div class="block-grid">
          <div class="block-item text"  onclick="insertBlock('title-text')"><i class="fas fa-heading"></i>Titre+Texte</div>
          <div class="block-item text"  onclick="insertBlock('faq')"><i class="fas fa-question-circle"></i>FAQ</div>
          <div class="block-item text"  onclick="insertBlock('testimonial')"><i class="fas fa-quote-right"></i>Témoignage</div>
          <div class="block-item media" onclick="insertBlock('image-text')"><i class="fas fa-image"></i>Image+Texte</div>
        </div>
        <div class="block-cat-title">Conversion</div>
        <div class="block-grid">
          <div class="block-item cta"  onclick="insertBlock('cta')"><i class="fas fa-bullhorn"></i>CTA</div>
          <div class="block-item form" onclick="insertBlock('contact-form')"><i class="fas fa-envelope"></i>Formulaire</div>
          <div class="block-item cta"  onclick="insertBlock('stats')"><i class="fas fa-chart-bar"></i>Chiffres</div>
          <div class="block-item form" onclick="insertBlock('estimation')"><i class="fas fa-home"></i>Estimation</div>
        </div>
        <div class="block-cat-title">Navigation</div>
        <div class="block-grid">
          <div class="block-item grid" onclick="insertBlock('breadcrumb')"><i class="fas fa-chevron-right"></i>Fil Ariane</div>
          <div class="block-item grid" onclick="insertBlock('pagination')"><i class="fas fa-ellipsis-h"></i>Pagination</div>
        </div>
      </div>
    </div>

    <!-- PANEL IA -->
    <div id="panel-ia" style="display:none;flex-direction:column;height:100%">
      <div class="panel-head"><span class="panel-head-title"><i class="fas fa-robot" style="color:var(--accent);margin-right:4px"></i> IA Claude</span><button class="panel-close" onclick="closeToolPanel()"><i class="fas fa-times"></i></button></div>
      <div class="ia-body">
        <div>
          <div class="block-cat-title">Mode</div>
          <div class="ia-mode-btns">
            <button class="ia-mode-btn active" data-mode="generate" onclick="setIaMode('generate')">✨ Générer</button>
            <button class="ia-mode-btn" data-mode="improve"  onclick="setIaMode('improve')">🔧 Améliorer</button>
            <button class="ia-mode-btn" data-mode="seo"      onclick="setIaMode('seo')">📈 SEO</button>
            <button class="ia-mode-btn" data-mode="rewrite"  onclick="setIaMode('rewrite')">🔄 Réécrire</button>
          </div>
        </div>
        <div class="ia-dispatch-box">
          <div class="ia-dispatch-title"><i class="fas fa-share-alt"></i> Dispatch automatique</div>
          <p style="font-size:10px;color:var(--text2);margin-bottom:8px">Claude place le code dans les bons onglets.</p>
          <div class="ia-dispatch-opts">
            <label class="ia-dispatch-opt"><input type="checkbox" id="d-html" checked><span class="dot-s" style="background:#f97316"></span> HTML</label>
            <label class="ia-dispatch-opt"><input type="checkbox" id="d-css"  checked><span class="dot-s" style="background:#3b82f6"></span> CSS</label>
            <label class="ia-dispatch-opt"><input type="checkbox" id="d-js"   checked><span class="dot-s" style="background:#f59e0b"></span> JS</label>
            <?php if ($isDynamic): ?>
            <label class="ia-dispatch-opt"><input type="checkbox" id="d-php"><span class="dot-s" style="background:#8892be"></span> PHP</label>
            <?php endif; ?>
            <label class="ia-dispatch-opt"><input type="checkbox" id="d-meta" checked><span class="dot-s" style="background:#16a34a"></span> Meta SEO</label>
            <label class="ia-dispatch-opt" style="width:100%;margin-top:4px"><input type="checkbox" id="d-replace"> Remplacer le contenu</label>
          </div>
        </div>
        <div>
          <div class="block-cat-title">Votre demande</div>
          <textarea id="ia-prompt" placeholder="Ex: Landing page acheteur immobilier Bordeaux. Hero H1, 3 avantages, formulaire contact."></textarea>
        </div>
        <button id="ia-generate-btn" onclick="generateWithIA()"><i class="fas fa-magic"></i> Générer avec Claude</button>
        <div class="ia-dispatch-status" id="ia-status"></div>
        <div id="ia-output-wrap" style="display:none">
          <div class="block-cat-title">Aperçu</div>
          <div id="ia-output"></div>
          <div class="ia-apply-row" style="margin-top:6px">
            <button class="btn btn-primary btn-sm" style="flex:1" onclick="applyIaResult()"><i class="fas fa-check"></i> Appliquer</button>
            <button class="btn btn-ghost btn-sm" onclick="document.getElementById('ia-output-wrap').style.display='none'"><i class="fas fa-times"></i></button>
          </div>
        </div>
        <div>
          <div class="block-cat-title">Idées rapides</div>
          <div class="ia-quick-row">
            <button class="ia-quick-btn" onclick="quickPrompt('hero')">🏠 Hero immobilier Bordeaux</button>
            <button class="ia-quick-btn" onclick="quickPrompt('services')">⭐ 3 services avec icônes</button>
            <button class="ia-quick-btn" onclick="quickPrompt('temoignage')">💬 Témoignage client</button>
            <button class="ia-quick-btn" onclick="quickPrompt('cta')">🎯 CTA estimation gratuite</button>
            <button class="ia-quick-btn" onclick="quickPrompt('faq')">❓ FAQ vendeur</button>
            <button class="ia-quick-btn" onclick="quickPrompt('seo-text')">📝 Texte SEO local 500 mots</button>
          </div>
        </div>
      </div>
    </div>

    <!-- PANEL META -->
    <div id="panel-meta" style="display:none;flex-direction:column;height:100%">
      <div class="panel-head"><span class="panel-head-title">SEO &amp; Meta</span><button class="panel-close" onclick="closeToolPanel()"><i class="fas fa-times"></i></button></div>
      <div class="panel-body">
        <div class="meta-section-title">📄 Page</div>
        <div class="form-group"><label>Titre</label><input type="text" class="form-input" id="field-title-meta" value="<?= $title ?>" oninput="syncTitles(this)"></div>
        <div class="form-group"><label>Slug URL</label><input type="text" class="form-input" id="field-slug-meta" value="<?= $slug ?>"></div>
        <div class="meta-section-title">🔍 SEO</div>
        <div class="form-group">
          <label>Meta Title <small>(55-65 car.)</small></label>
          <input type="text" class="form-input" id="meta-title" value="<?= $metaTitle ?>" oninput="updateCharCount(this,'mc-title',65)">
          <div id="mc-title" class="char-count">0 / 65 car.</div>
        </div>
        <div class="form-group">
          <label>Meta Description <small>(150-160 car.)</small></label>
          <textarea class="form-textarea" id="meta-desc" oninput="updateCharCount(this,'mc-desc',160)"><?= $metaDesc ?></textarea>
          <div id="mc-desc" class="char-count">0 / 160 car.</div>
        </div>
        <div class="form-group"><label>Mots-clés</label><input type="text" class="form-input" id="meta-keywords" value="<?= htmlspecialchars($entity['meta_keywords']??'',ENT_QUOTES) ?>"></div>
        <div class="meta-section-title">⚙️ Options</div>
        <div class="form-group">
          <label>Statut</label>
          <select class="form-select" id="field-status">
            <option value="published" <?= $status==='published'?'selected':'' ?>>Publié</option>
            <option value="draft"     <?= $status==='draft'    ?'selected':'' ?>>Brouillon</option>
          </select>
        </div>
        <button class="btn btn-primary btn-sm" style="width:100%" onclick="saveContent('keep')"><i class="fas fa-save"></i> Sauvegarder</button>
      </div>
    </div>

    <!-- PANEL CLONE DESIGN -->
   <!-- PANEL CLONE DESIGN -->
    <div id="panel-clone" style="display:none;flex-direction:column;height:100%">
      <div class="panel-head">
        <span class="panel-head-title"><i class="fas fa-clone" style="color:#7c3aed;margin-right:4px"></i> Cloner un design</span>
        <button class="panel-close" onclick="closeToolPanel()"><i class="fas fa-times"></i></button>
      </div>
      <div class="panel-body" style="padding-top:10px">

        <!-- TABS : Interne / Externe -->
        <div style="display:flex;gap:0;margin-bottom:14px;border:1px solid var(--border2);border-radius:var(--radius);overflow:hidden">
          <button id="clone-tab-internal" onclick="setCloneSource('internal')"
            style="flex:1;padding:7px;border:none;background:var(--accent);color:#fff;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:5px">
            <i class="fas fa-database"></i> Pages du site
          </button>
          <button id="clone-tab-external" onclick="setCloneSource('external')"
            style="flex:1;padding:7px;border:none;background:var(--bg3);color:var(--text2);font-size:11px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:5px">
            <i class="fas fa-globe"></i> URL externe
          </button>
        </div>

        <!-- SOURCE INTERNE -->
        <div id="clone-source-internal">
          <div style="font-size:11px;color:var(--text3);margin-bottom:8px">Choisissez une page existante à cloner :</div>
          <input type="text" id="clone-internal-search" class="clone-url-input"
            placeholder="Rechercher une page..." oninput="filterInternalPages(this.value)"
            style="margin-bottom:8px">
          <div id="clone-internal-list" style="max-height:280px;overflow-y:auto;display:flex;flex-direction:column;gap:3px">
            <div style="text-align:center;padding:16px;color:var(--text3);font-size:11px">
              <i class="fas fa-circle-notch fa-spin"></i> Chargement...
            </div>
          </div>
        </div>

        <!-- SOURCE EXTERNE -->
        <div id="clone-source-external" style="display:none">
          <div class="form-group">
            <label>URL de la page à cloner</label>
            <input type="url" class="clone-url-input" id="clone-url"
              placeholder="https://exemple.com/ma-page"
              onkeydown="if(event.key==='Enter')startClone()">
          </div>
          <div class="clone-example-title">🌐 Sites immo de référence</div>
          <button class="clone-example-btn" onclick="setCloneUrl('https://www.orpi.com')">🏢 orpi.com</button>
          <button class="clone-example-btn" onclick="setCloneUrl('https://www.century21.fr')">🏢 century21.fr</button>
          <button class="clone-example-btn" onclick="setCloneUrl('https://www.laforet.com')">🏢 laforet.com</button>
          <button class="clone-example-btn" onclick="setCloneUrl('https://www.efficity.com')">🏢 efficity.com</button>
          <button class="clone-example-btn" onclick="setCloneUrl('https://www.proprietes-privees.com')">🏢 proprietes-privees.com</button>
          <button class="clone-example-btn" onclick="setCloneUrl('https://www.safti.fr')">🏢 safti.fr</button>
        </div>

        <!-- MODE CLONAGE (commun) -->
        <div class="block-cat-title" style="margin-top:12px">Mode de clonage</div>
        <div class="clone-mode-grid">
          <button class="clone-mode-btn active" data-cmode="clone" onclick="setCloneMode('clone')">
            <i class="fas fa-copy" style="display:block;font-size:14px;margin-bottom:3px;color:#7c3aed"></i>Clone fidèle
          </button>
          <button class="clone-mode-btn" data-cmode="adapt" onclick="setCloneMode('adapt')">
            <i class="fas fa-magic" style="display:block;font-size:14px;margin-bottom:3px;color:#d97706"></i>Adapter immo
          </button>
          <button class="clone-mode-btn" data-cmode="extract" onclick="setCloneMode('extract')">
            <i class="fas fa-code" style="display:block;font-size:14px;margin-bottom:3px;color:#0891b2"></i>Template vide
          </button>
        </div>

        <!-- DISPATCH -->
        <div class="ia-dispatch-box" style="margin:10px 0 8px">
          <div class="ia-dispatch-title"><i class="fas fa-share-alt"></i> Dispatch dans les onglets</div>
          <div class="ia-dispatch-opts" style="margin-top:6px">
            <label class="ia-dispatch-opt"><input type="checkbox" id="cd-html" checked><span class="dot-s" style="background:#f97316"></span> HTML</label>
            <label class="ia-dispatch-opt"><input type="checkbox" id="cd-css"  checked><span class="dot-s" style="background:#3b82f6"></span> CSS</label>
            <label class="ia-dispatch-opt"><input type="checkbox" id="cd-js"   checked><span class="dot-s" style="background:#f59e0b"></span> JS</label>
            <label class="ia-dispatch-opt"><input type="checkbox" id="cd-meta" checked><span class="dot-s" style="background:#16a34a"></span> Meta SEO</label>
            <label class="ia-dispatch-opt" style="width:100%;margin-top:4px"><input type="checkbox" id="cd-replace"> Remplacer le contenu existant</label>
          </div>
        </div>

        <button id="clone-generate-btn" onclick="startClone()"><i class="fas fa-magic"></i> Cloner avec Claude</button>

        <!-- PROGRESSION -->
        <div class="clone-progress" id="clone-progress">
          <div style="font-size:11px;font-weight:700;color:var(--accent)">
            <i class="fas fa-circle-notch fa-spin"></i> <span id="clone-progress-label">Initialisation...</span>
          </div>
          <div class="clone-steps">
            <div class="clone-step" id="cstep-fetch"><div class="clone-step-icon"><i class="fas fa-download"></i></div>Récupération de la page</div>
            <div class="clone-step" id="cstep-extract"><div class="clone-step-icon"><i class="fas fa-cut"></i></div>Extraction HTML/CSS/JS</div>
            <div class="clone-step" id="cstep-claude"><div class="clone-step-icon"><i class="fas fa-robot"></i></div>Traitement par Claude AI</div>
            <div class="clone-step" id="cstep-dispatch"><div class="clone-step-icon"><i class="fas fa-share-alt"></i></div>Dispatch dans l'éditeur</div>
          </div>
        </div>

        <!-- RÉSUMÉ -->
        <div class="clone-summary" id="clone-summary">
          <strong>✅ Cloné !</strong> <span id="clone-summary-text"></span>
          <div class="clone-dispatch-info" id="clone-dispatch-info"></div>
        </div>

        <!-- HISTORIQUE -->
        <div class="clone-recent-title">🕐 Clonages récents</div>
        <div id="clone-recent-list"></div>
      </div>
    </div>

    <!-- PANEL DONNÉES DB -->
    <div id="panel-db" style="display:none;flex-direction:column;height:100%">
      <div class="panel-head">
        <span class="panel-head-title"><i class="fas fa-database" style="color:#0891b2;margin-right:4px"></i> Données DB</span>
        <button class="panel-close" onclick="closeToolPanel()"><i class="fas fa-times"></i></button>
      </div>
      <div class="panel-body" id="db-panel-body" style="padding-top:12px">
        <div style="text-align:center;padding:24px;color:var(--text3)"><i class="fas fa-circle-notch fa-spin" style="font-size:20px"></i></div>
      </div>
    </div>

    <!-- PANEL CONNECTEUR -->
    <?php if ($isDynamic && !empty($connectorData)): ?>
    <div id="panel-connector" style="display:none;flex-direction:column;height:100%">
      <div class="panel-head">
        <span class="panel-head-title"><i class="fas <?= $connectorIcon ?>" style="color:var(--accent);margin-right:4px"></i> <?= $connectorLabel ?></span>
        <button class="panel-close" onclick="closeToolPanel()"><i class="fas fa-times"></i></button>
      </div>
      <div class="panel-body" style="padding-top:10px">
        <p style="font-size:11px;color:var(--text2);margin-bottom:10px">Cliquez pour charger cet élément dans l'éditeur.</p>
        <input type="text" class="connector-search" placeholder="Rechercher..." oninput="filterConnector(this.value)">
        <div class="connector-list" id="connectorList">
          <?php foreach ($connectorData as $ci):
            $ciStatus=$ci['status']??'draft'; $ciName=htmlspecialchars($ci['name']??'Sans titre'); $ciId=(int)$ci['id'];
          ?>
          <div class="connector-item <?= $ciId===$entityId?'ci-active':'' ?>" data-name="<?= $ciName ?>" onclick="navigateConnector(<?= $ciId ?>)">
            <span class="ci-name"><?= $ciName ?></span>
            <span class="ci-status <?= $ciStatus ?>"><?= $ciStatus==='published'?'Pub':'Draft' ?></span>
            <a href="<?= $connectorEditBase ?><?= $ciId ?>" target="_blank" class="ci-edit" onclick="event.stopPropagation()"><i class="fas fa-external-link-alt"></i></a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /right-panel -->
</div><!-- /main -->

<!-- ACTION BAR -->
<div id="action-bar">
  <div class="ab-left">
    <span class="ab-info" id="word-count">0 mots</span>
    <span class="ab-info" id="ab-lines">0 lignes</span>
  </div>
  <div class="ab-center">
    <span class="ab-info" id="ab-modified" style="color:var(--yellow);display:none">⚠ Non sauvegardé</span>
  </div>
  <div class="ab-right">
    <span class="ab-info" id="ab-autosave" style="color:var(--text3)">Autosave off</span>
    <button class="btn btn-ghost btn-sm" onclick="toggleAutosave()"><i class="fas fa-clock" id="autosave-icon"></i> Autosave</button>
  </div>
</div>
</div><!-- /app -->

<div id="toast-container"></div>
<div id="loading-overlay"><div class="loading-spinner"></div><div style="font-size:14px;color:var(--text2)">Génération...</div></div>

<!-- MODALS -->
<div class="modal-overlay" id="modal-history">
  <div class="modal-box">
    <div class="modal-title"><i class="fas fa-history"></i> Historique des versions</div>
    <div id="history-list" style="display:flex;flex-direction:column;gap:8px;max-height:400px;overflow-y:auto"></div>
    <div class="modal-actions"><button class="btn btn-ghost" onclick="closeModal('modal-history')">Fermer</button></div>
  </div>
</div>
<div class="modal-overlay" id="modal-import">
  <div class="modal-box">
    <div class="modal-title"><i class="fas fa-file-import"></i> Importer HTML</div>
    <textarea class="form-textarea" id="import-textarea" style="min-height:200px;font-family:var(--font-mono);font-size:12px" placeholder="Collez votre HTML ici..."></textarea>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal('modal-import')">Annuler</button>
      <button class="btn btn-primary" onclick="importHtml()">Importer</button>
    </div>
  </div>
</div>
<script>
// ── CONFIG ────────────────────────────────────────────────────────────────────
const BP = {
  entityId: <?= $entityId ?>,
  context: <?= json_encode($context) ?>,
  isDynamic: <?= $isDynamic?'true':'false' ?>,
  saveUrl: '/admin/api/builder/save-content.php',
  cloneUrl: '/admin/api/builder/clone-design.php',
  aiUrl: <?= json_encode($aiProxyUrl) ?>,
  frontUrl: <?= json_encode($frontUrl) ?>,
  title: <?= json_encode($title) ?>,
  slug: <?= json_encode($slug) ?>,
  status: <?= json_encode($status) ?>,
  isDirty: false, autosave: false, autosaveTimer: null,
  currentTab: 'html', currentToolPanel: null,
  codeVisible: true, iaMode: 'generate', iaResult: null,
  cloneMode: 'clone',
  siteIdentity: <?= json_encode($siteIdentity) ?>,
};
const TABS = ['html','css','js'<?= $isDynamic?",'php'":'' ?>];
const TOOL_PANELS = ['code','blocks','ia','meta','clone','db','connector'];

// Données DB injectées depuis PHP
const BP_DB_DATA  = <?= json_encode($dbData,  JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?>;
const BP_DB_STATS = <?= json_encode($dbStats, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?>;

// ── INIT ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  TABS.forEach(t => { updateLineNumbers(t); updateSize(t); });
  updateStats();
  initCharCounts();
  trackChanges();
  document.getElementById('preview-url-display').textContent = BP.frontUrl || '(pas d\'URL frontend)';
  document.addEventListener('keydown', globalKey);
  resizePreviewIframe();
  window.addEventListener('resize', resizePreviewIframe);
  refreshPreview();
});

// ── RESIZE IFRAME ─────────────────────────────────────────────────────────────
function resizePreviewIframe() {
  const topbar   = document.getElementById('topbar')?.offsetHeight   || 50;
  const actionBar= document.getElementById('action-bar')?.offsetHeight|| 44;
  const pvTop    = document.querySelector('.preview-topbar')?.offsetHeight   || 40;
  const pvBottom = document.querySelector('.preview-bottombar')?.offsetHeight || 30;
  const h = window.innerHeight - topbar - actionBar - pvTop - pvBottom - 32;
  const iframe = document.getElementById('preview-iframe');
  if (iframe) iframe.style.height = Math.max(300, h) + 'px';
}

// ── CODE ZONE TOGGLE ──────────────────────────────────────────────────────────
function toggleCodeZone() {
  const cz = document.getElementById('code-zone');
  const btn = document.getElementById('code-collapse-btn');
  BP.codeVisible = !BP.codeVisible;
  cz.classList.toggle('collapsed', !BP.codeVisible);
  if (btn) btn.innerHTML = BP.codeVisible
    ? '<i class="fas fa-chevron-right"></i> Masquer'
    : '<i class="fas fa-chevron-left"></i> Code';
  setTimeout(resizePreviewIframe, 280);
}

// ── TABS CODE ─────────────────────────────────────────────────────────────────
function switchTab(tab) {
  BP.currentTab = tab;
  TABS.forEach(t => {
    document.getElementById('pane-'+t)?.classList.toggle('active', t === tab);
    document.getElementById('etab-'+t)?.classList.toggle('active', t === tab);
  });
  updateLineNumbers(tab);
  setTimeout(() => document.getElementById('editor-'+tab)?.focus(), 30);
}

// ── PANNEAU OUTILS ────────────────────────────────────────────────────────────
function openToolPanel(name) {
  const rp = document.getElementById('right-panel');
  if (BP.currentToolPanel === name && rp.classList.contains('open')) {
    closeToolPanel(); return;
  }
  BP.currentToolPanel = name;
  rp.classList.add('open');
  TOOL_PANELS.forEach(p => {
    const el = document.getElementById('panel-'+p);
    if (el) el.style.display = p === name ? 'flex' : 'none';
  });
  document.querySelectorAll('.sb-btn[id^="sb-"]').forEach(b => b.classList.remove('active'));
  document.getElementById('sb-'+name)?.classList.add('active');
  if (name === 'db')    setTimeout(loadDbPanel, 50);
  if (name === 'clone') setTimeout(loadCloneHistory, 50);
  setTimeout(resizePreviewIframe, 280);
}
function closeToolPanel() {
  document.getElementById('right-panel').classList.remove('open');
  BP.currentToolPanel = null;
  document.querySelectorAll('.sb-btn[id^="sb-"]').forEach(b => b.classList.remove('active'));
  setTimeout(resizePreviewIframe, 280);
}

// ── CONNECTEUR ────────────────────────────────────────────────────────────────
function filterConnector(q) {
  const ql = q.toLowerCase();
  document.querySelectorAll('#connectorList .connector-item').forEach(el =>
    el.style.display = el.dataset.name?.toLowerCase().includes(ql) ? '' : 'none'
  );
}
function navigateConnector(id) {
  const url = new URL(window.location.href);
  url.searchParams.set('entity_id', id);
  window.location.href = url.toString();
}

// ── PREVIEW ───────────────────────────────────────────────────────────────────
function setDevice(dev) {
  const wrap = document.getElementById('preview-wrap');
  wrap.className = 'preview-wrap' + (dev !== 'desktop' ? ' '+dev : '');
  ['desktop','tablet','mobile'].forEach(d =>
    document.getElementById('pv-'+d)?.classList.toggle('active', d === dev)
  );
  setTimeout(resizePreviewIframe, 50);
}
function refreshPreview() {
  const html = document.getElementById('editor-html')?.value || '';
  const css  = document.getElementById('editor-css')?.value  || '';
  const js   = document.getElementById('editor-js')?.value   || '';
  const isFullDoc = /<!doctype/i.test(html.trim()) || /^<html/i.test(html.trim());
  let doc;
  if (isFullDoc) {
    doc = html;
    if (css) doc = doc.replace('</head>', `<style>${css}</style>\n</head>`);
    if (js)  doc = doc.replace('</body>', `<script>${js}<\/script>\n</body>`);
  } else {
    doc = `<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box}body{margin:0;font-family:'DM Sans',sans-serif;background:#f9f6f3}${css}</style>
</head><body>${html}<script>${js}<\/script></body></html>`;
  }
  document.getElementById('preview-iframe').srcdoc = doc;
}
let _rt = null;
function debounceRefresh() { clearTimeout(_rt); _rt = setTimeout(refreshPreview, 600); }

// ── ÉDITEUR ───────────────────────────────────────────────────────────────────
function updateLineNumbers(tab) {
  const ta = document.getElementById('editor-'+tab);
  const ln = document.getElementById('ln-'+tab);
  if (!ta || !ln) return;
  ln.innerHTML = Array.from({length: ta.value.split('\n').length}, (_,i) => i+1).join('<br>');
}
function syncScroll(tab) {
  const ta = document.getElementById('editor-'+tab);
  const ln = document.getElementById('ln-'+tab);
  if (ta && ln) ln.scrollTop = ta.scrollTop;
}
function updateStats() {
  const h = document.getElementById('editor-html')?.value || '';
  document.getElementById('word-count').textContent = h.replace(/<[^>]+>/g,'').split(/\s+/).filter(Boolean).length + ' mots';
  document.getElementById('ab-lines').textContent = h.split('\n').length + ' lignes';
}
function updateSize(tab) {
  const ta = document.getElementById('editor-'+tab);
  const el = document.getElementById('size-'+tab);
  if (!ta || !el) return;
  const b = new Blob([ta.value]).size;
  el.textContent = b < 1024 ? b+' o' : (b/1024).toFixed(1)+' Ko';
}
function updateCursor(tab) {
  const ta = document.getElementById('editor-'+tab);
  const el = document.getElementById('cursor-'+tab);
  if (!ta || !el) return;
  const l = ta.value.substring(0, ta.selectionStart).split('\n');
  el.textContent = `Ligne ${l.length}, Col ${l[l.length-1].length+1}`;
}
function onEditorInput(tab) {
  updateLineNumbers(tab); updateStats(); markDirty(); updateCursor(tab); updateSize(tab);
  if (tab !== 'js' && tab !== 'php') debounceRefresh();
}

// ── DIRTY ─────────────────────────────────────────────────────────────────────
function trackChanges() {
  TABS.forEach(t => document.getElementById('editor-'+t)?.addEventListener('input', markDirty));
}
function markDirty() {
  BP.isDirty = true;
  document.getElementById('ab-modified').style.display = 'inline';
  document.title = '* ' + BP.title + ' — Builder Pro';
}
function markClean() {
  BP.isDirty = false;
  document.getElementById('ab-modified').style.display = 'none';
  document.title = BP.title + ' — Builder Pro';
}

// ── SAVE ──────────────────────────────────────────────────────────────────────
async function saveContent(statusParam) {
  const html  = document.getElementById('editor-html')?.value || '';
  const css   = document.getElementById('editor-css')?.value  || '';
  const js    = document.getElementById('editor-js')?.value   || '';
  const php   = document.getElementById('editor-php')?.value  || '';
  const title = document.getElementById('field-title')?.value || BP.title;
  const slug  = document.getElementById('field-slug')?.value  || BP.slug;
  const status = statusParam === 'keep'
    ? (document.getElementById('field-status')?.value || BP.status)
    : (statusParam === 'draft' || statusParam === 'published' ? statusParam : BP.status);
  const fd = new FormData();
  fd.append('context', BP.context); fd.append('entity_id', BP.entityId);
  fd.append('html_content', html); fd.append('custom_css', css);
  fd.append('custom_js', js); fd.append('php_content', php);
  fd.append('meta_title',       document.getElementById('meta-title')?.value    || '');
  fd.append('meta_description', document.getElementById('meta-desc')?.value     || '');
  fd.append('meta_keywords',    document.getElementById('meta-keywords')?.value || '');
  fd.append('title', title); fd.append('slug', slug); fd.append('status', status);
  setSaveStatus('saving');
  try {
    const r = await fetch(BP.saveUrl, {
      method:'POST',
      body:fd,
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    const raw = await r.text();
    let d = null;
    try {
      d = JSON.parse(raw);
    } catch (_parseErr) {
      if (r.status === 401 || r.status === 403) {
        throw new Error('Session expirée. Rechargez la page puis reconnectez-vous.');
      }
      const snippet = (raw || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 120);
      throw new Error(snippet ? `Réponse invalide du serveur (${r.status}) : ${snippet}` : `Réponse invalide du serveur (${r.status})`);
    }
    if (d.success) {
      markClean(); saveHistory(html, css, js);
      BP.status = status; updateStatusBadge(status);
      setSaveStatus('saved');
      showToast(status === 'published' ? '🚀 Publié !' : '💾 Sauvegardé', 'success');
    } else {
      setSaveStatus('error');
      showToast('Erreur : '+(d.error||d.message||'Inconnue'), 'error');
    }
  } catch(e) { setSaveStatus('error'); showToast('Erreur réseau : '+e.message, 'error'); }
}
function setSaveStatus(s) {
  const el = document.getElementById('save-status');
  el.innerHTML = {
    saving: '<i class="fas fa-circle-notch fa-spin"></i> Sauvegarde...',
    saved:  '<i class="fas fa-check" style="color:#22c55e"></i> Sauvegardé',
    error:  '<i class="fas fa-exclamation-circle" style="color:#ef4444"></i> Erreur'
  }[s] || '';
  if (s === 'saved') setTimeout(() => el.innerHTML = '', 3000);
}
function updateStatusBadge(s) {
  const el = document.getElementById('tb-status-badge');
  if (!el) return;
  el.className = s;
  el.textContent = s === 'published' ? '● Publié' : '○ Brouillon';
}
function toggleStatus() {
  const ns = BP.status === 'published' ? 'draft' : 'published';
  BP.status = ns; updateStatusBadge(ns);
  const s = document.getElementById('field-status'); if (s) s.value = ns;
  markDirty();
}

// ── AUTOSAVE ──────────────────────────────────────────────────────────────────
function toggleAutosave() {
  BP.autosave = !BP.autosave;
  const el = document.getElementById('ab-autosave');
  const ic = document.getElementById('autosave-icon');
  if (BP.autosave) {
    el.textContent = 'Autosave 30s'; el.style.color = 'var(--green)';
    ic.className = 'fas fa-check-circle';
    BP.autosaveTimer = setInterval(() => { if (BP.isDirty) saveContent('draft'); }, 30000);
  } else {
    el.textContent = 'Autosave off'; el.style.color = 'var(--text3)';
    ic.className = 'fas fa-clock';
    clearInterval(BP.autosaveTimer);
  }
}

// ── HISTORIQUE ────────────────────────────────────────────────────────────────
const HK = 'bpro_hist_'+BP.context+'_'+BP.entityId;
function saveHistory(h, c, j) {
  const hs = JSON.parse(localStorage.getItem(HK)||'[]');
  hs.unshift({html:h, css:c, js:j, time: new Date().toLocaleString('fr-FR')});
  if (hs.length > 20) hs.pop();
  localStorage.setItem(HK, JSON.stringify(hs));
}
function openHistory() {
  const hs = JSON.parse(localStorage.getItem(HK)||'[]');
  const list = document.getElementById('history-list');
  list.innerHTML = hs.length
    ? hs.map((h,i) => `<div style="background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:8px;cursor:pointer;font-size:11px" onclick="restoreHistory(${i})">
        <strong>Version du ${h.time}</strong><br>
        <span style="color:var(--text3)">${(h.html||'').substring(0,80)}...</span>
      </div>`).join('')
    : '<p style="color:var(--text3);font-size:12px">Aucune version sauvegardée.</p>';
  document.getElementById('modal-history').classList.add('show');
}
function restoreHistory(i) {
  const h = JSON.parse(localStorage.getItem(HK)||'[]')[i];
  if (!h || !confirm('Restaurer cette version ?')) return;
  document.getElementById('editor-html').value = h.html || '';
  document.getElementById('editor-css').value  = h.css  || '';
  document.getElementById('editor-js').value   = h.js   || '';
  TABS.forEach(t => updateLineNumbers(t));
  markDirty(); closeModal('modal-history'); refreshPreview();
  showToast('Version restaurée', 'info');
}

// ── IA GÉNÉRATION ─────────────────────────────────────────────────────────────
function setIaMode(m) {
  BP.iaMode = m;
  document.querySelectorAll('.ia-mode-btn').forEach(b => b.classList.toggle('active', b.dataset.mode === m));
}
const QP = {
  hero: "Crée une section hero pour conseiller immobilier à Bordeaux. H1 accrocheur, sous-titre, bouton CTA. Couleurs #1a4d7a et #d4a574.",
  services: "Section 3 services immobiliers (Achat, Vente, Estimation). Icônes Font Awesome, style épuré blanc.",
  temoignage: "Bloc témoignage : avatar initiales, nom, ville, étoiles, texte client satisfait. Style carte ombre.",
  cta: "CTA 'Estimation gratuite 24h'. Texte persuasif, formulaire email, bouton. Fond #1a4d7a.",
  faq: "FAQ 5 questions vendeurs immobiliers. Style accordéon <details>.",
  'seo-text': "Texte SEO 400-500 mots conseiller immobilier Bordeaux. Inclure Chartrons, Bacalan, Mériadeck.",
};
function quickPrompt(k) {
  document.getElementById('ia-prompt').value = QP[k] || '';
  document.getElementById('ia-prompt').focus();
}
async function generateWithIA() {
  const prompt = document.getElementById('ia-prompt').value.trim();
  if (!prompt) { showToast('Entrez une description', 'warning'); return; }
  const btn = document.getElementById('ia-generate-btn');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Génération...';
  const dHtml=document.getElementById('d-html')?.checked??true;
  const dCss=document.getElementById('d-css')?.checked??true;
  const dJs=document.getElementById('d-js')?.checked??true;
  const dPhp=document.getElementById('d-php')?.checked??false;
  const dMeta=document.getElementById('d-meta')?.checked??true;
  const doReplace=document.getElementById('d-replace')?.checked??false;
  const exHtml=document.getElementById('editor-html')?.value||'';
  const exCss=document.getElementById('editor-css')?.value||'';
  const statusEl=document.getElementById('ia-status');
  statusEl.className='ia-dispatch-status show';
  statusEl.innerHTML='<i class="fas fa-circle-notch fa-spin"></i> Envoi à Claude...';
  const system=`Tu es un expert développeur web spécialisé en immobilier local français.
Conseiller : Eduardo De Sul, Bordeaux/Blanquefort, eXp France. Couleurs #1a4d7a, #d4a574, #f9f6f3. Fonts Playfair Display + DM Sans.
${Object.keys(BP.siteIdentity).length?'Identité: '+JSON.stringify(BP.siteIdentity):''}
RÈGLE ABSOLUE : Réponds UNIQUEMENT avec un JSON valide, sans markdown, sans backtick, sans texte avant ou après.
Format : {"html":"...HTML body seulement...","css":"...CSS pur...","js":"...JS pur..."${BP.isDynamic?',"php":"...PHP..."':''},"meta_title":"...60 car max...","meta_desc":"...155 car max...","slug":"...slug..."}
Si champ vide : chaîne vide "".`;
  const userMsg=(BP.iaMode!=='generate'&&exHtml)?`HTML existant:\n${exHtml.substring(0,800)}\n\nCSS:\n${exCss.substring(0,400)}\n\nDemande: ${prompt}`:prompt;
  try {
    const r=await fetch(BP.aiUrl,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({module:'builder',action:'generate',prompt:userMsg,system,context:BP.context,id:BP.entityId})});
    const d=await r.json();
    if(!d.success&&!d.content) throw new Error(d.error||d.message||'Réponse vide');
    const raw=d.content||d.result||d.text||'';
    let gen=null;
    try{gen=JSON.parse(raw);}catch(e){const m=raw.match(/\{[\s\S]*\}/);if(m)try{gen=JSON.parse(m[0]);}catch(e2){}}
    if(gen){
      const done=[];
      if(dHtml&&gen.html!==undefined){const ta=document.getElementById('editor-html');if(ta){ta.value=doReplace?gen.html:(exHtml+'\n\n'+gen.html).trim();updateLineNumbers('html');done.push('<span class="idb idb-html">HTML</span>');}}
      if(dCss&&gen.css?.trim()){const ta=document.getElementById('editor-css');if(ta){ta.value=doReplace?gen.css:(exCss+'\n\n'+gen.css).trim();updateLineNumbers('css');done.push('<span class="idb idb-css">CSS</span>');}}
      if(dJs&&gen.js?.trim()){const ta=document.getElementById('editor-js');if(ta){ta.value=gen.js;updateLineNumbers('js');done.push('<span class="idb idb-js">JS</span>');}}
      if(BP.isDynamic&&dPhp&&gen.php?.trim()){const ta=document.getElementById('editor-php');if(ta){ta.value=gen.php;updateLineNumbers('php');done.push('<span class="idb idb-php">PHP</span>');}}
      if(dMeta){
        if(gen.meta_title){const el=document.getElementById('meta-title');if(el)el.value=gen.meta_title;}
        if(gen.meta_desc){const el=document.getElementById('meta-desc');if(el)el.value=gen.meta_desc;}
        if(gen.slug){const el=document.getElementById('field-slug');if(el)el.value=gen.slug;const el2=document.getElementById('field-slug-meta');if(el2)el2.value=gen.slug;}
        done.push('<span class="idb idb-meta">Meta SEO</span>');
      }
      statusEl.innerHTML='✅ Dispatché → '+done.join(' ');
      markDirty();updateStats();switchTab('html');setTimeout(refreshPreview,200);
      showToast('✨ Code généré et dispatché !','success');
    } else {
      if(raw&&dHtml){const ta=document.getElementById('editor-html');if(ta){ta.value=doReplace?raw:(exHtml+'\n\n'+raw).trim();updateLineNumbers('html');markDirty();}}
      const out=document.getElementById('ia-output');
      out.textContent=raw.substring(0,400)+(raw.length>400?'...':'');
      document.getElementById('ia-output-wrap').style.display='block';
      BP.iaResult=raw;
      statusEl.innerHTML='⚠️ JSON non structuré — placé dans HTML.';
      showToast('⚠️ Réponse non JSON — dans HTML','warning');
    }
  } catch(e){statusEl.innerHTML='❌ '+e.message;showToast('Erreur IA: '+e.message,'error');}
  finally{btn.disabled=false;btn.innerHTML='<i class="fas fa-magic"></i> Générer avec Claude';}
}
function applyIaResult() {
  if(!BP.iaResult) return;
  document.getElementById('editor-html').value=BP.iaResult;
  updateLineNumbers('html');markDirty();updateStats();refreshPreview();
  document.getElementById('ia-output-wrap').style.display='none';
  showToast('Appliqué','success');
}

// ── CLONE DESIGN ──────────────────────────────────────────────────────────────
const CLONE_HK = 'bpro_clone_history';
let _cloneSource = 'internal'; // 'internal' | 'external'
let _cloneInternalPages = null; // cache pages internes
let _cloneSelectedId = 0;
let _cloneSelectedType = '';
let _cloneSelectedLabel = '';

function setCloneSource(src) {
  _cloneSource = src;
  document.getElementById('clone-source-internal').style.display = src==='internal' ? '' : 'none';
  document.getElementById('clone-source-external').style.display = src==='external' ? '' : 'none';
  const btnI = document.getElementById('clone-tab-internal');
  const btnE = document.getElementById('clone-tab-external');
  if (btnI) { btnI.style.background = src==='internal'?'var(--accent)':'var(--bg3)'; btnI.style.color = src==='internal'?'#fff':'var(--text2)'; }
  if (btnE) { btnE.style.background = src==='external'?'var(--accent)':'var(--bg3)'; btnE.style.color = src==='external'?'#fff':'var(--text2)'; }
  if (src === 'internal' && !_cloneInternalPages) loadInternalPages();
}

async function loadInternalPages() {
  const list = document.getElementById('clone-internal-list');
  if (!list) return;
  list.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text3);font-size:11px"><i class="fas fa-circle-notch fa-spin"></i> Chargement...</div>';
  try {
    const r = await fetch(BP.cloneUrl + '?action=list');
    const d = await r.json();
    if (!d.success) throw new Error(d.error||'Erreur');
    _cloneInternalPages = d.pages || {};
    renderInternalPages('');
  } catch(e) {
    list.innerHTML = `<div style="color:var(--red);font-size:11px;padding:8px">Erreur: ${e.message}</div>`;
  }
}

const INTERNAL_TYPE_LABELS = {
  page:'📄 Pages',article:'📰 Articles',secteur:'📍 Secteurs',
  guide:'📚 Guides',capture:'🎯 Captures',header:'🔝 Headers',footer:'🔻 Footers',
};
const INTERNAL_TYPE_COLORS = {
  page:'#1a4d7a',article:'#d97706',secteur:'#16a34a',
  guide:'#7c3aed',capture:'#0891b2',header:'#dc2626',footer:'#ea580c',
};

function renderInternalPages(q) {
  const list = document.getElementById('clone-internal-list');
  if (!list || !_cloneInternalPages) return;
  const ql = q.toLowerCase().trim();
  let html = '';
  let total = 0;
  for (const [type, rows] of Object.entries(_cloneInternalPages)) {
    const filtered = ql ? rows.filter(r => (r.title||'').toLowerCase().includes(ql) || (r.slug||'').toLowerCase().includes(ql)) : rows;
    if (!filtered.length) continue;
    total += filtered.length;
    const color = INTERNAL_TYPE_COLORS[type]||'#666';
    html += `<div style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:${color};padding:6px 4px 3px;margin-top:4px">${INTERNAL_TYPE_LABELS[type]||type} (${filtered.length})</div>`;
    for (const row of filtered) {
      const isSelected = _cloneSelectedId === row.id && _cloneSelectedType === type;
      const status = row.status||'draft';
      const statusColor = status==='published'?'#16a34a':'#d97706';
      html += `<div class="clone-internal-item${isSelected?' selected':''}"
        data-id="${row.id}" data-type="${type}" data-title="${(row.title||'').replace(/"/g,'&quot;')}"
        onclick="selectInternalPage(${row.id},'${type}','${(row.title||'').replace(/'/g,"\\'")}',this)"
        style="display:flex;align-items:center;gap:8px;padding:7px 9px;background:${isSelected?'rgba(26,77,122,.1)':'var(--bg3)'};border:1px solid ${isSelected?'var(--accent)':'var(--border)'};border-radius:6px;cursor:pointer;transition:all .15s;font-size:11px;margin-bottom:2px">
        <span style="width:6px;height:6px;border-radius:50%;background:${color};flex-shrink:0"></span>
        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text)">${row.title||'Sans titre'}</span>
        <span style="font-size:9px;color:${statusColor};flex-shrink:0">${status==='published'?'●':'○'}</span>
      </div>`;
    }
  }
  if (!total) html = '<div style="color:var(--text3);font-size:11px;padding:8px">Aucun résultat</div>';
  list.innerHTML = html;
}

function filterInternalPages(q) {
  renderInternalPages(q);
}

function selectInternalPage(id, type, title, el) {
  _cloneSelectedId   = id;
  _cloneSelectedType = type;
  _cloneSelectedLabel = title;
  document.querySelectorAll('.clone-internal-item').forEach(e => {
    e.style.background = 'var(--bg3)';
    e.style.borderColor = 'var(--border)';
  });
  if (el) { el.style.background = 'rgba(26,77,122,.1)'; el.style.borderColor = 'var(--accent)'; }
}

function setCloneMode(m) {
  BP.cloneMode = m;
  document.querySelectorAll('.clone-mode-btn').forEach(b => b.classList.toggle('active', b.dataset.cmode === m));
}
function setCloneUrl(url) {
  const inp = document.getElementById('clone-url');
  if (inp) inp.value = url;
}
function loadCloneHistory() {
  const list = document.getElementById('clone-recent-list');
  if (!list) return;
  const hs = JSON.parse(localStorage.getItem(CLONE_HK)||'[]');
  if (!hs.length) { list.innerHTML = '<div style="font-size:11px;color:var(--text3);padding:4px 0">Aucun clonage récent</div>'; return; }
  list.innerHTML = hs.slice(0,5).map(h => `
    <div class="clone-recent-item" onclick="${h.source_type==='internal'?`selectRecentInternal(${h.id||0},'${(h.type||'').replace(/'/g,"\\'")}','${(h.label||'').replace(/'/g,"\\'")}')` : `setCloneUrl('${(h.url||'').replace(/'/g,"\\'")}');setCloneSource('external')`}">
      <i class="fas ${h.source_type==='internal'?'fa-database':'fa-globe'}" style="color:var(--text3);font-size:10px;flex-shrink:0"></i>
      <span class="cri-url" title="${h.label||h.url||''}">${(h.label||h.url||'').substring(0,40)}</span>
      <span class="cri-mode">${h.mode}</span>
    </div>`).join('');
  // Auto-charger les pages internes au premier open
  if (_cloneSource === 'internal' && !_cloneInternalPages) loadInternalPages();
}
function selectRecentInternal(id, type, label) {
  setCloneSource('internal');
  if (!_cloneInternalPages) { loadInternalPages(); }
  _cloneSelectedId = id; _cloneSelectedType = type; _cloneSelectedLabel = label;
}
function pushCloneHistory(entry) {
  const hs = JSON.parse(localStorage.getItem(CLONE_HK)||'[]');
  const filtered = hs.filter(h => !(h.id===entry.id && h.type===entry.type && h.url===entry.url));
  filtered.unshift({...entry, time: new Date().toLocaleString('fr-FR')});
  if (filtered.length > 10) filtered.pop();
  localStorage.setItem(CLONE_HK, JSON.stringify(filtered));
  loadCloneHistory();
}
function setCloneStep(step, state) {
  const el = document.getElementById('cstep-'+step);
  if (!el) return;
  el.className = 'clone-step' + (state ? ' '+state : '');
  const icons = {fetch:'fa-download',extract:'fa-cut',claude:'fa-robot',dispatch:'fa-share-alt'};
  if (state==='done')   el.querySelector('.clone-step-icon').innerHTML = '<i class="fas fa-check"></i>';
  if (state==='active') el.querySelector('.clone-step-icon').innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
  if (!state)           el.querySelector('.clone-step-icon').innerHTML = `<i class="fas ${icons[step]}"></i>`;
}
function resetCloneSteps() {
  ['fetch','extract','claude','dispatch'].forEach(s => setCloneStep(s, ''));
}

async function startClone() {
  const btn     = document.getElementById('clone-generate-btn');
  const prog    = document.getElementById('clone-progress');
  const summary = document.getElementById('clone-summary');
  const doHtml    = document.getElementById('cd-html')?.checked    ?? true;
  const doCss     = document.getElementById('cd-css')?.checked     ?? true;
  const doJs      = document.getElementById('cd-js')?.checked      ?? true;
  const doMeta    = document.getElementById('cd-meta')?.checked    ?? true;
  const doReplace = document.getElementById('cd-replace')?.checked ?? false;

  // Validation selon source
  let payload = {mode: BP.cloneMode, context: BP.context};
  if (_cloneSource === 'internal') {
    if (!_cloneSelectedId || !_cloneSelectedType) {
      showToast('Sélectionnez une page dans la liste', 'warning'); return;
    }
    payload.internal_id   = _cloneSelectedId;
    payload.internal_type = _cloneSelectedType;
  } else {
    const url = document.getElementById('clone-url')?.value.trim();
    if (!url || !url.startsWith('http')) { showToast('URL invalide', 'warning'); return; }
    payload.url = url;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Clonage en cours...';
  summary.className = 'clone-summary';
  prog.className = 'clone-progress show';
  resetCloneSteps();

  try {
    setCloneStep('fetch','active');
    document.getElementById('clone-progress-label').textContent =
      _cloneSource==='internal' ? 'Lecture depuis la base de données...' : 'Récupération de la page...';

    const res  = await fetch(BP.cloneUrl, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload),
    });
    setCloneStep('fetch','done');
    setCloneStep('extract','active');
    document.getElementById('clone-progress-label').textContent = 'Extraction HTML/CSS/JS...';

    const data = await res.json();
    if (!data.success) throw new Error(data.error||'Erreur serveur');

    setCloneStep('extract','done');
    setCloneStep('claude','active');
    document.getElementById('clone-progress-label').textContent = 'Traitement par Claude AI...';
    // Claude a déjà traité côté serveur — on marque done
    setCloneStep('claude','done');
    setCloneStep('dispatch','active');
    document.getElementById('clone-progress-label').textContent = "Dispatch dans l'éditeur...";

    const dispatched = [];
    if (doHtml && data.html !== undefined) {
      const ta = document.getElementById('editor-html');
      if (ta) {
        ta.value = doReplace ? data.html : ((ta.value||'')+'\n\n'+data.html).trim();
        updateLineNumbers('html'); dispatched.push('<span class="idb idb-html">HTML</span>');
      }
    }
    if (doCss && data.css?.trim()) {
      const ta = document.getElementById('editor-css');
      if (ta) {
        ta.value = doReplace ? data.css : ((ta.value||'')+'\n\n'+data.css).trim();
        updateLineNumbers('css'); dispatched.push('<span class="idb idb-css">CSS</span>');
      }
    }
    if (doJs && data.js?.trim()) {
      const ta = document.getElementById('editor-js');
      if (ta) {
        ta.value = doReplace ? data.js : ((ta.value||'')+'\n\n'+data.js).trim();
        updateLineNumbers('js'); dispatched.push('<span class="idb idb-js">JS</span>');
      }
    }
    if (doMeta) {
      if (data.meta_title){const el=document.getElementById('meta-title');if(el)el.value=data.meta_title;}
      if (data.meta_desc) {const el=document.getElementById('meta-desc'); if(el)el.value=data.meta_desc;}
      if (data.slug){
        const el=document.getElementById('field-slug');if(el)el.value=data.slug;
        const el2=document.getElementById('field-slug-meta');if(el2)el2.value=data.slug;
      }
      dispatched.push('<span class="idb idb-meta">Meta SEO</span>');
    }

    setCloneStep('dispatch','done');
    document.getElementById('clone-progress-label').textContent = 'Terminé !';
    document.getElementById('clone-summary-text').textContent = data.summary||'Design cloné avec succès.';
    document.getElementById('clone-dispatch-info').innerHTML   = dispatched.join(' ');
    summary.className = 'clone-summary show';

    // Historique
    pushCloneHistory(_cloneSource==='internal'
      ? {source_type:'internal', id:_cloneSelectedId, type:_cloneSelectedType, label:_cloneSelectedLabel||data.source_label, mode:BP.cloneMode}
      : {source_type:'external', url:payload.url, label:payload.url, mode:BP.cloneMode}
    );

    markDirty(); updateStats(); switchTab('html');
    setTimeout(refreshPreview, 300);
    showToast('✨ Design cloné et dispatché !', 'success');

  } catch(e) {
    resetCloneSteps();
    document.getElementById('clone-progress-label').textContent = '❌ '+e.message;
    showToast('Erreur clonage : '+e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-magic"></i> Cloner avec Claude';
    setTimeout(() => { prog.className = 'clone-progress'; }, 8000);
  }
}
// ── PANEL DB ──────────────────────────────────────────────────────────────────
const DB_CONTEXT_LABELS = {
  page:'📄 Pages CMS',article:'📰 Articles Blog',secteur:'📍 Secteurs',
  guide:'📚 Guides Locaux',capture:'🎯 Pages Capture',header:'🔝 Headers',footer:'🔻 Footers',
};
const DB_CONTEXT_ICONS = {
  page:'fa-file-alt',article:'fa-newspaper',secteur:'fa-map-marker-alt',
  guide:'fa-book',capture:'fa-magnet',header:'fa-heading',footer:'fa-shoe-prints',
};
function loadDbPanel() {
  const body = document.getElementById('db-panel-body');
  if (!body) return;
  const ctx   = BP.context;
  const label = DB_CONTEXT_LABELS[ctx]||ctx;
  const icon  = DB_CONTEXT_ICONS[ctx]||'fa-database';
  let html = `<div class="db-context-badge ${ctx}"><i class="fas ${icon}"></i> ${label}</div>`;
  // Stats
  if (BP_DB_STATS && Object.keys(BP_DB_STATS).length) {
    html += `<div class="db-section"><div class="db-section-title"><i class="fas fa-chart-bar"></i> Statistiques</div><div class="db-stats-grid">`;
    for (const [k,v] of Object.entries(BP_DB_STATS)) {
      html += `<div class="db-stat-card"><div class="db-stat-num">${v}</div><div class="db-stat-label">${k}</div></div>`;
    }
    html += `</div></div>`;
  }
  // Variables
  if (BP_DB_DATA?.vars?.length) {
    html += `<div class="db-section"><div class="db-section-title"><i class="fas fa-code"></i> Variables <small style="font-weight:400;text-transform:none;letter-spacing:0">(clic = insérer)</small></div><div class="db-var-list">`;
    for (const v of BP_DB_DATA.vars) {
      const esc = v.key.replace(/'/g,"\\'");
      html += `<div class="db-var-item" onclick="insertDbVar('${esc}')">
        <span class="db-var-key">${v.key}</span>
        <span class="db-var-desc">${v.desc}</span>
        <span class="dv-copy"><i class="fas fa-plus"></i></span>
      </div>`;
    }
    html += `</div></div>`;
  }
  // Boucle PHP
  if (BP_DB_DATA?.loop) {
    const esc = BP_DB_DATA.loop.replace(/</g,'&lt;').replace(/>/g,'&gt;');
    html += `<div class="db-section"><div class="db-section-title"><i class="fas fa-redo"></i> Boucle PHP</div>
      <div class="db-loop-box"><div class="db-loop-title">→ Onglet PHP (clic = insérer)</div>
      <div class="db-loop-code" onclick="insertDbLoop()">${esc}</div></div></div>`;
  }
  // Table source
  if (BP_DB_DATA?.table) {
    html += `<div class="db-section"><div class="db-section-title"><i class="fas fa-table"></i> Table source</div>
      <div style="font-family:var(--font-mono);font-size:11px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:8px;color:var(--accent)">${BP_DB_DATA.table}</div></div>`;
  }
  // Identité site
  if (Object.keys(BP.siteIdentity).length) {
    const siteVarMap = {
      advisor_name:{key:'{{advisor.name}}',desc:'Nom conseiller'},
      advisor_city:{key:'{{advisor.city}}',desc:'Ville'},
      advisor_phone:{key:'{{advisor.phone}}',desc:'Téléphone'},
      advisor_email:{key:'{{advisor.email}}',desc:'Email'},
      site_name:{key:'{{site.name}}',desc:'Nom du site'},
    };
    html += `<div class="db-section"><div class="db-section-title"><i class="fas fa-id-card"></i> Identité site</div><div class="db-var-list">`;
    for (const [sk,sv] of Object.entries(BP.siteIdentity)) {
      if (!siteVarMap[sk]) continue;
      const vm = siteVarMap[sk];
      html += `<div class="db-var-item" onclick="insertDbVar('${vm.key.replace(/'/g,"\\'")}')">
        <span class="db-var-key">${vm.key}</span>
        <span class="db-var-desc">${sv||vm.desc}</span>
        <span class="dv-copy"><i class="fas fa-plus"></i></span>
      </div>`;
    }
    html += `</div></div>`;
  }
  html += `<button class="db-refresh-btn" onclick="loadDbPanel()"><i class="fas fa-sync-alt"></i> Actualiser</button>`;
  body.innerHTML = html;
}
function insertDbVar(varKey) {
  const ta = document.getElementById('editor-html');
  if (!ta) return;
  const s = ta.selectionStart;
  ta.value = ta.value.substring(0,s) + varKey + ta.value.substring(ta.selectionEnd);
  ta.selectionStart = ta.selectionEnd = s + varKey.length;
  ta.focus(); onEditorInput('html');
  showToast('Variable insérée', 'info');
}
function insertDbLoop() {
  if (!BP_DB_DATA?.loop) return;
  const ta = document.getElementById('editor-php');
  if (!ta) { showToast('Onglet PHP non disponible pour ce contexte', 'warning'); return; }
  const s = ta.selectionStart;
  ta.value = ta.value.substring(0,s) + BP_DB_DATA.loop + ta.value.substring(ta.selectionEnd);
  ta.selectionStart = ta.selectionEnd = s + BP_DB_DATA.loop.length;
  updateLineNumbers('php'); switchTab('php'); onEditorInput('php');
  showToast('Boucle PHP insérée', 'success');
}

// ── SNIPPETS ──────────────────────────────────────────────────────────────────
const SNIPPETS = {
  section:`<section class="section">\n  <div class="container">\n    <h2>Titre</h2>\n    <p>Contenu...</p>\n  </div>\n</section>`,
  div:`<div class="block">\n  \n</div>`,
  h2:`<h2>Titre</h2>`,
  p:`<p>Paragraphe.</p>`,
  a:`<a href="#" class="btn-primary">Lien</a>`,
  img:`<img src="/front/assets/images/hero-bordeaux.jpg" alt="Description" style="max-width:100%;height:auto">`,
  form:`<form method="post">\n  <input type="text" name="nom" placeholder="Nom" required>\n  <input type="email" name="email" placeholder="Email" required>\n  <button type="submit">Envoyer</button>\n</form>`,
  btn:`<a href="#" style="display:inline-block;padding:12px 28px;background:#1a4d7a;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">CTA</a>`,
  grid:`<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px">\n  <div>Col 1</div><div>Col 2</div><div>Col 3</div>\n</div>`,
  flex:`<div style="display:flex;align-items:center;gap:16px">\n  <div>Élément 1</div><div>Élément 2</div>\n</div>`,
};
function insertSnippet(k) {
  const ta = document.getElementById('editor-'+BP.currentTab);
  if (!ta || !SNIPPETS[k]) return;
  const s = ta.selectionStart, e = ta.selectionEnd;
  ta.value = ta.value.substring(0,s) + SNIPPETS[k] + ta.value.substring(e);
  ta.selectionStart = ta.selectionEnd = s + SNIPPETS[k].length;
  ta.focus(); onEditorInput(BP.currentTab);
}
function insertVar(n) {
  const ta = document.getElementById('editor-html');
  const s = ta.selectionStart, v = `{{${n}}}`;
  ta.value = ta.value.substring(0,s) + v + ta.value.substring(s);
  ta.selectionStart = ta.selectionEnd = s + v.length;
  ta.focus(); onEditorInput('html');
}

// ── BLOCS ─────────────────────────────────────────────────────────────────────
const BLOCKS = {
  hero:`<section style="background:linear-gradient(135deg,#1a4d7a,#0d2d4a);color:#fff;padding:80px 20px;text-align:center"><div style="max-width:800px;margin:0 auto"><h1 style="font-size:2.5rem;margin-bottom:16px;font-family:'Playfair Display',serif">Votre Expert Immobilier à Bordeaux</h1><p style="font-size:1.1rem;opacity:.85;margin-bottom:32px;line-height:1.7">Accompagnement personnalisé, expertise locale.</p><a href="#estimation" style="display:inline-block;padding:14px 36px;background:#d4a574;color:#fff;border-radius:8px;text-decoration:none;font-weight:700">Estimer mon bien</a></div></section>`,
  '2col':`<section style="padding:60px 20px"><div style="max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center"><div><h2 style="font-family:'Playfair Display',serif;color:#1a4d7a;margin-bottom:16px">Titre</h2><p style="color:#4b5563;line-height:1.8">Texte...</p></div><div style="background:#f9f6f3;border-radius:12px;padding:32px;text-align:center"><i class="fas fa-home" style="font-size:48px;color:#d4a574"></i></div></div></section>`,
  '3col':`<section style="padding:60px 20px;background:#f9f6f3"><div style="max-width:1100px;margin:0 auto"><h2 style="text-align:center;font-family:'Playfair Display',serif;color:#1a4d7a;margin-bottom:40px">Nos Services</h2><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px"><div style="background:#fff;border-radius:12px;padding:28px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.06)"><i class="fas fa-search" style="font-size:32px;color:#d4a574;margin-bottom:16px"></i><h3 style="color:#1a4d7a">Recherche</h3></div><div style="background:#fff;border-radius:12px;padding:28px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.06)"><i class="fas fa-handshake" style="font-size:32px;color:#d4a574;margin-bottom:16px"></i><h3 style="color:#1a4d7a">Négociation</h3></div><div style="background:#fff;border-radius:12px;padding:28px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.06)"><i class="fas fa-chart-line" style="font-size:32px;color:#d4a574;margin-bottom:16px"></i><h3 style="color:#1a4d7a">Estimation</h3></div></div></div></section>`,
  features:`<section style="padding:60px 20px"><div style="max-width:900px;margin:0 auto"><h2 style="text-align:center;font-family:'Playfair Display',serif;color:#1a4d7a;margin-bottom:40px">Pourquoi nous choisir ?</h2><div style="display:grid;grid-template-columns:1fr 1fr;gap:20px"><div style="display:flex;gap:16px"><div style="width:40px;height:40px;background:rgba(212,165,116,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-map-marker-alt" style="color:#d4a574"></i></div><div><h4 style="color:#1a4d7a">Expertise locale</h4><p style="color:#6b7280;font-size:14px">15 ans sur le marché bordelais.</p></div></div><div style="display:flex;gap:16px"><div style="width:40px;height:40px;background:rgba(212,165,116,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-user-tie" style="color:#d4a574"></i></div><div><h4 style="color:#1a4d7a">Accompagnement</h4><p style="color:#6b7280;font-size:14px">Un interlocuteur unique.</p></div></div></div></div></section>`,
  'title-text':`<section style="padding:60px 20px"><div style="max-width:800px;margin:0 auto;text-align:center"><span style="font-size:12px;text-transform:uppercase;letter-spacing:2px;color:#d4a574;font-weight:600">Sous-titre</span><h2 style="font-family:'Playfair Display',serif;color:#1a4d7a;font-size:2rem;margin:12px 0 20px">Titre principal</h2><p style="color:#4b5563;line-height:1.8">Votre texte ici.</p></div></section>`,
  faq:`<section style="padding:60px 20px"><div style="max-width:780px;margin:0 auto"><h2 style="font-family:'Playfair Display',serif;color:#1a4d7a;margin-bottom:32px;text-align:center">Questions fréquentes</h2><details style="border-bottom:1px solid #e5e7eb;padding:16px 0"><summary style="cursor:pointer;font-weight:600;color:#1a4d7a;list-style:none">▶ Combien de temps pour vendre ?</summary><p style="margin-top:12px;color:#4b5563">En moyenne 45 à 90 jours.</p></details></div></section>`,
  testimonial:`<section style="padding:60px 20px;background:#f9f6f3"><div style="max-width:600px;margin:0 auto;text-align:center"><div style="background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,.06)"><div style="color:#d4a574;font-size:28px;margin-bottom:16px">❝</div><p style="font-style:italic;color:#374151;line-height:1.8">"Eduardo a vendu notre appartement en 3 semaines."</p><div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-top:24px"><div style="width:44px;height:44px;background:#1a4d7a;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700">ML</div><div style="text-align:left"><div style="font-weight:600;color:#1a4d7a">Marie-Laure B.</div><div style="font-size:13px;color:#9ca3af">Chartrons ⭐⭐⭐⭐⭐</div></div></div></div></div></section>`,
  'image-text':`<section style="padding:60px 20px"><div style="max-width:1100px;margin:0 auto;display:flex;gap:48px;align-items:center"><div style="flex:1"><img src="/front/assets/images/hero-bordeaux.jpg" alt="Bordeaux" style="width:100%;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.12)"></div><div style="flex:1"><span style="color:#d4a574;font-size:12px;text-transform:uppercase;letter-spacing:2px;font-weight:600">Expertise</span><h2 style="font-family:'Playfair Display',serif;color:#1a4d7a;margin:12px 0 16px">Bordeaux, un marché en évolution</h2><p style="color:#4b5563;line-height:1.8">Votre texte ici.</p></div></div></section>`,
  cta:`<section style="background:#1a4d7a;padding:60px 20px;text-align:center"><div style="max-width:600px;margin:0 auto"><h2 style="color:#fff;font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:12px">Estimation gratuite en 24h</h2><p style="color:rgba(255,255,255,.8);margin-bottom:28px">Découvrez la valeur réelle de votre bien.</p><a href="/estimation" style="display:inline-block;padding:14px 40px;background:#d4a574;color:#fff;border-radius:8px;text-decoration:none;font-weight:700">Je veux mon estimation</a></div></section>`,
  'contact-form':`<section style="padding:60px 20px" id="contact"><div style="max-width:560px;margin:0 auto"><h2 style="font-family:'Playfair Display',serif;color:#1a4d7a;margin-bottom:24px;text-align:center">Contactez-moi</h2><form method="post" action="/contact" style="display:flex;flex-direction:column;gap:16px"><input type="text" name="nom" placeholder="Votre nom" required style="padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:15px"><input type="email" name="email" placeholder="Email" required style="padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:15px"><textarea name="message" rows="4" placeholder="Message" style="padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:15px;resize:vertical"></textarea><button type="submit" style="padding:13px;background:#1a4d7a;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer">Envoyer</button></form></div></section>`,
  stats:`<section style="padding:50px 20px;background:#1a4d7a"><div style="max-width:900px;margin:0 auto;display:flex;justify-content:space-around;text-align:center;flex-wrap:wrap;gap:24px"><div><div style="font-size:2.5rem;font-weight:800;color:#d4a574">+200</div><div style="color:rgba(255,255,255,.8);font-size:14px">Clients satisfaits</div></div><div><div style="font-size:2.5rem;font-weight:800;color:#d4a574">15</div><div style="color:rgba(255,255,255,.8);font-size:14px">Années d'expérience</div></div><div><div style="font-size:2.5rem;font-weight:800;color:#d4a574">48h</div><div style="color:rgba(255,255,255,.8);font-size:14px">Délai de réponse</div></div><div><div style="font-size:2.5rem;font-weight:800;color:#d4a574">98%</div><div style="color:rgba(255,255,255,.8);font-size:14px">Satisfaction</div></div></div></section>`,
  estimation:`<section style="padding:60px 20px;background:#f9f6f3" id="estimation"><div style="max-width:520px;margin:0 auto;text-align:center"><h2 style="font-family:'Playfair Display',serif;color:#1a4d7a;margin-bottom:8px">Estimez votre bien</h2><p style="color:#6b7280;margin-bottom:28px">Gratuit et sans engagement</p><form method="post" action="/estimation" style="display:flex;flex-direction:column;gap:14px"><select name="type_bien" style="padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:15px;background:#fff"><option>Type de bien</option><option>Appartement</option><option>Maison</option><option>Terrain</option></select><input type="text" name="adresse" placeholder="Adresse" style="padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:15px"><input type="email" name="email" placeholder="Email" required style="padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:15px"><button type="submit" style="padding:13px;background:#d4a574;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer">Recevoir mon estimation</button></form></div></section>`,
  breadcrumb:`<nav style="padding:12px 20px;background:#f9f6f3;border-bottom:1px solid #e5e7eb"><div style="max-width:1100px;margin:0 auto;font-size:13px;color:#6b7280"><a href="/" style="color:#1a4d7a;text-decoration:none">Accueil</a><span style="margin:0 8px">›</span><span>Page</span></div></nav>`,
  pagination:`<div style="display:flex;justify-content:center;gap:8px;padding:32px 20px"><a href="?page=1" style="padding:8px 14px;border:1px solid #d1d5db;border-radius:6px;color:#1a4d7a;text-decoration:none">‹</a><span style="padding:8px 14px;background:#1a4d7a;color:#fff;border-radius:6px">1</span><a href="?page=2" style="padding:8px 14px;border:1px solid #d1d5db;border-radius:6px;color:#1a4d7a;text-decoration:none">2</a><a href="?page=2" style="padding:8px 14px;border:1px solid #d1d5db;border-radius:6px;color:#1a4d7a;text-decoration:none">›</a></div>`,
};
function insertBlock(k) {
  if (!BLOCKS[k]) return;
  const ta = document.getElementById('editor-html');
  const p = ta.selectionEnd;
  const ins = '\n\n'+BLOCKS[k]+'\n';
  ta.value = ta.value.substring(0,p)+ins+ta.value.substring(p);
  ta.selectionStart = ta.selectionEnd = p+ins.length;
  ta.focus(); onEditorInput('html'); switchTab('html');
  showToast('Bloc inséré','info');
}

// ── KEYBOARD ──────────────────────────────────────────────────────────────────
function globalKey(e) {
  if ((e.ctrlKey||e.metaKey)&&e.key==='s')              { e.preventDefault(); saveContent('draft'); }
  if ((e.ctrlKey||e.metaKey)&&e.shiftKey&&e.key==='P') { e.preventDefault(); saveContent('published'); }
  if ((e.ctrlKey||e.metaKey)&&e.key==='p')              { e.preventDefault(); toggleCodeZone(); }
  if (e.key==='Escape') closeAllModals();
}
function handleKey(e, tab) {
  if (e.key==='Tab') {
    e.preventDefault();
    const ta=e.target, s=ta.selectionStart;
    ta.value=ta.value.substring(0,s)+'  '+ta.value.substring(ta.selectionEnd);
    ta.selectionStart=ta.selectionEnd=s+2;
    onEditorInput(tab);
  }
}

// ── META ──────────────────────────────────────────────────────────────────────
function initCharCounts() {
  updateCharCount(document.getElementById('meta-title'),'mc-title',65);
  updateCharCount(document.getElementById('meta-desc'), 'mc-desc', 160);
}
function updateCharCount(el,id,max) {
  if (!el) return;
  const len=(el.value||'').length;
  const d=document.getElementById(id); if (!d) return;
  d.textContent=`${len} / ${max} car.`;
  d.className='char-count'+(len>max?' danger':len>max*.9?' warn':'');
}
function syncTitles(el) {
  const o=document.getElementById(el.id==='field-title'?'field-title-meta':'field-title');
  if(o)o.value=el.value; BP.title=el.value;
}

// ── FORMAT / CLEAR / COPY / IMPORT ───────────────────────────────────────────
function formatCode() {
  const ta=document.getElementById('editor-html'); let c=ta.value;
  const vo=/^(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)$/i;
  let d=0;
  c=c.replace(/>\s*</g,'>\n<').split('\n').map(l=>{
    l=l.trim(); if(!l)return '';
    if(/^<\//.test(l))d=Math.max(0,d-1);
    const ind='  '.repeat(d)+l;
    const m=l.match(/^<([a-z][a-z0-9]*)/i);
    if(m&&!vo.test(m[1])&&!l.startsWith('</')&&!l.endsWith('/>'))
      if(!l.includes('</'+m[1]+'>'))d++;
    return ind;
  }).filter(l=>l).join('\n');
  ta.value=c; updateLineNumbers('html'); markDirty();
  showToast('Formaté','info');
}
function clearAll() {
  if(!confirm('Vider HTML, CSS et JS ?')) return;
  TABS.forEach(t=>{const ta=document.getElementById('editor-'+t);if(ta)ta.value='';updateLineNumbers(t);});
  markDirty();updateStats();refreshPreview();
}
function copyCode() {
  navigator.clipboard.writeText(document.getElementById('editor-'+BP.currentTab)?.value||'')
    .then(()=>showToast('Copié !','success'));
}
function openImport(){document.getElementById('modal-import').classList.add('show');}
function importHtml(){
  const v=document.getElementById('import-textarea').value;
  if(!v.trim())return;
  document.getElementById('editor-html').value=v;
  updateLineNumbers('html');markDirty();updateStats();refreshPreview();
  closeModal('modal-import');showToast('Importé','success');
}

// ── MODAL / TOAST ─────────────────────────────────────────────────────────────
function closeModal(id){document.getElementById(id)?.classList.remove('show');}
function closeAllModals(){document.querySelectorAll('.modal-overlay').forEach(m=>m.classList.remove('show'));}
document.querySelectorAll('.modal-overlay').forEach(m=>
  m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('show');})
);
function showToast(msg,type='info',dur=3500){
  const icons={success:'fas fa-check-circle',error:'fas fa-exclamation-circle',info:'fas fa-info-circle',warning:'fas fa-exclamation-triangle'};
  const div=document.createElement('div');
  div.className='toast '+type;
  div.innerHTML=`<i class="${icons[type]||icons.info}"></i> ${msg}`;
  document.getElementById('toast-container').appendChild(div);
  setTimeout(()=>{div.style.opacity='0';div.style.transform='translateX(30px)';div.style.transition='all .3s';setTimeout(()=>div.remove(),300);},dur);
}

// ── INIT STATS ────────────────────────────────────────────────────────────────
updateStats();
TABS.forEach(t=>updateSize(t));
</script>
</body>
</html>
