<?php
/**
 * renderers/cms.php v5.1
 * Priorité de rendu :
 *  1. builder_pages (slug match, status published)  → blocks_data ou custom_html
 *  2. pages (slug match, status published)          → content HTML direct
 *  3. pages (slug match, TOUS statuts)              → fallback debug
 *  4. 404
 */

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

global $db;
if (!$db) $db = getDB();

$pageSlug = $pageSlug ?? $_GET['slug'] ?? '';
if ($pageSlug === '') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// ── Helpers ──────────────────────────────────────────────
if (!function_exists('buildCmsVars')) {
    function buildCmsVars(array $page): array {
        $siteUrl  = function_exists('siteUrl')  ? siteUrl()  : (defined('SITE_URL')  ? SITE_URL  : '');
        $siteName = function_exists('siteName') ? siteName() : (defined('SITE_TITLE') ? SITE_TITLE : '');
        return [
            '{{title}}'            => htmlspecialchars($page['title'] ?? ''),
            '{{titre}}'            => htmlspecialchars($page['title'] ?? ''),
            '{{content}}'          => $page['content'] ?? '',
            '{{contenu}}'          => $page['content'] ?? '',
            '{{slug}}'             => htmlspecialchars($page['slug'] ?? ''),
            '{{meta_title}}'       => htmlspecialchars($page['meta_title'] ?? ''),
            '{{meta_description}}' => htmlspecialchars($page['meta_description'] ?? ''),
            '{{canonical}}'        => htmlspecialchars($siteUrl . '/' . (($page['slug'] ?? '') === 'accueil' ? '' : ($page['slug'] ?? ''))),
            '{{site_name}}'        => htmlspecialchars($siteName),
            '{{site_url}}'         => htmlspecialchars($siteUrl),
            '{{year}}'             => date('Y'),
        ];
    }
}

