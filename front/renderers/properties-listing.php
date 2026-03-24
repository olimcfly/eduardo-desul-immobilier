<?php
/**
 * ══════════════════════════════════════════════════════════════
 * FRONTEND — LISTING BIENS IMMOBILIERS  v1.0
 * /front/renderers/properties-listing.php
 * Eduardo De Sul – Bordeaux / Blanquefort
 * Inclus dans front/page.php via routing
 * ══════════════════════════════════════════════════════════════
 * Route : /biens-immobiliers  ou  /biens
 */

global $pdo, $db;
if (!isset($pdo) && isset($db)) $pdo = $db;

// ─── Filtres URL ───
$filterType  = $_GET['type']        ?? 'all';
$filterTrans = $_GET['transaction']  ?? 'all';
$filterCity  = $_GET['ville']       ?? 'all';
$minPrice    = (int)($_GET['prix_min'] ?? 0);
$maxPrice    = (int)($_GET['prix_max'] ?? 0);
$minSurface  = (int)($_GET['surface_min'] ?? 0);
$searchQuery = trim($_GET['q']       ?? '');
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$perPage     = 12;
$offset      = ($currentPage - 1) * $perPage;

// ─── Colonnes ───
$availCols = [];
try { $availCols = $pdo->query("SHOW COLUMNS FROM properties")->fetchAll(PDO::FETCH_COLUMN); } catch(PDOException $e){}
$colTitle  = in_array('titre',       $availCols) ? 'titre'       : 'title';
$colPrice  = in_array('prix',        $availCols) ? 'prix'        : 'price';
$colStatus = in_array('statut',      $availCols) ? 'statut'      : 'status';
$colTrans  = in_array('transaction', $availCols) ? 'transaction' : 'transaction_type';
$colCity   = in_array('ville',       $availCols) ? 'ville'       : 'city';
$colType   = in_array('type_bien',   $availCols) ? 'type_bien'   : 'type';
$colSurf   = in_array('surface',     $availCols) ? 'surface'     : 'area';
$colRooms  = in_array('pieces',      $availCols) ? 'pieces'      : 'rooms';
$colBeds   = in_array('chambres',    $availCols) ? 'chambres'    : null;
$colPhotos = in_array('photos',      $availCols) ? 'photos'      : 'images';
$colFeat   = in_array('is_featured', $availCols) ? 'is_featured' : 'featured';
$colDpe    = in_array('dpe',         $availCols) ? 'dpe'         : 'classe_energie';
$colRef    = in_array('reference',   $availCols) ? 'reference'   : 'ref';

// ─── WHERE ───
$where  = ["`{$colStatus}` IN ('actif','active','disponible')"];
$params = [];
if ($filterTrans !== 'all') { $where[] = "`{$colTrans}` = ?"; $params[] = $filterTrans; }
if ($filterType  !== 'all') { $where[] = "`{$colType}` = ?";  $params[] = $filterType;  }
if ($filterCity  !== 'all') { $where[] = "`{$colCity}` = ?";  $params[] = $filterCity;  }
if ($minPrice > 0)  { $where[] = "`{$colPrice}` >= ?"; $params[] = $minPrice; }
if ($maxPrice > 0)  { $where[] = "`{$colPrice}` <= ?"; $params[] = $maxPrice; }
if ($minSurface > 0){ $where[] = "`{$colSurf}` >= ?";  $params[] = $minSurface; }
if ($searchQuery)   { $where[] = "(`{$colTitle}` LIKE ? OR `{$colCity}` LIKE ?)"; $params[] = "%{$searchQuery}%"; $params[] = "%{$searchQuery}%"; }
$whereSQL = 'WHERE ' . implode(' AND ', $where);

// ─── Stats + filtres disponibles ───
$totalProps = 0;
$properties = [];
$cities     = [];
$types      = [];
$biens_une  = [];

