<?php
/**
 * ══════════════════════════════════════════════════════════════
 * FRONTEND — DÉTAIL BIEN IMMOBILIER  v1.0
 * /front/renderers/property-single.php
 * Route : /biens/{slug}
 * ══════════════════════════════════════════════════════════════
 */

global $pdo, $db;
if (!isset($pdo) && isset($db)) $pdo = $db;

// ─── Récupérer le bien ───
$slug     = $slug ?? basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$property = null;

try {
    $availCols = $pdo->query("SHOW COLUMNS FROM properties")->fetchAll(PDO::FETCH_COLUMN);
    $colStatus = in_array('statut', $availCols) ? 'statut' : 'status';
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE slug = ? AND `{$colStatus}` IN ('actif','active') LIMIT 1");
    $stmt->execute([$slug]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    // Biens similaires
    if ($property) {
        $colType  = in_array('type_bien', $availCols) ? 'type_bien' : 'type';
        $colTrans = in_array('transaction', $availCols) ? 'transaction' : 'transaction_type';
        $colTitle = in_array('titre', $availCols) ? 'titre' : 'title';
        $colPrice = in_array('prix', $availCols) ? 'prix' : 'price';
        $colSurf  = in_array('surface', $availCols) ? 'surface' : 'area';
        $colCity  = in_array('ville', $availCols) ? 'ville' : 'city';
        $colRooms = in_array('pieces', $availCols) ? 'pieces' : 'rooms';
        $colPhotos= in_array('photos', $availCols) ? 'photos' : 'images';
        $colDpe   = in_array('dpe', $availCols) ? 'dpe' : 'classe_energie';
        $colRef   = in_array('reference', $availCols) ? 'reference' : 'ref';
        $colAddr  = in_array('adresse', $availCols) ? 'adresse' : (in_array('address', $availCols) ? 'address' : null);
        $colZip   = in_array('code_postal', $availCols) ? 'code_postal' : (in_array('zip', $availCols) ? 'zip' : null);
        $colBeds  = in_array('chambres', $availCols) ? 'chambres' : (in_array('bedrooms', $availCols) ? 'bedrooms' : null);
        $colBath  = in_array('salles_bain', $availCols) ? 'salles_bain' : (in_array('bathrooms', $availCols) ? 'bathrooms' : null);
        $colMandat= in_array('mandat', $availCols) ? 'mandat' : 'type_mandat';
        $colYear  = in_array('annee_construction', $availCols) ? 'annee_construction' : null;
        $colGes   = in_array('ges', $availCols) ? 'ges' : null;
        $colDesc  = in_array('description', $availCols) ? 'description' : 'contenu';
        $colLat   = in_array('latitude', $availCols) ? 'latitude' : null;
        $colLng   = in_array('longitude', $availCols) ? 'longitude' : null;

        $stmtSim = $pdo->prepare("SELECT id, `{$colTitle}` AS titre, `{$colPrice}` AS prix, `{$colSurf}` AS surface, `{$colTrans}` AS transaction, `{$colCity}` AS ville, slug, `{$colPhotos}` AS photos FROM properties WHERE `{$colType}` = ? AND id != ? AND `{$colStatus}` IN ('actif','active') LIMIT 3");
        $stmtSim->execute([$property[$colType] ?? '', $property['id']]);
        $similar = $stmtSim->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('[Property Single] ' . $e->getMessage());
}

if (!$property) {
    http_response_code(404);
    include __DIR__ . '/../renderers/404.php'; exit;
}

// ─── Photos ───
$photos = [];
$photosRaw = $property[$colPhotos] ?? '';
if ($photosRaw) {
    $decoded = json_decode($photosRaw, true);
    $photos = is_array($decoded) ? $decoded : [$photosRaw];
}

// ─── Données bien ───
$titre       = $property[$colTitle]   ?? 'Bien immobilier';
$prix        = (float)($property[$colPrice]  ?? 0);
$surface     = (float)($property[$colSurf]   ?? 0);
$transaction = $property[$colTrans]   ?? 'vente';
$ville       = $property[$colCity]    ?? '';
$pieces      = (int)($property[$colRooms]  ?? 0);
$chambres    = $colBeds ? (int)($property[$colBeds] ?? 0) : 0;
$sallesBain  = $colBath ? (int)($property[$colBath] ?? 0) : 0;
$description = $property[$colDesc]    ?? '';
$reference   = $property[$colRef]     ?? '';
$mandat      = $property[$colMandat]  ?? 'simple';
$dpe         = strtoupper($property[$colDpe] ?? '');
$ges         = $colGes ? strtoupper($property[$colGes] ?? '') : '';
$adresse     = $colAddr ? ($property[$colAddr] ?? '') : '';
$codePostal  = $colZip ? ($property[$colZip] ?? '') : '';
$annee       = $colYear ? ($property[$colYear] ?? '') : '';
$lat         = $colLat ? ($property[$colLat] ?? null) : null;
$lng         = $colLng ? ($property[$colLng] ?? null) : null;
$priceFormatted = $prix > 0 ? number_format($prix, 0, ',', ' ') . ($transaction === 'location' ? ' €/mois' : ' €') : 'Prix sur demande';

// ─── SEO ───
$_siteName = function_exists('siteName') ? siteName() : _ss('site_name', 'Mon entreprise');
$_siteUrl  = function_exists('siteUrl')  ? siteUrl()  : _ss('site_url', '');
$metaTitle = $property['meta_title'] ?? "{$titre} – {$_siteName}";
$metaDesc  = $property['meta_description'] ?? ($description ? mb_substr(strip_tags($description), 0, 155) . '…' : "Découvrez {$titre} à {$ville}. Contactez-nous pour plus d'informations.");
$canonUrl  = rtrim($_siteUrl, '/') . "/biens/{$slug}";

function dpeColor(string $d): array {
    $map = [
        'A'=>['#059669','#fff'],'B'=>['#34d399','#fff'],'C'=>['#86efac','#111'],
        'D'=>['#fde68a','#92400e'],'E'=>['#fed7aa','#92400e'],'F'=>['#fca5a5','#991b1b'],'G'=>['#ef4444','#fff']
    ];
    return $map[strtoupper($d)] ?? ['#e5e7eb','#111827'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($metaTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <link rel="canonical" href="<?= $canonUrl ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($metaTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <?php if (!empty($photos[0])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($photos[0]) ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">

    <!-- Schema.org RealEstateListing -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateListing",
        "name": <?= json_encode($titre) ?>,
        "description": <?= json_encode(substr(strip_tags($description), 0, 500)) ?>,
        "url": "<?= $canonUrl ?>",
        "price": <?= $prix > 0 ? $prix : 'null' ?>,
        "priceCurrency": "EUR",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": <?= json_encode($ville) ?>,
            "postalCode": <?= json_encode($codePostal) ?>,
            "addressCountry": "FR"
        },
        <?php if (!empty($photos[0])): ?>
        "image": <?= json_encode($photos[0]) ?>,
        <?php endif; ?>
        "numberOfRooms": <?= $pieces ?: 'null' ?>
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/front/assets/css/style.css">

    <style>
    :root {
        --blue:#1a4d7a; --blue-d:#0f3356; --gold:#d4a574; --gold-d:#c0936a;
        --beige:#f9f6f3; --text:#1a1a2e; --text2:#4a5568; --text3:#9ca3af; --border:#e8e0d8;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: var(--beige); color: var(--text); margin: 0; }

    /* ── Breadcrumb ── */
    .ps-breadcrumb {
        padding: 14px 0; font-size: .78rem; color: var(--text3);
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .ps-breadcrumb a { color: var(--blue); text-decoration: none; }
    .ps-breadcrumb a:hover { text-decoration: underline; }
    .ps-breadcrumb i { font-size: .6rem; }

    /* ── Container ── */
    .ps-container { max-width: 1180px; margin: 0 auto; padding: 0 24px; }

    /* ── Layout ── */
    .ps-layout { display: grid; grid-template-columns: 1fr 340px; gap: 32px; padding: 32px 0 60px; align-items: start; }
    @media (max-width: 900px) { .ps-layout { grid-template-columns: 1fr; } }

    /* ── Galerie ── */
    .ps-gallery { border-radius: 14px; overflow: hidden; margin-bottom: 24px; }
    .ps-main-photo {
        position: relative; height: 460px; background: #f0e8e0;
        cursor: pointer; overflow: hidden;
    }
    .ps-main-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
    .ps-main-photo:hover img { transform: scale(1.02); }
    .ps-main-photo-count {
        position: absolute; bottom: 14px; right: 14px;
        background: rgba(0,0,0,.55); color: #fff; border-radius: 8px;
        padding: 5px 12px; font-size: .78rem; font-weight: 700;
        display: flex; align-items: center; gap: 6px;
        cursor: pointer;
    }
    .ps-thumbnails {
        display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-top: 6px;
    }
    .ps-thumb {
        height: 80px; border-radius: 8px; overflow: hidden;
        cursor: pointer; opacity: .7; transition: all .2s; border: 2px solid transparent;
        background: var(--beige);
    }
    .ps-thumb:hover { opacity: 1; }
    .ps-thumb.active { opacity: 1; border-color: var(--blue); }
    .ps-thumb img { width: 100%; height: 100%; object-fit: cover; }

    /* ── Header bien ── */
    .ps-header { margin-bottom: 24px; }
    .ps-trans-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px; font-size: .65rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em; margin-bottom: 10px;
    }
    .ps-trans-badge.vente    { background: rgba(26,77,122,.1); color: var(--blue); }
    .ps-trans-badge.location { background: rgba(212,165,116,.15); color: var(--gold-d); }
    .ps-header h1 {
        font-family: 'Playfair Display', serif; font-size: clamp(1.5rem, 3vw, 2.2rem);
        font-weight: 700; color: var(--text); margin: 0 0 10px; line-height: 1.25;
    }
    .ps-location { color: var(--text2); font-size: .9rem; display: flex; align-items: center; gap: 6px; margin-bottom: 16px; }
    .ps-location i { color: var(--blue); }
    .ps-price {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem; font-weight: 900; color: var(--blue); letter-spacing: -.03em;
    }
    .ps-ref { font-size: .72rem; color: var(--text3); margin-top: 4px; font-family: monospace; }

    /* ── Stats rapides ── */
    .ps-stats {
        display: flex; gap: 0; flex-wrap: wrap;
        background: #fff; border-radius: 12px; border: 1px solid var(--border);
        overflow: hidden; margin: 20px 0;
    }
    .ps-stat {
        flex: 1; min-width: 100px; padding: 16px 14px; text-align: center;
        border-right: 1px solid var(--border);
    }
    .ps-stat:last-child { border-right: none; }
    .ps-stat i { font-size: 1.2rem; color: var(--blue); opacity: .7; margin-bottom: 6px; display: block; }
    .ps-stat .val { font-size: 1.1rem; font-weight: 700; color: var(--text); }
    .ps-stat .lbl { font-size: .65rem; color: var(--text3); text-transform: uppercase; letter-spacing: .05em; margin-top: 2px; }

    /* ── Sections ── */
    .ps-section { background: #fff; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 20px; overflow: hidden; }
    .ps-section-head {
        padding: 14px 20px; border-bottom: 1px solid var(--border); background: var(--beige);
        display: flex; align-items: center; gap: 8px;
        font-size: .75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: var(--text2);
    }
    .ps-section-head i { color: var(--blue); }
    .ps-section-body { padding: 20px; }

    /* Description */
    .ps-description { font-size: .95rem; color: var(--text2); line-height: 1.75; }
    .ps-description p { margin: 0 0 12px; }

    /* Caractéristiques grid */
    .ps-chars-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
    .ps-char {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; background: var(--beige); border-radius: 8px;
        font-size: .85rem;
    }
    .ps-char i { color: var(--blue); font-size: .9rem; width: 18px; text-align: center; }
    .ps-char-label { color: var(--text3); font-size: .72rem; display: block; }
    .ps-char-val   { font-weight: 600; color: var(--text); }

    /* DPE section */
    .ps-dpe-grid { display: flex; gap: 8px; align-items: flex-start; }
    .ps-dpe-scale { display: flex; flex-direction: column; gap: 3px; }
    .ps-dpe-bar {
        display: flex; align-items: center; gap: 8px;
        padding: 3px 10px; border-radius: 0 6px 6px 0; font-size: .72rem; font-weight: 800;
        min-width: 40px; color: #fff; letter-spacing: .04em;
        clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%);
    }
    .ps-dpe-current { transform: scale(1.12); box-shadow: 0 2px 8px rgba(0,0,0,.25); z-index: 1; }
    .ps-dpe-info { margin-left: 16px; }
    .ps-dpe-info h4 { font-size: .85rem; font-weight: 700; color: var(--text); margin: 0 0 4px; }
    .ps-dpe-info p { font-size: .78rem; color: var(--text2); margin: 0; }

    /* Carte contact – sidebar */
    .ps-sidebar-card {
        background: #fff; border-radius: 14px; border: 1px solid var(--border);
        overflow: hidden; position: sticky; top: 90px;
    }
    .ps-sidebar-top {
        background: linear-gradient(135deg, var(--blue-d), var(--blue));
        padding: 22px 20px; color: #fff; text-align: center; position: relative;
    }
    .ps-sidebar-top::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, var(--gold), transparent);
    }
    .ps-advisor-photo {
        width: 64px; height: 64px; border-radius: 50%; border: 2px solid rgba(212,165,116,.5);
        margin: 0 auto 10px; overflow: hidden; background: rgba(255,255,255,.1);
        display: flex; align-items: center; justify-content: center;
    }
    .ps-advisor-photo img { width: 100%; height: 100%; object-fit: cover; }
    .ps-advisor-photo i { font-size: 1.8rem; opacity: .5; }
    .ps-sidebar-top h3 { font-family: 'Playfair Display',serif; font-size: 1.05rem; margin: 0 0 3px; }
    .ps-sidebar-top p { font-size: .72rem; color: rgba(255,255,255,.65); margin: 0; }

    .ps-sidebar-body { padding: 20px; }
    .ps-sidebar-price {
        text-align: center; margin-bottom: 16px;
        font-family: 'Playfair Display',serif; font-size: 1.6rem; font-weight: 700; color: var(--blue);
    }

    .ps-contact-form { display: flex; flex-direction: column; gap: 10px; }
    .ps-contact-form input, .ps-contact-form textarea, .ps-contact-form select {
        padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 8px;
        background: var(--beige); color: var(--text); font-family: 'DM Sans',sans-serif;
        font-size: .83rem; width: 100%; transition: border-color .15s;
    }
    .ps-contact-form input:focus, .ps-contact-form textarea:focus {
        outline: none; border-color: var(--blue); background: #fff;
    }
    .ps-contact-form textarea { min-height: 80px; resize: vertical; }
    .ps-contact-submit {
        background: var(--gold); color: #fff; border: none; border-radius: 8px;
        padding: 12px; font-family: 'DM Sans',sans-serif; font-size: .9rem; font-weight: 700;
        cursor: pointer; transition: all .15s; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .ps-contact-submit:hover { background: var(--gold-d); transform: translateY(-1px); }

    .ps-sidebar-tel {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        background: rgba(26,77,122,.06); border: 1px solid rgba(26,77,122,.12);
        border-radius: 8px; padding: 10px; margin-top: 10px;
        text-decoration: none; color: var(--blue); font-weight: 700; font-size: .85rem;
        transition: all .15s;
    }
    .ps-sidebar-tel:hover { background: rgba(26,77,122,.1); }

    /* Similaires */
    .ps-similar { margin-top: 50px; margin-bottom: 50px; }
    .ps-similar-title {
        font-family: 'Playfair Display',serif; font-size: 1.4rem; font-weight: 700;
        color: var(--blue); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }
    .ps-similar-title::after { content:''; flex:1; height:2px; background:linear-gradient(90deg,var(--gold),transparent); border-radius:1px; }
    .ps-similar-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
    .ps-sim-card {
        background: #fff; border-radius: 12px; border: 1px solid var(--border);
        overflow: hidden; text-decoration: none; color: var(--text); transition: all .2s;
    }
    .ps-sim-card:hover { border-color: var(--blue); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,77,122,.1); }
    .ps-sim-img { height: 160px; overflow: hidden; background: var(--beige); }
    .ps-sim-img img { width:100%;height:100%;object-fit:cover;transition:transform .4s; }
    .ps-sim-card:hover .ps-sim-img img { transform:scale(1.04); }
    .ps-sim-body { padding: 12px 14px; }
    .ps-sim-price { font-family:'Playfair Display',serif; font-weight:700; color:var(--blue); font-size:1.1rem; margin-bottom:4px; }
    .ps-sim-title { font-size:.82rem; font-weight:600; color:var(--text); line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .ps-sim-city { font-size:.7rem; color:var(--text3); margin-top:5px; display:flex; align-items:center; gap:3px; }

    /* Lightbox */
    .ps-lightbox {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,.9);
        z-index: 9999; align-items: center; justify-content: center; flex-direction: column;
    }
    .ps-lightbox.open { display: flex; }
    .ps-lightbox img { max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 4px; }
    .ps-lightbox-nav {
        position: absolute; top: 50%; transform: translateY(-50%);
        width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.3); color: #fff; display: flex; align-items: center;
        justify-content: center; cursor: pointer; font-size: 1.1rem; transition: background .15s;
        backdrop-filter: blur(4px);
    }
    .ps-lightbox-nav:hover { background: rgba(255,255,255,.25); }
    .ps-lightbox-prev { left: 16px; }
    .ps-lightbox-next { right: 16px; }
    .ps-lightbox-close {
        position: absolute; top: 16px; right: 16px;
        width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.3); color: #fff; display: flex; align-items: center;
        justify-content: center; cursor: pointer; font-size: .9rem;
    }
    .ps-lightbox-counter { color: rgba(255,255,255,.6); font-size: .78rem; margin-top: 12px; }

    @media (max-width: 900px) { .ps-main-photo { height: 260px; } .ps-thumbnails { grid-template-columns: repeat(4,1fr); } }
    </style>
</head>
<body>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="ps-container">
    <!-- Breadcrumb -->
    <nav class="ps-breadcrumb" aria-label="Fil d'Ariane">
        <a href="/">Accueil</a>
        <i class="fas fa-chevron-right"></i>
        <a href="/biens-immobiliers">Biens immobiliers</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars($titre) ?></span>
    </nav>

    <div class="ps-layout">
    <!-- ══ Colonne principale ══════════════════════════════ -->
    <div>

        <!-- Galerie photos -->
        <div class="ps-gallery">
            <div class="ps-main-photo" onclick="PSL.open(0)">
                <?php if (!empty($photos[0])): ?>
                <img src="<?= htmlspecialchars($photos[0]) ?>" alt="<?= htmlspecialchars($titre) ?>" id="psMainImg">
                <?php else: ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f0e8e0;color:#9ca3af;flex-direction:column">
                    <i class="fas fa-home" style="font-size:3rem;opacity:.2;margin-bottom:10px"></i>
                    <span style="font-size:.8rem">Aucune photo disponible</span>
                </div>
                <?php endif; ?>
                <?php if (count($photos) > 1): ?>
                <div class="ps-main-photo-count"><i class="fas fa-images"></i> <?= count($photos) ?> photos</div>
                <?php endif; ?>
            </div>
            <?php if (count($photos) > 1): ?>
            <div class="ps-thumbnails">
                <?php foreach (array_slice($photos, 0, 5) as $i => $ph): ?>
                <div class="ps-thumb <?= $i === 0 ? 'active' : '' ?>" onclick="PSL.setMain(<?= $i ?>)" data-idx="<?= $i ?>">
                    <img src="<?= htmlspecialchars($ph) ?>" alt="Photo <?= $i+1 ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Header -->
        <div class="ps-header">
            <span class="ps-trans-badge <?= $transaction ?>">
                <i class="fas <?= $transaction === 'location' ? 'fa-key' : 'fa-tag' ?>"></i>
                <?= $transaction === 'location' ? 'À louer' : 'À vendre' ?>
            </span>
            <?php if ($mandat === 'exclusif'): ?>
            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:.65rem;font-weight:800;background:#fef9c3;color:#a16207;border:1px solid #fde047;margin-left:8px;text-transform:uppercase">
                <i class="fas fa-shield-alt"></i> Mandat exclusif
            </span>
            <?php endif; ?>
            <h1><?= htmlspecialchars($titre) ?></h1>
            <div class="ps-location">
                <i class="fas fa-map-marker-alt"></i>
                <?php if ($adresse): ?><?= htmlspecialchars($adresse) ?>, <?php endif; ?>
                <?= htmlspecialchars($ville) ?><?php if ($codePostal): ?> (<?= htmlspecialchars($codePostal) ?>)<?php endif; ?>
            </div>
            <div class="ps-price"><?= $priceFormatted ?></div>
            <?php if ($reference): ?><div class="ps-ref">Réf. <?= htmlspecialchars($reference) ?></div><?php endif; ?>
        </div>

        <!-- Stats rapides -->
        <div class="ps-stats">
            <?php if ($surface): ?>
            <div class="ps-stat">
                <i class="fas fa-ruler-combined"></i>
                <div class="val"><?= $surface ?> m²</div>
                <div class="lbl">Surface</div>
            </div>
            <?php endif; ?>
            <?php if ($pieces): ?>
            <div class="ps-stat">
                <i class="fas fa-door-open"></i>
                <div class="val"><?= $pieces ?></div>
                <div class="lbl">Pièces</div>
            </div>
            <?php endif; ?>
            <?php if ($chambres): ?>
            <div class="ps-stat">
                <i class="fas fa-bed"></i>
                <div class="val"><?= $chambres ?></div>
                <div class="lbl">Chambres</div>
            </div>
            <?php endif; ?>
            <?php if ($sallesBain): ?>
            <div class="ps-stat">
                <i class="fas fa-bath"></i>
                <div class="val"><?= $sallesBain ?></div>
                <div class="lbl">Sdb</div>
            </div>
            <?php endif; ?>
            <?php if ($annee): ?>
            <div class="ps-stat">
                <i class="fas fa-calendar-alt"></i>
                <div class="val"><?= $annee ?></div>
                <div class="lbl">Construit</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <?php if ($description): ?>
        <div class="ps-section">
            <div class="ps-section-head"><i class="fas fa-align-left"></i> Description</div>
            <div class="ps-section-body">
                <div class="ps-description"><?= nl2br(htmlspecialchars($description)) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Caractéristiques -->
        <div class="ps-section">
            <div class="ps-section-head"><i class="fas fa-list-ul"></i> Caractéristiques détaillées</div>
            <div class="ps-section-body">
                <div class="ps-chars-grid">
                    <?php
                    $chars = [];
                    if ($surface)     $chars[] = ['fa-ruler-combined','Surface','habitable',"{$surface} m²"];
                    if ($pieces)      $chars[] = ['fa-door-open','Nombre de','pièces',$pieces];
                    if ($chambres)    $chars[] = ['fa-bed','Chambres','',$chambres];
                    if ($sallesBain)  $chars[] = ['fa-bath','Salles de','bain',$sallesBain];
                    if ($annee)       $chars[] = ['fa-calendar-alt','Année de','construction',$annee];
                    if ($transaction === 'location') $chars[] = ['fa-key','Type de','location','Location'];
                    else $chars[] = ['fa-tag','Type de','vente','Vente'];
                    $chars[] = ['fa-map-marker-alt','Commune','',$ville];
                    if ($codePostal) $chars[] = ['fa-map-pin','Code','postal',$codePostal];
                    foreach ($chars as [$icon, $label1, $label2, $val]):
                    ?>
                    <div class="ps-char">
                        <i class="fas <?= $icon ?>"></i>
                        <div>
                            <span class="ps-char-label"><?= $label1 . ($label2 ? ' '.$label2 : '') ?></span>
                            <span class="ps-char-val"><?= htmlspecialchars((string)$val) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- DPE -->
        <?php if ($dpe && strlen($dpe) === 1): ?>
        <div class="ps-section">
            <div class="ps-section-head"><i class="fas fa-leaf"></i> Performance énergétique (DPE)</div>
            <div class="ps-section-body">
                <div class="ps-dpe-grid">
                    <div class="ps-dpe-scale">
                        <?php foreach (['A','B','C','D','E','F','G'] as $l):
                            [$bg,$col] = dpeColor($l);
                            $isActive  = strtoupper($dpe) === $l;
                            $widths    = ['A'=>50,'B'=>58,'C'=>66,'D'=>74,'E'=>82,'F'=>90,'G'=>98];
                        ?>
                        <div class="ps-dpe-bar <?= $isActive ? 'ps-dpe-current' : '' ?>"
                             style="background:<?= $bg ?>;color:<?= $col ?>;width:<?= $widths[$l] ?>px">
                            <?= $l ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ps-dpe-info">
                        <h4>Classe énergie : <?= htmlspecialchars($dpe) ?></h4>
                        <p>Diagnostique de performance énergétique réalisé lors du mandat.</p>
                        <?php if ($ges && strlen($ges) === 1): ?>
                        <div style="margin-top:10px">
                            <span style="font-size:.72rem;font-weight:700;color:var(--text2)">GES :</span>
                            <?php [$gbg,$gcol] = dpeColor($ges); ?>
                            <span style="background:<?= $gbg ?>;color:<?= $gcol ?>;padding:2px 10px;border-radius:5px;font-size:.7rem;font-weight:800"><?= htmlspecialchars($ges) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /col main -->

    <!-- ══ Sidebar ════════════════════════════════════════ -->
    <div>
        <div class="ps-sidebar-card">
            <div class="ps-sidebar-top">
                <div class="ps-advisor-photo">
                    <?php
                    $photoAdvisor = _ss('agent_photo', '');
                    if ($photoAdvisor && file_exists(dirname(dirname(__DIR__)) . parse_url($photoAdvisor, PHP_URL_PATH))): ?>
                    <img src="<?= htmlspecialchars($photoAdvisor) ?>" alt="<?= htmlspecialchars(_ss('agent_name', '')) ?>">
                    <?php else: ?><i class="fas fa-user"></i><?php endif; ?>
                </div>
                <h3><?= htmlspecialchars(_ss('agent_name', _ss('site_name', 'Mon entreprise'))) ?></h3>
                <p><?= htmlspecialchars(_ss('agent_title', 'Conseiller immobilier')) ?></p>
            </div>
            <div class="ps-sidebar-body">
                <div class="ps-sidebar-price"><?= $priceFormatted ?></div>

                <form class="ps-contact-form" id="psContactForm">
                    <input type="hidden" name="bien_id"    value="<?= $property['id'] ?>">
                    <input type="hidden" name="bien_titre" value="<?= htmlspecialchars($titre) ?>">
                    <input type="hidden" name="action"     value="contact_bien">
                    <input type="text"   name="nom"        placeholder="Votre nom *" required>
                    <input type="email"  name="email"      placeholder="Email *" required>
                    <input type="tel"    name="telephone"  placeholder="Téléphone">
                    <select name="objet">
                        <option value="visite">Demander une visite</option>
                        <option value="info">Demander des informations</option>
                        <option value="offre">Formuler une offre</option>
                        <option value="financement">Simulation financement</option>
                    </select>
                    <textarea name="message" placeholder="Votre message…"><?= htmlspecialchars("Bonjour,\n\nJe suis intéressé(e) par le bien « {$titre} » (Réf. {$reference}).\n\nPouvez-vous me contacter ?\n\nMerci.") ?></textarea>
                    <button type="submit" class="ps-contact-submit">
                        <i class="fas fa-paper-plane"></i> Envoyer ma demande
                    </button>
                </form>
                <p style="font-size:.65rem;color:var(--text3);text-align:center;margin:8px 0 12px">Réponse sous 24h ouvrées · Données protégées</p>
                <?php $agentPhone = _ss('phone', ''); $agentWhatsapp = _ss('whatsapp', ''); ?>
                <?php if ($agentPhone): ?>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $agentPhone)) ?>" class="ps-sidebar-tel">
                    <i class="fas fa-phone"></i> <?= htmlspecialchars($agentPhone) ?>
                </a>
                <?php endif; ?>
                <?php if ($agentWhatsapp): ?>
                <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\s+/', '', $agentWhatsapp)) ?>" target="_blank" class="ps-sidebar-tel" style="margin-top:6px;background:rgba(37,211,102,.08);border-color:rgba(37,211,102,.2);color:#25d366">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- /sidebar -->
    </div><!-- /layout -->

    <!-- Biens similaires -->
    <?php if (!empty($similar ?? [])): ?>
    <div class="ps-similar">
        <h2 class="ps-similar-title"><i class="fas fa-home" style="color:#d4a574;font-size:.9em"></i> Biens similaires</h2>
        <div class="ps-similar-grid">
        <?php foreach ($similar as $s):
            $sPhoto = '';
            $sDec = json_decode($s['photos']??'[]', true);
            if (is_array($sDec) && !empty($sDec)) $sPhoto = $sDec[0];
            $sPrice = $s['prix'] > 0 ? number_format((float)$s['prix'], 0, ',', ' ') . ($s['transaction']==='location'?' €/mois':' €') : 'Sur demande';
        ?>
        <a href="/biens/<?= htmlspecialchars($s['slug'] ?? $s['id']) ?>" class="ps-sim-card">
            <div class="ps-sim-img">
                <?php if ($sPhoto): ?><img src="<?= htmlspecialchars($sPhoto) ?>" alt="<?= htmlspecialchars($s['titre']) ?>" loading="lazy">
                <?php else: ?><div style="height:100%;display:flex;align-items:center;justify-content:center;color:#9ca3af;background:#f9f6f3"><i class="fas fa-home" style="font-size:2rem;opacity:.2"></i></div><?php endif; ?>
            </div>
            <div class="ps-sim-body">
                <div class="ps-sim-price"><?= $sPrice ?></div>
                <div class="ps-sim-title"><?= htmlspecialchars($s['titre']) ?></div>
                <div class="ps-sim-city"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($s['ville'] ?? '') ?></div>
            </div>
        </a>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /container -->