// ── Rendu blocks_data JSON → HTML ────────────────────────
if (!function_exists('renderBlocksData')) {
    function renderBlocksData(string $blocksJson): string {
        $blocks = json_decode($blocksJson, true);
        if (!is_array($blocks) || empty($blocks)) return '';

        $html = '';
        foreach ($blocks as $block) {
            $type   = $block['type']   ?? $block['block_type'] ?? 'text';
            $config = $block['config'] ?? $block;

            switch ($type) {
                case 'hero':
                    $bg     = htmlspecialchars($config['bgColor']    ?? '#1a4d7a');
                    $img    = htmlspecialchars($config['bgImage']     ?? '');
                    $title  = $config['title']    ?? '';
                    $sub    = $config['subtitle'] ?? '';
                    $height = htmlspecialchars($config['height']     ?? '60vh');
                    $align  = htmlspecialchars($config['alignment']  ?? 'center');
                    $bgStyle = $img
                        ? "background:linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),url('$img') center/cover no-repeat;"
                        : "background:$bg;";
                    $html .= "<section style=\"{$bgStyle}min-height:{$height};display:flex;align-items:center;justify-content:{$align};text-align:{$align};padding:60px 24px;\">
                        <div style=\"max-width:900px;color:#fff;\">
                            " . ($title ? "<h1 style=\"font-family:'Playfair Display',serif;font-size:clamp(32px,5vw,56px);font-weight:700;line-height:1.2;margin-bottom:16px;\">$title</h1>" : '') . "
                            " . ($sub   ? "<p style=\"font-size:clamp(16px,2vw,22px);opacity:.9;max-width:700px;margin:0 auto;\">$sub</p>" : '') . "
                        </div>
                    </section>";
                    break;

                case 'text':
                case 'html':
                    $content   = $config['content']  ?? $config['html'] ?? '';
                    $maxWidth  = htmlspecialchars($config['maxWidth'] ?? '900px');
                    $padding   = htmlspecialchars($config['padding']  ?? '40px 24px');
                    $bgColor   = htmlspecialchars($config['bgColor']  ?? '');
                    $bgStyle   = $bgColor ? "background:$bgColor;" : '';
                    $html .= "<div style=\"{$bgStyle}padding:{$padding};\">
                        <div style=\"max-width:{$maxWidth};margin:0 auto;\">$content</div>
                    </div>";
                    break;

                case 'cta':
                    $headline = htmlspecialchars($config['headline'] ?? $config['title'] ?? '');
                    $text     = htmlspecialchars($config['text']     ?? '');
                    $btnText  = htmlspecialchars($config['buttonText'] ?? $config['button_text'] ?? 'En savoir plus');
                    $btnUrl   = htmlspecialchars($config['buttonUrl']  ?? $config['button_url']  ?? '#');
                    $bg       = htmlspecialchars($config['bgColor']    ?? '#f8f5f0');
                    $html .= "<section style=\"background:{$bg};padding:60px 24px;text-align:center;\">
                        <div style=\"max-width:700px;margin:0 auto;\">
                            " . ($headline ? "<h2 style=\"font-family:'Playfair Display',serif;font-size:32px;font-weight:700;color:#1a4d7a;margin-bottom:12px;\">$headline</h2>" : '') . "
                            " . ($text     ? "<p style=\"color:#718096;font-size:18px;margin-bottom:28px;\">$text</p>" : '') . "
                            <a href=\"$btnUrl\" style=\"display:inline-block;background:#1a4d7a;color:#fff;padding:14px 36px;border-radius:10px;font-weight:700;font-size:16px;text-decoration:none;\">$btnText</a>
                        </div>
                    </section>";
                    break;

                case 'image':
                    $src     = htmlspecialchars($config['src']     ?? '');
                    $alt     = htmlspecialchars($config['alt']     ?? '');
                    $caption = htmlspecialchars($config['caption'] ?? '');
                    $width   = htmlspecialchars($config['width']   ?? '100%');
                    if ($src) {
                        $html .= "<figure style=\"margin:0;padding:24px;text-align:center;\">
                            <img src=\"$src\" alt=\"$alt\" style=\"max-width:{$width};height:auto;border-radius:12px;\">
                            " . ($caption ? "<figcaption style=\"color:#718096;font-size:14px;margin-top:8px;\">$caption</figcaption>" : '') . "
                        </figure>";
                    }
                    break;

                case 'separator':
                    $color = htmlspecialchars($config['color'] ?? '#d4a574');
                    $width = htmlspecialchars($config['width'] ?? '60%');
                    $html .= "<div style=\"text-align:center;padding:16px 0;\"><hr style=\"border:none;border-top:2px solid {$color};width:{$width};margin:0 auto;\"></div>";
                    break;

                case 'columns':
                case 'features':
                    $cols  = $config['columns'] ?? $config['cols'] ?? $config['items'] ?? [];
                    $count = count($cols) ?: 3;
                    $bg    = htmlspecialchars($config['bgColor'] ?? '');
                    $title = $config['title'] ?? '';
                    $bgStyle = $bg ? "background:$bg;" : '';
                    $colsHtml = '';
                    foreach ($cols as $col) {
                        $icon  = htmlspecialchars($col['icon']  ?? '');
                        $ctitle= htmlspecialchars($col['title'] ?? $col['label'] ?? '');
                        $ctext = $col['text'] ?? $col['content'] ?? '';
                        $colsHtml .= "<div style=\"flex:1;min-width:200px;text-align:center;padding:24px;\">
                            " . ($icon   ? "<div style=\"font-size:40px;margin-bottom:16px;\">$icon</div>" : '') . "
                            " . ($ctitle ? "<h3 style=\"font-size:20px;font-weight:700;color:#1a4d7a;margin-bottom:10px;\">$ctitle</h3>" : '') . "
                            " . ($ctext  ? "<p style=\"color:#718096;font-size:15px;line-height:1.6;\">$ctext</p>" : '') . "
                        </div>";
                    }
                    $html .= "<section style=\"{$bgStyle}padding:60px 24px;\">
                        <div style=\"max-width:1200px;margin:0 auto;\">
                            " . ($title ? "<h2 style=\"text-align:center;font-family:'Playfair Display',serif;font-size:32px;font-weight:700;color:#1a4d7a;margin-bottom:40px;\">".htmlspecialchars($title)."</h2>" : '') . "
                            <div style=\"display:flex;flex-wrap:wrap;gap:24px;justify-content:center;\">$colsHtml</div>
                        </div>
                    </section>";
                    break;

                case 'internal_links':
                    break;

                default:
                    $raw = $config['content'] ?? $config['html'] ?? $config['text'] ?? '';
                    if ($raw) {
                        $html .= "<div style=\"padding:24px;\">$raw</div>";
                    }
                    break;
            }
        }
        return $html;
    }
}

