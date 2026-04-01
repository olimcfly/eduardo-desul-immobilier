<?php
/**
 * renderers/cms.php v6 - CMS AVEC TEMPLATES ET BLOCS
 *
 * Priorité de rendu :
 *  1. Page avec template + blocs en page_blocks → Rendu par blocs
 *  2. Page avec template vide + blocs en page_blocks → Rendu par blocs
 *  3. Page sans template → Content HTML direct (backward compat)
 *  4. 404
 */

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

// ── Charger les dépendances ────────────────────────────────
if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
require_once ROOT_PATH . '/includes/helpers/templates-helper.php';

global $db;
if (!$db) $db = Database::getInstance();

$pageSlug = $pageSlug ?? $_GET['slug'] ?? '';
if ($pageSlug === '') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// ── CHARGER LA PAGE ────────────────────────────────────────
try {
    $stmt = $db->prepare("
        SELECT id, title, slug, template, content, meta_title, meta_description, seo_description
        FROM pages
        WHERE slug = ? AND status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$pageSlug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $page = null;
}

if (!$page) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// ── DÉTERMINER LE MODE DE RENDU ────────────────────────────
$useTemplateBlocks = false;
$pageBlocks = [];

if (!empty($page['template'])) {
    // Essayer de charger les blocs du template
    $pageBlocks = getPageBlocks($db, $page['id']);
    if (!empty($pageBlocks)) {
        $useTemplateBlocks = true;
    }
}

// ════════════════════════════════════════════════════════════
// RENDU AVEC TEMPLATES ET BLOCS (NOUVEAU SYSTÈME)
// ════════════════════════════════════════════════════════════

if ($useTemplateBlocks) {
    // Préparer les variables SEO
    $pageTitle = $page['meta_title'] ?: $page['title'];
    $pageDesc = $page['meta_description'] ?: $page['seo_description'] ?: '';
    $siteUrl = defined('SITE_URL') ? SITE_URL : '';
    $siteName = defined('SITE_TITLE') ? SITE_TITLE : 'Eduardo De Sul';
    $canonical = $siteUrl . '/' . ($page['slug'] === 'accueil' ? '' : $page['slug']);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars($siteName); ?></title>
        <meta name="description" content="<?php echo htmlspecialchars($pageDesc); ?>">
        <meta name="canonical" content="<?php echo htmlspecialchars($canonical); ?>">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#1a4d7a">

        <!-- Styles -->
        <?php echo defined('FRONT_HEAD') ? FRONT_HEAD : ''; ?>
        <?php if (function_exists('eduardoHead')) echo eduardoHead(); ?>

        <!-- Headers depuis settings -->
        <?php
        if ($db) {
            list('header' => $headerConfig, 'footer' => $footerConfig) = getHeaderFooter($db, $page['slug']);
            if (!empty($headerConfig['custom_css'])) echo '<style>' . $headerConfig['custom_css'] . '</style>';
        }
        ?>
    </head>
    <body>
        <!-- HEADER -->
        <?php
        if ($db) {
            list('header' => $headerConfig, 'footer' => $footerConfig) = getHeaderFooter($db, $page['slug']);
            if ($headerConfig) echo renderHeader($headerConfig);
        }
        ?>

        <!-- CONTENU PRINCIPAL (BLOCS) -->
        <main>
            <?php
            // Récupérer la config du template pour définir l'ordre des blocs
            $templateConfig = getTemplate($page['template']);
            if ($templateConfig && !empty($templateConfig['blocks'])) {
                // Afficher les blocs dans l'ordre du template
                foreach ($templateConfig['blocks'] as $blockKey => $blockDef) {
                    if (!isset($pageBlocks[$blockKey])) continue;

                    $block = $pageBlocks[$blockKey];
                    if (!$block['visible']) continue; // Bloc masqué par le client

                    $blockType = $block['type'];
                    $blockData = $block['data'];

                    // Inclure le renderer du bloc
                    $blockRenderer = __DIR__ . '/blocks/' . $blockType . '.php';
                    if (file_exists($blockRenderer)) {
                        include $blockRenderer;
                    } else {
                        // Fallback : bloc type inconnu
                        echo "<!-- Bloc $blockType non trouvé -->";
                    }
                }
            }
            ?>
        </main>

        <!-- FOOTER -->
        <?php
        if ($db) {
            list('header' => $headerConfig, 'footer' => $footerConfig) = getHeaderFooter($db, $page['slug']);
            if ($footerConfig) echo renderFooter($footerConfig);
        }
        ?>

        <!-- Scripts -->
        <script src="/front/assets/js/main.js"></script>
    </body>
    </html>

<?php
// ════════════════════════════════════════════════════════════
// FALLBACK : RENDU HTML DIRECT (BACKWARD COMPATIBILITY)
// ════════════════════════════════════════════════════════════
} else {
    // Mode legacy : afficher le contenu HTML direct
    $pageTitle = $page['meta_title'] ?: $page['title'];
    $pageDesc = $page['meta_description'] ?: '';
    $siteUrl = defined('SITE_URL') ? SITE_URL : '';
    $siteName = defined('SITE_TITLE') ? SITE_TITLE : 'Eduardo De Sul';
    $canonical = $siteUrl . '/' . ($page['slug'] === 'accueil' ? '' : $page['slug']);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars($siteName); ?></title>
        <meta name="description" content="<?php echo htmlspecialchars($pageDesc); ?>">
        <meta name="canonical" content="<?php echo htmlspecialchars($canonical); ?>">
        <meta name="robots" content="index, follow">

        <!-- Styles -->
        <?php echo defined('FRONT_HEAD') ? FRONT_HEAD : ''; ?>
        <?php if (function_exists('eduardoHead')) echo eduardoHead(); ?>
    </head>
    <body>
        <!-- HEADER -->
        <?php
        if ($db) {
            list('header' => $headerConfig, 'footer' => $footerConfig) = getHeaderFooter($db, $page['slug']);
            if ($headerConfig) echo renderHeader($headerConfig);
        }
        ?>

        <!-- CONTENU LEGACY (HTML direct) -->
        <main style="padding: 60px 24px;">
            <div style="max-width: 1200px; margin: 0 auto;">
                <h1 style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 700; color: #1a4d7a; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($page['title']); ?>
                </h1>
                <div style="color: #2d3748; line-height: 1.8;">
                    <?php echo $page['content'] ?? ''; ?>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <?php
        if ($db) {
            list('header' => $headerConfig, 'footer' => $footerConfig) = getHeaderFooter($db, $page['slug']);
            if ($footerConfig) echo renderFooter($footerConfig);
        }
        ?>

        <!-- Scripts -->
        <script src="/front/assets/js/main.js"></script>
    </body>
    </html>
    <?php
}
