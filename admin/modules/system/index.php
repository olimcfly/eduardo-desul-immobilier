<?php
/**
 * ============================================================
 *  MODULE SYSTÈME — Hub principal
 *  /admin/modules/system/index.php
 *
 *  Tableau de bord système : santé, paramètres, maintenance,
 *  licences, diagnostics, infos serveur
 * ============================================================
 */

if (!isset($pdo)) {
    $cfgPaths = [
        __DIR__ . '/../../../config/config.php',
        $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php',
        $_SERVER['DOCUMENT_ROOT'] . '/config/config.php',
    ];
    foreach ($cfgPaths as $p) { if (file_exists($p)) { require_once $p; break; } }
    try {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
        );
    } catch (Exception $e) {
        die('<div style="background:#fee2e2;color:#991b1b;padding:20px;margin:20px;border-radius:8px;">❌ '.$e->getMessage().'</div>');
    }
}

// ── Infos système ─────────────────────────────────────────
$phpVersion  = PHP_VERSION;
$phpOk       = version_compare($phpVersion, '8.0', '>=');
$mysqlVersion= '—';
try { $mysqlVersion = $pdo->query("SELECT VERSION()")->fetchColumn(); } catch(Throwable){}

$diskTotal   = disk_total_space('/');
$diskFree    = disk_free_space('/');
$diskUsed    = $diskTotal - $diskFree;
$diskPct     = $diskTotal ? round($diskUsed / $diskTotal * 100) : 0;

$memLimit    = ini_get('memory_limit');
$uploadMax   = ini_get('upload_max_filesize');
$postMax     = ini_get('post_max_size');
$maxExec     = ini_get('max_execution_time');

// ── Extensions PHP ────────────────────────────────────────
$requiredExt = ['pdo','pdo_mysql','curl','json','mbstring','gd','zip','openssl'];
$extStatus   = [];
foreach ($requiredExt as $ext) $extStatus[$ext] = extension_loaded($ext);

// ── Infos IA ──────────────────────────────────────────────
$aiConfigured = false;
$aiProvider   = 'Non configuré';
try {
    $aiKey = $pdo->query("SELECT setting_value FROM ai_settings WHERE setting_key='anthropic_api_key'")->fetchColumn();
    if ($aiKey) { $aiConfigured = true; $aiProvider = 'Anthropic Claude'; }
    else {
        $aiKey = $pdo->query("SELECT setting_value FROM ai_settings WHERE setting_key='openai_api_key'")->fetchColumn();
        if ($aiKey) { $aiConfigured = true; $aiProvider = 'OpenAI GPT'; }
    }
} catch(Throwable){}
if (!$aiConfigured) {
    if (defined('ANTHROPIC_API_KEY') && ANTHROPIC_API_KEY) { $aiConfigured = true; $aiProvider = 'Anthropic Claude (config.php)'; }
    elseif (defined('OPENAI_API_KEY') && OPENAI_API_KEY) { $aiConfigured = true; $aiProvider = 'OpenAI GPT (config.php)'; }
}

// ── Stats DB ──────────────────────────────────────────────
$dbTables = 0;
$dbSize   = 0;
try {
    $dbTables = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".DB_NAME."'")->fetchColumn();
    $dbSize   = (float)$pdo->query("SELECT ROUND(SUM(data_length+index_length)/1024/1024, 2) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".DB_NAME."'")->fetchColumn();
} catch(Throwable){}

function fmtBytes(int $b): string {
    if ($b >= 1073741824) return round($b/1073741824,1).' Go';
    if ($b >= 1048576)    return round($b/1048576,1).' Mo';
    return round($b/1024,1).' Ko';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Système — EcosystèmeImmo</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#f1f5f9; --bg-card:#fff; --bdr:#e2e8f0;
  --t1:#0f172a; --t2:#475569; --t3:#94a3b8;
  --primary:#6366f1; --primary-l:#eef2ff;
  --green:#10b981; --red:#ef4444; --warn:#f59e0b;
  --ai:#7c3aed; --ai-l:#faf5ff;
  --shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.04);
  --r:14px;
}
body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--t1);font-size:14px;line-height:1.6}

