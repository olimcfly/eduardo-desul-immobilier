<?php
/**
 * ══════════════════════════════════════════════════════════════
 * MODULE PAGES — Création / Édition  v1.0
 * /admin/modules/content/pages/action.php
 *
 * Appelé depuis index.php via action=create|edit
 * ou directement via ?page=pages&action=create|edit&id=X
 * ══════════════════════════════════════════════════════════════
 */

if (!defined('ADMIN_ROUTER')) {
    $initCandidates = [
        dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/init.php',
        dirname(dirname(dirname(__DIR__)))           . '/includes/init.php',
        dirname(dirname(__DIR__))                    . '/includes/init.php',
    ];
    foreach ($initCandidates as $init) {
        if (file_exists($init)) { require_once $init; break; }
    }
}
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db  = $pdo;

$id       = (int)($_GET['id'] ?? 0);
$isCreate = ($id === 0);
$page     = null;
$errors   = [];
$success  = false;

// ─── Colonnes disponibles ───
$availCols = [];
try {
    $availCols = $pdo->query("SHOW COLUMNS FROM pages")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo '<div style="color:red;padding:20px">Table pages introuvable</div>'; return;
}

$hasVisibility  = in_array('visibility',    $availCols);
$hasTemplate    = in_array('template',      $availCols);
$hasMetaKw      = in_array('meta_keywords', $availCols);
$hasOgImage     = in_array('og_image',      $availCols);
$hasCustomCss   = in_array('custom_css',    $availCols);
$hasCustomJs    = in_array('custom_js',     $availCols);
$hasGoogleIdx   = in_array('google_indexed',$availCols);
$hasPublishedAt = in_array('published_at',  $availCols);
$hasWordCount   = in_array('word_count',    $availCols);
$hasSeoScore    = in_array('seo_score',     $availCols);
$hasSemanticScore = in_array('semantic_score', $availCols);
$hasSeoTitle    = in_array('seo_title',     $availCols);
$hasSeoDesc     = in_array('seo_description', $availCols);
$hasSeoKw       = in_array('seo_keywords',  $availCols);
$hasNoindex     = in_array('noindex',       $availCols);
$hasFocusKw     = in_array('focus_keyword', $availCols);
$hasMainKw      = in_array('main_keyword',  $availCols);
$hasSecondaryKw = in_array('secondary_keywords', $availCols);
$hasH1          = in_array('h1',            $availCols);

// ─── Templates de pages (gérés dans Settings) ───
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS page_templates (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        template_key VARCHAR(120) NOT NULL UNIQUE,
        name VARCHAR(180) NOT NULL,
        description VARCHAR(255) DEFAULT NULL,
        fields_json LONGTEXT DEFAULT NULL,
        html_template LONGTEXT DEFAULT NULL,
        is_active TINYINT(1) UNSIGNED DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {}

$columnsToAdd = [
    'template_config_key' => "VARCHAR(120) DEFAULT NULL AFTER `template`",
    'template_data'       => "LONGTEXT DEFAULT NULL AFTER `template_config_key`",
];
foreach ($columnsToAdd as $col => $sqlDef) {
    if (!in_array($col, $availCols, true)) {
        try {
            // Validate against whitelist to prevent SQL injection
            if (isset($columnsToAdd[$col]) && $sqlDef === $columnsToAdd[$col]) {
                $pdo->exec("ALTER TABLE pages ADD COLUMN `" . $col . "` " . $sqlDef);
                $availCols[] = $col;
            }
        } catch (PDOException $e) {}
    }
}

$hasTemplateConfigKey = in_array('template_config_key', $availCols, true);
$hasTemplateData      = in_array('template_data', $availCols, true);

$pageTemplates = [];
try {
    $pageTemplates = $pdo->query("SELECT * FROM page_templates WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// ─── Chargement si édition ───
if (!$isCreate) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
    if (!$page) {
        echo '<div style="background:#fef2f2;color:#dc2626;padding:20px;border-radius:10px;margin:10px">Page introuvable (ID '.$id.')</div>';
        return;
    }
}

// ─── Traitement POST ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_save_page'])) {
    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug']  ?? '');

    if (!$title) $errors[] = 'Le titre est requis';

    if (!$slug && $title) {
        $slug = strtolower(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$title) ?: $title);
        $slug = preg_replace('/[^a-z0-9]+/','-',$slug);
        $slug = trim($slug,'-');
    }
    if (!$slug) $errors[] = 'Le slug est requis';

    // Unicité slug
    if ($slug && !$errors) {
        try {
            $chk = $pdo->prepare("SELECT id FROM pages WHERE slug=? AND id!=?");
            $chk->execute([$slug, $id]);
            if ($chk->fetchColumn()) $errors[] = 'Ce slug est déjà utilisé';
        } catch (PDOException $e) {}
    }

    if (!$errors) {
        $statusVal = in_array($_POST['status']??'draft',['draft','published','archived']) ? $_POST['status'] : 'draft';

        $fields = [
            'title'            => $title,
            'slug'             => $slug,
            'status'           => $statusVal,
            'content'          => $_POST['content']          ?? '',
            'meta_title'       => trim($_POST['meta_title']       ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'updated_at'       => date('Y-m-d H:i:s'),
        ];
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$fields['content'])));
        $wordCount = $plainText === '' ? 0 : count(preg_split('/\s+/u', $plainText));

        if ($hasTemplate)   $fields['template']      = trim($_POST['template']   ?? 'default') ?: 'default';
        if ($hasVisibility) $fields['visibility']    = in_array($_POST['visibility']??'public',['public','private']) ? $_POST['visibility'] : 'public';
        if ($hasMetaKw)     $fields['meta_keywords'] = trim($_POST['meta_keywords'] ?? '');
        if ($hasOgImage)    $fields['og_image']      = trim($_POST['og_image']      ?? '');
        if ($hasCustomCss)  $fields['custom_css']    = $_POST['custom_css']  ?? '';
        if ($hasCustomJs)   $fields['custom_js']     = $_POST['custom_js']   ?? '';
        if ($hasWordCount)  $fields['word_count']    = $wordCount;
        if ($hasSeoTitle)   $fields['seo_title']       = trim($_POST['seo_title'] ?? '');
        if ($hasSeoDesc)    $fields['seo_description'] = trim($_POST['seo_description'] ?? '');
        if ($hasSeoKw)      $fields['seo_keywords']    = trim($_POST['seo_keywords'] ?? '');
        if ($hasNoindex)    $fields['noindex']         = isset($_POST['noindex']) ? 1 : 0;
        if ($hasFocusKw)    $fields['focus_keyword']   = trim($_POST['focus_keyword'] ?? '');
        if ($hasMainKw)     $fields['main_keyword']    = trim($_POST['main_keyword'] ?? '');
        if ($hasSecondaryKw)$fields['secondary_keywords'] = trim($_POST['secondary_keywords'] ?? '');
        if ($hasH1)         $fields['h1']              = trim($_POST['h1'] ?? '');
        if ($hasTemplateConfigKey) $fields['template_config_key'] = trim($_POST['template_config_key'] ?? '');
        if ($hasTemplateData) {
            $templateData = $_POST['template_data'] ?? [];
            if (!is_array($templateData)) $templateData = [];
            $cleanTemplateData = [];
            foreach ($templateData as $k => $v) {
                $key = preg_replace('/[^a-z0-9_]/i', '', (string)$k);
                if ($key === '') continue;
                $cleanTemplateData[$key] = is_string($v) ? trim($v) : $v;
            }
            $fields['template_data'] = !empty($cleanTemplateData)
                ? json_encode($cleanTemplateData, JSON_UNESCAPED_UNICODE)
                : null;
        }

        if ($statusVal === 'published' && $hasPublishedAt) {
            if ($isCreate || ($page['status'] ?? '') !== 'published') {
                $fields['published_at'] = date('Y-m-d H:i:s');
            }
        }

        // Filtrer sur colonnes existantes
        $filtered = array_filter($fields, fn($k) => in_array($k, $availCols), ARRAY_FILTER_USE_KEY);

        try {
            if ($isCreate) {
                $filtered['created_at'] = date('Y-m-d H:i:s');
                $cols = array_keys($filtered);
                $pdo->prepare(
                    "INSERT INTO pages (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")"
                )->execute(array_values($filtered));
                $newId = (int)$pdo->lastInsertId();
                header("Location: ?page=pages&action=edit&id={$newId}&msg=created");
                exit;
            } else {
                $sets=[]; $vals=[];
                foreach ($filtered as $k=>$v) { $sets[]="`{$k}`=?"; $vals[]=$v; }
                $vals[] = $id;
                $pdo->prepare("UPDATE pages SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
                // Recharger
                $stmt = $pdo->prepare("SELECT * FROM pages WHERE id=?");
                $stmt->execute([$id]); $page = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur BDD : ' . $e->getMessage();
        }
    }
}

