<?php
/**
 * LEGAL TEMPLATE
 * /front/templates/legal.php
 *
 * Template pour pages légales : mentions-legales, politique-confidentialite,
 * cgu, honoraires, mediation, etc.
 *
 * Variables disponibles depuis router.php :
 * - $page (array)    — ligne complète de la table pages
 * - $website (array) — ligne complète de la table websites
 * - $pdo (PDO)       — connexion base de données
 */

// ── Charger les helpers de front/page.php ──────────────────
$frontPagePath = dirname(__DIR__) . '/page.php';
if (!defined('FRONT_ROUTER')) define('FRONT_ROUTER', true);

// On a besoin des fonctions : getHeaderFooter, renderHeader, renderFooter, eduardoHead
if (file_exists($frontPagePath) && !function_exists('renderHeader')) {
    // page.php définit les helpers puis fait du routing ;
    // on doit l'inclure sans déclencher le routing.
    // On va redéfinir les helpers manuellement s'il n'est pas inclus.
}

// ── Connexion DB ───────────────────────────────────────────
$db = $pdo ?? null;
if (!$db) {
    $configPath = dirname(dirname(__DIR__)) . '/config/config.php';
    if (file_exists($configPath)) require_once $configPath;
    $dbPath = dirname(dirname(__DIR__)) . '/config/database.php';
    if (file_exists($dbPath)) require_once $dbPath;
    if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
    if (function_exists('getDB')) $db = Database::getInstance();
}

// ── Helper eduardoHead ─────────────────────────────────────
if (!function_exists('eduardoHead')) {
    function eduardoHead(): string {
        return '<link rel="stylesheet" href="/front/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">';
    }
}

// ── Helper _ss (settings) ──────────────────────────────────
if (!function_exists('_ss')) {
    function _ss(string $key, string $default = ''): string {
        global $db, $pdo;
        $conn = $db ?? $pdo ?? null;
        if (!$conn) return $default;
        try {
            $s = $conn->prepare("SELECT value FROM settings WHERE name=? LIMIT 1");
            $s->execute([$key]);
            return $s->fetchColumn() ?: $default;
        } catch (Exception $e) { return $default; }
    }
}