// ── Chargement header/footer ──────────────────────────────
function _loadHF_v5(PDO $db, string $type, ?int $specificId): ?array {
    $tables = ($type === 'header') ? ['headers', 'site_headers'] : ['footers', 'site_footers'];
    if ($specificId) {
        foreach ($tables as $tbl) {
            try {
                $s = $db->prepare("SELECT * FROM `$tbl` WHERE id=? LIMIT 1");
                $s->execute([$specificId]);
                $row = $s->fetch(PDO::FETCH_ASSOC);
                if ($row) return $row;
            } catch (Exception $e) {}
        }
    }
    foreach ($tables as $tbl) {
        try {
            $row = $db->query("SELECT * FROM `$tbl` WHERE status='active' ORDER BY is_default DESC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($row) return $row;
        } catch (Exception $e) {}
    }
    return null;
}

// ══════════════════════════════════════════════════════════
// ÉTAPE 1 — Chercher dans builder_pages
// ══════════════════════════════════════════════════════════
$page        = null;
$sourceTable = '';
$html        = '';
$css         = '';
$js          = '';

try {
    $stmt = $db->prepare("SELECT * FROM builder_pages WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$pageSlug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $page        = $row;
        $sourceTable = 'builder_pages';
    }
} catch (Exception $e) {
    error_log("CMS builder_pages lookup error: " . $e->getMessage());
}

// ══════════════════════════════════════════════════════════
// ÉTAPE 2 — Chercher dans pages
// ══════════════════════════════════════════════════════════
if (!$page) {
    try {
        $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
        $stmt->execute([$pageSlug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $page        = $row;
            $sourceTable = 'pages';
        }
    } catch (Exception $e) {
        error_log("CMS pages lookup error: " . $e->getMessage());
    }
}

// ══════════════════════════════════════════════════════════
// ÉTAPE 3 — Fallback toutes statuts (debug)
// ══════════════════════════════════════════════════════════
if (!$page) {
    try {
        $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
        $stmt->execute([$pageSlug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            error_log("CMS: page '$pageSlug' found but status='" . ($row['status'] ?? '?') . "' (not published)");
            $page        = $row;
            $sourceTable = 'pages_draft';
        }
    } catch (Exception $e) {}
}

if (!$page) {
    http_response_code(404);
    if (file_exists(__DIR__ . '/404.php')) require __DIR__ . '/404.php';
    else echo '<h1>404 – Page non trouvée</h1><p>Slug : ' . htmlspecialchars($pageSlug) . '</p>';
    exit;
}

// ══════════════════════════════════════════════════════════
// RENDU DU CONTENU selon la source
// ══════════════════════════════════════════════════════════
$vars = buildCmsVars($page);

if ($sourceTable === 'builder_pages') {
    // builder_pages : blocks_data JSON → HTML
    if (!empty($page['blocks_data']) && $page['blocks_data'] !== '[]') {
        $html = renderBlocksData($page['blocks_data']);
    }
    $css = $page['custom_css'] ?? '';
    $js  = $page['custom_js']  ?? '';

} else {
    $editorType = $page['editor_type'] ?? 'sections';

    if ($editorType === 'builder' && !empty($page['builder_data'])) {
        $html = renderBlocksData($page['builder_data']);
        $css  = $page['custom_css'] ?? '';
        $js   = $page['custom_js']  ?? '';

    } elseif (!empty($page['template_id'])) {
        try {
            $stmt = $db->prepare("SELECT * FROM builder_templates WHERE id = ? LIMIT 1");
            $stmt->execute([$page['template_id']]);
            $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tpl) {
                $html = str_replace(array_keys($vars), array_values($vars), $tpl['content'] ?? $tpl['html'] ?? '');
                $css  = str_replace(array_keys($vars), array_values($vars), $tpl['custom_css'] ?? '');
                $js   = str_replace(array_keys($vars), array_values($vars), $tpl['custom_js']  ?? '');
            }
        } catch (Exception $e) {
            error_log("CMS template load error: " . $e->getMessage());
        }
        if (empty($html)) {
            $html = str_replace(array_keys($vars), array_values($vars), $page['content'] ?? '');
        }
        $css = $css ?: ($page['custom_css'] ?? '');
        $js  = $js  ?: ($page['custom_js']  ?? '');

    } else {
        // HTML direct dans pages.content
        $raw = str_replace(array_keys($vars), array_values($vars), $page['content'] ?? '');

        // Si le content est un HTML complet (Builder Pro sauve tout le document)
        if (stripos($raw, '<html') !== false) {
            // Extraire le CSS des <style> dans le <head>
            preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $raw, $styleMatches);
            if (!empty($styleMatches[1])) {
                $css .= "\n" . implode("\n", $styleMatches[1]);
            }

            // Extraire le JS inline
            preg_match_all('/<script(?![^>]*src)[^>]*>(.*?)<\/script>/is', $raw, $scriptMatches);
            if (!empty($scriptMatches[1])) {
                $js .= "\n" . implode("\n", $scriptMatches[1]);
            }

            // Extraire uniquement le contenu du <body>
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $raw, $bodyMatch)) {
                $raw = $bodyMatch[1];
            } else {
                // Pas de <body> explicite : supprimer les balises structurelles
                $raw = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $raw);
                $raw = preg_replace('/<\/?html[^>]*>/i', '', $raw);
                $raw = preg_replace('/<\/?body[^>]*>/i', '', $raw);
            }
        }

        $html = $raw;
        $css  = $css ?: ($page['custom_css'] ?? '');
        $js   = $js  ?: ($page['custom_js']  ?? '');
    }
}

