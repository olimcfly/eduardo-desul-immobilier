<?php
/**
 * ══════════════════════════════════════════════════════════════════
 * BUILDER PRO v3.8 — API Save Content
 * /admin/api/builder/save-content.php
 * ══════════════════════════════════════════════════════════════════
 */

define('ADMIN_ROUTER', true);
require_once dirname(__DIR__, 2) . '/includes/init.php';

header('Content-Type: application/json; charset=utf-8');

// ─── Auth — même logique que editor.php ──────────────────────────────────────
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$_isAuth = !empty($_SESSION['admin_logged_in'])
        || !empty($_SESSION['user_id'])
        || !empty($_SESSION['admin_id'])
        || !empty($_SESSION['logged_in'])
        || !empty($_SESSION['is_admin']);

if (!$_isAuth) {
    echo json_encode(['success'=>false,'error'=>'Non autorisé — session expirée ou invalide']); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Méthode non autorisée']); exit;
}

// ─── Paramètres ─────────────────────────────────────────────────────────────
$context  = trim($_POST['context']  ?? 'page');
$entityId = (int)($_POST['entity_id'] ?? 0);

if ($entityId <= 0) {
    echo json_encode(['success'=>false,'error'=>'entity_id manquant']); exit;
}

// ─── Mapping contexte → table / colonnes ─────────────────────────────────────
$CTX = [
    'page'    => ['table'=>'pages',        'col_title'=>'title', 'col_content'=>'content',     'col_css'=>'custom_css', 'col_js'=>'custom_js', 'col_slug'=>'slug',  'col_status'=>'status'],
    'secteur' => ['table'=>'secteurs',     'col_title'=>'nom',   'col_content'=>'content',     'col_css'=>'custom_css', 'col_js'=>'custom_js', 'col_slug'=>'slug',  'col_status'=>'status'],
    'article' => ['table'=>'articles',     'col_title'=>'title', 'col_content'=>'content',     'col_css'=>'custom_css', 'col_js'=>'custom_js', 'col_slug'=>'slug',  'col_status'=>'status'],
    'header'  => ['table'=>'headers',      'col_title'=>'name',  'col_content'=>'custom_html', 'col_css'=>'custom_css', 'col_js'=>'custom_js', 'col_slug'=>'name',  'col_status'=>'status'],
    'footer'  => ['table'=>'footers',      'col_title'=>'name',  'col_content'=>'custom_html', 'col_css'=>'custom_css', 'col_js'=>'custom_js', 'col_slug'=>'name',  'col_status'=>'status'],
    'capture' => ['table'=>'capture_pages','col_title'=>'name',  'col_content'=>'content',     'col_css'=>'custom_css', 'col_js'=>'custom_js', 'col_slug'=>'slug',  'col_status'=>'status'],
    'landing' => ['table'=>'pages',        'col_title'=>'title', 'col_content'=>'content',     'col_css'=>'custom_css', 'col_js'=>'custom_js', 'col_slug'=>'slug',  'col_status'=>'status'],
];

if (!isset($CTX[$context])) {
    echo json_encode(['success'=>false,'error'=>'Contexte invalide: '.$context]); exit;
}
$C = $CTX[$context];

// ─── Données à sauvegarder ───────────────────────────────────────────────────
$html     = $_POST['html_content']     ?? '';
$css      = $_POST['custom_css']       ?? '';
$js       = $_POST['custom_js']        ?? '';
$title    = trim($_POST['title']       ?? '');
$slug     = trim($_POST['slug']        ?? '');
$status   = trim($_POST['status']      ?? 'draft');
$template = trim($_POST['template']    ?? '');

// Meta
$metaTitle  = trim($_POST['meta_title']       ?? '');
$metaDesc   = trim($_POST['meta_description'] ?? '');
$metaKw     = trim($_POST['meta_keywords']    ?? '');
$ogTitle    = trim($_POST['og_title']         ?? '');
$ogDesc     = trim($_POST['og_description']   ?? '');

// Validation statut
$allowedStatus = ['published','draft','archived'];
if (!in_array($status, $allowedStatus)) $status = 'draft';

// ─── Construire le SET dynamiquement ─────────────────────────────────────────
$updates = [];
$params  = [];

$updates[] = "`{$C['col_content']}` = ?"; $params[] = $html;
$updates[] = "`updated_at` = NOW()";

try {
    $cols = [];
    $stmt = $pdo->query("DESCRIBE `{$C['table']}`");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cols[] = $row['Field'];
    }

    if ($C['col_css'] && in_array($C['col_css'], $cols)) {
        $updates[] = "`{$C['col_css']}` = ?"; $params[] = $css;
    }
    if ($C['col_js'] && in_array($C['col_js'], $cols)) {
        $updates[] = "`{$C['col_js']}` = ?"; $params[] = $js;
    }
    if (in_array('meta_title', $cols)) {
        $updates[] = "`meta_title` = ?"; $params[] = $metaTitle;
    }
    if (in_array('meta_description', $cols)) {
        $updates[] = "`meta_description` = ?"; $params[] = $metaDesc;
    }
    if (in_array('meta_keywords', $cols)) {
        $updates[] = "`meta_keywords` = ?"; $params[] = $metaKw;
    }
    if (in_array('og_title', $cols)) {
        $updates[] = "`og_title` = ?"; $params[] = $ogTitle;
    }
    if (in_array('og_description', $cols)) {
        $updates[] = "`og_description` = ?"; $params[] = $ogDesc;
    }
    if ($title && in_array($C['col_title'], $cols)) {
        $updates[] = "`{$C['col_title']}` = ?"; $params[] = $title;
    }
    if ($slug && in_array($C['col_slug'], $cols)) {
        $cleanSlug = strtolower(trim(preg_replace('/[^a-z0-9\-]/i', '-', iconv('UTF-8','ASCII//TRANSLIT',$slug)), '-'));
        $cleanSlug = preg_replace('/-+/', '-', $cleanSlug);
        $updates[] = "`{$C['col_slug']}` = ?"; $params[] = $cleanSlug;
    }
    if (in_array($C['col_status'], $cols)) {
        $updates[] = "`{$C['col_status']}` = ?"; $params[] = $status;
    }
    if ($template && in_array('template', $cols)) {
        $updates[] = "`template` = ?"; $params[] = $template;
    }

} catch (Exception $e) {
    $updates[] = "`{$C['col_css']}` = ?"; $params[] = $css;
    $updates[] = "`{$C['col_js']}` = ?";  $params[] = $js;
    $updates[] = "`{$C['col_status']}` = ?"; $params[] = $status;
}

$params[] = $entityId;
$sql = "UPDATE `{$C['table']}` SET " . implode(', ', $updates) . " WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare("SELECT id FROM `{$C['table']}` WHERE id = ?");
        $check->execute([$entityId]);
        if (!$check->fetch()) {
            echo json_encode(['success'=>false,'error'=>"Entité #$entityId non trouvée dans {$C['table']}"]); exit;
        }
    }

    // Log optionnel
    try {
        $pdo->prepare("INSERT INTO builder_logs (entity_type, entity_id, action, created_at) VALUES (?, ?, 'save', NOW())")
            ->execute([$context, $entityId]);
    } catch (Exception $e) {}

    echo json_encode([
        'success' => true,
        'message' => ucfirst($context) . ' sauvegardé',
        'status'  => $status,
        'id'      => $entityId,
    ]);

} catch (PDOException $e) {
    error_log('[Builder save-content] ' . $e->getMessage() . ' SQL: ' . $sql);
    echo json_encode(['success'=>false,'error'=>'Erreur DB : '.$e->getMessage()]);
}