// ─── Helper valeur ───
$val = fn(string $k, string $d='') => htmlspecialchars((string)(($page[$k] ?? null) ?? $d));
$currentStatus = $page['status'] ?? 'draft';
$currentVis    = $page['visibility'] ?? 'public';
$pageLabel     = $isCreate ? 'Nouvelle page' : htmlspecialchars($page['title'] ?? 'Page');
$seoScoreVal   = (int)($page['seo_score'] ?? 0);
$semScoreVal   = (int)($page['semantic_score'] ?? 0);
$selectedTemplateKey = $page['template_config_key'] ?? '';
$templateData = json_decode((string)($page['template_data'] ?? ''), true);
if (!is_array($templateData)) $templateData = [];
$activeTemplate = null;
foreach ($pageTemplates as $tplRow) {
    if (($tplRow['template_key'] ?? '') === $selectedTemplateKey) {
        $activeTemplate = $tplRow;
        break;
    }
}
$activeTemplateFields = [];
if ($activeTemplate && !empty($activeTemplate['fields_json'])) {
    $decodedFields = json_decode((string)$activeTemplate['fields_json'], true);
    if (is_array($decodedFields)) $activeTemplateFields = $decodedFields;
}

// ─── CSRF ───
if (!isset($_SESSION['auth_csrf_token'])) $_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));
?>

<style>
/* ══════════════════════════════════════════
   PAGES ACTION — Création / Édition  v1.0
══════════════════════════════════════════ */
.pga-wrap {
    font-family: var(--font,'Inter',sans-serif);
    width: 100%;
    max-width: none;
}