try {
    $totalProps = (int)$pdo->prepare("SELECT COUNT(*) FROM properties {$whereSQL}")->execute($params) ? $pdo->query("SELECT COUNT(*) FROM properties {$whereSQL}")->fetchColumn() : 0;

    // Recompter correctement
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM properties {$whereSQL}");
    $stmtCount->execute($params);
    $totalProps = (int)$stmtCount->fetchColumn();
    $totalPages = max(1, ceil($totalProps / $perPage));

    // Biens liste
    $stmt = $pdo->prepare("SELECT id, `{$colTitle}` AS titre, `{$colPrice}` AS prix, `{$colSurf}` AS surface, `{$colType}` AS type_bien, `{$colTrans}` AS transaction, `{$colCity}` AS ville, `{$colRooms}` AS pieces, slug, `{$colPhotos}` AS photos, `{$colFeat}` AS is_featured, `{$colDpe}` AS dpe, `{$colRef}` AS reference, created_at FROM properties {$whereSQL} ORDER BY `{$colFeat}` DESC, created_at DESC LIMIT {$perPage} OFFSET {$offset}");
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Villes dispo
    $cities = $pdo->query("SELECT DISTINCT `{$colCity}` FROM properties WHERE `{$colStatus}` IN ('actif','active') AND `{$colCity}` IS NOT NULL AND `{$colCity}` != '' ORDER BY `{$colCity}`")->fetchAll(PDO::FETCH_COLUMN);

    // Types dispo
    $types = $pdo->query("SELECT DISTINCT `{$colType}` FROM properties WHERE `{$colStatus}` IN ('actif','active') AND `{$colType}` IS NOT NULL AND `{$colType}` != '' ORDER BY `{$colType}`")->fetchAll(PDO::FETCH_COLUMN);

    // Biens à la une (pour la section hero)
    $stmtUne = $pdo->query("SELECT id, `{$colTitle}` AS titre, `{$colPrice}` AS prix, `{$colSurf}` AS surface, `{$colType}` AS type_bien, `{$colTrans}` AS transaction, `{$colCity}` AS ville, `{$colRooms}` AS pieces, slug, `{$colPhotos}` AS photos FROM properties WHERE `{$colFeat}` = 1 AND `{$colStatus}` IN ('actif','active') ORDER BY created_at DESC LIMIT 3");
    $biens_une = $stmtUne ? $stmtUne->fetchAll(PDO::FETCH_ASSOC) : [];

} catch (PDOException $e) {
    error_log('[Properties Listing] ' . $e->getMessage());
}

// ─── Helpers ───
function frontFirstPhoto(string $json): string {
    $decoded = json_decode($json, true);
    if (is_array($decoded) && !empty($decoded)) return $decoded[0];
    if (!empty($json) && !str_starts_with($json, '[')) return $json;
    return '';
}
function frontPrice(float $price, string $trans): string {
    if ($price <= 0) return 'Prix sur demande';
    $f = number_format($price, 0, ',', ' ');
    return $trans === 'location' ? $f . ' €/mois' : $f . ' €';
}
function frontDpeClass(string $d): string {
    return ['A'=>'#059669','B'=>'#34d399','C'=>'#86efac','D'=>'#fde68a','E'=>'#fed7aa','F'=>'#fca5a5','G'=>'#ef4444'][strtoupper($d)] ?? '#e5e7eb';
}
function frontDpeTextClass(string $d): string {
    return in_array(strtoupper($d),['A','B','C']) ? '#fff' : (strtoupper($d)==='G'?'#fff':'#111827');
}