// ── Header / Footer ──────────────────────────────────────
$hf = [
    'header' => _loadHF_v5($db, 'header', !empty($page['header_id']) ? (int)$page['header_id'] : null),
    'footer' => _loadHF_v5($db, 'footer', !empty($page['footer_id']) ? (int)$page['footer_id'] : null),
];

$showHeader = (($page['show_header']     ?? 1) == 1) && (($page['header_enabled'] ?? 1) == 1);
$showFooter = (($page['show_footer']     ?? 1) == 1) && (($page['footer_enabled'] ?? 1) == 1);

// ── Métadonnées ──────────────────────────────────────────
$_siteUrl  = function_exists('siteUrl')  ? siteUrl()  : (defined('SITE_URL')   ? SITE_URL   : '');
$_siteName = function_exists('siteName') ? siteName() : (defined('SITE_TITLE') ? SITE_TITLE : '');

$metaTitle  = trim(($page['meta_title'] ?: $page['title']) . ' | ' . $_siteName);
$metaDesc   = $page['meta_description'] ?? '';
$canonical  = $_siteUrl . '/' . ($pageSlug === 'accueil' ? '' : $pageSlug);
$ogImage    = $page['og_image'] ?? '';
$metaRobots = $page['meta_robots'] ?? 'index, follow';

$externalCss = [];
$externalJs  = [];
if (!empty($page['external_css'])) {
    $decoded = json_decode($page['external_css'], true);
    if (is_array($decoded)) $externalCss = $decoded;
}
if (!empty($page['external_js'])) {
    $decoded = json_decode($page['external_js'], true);
    if (is_array($decoded)) $externalJs = $decoded;
}

// Incrémenter les vues (silencieux)
try {
    if ($sourceTable === 'builder_pages') {
        $db->prepare("UPDATE builder_pages SET views_count = views_count + 1 WHERE id = ?")->execute([$page['id']]);
    } else {
        $db->prepare("UPDATE pages SET views = views + 1 WHERE id = ?")->execute([$page['id']]);
    }
} catch (Exception $e) {}

