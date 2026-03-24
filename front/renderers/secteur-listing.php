<?php
/**
 * ══════════════════════════════════════════════════════════════
 * SECTEUR LISTING — Front  v2.0  Eduardo De Sul
 * /front/renderers/secteur-listing.php
 * ══════════════════════════════════════════════════════════════
 *
 * 1. Tente de charger une page Builder (slug='secteurs')
 * 2. Si aucune page Builder → rendu autonome avec header/footer
 */

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

global $db;
if (!$db) $db = getDB();

// ── Charger la page Builder slug='secteurs' ──
$builderPage = null;
try {
    $stmt = $db->prepare("
        SELECT * FROM pages
        WHERE slug = 'secteurs'
          AND (status = 'published' OR statut = 'publié')
        LIMIT 1
    ");
    $stmt->execute();
    $builderPage = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $builderPage = null;
}

// ── Page Builder trouvée → déléguer à cms.php ──
if ($builderPage) {
    $page = $builderPage;
    require __DIR__ . '/cms.php';
    exit;
}

// ══════════════════════════════════════════════════════════════
// RENDU AUTONOME — Listing des secteurs
// ══════════════════════════════════════════════════════════════

$typeFilter = trim($_GET['type'] ?? '');
$villeFilter = trim($_GET['ville'] ?? '');
$searchFilter = trim($_GET['search'] ?? '');

// ─── Secteurs publiés ───
$secteurs = [];
try {
    $sql = "SELECT * FROM secteurs WHERE status = 'published'";
    $params = [];
    if ($typeFilter) {
        $sql .= " AND type_secteur = ?";
        $params[] = $typeFilter;
    }
    if ($villeFilter) {
        $sql .= " AND ville = ?";
        $params[] = $villeFilter;
    }
    if ($searchFilter) {
        $sql .= " AND (nom LIKE ? OR description LIKE ? OR ville LIKE ?)";
        $params[] = "%$searchFilter%";
        $params[] = "%$searchFilter%";
        $params[] = "%$searchFilter%";
    }
    $sql .= " ORDER BY ville ASC, nom ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Secteur listing: " . $e->getMessage());
}

$total = count($secteurs);