<!-- Lightbox -->
<?php if (!empty($photos)): ?>
<div class="ps-lightbox" id="psLightbox">
    <button class="ps-lightbox-close" onclick="PSL.close()"><i class="fas fa-times"></i></button>
    <button class="ps-lightbox-nav ps-lightbox-prev" onclick="PSL.prev()"><i class="fas fa-chevron-left"></i></button>
    <img src="" id="psLbImg" alt="Photo bien">
    <button class="ps-lightbox-nav ps-lightbox-next" onclick="PSL.next()"><i class="fas fa-chevron-right"></i></button>
    <div class="ps-lightbox-counter" id="psLbCounter"></div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script>
const PS_PHOTOS = <?= json_encode($photos) ?>;

const PSL = {
    current: 0,
    open(idx) {
        this.current = idx;
        document.getElementById('psLightbox').classList.add('open');
        this.render();
        document.addEventListener('keydown', this.keyHandler.bind(this));
    },
    close() {
        document.getElementById('psLightbox').classList.remove('open');
        document.removeEventListener('keydown', this.keyHandler.bind(this));
    },
    prev() { this.current = (this.current - 1 + PS_PHOTOS.length) % PS_PHOTOS.length; this.render(); },
    next() { this.current = (this.current + 1) % PS_PHOTOS.length; this.render(); },
    render() {
        document.getElementById('psLbImg').src = PS_PHOTOS[this.current];
        document.getElementById('psLbCounter').textContent = `${this.current + 1} / ${PS_PHOTOS.length}`;
    },
    keyHandler(e) {
        if (e.key === 'Escape') this.close();
        if (e.key === 'ArrowLeft') this.prev();
        if (e.key === 'ArrowRight') this.next();
    },
    setMain(idx) {
        this.current = idx;
        document.getElementById('psMainImg').src = PS_PHOTOS[idx];
        document.querySelectorAll('.ps-thumb').forEach((t,i) => t.classList.toggle('active', i === idx));
    }
};

// Fermer lightbox au clic background
document.getElementById('psLightbox')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) PSL.close();
});

// Formulaire contact
document.getElementById('psContactForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = e.target.querySelector('.ps-contact-submit');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi…';
    try {
        const fd = new FormData(e.target);
        const r = await fetch('/front/capture/index.php', {method:'POST', body:fd});
        const d = await r.json().catch(() => ({success: false}));
        if (d.success !== false) {
            e.target.innerHTML = `<div style="text-align:center;padding:20px;color:#059669"><i class="fas fa-check-circle" style="font-size:2rem;margin-bottom:10px;display:block"></i><strong>Message envoyé !</strong><br><span style="font-size:.8rem;opacity:.8">Nous vous contacterons rapidement.</span></div>`;
        } else {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Renvoyer';
            alert('Erreur lors de l\'envoi. Veuillez appeler directement.');
        }
    } catch(err) {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Renvoyer';
    }
});
</script>
</body>
</html>