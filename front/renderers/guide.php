<?php
/**
 * ============================================================
 * renderers/guide.php  v3
 * Renderer : Guide local single (/guide-local/{slug})
 * ============================================================
 *
 * PRINCIPE : zéro CSS hardcodé ici.
 * Le design vient entièrement du Builder Pro :
 *   - Header/Footer actifs depuis la DB
 *   - Contenu du guide = champ `contenu` de la table `guides`
 *     (éditable depuis admin/modules/content/guide-local/)
 *   - Si un template builder_templates de type 'guide'
 *     est actif → il remplace tout le <main>
 *
 * Table source : `guides`
 * Champs FR    : titre, slug, contenu, description, categorie,
 *                statut/status, image, seo_title, seo_description,
 *                date_publication, downloads_count
 * ============================================================
 */

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
global $db;
if (!$db) $db = Database::getInstance();

// ── Charger le guide depuis l'URL ────────────────────────
$guideSlug = $_GET['slug'] ?? '';
if (!$guideSlug) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$guide = null;
try {
    $stmt = $db->prepare("
        SELECT * FROM guides
        WHERE slug = ?
          AND (statut = 'publie' OR status = 'published')
        LIMIT 1
    ");
    $stmt->execute([$guideSlug]);
    $guide = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("guide.php load: " . $e->getMessage());
}

if (!$guide) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

// ── Incrémenter vues (silencieux) ────────────────────────
try {
    $db->prepare("UPDATE guides SET downloads_count = downloads_count + 1 WHERE id = ?")->execute([$guide['id']]);
} catch (Exception $e) {}

// ── Normalisation des champs FR/EN ───────────────────────
$guideTitle   = $guide['titre']            ?? '';
$guideContent = $guide['contenu']          ?? '';
$guideDesc    = $guide['description']      ?? '';
$guideCat     = $guide['categorie']        ?? '';
$guideImage   = $guide['image']            ?? '';
$pubDate      = $guide['date_publication'] ?? $guide['created_at'] ?? '';
$seoTitle     = $guide['seo_title']        ?? $guideTitle;
$seoDesc      = $guide['seo_description']  ?? $guideDesc;

// ── Guides liés (même catégorie) ────────────────────────
$guidesLies = [];
try {
    $params = [$guide['id']];
    $catSql = '';
    if ($guideCat) {
        $catSql   = " AND categorie = ?";
        $params[] = $guideCat;
    }
    $stmt = $db->prepare("
        SELECT id, titre, slug, description, image, categorie
        FROM guides
        WHERE (statut = 'publie' OR status = 'published')
          AND id != ?
          $catSql
        ORDER BY COALESCE(date_publication, created_at) DESC
        LIMIT 3
    ");
    $stmt->execute($params);
    $guidesLies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si pas assez, compléter sans filtre catégorie
    if (count($guidesLies) < 3) {
        $exclude   = array_merge([$guide['id']], array_column($guidesLies, 'id'));
        $inClause  = implode(',', array_fill(0, count($exclude), '?'));
        $stmt2     = $db->prepare("
            SELECT id, titre, slug, description, image, categorie
            FROM guides
            WHERE (statut = 'publie' OR status = 'published')
              AND id NOT IN ($inClause)
            ORDER BY COALESCE(date_publication, created_at) DESC
            LIMIT " . (3 - count($guidesLies))
        );
        $stmt2->execute($exclude);
        $guidesLies = array_merge($guidesLies, $stmt2->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {}

// ── Template builder_templates (optionnel) ───────────────
$tplHtml = '';
$tplCss  = '';
$tplJs   = '';
$useBuilderTemplate = false;

try {
    $stmt = $db->prepare("
        SELECT * FROM builder_templates
        WHERE (type = 'guide' OR category = 'guide')
          AND status = 'active'
        ORDER BY is_default DESC, id DESC
        LIMIT 1
    ");
    $stmt->execute();
    $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($tpl && (!empty($tpl['html']) || !empty($tpl['content']))) {
        $vars    = buildGuideVarsV3($guide, $db);
        $raw     = $tpl['html'] ?? $tpl['content'] ?? '';
        $tplHtml = str_replace(array_keys($vars), array_values($vars), $raw);
        $tplCss  = str_replace(array_keys($vars), array_values($vars), $tpl['css'] ?? $tpl['custom_css'] ?? '');
        $tplJs   = str_replace(array_keys($vars), array_values($vars), $tpl['js']  ?? $tpl['custom_js']  ?? '');
        $useBuilderTemplate = true;
    }
} catch (Exception $e) {
    error_log("guide.php template: " . $e->getMessage());
}

// ── Header / Footer ──────────────────────────────────────
$hfHeader = null;
$hfFooter = null;

if (function_exists('getHeaderFooter')) {
    $hf       = getHeaderFooter($db, $guideSlug);
    $hfHeader = $hf['header'];
    $hfFooter = $hf['footer'];
} else {
    foreach ([['headers','site_headers'],['footers','site_footers']] as $idx => $tables) {
        $found = null;
        foreach ($tables as $tbl) {
            try {
                $found = $db->query("SELECT * FROM `$tbl` WHERE status='active' ORDER BY is_default DESC, id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($found) break;
            } catch (Exception $e) {}
        }
        if ($idx === 0) $hfHeader = $found;
        else            $hfFooter = $found;
    }
}

// ── Métadonnées ──────────────────────────────────────────
$_siteUrl  = function_exists('siteUrl')  ? siteUrl()  : (defined('SITE_URL')   ? SITE_URL   : '');
$_siteName = function_exists('siteName') ? siteName() : (defined('SITE_TITLE') ? SITE_TITLE : '');
$canonical = $_siteUrl . '/guide-local/' . $guideSlug;
$metaTitle = htmlspecialchars($seoTitle . ' | ' . $_siteName);
$metaDesc  = htmlspecialchars($seoDesc);

// Temps de lecture
$readTime = 1;
if (function_exists('readingTime')) {
    $readTime = readingTime($guideContent);
} elseif ($guideContent) {
    $readTime = max(1, (int)(str_word_count(strip_tags($guideContent)) / 200));
}

// Format date FR
$pubDateFr = '';
if ($pubDate) {
    if (function_exists('formatDateFr')) {
        $pubDateFr = formatDateFr($pubDate);
    } else {
        $ts        = strtotime($pubDate);
        $mois      = ['jan.','fév.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
        $pubDateFr = $ts ? (date('d',$ts).' '.$mois[(int)date('m',$ts)-1].' '.date('Y',$ts)) : '';
    }
}

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

    <meta property="og:title"       content="<?= htmlspecialchars($seoTitle) ?>">
    <meta property="og:type"        content="article">
    <meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:site_name"   content="<?= htmlspecialchars($_siteName) ?>">
    <?php if ($metaDesc):  ?><meta property="og:description" content="<?= $metaDesc ?>"><?php endif; ?>
    <?php if ($guideImage): ?><meta property="og:image"       content="<?= htmlspecialchars($guideImage) ?>"><?php endif; ?>

    <?php if (function_exists('eduardoHead')): echo eduardoHead(); endif; ?>

    <!-- Styles extraits du header/footer (remontés dans <head>) -->
    <?= $extractedStyles ?>

    <?php if (!empty($hfHeader['custom_css'])): ?><style><?= $hfHeader['custom_css'] ?></style><?php endif; ?>
    <?php if (!empty($hfFooter['custom_css'])): ?><style><?= $hfFooter['custom_css'] ?></style><?php endif; ?>
    <?php if ($tplCss): ?><style id="guide-tpl-css"><?= $tplCss ?></style><?php endif; ?>

    <!-- Schema.org -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "articleSection": "Guide",
        "headline": <?= json_encode($guideTitle) ?>,
        "description": <?= json_encode(strip_tags($guideDesc ?: $seoDesc)) ?>,
        "url": <?= json_encode($canonical) ?>,
        "publisher": {"@type":"Organization","name":<?= json_encode($_siteName) ?>}
        <?php if ($pubDate): ?>,"datePublished":<?= json_encode(date('c', strtotime($pubDate))) ?><?php endif; ?>
        <?php if ($guideImage): ?>,"image":<?= json_encode($guideImage) ?><?php endif; ?>
    }
    </script>
    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[
        {"@type":"ListItem","position":1,"name":"Accueil","item":"<?= htmlspecialchars($_siteUrl) ?>/"},
        {"@type":"ListItem","position":2,"name":"Guides","item":"<?= htmlspecialchars($_siteUrl) ?>/guide-local"},
        {"@type":"ListItem","position":3,"name":<?= json_encode($guideTitle) ?>,"item":"<?= htmlspecialchars($canonical) ?>"}
    ]}
    </script>
</head>
<body>

<?php echo $headerHtml; ?>

<main id="main-content">

<?php if ($useBuilderTemplate): ?>

    <!-- ══ Template Builder Pro ══ -->
    <?= $tplHtml ?>

<?php else: ?>

    <!-- ══ Rendu direct du contenu guide ══ -->
    <!-- Breadcrumb minimal sémantique (sans style imposé) -->
    <nav aria-label="Fil d'Ariane">
        <a href="/">Accueil</a> ›
        <a href="/guide-local">Guides</a> ›
        <span><?= htmlspecialchars($guideTitle) ?></span>
    </nav>

    <!-- En-tête guide -->
    <header id="guide-header">
        <?php if ($guideCat): ?>
        <div id="guide-category"><?= htmlspecialchars($guideCat) ?></div>
        <?php endif; ?>

        <h1 id="guide-title"><?= htmlspecialchars($guideTitle) ?></h1>

        <?php if ($guideDesc): ?>
        <p id="guide-excerpt"><?= htmlspecialchars($guideDesc) ?></p>
        <?php endif; ?>

        <div id="guide-meta">
            <?php if ($pubDateFr): ?><span id="guide-date"><?= $pubDateFr ?></span><?php endif; ?>
            <span id="guide-read-time"><?= $readTime ?> min de lecture</span>
        </div>
    </header>

    <?php if ($guideImage): ?>
    <figure id="guide-featured-image">
        <img src="<?= htmlspecialchars($guideImage) ?>"
             alt="<?= htmlspecialchars($guideTitle) ?>"
             loading="eager">
    </figure>
    <?php endif; ?>

    <!-- Corps du guide (HTML édité depuis admin) -->
    <div id="guide-body">
        <?php if ($guideContent): ?>
            <?= $guideContent ?>
        <?php else: ?>
            <p><em>Contenu en cours de rédaction.</em></p>
        <?php endif; ?>
    </div>

    <!-- Partage -->
    <?php
    $shareUrl   = urlencode($canonical);
    $shareTitle = urlencode($guideTitle);
    ?>
    <div id="guide-share">
        <span>Partager :</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener">Facebook</a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener">LinkedIn</a>
        <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener">WhatsApp</a>
    </div>

    <!-- Guides liés -->
    <?php if (!empty($guidesLies)): ?>
    <section id="guides-lies">
        <h2>Guides similaires</h2>
        <div id="guides-lies-grid">
            <?php foreach ($guidesLies as $gl): ?>
            <a href="/guide-local/<?= htmlspecialchars($gl['slug']) ?>" class="guide-lie-item">
                <?php if (!empty($gl['image'])): ?>
                    <img src="<?= htmlspecialchars($gl['image']) ?>"
                         alt="<?= htmlspecialchars($gl['titre'] ?? '') ?>"
                         loading="lazy">
                <?php endif; ?>
                <div class="guide-lie-body">
                    <?php if (!empty($gl['categorie'])): ?>
                    <div class="guide-lie-cat"><?= htmlspecialchars($gl['categorie']) ?></div>
                    <?php endif; ?>
                    <div class="guide-lie-title"><?= htmlspecialchars($gl['titre'] ?? '') ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

<?php endif; // fin if useBuilderTemplate ?>

</main>

<?php echo $footerHtml; ?>

<?php if ($tplJs): ?><script><?= $tplJs ?></script><?php endif; ?>

<script>
(function () {
    // Barre de progression lecture
    var bar = document.getElementById('guide-progress');
    if (bar) {
        window.addEventListener('scroll', function () {
            var h   = document.documentElement;
            var pct = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
            bar.style.width = Math.min(100, pct) + '%';
        }, { passive: true });
    }

    // Sommaire auto depuis les H2 du corps
    var body = document.getElementById('guide-body');
    var toc  = document.getElementById('guide-toc');
    if (body && toc) {
        var headings = body.querySelectorAll('h2');
        if (headings.length === 0) {
            var parent = toc.parentElement;
            if (parent) parent.style.display = 'none';
            return;
        }
        toc.innerHTML = '';
        headings.forEach(function (h, i) {
            h.id = 'guide-section-' + i;
            var li = document.createElement('li');
            var a  = document.createElement('a');
            a.href        = '#guide-section-' + i;
            a.textContent = h.textContent;
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var t = document.getElementById('guide-section-' + i);
                if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            li.appendChild(a);
            toc.appendChild(li);
        });

        // Lien actif au scroll
        window.addEventListener('scroll', function () {
            var current = '';
            headings.forEach(function (h) {
                if (window.scrollY >= h.offsetTop - 140) current = h.id;
            });
            toc.querySelectorAll('a').forEach(function (a) {
                a.classList.toggle('active', a.getAttribute('href') === '#' + current);
            });
        }, { passive: true });
    }
})();
</script>

</body>
</html>

<?php
// ════════════════════════════════════════════════════════════
// buildGuideVarsV3() — variables pour builder_templates
// ════════════════════════════════════════════════════════════
if (!function_exists('buildGuideVarsV3')) {
    function buildGuideVarsV3(array $guide, PDO $db): array
    {
        $siteUrl  = function_exists('siteUrl')  ? siteUrl()  : (defined('SITE_URL')   ? SITE_URL   : '');
        $siteName = function_exists('siteName') ? siteName() : (defined('SITE_TITLE') ? SITE_TITLE : '');
        $phone    = function_exists('_ss')      ? _ss('phone', '') : '';
        $pubDate  = $guide['date_publication']  ?? $guide['created_at'] ?? '';
        $pubDateFr = '';
        if ($pubDate) {
            if (function_exists('formatDateFr')) {
                $pubDateFr = formatDateFr($pubDate);
            } else {
                $ts        = strtotime($pubDate);
                $mois      = ['jan.','fév.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
                $pubDateFr = $ts ? date('d',$ts).' '.$mois[(int)date('m',$ts)-1].' '.date('Y',$ts) : '';
            }
        }
        $content  = $guide['contenu']  ?? '';
        $readTime = function_exists('readingTime') ? readingTime($content) : max(1, (int)(str_word_count(strip_tags($content)) / 200));

        return [
            '{{title}}'        => htmlspecialchars($guide['titre']            ?? ''),
            '{{titre}}'        => htmlspecialchars($guide['titre']            ?? ''),
            '{{content}}'      => $content,
            '{{contenu}}'      => $content,
            '{{description}}'  => htmlspecialchars($guide['description']      ?? ''),
            '{{excerpt}}'      => htmlspecialchars($guide['description']      ?? ''),
            '{{image}}'        => htmlspecialchars($guide['image']            ?? ''),
            '{{hero_image}}'   => htmlspecialchars($guide['image']            ?? ''),
            '{{categorie}}'    => htmlspecialchars($guide['categorie']        ?? ''),
            '{{category}}'     => htmlspecialchars($guide['categorie']        ?? ''),
            '{{theme}}'        => htmlspecialchars($guide['categorie']        ?? ''),
            '{{slug}}'         => htmlspecialchars($guide['slug']             ?? ''),
            '{{url}}'          => htmlspecialchars($siteUrl . '/guide-local/' . ($guide['slug'] ?? '')),
            '{{date}}'         => $pubDateFr,
            '{{date_raw}}'     => htmlspecialchars($pubDate),
            '{{read_time}}'    => $readTime . ' min',
            '{{downloads}}'    => (string)(int)($guide['downloads_count']    ?? 0),
            '{{seo_title}}'    => htmlspecialchars($guide['seo_title']        ?? $guide['titre'] ?? ''),
            '{{seo_desc}}'     => htmlspecialchars($guide['seo_description']  ?? $guide['description'] ?? ''),
            '{{phone}}'        => htmlspecialchars($phone),
            '{{phone_clean}}'  => htmlspecialchars(preg_replace('/\s+/', '', $phone)),
            '{{site_name}}'    => htmlspecialchars($siteName),
            '{{site_url}}'     => htmlspecialchars($siteUrl),
            '{{year}}'         => date('Y'),
        ];
    }
}