// ─── Types distincts (pour les filtres) ───
$types = [];
try {
    $stmt = $db->query("SELECT type_secteur AS t, COUNT(*) AS nb FROM secteurs WHERE status='published' AND type_secteur IS NOT NULL AND type_secteur != '' GROUP BY type_secteur ORDER BY nb DESC");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ─── Header / Footer ───
$hf = getHeaderFooter($db, 'secteurs');
$_siteUrl  = siteUrl();
$_siteName = siteName();

// ─── SEO ───
$pageTitle = $typeFilter
    ? 'Secteurs · ' . ucfirst($typeFilter)
    : 'Quartiers & Secteurs de Bordeaux';
$metaTitle = htmlspecialchars($pageTitle . ' | ' . $_siteName);
$metaDesc  = htmlspecialchars('Découvrez les quartiers bordelais : guides complets, prix immobiliers, transports et ambiance. Trouvez le secteur idéal pour votre projet immobilier à Bordeaux.');
$canonical = $_siteUrl . '/secteurs' . ($typeFilter ? '?type=' . urlencode($typeFilter) : '');

function truncateSecteur(string $text, int $max = 120): string {
    $text = strip_tags($text);
    return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '…' : $text;
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $metaTitle ?></title>
<meta name="description" content="<?= $metaDesc ?>">
<meta name="robots" content="index,follow">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<meta property="og:title"       content="<?= $metaTitle ?>">
<meta property="og:description" content="<?= $metaDesc ?>">
<meta property="og:type"        content="website">
<meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:site_name"   content="<?= htmlspecialchars($_siteName) ?>">

<!-- Schema.org -->
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"ItemList",
  "name":"<?= addslashes($pageTitle) ?>",
  "description":"<?= addslashes(strip_tags($metaDesc)) ?>",
  "url":"<?= addslashes($canonical) ?>",
  "numberOfItems":<?= $total ?>,
  "itemListElement":[
<?php foreach ($secteurs as $i => $s): ?>
    {"@type":"ListItem","position":<?= $i + 1 ?>,"url":"<?= $_siteUrl ?>/secteurs/<?= addslashes($s['slug'] ?? '') ?>","name":"<?= addslashes($s['nom'] ?? '') ?>"}<?= $i < $total - 1 ? ',' : '' ?>

<?php endforeach; ?>
  ]
}
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<?php if (class_exists('SiteSettings')): ?><?= SiteSettings::trackingHead() ?><?php endif; ?>
<?= eduardoHead() ?>
<link rel="stylesheet" href="/front/assets/css/secteurs.css">

<style>
/* ── Variables bridge pour secteurs.css ── */
:root {
  --primary-dark: #0e3a5c;
  --white: #ffffff;
  --bg-cream: #f9f6f3;
  --text-primary: #1a1a2e;
  --text-secondary: #4a5568;
  --radius-md: 8px;
  --radius-xl: 16px;
  --shadow-sm: 0 2px 8px rgba(0,0,0,.06);
  --shadow-primary: 0 4px 20px rgba(26,77,122,.25);
  --border-color: #e8e0d8;
  --border-light: #ece8e2;
  --transition-base: .25s ease;
  --transition-fast: .15s ease;
  --section-padding: 60px;
}

/* ── Filter bar ── */
.sl-filters {
    background: var(--white);
    border-bottom: 1px solid var(--border-color);
    padding: 16px 20px;
    position: sticky;
    top: 0;
    z-index: 50;
}
.sl-filters .container {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.sl-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-secondary);
    background: var(--bg-cream);
    border: 1px solid transparent;
    text-decoration: none;
    transition: var(--transition-fast);
}
.sl-filter-pill:hover {
    border-color: var(--primary, #1a4d7a);
    color: var(--primary, #1a4d7a);
}
.sl-filter-pill.active {
    background: var(--primary, #1a4d7a);
    color: var(--white);
}
.sl-filter-pill .count {
    font-size: 0.8rem;
    opacity: 0.7;
}

/* ── Listing section ── */
.sl-listing {
    padding: var(--section-padding) 20px;
    background: var(--bg-cream);
    min-height: 400px;
}
.sl-listing .section-header {
    text-align: center;
    margin-bottom: 40px;
}
.sl-listing .section-header h2 {
    font-family: var(--ff-heading, 'Playfair Display', serif);
    font-size: clamp(1.5rem, 3vw, 2rem);
    color: var(--text-primary);
    margin-bottom: 8px;
}
.sl-listing .section-header p {
    color: var(--text-secondary);
    font-size: 1rem;
}

/* ── Card link wrapper ── */
.card-quartier {
    display: block;
    text-decoration: none;
    color: inherit;
}

/* ── Empty state ── */
.sl-empty {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-secondary);
}
.sl-empty i {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.4;
}
.sl-empty p {
    font-size: 1.1rem;
    margin-bottom: 20px;
}
.sl-empty a {
    color: var(--primary, #1a4d7a);
    font-weight: 600;
    text-decoration: underline;
}

/* ── Badge ── */
.secteur-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 10px;
    border-radius: 4px;
    background: var(--bg-cream);
    color: var(--primary, #1a4d7a);
    margin-bottom: 8px;
}

/* ── Hero stats ── */
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 30px;
}
.hero-stat {
    text-align: center;
}
.hero-stat .number {
    font-size: 2rem;
    font-weight: 800;
}
.hero-stat .label {
    font-size: 0.9rem;
    opacity: 0.85;
}

/* ── Discover link inside card ── */
.card-quartier .discover-link {
    display: inline-block;
    color: var(--primary, #1a4d7a);
    font-weight: 600;
    font-size: 0.95rem;
    margin-top: 15px;
    transition: var(--transition-fast);
}
.card-quartier:hover .discover-link {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .hero-stats { gap: 20px; }
    .hero-stat .number { font-size: 1.5rem; }
    .sl-filters .container { gap: 6px; }
    .sl-filter-pill { padding: 6px 14px; font-size: 0.85rem; }
}
</style>
</head>
<body>
<?php if (class_exists('SiteSettings')): ?><?= SiteSettings::trackingBody() ?><?php endif; ?>
<?php if (!empty($hf['header'])): echo renderHeader($hf['header']); endif; ?>

<!-- ═══ HERO ═══ -->
<section class="hero-quartier">
    <div class="hero-container">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p class="hero-subtitle">Découvrez nos guides complets des quartiers bordelais : prix, transports, ambiance et conseils pour votre projet immobilier.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="number"><?= $total ?></div>
                <div class="label">secteur<?= $total > 1 ? 's' : '' ?></div>
            </div>
<?php
$villes = [];
foreach ($secteurs as $s) {
    $v = $s['ville'] ?? '';
    if ($v) $villes[$v] = true;
}
$nbVilles = count($villes);
if ($nbVilles > 0):
?>
            <div class="hero-stat">
                <div class="number"><?= $nbVilles ?></div>
                <div class="label">ville<?= $nbVilles > 1 ? 's' : '' ?></div>
            </div>
<?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══ FIL D'ARIANE ═══ -->
<nav class="fil-ariane">
    <div class="container">
        <a href="/">Accueil</a> ›
<?php if ($typeFilter): ?>
        <a href="/secteurs">Secteurs</a> › <strong><?= htmlspecialchars(ucfirst($typeFilter)) ?></strong>
<?php else: ?>
        <strong>Secteurs</strong>
<?php endif; ?>
    </div>
</nav>

<!-- ═══ FILTRES ═══ -->
<?php if (!empty($types)): ?>
<div class="sl-filters">
    <div class="container">
        <a href="/secteurs" class="sl-filter-pill<?= !$typeFilter ? ' active' : '' ?>">
            Tous <span class="count">(<?= $total ?>)</span>
        </a>
<?php foreach ($types as $t): ?>
        <a href="/secteurs?type=<?= urlencode($t['t']) ?>"
           class="sl-filter-pill<?= $typeFilter === $t['t'] ? ' active' : '' ?>">
            <?= htmlspecialchars(ucfirst($t['t'])) ?> <span class="count">(<?= $t['nb'] ?>)</span>
        </a>
<?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ═══ LISTING ═══ -->
<section class="sl-listing">
    <div class="container">

<?php if ($total > 0): ?>
        <div class="section-header">
            <h2><?= $total ?> secteur<?= $total > 1 ? 's' : '' ?> à découvrir</h2>
            <p>Cliquez sur un quartier pour accéder à son guide complet.</p>
        </div>

        <div class="grid-quartiers">
<?php foreach ($secteurs as $s):
    $img  = $s['hero_image'] ?? '';
    $desc = truncateSecteur($s['description'] ?? '', 120);
    $slug = $s['slug'] ?? '';
    $nom  = $s['nom'] ?? '';
    $type = $s['type_secteur'] ?? 'quartier';
    $prix = $s['prix_moyen'] ?? '';
?>
            <a href="/secteurs/<?= htmlspecialchars($slug) ?>" class="card-quartier">
<?php if ($img): ?>
                <div class="card-quartier-image" style="background-image:url('<?= htmlspecialchars($img) ?>')"></div>
<?php else: ?>
                <div class="card-quartier-image" style="background:linear-gradient(135deg, var(--primary, #1a4d7a), var(--primary-dark, #0e3a5c))"></div>
<?php endif; ?>
                <div class="card-quartier-content">
                    <span class="secteur-badge"><?= htmlspecialchars(ucfirst($type)) ?></span>
                    <h3><?= htmlspecialchars($nom) ?></h3>
<?php if ($desc): ?>
                    <p><?= htmlspecialchars($desc) ?></p>
<?php endif; ?>
<?php if ($prix): ?>
                    <div class="price">Prix moyen : <strong><?= htmlspecialchars($prix) ?>/m²</strong></div>
<?php endif; ?>
                    <span class="discover-link">Découvrir <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
<?php endforeach; ?>
        </div>

<?php else: ?>
        <div class="sl-empty">
            <div><i class="fas fa-map-marked-alt"></i></div>
<?php if ($typeFilter || $villeFilter || $searchFilter): ?>
            <p>Aucun secteur ne correspond à vos critères.</p>
            <a href="/secteurs">Voir tous les secteurs</a>
<?php else: ?>
            <p>Les secteurs sont en cours de préparation.<br>Revenez bientôt pour découvrir nos guides de quartiers !</p>
            <a href="/">Retour à l'accueil</a>
<?php endif; ?>
        </div>
<?php endif; ?>

    </div>
</section>

<!-- ═══ CTA ═══ -->
<section class="cta-secteur">
    <div class="container">
        <h2>Vous cherchez un bien dans un quartier précis ?</h2>
        <a href="/contact">Contactez-nous <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

<?php if (!empty($hf['footer'])): echo renderFooter($hf['footer']); endif; ?>
</body>
</html>