// ── Préparer header/footer HTML + extraire leurs styles ──
$headerHtml = ($showHeader && $hf['header'] && function_exists('renderHeader'))
    ? renderHeader($hf['header']) : '';
$footerHtml = ($showFooter && $hf['footer'] && function_exists('renderFooter'))
    ? renderFooter($hf['footer']) : '';

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

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($metaTitle) ?></title>
    <?php if ($metaDesc): ?><meta name="description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
    <meta name="robots" content="<?= htmlspecialchars($metaRobots) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:title"     content="<?= htmlspecialchars($page['meta_title'] ?: $page['title']) ?>">
    <meta property="og:type"      content="website">
    <meta property="og:url"       content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($_siteName) ?>">
    <?php if ($metaDesc): ?><meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
    <?php if ($ogImage):  ?><meta property="og:image"       content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>

    <?php if (function_exists('eduardoHead')): echo eduardoHead(); endif; ?>

    <style id="ed-base-css">
    :root {
      --ed-primary: #1a4d7a;
      --ed-primary-dk: #0e3a5c;
      --ed-accent: #d4a574;
      --ed-accent-lt: #e8c49a;
      --ed-text: #2d3748;
      --ed-text-light: #718096;
      --ed-card-bg: #f9f6f3;
      --ed-border: #e2d9ce;
      --ed-bg: #f9f6f3;
      --ff-heading: 'Playfair Display', serif;
      --ff-body: 'DM Sans', sans-serif;
      --ed-radius: 8px;
      --ed-radius-lg: 12px;
      --ed-shadow: 0 2px 8px rgba(0,0,0,.07);
      --ed-shadow-lg: 0 8px 30px rgba(0,0,0,.12);
      --ed-transition: all .2s ease;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: var(--ff-body);
      color: var(--ed-text);
      background: #fff;
      line-height: 1.6;
      margin: 0;
    }
    .hero-landing, .hero-section, [class*="hero"] { color: #fff !important; }
    .hero-landing h1, .hero-landing h2, .hero-landing h3,
    .hero-landing p, .hero-landing li,
    .hero-section h1, .hero-section h2, .hero-section h3,
    .hero-section p,
    [class*="hero"] h1, [class*="hero"] h2, [class*="hero"] p { color: #fff !important; }
    .cta-final, .section-dark, .bg-dark { color: #fff !important; }
    .cta-final h1, .cta-final h2, .cta-final h3, .cta-final p { color: #fff !important; }
    </style>

    <?php foreach ($externalCss as $cssUrl): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssUrl) ?>">
    <?php endforeach; ?>

    <?php if (!empty($hf['header']['custom_css'])): ?>
        <style><?= $hf['header']['custom_css'] ?></style>
    <?php endif; ?>
    <?php if (!empty($hf['footer']['custom_css'])): ?>
        <style><?= $hf['footer']['custom_css'] ?></style>
    <?php endif; ?>
    <?php if (!empty($page['inline_css'])): ?>
        <style><?= $page['inline_css'] ?></style>
    <?php endif; ?>

    <?= $extractedStyles ?>

    <?php if ($css): ?>
    <style id="page-custom-css">
        <?= $css ?>
    </style>
    <?php endif; ?>

    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[
        {"@type":"ListItem","position":1,"name":"Accueil","item":"<?= $_siteUrl ?>/"},
        {"@type":"ListItem","position":2,"name":"<?= htmlspecialchars($page['title']) ?>","item":"<?= htmlspecialchars($canonical) ?>"}
    ]}
    </script>
</head>
<body>

<?php if ($headerHtml): echo $headerHtml; endif; ?>

<main id="main-content">
<div id="page-content">
    <?= $html ?>
</div>
</main>

<?php if ($footerHtml): echo $footerHtml; endif; ?>

<?php foreach ($externalJs as $jsUrl): ?>
    <script src="<?= htmlspecialchars($jsUrl) ?>"></script>
<?php endforeach; ?>
<?php if ($js): ?>
    <script><?= $js ?></script>
<?php endif; ?>
<?php if (!empty($page['custom_js']) && $js !== $page['custom_js']): ?>
    <script><?= $page['custom_js'] ?></script>
<?php endif; ?>

</body>
</html>