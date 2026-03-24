<?php
/**
 * ============================================================
 * renderers/guide-listing.php
 * Hub /guide-local — contenu éditorial depuis Builder Pro
 * + liste dynamique des guides (table `guides`)
 * ============================================================
 *
 * PRINCIPE : zéro CSS hardcodé ici.
 * Le design vient entièrement du Builder Pro :
 *   - Header/Footer actifs depuis la DB
 *   - Contenu "au-dessus" = page hub slug='guide-local' dans
 *     builder_pages ou pages (éditable depuis l'admin)
 *   - La liste des guides est injectée via le bloc spécial
 *     {{guides_list}} dans le contenu du hub, OU affichée
 *     automatiquement après le contenu hub si absent
 *
 * URL : /guide-local          → ce fichier
 * URL : /guide-local/{slug}   → renderers/guide.php
 * ============================================================
 */

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

global $db;
if (!$db) $db = getDB();

// ── Filtres URL ──────────────────────────────────────────
$catFilter = trim($_GET['categorie'] ?? $_GET['cat'] ?? '');
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 12;
$offset    = ($page - 1) * $perPage;

// ── 1. Page hub depuis builder_pages ou pages ────────────
$hubPage   = null;
$hubHtml   = '';
$hubCss    = '';
$hubJs     = '';

// Cherche dans builder_pages (priorité)
try {
    $stmt = $db->prepare("SELECT * FROM builder_pages WHERE slug = 'guide-local' AND status = 'published' LIMIT 1");
    $stmt->execute();
    $hubPage = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($hubPage) {
        // Rendu blocks_data si dispo
        if (!empty($hubPage['blocks_data']) && $hubPage['blocks_data'] !== '[]') {
            if (function_exists('renderBlocksData')) {
                $hubHtml = renderBlocksData($hubPage['blocks_data']);
            }
        }
        $hubCss = $hubPage['custom_css'] ?? '';
        $hubJs  = $hubPage['custom_js']  ?? '';
    }
} catch (Exception $e) {
    error_log("guide-listing hub builder_pages: " . $e->getMessage());
}

// Fallback : table pages
if (!$hubPage) {
    try {
        $stmt = $db->prepare("SELECT * FROM pages WHERE slug = 'guide-local' AND status = 'published' LIMIT 1");
        $stmt->execute();
        $hubPage = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($hubPage) {
            $hubHtml = $hubPage['content'] ?? '';
            $hubCss  = $hubPage['custom_css'] ?? '';
            $hubJs   = $hubPage['custom_js']  ?? '';
        }
    } catch (Exception $e) {
        error_log("guide-listing hub pages: " . $e->getMessage());
    }
}