.pga-bc {
    display: flex; align-items: center; gap: 8px;
    font-size: .78rem; color: var(--text-3,#9ca3af); margin-bottom: 18px;
}
.pga-bc a { color: var(--text-2,#6b7280); text-decoration: none; display: flex; align-items: center; gap: 5px; }
.pga-bc a:hover { color: var(--accent,#4f46e5); }
.pga-bc i { font-size: .6rem; }

/* Header */
.pga-header {
    background: var(--surface,#fff); border-radius: var(--radius-xl,16px);
    padding: 20px 28px; margin-bottom: 22px;
    border: 1px solid var(--border,#e5e7eb);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    position: relative; overflow: hidden;
}
.pga-header::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--accent,#4f46e5), var(--accent-l,#818cf8));
    opacity: .8;
}
.pga-header-left { display: flex; align-items: center; gap: 12px; }
.pga-header-icon {
    width: 46px; height: 46px; border-radius: 12px;
    background: linear-gradient(135deg, var(--accent,#4f46e5), var(--accent-l,#818cf8));
    display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem;
}
.pga-header-title { font-size: 1.15rem; font-weight: 700; color: var(--text,#111827); margin: 0; letter-spacing: -.01em; }
.pga-header-sub   { font-size: .78rem; color: var(--text-3,#9ca3af); margin: 2px 0 0; }
.pga-header-actions { display: flex; align-items: center; gap: 8px; }

/* Boutons */
.pga-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 20px; border-radius: var(--radius,10px);
    font-size: .82rem; font-weight: 600; cursor: pointer; border: none;
    transition: all .15s; font-family: var(--font,'Inter',sans-serif);
    text-decoration: none; line-height: 1.3;
}
.pga-btn-primary { background: var(--accent,#4f46e5); color: #fff; box-shadow: 0 1px 4px rgba(79,70,229,.22); }
.pga-btn-primary:hover { background: #4338ca; transform: translateY(-1px); color: #fff; }
.pga-btn-green   { background: #10b981; color: #fff; }
.pga-btn-green:hover { background: #059669; transform: translateY(-1px); color: #fff; }
.pga-btn-outline { background: var(--surface,#fff); color: var(--text-2,#6b7280); border: 1px solid var(--border,#e5e7eb); }
.pga-btn-outline:hover { border-color: var(--accent,#4f46e5); color: var(--accent,#4f46e5); }
.pga-btn-ghost   { background: transparent; color: var(--text-3,#9ca3af); border: 1px solid var(--border,#e5e7eb); }
.pga-btn-ghost:hover { color: var(--accent,#4f46e5); border-color: var(--accent,#4f46e5); }

/* Layout */
.pga-layout { display: grid; grid-template-columns: 1fr 310px; gap: 22px; align-items: start; }
@media(max-width:960px) { .pga-layout { grid-template-columns: 1fr; } }

/* Card */
.pga-card {
    background: var(--surface,#fff); border-radius: var(--radius-lg,12px);
    border: 1px solid var(--border,#e5e7eb); overflow: hidden; margin-bottom: 16px;
}
.pga-card-head {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 20px; border-bottom: 1px solid var(--border,#e5e7eb);
    background: var(--surface-2,#f9fafb);
}
.pga-card-head h3 {
    font-size: .82rem; font-weight: 700; color: var(--text,#111827);
    margin: 0; text-transform: uppercase; letter-spacing: .04em;
    display: flex; align-items: center; gap: 8px;
}
.pga-card-head h3 i { color: var(--accent,#4f46e5); font-size: .75rem; }
.pga-card-body { padding: 20px; }

/* Tabs */
.pga-tabs { display: flex; border-bottom: 2px solid var(--border,#e5e7eb); margin-bottom: 20px; gap: 2px; flex-wrap: wrap; }
.pga-tab {
    padding: 10px 18px; font-size: .8rem; font-weight: 600; cursor: pointer;
    color: var(--text-3,#9ca3af); border: none; background: transparent;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    transition: all .15s; font-family: var(--font,'Inter',sans-serif);
    display: flex; align-items: center; gap: 6px;
}
.pga-tab:hover { color: var(--text,#111827); }
.pga-tab.active { color: var(--accent,#4f46e5); border-bottom-color: var(--accent,#4f46e5); }
.pga-tab-panel { display: none; }
.pga-tab-panel.active { display: block; }

/* Champs */
.pga-field { margin-bottom: 18px; }
.pga-field:last-child { margin-bottom: 0; }
.pga-field label {
    display: block; font-size: .75rem; font-weight: 600;
    color: var(--text-2,#6b7280); margin-bottom: 6px;
    text-transform: uppercase; letter-spacing: .04em;
}
.pga-field label span { color: #ef4444; margin-left: 2px; }
.pga-field input[type=text],
.pga-field input[type=url],
.pga-field textarea,
.pga-field select {
    width: 100%; padding: 10px 14px;
    border: 1px solid var(--border,#e5e7eb); border-radius: 8px;
    background: var(--surface,#fff); color: var(--text,#111827);
    font-size: .85rem; font-family: var(--font,'Inter',sans-serif);
    transition: border-color .15s; box-sizing: border-box;
}
.pga-field input:focus, .pga-field textarea:focus, .pga-field select:focus {
    outline: none; border-color: var(--accent,#4f46e5);
    box-shadow: 0 0 0 3px rgba(79,70,229,.1);
}
.pga-field textarea { resize: vertical; min-height: 100px; }
.pga-field .hint { font-size: .72rem; color: var(--text-3,#9ca3af); margin-top: 5px; }

/* Slug preview */
.pga-slug-wrap {
    display: flex; align-items: center; gap: 8px;
    background: var(--surface-2,#f9fafb); border: 1px solid var(--border,#e5e7eb);
    border-radius: 8px; padding: 9px 14px; font-size: .82rem;
}
.pga-slug-wrap span { color: var(--text-3,#9ca3af); }
.pga-slug-wrap strong { color: var(--text,#111827); font-weight: 600; min-width: 60px; }
.pga-slug-edit-btn { margin-left: auto; color: var(--accent,#4f46e5); font-size: .72rem; cursor: pointer; text-decoration: underline; background: none; border: none; font-family: var(--font,'Inter',sans-serif); }

/* Status selector */
.pga-status-opts { display: flex; flex-direction: column; gap: 6px; }
.pga-status-opt {
    display: flex; align-items: center; gap: 10px; padding: 10px 14px;
    border: 2px solid var(--border,#e5e7eb); border-radius: 8px;
    cursor: pointer; transition: all .15s;
}
.pga-status-opt:hover { border-color: var(--accent,#4f46e5); }
.pga-status-opt.sel-published { border-color: #10b981; background: #ecfdf5; }
.pga-status-opt.sel-draft     { border-color: #f59e0b; background: #fffbeb; }
.pga-status-opt input[type=radio] { accent-color: var(--accent,#4f46e5); }
.pga-status-opt .s-lbl { font-size: .8rem; font-weight: 600; color: var(--text,#111827); }
.pga-status-opt .s-desc { font-size: .7rem; color: var(--text-3,#9ca3af); margin-top: 1px; }

/* Char counter */
.pga-counter { font-size: .68rem; text-align: right; margin-top: 3px; }
.pga-counter.ok   { color: #10b981; }
.pga-counter.warn { color: #f59e0b; }
.pga-counter.bad  { color: #ef4444; }

/* SERP Preview */
.pga-serp {
    background: var(--surface-2,#f9fafb); border: 1px solid var(--border,#e5e7eb);
    border-radius: 10px; padding: 16px; margin-top: 10px;
}
.pga-serp-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-3,#9ca3af); margin-bottom: 10px; }
.pga-serp-title { font-size: 1.05rem; color: #1a0dab; line-height: 1.3; }
.pga-serp-url   { font-size: .78rem; color: #006621; margin: 2px 0; }
.pga-serp-desc  { font-size: .85rem; color: #545454; line-height: 1.5; }

/* Flash */
.pga-flash {
    padding: 12px 18px; border-radius: 10px; font-size: .85rem; font-weight: 600;
    margin-bottom: 16px; display: flex; align-items: center; gap: 8px; animation: pgaFlash .3s;
}
.pga-flash.success { background: #d1fae5; color: #059669; border: 1px solid rgba(5,150,105,.12); }
.pga-flash.error   { background: #fef2f2; color: #dc2626; border: 1px solid rgba(220,38,38,.12); }
@keyframes pgaFlash { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:none} }
</style>

<div class="pga-wrap">

<!-- Breadcrumb -->
<div class="pga-bc">
    <a href="?page=pages"><i class="fas fa-file-alt"></i> Pages</a>
    <i class="fas fa-chevron-right"></i>
    <span><?= $pageLabel ?></span>
</div>

<!-- Flash -->
<?php if ($success): ?>
    <div class="pga-flash success"><i class="fas fa-check-circle"></i> Page enregistrée avec succès</div>
<?php endif; ?>
<?php if ($_GET['msg'] ?? '' === 'created'): ?>
    <div class="pga-flash success"><i class="fas fa-check-circle"></i> Page créée avec succès</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="pga-flash error"><i class="fas fa-exclamation-circle"></i> <?= implode(' — ', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<form method="POST" id="pgaForm">
<input type="hidden" name="_save_page" value="1">
<input type="hidden" name="_csrf"       value="<?= htmlspecialchars($_SESSION['auth_csrf_token']) ?>">
<input type="hidden" name="status"      id="pgaStatus" value="<?= htmlspecialchars($currentStatus) ?>">
<input type="hidden" name="slug"        id="pgaSlugHidden" value="<?= $val('slug') ?>">

<!-- En-tête -->
<div class="pga-header">
    <div class="pga-header-left">
        <div class="pga-header-icon"><i class="fas fa-file-alt"></i></div>
        <div>
            <h1 class="pga-header-title"><?= $isCreate ? 'Nouvelle page' : 'Modifier : '.$pageLabel ?></h1>
            <p class="pga-header-sub"><?= $isCreate ? 'Créer une nouvelle page CMS' : 'Édition de la page' ?></p>
        </div>
    </div>
    <div class="pga-header-actions">
        <?php if (!$isCreate): ?>
        <a href="/<?= htmlspecialchars($page['slug']??'') ?>" target="_blank" class="pga-btn pga-btn-outline">
            <i class="fas fa-external-link-alt"></i> Voir
        </a>
        <?php endif; ?>
        <a href="?page=pages" class="pga-btn pga-btn-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
        <button type="submit" onclick="PGA.setStatus('draft')" class="pga-btn pga-btn-outline">
            <i class="fas fa-save"></i> Brouillon
        </button>
        <button type="submit" onclick="PGA.setStatus('published')" class="pga-btn pga-btn-green">
            <i class="fas fa-check-circle"></i> Publier
        </button>
    </div>
</div>

<div class="pga-layout">

<!-- ═══ COLONNE PRINCIPALE ═══ -->
<div>

    <!-- Informations générales -->
    <div class="pga-card">
        <div class="pga-card-head"><h3><i class="fas fa-info-circle"></i> Informations générales</h3></div>
        <div class="pga-card-body">

            <div class="pga-field">
                <label>Titre de la page <span>*</span></label>
                <input type="text" name="title" id="pgaTitle"
                       value="<?= $val('title') ?>"
                       placeholder="Ex: À propos, Contact, Estimation immobilière…"
                       oninput="PGA.updateSlug()" required>
            </div>

            <!-- Slug -->
            <div class="pga-field">
                <label>URL (slug)</label>
                <div class="pga-slug-wrap">
                    <i class="fas fa-link" style="color:#9ca3af;font-size:.75rem"></i>
                    <span>eduardo-desul-immobilier.fr/</span>
                    <strong id="pgaSlugDisplay"><?= $val('slug') ?: 'votre-slug' ?></strong>
                    <button type="button" class="pga-slug-edit-btn" onclick="PGA.editSlug()">modifier</button>
                </div>
                <div id="pgaSlugEditWrap" style="display:none;margin-top:6px">
                    <input type="text" id="pgaSlugInput" value="<?= $val('slug') ?>"
                           oninput="PGA.syncSlug(this.value)"
                           placeholder="votre-slug-unique">
                    <div class="hint">Uniquement lettres minuscules, chiffres, tirets</div>
                </div>
            </div>

            <?php if ($hasTemplate): ?>
            <div class="pga-field">
                <label>Template de page</label>
                <select name="template">
                    <option value="default"  <?= ($page['template']??'default')==='default' ?'selected':'' ?>>Par défaut</option>
                    <option value="landing"  <?= ($page['template']??'')==='landing'  ?'selected':'' ?>>Landing page</option>
                    <option value="full"     <?= ($page['template']??'')==='full'     ?'selected':'' ?>>Pleine largeur</option>
                    <option value="legal"    <?= ($page['template']??'')==='legal'    ?'selected':'' ?>>Page légale</option>
                    <option value="contact"  <?= ($page['template']??'')==='contact'  ?'selected':'' ?>>Contact</option>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($hasTemplateConfigKey): ?>
            <div class="pga-field">
                <label>Structure éditoriale (Settings → Templates)</label>
                <select name="template_config_key" id="pgaTemplateConfig">
                    <option value="">Aucun modèle structuré</option>
                    <?php foreach ($pageTemplates as $tpl): ?>
                    <option value="<?= htmlspecialchars($tpl['template_key']) ?>"
                        <?= ($selectedTemplateKey === $tpl['template_key']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tpl['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="hint">Chaque page peut utiliser son propre modèle de champs sans éditeur typographique.</div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Contenu avec Tabs -->
    <div class="pga-card">
        <div class="pga-card-head"><h3><i class="fas fa-align-left"></i> Contenu &amp; SEO</h3></div>
        <div class="pga-card-body">

            <div class="pga-tabs">
                <button type="button" class="pga-tab active" onclick="PGA.tab(this,'tab-content')">
                    <i class="fas fa-file-alt"></i> Contenu
                </button>
                <button type="button" class="pga-tab" onclick="PGA.tab(this,'tab-seo')">
                    <i class="fas fa-search"></i> SEO
                </button>
                <?php if ($hasCustomCss || $hasCustomJs): ?>
                <button type="button" class="pga-tab" onclick="PGA.tab(this,'tab-code')">
                    <i class="fas fa-code"></i> Code
                </button>
                <?php endif; ?>
            </div>

            <!-- Tab : Contenu -->
            <div class="pga-tab-panel active" id="tab-content">
                <?php if (!empty($activeTemplateFields)): ?>
                <div class="pga-card" style="margin-bottom:16px">
                    <div class="pga-card-head"><h3><i class="fas fa-list-check"></i> Champs du modèle</h3></div>
                    <div class="pga-card-body">
                        <?php foreach ($activeTemplateFields as $field):
                            $fKey = preg_replace('/[^a-z0-9_]/i', '', (string)($field['key'] ?? ''));
                            if ($fKey === '') continue;
                            $fLabel = $field['label'] ?? $fKey;
                            $fType = $field['type'] ?? 'text';
                            $fPlaceholder = $field['placeholder'] ?? '';
                            $fValue = $templateData[$fKey] ?? '';
                        ?>
                        <div class="pga-field">
                            <label><?= htmlspecialchars($fLabel) ?></label>
                            <?php if ($fType === 'textarea'): ?>
                                <textarea name="template_data[<?= htmlspecialchars($fKey) ?>]" rows="4"
                                          placeholder="<?= htmlspecialchars($fPlaceholder) ?>"><?= htmlspecialchars((string)$fValue) ?></textarea>
                            <?php else: ?>
                                <input type="<?= $fType === 'url' ? 'url' : 'text' ?>"
                                       name="template_data[<?= htmlspecialchars($fKey) ?>]"
                                       value="<?= htmlspecialchars((string)$fValue) ?>"
                                       placeholder="<?= htmlspecialchars($fPlaceholder) ?>">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="pga-field" style="margin-bottom:0">
                    <label>Contenu HTML / Texte (sans éditeur typographique)</label>
                    <textarea name="content" id="pgaContent" rows="20"
                              style="font-family:monospace;font-size:.82rem;line-height:1.6"
                              placeholder="Contenu de votre page...&#10;&#10;Vous pouvez utiliser du HTML ou du texte simple.&#10;Pour un design avancé, utilisez le Builder Pro (bouton en haut à droite)."><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
                    <div class="hint">Saisie structurée avec champs + HTML libre, sans éditeur de mise en forme visuelle.</div>
                </div>
            </div>

            <!-- Tab : SEO -->
            <div class="pga-tab-panel" id="tab-seo">
                <div class="pga-field">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" id="pgaMetaTitle"
                           value="<?= $val('meta_title') ?>"
                           placeholder="Titre SEO · 50-65 caractères recommandés"
                           oninput="PGA.countChars('pgaMetaTitle','pgaMTCount',50,65);PGA.updateSerp()">
                    <div class="pga-counter" id="pgaMTCount"><?= strlen($page['meta_title']??'') ?> / 65</div>
                </div>
                <?php if ($hasSeoTitle): ?>
                <div class="pga-field">
                    <label>SEO Title (prioritaire)</label>
                    <input type="text" name="seo_title" id="pgaSeoTitle"
                           value="<?= $val('seo_title') ?>"
                           placeholder="Optionnel : remplace Meta Title dans le rendu SEO"
                           oninput="PGA.updateSerp();PGA.updateLiveScores()">
                </div>
                <?php endif; ?>
                <div class="pga-field">
                    <label>Meta Description</label>
                    <textarea name="meta_description" id="pgaMetaDesc" rows="3"
                              placeholder="Description SEO · 120-160 caractères recommandés"
                              oninput="PGA.countChars('pgaMetaDesc','pgaMDCount',120,160);PGA.updateSerp()"><?= $val('meta_description') ?></textarea>
                    <div class="pga-counter" id="pgaMDCount"><?= strlen($page['meta_description']??'') ?> / 160</div>
                </div>
                <?php if ($hasSeoDesc): ?>
                <div class="pga-field">
                    <label>SEO Description (prioritaire)</label>
                    <textarea name="seo_description" id="pgaSeoDesc" rows="3"
                              placeholder="Optionnel : remplace Meta Description dans le rendu SEO"
                              oninput="PGA.updateSerp();PGA.updateLiveScores()"><?= $val('seo_description') ?></textarea>
                </div>
                <?php endif; ?>
                <?php if ($hasMetaKw): ?>
                <div class="pga-field">
                    <label>Mots-clés (séparés par virgule)</label>
                    <input type="text" name="meta_keywords" value="<?= $val('meta_keywords') ?>" placeholder="immobilier bordeaux, achat maison bordeaux…">
                </div>
                <?php endif; ?>
                <?php if ($hasSeoKw): ?>
                <div class="pga-field">
                    <label>SEO Keywords (prioritaires)</label>
                    <input type="text" name="seo_keywords" id="pgaSeoKeywords" value="<?= $val('seo_keywords') ?>"
                           oninput="PGA.updateLiveScores()"
                           placeholder="mot-clé principal, variante locale, intention…">
                </div>
                <?php endif; ?>
                <?php if ($hasFocusKw || $hasMainKw || $hasSecondaryKw): ?>
                <div class="pga-field">
                    <label>Mot-clé focus</label>
                    <input type="text" name="focus_keyword" id="pgaFocusKeyword" value="<?= $val('focus_keyword') ?>"
                           oninput="PGA.updateLiveScores()" placeholder="ex : estimation maison bordeaux">
                </div>
                <?php if ($hasMainKw): ?>
                <div class="pga-field">
                    <label>Mot-clé principal</label>
                    <input type="text" name="main_keyword" id="pgaMainKeyword" value="<?= $val('main_keyword') ?>"
                           oninput="PGA.updateLiveScores()">
                </div>
                <?php endif; ?>
                <?php if ($hasSecondaryKw): ?>
                <div class="pga-field">
                    <label>Mots-clés secondaires</label>
                    <input type="text" name="secondary_keywords" id="pgaSecondaryKeywords" value="<?= $val('secondary_keywords') ?>"
                           oninput="PGA.updateLiveScores()" placeholder="séparés par virgule">
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($hasH1): ?>
                <div class="pga-field">
                    <label>H1 (optionnel)</label>
                    <input type="text" name="h1" id="pgaH1" value="<?= $val('h1') ?>" oninput="PGA.updateLiveScores()">
                </div>
                <?php endif; ?>
                <?php if ($hasNoindex): ?>
                <div class="pga-field" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="noindex" id="pgaNoindex" <?= !empty($page['noindex']) ? 'checked' : '' ?>>
                    <label for="pgaNoindex" style="margin:0;text-transform:none;letter-spacing:normal">Noindex (ne pas indexer cette page)</label>
                </div>
                <?php endif; ?>
                <?php if ($hasOgImage): ?>
                <div class="pga-field">
                    <label>Image OG (Open Graph)</label>
                    <input type="url" name="og_image" value="<?= $val('og_image') ?>" placeholder="https://…">
                    <div class="hint">Image affichée lors des partages sur les réseaux sociaux (1200×630px recommandé)</div>
                </div>
                <?php endif; ?>

                <!-- SERP Preview -->
                <div class="pga-serp">
                    <div class="pga-serp-label">Aperçu Google</div>
                    <div class="pga-serp-title" id="pgaSerpTitle"><?= $val('seo_title') ?: ($val('meta_title') ?: $val('title')) ?></div>
                    <div class="pga-serp-url">https://eduardo-desul-immobilier.fr/<span id="pgaSerpSlug"><?= $val('slug') ?></span></div>
                    <div class="pga-serp-desc" id="pgaSerpDesc"><?= $val('seo_description') ?: $val('meta_description') ?></div>
                </div>
            </div>

            <!-- Tab : Code -->
            <?php if ($hasCustomCss || $hasCustomJs): ?>
            <div class="pga-tab-panel" id="tab-code">
                <?php if ($hasCustomCss): ?>
                <div class="pga-field">
                    <label>CSS personnalisé</label>
                    <textarea name="custom_css" rows="10" style="font-family:monospace;font-size:.82rem"
                              placeholder="/* CSS spécifique à cette page */"><?= htmlspecialchars($page['custom_css'] ?? '') ?></textarea>
                </div>
                <?php endif; ?>
                <?php if ($hasCustomJs): ?>
                <div class="pga-field">
                    <label>JS personnalisé</label>
                    <textarea name="custom_js" rows="8" style="font-family:monospace;font-size:.82rem"
                              placeholder="// JavaScript spécifique à cette page"><?= htmlspecialchars($page['custom_js'] ?? '') ?></textarea>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div><!-- /.main -->

<!-- ═══ SIDEBAR ═══ -->
<div>

    <!-- Statut -->
    <div class="pga-card">
        <div class="pga-card-head"><h3><i class="fas fa-toggle-on"></i> Statut</h3></div>
        <div class="pga-card-body">
            <div class="pga-status-opts">
                <label class="pga-status-opt <?= $currentStatus==='published'?'sel-published':'' ?>" id="pgaOptPub"
                       onclick="PGA.setStatus('published')">
                    <input type="radio" name="_status_ui" value="published" <?= $currentStatus==='published'?'checked':'' ?>>
                    <div>
                        <div class="s-lbl" style="color:#059669"><i class="fas fa-check-circle"></i> Publié</div>
                        <div class="s-desc">Visible sur le site</div>
                    </div>
                </label>
                <label class="pga-status-opt <?= $currentStatus==='draft'?'sel-draft':'' ?>" id="pgaOptDraft"
                       onclick="PGA.setStatus('draft')">
                    <input type="radio" name="_status_ui" value="draft" <?= $currentStatus==='draft'?'checked':'' ?>>
                    <div>
                        <div class="s-lbl" style="color:#d97706"><i class="fas fa-pencil-alt"></i> Brouillon</div>
                        <div class="s-desc">Non visible en ligne</div>
                    </div>
                </label>
                <label class="pga-status-opt" id="pgaOptArc"
                       onclick="PGA.setStatus('archived')">
                    <input type="radio" name="_status_ui" value="archived" <?= $currentStatus==='archived'?'checked':'' ?>>
                    <div>
                        <div class="s-lbl" style="color:#9ca3af"><i class="fas fa-archive"></i> Archivé</div>
                        <div class="s-desc">Retiré du site</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Visibilité -->
    <?php if ($hasVisibility): ?>
    <div class="pga-card">
        <div class="pga-card-head"><h3><i class="fas fa-eye"></i> Visibilité</h3></div>
        <div class="pga-card-body">
            <div class="pga-field" style="margin-bottom:0">
                <select name="visibility">
                    <option value="public"  <?= $currentVis==='public' ?'selected':'' ?>>🌐 Publique</option>
                    <option value="private" <?= $currentVis==='private'?'selected':'' ?>>🔒 Privée</option>
                </select>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions rapides -->
    <div class="pga-card">
        <div class="pga-card-head"><h3><i class="fas fa-chart-line"></i> SEO &amp; Sémantique</h3></div>
        <div class="pga-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                <div style="padding:10px;border:1px solid var(--border,#e5e7eb);border-radius:10px;background:var(--surface-2,#f9fafb);text-align:center">
                    <div style="font-size:.68rem;color:var(--text-3,#9ca3af);text-transform:uppercase;letter-spacing:.05em">SEO</div>
                    <div id="pgaSeoScoreLive" style="font-size:1.3rem;font-weight:800;color:#2563eb"><?= $seoScoreVal ?></div>
                </div>
                <div style="padding:10px;border:1px solid var(--border,#e5e7eb);border-radius:10px;background:var(--surface-2,#f9fafb);text-align:center">
                    <div style="font-size:.68rem;color:var(--text-3,#9ca3af);text-transform:uppercase;letter-spacing:.05em">Sémantique</div>
                    <div id="pgaSemanticScoreLive" style="font-size:1.3rem;font-weight:800;color:#059669"><?= $semScoreVal ?></div>
                </div>
            </div>
            <ul id="pgaSeoHints" style="margin:0;padding-left:16px;font-size:.74rem;color:var(--text-2,#6b7280);line-height:1.5">
                <li>Ajoutez un mot-clé principal et des variantes.</li>
                <li>Renseignez les méta (title/description) pour Google.</li>
                <li>Utilisez un H1 et une structure claire.</li>
            </ul>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="pga-card">
        <div class="pga-card-head"><h3><i class="fas fa-bolt"></i> Actions</h3></div>
        <div class="pga-card-body" style="display:flex;flex-direction:column;gap:8px">
            <button type="submit" class="pga-btn pga-btn-primary" style="width:100%;justify-content:center">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <?php if (!$isCreate): ?>
            <button type="button" class="pga-btn pga-btn-outline" style="width:100%;justify-content:center"
                    onclick="PGA.duplicate(<?= $id ?>)">
                <i class="fas fa-copy"></i> Dupliquer
            </button>
            <button type="button" class="pga-btn" style="width:100%;justify-content:center;background:#fef2f2;color:#dc2626;border:1px solid rgba(220,38,38,.2)"
                    onclick="PGA.deletePage(<?= $id ?>, '<?= addslashes(htmlspecialchars($page['title']??'')) ?>')">
                <i class="fas fa-trash"></i> Supprimer
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Infos -->
    <?php if (!$isCreate): ?>
    <div class="pga-card">
        <div class="pga-card-head"><h3><i class="fas fa-clock"></i> Informations</h3></div>
        <div class="pga-card-body" style="font-size:.78rem;color:var(--text-2,#6b7280)">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <span>Créée le</span>
                <strong><?= !empty($page['created_at']) ? date('d/m/Y H:i', strtotime($page['created_at'])) : '—' ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <span>Modifiée le</span>
                <strong><?= !empty($page['updated_at']) ? date('d/m/Y H:i', strtotime($page['updated_at'])) : '—' ?></strong>
            </div>
            <?php if ($hasSeoScore && !empty($page['seo_score'])): ?>
            <div style="display:flex;justify-content:space-between">
                <span>Score SEO</span>
                <strong style="color:#10b981"><?= (int)$page['seo_score'] ?>%</strong>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.sidebar -->
</div><!-- /.layout -->
</form>
</div>

<script>
const PGA = {
    apiUrl: '/admin/modules/content/pages/api/pages.php',

    setStatus(v) {
        document.getElementById('pgaStatus').value = v;
        ['published','draft','archived'].forEach(s => {
            const el = document.getElementById('pgaOpt'+s.charAt(0).toUpperCase()+s.slice(1));
            if (!el) return;
            el.classList.remove('sel-published','sel-draft');
            if (s === v) el.classList.add('sel-'+s);
        });
    },

    updateSlug() {
        const title = document.getElementById('pgaTitle').value;
        // Ne modifier le slug que si création ou slug encore vide
        const current = document.getElementById('pgaSlugHidden').value;
        if (current && document.getElementById('pgaSlugEditWrap').style.display !== 'block') return;
        const slug = title.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
            .replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        this.syncSlug(slug);
    },
    syncSlug(v) {
        document.getElementById('pgaSlugHidden').value = v;
        document.getElementById('pgaSlugDisplay').textContent = v || 'votre-slug';
        const inp = document.getElementById('pgaSlugInput');
        if (inp) inp.value = v;
        const ss = document.getElementById('pgaSerpSlug');
        if (ss) ss.textContent = v;
    },
    editSlug() {
        const w = document.getElementById('pgaSlugEditWrap');
        w.style.display = w.style.display === 'none' ? 'block' : 'none';
    },

    tab(btn, id) {
        document.querySelectorAll('.pga-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.pga-tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        const p = document.getElementById(id); if (p) p.classList.add('active');
    },

    countChars(inputId, countId, min, max) {
        const el = document.getElementById(inputId);
        const ct = document.getElementById(countId);
        if (!el || !ct) return;
        const n = el.value.length;
        ct.textContent = n + ' / ' + max;
        ct.className = 'pga-counter ' + (n >= min && n <= max ? 'ok' : n > max ? 'bad' : 'warn');
    },

    updateSerp() {
        const seoTitle = document.getElementById('pgaSeoTitle');
        const seoDesc = document.getElementById('pgaSeoDesc');
        const mt = document.getElementById('pgaMetaTitle');
        const md = document.getElementById('pgaMetaDesc');
        const st = document.getElementById('pgaSerpTitle');
        const sd = document.getElementById('pgaSerpDesc');
        if (st) st.textContent = (seoTitle && seoTitle.value) || (mt && mt.value) || document.getElementById('pgaTitle').value || '…';
        if (sd) sd.textContent = (seoDesc && seoDesc.value) || (md && md.value) || '…';
    },

    updateLiveScores() {
        const title = (document.getElementById('pgaTitle')?.value || '').trim();
        const metaTitle = (document.getElementById('pgaMetaTitle')?.value || '').trim();
        const metaDesc = (document.getElementById('pgaMetaDesc')?.value || '').trim();
        const seoTitle = (document.getElementById('pgaSeoTitle')?.value || '').trim();
        const seoDesc = (document.getElementById('pgaSeoDesc')?.value || '').trim();
        const h1 = (document.getElementById('pgaH1')?.value || '').trim();
        const content = document.getElementById('pgaContent')?.value || '';
        const focus = (document.getElementById('pgaFocusKeyword')?.value || '').trim();
        const main = (document.getElementById('pgaMainKeyword')?.value || '').trim();
        const secondary = (document.getElementById('pgaSecondaryKeywords')?.value || '').trim();
        const kws = (document.getElementById('pgaSeoKeywords')?.value || '').trim();

        const plain = content
            .replace(/<[^>]+>/g, ' ')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
        const words = plain ? plain.split(' ').length : 0;
        const targetKeyword = (focus || main || (kws.split(',')[0] || '')).trim().toLowerCase();
        const kwCount = targetKeyword && plain ? (plain.match(new RegExp(targetKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length : 0;

        let seo = 0;
        let sem = 0;
        const hints = [];

        if ((seoTitle || metaTitle).length >= 45 && (seoTitle || metaTitle).length <= 65) seo += 22; else hints.push('Meta title/SEO title : visez 45-65 caractères.');
        if ((seoDesc || metaDesc).length >= 110 && (seoDesc || metaDesc).length <= 160) seo += 22; else hints.push('Meta description : visez 110-160 caractères.');
        if (targetKeyword) seo += 18; else hints.push('Ajoutez un mot-clé focus/principal.');
        if (title.length >= 10) seo += 10;
        if (h1.length >= 10 || /<h1[\s>]/i.test(content)) seo += 14; else hints.push('Ajoutez un H1 explicite.');
        if (words >= 120) seo += 14; else hints.push('Ajoutez plus de contenu utile (120+ mots).');

        if (words >= 200) sem += 30; else if (words >= 120) sem += 18; else hints.push('Le texte est court pour la sémantique.');
        if (targetKeyword && kwCount > 0) sem += 25; else hints.push('Le mot-clé principal n’apparaît pas encore dans le contenu.');
        if (secondary.split(',').filter(Boolean).length >= 2) sem += 20;
        if (/(<h2[\s>]|<h3[\s>])/i.test(content)) sem += 15; else hints.push('Structurez avec H2/H3.');
        if (/[.!?]/.test(plain)) sem += 10;

        seo = Math.min(100, seo);
        sem = Math.min(100, sem);

        const seoEl = document.getElementById('pgaSeoScoreLive');
        const semEl = document.getElementById('pgaSemanticScoreLive');
        const hintsEl = document.getElementById('pgaSeoHints');
        if (seoEl) seoEl.textContent = String(seo);
        if (semEl) semEl.textContent = String(sem);
        if (hintsEl) {
            const uniqHints = Array.from(new Set(hints)).slice(0, 4);
            hintsEl.innerHTML = uniqHints.length
                ? uniqHints.map(h => `<li>${h}</li>`).join('')
                : '<li>Excellente base SEO/sémantique. Continuez avec un maillage interne.</li>';
        }
    },

    async duplicate(id) {
        if (!confirm('Dupliquer cette page ?')) return;
        const fd = new FormData(); fd.append('action','duplicate'); fd.append('id',id);
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        d.success ? location.reload() : alert(d.error||'Erreur');
    },

    async deletePage(id, title) {
        if (!confirm(`Supprimer « ${title} » ? Action irréversible.`)) return;
        const fd = new FormData(); fd.append('action','delete'); fd.append('id',id);
        const r = await fetch(this.apiUrl, {method:'POST',body:fd});
        const d = await r.json();
        if (d.success) window.location.href = '?page=pages&msg=deleted';
        else alert(d.error||'Erreur');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    PGA.countChars('pgaMetaTitle','pgaMTCount',50,65);
    PGA.countChars('pgaMetaDesc','pgaMDCount',120,160);
    PGA.updateSerp();
    PGA.updateLiveScores();
    ['pgaTitle','pgaMetaTitle','pgaMetaDesc','pgaSeoTitle','pgaSeoDesc','pgaFocusKeyword','pgaMainKeyword','pgaSecondaryKeywords','pgaSeoKeywords','pgaH1','pgaContent']
        .forEach(id => document.getElementById(id)?.addEventListener('input', () => PGA.updateLiveScores()));
    // Sync slug vide en création
    <?php if ($isCreate): ?>
    document.getElementById('pgaSlugEditWrap').style.display = 'block';
    <?php endif; ?>
});
</script>
