<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * DASHBOARD — IMMO LOCAL+
 */

// Charger config UNE SEULE FOIS
require_once __DIR__ . '/../config/config.php';

// Démarrer session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ BYPASS TEMPORAIRE : forcer la session admin pour tester
if (empty($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['user'] = 'admin@test.local';
    $_SESSION['admin_email'] = 'admin@test.local';
    $_SESSION['admin_role'] = 'superuser';
    $_SESSION['advisor_name'] = 'Administrateur';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Bootstrap
if (!class_exists('Database')) {
    require_once __DIR__ . '/../includes/classes/Database.php';
}
if (!defined('ADMIN_ROUTER')) define('ADMIN_ROUTER', true);
if (!defined('IMMO_VERSION')) define('IMMO_VERSION', '8.6');

try {
    $pdo = Database::getInstance();
} catch (Exception $e) {
    error_log('Dashboard DB: ' . $e->getMessage());
    $pdo = null;
}

// ── Routing ─────────────────────────────────────────────────
$originalModule = strtolower(preg_replace('/[^a-z0-9_\/-]/i', '',
    $_GET['page'] ?? $_GET['module'] ?? 'dashboard'));

$aliases = [
    'home' => 'dashboard', 'accueil' => 'dashboard',
    'blog' => 'articles',  'posts'   => 'articles',
    'contacts'        => 'crm',
    'leads-list'      => 'leads',
    'pages-capture'   => 'captures',
    'capture'         => 'captures',
    'quartiers'       => 'secteurs',
    'neighborhoods'   => 'secteurs',
    'social-media'    => 'reseaux-sociaux',
    'social'          => 'reseaux-sociaux',
    'gmb-prospects'   => 'scraper-gmb',
    'strategy'        => 'launchpad',
    'strategie'       => 'launchpad',
    'module-manager'  => 'modules',
    'advisor-context' => 'advisor-context',
    'contexte-ia'     => 'advisor-context',
    'biens'           => 'properties',
    'properties'      => 'properties',
];
$module = $aliases[$originalModule] ?? $originalModule;

// ── Fichiers modules ─────────────────────────────────────────
$subRoutes = [
    'dashboard'           => ['file' => 'system/index.php'],
    'pages'               => ['file' => 'content/pages/index.php'],
    'pages-create'        => ['file' => 'content/pages/create.php'],
    'pages-edit'          => ['file' => 'content/pages/edit.php'],
    'articles'            => ['file' => 'content/articles/index.php'],
    'articles-edit'       => ['file' => 'content/articles/edit.php'],
    'captures'            => ['file' => 'content/pages-capture/index.php'],
    'captures-create'     => ['file' => 'content/pages-capture/create.php'],
    'captures-edit'       => ['file' => 'content/pages-capture/edit.php'],
    'secteurs'            => ['file' => 'content/secteurs/index.php'],
    'secteurs-edit'       => ['file' => 'content/secteurs/edit.php'],
    'guide-local'         => ['file' => 'content/guide-local/index.php'],
    'guide-local-edit'    => ['file' => 'content/guide-local/edit.php'],
    'sections'            => ['file' => 'content/sections/index.php'],
    'templates'           => ['file' => 'content/templates/index.php'],
    'builder'             => ['file' => 'builder/builder/index.php'],
    'builder-editor'      => ['file' => 'builder/builder/editor.php'],
    'builder-edit'        => ['file' => 'builder/builder/editor.php'],
    'builder-create'      => ['file' => 'builder/builder/create.php'],
    'headers'             => ['file' => 'builder/builder/headers.php'],
    'headers-edit'        => ['file' => 'builder/builder/edit-header.php'],
    'footers'             => ['file' => 'builder/builder/footers.php'],
    'menus'               => ['file' => 'builder/menus/index.php'],
    'design'              => ['file' => 'builder/design/index.php'],
    'properties'          => ['file' => 'immobilier/properties/index.php'],
    'properties-edit'     => ['file' => 'immobilier/properties/edit.php'],
    'estimation'          => ['file' => 'immobilier/estimation/index.php'],
    'rdv'                 => ['file' => 'immobilier/rdv/index.php'],
    'financement'         => ['file' => 'immobilier/financement/index.php'],
    'market-analyzer'     => ['file' => 'immobilier/market-analyzer/index.php'],
    'crm'                 => ['file' => 'marketing/crm/index.php'],
    'leads'               => ['file' => 'marketing/leads/index.php'],
    'emails'              => ['file' => 'marketing/emails/index.php'],
    'sequences'           => ['file' => 'marketing/sequences/index.php'],
    'scoring'             => ['file' => 'marketing/scoring/index.php'],
    'campagnes'           => ['file' => 'marketing/campagnes/index.php'],
    'messagerie'          => ['file' => 'crm/messagerie/index.php'],
    'seo'                 => ['file' => 'seo/seo/index.php'],
    'seo-semantic'        => ['file' => 'seo/seo-semantic/index.php'],
    'local-seo'           => ['file' => 'seo/local-seo/index.php'],
    'analytics'           => ['file' => 'seo/analytics/index.php'],
    'reseaux-sociaux'     => ['file' => 'social/reseaux-sociaux/index.php'],
    'facebook'            => ['file' => 'social/facebook/index.php'],
    'instagram'           => ['file' => 'social/instagram/index.php'],
    'linkedin'            => ['file' => 'social/linkedin/index.php'],
    'tiktok'              => ['file' => 'social/tiktok/index.php'],
    'gmb'                 => ['file' => 'social/gmb/index.php'],
    'image-editor'        => ['file' => 'social/image-editor/index.php'],
    'scraper-gmb'         => ['file' => 'network/scraper-gmb/index.php'],
    'websites'            => ['file' => 'network/websites/index.php'],
    'launchpad'           => ['file' => 'strategy/launchpad/index.php'],
    'strategy-module'     => ['file' => 'strategy/strategy/index.php'],
    'seo-strategie'       => ['file' => 'strategy/strategy/seo-strategie.php'],
    'analyse-marche'      => ['file' => 'strategy/strategy/analyse-marche.php'],
    'ressources'          => ['file' => 'strategy/strategy/ressources/index.php'],
    'neuropersona'        => ['file' => 'ai/neuropersona/index.php'],
    'ai'                  => ['file' => 'ai/ai/index.php'],
    'ai-prompts'          => ['file' => 'ai/ai-prompts/index.php'],
    'journal'             => ['file' => 'ai/journal/index.php'],
    'agents'              => ['file' => 'ai/agents/index.php'],
    'advisor-context'     => ['file' => 'ai/advisor-context/index.php'],
    'modules'             => ['file' => 'system/modules.php'],
    'maintenance'         => ['file' => 'system/maintenance/index.php'],
    'license'             => ['file' => 'license/index.php'],
    'settings'            => ['file' => 'system/settings/index.php'],
    'settings-email'      => ['file' => 'system/settings/index.php', 'extra' => ['tab'=>'email']],
    'settings-api'        => ['file' => 'system/settings/index.php', 'extra' => ['tab'=>'api']],
    'settings-ai'         => ['file' => 'system/settings/index.php', 'extra' => ['tab'=>'ai']],
    'api-keys'            => ['file' => 'system/settings/api/api-keys.php'],
    'ai-settings'         => ['file' => 'system/settings/ai_settings.php'],
    'profile'             => ['file' => 'ai/advisor-context/index.php'],
    'users'               => ['file' => 'system/users/index.php'],
];

// ── Titres ───────────────────────────────────────────────────
$titles = [
    'dashboard'       => 'Tableau de bord',
    'pages'           => 'Mes pages',
    'articles'        => 'Mes articles',
    'captures'        => 'Pages de capture',
    'secteurs'        => 'Mes quartiers',
    'guide-local'     => 'Guide Local',
    'sections'        => 'Sections',
    'templates'       => 'Modèles de pages',
    'builder'         => 'Éditeur de site',
    'builder-edit'    => 'Éditeur de site',
    'builder-editor'  => 'Éditeur de site',
    'headers'         => 'Haut de page',
    'headers-edit'    => 'Modifier le header',
    'footers'         => 'Bas de page',
    'menus'           => 'Menus',
    'design'          => 'Charte graphique',
    'properties'      => 'Mes biens',
    'estimation'      => 'Estimations reçues',
    'rdv'             => 'Mes rendez-vous',
    'financement'     => 'Financement',
    'market-analyzer' => 'Analyseur de Marché',
    'crm'             => 'Mes contacts',
    'leads'           => 'Mes prospects',
    'emails'          => 'Emails automatiques',
    'sequences'       => 'Séquences email',
    'scoring'         => 'Score des prospects',
    'campagnes'       => 'Campagnes email',
    'messagerie'      => 'Messagerie',
    'seo'             => 'Mon référencement',
    'seo-semantic'    => 'Mots-clés & sémantique',
    'local-seo'       => 'Google My Business',
    'analytics'       => 'Mes statistiques',
    'reseaux-sociaux' => 'Mes réseaux sociaux',
    'facebook'        => 'Facebook',
    'instagram'       => 'Instagram',
    'linkedin'        => 'LinkedIn',
    'tiktok'          => 'TikTok',
    'gmb'             => 'Google My Business',
    'image-editor'    => "Éditeur d'images IA",
    'scraper-gmb'     => 'Trouver des partenaires',
    'launchpad'       => 'Plan de lancement',
    'seo-strategie'   => 'SEO stratégie',
    'analyse-marche'  => 'Analyse de marché',
    'ressources'      => 'Guides & Ressources',
    'neuropersona'    => 'Mon client idéal',
    'ai'              => 'Assistant IA',
    'ai-prompts'      => 'Mes prompts IA',
    'journal'         => 'Planning éditorial',
    'agents'          => 'Agents IA',
    'advisor-context' => 'Mon profil IA',
    'profile'         => 'Mon profil',
    'modules'         => 'Gestion des modules',
    'maintenance'     => 'Maintenance du site',
    'license'         => 'Ma licence',
    'settings'        => 'Configuration',
    'settings-email'  => 'Emails & SMTP',
    'settings-api'    => 'Connexions API',
    'settings-ai'     => 'Config IA',
    'api-keys'        => 'Clés API',
    'ai-settings'     => 'Paramètres AI',
    'users'           => 'Gestion des utilisateurs',
];

$pageTitle = $titles[$module] ?? $titles[$originalModule]
           ?? ucfirst(str_replace('-', ' ', $module));

// ── Highlight sidebar ────────────────────────────────────────
$highlightMap = [
    'pages-create'    => 'pages',    'pages-edit'    => 'pages',
    'articles-edit'   => 'articles',
    'captures-create' => 'captures', 'captures-edit' => 'captures',
    'secteurs-edit'   => 'secteurs',
    'guide-local-edit'=> 'guide-local',
    'builder-editor'  => 'builder',  'builder-edit'  => 'builder',
    'builder-create'  => 'builder',
    'headers-edit'    => 'headers',
    'properties-edit' => 'properties',
    'settings-email'  => 'settings', 'settings-api'  => 'settings',
    'settings-ai'     => 'settings', 'profile'       => 'advisor-context',
    'users'           => 'users',
];
$activeModule = $highlightMap[$module] ?? $highlightMap[$originalModule] ?? $module;

// ── Extra GET params ─────────────────────────────────────────
if (isset($subRoutes[$module]['extra'])) {
    foreach ($subRoutes[$module]['extra'] as $k => $v) {
        $_GET[$k] = $_GET[$k] ?? $v;
    }
}

// ── Résolution fichier module ────────────────────────────────
$module_file  = null;
$modulesBase  = __DIR__ . '/modules/';

if (isset($subRoutes[$module]['file'])) {
    $c = $modulesBase . $subRoutes[$module]['file'];
    if (file_exists($c)) $module_file = $c;
}
if (!$module_file && $module !== 'dashboard') {
    foreach ([
        $modulesBase . $module . '/index.php',
        $modulesBase . $module . '.php',
    ] as $c) {
        if (file_exists($c)) { $module_file = $c; break; }
    }
    if (!$module_file) {
        foreach (glob($modulesBase . '*', GLOB_ONLYDIR) as $dir) {
            $f = $dir . '/' . $module . '/index.php';
            if (file_exists($f)) { $module_file = $f; break; }
        }
    }
}

// ── Module states ────────────────────────────────────────────
$moduleStates = [];
$msFile = __DIR__ . '/../config/module-states.json';
if (file_exists($msFile)) {
    $moduleStates = json_decode(file_get_contents($msFile), true) ?: [];
}

// ── Vérification des permissions ─────────────────────────────
if ($module !== 'dashboard' && function_exists('isModuleAllowed') && !isModuleAllowed($module)) {
    $module_file = null; // Bloquer le chargement
    $permissionDenied = true;
}

// ── AJAX : inclure le module directement sans layout ─────────
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    if (!empty($permissionDenied)) {
        http_response_code(403);
        echo '<div class="es"><i class="fas fa-lock"></i><h3>Accès restreint</h3><p>Vous n\'avez pas accès à ce module.</p></div>';
        exit;
    }
    if ($module_file) {
        include $module_file;
        exit;
    }
}

// ══════════════════════════════════════════════════════════════
//  RENDU
// ══════════════════════════════════════════════════════════════
ob_start();

require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/layout/sidebar.php';
?>

<main class="main">

<?php if ($module === 'dashboard'): ?>
<?php
    // Stats
    $stats = ['articles'=>0,'pages'=>0,'leads'=>0,'properties'=>0,'estimations'=>0,'captures'=>0];
    if ($pdo) {
        $tbls = ['articles'=>'articles','pages'=>'pages','leads'=>'leads',
                 'properties'=>'properties','estimations'=>'estimations','captures'=>'captures'];
        foreach ($tbls as $key => $tbl) {
            try { $stats[$key] = (int)$pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn(); }
            catch (Exception $e) {}
        }
    }
    $firstName = explode(' ', $advisorName ?? 'Utilisateur')[0];
?>
<div class="page-hd anim">
    <div>
        <h1>Bonjour, <?= htmlspecialchars($firstName) ?> 👋</h1>
        <div class="page-hd-sub">Votre tableau de bord IMMO LOCAL+ — v<?= IMMO_VERSION ?></div>
    </div>
    <a href="?page=advisor-context" class="btn btn-p btn-sm">
        <i class="fas fa-user-circle"></i> Mon profil IA
    </a>
</div>

<div class="grid-4 anim">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
            <i class="fas fa-newspaper"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val"><?= $stats['articles'] ?></div>
            <div class="stat-label">Articles sur le blog</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> Visible sur Google</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(13,162,113,.1);color:#0da271">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val"><?= $stats['leads'] ?></div>
            <div class="stat-label">Prospects captés</div>
            <div class="stat-trend up"><i class="fas fa-bolt"></i> À contacter</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(201,145,59,.1);color:#c9913b">
            <i class="fas fa-house"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val"><?= $stats['properties'] ?></div>
            <div class="stat-label">Biens en portefeuille</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(217,119,6,.1);color:#d97706">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val"><?= $stats['estimations'] ?></div>
            <div class="stat-label">Estimations demandées</div>
        </div>
    </div>
</div>

<div class="grid-3 anim d1" style="margin-top:16px">
    <div class="card">
        <div class="card-hd">
            <h3><i class="fas fa-bolt" style="color:var(--accent);margin-right:6px"></i>Créer maintenant</h3>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <a href="?page=articles-edit"   class="btn btn-s btn-sm"><i class="fas fa-pen"></i> Nouvel article</a>
            <a href="?page=pages-create"    class="btn btn-s btn-sm"><i class="fas fa-file-plus"></i> Nouvelle page</a>
            <a href="?page=captures-create" class="btn btn-s btn-sm"><i class="fas fa-bolt"></i> Page de capture</a>
            <a href="?page=properties-edit" class="btn btn-s btn-sm"><i class="fas fa-house-plus"></i> Nouveau bien</a>
        </div>
    </div>
    <div class="card">
        <div class="card-hd">
            <h3><i class="fas fa-wand-magic-sparkles" style="color:var(--gold);margin-right:6px"></i>Mes outils IA</h3>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <a href="?page=journal"         class="btn btn-s btn-sm"><i class="fas fa-calendar-days"></i> Planning contenu</a>
            <a href="?page=seo-semantic"    class="btn btn-s btn-sm"><i class="fas fa-chart-bar"></i> Mots-clés & textes</a>
            <a href="?page=neuropersona"    class="btn btn-s btn-sm"><i class="fas fa-brain"></i> Mon client idéal</a>
            <a href="?page=advisor-context" class="btn btn-s btn-sm"><i class="fas fa-user-circle"></i> Mon profil IA</a>
        </div>
    </div>
    <div class="card">
        <div class="card-hd">
            <h3><i class="fas fa-gear" style="color:var(--text-3);margin-right:6px"></i>Réglages rapides</h3>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <a href="?page=settings"     class="btn btn-s btn-sm"><i class="fas fa-sliders"></i> Configuration</a>
            <a href="?page=api-keys"     class="btn btn-s btn-sm"><i class="fas fa-key"></i> Mes clés API</a>
            <a href="?page=modules"      class="btn btn-s btn-sm"><i class="fas fa-puzzle-piece"></i> État des modules</a>
            <a href="?page=maintenance"  class="btn btn-s btn-sm"><i class="fas fa-wrench"></i> Maintenance</a>
        </div>
    </div>
</div>

<?php else: ?>

<div id="module-content">
    <?php if (!empty($permissionDenied)): ?>
        <div class="es">
            <i class="fas fa-lock" style="color:#dc2626"></i>
            <h3>Accès restreint</h3>
            <p>Vous n'avez pas accès à ce module. Contactez le Super Administrateur pour obtenir l'accès.</p>
            <a href="?page=dashboard" class="es-btn">&larr; Retour au tableau de bord</a>
        </div>
    <?php elseif ($module_file): ?>
        <?php include $module_file; ?>
    <?php else: ?>
        <div class="es">
            <i class="fas fa-folder-open"></i>
            <h3>Module en cours de préparation</h3>
            <p>Cette fonctionnalité arrive bientôt.</p>
            <p style="font-size:10px;color:var(--text-3);margin-top:8px">
                Module : <?= htmlspecialchars($originalModule) ?>
            </p>
            <a href="?page=dashboard" class="es-btn">← Retour au tableau de bord</a>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

</main>
</div><!-- /.admin-wrapper -->
</body>
</html>
<?php
echo ob_get_clean();
?>
