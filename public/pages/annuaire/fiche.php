<?php
/**
 * Fiche annuaire : /commerces/{ville_slug}/{poi_slug}
 * Données : table guide_pois (migration 032+034).
 */
declare(strict_types=1);

$villeSlug = isset($ville_slug) ? normalizeVilleSlug((string) $ville_slug) : '';
$poiSlug   = trim((string) ($poi_slug ?? ''));
$poiSlug   = preg_replace('/[^a-z0-9\-]/', '', strtolower($poiSlug)) ?? '';

if ($villeSlug === '' || $poiSlug === '') {
    http_response_code(404);
    if (is_file(ROOT_PATH . '/public/pages/404.php')) {
        require ROOT_PATH . '/public/pages/404.php';
    } else {
        echo '404';
    }
    return;
}

$row = null;
try {
    $pdo = db();
    $st = $pdo->prepare(
        'SELECT p.*,
                COALESCE(v.slug, vq.slug) AS city_slug,
                COALESCE(v.nom, vq.nom) AS ville_nom,
                q.slug AS q_slug, q.nom AS q_nom,
                c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
         FROM guide_pois p
         LEFT JOIN villes v ON v.id = p.ville_id
         LEFT JOIN quartiers q ON q.id = p.quartier_id
         LEFT JOIN villes vq ON vq.id = q.ville_id
         INNER JOIN guide_poi_categories c ON c.id = p.category_id
         WHERE p.slug = ? AND p.is_active = 1 LIMIT 1'
    );
    $st->execute([$poiSlug]);
    $row = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($row && (!isset($row['city_slug']) || (string) $row['city_slug'] === '')) {
        $row = null;
    } elseif ($row && normalizeVilleSlug((string) $row['city_slug']) !== $villeSlug) {
        $row = null;
    }
} catch (Throwable $e) {
    error_log('[annuaire fiche] ' . $e->getMessage());
}

if (!$row) {
    http_response_code(404);
    if (is_file(ROOT_PATH . '/public/pages/404.php')) {
        require ROOT_PATH . '/public/pages/404.php';
    } else {
        echo '404';
    }
    return;
}

$nom  = (string) ($row['name'] ?? '');
$desc = trim((string) ($row['description'] ?? ''));
$metaDesc = $desc !== '' ? strip_tags(substr($desc, 0, 160)) : $nom . ' — ' . (string) ($row['cat_name'] ?? '') . ' à ' . (string) ($row['ville_nom'] ?? '');

$pageTitle = $nom . ' — ' . (string) ($row['cat_name'] ?? 'Commerce') . ' à ' . (string) ($row['ville_nom'] ?? '');
$metaDesc  = $metaDesc . (mb_strlen($desc, 'UTF-8') > 160 ? '…' : '');

$addrParts = array_filter([
    (string) ($row['address'] ?? ''),
    trim((string) ($row['postal_code'] ?? '') . ' ' . (string) ($row['ville_nom'] ?? '')),
], static fn (string $s): bool => $s !== '');
$addrLine  = $addrParts !== [] ? implode(', ', $addrParts) : (string) ($row['ville_nom'] ?? '');

$lat = $row['latitude'] ?? null;
$lng = $row['longitude'] ?? null;
$mapQuery = $addrLine !== '' ? $addrLine : $nom . ' ' . (string) ($row['ville_nom'] ?? '');

$img = (string) ($row['featured_image'] ?? '');
if ($img !== '' && $img[0] === '/') {
    $img = rtrim((string) (defined('APP_URL') ? APP_URL : ''), '/') . $img;
}

$rating = isset($row['rating']) && $row['rating'] !== null && (string) $row['rating'] !== ''
    ? (float) $row['rating'] : null;
$reviewsC = (int) ($row['reviews_count'] ?? 0);
$verif   = (int) ($row['is_verified'] ?? 0) === 1;

