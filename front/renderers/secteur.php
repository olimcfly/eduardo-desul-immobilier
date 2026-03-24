<?php
/**
 * ============================================================
 * renderers/secteur.php  v2.1
 * Renderer : Page secteur / quartier (single)
 * ============================================================
 *
 * Reçoit :
 *   $db      PDO instance
 *   $secteur array  enregistrement DB complet
 *   OU charge depuis $_GET['slug'] si $secteur absent
 *
 * Priorité contenu :
 *   1. Builder Pro : secteur.content (html long) + custom_css/custom_js
 *   2. Template builder_templates WHERE type='secteur'
 *   3. Template par défaut Eduardo (brand)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

// ─────────────────────────────────────────────────
// Charger le secteur si pas injecté par le router
// ─────────────────────────────────────────────────
if (!isset($secteur) || empty($secteur)) {
    global $pdo;
    $db   = $db   ?? $pdo;
    $slug = $_GET['slug'] ?? '';
    if (!$slug) { http_response_code(404); require_once __DIR__ . '/404.php'; exit; }
    try {
        $stmt = $db->prepare("SELECT * FROM secteurs WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $secteur = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("secteur.php load error: " . $e->getMessage());
        $secteur = null;
    }
    if (!$secteur) {
        http_response_code(404);
        require_once __DIR__ . '/404.php';
        exit;
    }
}

$db = $db ?? $pdo ?? null;
if (!$db) { http_response_code(500); exit('DB non disponible'); }

// ─────────────────────────────────────────────────
// Helpers locaux (si non définis dans init.php)
// ─────────────────────────────────────────────────
if (!function_exists('_jsonDecode')) {
    function _jsonDecode($val): array {
        if (empty($val)) return [];
        if (is_array($val)) return $val;
        $r = json_decode($val, true);
        return is_array($r) ? $r : [];
    }
}
if (!function_exists('_truncate')) {
    function _truncate(string $str, int $len = 160): string {
        return mb_strlen($str) > $len ? mb_substr($str, 0, $len) . '…' : $str;
    }
}

// ─────────────────────────────────────────────────
// Normalisation des champs (nom / name / title)
// ─────────────────────────────────────────────────
$sectName  = $secteur['nom']   ?? $secteur['name']  ?? $secteur['title'] ?? '';
$sectSlug  = $secteur['slug']  ?? '';
$sectVille = $secteur['ville'] ?? $secteur['city']  ?? $sectName;

// Contenu principal (Builder Pro)
$builderHtml = $secteur['content']    ?? '';   // colonne Builder Pro
$builderCss  = $secteur['custom_css'] ?? '';
$builderJs   = $secteur['custom_js']  ?? '';

// Description courte
$sectDesc    = $secteur['description'] ?? $secteur['presentation'] ?? '';
if (is_array($sectDesc)) $sectDesc = '';  // JSON → skip

// ─────────────────────────────────────────────────
// 1. Cherche template builder_templates si pas de contenu Builder
// ─────────────────────────────────────────────────
$tplHtml = ''; $tplCss = ''; $tplJs = '';
$useBuilderTemplate = false;

if (empty($builderHtml)) {
    try {
        $stmt = $db->prepare("
            SELECT * FROM builder_templates
            WHERE (type = 'secteur' OR category = 'secteur') AND status = 'active'
            ORDER BY is_default DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute();
        $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tpl && !empty($tpl['html'])) {
            $vars    = buildSecteurVars($secteur, $db);
            $tplHtml = str_replace(array_keys($vars), array_values($vars), $tpl['html']);
            $tplCss  = str_replace(array_keys($vars), array_values($vars), $tpl['css'] ?? '');
            $tplJs   = str_replace(array_keys($vars), array_values($vars), $tpl['js']  ?? '');
            $useBuilderTemplate = true;
        }
    } catch (Exception $e) {
        error_log("Secteur template load: " . $e->getMessage());
    }
}

// ─────────────────────────────────────────────────
// 2. Biens disponibles dans ce secteur
// ─────────────────────────────────────────────────
$biens = [];
try {
    $stmt = $db->prepare("
        SELECT id, title, slug, type, transaction, price, surface, rooms, images, city
        FROM properties
        WHERE status = 'available'
          AND (city LIKE ? OR city LIKE ?)
        ORDER BY featured DESC, created_at DESC
        LIMIT 6
    ");
    $stmt->execute(['%' . $sectVille . '%', '%' . $sectSlug . '%']);
    $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ─────────────────────────────────────────────────
// 3. Secteurs voisins
// ─────────────────────────────────────────────────
$voisins = [];
try {
    $stmt = $db->prepare("
        SELECT id, nom, slug, ville, hero_image, description
        FROM secteurs
        WHERE status = 'published' AND id != ?
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([$secteur['id']]);
    $voisins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ─────────────────────────────────────────────────
// 4. Header / Footer
// ─────────────────────────────────────────────────
$hf = ['header' => null, 'footer' => null];
if (function_exists('getHeaderFooter')) {
    $hf = getHeaderFooter($db, $sectSlug);
} else {
    try {
        // Respecte header_id/footer_id du secteur si définis
        $hid = $secteur['header_id'] ?? null;
        $fid = $secteur['footer_id'] ?? null;
        if ($hid) {
            $s = $db->prepare("SELECT * FROM site_headers WHERE id = ? LIMIT 1");
            $s->execute([$hid]); $hf['header'] = $s->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if (!$hf['header']) {
            $hf['header'] = $db->query("SELECT * FROM site_headers WHERE status='active' ORDER BY is_default DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if ($fid) {
            $s = $db->prepare("SELECT * FROM site_footers WHERE id = ? LIMIT 1");
            $s->execute([$fid]); $hf['footer'] = $s->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if (!$hf['footer']) {
            $hf['footer'] = $db->query("SELECT * FROM site_footers WHERE status='active' ORDER BY is_default DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    } catch (Exception $e) {}
}

// ─────────────────────────────────────────────────
// 5. Métadonnées
// ─────────────────────────────────────────────────
$_siteUrl  = function_exists('siteUrl')  ? siteUrl()  : ((!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http').'://'.$_SERVER['HTTP_HOST'];
$_siteName = function_exists('siteName') ? siteName() : 'Eduardo De Sul Immobilier';
$metaTitle = htmlspecialchars($secteur['meta_title'] ?: 'Immobilier ' . $sectName . ' | ' . $_siteName);
$metaDesc  = htmlspecialchars($secteur['meta_description'] ?? 'Découvrez le marché immobilier à ' . $sectName . '. Acheter, vendre ou estimer votre bien avec Eduardo De Sul.');
$canonical = $_siteUrl . '/' . $sectSlug;
$ogImage   = $secteur['hero_image'] ?? $secteur['image'] ?? '';

// Stats
$prixMoyen = $secteur['prix_moyen']    ?? '';
$prixM2    = $secteur['prix_moyen_m2'] ?? $secteur['prix_m2'] ?? '';
if ($prixM2) $prixM2 = number_format((int)$prixM2, 0, ',', ' ');
$population  = $secteur['population']  ?? '';
$phone       = function_exists('_ss') ? _ss('phone', '06 24 10 58 16') : '06 24 10 58 16';
$phoneclean  = preg_replace('/\s+/', '', $phone);

$transports = _jsonDecode($secteur['transport'] ?? $secteur['transports'] ?? '');
$atouts     = _jsonDecode($secteur['atouts']    ?? '');
$faq        = _jsonDecode($secteur['faq']       ?? '');

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $metaTitle ?></title>
    <meta name="description" content="<?= $metaDesc ?>">
    <meta name="robots" content="<?= htmlspecialchars($secteur['meta_robots'] ?? 'index, follow') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($sectName . ' | Immobilier ' . $_siteName) ?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:description" content="<?= $metaDesc ?>">
    <?php if ($ogImage): ?><meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>

    <?php if (function_exists('eduardoHead')): echo eduardoHead(); else: ?>
    <link rel="stylesheet" href="/front/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php endif; ?>

    <?php if ($builderCss): ?><style><?= $builderCss ?></style><?php endif; ?>
    <?php if ($tplCss):     ?><style><?= $tplCss ?></style><?php endif; ?>
    <?php if (!empty($hf['header']['custom_css'])): ?><style><?= $hf['header']['custom_css'] ?></style><?php endif; ?>
    <?php if (!empty($hf['footer']['custom_css'])): ?><style><?= $hf['footer']['custom_css'] ?></style><?php endif; ?>

    <!-- Schema.org Place -->
    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"Place","name":<?= json_encode($sectName) ?>,"description":<?= json_encode(strip_tags($sectDesc ?: $metaDesc)) ?>,"url":<?= json_encode($canonical) ?><?php if (!empty($secteur['latitude']) && !empty($secteur['longitude'])): ?>,"geo":{"@type":"GeoCoordinates","latitude":<?= floatval($secteur['latitude']) ?>,"longitude":<?= floatval($secteur['longitude']) ?>}<?php endif; ?>}
    </script>
    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Accueil","item":"<?= $_siteUrl ?>/"},{"@type":"ListItem","position":2,"name":"Secteurs","item":"<?= $_siteUrl ?>/secteurs"},{"@type":"ListItem","position":3,"name":<?= json_encode($sectName) ?>,"item":"<?= htmlspecialchars($canonical) ?>"}]}
    </script>
</head>
<body>

<!-- Header -->
<?php if (!empty($hf['header'])): echo renderHeader($hf['header']); endif; ?>

<main id="main-content">

<?php if (!empty($builderHtml)): ?>
    <!-- ══ Contenu Builder Pro ══ -->
    <?= $builderHtml ?>

<?php elseif ($useBuilderTemplate): ?>
    <!-- ══ Template builder_templates ══ -->
    <?= $tplHtml ?>

<?php else: ?>

    <!-- ══ Template par défaut Eduardo ══ -->
    <style>
    :root{--ed-primary:#1a4d7a;--ed-primary-dk:#0e3a5c;--ed-accent:#d4a574;--ed-accent-lt:#e8c49a;--ed-text:#2d3748;--ed-text-light:#718096;--ed-card-bg:#f9f6f3;--ed-border:#e2d9ce;--ed-border-lt:#ece8e2;--ff-heading:"Playfair Display",serif;--ff-body:"DM Sans",sans-serif;--ed-radius:8px;--ed-radius-lg:12px;--ed-shadow:0 2px 8px rgba(0,0,0,.07);--ed-shadow-lg:0 8px 30px rgba(0,0,0,.12);--ed-transition:all .2s ease}
    .sec-hero{position:relative;overflow:hidden;min-height:420px;display:flex;align-items:flex-end;background:linear-gradient(135deg,var(--ed-primary-dk),var(--ed-primary))}
    .sec-hero__bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform 8s ease}
    .sec-hero:hover .sec-hero__bg{transform:scale(1.03)}
    .sec-hero__overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(14,58,92,.92) 0%,rgba(14,58,92,.5) 60%,rgba(14,58,92,.2) 100%)}
    .sec-hero__inner{position:relative;z-index:2;max-width:1200px;margin:0 auto;padding:0 24px 52px;width:100%}
    .sec-hero__breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.7);margin-bottom:20px;flex-wrap:wrap}
    .sec-hero__breadcrumb a{color:inherit;text-decoration:none;transition:color .2s}
    .sec-hero__breadcrumb a:hover{color:var(--ed-accent-lt)}
    .sec-hero__breadcrumb span{opacity:.5}
    .sec-hero__badge{display:inline-flex;align-items:center;gap:6px;background:rgba(212,165,116,.2);border:1px solid rgba(212,165,116,.4);color:var(--ed-accent-lt);padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:14px}
    .sec-hero__title{font-family:var(--ff-heading);font-size:clamp(28px,5vw,52px);font-weight:700;color:white;line-height:1.15;margin-bottom:14px}
    .sec-hero__subtitle{font-size:17px;color:rgba(255,255,255,.82);max-width:580px;line-height:1.6;margin-bottom:28px}
    .sec-hero__stats{display:flex;gap:20px;flex-wrap:wrap}
    .sec-hero__stat{background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:var(--ed-radius);padding:12px 20px;text-align:center;min-width:120px}
    .sec-hero__stat-val{font-family:var(--ff-heading);font-size:20px;font-weight:700;color:var(--ed-accent-lt)}
    .sec-hero__stat-lbl{font-size:11px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.5px;margin-top:2px}
    .sec-layout{max-width:1200px;margin:0 auto;padding:56px 24px 80px;display:grid;grid-template-columns:1fr 320px;gap:48px}
    @media(max-width:960px){.sec-layout{grid-template-columns:1fr}}
    .sec-body{font-size:16px;line-height:1.8;color:var(--ed-text)}
    .sec-body h2{font-family:var(--ff-heading);font-size:22px;font-weight:700;color:var(--ed-primary);margin:40px 0 14px;padding-bottom:10px;border-bottom:2px solid var(--ed-accent);display:inline-block}
    .sec-body h3{font-family:var(--ff-heading);font-size:18px;font-weight:600;color:var(--ed-primary);margin:28px 0 10px}
    .sec-body p{margin-bottom:18px}
    .sec-body ul,.sec-body ol{padding-left:22px;margin-bottom:18px}
    .sec-body li{margin-bottom:7px}
    .sec-body a{color:var(--ed-primary);text-decoration:underline}
    .sec-body blockquote{border-left:4px solid var(--ed-accent);padding:16px 20px;margin:28px 0;background:var(--ed-card-bg);border-radius:0 var(--ed-radius) var(--ed-radius) 0;font-style:italic}
    .sec-info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;margin:32px 0}
    .sec-info-card{background:var(--ed-card-bg);border-radius:var(--ed-radius);border:1px solid var(--ed-border);padding:20px}
    .sec-info-card__title{display:flex;align-items:center;gap:10px;font-weight:700;color:var(--ed-primary);font-size:15px;margin-bottom:12px}
    .sec-info-card__icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
    .sec-info-card ul{list-style:none;padding:0;margin:0}
    .sec-info-card li{font-size:13px;color:var(--ed-text-light);padding:5px 0;border-bottom:1px solid var(--ed-border-lt);display:flex;align-items:center;gap:8px}
    .sec-info-card li:last-child{border-bottom:none}
    .sec-info-card li::before{content:'›';color:var(--ed-accent);font-weight:700}
    .sec-biens{margin-top:48px}
    .sec-biens__title{font-family:var(--ff-heading);font-size:22px;font-weight:700;color:var(--ed-primary);margin-bottom:24px}
    .sec-biens__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px}
    .sec-bien-card{background:white;border-radius:var(--ed-radius-lg);border:1px solid var(--ed-border);overflow:hidden;box-shadow:var(--ed-shadow);transition:var(--ed-transition);text-decoration:none;display:block}
    .sec-bien-card:hover{transform:translateY(-4px);box-shadow:var(--ed-shadow-lg);border-color:var(--ed-accent)}
    .sec-bien-card__img{height:180px;object-fit:cover;width:100%}
    .sec-bien-card__body{padding:16px}
    .sec-bien-card__type{font-size:11px;font-weight:700;color:var(--ed-accent);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
    .sec-bien-card__name{font-family:var(--ff-heading);font-size:15px;font-weight:700;color:var(--ed-primary);margin-bottom:8px;line-height:1.3}
    .sec-bien-card__price{font-size:17px;font-weight:700;color:var(--ed-primary)}
    .sec-bien-card__meta{display:flex;gap:12px;font-size:12px;color:var(--ed-text-light);margin-top:6px}
    .sec-voisins{margin-top:48px}
    .sec-voisins__title{font-family:var(--ff-heading);font-size:20px;font-weight:700;color:var(--ed-primary);margin-bottom:20px}
    .sec-voisins__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px}
    .sec-voisin-card{display:block;text-decoration:none;border-radius:var(--ed-radius);overflow:hidden;position:relative;height:120px;background:var(--ed-primary);transition:var(--ed-transition)}
    .sec-voisin-card:hover{transform:translateY(-3px);box-shadow:var(--ed-shadow-lg)}
    .sec-voisin-card img{width:100%;height:100%;object-fit:cover;opacity:.6;transition:opacity .3s}
    .sec-voisin-card:hover img{opacity:.8}
    .sec-voisin-card__name{position:absolute;bottom:0;left:0;right:0;padding:10px 12px;background:linear-gradient(to top,rgba(14,58,92,.9),transparent);color:white;font-size:14px;font-weight:700;font-family:var(--ff-heading)}
    .sec-sidebar{display:flex;flex-direction:column;gap:24px}
    .sec-widget{background:white;border-radius:var(--ed-radius-lg);border:1px solid var(--ed-border);padding:24px;box-shadow:var(--ed-shadow)}
    .sec-widget__title{font-family:var(--ff-heading);font-size:16px;font-weight:700;color:var(--ed-primary);margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid var(--ed-accent)}
    .sec-cta-widget{background:linear-gradient(135deg,var(--ed-primary-dk),var(--ed-primary));color:white;border:none}
    .sec-cta-widget .sec-widget__title{color:white;border-color:rgba(255,255,255,.2)}
    .sec-cta-widget p{font-size:14px;opacity:.85;margin-bottom:16px;line-height:1.6}
    .sec-market-stat{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--ed-border-lt)}
    .sec-market-stat:last-child{border-bottom:none}
    .sec-market-stat__label{font-size:13px;color:var(--ed-text-light)}
    .sec-market-stat__value{font-size:15px;font-weight:700;color:var(--ed-primary)}
    .ed-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:var(--ed-radius);font-weight:600;font-size:14px;cursor:pointer;transition:var(--ed-transition);border:2px solid transparent;text-decoration:none}
    .ed-btn--primary{background:var(--ed-primary);color:#fff;border-color:var(--ed-primary)}
    .ed-btn--primary:hover{background:var(--ed-primary-dk)}
    .ed-btn--secondary{background:var(--ed-accent);color:#fff;border-color:var(--ed-accent)}
    .ed-btn--ghost{background:transparent;color:#fff;border-color:rgba(255,255,255,.4)}
    .ed-btn--ghost:hover{background:rgba(255,255,255,.1)}
    </style>

    <!-- Hero -->
    <section class="sec-hero">
        <?php if ($ogImage): ?>
        <div class="sec-hero__bg" style="background-image:url('<?= htmlspecialchars($ogImage) ?>')"></div>
        <?php endif; ?>
        <div class="sec-hero__overlay"></div>
        <div class="sec-hero__inner">
            <nav class="sec-hero__breadcrumb">
                <a href="/">Accueil</a><span>›</span>
                <a href="/secteurs">Secteurs</a><span>›</span>
                <span><?= htmlspecialchars($sectName) ?></span>
            </nav>
            <div class="sec-hero__badge"><i class="fas fa-map-marker-alt"></i> Bordeaux Métropole</div>
            <h1 class="sec-hero__title">Immobilier à <?= htmlspecialchars($sectName) ?></h1>
            <?php if ($sectDesc && !is_array($sectDesc)): ?>
            <p class="sec-hero__subtitle"><?= htmlspecialchars(_truncate(strip_tags((string)$sectDesc), 180)) ?></p>
            <?php endif; ?>
            <?php if ($prixM2 || $prixMoyen || $population): ?>
            <div class="sec-hero__stats">
                <?php if ($prixM2): ?><div class="sec-hero__stat"><div class="sec-hero__stat-val"><?= htmlspecialchars($prixM2) ?> €/m²</div><div class="sec-hero__stat-lbl">Prix médian</div></div><?php endif; ?>
                <?php if ($prixMoyen): ?><div class="sec-hero__stat"><div class="sec-hero__stat-val"><?= htmlspecialchars($prixMoyen) ?></div><div class="sec-hero__stat-lbl">Prix moyen</div></div><?php endif; ?>
                <?php if ($population): ?><div class="sec-hero__stat"><div class="sec-hero__stat-val"><?= number_format((int)$population, 0, ',', ' ') ?></div><div class="sec-hero__stat-lbl">Habitants</div></div><?php endif; ?>
                <?php if (!empty($biens)): ?><div class="sec-hero__stat"><div class="sec-hero__stat-val"><?= count($biens) ?>+</div><div class="sec-hero__stat-lbl">Biens disponibles</div></div><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Layout 2 colonnes -->
    <div class="sec-layout">
        <div>
            <!-- Contenu texte (éditeur classique) -->
            <?php
            $contenuLong = $secteur['contenu'] ?? '';
            if (!empty($contenuLong)): ?>
            <div class="sec-body"><?= $contenuLong ?></div>
            <?php elseif ($sectDesc && !is_array($sectDesc)): ?>
            <div class="sec-body"><p><?= nl2br(htmlspecialchars((string)$sectDesc)) ?></p></div>
            <?php endif; ?>

            <!-- Infos pratiques -->
            <?php if (!empty($transports) || !empty($atouts)): ?>
            <h2 style="font-family:var(--ff-heading);font-size:22px;font-weight:700;color:var(--ed-primary);margin:40px 0 20px">Vie à <?= htmlspecialchars($sectName) ?></h2>
            <div class="sec-info-grid">
                <?php if (!empty($transports)): ?>
                <div class="sec-info-card">
                    <div class="sec-info-card__title"><div class="sec-info-card__icon" style="background:rgba(26,77,122,.1)"><i class="fas fa-bus" style="color:var(--ed-primary)"></i></div>Transports</div>
                    <ul><?php foreach ((array)$transports as $t): ?><li><?= htmlspecialchars(is_string($t) ? $t : ($t['name'] ?? $t['nom'] ?? json_encode($t))) ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>
                <?php if (!empty($atouts)): ?>
                <div class="sec-info-card">
                    <div class="sec-info-card__title"><div class="sec-info-card__icon" style="background:rgba(212,165,116,.1)"><i class="fas fa-star" style="color:var(--ed-accent)"></i></div>Atouts</div>
                    <ul><?php foreach ((array)$atouts as $a): ?><li><?= htmlspecialchars(is_string($a) ? $a : ($a['name'] ?? $a['label'] ?? json_encode($a))) ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- FAQ -->
            <?php if (!empty($faq)): ?>
            <h2 style="font-family:var(--ff-heading);font-size:22px;font-weight:700;color:var(--ed-primary);margin:40px 0 20px">Questions fréquentes</h2>
            <?php foreach ((array)$faq as $item): $q = $item['question'] ?? $item['q'] ?? ''; $a = $item['answer'] ?? $item['reponse'] ?? $item['a'] ?? ''; if (!$q) continue; ?>
            <details style="margin-bottom:12px;border:1px solid var(--ed-border);border-radius:var(--ed-radius);overflow:hidden">
                <summary style="padding:16px;font-weight:600;color:var(--ed-primary);cursor:pointer;background:var(--ed-card-bg)"><?= htmlspecialchars($q) ?></summary>
                <div style="padding:16px;font-size:15px;line-height:1.7;color:var(--ed-text)"><?= nl2br(htmlspecialchars($a)) ?></div>
            </details>
            <?php endforeach; endif; ?>

            <!-- Biens disponibles -->
            <?php if (!empty($biens)): ?>
            <div class="sec-biens">
                <h2 class="sec-biens__title"><i class="fas fa-home" style="color:var(--ed-accent);margin-right:10px"></i>Biens disponibles à <?= htmlspecialchars($sectName) ?></h2>
                <div class="sec-biens__grid">
                    <?php foreach ($biens as $bien):
                        $bienImages = _jsonDecode($bien['images'] ?? '');
                        $bienImg    = $bienImages[0] ?? '';
                        $bienPrice  = $bien['price'] ? number_format(floatval($bien['price']), 0, ',', ' ') . ' €' : 'Prix sur demande';
                    ?>
                    <a href="/biens/<?= htmlspecialchars($bien['slug'] ?? $bien['id']) ?>" class="sec-bien-card">
                        <?php if ($bienImg): ?><img src="<?= htmlspecialchars($bienImg) ?>" alt="<?= htmlspecialchars($bien['title']) ?>" class="sec-bien-card__img" loading="lazy">
                        <?php else: ?><div class="sec-bien-card__img" style="background:linear-gradient(135deg,var(--ed-primary-dk),var(--ed-primary));display:flex;align-items:center;justify-content:center;color:white;font-size:36px"><i class="fas fa-home"></i></div><?php endif; ?>
                        <div class="sec-bien-card__body">
                            <div class="sec-bien-card__type"><?= htmlspecialchars(ucfirst($bien['type'] ?? '')) ?> · <?= htmlspecialchars($bien['transaction'] ?? '') ?></div>
                            <div class="sec-bien-card__name"><?= htmlspecialchars($bien['title']) ?></div>
                            <div class="sec-bien-card__price"><?= $bienPrice ?></div>
                            <div class="sec-bien-card__meta">
                                <?php if ($bien['surface']): ?><span><i class="fas fa-ruler-combined"></i> <?= intval($bien['surface']) ?> m²</span><?php endif; ?>
                                <?php if ($bien['rooms']):   ?><span><i class="fas fa-door-open"></i> <?= intval($bien['rooms']) ?> pièces</span><?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div style="text-align:center;margin-top:24px">
                    <a href="/biens?ville=<?= urlencode($sectVille) ?>" class="ed-btn ed-btn--secondary"><i class="fas fa-search"></i> Voir tous les biens</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Secteurs voisins -->
            <?php if (!empty($voisins)): ?>
            <div class="sec-voisins">
                <h2 class="sec-voisins__title">Secteurs voisins</h2>
                <div class="sec-voisins__grid">
                    <?php foreach ($voisins as $v): ?>
                    <a href="/<?= htmlspecialchars($v['slug']) ?>" class="sec-voisin-card">
                        <?php if (!empty($v['hero_image'])): ?><img src="<?= htmlspecialchars($v['hero_image']) ?>" alt="<?= htmlspecialchars($v['nom'] ?? '') ?>" loading="lazy">
                        <?php else: ?><div style="width:100%;height:100%;background:linear-gradient(135deg,var(--ed-primary-dk),var(--ed-primary))"></div><?php endif; ?>
                        <div class="sec-voisin-card__name"><?= htmlspecialchars($v['nom'] ?? '') ?></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="sec-sidebar">
            <div class="sec-widget sec-cta-widget">
                <div class="sec-widget__title"><i class="fas fa-map-marker-alt" style="margin-right:8px"></i> Votre projet à <?= htmlspecialchars($sectName) ?></div>
                <p>Eduardo connaît parfaitement ce secteur. Estimation gratuite ou recherche sur mesure.</p>
                <a href="tel:<?= $phoneclean ?>" class="ed-btn ed-btn--primary" style="width:100%;justify-content:center;margin-bottom:10px"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($phone) ?></a>
                <a href="/estimation" class="ed-btn ed-btn--ghost" style="width:100%;justify-content:center"><i class="fas fa-calculator"></i> Estimer mon bien</a>
            </div>

            <?php if ($prixM2 || $prixMoyen): ?>
            <div class="sec-widget">
                <div class="sec-widget__title"><i class="fas fa-chart-line" style="color:var(--ed-accent);margin-right:8px"></i> Marché immobilier</div>
                <?php if ($prixM2): ?><div class="sec-market-stat"><span class="sec-market-stat__label">Prix au m²</span><span class="sec-market-stat__value"><?= htmlspecialchars($prixM2) ?> €</span></div><?php endif; ?>
                <?php if ($prixMoyen): ?><div class="sec-market-stat"><span class="sec-market-stat__label">Prix moyen</span><span class="sec-market-stat__value"><?= htmlspecialchars($prixMoyen) ?></span></div><?php endif; ?>
                <?php if (!empty($biens)): ?><div class="sec-market-stat"><span class="sec-market-stat__label">Biens disponibles</span><span class="sec-market-stat__value"><?= count($biens) ?></span></div><?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="sec-widget" style="background:var(--ed-card-bg)">
                <div class="sec-widget__title"><i class="fas fa-home" style="color:var(--ed-accent);margin-right:8px"></i> Vous vendez ?</div>
                <p style="font-size:14px;color:var(--ed-text-light);margin-bottom:16px">Obtenez une estimation gratuite de votre bien à <?= htmlspecialchars($sectName) ?>.</p>
                <a href="/estimation" class="ed-btn ed-btn--primary" style="width:100%;justify-content:center"><i class="fas fa-calculator"></i> Estimation gratuite</a>
            </div>
        </aside>
    </div>
    <!-- ══ Fin template par défaut ══ -->

<?php endif; ?>

</main>

<!-- Footer -->
<?php if (!empty($hf['footer'])): echo renderFooter($hf['footer']); endif; ?>

<?php if ($builderJs): ?><script><?= $builderJs ?></script><?php endif; ?>
<?php if ($tplJs):     ?><script><?= $tplJs ?></script><?php endif; ?>

</body>
</html>

<?php

// ════════════════════════════════════════════════════════
// buildSecteurVars() — pour les templates builder_templates
// ════════════════════════════════════════════════════════
if (!function_exists('buildSecteurVars')) {
function buildSecteurVars(array $secteur, PDO $db): array {
    $siteUrl  = function_exists('siteUrl')  ? siteUrl()  : 'https://' . ($_SERVER['HTTP_HOST'] ?? '');
    $siteName = function_exists('siteName') ? siteName() : 'Eduardo De Sul Immobilier';
    $phone    = function_exists('_ss')      ? _ss('phone', '06 24 10 58 16') : '06 24 10 58 16';
    $name     = $secteur['nom'] ?? $secteur['name'] ?? $secteur['title'] ?? '';

    return [
        '{{name}}'        => htmlspecialchars($name),
        '{{nom}}'         => htmlspecialchars($name),
        '{{title}}'       => htmlspecialchars($name),
        '{{titre}}'       => htmlspecialchars($name),
        '{{slug}}'        => htmlspecialchars($secteur['slug'] ?? ''),
        '{{description}}' => htmlspecialchars($secteur['description'] ?? ''),
        '{{content}}'     => $secteur['content']  ?? $secteur['contenu'] ?? '',
        '{{contenu}}'     => $secteur['contenu']  ?? $secteur['content'] ?? '',
        '{{hero_image}}'  => htmlspecialchars($secteur['hero_image'] ?? $secteur['image'] ?? ''),
        '{{image}}'       => htmlspecialchars($secteur['hero_image'] ?? $secteur['image'] ?? ''),
        '{{ville}}'       => htmlspecialchars($secteur['ville'] ?? $name),
        '{{prix_m2}}'     => htmlspecialchars($secteur['prix_moyen_m2'] ?? $secteur['prix_m2'] ?? ''),
        '{{prix_moyen}}'  => htmlspecialchars($secteur['prix_moyen'] ?? ''),
        '{{population}}'  => htmlspecialchars($secteur['population'] ?? ''),
        '{{latitude}}'    => htmlspecialchars($secteur['latitude'] ?? ''),
        '{{longitude}}'   => htmlspecialchars($secteur['longitude'] ?? ''),
        '{{url}}'         => htmlspecialchars($siteUrl . '/' . ($secteur['slug'] ?? '')),
        '{{phone}}'       => htmlspecialchars($phone),
        '{{phone_clean}}' => htmlspecialchars(preg_replace('/\s+/', '', $phone)),
        '{{site_name}}'   => htmlspecialchars($siteName),
        '{{site_url}}'    => htmlspecialchars($siteUrl),
        '{{year}}'        => date('Y'),
    ];
}
}