// ─── SEO page ───
$pageTitle    = 'Biens Immobiliers – Bordeaux et alentours | Eduardo De Sul';
$pageDesc     = 'Découvrez les biens immobiliers disponibles à Bordeaux, Blanquefort, Mérignac et la Gironde. Maisons, appartements et terrains à vendre ou à louer.';
$currentUrl   = 'https://eduardo-desul-immobilier.fr/biens-immobiliers';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <link rel="canonical" href="<?= $currentUrl ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= $currentUrl ?>">

    <!-- Schema.org RealEstateListing -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Biens immobiliers disponibles – Eduardo De Sul",
        "numberOfItems": <?= $totalProps ?>,
        "url": "<?= $currentUrl ?>"
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/front/assets/css/style.css">

    <style>
    /* ══════════════════════════════════════════════════════════
       LISTING BIENS — FRONTEND Eduardo De Sul
       Palette #1a4d7a / #d4a574 / #f9f6f3
    ══════════════════════════════════════════════════════════ */
    :root {
        --blue:   #1a4d7a;
        --blue-d: #0f3356;
        --gold:   #d4a574;
        --gold-d: #c0936a;
        --beige:  #f9f6f3;
        --white:  #ffffff;
        --text:   #1a1a2e;
        --text2:  #4a5568;
        --text3:  #9ca3af;
        --border: #e8e0d8;
        --r:      12px;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: var(--beige); color: var(--text); margin: 0; }

    /* ── Hero section ── */
    .prop-hero {
        background: linear-gradient(135deg, var(--blue-d) 0%, var(--blue) 60%, #1e5a8e 100%);
        padding: 70px 24px 80px;
        text-align: center; position: relative; overflow: hidden;
    }
    .prop-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .prop-hero-content { position: relative; z-index: 1; max-width: 700px; margin: 0 auto; }
    .prop-hero-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(212,165,116,.2); border: 1px solid rgba(212,165,116,.4);
        color: var(--gold); padding: 5px 14px; border-radius: 20px;
        font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
        margin-bottom: 16px;
    }
    .prop-hero h1 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(2rem, 5vw, 3.2rem); font-weight: 900; color: #fff;
        margin: 0 0 14px; line-height: 1.15;
    }
    .prop-hero h1 span { color: var(--gold); }
    .prop-hero p { color: rgba(255,255,255,.7); font-size: 1.05rem; margin: 0 0 32px; line-height: 1.6; }
    .prop-hero-stats {
        display: flex; justify-content: center; gap: 32px; flex-wrap: wrap;
        border-top: 1px solid rgba(255,255,255,.12); padding-top: 24px; margin-top: 10px;
    }
    .prop-hero-stat { text-align: center; }
    .prop-hero-stat .n { font-size: 1.8rem; font-weight: 800; color: #fff; font-family: 'Playfair Display',serif; }
    .prop-hero-stat .l { font-size: .7rem; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .06em; margin-top: 2px; }

    /* ── Container ── */
    .prop-container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }

    /* ── Filtres ── */
    .prop-filters-wrap {
        background: var(--white); border-radius: var(--r);
        box-shadow: 0 4px 24px rgba(26,77,122,.08);
        margin: -30px auto 40px; max-width: 900px;
        padding: 24px 28px; position: relative; z-index: 10;
    }
    .prop-filters-grid {
        display: grid; grid-template-columns: 1fr 1fr 1fr auto;
        gap: 12px; align-items: end;
    }
    @media (max-width: 768px) { .prop-filters-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 480px) { .prop-filters-grid { grid-template-columns: 1fr; } }

    .prop-fgroup label {
        display: block; font-size: .68rem; font-weight: 700; color: var(--text2);
        text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;
    }
    .prop-fgroup select, .prop-fgroup input {
        width: 100%; padding: 11px 14px; border: 1.5px solid var(--border);
        border-radius: 8px; background: var(--beige); color: var(--text);
        font-family: 'DM Sans', sans-serif; font-size: .85rem; transition: border-color .15s;
    }
    .prop-fgroup select:focus, .prop-fgroup input:focus {
        outline: none; border-color: var(--blue); background: var(--white);
    }
    .prop-filter-btn {
        padding: 11px 24px; background: var(--blue); color: var(--white);
        border: none; border-radius: 8px; font-family: 'DM Sans',sans-serif;
        font-size: .88rem; font-weight: 600; cursor: pointer; transition: all .15s;
        display: flex; align-items: center; gap: 6px; white-space: nowrap;
    }
    .prop-filter-btn:hover { background: var(--blue-d); transform: translateY(-1px); }

    /* ── Tags de filtres actifs ── */
    .prop-active-filters { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
    .prop-filter-tag {
        display: inline-flex; align-items: center; gap: 5px;
        background: rgba(26,77,122,.07); border: 1px solid rgba(26,77,122,.15);
        color: var(--blue); padding: 4px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600;
    }
    .prop-filter-tag a { color: var(--blue); text-decoration: none; opacity: .6; }
    .prop-filter-tag a:hover { opacity: 1; }

    /* ── Barre résultats ── */
    .prop-results-bar {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px; flex-wrap: wrap; gap: 10px;
    }
    .prop-results-count { font-size: .88rem; color: var(--text2); }
    .prop-results-count strong { color: var(--blue); font-weight: 700; }
    .prop-sort-select {
        padding: 8px 14px; border: 1.5px solid var(--border); border-radius: 8px;
        background: var(--white); color: var(--text); font-family: 'DM Sans',sans-serif;
        font-size: .82rem; cursor: pointer;
    }

    /* ── À la une ── */
    .prop-section-title {
        font-family: 'Playfair Display',serif; font-size: 1.6rem; font-weight: 700;
        color: var(--blue); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }
    .prop-section-title::after {
        content: ''; flex: 1; height: 2px;
        background: linear-gradient(90deg, var(--gold), transparent);
        border-radius: 1px;
    }
    .prop-une-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 20px; margin-bottom: 50px;
    }

    /* ── Grille biens ── */
    .prop-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    /* ── Carte bien ── */
    .prop-card {
        background: var(--white); border-radius: var(--r);
        box-shadow: 0 2px 12px rgba(26,77,122,.06);
        border: 1px solid var(--border);
        overflow: hidden; transition: all .25s;
        display: flex; flex-direction: column;
        text-decoration: none; color: var(--text);
    }
    .prop-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(26,77,122,.14);
        border-color: var(--blue);
    }
    .prop-card.featured { border-color: var(--gold); }

    /* Photo */
    .prop-card-img {
        height: 210px; position: relative; overflow: hidden;
        background: var(--beige); flex-shrink: 0;
    }
    .prop-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
    .prop-card:hover .prop-card-img img { transform: scale(1.05); }
    .prop-card-no-photo {
        width: 100%; height: 100%;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--beige), #f0e8e0);
        color: var(--text3);
    }
    .prop-card-no-photo i { font-size: 2.5rem; opacity: .2; margin-bottom: 8px; }

    /* Badges */
    .prop-card-badges { position: absolute; top: 10px; left: 10px; display: flex; gap: 5px; flex-wrap: wrap; }
    .prop-cbadge {
        padding: 3px 10px; border-radius: 20px; font-size: .58rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em; backdrop-filter: blur(6px);
    }
    .prop-cbadge.vente    { background: rgba(26,77,122,.88); color: #fff; }
    .prop-cbadge.location { background: rgba(212,165,116,.92); color: #fff; }
    .prop-cbadge.une      { background: #f59e0b; color: #fff; }

    /* Photo count */
    .prop-photo-count {
        position: absolute; bottom: 8px; right: 8px;
        background: rgba(0,0,0,.5); color: #fff; border-radius: 6px;
        font-size: .62rem; font-weight: 700; padding: 2px 8px;
        display: flex; align-items: center; gap: 3px;
    }

    /* Corps */
    .prop-card-body { padding: 16px 18px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
    .prop-card-price {
        font-family: 'Playfair Display', serif;
        font-size: 1.35rem; font-weight: 700; color: var(--blue); letter-spacing: -.02em;
    }
    .prop-card-title {
        font-size: .92rem; font-weight: 600; color: var(--text); line-height: 1.4;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .prop-card-meta {
        display: flex; flex-wrap: wrap; gap: 10px; margin-top: 4px;
    }
    .prop-card-meta-item {
        display: flex; align-items: center; gap: 4px;
        font-size: .75rem; color: var(--text2); font-weight: 500;
    }
    .prop-card-meta-item i { font-size: .65rem; color: var(--text3); }

    /* Footer carte */
    .prop-card-footer {
        padding: 10px 18px; border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .prop-card-city { font-size: .72rem; color: var(--text3); display: flex; align-items: center; gap: 4px; }
    .prop-card-cta {
        display: inline-flex; align-items: center; gap: 4px;
        background: var(--blue); color: var(--white); padding: 5px 14px;
        border-radius: 20px; font-size: .72rem; font-weight: 700;
        transition: background .15s;
    }
    .prop-card:hover .prop-card-cta { background: var(--gold); }

    /* DPE mini */
    .prop-dpe {
        position: absolute; top: 10px; right: 10px;
        padding: 3px 8px; border-radius: 5px;
        font-size: .6rem; font-weight: 800; letter-spacing: .04em;
    }

    /* ── Pagination ── */
    .prop-pagination { display: flex; justify-content: center; gap: 6px; margin: 50px 0 30px; flex-wrap: wrap; }
    .prop-pag-btn {
        padding: 8px 16px; border: 1.5px solid var(--border); border-radius: 8px;
        color: var(--text2); text-decoration: none; font-weight: 600; font-size: .82rem;
        background: var(--white); transition: all .15s;
    }
    .prop-pag-btn:hover { border-color: var(--blue); color: var(--blue); }
    .prop-pag-btn.active { background: var(--blue); color: var(--white); border-color: var(--blue); }

    /* ── CTA Contact ── */
    .prop-cta-section {
        background: linear-gradient(135deg, var(--blue-d), var(--blue));
        border-radius: 20px; padding: 50px 32px;
        text-align: center; margin: 50px 0;
        position: relative; overflow: hidden;
    }
    .prop-cta-section::before {
        content: ''; position: absolute; bottom: -30%; right: -5%;
        width: 250px; height: 250px;
        background: radial-gradient(circle, rgba(212,165,116,.12), transparent 70%);
        border-radius: 50%;
    }
    .prop-cta-section h2 { font-family: 'Playfair Display',serif; font-size: 1.8rem; color: #fff; margin: 0 0 12px; }
    .prop-cta-section p { color: rgba(255,255,255,.7); margin: 0 0 24px; font-size: .95rem; }
    .prop-cta-btn {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--gold); color: var(--white); padding: 14px 32px;
        border-radius: 10px; font-weight: 700; font-size: .95rem; text-decoration: none;
        transition: all .2s; border: 2px solid var(--gold);
    }
    .prop-cta-btn:hover { background: var(--gold-d); border-color: var(--gold-d); transform: translateY(-2px); }

    /* Vide */
    .prop-empty {
        text-align: center; padding: 80px 20px;
        background: var(--white); border-radius: var(--r); border: 1px solid var(--border);
    }
    .prop-empty i { font-size: 3rem; color: var(--blue); opacity: .15; margin-bottom: 16px; display: block; }
    .prop-empty h3 { font-family: 'Playfair Display',serif; font-size: 1.2rem; color: var(--text2); margin-bottom: 8px; }
    .prop-empty p { color: var(--text3); font-size: .88rem; }

    @media (max-width: 900px) { .prop-une-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .prop-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include __DIR__ . '/../components/header.php'; ?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<section class="prop-hero">
    <div class="prop-hero-content">
        <div class="prop-hero-badge"><i class="fas fa-home"></i> Portefeuille Immobilier</div>
        <h1>Trouvez votre <span>bien idéal</span><br>à Bordeaux &amp; Gironde</h1>
        <p>Maisons, appartements, terrains — un accompagnement personnalisé<br>pour chaque projet immobilier</p>
        <div class="prop-hero-stats">
            <div class="prop-hero-stat"><div class="n"><?= $totalProps ?></div><div class="l">Biens disponibles</div></div>
            <div class="prop-hero-stat"><div class="n"><?= count($cities) ?></div><div class="l">Communes</div></div>
            <div class="prop-hero-stat"><div class="n">eXp</div><div class="l">Réseau France</div></div>
        </div>
    </div>
</section>

<!-- ══ FILTRES ═══════════════════════════════════════════════ -->
<div class="prop-container">
<div class="prop-filters-wrap">
    <form method="GET" action="/biens-immobiliers">
        <div class="prop-filters-grid">
            <div class="prop-fgroup">
                <label>Type de bien</label>
                <select name="type">
                    <option value="all">Tous les types</option>
                    <?php foreach ($types as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= $filterType === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="prop-fgroup">
                <label>Transaction</label>
                <select name="transaction">
                    <option value="all">Vente &amp; Location</option>
                    <option value="vente"    <?= $filterTrans === 'vente'    ? 'selected' : '' ?>>Vente</option>
                    <option value="location" <?= $filterTrans === 'location' ? 'selected' : '' ?>>Location</option>
                </select>
            </div>
            <div class="prop-fgroup">
                <label>Ville</label>
                <select name="ville">
                    <option value="all">Toutes les villes</option>
                    <?php foreach ($cities as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= $filterCity === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="prop-fgroup">
                <button type="submit" class="prop-filter-btn"><i class="fas fa-search"></i> Filtrer</button>
            </div>
        </div>
        <?php if ($minPrice || $maxPrice || $minSurface || $searchQuery): ?>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:12px">
            <div class="prop-fgroup">
                <label>Prix min (€)</label>
                <input type="number" name="prix_min" placeholder="Ex: 150000" value="<?= $minPrice ?: '' ?>">
            </div>
            <div class="prop-fgroup">
                <label>Prix max (€)</label>
                <input type="number" name="prix_max" placeholder="Ex: 500000" value="<?= $maxPrice ?: '' ?>">
            </div>
            <div class="prop-fgroup">
                <label>Surface min (m²)</label>
                <input type="number" name="surface_min" placeholder="Ex: 80" value="<?= $minSurface ?: '' ?>">
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- ── Tags filtres actifs ── -->
<?php if ($filterType !== 'all' || $filterTrans !== 'all' || $filterCity !== 'all' || $searchQuery): ?>
<div class="prop-active-filters">
    <span style="font-size:.72rem;color:var(--text2);font-weight:600">Filtres :</span>
    <?php if ($filterType !== 'all'): ?>
    <span class="prop-filter-tag"><?= htmlspecialchars($filterType) ?> <a href="?<?= http_build_query(array_merge($_GET,['type'=>'all'])) ?>">×</a></span>
    <?php endif; ?>
    <?php if ($filterTrans !== 'all'): ?>
    <span class="prop-filter-tag"><?= htmlspecialchars($filterTrans) ?> <a href="?<?= http_build_query(array_merge($_GET,['transaction'=>'all'])) ?>">×</a></span>
    <?php endif; ?>
    <?php if ($filterCity !== 'all'): ?>
    <span class="prop-filter-tag"><?= htmlspecialchars($filterCity) ?> <a href="?<?= http_build_query(array_merge($_GET,['ville'=>'all'])) ?>">×</a></span>
    <?php endif; ?>
    <a href="/biens-immobiliers" style="font-size:.72rem;color:var(--text3);text-decoration:none;margin-left:4px">Tout effacer</a>
</div>
<?php endif; ?>

<!-- ── À la une ── -->
<?php if (!empty($biens_une) && $currentPage === 1 && $filterType === 'all' && $filterTrans === 'all'): ?>
<h2 class="prop-section-title"><i class="fas fa-star" style="color:#d4a574;font-size:.9em"></i> À la une</h2>
<div class="prop-une-grid">
<?php foreach ($biens_une as $b): ?>
<?php
    $photo = frontFirstPhoto($b['photos'] ?? '');
    $photoCount = is_array(json_decode($b['photos']??'[]',true)) ? count(json_decode($b['photos'],true)) : 0;
    $bSlug  = $b['slug'] ?? $b['id'];
    $bPrice = frontPrice((float)($b['prix'] ?? 0), $b['transaction'] ?? 'vente');
    $bTrans = $b['transaction'] ?? 'vente';
?>
<a href="/biens/<?= htmlspecialchars($bSlug) ?>" class="prop-card featured">
    <div class="prop-card-img">
        <?php if ($photo): ?><img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($b['titre']) ?>" loading="lazy">
        <?php else: ?><div class="prop-card-no-photo"><i class="fas fa-home"></i></div><?php endif; ?>
        <div class="prop-card-badges">
            <span class="prop-cbadge <?= $bTrans ?>"><?= $bTrans === 'location' ? 'Location' : 'Vente' ?></span>
            <span class="prop-cbadge une"><i class="fas fa-star"></i> À la une</span>
        </div>
        <?php if ($photoCount > 1): ?><div class="prop-photo-count"><i class="fas fa-images"></i> <?= $photoCount ?></div><?php endif; ?>
    </div>
    <div class="prop-card-body">
        <div class="prop-card-price"><?= $bPrice ?></div>
        <div class="prop-card-title"><?= htmlspecialchars($b['titre']) ?></div>
        <div class="prop-card-meta">
            <?php if ($b['surface']): ?><span class="prop-card-meta-item"><i class="fas fa-ruler-combined"></i> <?= (float)$b['surface'] ?> m²</span><?php endif; ?>
            <?php if ($b['pieces']): ?><span class="prop-card-meta-item"><i class="fas fa-door-open"></i> <?= $b['pieces'] ?> pièces</span><?php endif; ?>
            <?php if ($b['ville']): ?><span class="prop-card-meta-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($b['ville']) ?></span><?php endif; ?>
        </div>
    </div>
    <div class="prop-card-footer">
        <span class="prop-card-city"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($b['ville'] ?? '') ?></span>
        <span class="prop-card-cta"><i class="fas fa-eye"></i> Voir</span>
    </div>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Résultats ── -->
<div class="prop-results-bar">
    <p class="prop-results-count">
        <strong><?= $totalProps ?></strong> bien<?= $totalProps > 1 ? 's' : '' ?> trouvé<?= $totalProps > 1 ? 's' : '' ?>
        <?php if ($filterCity !== 'all'): ?> à <strong><?= htmlspecialchars($filterCity) ?></strong><?php endif; ?>
    </p>
    <select class="prop-sort-select" onchange="window.location.href = this.value">
        <option value="?<?= http_build_query(array_merge($_GET,['sort'=>'recent'])) ?>">Plus récents</option>
        <option value="?<?= http_build_query(array_merge($_GET,['sort'=>'prix_asc'])) ?>">Prix croissant</option>
        <option value="?<?= http_build_query(array_merge($_GET,['sort'=>'prix_desc'])) ?>">Prix décroissant</option>
        <option value="?<?= http_build_query(array_merge($_GET,['sort'=>'surface'])) ?>">Surface</option>
    </select>
</div>

<!-- ── Grille ── -->
<?php if (empty($properties)): ?>
<div class="prop-empty">
    <i class="fas fa-home"></i>
    <h3>Aucun bien disponible</h3>
    <p>Aucun bien ne correspond à vos critères actuellement.<br>Modifiez vos filtres ou <a href="/contact" style="color:#1a4d7a;font-weight:600">contactez-nous</a> pour une recherche personnalisée.</p>
</div>
<?php else: ?>
<div class="prop-grid">
<?php foreach ($properties as $b): ?>
<?php
    $photo = frontFirstPhoto($b['photos'] ?? '');
    $photoArr = json_decode($b['photos']??'[]', true);
    $photoCount = is_array($photoArr) ? count($photoArr) : 0;
    $bSlug  = $b['slug'] ?? $b['id'];
    $bPrice = frontPrice((float)($b['prix'] ?? 0), $b['transaction'] ?? 'vente');
    $bTrans = $b['transaction'] ?? 'vente';
    $bDpe   = strtoupper($b['dpe'] ?? '');
    $isFeat = !empty($b['is_featured']);
?>
<a href="/biens/<?= htmlspecialchars($bSlug) ?>" class="prop-card <?= $isFeat ? 'featured' : '' ?>">
    <div class="prop-card-img">
        <?php if ($photo): ?><img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($b['titre']) ?>" loading="lazy">
        <?php else: ?><div class="prop-card-no-photo"><i class="fas fa-home"></i></div><?php endif; ?>
        <div class="prop-card-badges">
            <span class="prop-cbadge <?= $bTrans ?>"><?= $bTrans === 'location' ? 'Location' : 'Vente' ?></span>
            <?php if ($isFeat): ?><span class="prop-cbadge une"><i class="fas fa-star"></i></span><?php endif; ?>
        </div>
        <?php if ($photoCount > 1): ?><div class="prop-photo-count"><i class="fas fa-images"></i> <?= $photoCount ?></div><?php endif; ?>
        <?php if ($bDpe && strlen($bDpe) === 1): ?>
        <div class="prop-dpe" style="background:<?= frontDpeClass($bDpe) ?>;color:<?= frontDpeTextClass($bDpe) ?>">DPE <?= $bDpe ?></div>
        <?php endif; ?>
    </div>
    <div class="prop-card-body">
        <div class="prop-card-price"><?= $bPrice ?></div>
        <div class="prop-card-title"><?= htmlspecialchars($b['titre']) ?></div>
        <div class="prop-card-meta">
            <?php if ($b['surface']): ?><span class="prop-card-meta-item"><i class="fas fa-ruler-combined"></i> <?= (float)$b['surface'] ?> m²</span><?php endif; ?>
            <?php if ($b['pieces']): ?><span class="prop-card-meta-item"><i class="fas fa-door-open"></i> <?= $b['pieces'] ?> p.</span><?php endif; ?>
            <?php if ($b['type_bien']): ?><span class="prop-card-meta-item"><i class="fas fa-tag"></i> <?= htmlspecialchars($b['type_bien']) ?></span><?php endif; ?>
        </div>
    </div>
    <div class="prop-card-footer">
        <span class="prop-card-city"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($b['ville'] ?? '') ?></span>
        <span class="prop-card-cta"><i class="fas fa-arrow-right"></i> Voir</span>
    </div>
</a>
<?php endforeach; ?>
</div><!-- /grid -->

<!-- ── Pagination ── -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
<div class="prop-pagination">
    <?php for ($i=1; $i<=$totalPages; $i++):
        $pUrl = '/biens-immobiliers?' . http_build_query(array_merge($_GET, ['p'=>$i]));
    ?>
    <a href="<?= $pUrl ?>" class="prop-pag-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ── CTA ── -->
<div class="prop-cta-section">
    <h2>Vous ne trouvez pas votre bien idéal ?</h2>
    <p>Partagez votre projet avec Eduardo — il vous propose les opportunités du marché Bordelais en exclusivité.</p>
    <a href="/contact" class="prop-cta-btn"><i class="fas fa-phone"></i> Nous contacter</a>
</div>

</div><!-- /container -->
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>