$canonicalPath = '/commerces/' . rawurlencode($villeSlug) . '/' . rawurlencode($poiSlug);
$canonical = rtrim((string) (defined('APP_URL') ? APP_URL : ''), '/') . $canonicalPath;
$ld = [
    '@context'   => 'https://schema.org',
    '@type'      => 'LocalBusiness',
    'name'       => $nom,
    'url'        => $canonical,
];
if ($img !== '') {
    $ld['image'] = $img;
}
if ($addrLine !== '') {
    $ld['address'] = [
        '@type' => 'PostalAddress',
        'streetAddress'   => (string) ($row['address'] ?? ''),
        'postalCode'     => (string) ($row['postal_code'] ?? ''),
        'addressLocality'=> (string) ($row['ville_nom'] ?? ''),
        'addressCountry' => 'FR',
    ];
}
$phoneB = (string) ($row['phone'] ?? '');
if ($phoneB !== '') {
    $ld['telephone'] = $phoneB;
}
$emailB = (string) ($row['email'] ?? '');
if ($emailB !== '') {
    $ld['email'] = $emailB;
}
$webB = (string) ($row['website'] ?? '');
if ($webB !== '') {
    $ld['sameAs'] = array_filter([
        $webB,
        (string) ($row['facebook'] ?? ''),
        (string) ($row['instagram'] ?? ''),
    ], static fn (string $s): bool => $s !== '');
    if (count($ld['sameAs']) === 1) {
        $ld['sameAs'] = $ld['sameAs'][0];
    }
}
if ($rating !== null && $rating > 0 && $reviewsC > 0) {
    $ld['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => (string) $rating,
        'reviewCount' => (string) $reviewsC,
    ];
} elseif ($rating !== null && $rating > 0) {
    $ld['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => (string) $rating,
        'reviewCount' => '1',
    ];
}
?>
<article class="annuaire-fiche container" style="max-width:860px;margin:0 auto;padding:32px 20px 60px">
    <nav style="font-size:14px;color:#64748b;margin-bottom:20px">
        <a href="/">Accueil</a>
        <span style="margin:0 6px">/</span>
        <a href="/guide-local">Guide local</a>
        <span style="margin:0 6px">/</span>
        <a href="/guide-local/annuaire/<?= e($villeSlug) ?>"><?= e((string) ($row['ville_nom'] ?? '')) ?></a>
    </nav>

    <header style="display:flex;flex-wrap:wrap;gap:20px;align-items:flex-start;margin-bottom:24px">
        <?php if ($img !== ''): ?>
            <img src="<?= e($img) ?>" alt="" width="160" height="120" style="object-fit:cover;border-radius:12px;border:1px solid #e2e8f0">
        <?php endif; ?>
        <div>
            <p style="margin:0 0 4px;font-size:14px;color:#64748b">
                <span class="fas <?= e((string) ($row['cat_icon'] ?: 'fa-store')) ?>" aria-hidden="true"></span>
                <?= e((string) ($row['cat_name'] ?? '')) ?>
            </p>
            <h1 style="margin:0;font-size:clamp(1.4rem,3vw,1.85rem);line-height:1.2"><?= e($nom) ?>
                <?php if ($verif): ?>
                    <span style="font-size:14px;color:#0ea5e9;font-weight:600;white-space:nowrap" title="Fiche vérifiée">(vérifié)</span>
                <?php endif; ?>
            </h1>
            <p style="margin:8px 0 0;font-size:15px;color:#334155"><?= e($addrLine) ?></p>
            <?php if ($rating !== null && $rating > 0): ?>
                <p style="margin:6px 0 0;font-size:15px">★ <?= e(number_format($rating, 1, ',', ' ')) ?><?= $reviewsC > 0 ? ' · ' . (int) $reviewsC . ' avis' : '' ?></p>
            <?php endif; ?>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:14px">
                <?php if ($phoneB !== ''): ?>
                    <a class="btn btn-primary" href="tel:<?= e(preg_replace('/\s+/', '', $phoneB) ?: $phoneB) ?>">Appeler</a>
                <?php endif; ?>
                <?php if ($webB !== ''): ?>
                    <a class="btn" href="<?= e($webB) ?>" rel="nofollow noopener" target="_blank">Site web</a>
                <?php endif; ?>
                <a class="btn" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($mapQuery)) ?>" rel="noopener" target="_blank">Itinéraire</a>
            </div>
        </div>
    </header>

    <?php if ($desc !== ''): ?>
        <div class="prose" style="font-size:16px;line-height:1.65;color:#1e293b">
            <?php echo nl2br(e($desc)); ?>
        </div>
    <?php endif; ?>

    <?php
    $hours = (string) ($row['opening_hours'] ?? '');
    if ($hours !== ''): ?>
        <section style="margin-top:28px">
            <h2 style="font-size:18px;margin:0 0 10px">Horaires</h2>
            <div style="white-space:pre-wrap;font-size:15px;color:#334155"><?= e($hours) ?></div>
        </section>
    <?php endif; ?>

    <?php if (is_numeric((string) $lat) && is_numeric((string) $lng) && (float) $lat !== 0.0 && (float) $lng !== 0.0): ?>
        <section style="margin-top:24px">
            <h2 style="font-size:18px;margin:0 0 8px">Carte</h2>
            <p style="font-size:14px;color:#64748b">Coordonnées GPS : <?= e((string) $lat) ?>, <?= e((string) $lng) ?> — voir la <a href="/guide-local">carte interactive de l’annuaire</a> pour l’affichage sur une carte.</p>
        </section>
    <?php endif; ?>
</article>

<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