/* Wrapper */
.sys-wrap{max-width:1100px;margin:0 auto;padding:28px 20px 60px}

/* Header */
.sys-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:14px}
.sys-header-left{display:flex;align-items:center;gap:16px}
.sys-header-icon{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#1e293b,#334155);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,.2)}
.sys-header h1{font-size:24px;font-weight:800;letter-spacing:-.02em}
.sys-header p{font-size:13px;color:var(--t3);margin-top:2px}
.sys-breadcrumb{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--t3)}
.sys-breadcrumb a{color:var(--primary);text-decoration:none;font-weight:600}
.sys-breadcrumb a:hover{text-decoration:underline}

/* Status bar */
.sys-status-bar{display:flex;gap:10px;margin-bottom:28px;flex-wrap:wrap}
.sys-status-pill{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:20px;font-size:12px;font-weight:700;border:1px solid}
.sys-status-pill.ok{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.sys-status-pill.warn{background:#fffbeb;color:#92400e;border-color:#fde68a}
.sys-status-pill.err{background:#fff1f1;color:#991b1b;border-color:#fca5a5}
.sys-status-pill .dot{width:7px;height:7px;border-radius:50%}
.sys-status-pill.ok .dot{background:#10b981}
.sys-status-pill.warn .dot{background:#f59e0b}
.sys-status-pill.err .dot{background:#ef4444}

/* Section title */
.sys-section-title{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--t3);margin-bottom:14px;padding-left:4px}

/* Module cards grid */
.sys-modules-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-bottom:32px}
.sys-module-card{
  background:var(--bg-card);border:1.5px solid var(--bdr);border-radius:var(--r);
  padding:22px;cursor:pointer;transition:all .2s;text-decoration:none;color:inherit;
  display:flex;align-items:flex-start;gap:16px;position:relative;overflow:hidden;
  box-shadow:var(--shadow);
}
.sys-module-card::before{content:'';position:absolute;inset:0;opacity:0;transition:opacity .2s}
.sys-module-card:hover{border-color:var(--card-color,var(--primary));transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1)}
.sys-module-card:hover::before{opacity:.04;background:var(--card-color,var(--primary))}
.sys-module-card.featured{border-color:var(--card-color,var(--primary));background:linear-gradient(135deg,#faf5ff,#fff)}

.sys-mod-icon{
  width:44px;height:44px;border-radius:12px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;
}
.sys-mod-body{flex:1;min-width:0}
.sys-mod-title{font-size:14px;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:8px}
.sys-mod-desc{font-size:12px;color:var(--t2);line-height:1.5}
.sys-mod-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:8px;background:var(--card-color,var(--primary));color:#fff}
.sys-mod-arrow{position:absolute;right:16px;top:50%;transform:translateY(-50%);color:var(--t3);font-size:13px;transition:all .2s}
.sys-module-card:hover .sys-mod-arrow{color:var(--card-color,var(--primary));transform:translateY(-50%) translateX(3px)}

/* Infos server grid */
.sys-info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:32px}
.sys-info-card{background:var(--bg-card);border:1px solid var(--bdr);border-radius:12px;padding:16px 18px;box-shadow:var(--shadow)}
.sys-info-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--t3);margin-bottom:6px}
.sys-info-val{font-size:18px;font-weight:800;color:var(--t1)}
.sys-info-sub{font-size:11px;color:var(--t3);margin-top:3px}

/* Progress bar */
.sys-prog{height:5px;background:#e2e8f0;border-radius:10px;margin-top:8px;overflow:hidden}
.sys-prog-bar{height:100%;border-radius:10px;transition:width .6s ease}

/* Extensions grid */
.sys-ext-grid{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:32px}
.sys-ext-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid}
.sys-ext-pill.ok{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.sys-ext-pill.miss{background:#fff1f1;color:#991b1b;border-color:#fca5a5}

/* Responsive */
@media(max-width:640px){.sys-modules-grid{grid-template-columns:1fr}.sys-info-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="sys-wrap">

<!-- Header -->
<div class="sys-header">
  <div class="sys-header-left">
    <div class="sys-header-icon"><i class="fas fa-server"></i></div>
    <div>
      <h1>Système</h1>
      <p>Administration, configuration et santé de la plateforme</p>
    </div>
  </div>
  <div class="sys-breadcrumb">
    <a href="?page=dashboard"><i class="fas fa-home"></i> Accueil</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <span>Système</span>
  </div>
</div>

<!-- Status bar -->
<div class="sys-status-bar">
  <div class="sys-status-pill <?= $phpOk?'ok':'warn' ?>">
    <span class="dot"></span>
    PHP <?= $phpVersion ?>
  </div>
  <div class="sys-status-pill ok">
    <span class="dot"></span>
    MySQL <?= htmlspecialchars($mysqlVersion) ?>
  </div>
  <div class="sys-status-pill <?= $diskPct < 80 ? 'ok' : ($diskPct < 90 ? 'warn' : 'err') ?>">
    <span class="dot"></span>
    Disque <?= $diskPct ?>% utilisé
  </div>
  <div class="sys-status-pill <?= $aiConfigured ? 'ok' : 'warn' ?>">
    <span class="dot"></span>
    IA : <?= htmlspecialchars($aiProvider) ?>
  </div>
</div>

<!-- ── Modules ────────────────────────────────────────── -->
<div class="sys-section-title"><i class="fas fa-puzzle-piece"></i> Modules système</div>
<div class="sys-modules-grid">

  <a href="?page=system/settings" class="sys-module-card" style="--card-color:#6366f1">
    <div class="sys-mod-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5)"><i class="fas fa-sliders-h"></i></div>
    <div class="sys-mod-body">
      <div class="sys-mod-title">Paramètres généraux</div>
      <div class="sys-mod-desc">Configuration du site, branding, URL, langue, fuseau horaire</div>
    </div>
    <i class="fas fa-chevron-right sys-mod-arrow"></i>
  </a>

  <a href="?page=system/settings/ai" class="sys-module-card featured" style="--card-color:#7c3aed">
    <div class="sys-mod-icon" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)"><i class="fas fa-robot"></i></div>
    <div class="sys-mod-body">
      <div class="sys-mod-title">
        Intelligence Artificielle
        <?php if ($aiConfigured): ?>
        <span class="sys-mod-badge" style="background:#10b981">Actif</span>
        <?php else: ?>
        <span class="sys-mod-badge" style="background:#ef4444">Config requise</span>
        <?php endif; ?>
      </div>
      <div class="sys-mod-desc">Clés API, modèles IA, prompts système par fonctionnalité</div>
    </div>
    <i class="fas fa-chevron-right sys-mod-arrow"></i>
  </a>

  <a href="?page=system/settings/api" class="sys-module-card" style="--card-color:#0891b2">
    <div class="sys-mod-icon" style="background:linear-gradient(135deg,#0891b2,#0e7490)"><i class="fas fa-plug"></i></div>
    <div class="sys-mod-body">
      <div class="sys-mod-title">Intégrations API</div>
      <div class="sys-mod-desc">Google, Facebook, webhooks, services tiers connectés</div>
    </div>
    <i class="fas fa-chevron-right sys-mod-arrow"></i>
  </a>

  <a href="?page=system/maintenance" class="sys-module-card" style="--card-color:#d97706">
    <div class="sys-mod-icon" style="background:linear-gradient(135deg,#d97706,#b45309)"><i class="fas fa-tools"></i></div>
    <div class="sys-mod-body">
      <div class="sys-mod-title">Maintenance</div>
      <div class="sys-mod-desc">Cache, logs, sauvegardes, mode maintenance, nettoyage DB</div>
    </div>
    <i class="fas fa-chevron-right sys-mod-arrow"></i>
  </a>

  <a href="?page=system/diagnostic" class="sys-module-card" style="--card-color:#059669">
    <div class="sys-mod-icon" style="background:linear-gradient(135deg,#059669,#047857)"><i class="fas fa-heartbeat"></i></div>
    <div class="sys-mod-body">
      <div class="sys-mod-title">Diagnostic</div>
      <div class="sys-mod-desc">Santé du système, tests de connexion, erreurs PHP, logs</div>
    </div>
    <i class="fas fa-chevron-right sys-mod-arrow"></i>
  </a>

  <a href="?page=system/license" class="sys-module-card" style="--card-color:#dc2626">
    <div class="sys-mod-icon" style="background:linear-gradient(135deg,#dc2626,#b91c1c)"><i class="fas fa-key"></i></div>
    <div class="sys-mod-body">
      <div class="sys-mod-title">Licence</div>
      <div class="sys-mod-desc">Clé de licence, activations, plan actif, renouvellement</div>
    </div>
    <i class="fas fa-chevron-right sys-mod-arrow"></i>
  </a>

</div>

<!-- ── Infos serveur ──────────────────────────────────── -->
<div class="sys-section-title"><i class="fas fa-microchip"></i> Ressources serveur</div>
<div class="sys-info-grid">

  <div class="sys-info-card">
    <div class="sys-info-label">PHP</div>
    <div class="sys-info-val" style="color:<?= $phpOk?'var(--green)':'var(--warn)' ?>"><?= $phpVersion ?></div>
    <div class="sys-info-sub"><?= $phpOk ? '✓ Compatible' : '⚠ Upgrade recommandé' ?></div>
  </div>

  <div class="sys-info-card">
    <div class="sys-info-label">Base de données</div>
    <div class="sys-info-val"><?= $dbTables ?> tables</div>
    <div class="sys-info-sub"><?= $dbSize ?> Mo · MySQL <?= htmlspecialchars(explode('-',$mysqlVersion)[0]) ?></div>
  </div>

  <div class="sys-info-card">
    <div class="sys-info-label">Espace disque</div>
    <div class="sys-info-val"><?= $diskPct ?>%</div>
    <div class="sys-info-sub"><?= fmtBytes($diskFree) ?> libre / <?= fmtBytes($diskTotal) ?></div>
    <div class="sys-prog"><div class="sys-prog-bar" style="width:<?= $diskPct ?>%;background:<?= $diskPct<70?'#10b981':($diskPct<85?'#f59e0b':'#ef4444') ?>"></div></div>
  </div>

  <div class="sys-info-card">
    <div class="sys-info-label">Mémoire PHP</div>
    <div class="sys-info-val"><?= $memLimit ?></div>
    <div class="sys-info-sub">Upload max : <?= $uploadMax ?> · POST : <?= $postMax ?></div>
  </div>

  <div class="sys-info-card">
    <div class="sys-info-label">Exécution max</div>
    <div class="sys-info-val"><?= $maxExec ?>s</div>
    <div class="sys-info-sub">Timezone : <?= date_default_timezone_get() ?></div>
  </div>

  <div class="sys-info-card">
    <div class="sys-info-label">Intelligence IA</div>
    <div class="sys-info-val" style="font-size:13px;color:<?= $aiConfigured?'var(--ai)':'var(--red)' ?>"><?= $aiConfigured ? '✓ Opérationnel' : '✗ Non configuré' ?></div>
    <div class="sys-info-sub"><?= htmlspecialchars($aiProvider) ?></div>
  </div>

</div>

<!-- ── Extensions PHP ────────────────────────────────── -->
<div class="sys-section-title"><i class="fas fa-puzzle-piece"></i> Extensions PHP requises</div>
<div class="sys-ext-grid">
<?php foreach ($extStatus as $ext => $loaded): ?>
  <span class="sys-ext-pill <?= $loaded?'ok':'miss' ?>">
    <i class="fas fa-<?= $loaded?'check':'times' ?>"></i>
    <?= $ext ?>
  </span>
<?php endforeach; ?>
</div>

</div>
</body>
</html>