// ── Charger header/footer ──────────────────────────────────
if (!function_exists('getHeaderFooter')) {
    function getHeaderFooter(PDO $db, string $slug = ''): array {
        $result = ['header' => null, 'footer' => null];
        foreach (['headers', 'site_headers'] as $tbl) {
            try {
                $h = $db->query("SELECT * FROM `$tbl` WHERE status='active' ORDER BY is_default DESC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($h) { $result['header'] = $h; break; }
            } catch (Exception $e) {}
        }
        foreach (['footers', 'site_footers'] as $tbl) {
            try {
                $f = $db->query("SELECT * FROM `$tbl` WHERE status='active' ORDER BY is_default DESC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($f) { $result['footer'] = $f; break; }
            } catch (Exception $e) {}
        }
        return $result;
    }
}

// ── Charger renderHeader depuis page.php si disponible ─────
if (!function_exists('renderHeader') && file_exists($frontPagePath)) {
    // Inclure page.php pour obtenir renderHeader/renderFooter
    // On capture la sortie pour éviter tout echo accidentel
    ob_start();
    // Empêcher le routing de page.php en simulant un type déjà traité
    $_GET['type'] = '__legal_skip__';
    $originalSlug = $_GET['slug'] ?? '';
    $_GET['slug'] = '';
    try {
        include $frontPagePath;
    } catch (Exception $e) {}
    // Restaurer
    $_GET['slug'] = $originalSlug;
    unset($_GET['type']);
    ob_end_clean();
}

// ── Fallback renderHeader si toujours pas défini ───────────
if (!function_exists('renderHeader')) {
    function renderHeader(?array $header): string {
        if (!$header) return '';
        $css = '';
        if (!empty($header['custom_css'])) $css = '<style>'.$header['custom_css'].'</style>';
        if (!empty($header['custom_html'])) {
            $ch = trim($header['custom_html']);
            if (!($ch[0] === '[' && isset($ch[1]) && $ch[1] === '{')) {
                return $css . $ch;
            }
        }
        // Fallback minimal
        $bg = htmlspecialchars($header['bg_color'] ?? '#ffffff');
        $logoText = htmlspecialchars($header['logo_text'] ?? ($header['name'] ?? 'Eduardo Desul'));
        return $css . '<header style="background:'.$bg.';padding:20px 24px;border-bottom:1px solid #e2d9ce">
            <div style="max-width:1260px;margin:0 auto;display:flex;align-items:center;justify-content:space-between">
                <a href="/" style="font-family:\'Playfair Display\',serif;font-size:22px;font-weight:800;color:#1a4d7a;text-decoration:none">'.$logoText.'</a>
            </div>
        </header>';
    }
}

if (!function_exists('renderFooter')) {
    function renderFooter(?array $footer): string {
        if (!$footer) return '';
        $css = '';
        if (!empty($footer['custom_css'])) $css = '<style>'.$footer['custom_css'].'</style>';
        if (!empty($footer['custom_html'])) {
            $ch = trim($footer['custom_html']);
            if (!($ch[0] === '[' && isset($ch[1]) && $ch[1] === '{')) {
                return $css . $ch;
            }
        }
        $bg = htmlspecialchars($footer['bg_color'] ?? '#1e293b');
        $tc = htmlspecialchars($footer['text_color'] ?? '#94a3b8');
        $copy = htmlspecialchars($footer['copyright_text'] ?? '© '.date('Y').' Eduardo Desul Immobilier. Tous droits réservés.');
        return $css . '<footer style="background:'.$bg.';color:'.$tc.';padding:48px 24px 24px">
            <div style="max-width:1260px;margin:0 auto;text-align:center;font-size:13px">'.$copy.'</div>
        </footer>';
    }
}

// ── Récupérer header/footer depuis la DB ───────────────────
$hf = ($db) ? getHeaderFooter($db) : ['header' => null, 'footer' => null];
$headerHtml = renderHeader($hf['header']);
$footerHtml = renderFooter($hf['footer']);

// ── Extraire les <style> des header/footer ─────────────────
$extractedStyles = '';
$stripStyles = function(&$html) use (&$extractedStyles) {
    $html = preg_replace_callback(
        '/<style(\b[^>]*)>(.+?)<\/style>/is',
        function($m) use (&$extractedStyles) {
            $extractedStyles .= "\n<style{$m[1]}>{$m[2]}</style>";
            return '';
        },
        $html
    );
};
$stripStyles($headerHtml);
$stripStyles($footerHtml);

// ── Métadonnées ────────────────────────────────────────────
$siteUrl   = defined('SITE_URL')   ? SITE_URL   : '';
$siteName  = defined('SITE_TITLE') ? SITE_TITLE : ($website['name'] ?? 'Eduardo Desul Immobilier');
$metaTitle = trim(($page['meta_title'] ?: $page['title']) . ' | ' . $siteName);
$metaDesc  = $page['meta_description'] ?? '';
$slug      = $page['slug'] ?? 'mentions-legales';
$canonical = $siteUrl . '/' . $slug;

// ── Contenu de la page ─────────────────────────────────────
$content = $page['content'] ?? $page['html_content'] ?? '';

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($metaTitle) ?></title>
    <?php if ($metaDesc): ?><meta name="description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:title"     content="<?= htmlspecialchars($page['meta_title'] ?: $page['title']) ?>">
    <meta property="og:type"      content="website">
    <meta property="og:url"       content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($siteName) ?>">
    <?php if ($metaDesc): ?><meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>

    <?= eduardoHead() ?>

    <link rel="stylesheet" href="/front/assets/css/legal.css">

    <style id="ed-legal-base">
    :root {
        --primary: #1a4d7a;
        --primary-dark: #0e3a5c;
        --dark-bg: #0f172a;
        --dark: #1e293b;
        --white: #ffffff;
        --bg-light: #f8fafc;
        --bg-beige: #f9f6f3;
        --text-primary: #2d3748;
        --text-secondary: #718096;
        --border-color: #e2d9ce;
        --section-padding: 60px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --shadow-sm: 0 2px 8px rgba(0,0,0,.07);
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: var(--text-primary);
        background: #fff;
        line-height: 1.6;
        margin: 0;
    }
    </style>

    <?= $extractedStyles ?>

    <?php if (!empty($page['custom_css'])): ?>
    <style id="page-custom-css"><?= $page['custom_css'] ?></style>
    <?php endif; ?>

    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[
        {"@type":"ListItem","position":1,"name":"Accueil","item":"<?= $siteUrl ?>/"},
        {"@type":"ListItem","position":2,"name":"<?= htmlspecialchars($page['title']) ?>","item":"<?= htmlspecialchars($canonical) ?>"}
    ]}
    </script>
</head>
<body>

<?php if ($headerHtml): echo $headerHtml; endif; ?>

<!-- Hero Legal -->
<section class="hero-legal">
    <div style="max-width:900px;margin:0 auto">
        <h1><?= htmlspecialchars($page['title']) ?></h1>
        <?php if (!empty($page['meta_description'])): ?>
        <p class="subtitle"><?= htmlspecialchars($page['meta_description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Contenu légal -->
<main>
    <div class="legal-content">
        <div class="legal-text">
            <?= $content ?>
        </div>

        <p class="legal-updated">
            Dernière mise à jour : <?= date('d/m/Y', strtotime($page['updated_at'] ?? 'now')) ?>
        </p>
    </div>
</main>

<?php if ($footerHtml): echo $footerHtml; endif; ?>

<?php if (!empty($page['custom_js'])): ?>
<script><?= $page['custom_js'] ?></script>
<?php endif; ?>

</body>
</html>
