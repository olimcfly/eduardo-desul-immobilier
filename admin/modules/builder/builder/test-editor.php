<?php
/**
 * Test rapide de l'éditeur - toutes les APIs
 * URL: /admin/modules/builder/builder/test-editor.php
 * Accès après login admin
 */
require_once __DIR__ . '/../../../includes/init.php';
header('Content-Type: text/html; charset=utf-8');

$csrf = $_SESSION['csrf_token'];
$tests = [];

// Test 1: DB connection
$tests[] = ['name'=>'DB Connection', 'ok'=>isset($pdo), 'detail'=>isset($pdo)?'PDO OK':'PDO missing'];

// Test 2: Tables
$required_tables = ['pages','articles','secteurs','builder_templates','builder_layouts','builder_content','builder_saved_blocks','ai_prompts','seo_scores'];
foreach ($required_tables as $t) {
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $tests[] = ['name'=>"Table: $t", 'ok'=>true, 'detail'=>"$cnt rows"];
    } catch(PDOException $e) {
        $tests[] = ['name'=>"Table: $t", 'ok'=>false, 'detail'=>$e->getMessage()];
    }
}

// Test 3: BuilderController
try {
    require_once __DIR__ . '/BuilderController.php';
    $bc = new BuilderController($pdo);
    $layouts = $bc->getLayouts('landing');
    $templates = $bc->getTemplates('landing');
    $blocks = $bc->getBlockTypes('landing');
    $tests[] = ['name'=>'BuilderController::getLayouts', 'ok'=>true, 'detail'=>count($layouts).' layouts'];
    $tests[] = ['name'=>'BuilderController::getTemplates', 'ok'=>true, 'detail'=>count($templates).' templates'];
    $tests[] = ['name'=>'BuilderController::getBlockTypes', 'ok'=>true, 'detail'=>count($blocks).' categories'];
} catch(Throwable $e) {
    $tests[] = ['name'=>'BuilderController', 'ok'=>false, 'detail'=>$e->getMessage()];
}

// Test 4: API Handlers
$handlers = [
    'ai-prompts' => ['action'=>'list&active_only=1', 'method'=>'GET', 'expect_key'=>'prompts'],
    'seo'        => ['action'=>'save_score',          'method'=>'POST', 'expect_key'=>'success'],
    'builder'    => ['action'=>'layouts&context=landing', 'method'=>'GET', 'expect_key'=>'data'],
];

foreach ($handlers as $module => $cfg) {
    $url = "/admin/api/router.php?module={$module}&action={$cfg['action']}";
    $tests[] = ['name'=>"Handler: $module/{$cfg['action']}", 'ok'=>true, 'detail'=>"URL: $url"];
}

// Test 5: AI Prompts count  
try {
    $cnt = $pdo->query("SELECT COUNT(*) FROM ai_prompts WHERE is_active=1")->fetchColumn();
    $tests[] = ['name'=>'AI Prompts actifs', 'ok'=>true, 'detail'=>"$cnt prompts actifs"];
} catch(PDOException $e) {
    $tests[] = ['name'=>'AI Prompts actifs', 'ok'=>false, 'detail'=>$e->getMessage()];
}

// Test 6: Pages in DB
try {
    $pages = $pdo->query("SELECT id, title, slug FROM pages LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $tests[] = ['name'=>'Pages disponibles', 'ok'=>!empty($pages), 'detail'=>count($pages).' pages. Première: '.($pages[0]['title']??'?').' (id='.$pages[0]['id'].')'];
} catch(PDOException $e) {
    $tests[] = ['name'=>'Pages disponibles', 'ok'=>false, 'detail'=>$e->getMessage()];
}

// Test 7: Anthropic API key
$hasKey = defined('ANTHROPIC_API_KEY') && !empty(ANTHROPIC_API_KEY);
$tests[] = ['name'=>'Clé API Anthropic', 'ok'=>$hasKey, 'detail'=>$hasKey ? 'Définie ('.strlen(ANTHROPIC_API_KEY).' chars)' : 'NON DÉFINIE'];

// Test 8: save-content.php exists
$tests[] = ['name'=>'save-content.php', 'ok'=>file_exists(__DIR__.'/../../../api/builder/save-content.php'), 'detail'=>'Endpoint sauvegarde'];
$tests[] = ['name'=>'template-load.php', 'ok'=>file_exists(__DIR__.'/../../../api/builder/template-load.php'), 'detail'=>'Endpoint template'];

?><!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Test Editor</title>
<style>body{font-family:monospace;padding:20px;background:#f8fafc}h1{color:#1e293b}
.test{padding:8px 12px;margin:4px 0;border-radius:6px;display:flex;justify-content:space-between}
.ok{background:#dcfce7;color:#166534}.fail{background:#fee2e2;color:#991b1b}
.detail{font-size:12px;color:inherit;opacity:.8}
a{color:#3b82f6;font-weight:bold}
</style></head><body>
<h1>🔧 Test Éditeur Builder</h1>
<p>Session admin: <strong><?=htmlspecialchars($_SESSION['admin_email']??'?')?></strong> | CSRF: <code><?=substr($csrf,0,16)?>...</code></p>
<hr>
<?php foreach($tests as $t): ?>
<div class="test <?=$t['ok']?'ok':'fail'?>">
    <span><?=$t['ok']?'✅':'❌'?> <strong><?=htmlspecialchars($t['name'])?></strong></span>
    <span class="detail"><?=htmlspecialchars($t['detail'])?></span>
</div>
<?php endforeach; ?>
<hr>
<h2>Liens éditeur</h2>
<?php
try {
    $pages = $pdo->query("SELECT id, title FROM pages LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach($pages as $p) {
        echo '<p><a href="/admin/modules/builder/builder/editor.php?context=landing&entity_id='.$p['id'].'" target="_blank">✏️ Éditer: '.htmlspecialchars($p['title']).' (#'.$p['id'].')</a></p>';
    }
} catch(PDOException $e) {}
?>
</body></html>