// ── 2. Catégories disponibles ────────────────────────────
$categories = [];
try {
    $stmt = $db->query("
        SELECT categorie AS cat, COUNT(*) AS nb
        FROM guides
        WHERE statut = 'publie' OR status = 'published'
        GROUP BY categorie
        ORDER BY nb DESC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ── 3. Guides publiés ────────────────────────────────────
$guides = [];
$total  = 0;
try {
    $where  = "(statut = 'publie' OR status = 'published')";
    $params = [];
    if ($catFilter) {
        $where  .= " AND categorie = ?";
        $params[] = $catFilter;
    }
    $countStmt = $db->prepare("SELECT COUNT(*) FROM guides WHERE $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT id, titre, slug, description, categorie, image,
               statut, status, date_publication, created_at, downloads_count
        FROM guides
        WHERE $where
        ORDER BY COALESCE(date_publication, created_at) DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("guide-listing guides query: " . $e->getMessage());
}

$totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

// ── 4. Construire le HTML de la liste des guides ─────────
// Ce bloc est injecté dans {{guides_list}} ou affiché après
function renderGuidesList(array $guides, array $categories, string $catFilter, int $total, int $page, int $totalPages, string $siteUrl): string
{
    if (empty($guides) && empty($categories)) {
        return '<div style="text-align:center;padding:48px 24px;opacity:.6">
            <p>Aucun guide disponible pour le moment.</p>
        </div>';
    }

    $paginBase = '/guide-local' . ($catFilter ? '?categorie=' . urlencode($catFilter) . '&' : '?') . 'page=';

    $out = '';

    // Filtres catégories
    if (!empty($categories)) {
        $out .= '<nav style="margin-bottom:32px" aria-label="Filtrer par catégorie">';
        $out .= '<a href="/guide-local" style="display:inline-block;margin:4px;padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid currentColor;' . (!$catFilter ? 'background:currentColor' : '') . '">Tous (' . $total . ')</a>';
        foreach ($categories as $cat) {
            $active = ($catFilter === $cat['cat']);
            $out .= '<a href="/guide-local?categorie=' . urlencode($cat['cat']) . '"'
                  . ' style="display:inline-block;margin:4px;padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid currentColor;' . ($active ? 'opacity:1;font-weight:700' : 'opacity:.65') . '">'
                  . htmlspecialchars($cat['cat']) . ' (' . (int)$cat['nb'] . ')</a>';
        }
        $out .= '</nav>';
    }

    // Grille guides
    $out .= '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;margin-bottom:40px">';
    foreach ($guides as $g) {
        $img   = htmlspecialchars($g['image'] ?? '');
        $titre = htmlspecialchars($g['titre'] ?? '');
        $slug  = htmlspecialchars($g['slug']  ?? '');
        $cat   = htmlspecialchars($g['categorie'] ?? '');
        $desc  = htmlspecialchars(mb_substr(strip_tags($g['description'] ?? ''), 0, 120));
        $dl    = (int)($g['downloads_count'] ?? 0);
        $url   = '/guide-local/' . $slug;

        $out .= '<a href="' . $url . '" style="display:block;text-decoration:none;color:inherit">';
        $out .= '<article>';

        // Image ou placeholder
        if ($img) {
            $out .= '<img src="' . $img . '" alt="' . $titre . '" style="width:100%;height:180px;object-fit:cover;display:block">';
        } else {
            $out .= '<div style="width:100%;height:180px;display:flex;align-items:center;justify-content:center;font-size:40px">📖</div>';
        }

        $out .= '<div style="padding:16px">';
        if ($cat) {
            $out .= '<div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">' . $cat . '</div>';
        }
        $out .= '<h3 style="font-size:16px;font-weight:700;margin:0 0 8px;line-height:1.3">' . $titre . '</h3>';
        if ($desc) {
            $out .= '<p style="font-size:13px;margin:0 0 12px;opacity:.7;line-height:1.5">' . $desc . ($dl > 0 ? '…' : '') . '</p>';
        }
        if ($dl > 0) {
            $out .= '<div style="font-size:12px;opacity:.5">' . $dl . ' téléchargement' . ($dl > 1 ? 's' : '') . '</div>';
        }
        $out .= '<div style="margin-top:12px;font-size:13px;font-weight:600">Lire le guide →</div>';
        $out .= '</div></article></a>';
    }
    $out .= '</div>';

    // Pagination
    if ($totalPages > 1) {
        $out .= '<nav style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-top:32px" aria-label="Pagination">';
        if ($page > 1) {
            $out .= '<a href="' . $paginBase . ($page - 1) . '" style="padding:8px 16px;text-decoration:none;font-weight:600">← Précédent</a>';
        }
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i === $page);
            $out .= '<a href="' . $paginBase . $i . '" style="padding:8px 16px;text-decoration:none;font-weight:' . ($active ? '700' : '400') . ';' . ($active ? 'text-decoration:underline' : '') . '">' . $i . '</a>';
        }
        if ($page < $totalPages) {
            $out .= '<a href="' . $paginBase . ($page + 1) . '" style="padding:8px 16px;text-decoration:none;font-weight:600">Suivant →</a>';
        }
        $out .= '</nav>';
    }

    return $out;
}

$guidesList = renderGuidesList($guides, $categories, $catFilter, $total, $page, $totalPages, function_exists('siteUrl') ? siteUrl() : '');

// Injecter {{guides_list}} si présent dans hubHtml, sinon ajouter après
if (strpos($hubHtml, '{{guides_list}}') !== false) {
    $hubHtml = str_replace('{{guides_list}}', $guidesList, $hubHtml);
    $appendList = false;
} else {
    $appendList = true;
}

// ── 5. Header / Footer ───────────────────────────────────
$hfHeader = null;
$hfFooter = null;
$hid = $hubPage['header_id'] ?? null;
$fid = $hubPage['footer_id'] ?? null;

foreach ([['headers','site_headers'], ['footers','site_footers']] as $idx => $tables) {
    $specificId = ($idx === 0) ? $hid : $fid;
    $found = null;
    if ($specificId) {
        foreach ($tables as $tbl) {
            try {
                $s = $db->prepare("SELECT * FROM `$tbl` WHERE id = ? LIMIT 1");
                $s->execute([$specificId]);
                $found = $s->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($found) break;
            } catch (Exception $e) {}
        }
    }
    if (!$found) {
        foreach ($tables as $tbl) {
            try {
                $found = $db->query("SELECT * FROM `$tbl` WHERE status='active' ORDER BY is_default DESC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($found) break;
            } catch (Exception $e) {}
        }
    }
    if ($idx === 0) $hfHeader = $found;
    else            $hfFooter = $found;
}

// ── 6. Métadonnées ───────────────────────────────────────
$_siteUrl  = function_exists('siteUrl')  ? siteUrl()  : (defined('SITE_URL')  ? SITE_URL  : '');
$_siteName = function_exists('siteName') ? siteName() : (defined('SITE_TITLE') ? SITE_TITLE : '');
$metaTitle = $hubPage['meta_title'] ?? ($catFilter ? 'Guides · ' . $catFilter : 'Guides immobiliers Bordeaux');
$metaTitle = htmlspecialchars($metaTitle . ' | ' . $_siteName);
$metaDesc  = htmlspecialchars($hubPage['meta_description'] ?? 'Guides pratiques pour acheter, vendre et investir à Bordeaux par Eduardo De Sul, conseiller immobilier indépendant eXp France.');
$canonical = $_siteUrl . '/guide-local' . ($catFilter ? '?categorie=' . urlencode($catFilter) : '') . ($page > 1 ? ($catFilter ? '&' : '?') . 'page=' . $page : '');

// ── Extraire CSS embarqués dans header/footer HTML ───────
$headerHtml      = '';
$footerHtml      = '';
$extractedStyles = '';

if ($hfHeader && function_exists('renderHeader')) {
    $headerHtml = renderHeader($hfHeader);
}
if ($hfFooter && function_exists('renderFooter')) {
    $footerHtml = renderFooter($hfFooter);
}

// Extraire les <style> du header/footer pour les monter dans <head>
$stripStyles = function (&$html) use (&$extractedStyles) {
    $html = preg_replace_callback(
        '/<style(\b[^>]*)>(.+?)<\/style>/is',
        function ($m) use (&$extractedStyles) {
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
    <title><?= $metaTitle ?></title>
    <?php if ($metaDesc): ?><meta name="description" content="<?= $metaDesc ?>"><?php endif; ?>
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:title"       content="<?= $metaTitle ?>">
    <meta property="og:description" content="<?= $metaDesc ?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:site_name"   content="<?= htmlspecialchars($_siteName) ?>">

    <?php if (function_exists('eduardoHead')): echo eduardoHead(); endif; ?>

    <!-- Styles extraits du header/footer (remontés dans <head>) -->
    <?= $extractedStyles ?>

    <?php if (!empty($hfHeader['custom_css'])): ?><style><?= $hfHeader['custom_css'] ?></style><?php endif; ?>
    <?php if (!empty($hfFooter['custom_css'])): ?><style><?= $hfFooter['custom_css'] ?></style><?php endif; ?>
    <?php if ($hubCss): ?><style id="hub-custom-css"><?= $hubCss ?></style><?php endif; ?>

    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[
        {"@type":"ListItem","position":1,"name":"Accueil","item":"<?= htmlspecialchars($_siteUrl) ?>/"},
        {"@type":"ListItem","position":2,"name":"Guides","item":"<?= htmlspecialchars($canonical) ?>"}
    ]}
    </script>
    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"CollectionPage",
     "name":<?= json_encode(strip_tags($metaTitle)) ?>,
     "description":<?= json_encode(strip_tags($metaDesc)) ?>,
     "url":<?= json_encode($canonical) ?>}
    </script>
</head>
<body>

<?php echo $headerHtml; ?>

<main id="main-content">

    <?php if ($hubHtml): ?>
        <!-- Contenu hub éditable depuis le Builder Pro -->
        <div id="hub-content">
            <?= $hubHtml ?>
        </div>
    <?php endif; ?>

    <?php if ($appendList): ?>
        <!-- Liste des guides — appended après le contenu hub -->
        <div id="guides-list-wrap">
            <?= $guidesList ?>
        </div>
    <?php endif; ?>

    <?php if (!$hubHtml && empty($guides)): ?>
        <!-- Fallback : aucun contenu et aucun guide -->
        <div style="text-align:center;padding:80px 24px">
            <h1>Guides immobiliers</h1>
            <p>Les guides seront bientôt disponibles.</p>
            <a href="/">Retour à l'accueil</a>
        </div>
    <?php endif; ?>

</main>

<?php echo $footerHtml; ?>

<?php if ($hubJs): ?><script><?= $hubJs ?></script><?php endif; ?>

</body>
</html>