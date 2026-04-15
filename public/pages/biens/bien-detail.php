<?php
// bootstrap déjà chargé par public/index.php

// ── Récupération du bien ──────────────────────────────────────
$slug = $GLOBALS['bienSlug'] ?? '';

$pdo  = db();
$bien = $pdo->prepare("SELECT * FROM biens WHERE slug = :slug AND statut != 'Archivé' LIMIT 1");
$bien->execute([':slug' => $slug]);
$b = $bien->fetch(PDO::FETCH_ASSOC);

if (!$b) {
    http_response_code(404);
    $pageTitle   = 'Bien introuvable';
    $pageContent = '<section class="section"><div class="container text-center"><h1 style="font-size:4rem;color:var(--clr-primary)">404</h1><p>Ce bien est introuvable ou n\'est plus disponible.</p><a href="/biens" class="btn btn--primary" style="margin-top:1.5rem">Voir nos biens</a></div></section>';
    require ROOT_PATH . '/public/templates/layout.php';
    exit;
}

// ── Photos : on construit un tableau depuis photo_principale ──
$photos = [];
if (!empty($b['photo_principale'])) {
    $photos[] = ['url' => $b['photo_principale'], 'url_thumb' => $b['photo_principale']];
}

// ── Biens similaires ──────────────────────────────────────────
$similairesStmt = $pdo->prepare("
    SELECT * FROM biens
    WHERE  statut != 'Archivé'
    AND    id     != :id
    AND    type_bien = :type_bien
    ORDER  BY created_at DESC
    LIMIT  3
");
$similairesStmt->execute([':id' => $b['id'], ':type_bien' => $b['type_bien']]);
$similaires = $similairesStmt->fetchAll(PDO::FETCH_ASSOC);

// ── Aliases de colonnes pour rétro-compatibilité du template ─
$b['secteur_name'] = $b['secteur'] ?: $b['ville'] ?: 'Aix-en-Provence';
$b['type_label']   = ucfirst($b['type_bien'] ?? 'Bien');
$typeIcons = ['appartement'=>'fa-building','maison'=>'fa-home','terrain'=>'fa-mountain','local'=>'fa-store','autre'=>'fa-key'];
$b['type_icon']    = $typeIcons[$b['type_bien'] ?? ''] ?? 'fa-home';
$b['sdb']          = $b['salles_de_bain'] ?? null;
// Advisor — chargé depuis les settings
$_advFirst = trim((string) setting('advisor_firstname', ''));
$_advLast  = trim((string) setting('advisor_lastname',  ''));
$_advFull  = trim($_advFirst . ' ' . $_advLast);
if ($_advFull === '') {
    $_advFull = isset($advisorName) && $advisorName !== ''
        ? $advisorName
        : (defined('ADVISOR_NAME') ? ADVISOR_NAME : 'Votre conseiller');
}
$_advCity = isset($zoneCity) && $zoneCity !== '' ? $zoneCity : (defined('APP_CITY') ? APP_CITY : '');

$b['advisor_name']  = $_advFull;
$b['advisor_title'] = trim((string) setting('advisor_title', 'Expert immobilier'))
    . ($_advCity !== '' ? ' — ' . $_advCity : '');
$b['advisor_phone'] = isset($advisorPhoneDisplay) && $advisorPhoneDisplay !== ''
    ? $advisorPhoneDisplay
    : trim((string) setting('advisor_phone', defined('APP_PHONE') ? APP_PHONE : ''));
$b['advisor_photo'] = trim((string) setting('advisor_photo', '/assets/images/advisor-photo.jpg'));
$b['advisor_email'] = defined('CONTACT_EMAIL') ? CONTACT_EMAIL
    : (defined('APP_EMAIL') ? APP_EMAIL : '');

// ── DPE — couleurs ────────────────────────────────────────────
$dpeColors = [
    'A' => ['bg' => '#009a44', 'label' => 'Très performant'],
    'B' => ['bg' => '#56c02b', 'label' => 'Performant'],
    'C' => ['bg' => '#addb3b', 'label' => 'Assez performant'],
    'D' => ['bg' => '#ffcf00', 'label' => 'Peu performant'],
    'E' => ['bg' => '#f6a31a', 'label' => 'Énergivore'],
    'F' => ['bg' => '#f15a24', 'label' => 'Très énergivore'],
    'G' => ['bg' => '#e2001a', 'label' => 'Extrêmement énergivore'],
];
$dpe    = strtoupper($b['dpe_classe']    ?? 'D');
$ges    = strtoupper($b['ges_classe']    ?? 'D');

// ── Formatage prix ────────────────────────────────────────────
if (!function_exists('formatPrice')) {
    function formatPrice(int $price): string {
        return number_format($price, 0, ',', ' ') . ' €';
    }
}

// ── Meta ──────────────────────────────────────────────────────
$pageTitle = htmlspecialchars($b['titre'])
           . ' — '
           . htmlspecialchars($b['secteur_name'])
           . ' | Pascal Hamm Immobilier';

$metaDesc  = 'Découvrez ce bien immobilier à '
           . htmlspecialchars($b['secteur_name'])
           . ' : '
           . htmlspecialchars(mb_substr(strip_tags($b['description'] ?? ''), 0, 140))
           . '…';

$extraCss = ['/assets/css/bien-detail.css'];
$extraJs  = ['/assets/js/bien-detail.js'];
?>

<!-- ── Schema.org RealEstateListing ──────────────────────────── -->
<script type="application/ld+json">
{
    "@context":     "https://schema.org",
    "@type":        "RealEstateListing",
    "name":         "<?= htmlspecialchars($b['titre']) ?>",
    "description":  "<?= htmlspecialchars(mb_substr(strip_tags($b['description'] ?? ''), 0, 200)) ?>",
    "url":          "https://pascalhamm.fr/biens/<?= htmlspecialchars($b['slug']) ?>",
    "datePosted":   "<?= date('Y-m-d', strtotime($b['created_at'])) ?>",
    "price":        "<?= $b['prix'] ?>",
    "priceCurrency":"EUR",
    "image":        "<?= !empty($photos) ? htmlspecialchars($photos[0]['url']) : '' ?>",
    "address": {
        "@type":           "PostalAddress",
        "addressLocality": "<?= htmlspecialchars($b['secteur_name']) ?>",
        "addressRegion":   "Bouches-du-Rhône",
        "addressCountry":  "FR"
    }
}
</script>

<div class="bien-detail-page">

    <!-- ── BREADCRUMB ──────────────────────────────────────── -->
    <nav class="breadcrumb" aria-label="Fil d'ariane">
        <div class="container">
            <ol class="breadcrumb__list">
                <li class="breadcrumb__item">
                    <a href="/">Accueil</a>
                </li>
                <li class="breadcrumb__item">
                    <a href="/biens">Annonces</a>
                </li>
                <li class="breadcrumb__item">
                    <a href="/biens?secteur=<?= urlencode($b['secteur_name']) ?>">
                        <?= htmlspecialchars($b['secteur_name']) ?>
                    </a>
                </li>
                <li class="breadcrumb__item breadcrumb__item--active" aria-current="page">
                    <?= htmlspecialchars($b['titre']) ?>
                </li>
            </ol>
        </div>
    </nav>

    <div class="container">
        <div class="bien-detail-layout">

            <!-- ══ COLONNE PRINCIPALE ════════════════════════ -->
            <div class="bien-detail-main">

                <!-- ── GALERIE PHOTOS ─────────────────────── -->
                <section class="bien-gallery" id="gallery">
                    <?php if (!empty($photos)): ?>

                    <!-- Photo principale -->
                    <div class="bien-gallery__main">
                        <img
                            src="<?= htmlspecialchars($photos[0]['url']) ?>"
                            alt="<?= htmlspecialchars($b['titre']) ?> — photo principale"
                            class="bien-gallery__main-img"
                            id="galleryMainImg"
                            loading="eager"
                            onerror="this.onerror=null;this.src='/assets/images/placeholder.php?type=<?= urlencode($b['type_bien'] ?? 'bien') ?>&surface=<?= (int)($b['surface'] ?? 0) ?>&pieces=<?= (int)($b['pieces'] ?? 0) ?>'">

                        <!-- Badges sur la photo -->
                        <div class="bien-gallery__badges">
                            <?php if (!empty($b['exclusif'])): ?>
                            <span class="bien-badge bien-badge--exclusif">
                                <i class="fas fa-star"></i> Exclusivité
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($b['nouveaute'])): ?>
                            <span class="bien-badge bien-badge--new">
                                <i class="fas fa-bolt"></i> Nouveauté
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($b['viager'])): ?>
                            <span class="bien-badge bien-badge--viager">
                                <i class="fas fa-handshake"></i> Viager
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($b['vendu'])): ?>
                            <span class="bien-badge bien-badge--vendu">
                                <i class="fas fa-check-circle"></i> Vendu
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Compteur photos -->
                        <div class="bien-gallery__counter">
                            <i class="fas fa-camera"></i>
                            <span id="galleryCurrentIdx">1</span> / <?= count($photos) ?>
                        </div>

                        <!-- Flèches -->
                        <button class="bien-gallery__arrow bien-gallery__arrow--prev"
                                id="galleryPrev" aria-label="Photo précédente">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="bien-gallery__arrow bien-gallery__arrow--next"
                                id="galleryNext" aria-label="Photo suivante">
                            <i class="fas fa-chevron-right"></i>
                        </button>

                        <!-- Bouton fullscreen -->
                        <button class="bien-gallery__fullscreen" id="galleryFullscreen"
                                aria-label="Plein écran">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>

                    <!-- Miniatures -->
                    <?php if (count($photos) > 1): ?>
                    <div class="bien-gallery__thumbs" id="galleryThumbs">
                        <?php foreach ($photos as $i => $photo): ?>
                        <button class="bien-gallery__thumb <?= $i === 0 ? 'is-active' : '' ?>"
                                data-index="<?= $i ?>"
                                aria-label="Photo <?= $i + 1 ?>">
                            <img
                                src="<?= htmlspecialchars($photo['url_thumb'] ?? $photo['url']) ?>"
                                alt="<?= htmlspecialchars($b['titre']) ?> — photo <?= $i + 1 ?>"
                                loading="lazy">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Lightbox (caché, activé par JS) -->
                    <div class="bien-lightbox" id="bien-lightbox" aria-hidden="true">
                        <div class="bien-lightbox__overlay" id="lightboxOverlay"></div>
                        <div class="bien-lightbox__content">
                            <button class="bien-lightbox__close" id="lightboxClose"
                                    aria-label="Fermer">
                                <i class="fas fa-times"></i>
                            </button>
                            <button class="bien-lightbox__arrow bien-lightbox__arrow--prev"
                                    id="lightboxPrev" aria-label="Précédent">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <img src="" alt="" class="bien-lightbox__img" id="lightboxImg">
                            <button class="bien-lightbox__arrow bien-lightbox__arrow--next"
                                    id="lightboxNext" aria-label="Suivant">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <div class="bien-lightbox__counter">
                                <span id="lightboxIdx">1</span> / <?= count($photos) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Data JSON pour JS -->
                    <script>
                        window.GALLERY_PHOTOS = <?= json_encode(array_column($photos, 'url')) ?>;
                    </script>

                    <?php else: ?>
                    <!-- Placeholder si pas de photo -->
                    <div class="bien-gallery__placeholder">
                        <i class="fas fa-image"></i>
                        <span>Photos bientôt disponibles</span>
                    </div>
                    <?php endif; ?>
                </section>

                <!-- ── EN-TÊTE DU BIEN ────────────────────── -->
                <section class="bien-header">
                    <div class="bien-header__top">
                        <div class="bien-header__type">
                            <i class="fas <?= htmlspecialchars($b['type_icon'] ?? 'fa-home') ?>"></i>
                            <?= htmlspecialchars($b['type_label'] ?? 'Bien') ?>
                        </div>
                        <div class="bien-header__ref">
                            Réf. <?= htmlspecialchars($b['reference'] ?? $b['id']) ?>
                        </div>
                    </div>

                    <h1 class="bien-header__title">
                        <?= htmlspecialchars($b['titre']) ?>
                    </h1>

                    <div class="bien-header__location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($b['secteur_name']) ?>
                        <?php if (!empty($b['ville'])): ?>
                            — <?= htmlspecialchars($b['ville']) ?>
                        <?php endif; ?>
                    </div>

                    <div class="bien-header__price-row">
                        <div class="bien-header__price">
                            <?= formatPrice((int)$b['prix']) ?>
                            <?php if (!empty($b['prix_hni'])): ?>
                            <span class="bien-header__price-hni">
                                dont <?= formatPrice((int)$b['honoraires']) ?> d'honoraires
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($b['surface'])): ?>
                        <div class="bien-header__prixm2">
                            <?= number_format($b['prix'] / $b['surface'], 0, ',', ' ') ?> €/m²
                        </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- ── CHIFFRES CLÉS ──────────────────────── -->
                <section class="bien-specs">
                    <?php
                    $specs = [
                        ['icon' => 'fa-ruler-combined', 'label' => 'Surface',    'val' => !empty($b['surface'])   ? $b['surface'].' m²'       : null],
                        ['icon' => 'fa-door-open',      'label' => 'Pièces',     'val' => !empty($b['pieces'])    ? $b['pieces'].' pièce(s)'  : null],
                        ['icon' => 'fa-bed',            'label' => 'Chambres',   'val' => !empty($b['chambres'])  ? $b['chambres']            : null],
                        ['icon' => 'fa-bath',           'label' => 'SDB',        'val' => !empty($b['sdb'])       ? $b['sdb']                 : null],
                        ['icon' => 'fa-building',       'label' => 'Étage',      'val' => !empty($b['etage'])     ? $b['etage']               : null],
                        ['icon' => 'fa-tree',           'label' => 'Terrain',    'val' => !empty($b['terrain'])   ? $b['terrain'].' m²'       : null],
                        ['icon' => 'fa-car',            'label' => 'Parking',    'val' => !empty($b['parking'])   ? $b['parking'].' place(s)' : null],
                        ['icon' => 'fa-calendar-alt',   'label' => 'Construit',  'val' => !empty($b['annee'])     ? $b['annee']               : null],
                    ];
                    ?>
                    <div class="bien-specs__grid">
                        <?php foreach ($specs as $spec):
                            if ($spec['val'] === null) continue; ?>
                        <div class="bien-spec-item">
                            <div class="bien-spec-item__icon">
                                <i class="fas <?= $spec['icon'] ?>"></i>
                            </div>
                            <div class="bien-spec-item__body">
                                <span class="bien-spec-item__label"><?= $spec['label'] ?></span>
                                <strong class="bien-spec-item__val"><?= htmlspecialchars($spec['val']) ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- ── DESCRIPTION ───────────────────────── -->
                <section class="bien-description" id="description">
                    <h2 class="bien-section__title">
                        <i class="fas fa-align-left"></i>
                        Description
                    </h2>
                    <div class="bien-description__text" id="descText">
                        <?= nl2br(htmlspecialchars($b['description'] ?? '')) ?>
                    </div>
                    <?php if (strlen($b['description'] ?? '') > 600): ?>
                    <button class="bien-description__toggle" id="descToggle">
                        <i class="fas fa-chevron-down"></i>
                        Lire la suite
                    </button>
                    <?php endif; ?>
                </section>

                <!-- ── ATOUTS & ÉQUIPEMENTS ───────────────── -->
                <?php if (!empty($b['equipements'])): ?>
                <?php $equips = json_decode($b['equipements'], true) ?? []; ?>
                <?php if (!empty($equips)): ?>
                <section class="bien-equips" id="equipements">
                    <h2 class="bien-section__title">
                        <i class="fas fa-list-check"></i>
                        Atouts & Équipements
                    </h2>
                    <div class="bien-equips__grid">
                        <?php foreach ($equips as $eq): ?>
                        <div class="bien-equip-item">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($eq) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                <?php endif; ?>

                <!-- ── DPE & GES ──────────────────────────── -->
                <?php if (!empty($b['dpe_classe'])): ?>
                <section class="bien-dpe" id="dpe">
                    <h2 class="bien-section__title">
                        <i class="fas fa-leaf"></i>
                        Diagnostic de Performance Énergétique
                    </h2>
                    <div class="bien-dpe__grid">

                        <!-- DPE -->
                        <div class="dpe-block">
                            <div class="dpe-block__title">Énergie</div>
                            <div class="dpe-scale">
                                <?php foreach (['A','B','C','D','E','F','G'] as $letter): ?>
                                <div class="dpe-scale__row
                                    <?= $letter === $dpe ? 'dpe-scale__row--active' : '' ?>"
                                    style="--dpe-color: <?= $dpeColors[$letter]['bg'] ?>">
                                    <span class="dpe-scale__letter"><?= $letter ?></span>
                                    <?php if ($letter === $dpe): ?>
                                    <span class="dpe-scale__value">
                                        <?= (int)($b['dpe_valeur'] ?? 0) ?> kWh/m²/an
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="dpe-block__label"
                                 style="background: <?= $dpeColors[$dpe]['bg'] ?>">
                                <?= $dpe ?> — <?= $dpeColors[$dpe]['label'] ?>
                            </div>
                        </div>

                        <!-- GES -->
                        <?php if (!empty($b['ges_classe'])): ?>
                        <div class="dpe-block">
                            <div class="dpe-block__title">Gaz à effet de serre</div>
                            <div class="dpe-scale">
                                <?php foreach (['A','B','C','D','E','F','G'] as $letter): ?>
                                <div class="dpe-scale__row
                                    <?= $letter === $ges ? 'dpe-scale__row--active' : '' ?>"
                                    style="--dpe-color: <?= $dpeColors[$letter]['bg'] ?>">
                                    <span class="dpe-scale__letter"><?= $letter ?></span>
                                    <?php if ($letter === $ges): ?>
                                    <span class="dpe-scale__value">
                                        <?= (int)($b['ges_valeur'] ?? 0) ?> kg CO₂/m²/an
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="dpe-block__label"
                                 style="background: <?= $dpeColors[$ges]['bg'] ?>">
                                <?= $ges ?> — <?= $dpeColors[$ges]['label'] ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </section>
                <?php endif; ?>

                <!-- ── LOCALISATION / CARTE ───────────────── -->
                <section class="bien-map" id="localisation">
                    <h2 class="bien-section__title">
                        <i class="fas fa-map-marker-alt"></i>
                        Localisation
                    </h2>
                    <div class="bien-map__wrapper">
                        <?php if (!empty($b['lat']) && !empty($b['lng'])): ?>
                        <div class="bien-map__container"
                             id="bienMap"
                             data-lat="<?= htmlspecialchars($b['lat']) ?>"
                             data-lng="<?= htmlspecialchars($b['lng']) ?>"
                             data-titre="<?= htmlspecialchars($b['titre']) ?>">
                        </div>
                        <?php else: ?>
                        <div class="bien-map__placeholder">
                            <i class="fas fa-map"></i>
                            <span>Localisation disponible sur demande</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="bien-map__note">
                        <i class="fas fa-info-circle"></i>
                        La localisation exacte est communiquée lors de la prise de contact.
                    </p>

                    <!-- Points d'intérêt Aix -->
                    <div class="bien-poi-grid">
                        <?php
                        $pois = [
                            ['icon' => 'fa-graduation-cap', 'label' => 'Universités / Grandes écoles'],
                            ['icon' => 'fa-train',          'label' => 'Gare TGV Aix-en-Provence'],
                            ['icon' => 'fa-bus',            'label' => 'Bus / Navettes'],
                            ['icon' => 'fa-shopping-bag',   'label' => 'Cours Mirabeau / commerces'],
                            ['icon' => 'fa-plane',          'label' => 'Aéroport Marseille-Provence'],
                            ['icon' => 'fa-road',           'label' => 'A8 / A51 — accès autoroute'],
                        ];
                        foreach ($pois as $poi): ?>
                        <div class="bien-poi-item">
                            <i class="fas <?= $poi['icon'] ?>"></i>
                            <span><?= $poi['label'] ?></span>
                            <?php if (!empty($b['lat'])): ?>
                            <span class="bien-poi-item__dist" data-poi="<?= $poi['label'] ?>">
                                — km
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- ── MENTIONS LÉGALES PRIX ─────────────── -->
                <section class="bien-legal">
                    <?php if (!empty($b['honoraires_pct'])): ?>
                    <p>
                        <strong>Honoraires :</strong>
                        <?= htmlspecialchars($b['honoraires_pct']) ?>% TTC du prix de vente,
                        à la charge de l'acquéreur.
                        Prix vendeur net : <?= formatPrice((int)($b['prix'] - $b['honoraires'])) ?>.
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($b['copro_lots'])): ?>
                    <p>
                        <strong>Copropriété :</strong>
                        <?= (int)$b['copro_lots'] ?> lots —
                        charges annuelles : <?= formatPrice((int)$b['copro_charges']) ?> —
                        <?= !empty($b['copro_procedure']) ? 'Procédure en cours.' : 'Pas de procédure en cours.' ?>
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($b['dpe_classe']) && in_array($b['dpe_classe'], ['F','G'])): ?>
                    <p class="bien-legal__alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        Ce bien est classé <?= $b['dpe_classe'] ?>.
                        Il est considéré comme une passoire thermique
                        au sens de la loi Climat et Résilience.
                    </p>
                    <?php endif; ?>
                    <p class="bien-legal__ref">
                        Bien présenté par Pascal Hamm — Mandataire immobilier
                        indépendant rattaché au réseau IAD France.
                        RCP souscrite. Pas de maniement de fonds.
                    </p>
                </section>

            </div><!-- /.bien-detail-main -->

            <!-- ══ SIDEBAR ═══════════════════════════════════ -->
            <aside class="bien-detail-sidebar">

                <!-- Prix sticky -->
                <div class="sidebar-card sidebar-card--price">
                    <div class="sidebar-price">
                        <?= formatPrice((int)$b['prix']) ?>
                    </div>
                    <?php if (!empty($b['surface'])): ?>
                    <div class="sidebar-price__m2">
                        <?= number_format($b['prix'] / $b['surface'], 0, ',', ' ') ?> €/m²
                    </div>
                    <?php endif; ?>
                    <div class="sidebar-price__specs">
                        <?php if (!empty($b['surface'])): ?>
                        <span><i class="fas fa-ruler-combined"></i> <?= $b['surface'] ?> m²</span>
                        <?php endif; ?>
                        <?php if (!empty($b['pieces'])): ?>
                        <span><i class="fas fa-door-open"></i> <?= $b['pieces'] ?> pièces</span>
                        <?php endif; ?>
                        <?php if (!empty($b['chambres'])): ?>
                        <span><i class="fas fa-bed"></i> <?= $b['chambres'] ?> chambres</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Formulaire de contact rapide -->
                <div class="sidebar-card sidebar-card--contact-form">
                    <h3 class="sidebar-card__title">
                        <i class="fas fa-comments"></i>
                        Je suis intéressé(e)
                    </h3>

                    <?php
                    // Traitement formulaire contact rapide
                    $formSuccess = false;
                    $formError   = '';

                    if ($_SERVER['REQUEST_METHOD'] === 'POST'
                        && isset($_POST['contact_bien_submit'])
                    ) {
                        $nom     = trim($_POST['nom']     ?? '');
                        $email   = trim($_POST['email']   ?? '');
                        $tel     = trim($_POST['tel']     ?? '');
                        $message = trim($_POST['message'] ?? '');
                        $token   = $_POST['csrf_token']   ?? '';

                        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                            $formError = 'Requête invalide. Veuillez réessayer.';
                        } elseif (empty($nom) || empty($email) || empty($tel)) {
                            $formError = 'Merci de remplir tous les champs obligatoires.';
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $formError = 'Adresse email invalide.';
                        } else {
                            // Insert DB
                            [$fn, $ln] = array_pad(explode(' ', $nom, 2), 2, '');
                            $insertLead = $pdo->prepare("
                                INSERT INTO crm_leads
                                    (source_type, pipeline, stage, first_name, last_name, email, phone, notes, consent, created_at)
                                VALUES
                                    ('bien-detail','inbound','new',:fn,:ln,:email,:tel,:notes,1,NOW())
                            ");
                            $insertLead->execute([
                                ':fn'    => $fn,
                                ':ln'    => $ln,
                                ':email' => $email,
                                ':tel'   => $tel,
                                ':notes' => "Ref: " . ($b['reference'] ?? $b['id']) . "\n" . $message,
                            ]);

                            // Email notification
                            if (function_exists('send_notification_email')) {
                                send_notification_email([
                                    'subject' => 'Nouveau contact — ' . $b['titre'],
                                    'body'    => "Nom: $nom\nEmail: $email\nTél: $tel\n\n$message",
                                    'bien'    => $b['titre'],
                                ]);
                            }

                            $formSuccess = true;
                        }
                    }

                    // Génération CSRF
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    ?>

                    <?php if ($formSuccess): ?>
                    <div class="form-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Message envoyé !</strong>
                        <p>Pascal Hamm vous contactera sous 24h.</p>
                    </div>
                    <?php else: ?>

                    <?php if ($formError): ?>
                    <div class="form-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($formError) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="#" class="contact-bien-form" novalidate>
                        <input type="hidden" name="csrf_token"
                               value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="contact_bien_submit" value="1">
                        <input type="hidden" name="bien_ref"
                               value="<?= htmlspecialchars($b['reference'] ?? $b['id']) ?>">

                        <div class="form-group">
                            <label for="cb_nom">Nom complet <span>*</span></label>
                            <input type="text"
                                   id="cb_nom"
                                   name="nom"
                                   placeholder="Prénom Nom"
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                                   required
                                   autocomplete="name">
                        </div>

                        <div class="form-group">
                            <label for="cb_email">Email <span>*</span></label>
                            <input type="email"
                                   id="cb_email"
                                   name="email"
                                   placeholder="vous@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required
                                   autocomplete="email">
                        </div>

                        <div class="form-group">
                            <label for="cb_tel">Téléphone <span>*</span></label>
                            <input type="tel"
                                   id="cb_tel"
                                   name="tel"
                                   placeholder="06 00 00 00 00"
                                   value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>"
                                   required
                                   autocomplete="tel">
                        </div>

                        <div class="form-group">
                            <label for="cb_message">Message</label>
                            <textarea id="cb_message"
                                      name="message"
                                      rows="4"
                                      placeholder="Je souhaite obtenir plus d'informations sur ce bien..."
                                      ><?= htmlspecialchars($_POST['message'] ?? "Bonjour,\n\nJe suis intéressé(e) par ce bien (réf. " . ($b['reference'] ?? $b['id']) . ") et souhaite organiser une visite.\n\nCordialement") ?></textarea>
                        </div>

                        <div class="form-group form-group--check">
                            <label class="form-check">
                                <input type="checkbox" name="rgpd" required>
                                <span>
                                    J'accepte que mes données soient utilisées
                                    pour être recontacté(e) —
                                    <a href="/politique-confidentialite" target="_blank">
                                        Politique de confidentialité
                                    </a>
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn--primary btn--block">
                            <i class="fas fa-paper-plane"></i>
                            Envoyer ma demande
                        </button>

                    </form>
                    <?php endif; ?>

                </div>

                <!-- Contact direct -->
                <?php if ($b['advisor_phone']): ?>
                <div class="sidebar-card sidebar-card--advisor">
                    <div class="sidebar-advisor">
                        <?php if ($b['advisor_photo']): ?>
                        <img
                            src="<?= htmlspecialchars($b['advisor_photo']) ?>"
                            alt="<?= htmlspecialchars($b['advisor_name']) ?>"
                            class="sidebar-advisor__photo"
                            loading="lazy">
                        <?php endif; ?>
                        <div class="sidebar-advisor__info">
                            <strong><?= htmlspecialchars($b['advisor_name']) ?></strong>
                            <span><?= htmlspecialchars($b['advisor_title']) ?></span>
                        </div>
                    </div>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $b['advisor_phone'])) ?>"
                       class="btn btn--outline btn--block">
                        <i class="fas fa-phone"></i>
                        <?= htmlspecialchars($b['advisor_phone']) ?>
                    </a>
                    <a href="https://wa.me/33<?= ltrim(preg_replace('/\s+/', '', $b['advisor_phone']), '0') ?>"
                       class="btn btn--whatsapp btn--block"
                       target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </a>
                </div>
                <?php endif; ?>

                <!-- Share -->
                <div class="sidebar-card sidebar-card--share">
                    <p><strong>Partager ce bien</strong></p>
                    <div class="share-buttons">
                        <button class="share-btn share-btn--copy" id="shareCopy"
                                data-url="<?= htmlspecialchars('https://pascalhamm.fr/biens/' . $b['slug']) ?>">
                            <i class="fas fa-link"></i> Copier le lien
                        </button>
                        <a href="https://wa.me/?text=<?= urlencode($b['titre'] . ' — https://pascalhamm.fr/biens/' . $b['slug']) ?>"
                           class="share-btn share-btn--whatsapp"
                           target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="mailto:?subject=<?= urlencode($b['titre']) ?>&body=<?= urlencode('Bonjour,\n\nJe pense que ce bien pourrait vous intéresser :\n' . $b['titre'] . '\nhttps://pascalhamm.fr/biens/' . $b['slug']) ?>"
                           class="share-btn share-btn--email">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                </div>

                <!-- Favoris -->
                <div class="sidebar-card sidebar-card--fav">
                    <button class="btn btn--ghost btn--block btn--fav"
                            id="favBtn"
                            data-id="<?= $b['id'] ?>"
                            aria-label="Ajouter aux favoris">
                        <i class="far fa-heart" id="favIcon"></i>
                        <span id="favLabel">Ajouter aux favoris</span>
                    </button>
                </div>

            </aside><!-- /.bien-detail-sidebar -->

        </div><!-- /.bien-detail-layout -->
    </div><!-- /.container -->

    <!-- ── BIENS SIMILAIRES ────────────────────────────────── -->
    <?php if (!empty($similaires)): ?>
    <section class="biens-similaires section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-th-large"></i>
                Biens similaires
            </h2>
            <div class="biens-similaires__grid">
                <?php foreach ($similaires as $s): ?>
                <article class="bien-card">
                    <a href="/biens/<?= htmlspecialchars($s['slug']) ?>"
                       class="bien-card__img-link">
                        <img
                            src="<?= !empty($s['photo_principale']) ? htmlspecialchars($s['photo_principale']) : '/assets/images/placeholder.php?type=' . urlencode($s['type_bien'] ?? 'bien') . '&surface=' . (int)($s['surface'] ?? 0) . '&pieces=' . (int)($s['pieces'] ?? 0) ?>"
                            alt="<?= htmlspecialchars($s['titre']) ?>"
                            loading="lazy"
                            class="bien-card__img"
                            onerror="this.onerror=null;this.src='/assets/images/placeholder.php?type=<?= urlencode($s['type_bien'] ?? 'bien') ?>'">
                        <span class="bien-card__type">
                            <?= htmlspecialchars(ucfirst($s['type_bien'] ?? 'Bien')) ?>
                        </span>
                    </a>
                    <div class="bien-card__body">
                        <h3 class="bien-card__title">
                            <a href="/biens/<?= htmlspecialchars($s['slug']) ?>">
                                <?= htmlspecialchars($s['titre']) ?>
                            </a>
                        </h3>
                        <div class="bien-card__price">
                            <?= formatPrice((int)$s['prix']) ?>
                        </div>
                        <div class="bien-card__specs">
                            <?php if (!empty($s['surface'])): ?>
                            <span><i class="fas fa-ruler-combined"></i> <?= $s['surface'] ?> m²</span>
                            <?php endif; ?>
                            <?php if (!empty($s['pieces'])): ?>
                            <span><i class="fas fa-door-open"></i> <?= $s['pieces'] ?> pièces</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="biens-similaires__more">
                <a href="/biens" class="btn btn--outline">
                    <i class="fas fa-search"></i>
                    Voir toutes les annonces
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

</div><!-- /.bien-detail-page -->

