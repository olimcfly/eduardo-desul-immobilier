<?php
/**
 * Guide annuaire commerçants par ville — /guide-local/annuaire/{ville_slug}
 * Contenu de présentation : table villes (image_url, description) + fiches guide_pois.
 */
declare(strict_types=1);

$villeSlug = isset($ville_slug) ? (string) $ville_slug : '';
$villeSlug = $villeSlug !== '' ? normalizeVilleSlug($villeSlug) : '';

$ville  = null;
$pois   = [];
$err    = '';
$baseU  = rtrim((string) (defined('APP_URL') ? APP_URL : ''), '/');

try {
    $pdo = db();
    $stV = $pdo->prepare('SELECT id, nom, slug, code_postal, description, image_url FROM villes WHERE slug = ? AND actif = 1 LIMIT 1');
    $stV->execute([$villeSlug]);
    $ville = $stV->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($ville && annuaire_ville_poi_table_ok($pdo)) {
        $stP = $pdo->prepare(
            'SELECT p.id, p.slug, p.name, p.description, p.featured_image, c.name AS cat_name, c.icon AS cat_icon, c.slug AS cat_slug
             FROM guide_pois p
             LEFT JOIN villes v1 ON v1.id = p.ville_id
             LEFT JOIN quartiers q ON q.id = p.quartier_id
             LEFT JOIN villes v2 ON v2.id = q.ville_id
             INNER JOIN guide_poi_categories c ON c.id = p.category_id AND c.is_active = 1
             WHERE p.is_active = 1 AND COALESCE(v1.slug, v2.slug) = ?
             ORDER BY c.sort_order ASC, c.name ASC, p.name ASC
             LIMIT 200'
        );
        $stP->execute([$villeSlug]);
        $pois = $stP->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Throwable $e) {
    error_log('[annuaire-ville] ' . $e->getMessage());
    $err = 'Impossible de charger le guide pour le moment.';
}

if (!$ville) {
    http_response_code(404);
    if (is_file(ROOT_PATH . '/public/pages/404.php')) {
        require ROOT_PATH . '/public/pages/404.php';
    } else {
        echo '404';
    }
    return;
}

if (!function_exists('annuaire_ville_poi_table_ok')) {
    function annuaire_ville_poi_table_ok(PDO $pdo): bool
    {
        try {
            $pdo->query("SELECT 1 FROM guide_pois LIMIT 1");

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}

$nomVille  = (string) ($ville['nom'] ?? '');
$intro     = trim((string) ($ville['description'] ?? ''));
$cover     = trim((string) ($ville['image_url'] ?? ''));
if ($cover !== '' && $cover[0] === '/') {
    $cover = $baseU . $cover;
}
$cp = (string) ($ville['code_postal'] ?? '');

$pageTitle = 'Commerces &amp; artisans à ' . $nomVille;
$metaDesc  = $intro !== ''
    ? (mb_strlen($intro, 'UTF-8') > 160 ? mb_substr($intro, 0, 157, 'UTF-8') . '…' : $intro)
    : 'Annuaire des commerces et services à ' . $nomVille . ($cp !== '' ? ' (' . $cp . ')' : '') . ' : repères locaux pour votre installation.';

function excerptPoiDesc(?string $html, int $len = 140): string
{
    $s = trim(strip_tags((string) $html));
    if ($s === '') {
        return '';
    }
    if (mb_strlen($s, 'UTF-8') > $len) {
        return mb_substr($s, 0, $len, 'UTF-8') . '…';
    }

    return $s;
}

$pubBase = $baseU . '/commerces/' . rawurlencode($villeSlug) . '/';
$guideHub = $baseU . '/guide-local/annuaire/';

?>
<section class="section" style="padding-top:0">
    <div class="container" style="max-width:1000px">
        <nav style="font-size:14px;color:#64748b;margin:20px 0 16px">
            <a href="/">Accueil</a>
            <span style="margin:0 6px">/</span>
            <a href="/guide-local">Guide local</a>
            <span style="margin:0 6px">/</span>
            <span style="color:#0f172a"><?= e($nomVille) ?></span>
        </nav>

        <header class="gla-hero" style="border-radius:16px;overflow:hidden;border:1px solid #e5e7eb;margin-bottom:28px;background:#fff;box-shadow:0 2px 12px rgba(15,23,42,.06)">
            <div style="display:grid;grid-template-columns:1fr;gap:0">
                <?php if ($cover !== ''): ?>
                <div style="aspect-ratio:2.2/1;min-height:180px;background:#e2e8f0">
                    <img src="<?= e($cover) ?>" alt="" style="width:100%;height:100%;object-fit:cover" loading="eager" decoding="async" width="1000" height="450">
                </div>
                <?php else: ?>
                <div style="min-height:140px;background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);"></div>
                <?php endif; ?>
                <div style="padding:24px 22px 26px">
                    <span class="section-label" style="display:block;margin-bottom:6px">Annuaire local</span>
                    <h1 style="margin:0 0 10px;font-size:clamp(1.4rem,3vw,1.85rem);color:#0f172a"><?= e($nomVille) ?><?= $cp !== '' ? ' <span style="color:#64748b;font-weight:500">(' . e($cp) . ')</span>' : '' ?></h1>
                    <?php if ($intro !== ''): ?>
                        <div class="prose" style="font-size:1rem;line-height:1.65;color:#334155;max-width:65ch">
                            <?php echo nl2br(e($intro)); ?>
                        </div>
                    <?php else: ?>
                        <p style="margin:0;font-size:15px;color:#64748b;max-width:60ch">Découvrez les commerçants et services repérés autour de <?= e($nomVille) ?>. Les fiches ci-dessous renvoient vers des pages dédiées (photo, contact, itinéraire).</p>
                    <?php endif; ?>
                    <p style="margin:16px 0 0">
                        <a class="btn btn--outline" href="/guide-local">Tous les guides</a>
                        <a class="btn btn--primary" href="/guide-local#map-section">Carte interactive</a>
                    </p>
                </div>
            </div>
        </header>

        <?php if ($err !== ''): ?>
            <p style="color:#b91c1c;font-size:15px"><?= e($err) ?></p>
        <?php endif; ?>

        <div class="section__header" style="text-align:left;margin-bottom:1rem">
            <span class="section-label">Commerces &amp; services</span>
            <h2 class="section-title" style="font-size:1.25rem">Liste</h2>
            <p class="section-subtitle" style="max-width:50rem">Chaque fiche s’ouvre sur le site avec visuel, description et liens (téléphone, itinéraire, site).</p>
        </div>

        <?php if ($pois === [] && $err === ''): ?>
            <p style="padding:20px;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b">Aucune fiche publiée pour <?= e($nomVille) ?> pour l’instant. Revenez bientôt ou <a href="/contact">contactez le conseiller</a>.</p>
        <?php else: ?>
        <ul class="gla-list" style="list-style:none;margin:0 0 40px;padding:0;display:grid;grid-template-columns:1fr;gap:16px">
            <?php foreach ($pois as $p):
                $imgP = (string) ($p['featured_image'] ?? '');
                if ($imgP !== '' && $imgP[0] === '/') {
                    $imgP = $baseU . $imgP;
                }
                $ex = excerptPoiDesc($p['description'] ?? null, 150);
                $ficheUrl = '/commerces/' . rawurlencode($villeSlug) . '/' . rawurlencode((string) $p['slug']);
                ?>
                <li>
                    <a href="<?= e($ficheUrl) ?>" class="gla-card" style="display:grid;grid-template-columns:120px 1fr;gap:16px;align-items:start;text-decoration:none;color:inherit;padding:16px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;transition:box-shadow .15s;border-color:#e2e8f0"
                       onmouseover="this.style.boxShadow='0 4px 18px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
                        <div style="width:120px;height:100px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#f1f5f9">
                            <?php if ($imgP !== ''): ?>
                                <img src="<?= e($imgP) ?>" alt="" width="120" height="100" style="width:100%;height:100%;object-fit:cover" loading="lazy" decoding="async">
                            <?php else: ?>
                                <div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:32px;opacity:0.35" aria-hidden="true">
                                    <i class="fas <?= e((string) ($p['cat_icon'] ?: 'fa-store')) ?>"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p style="margin:0 0 4px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#2563eb">
                                <?= e((string) ($p['cat_name'] ?? 'Commerce')) ?>
                            </p>
                            <h3 style="margin:0 0 6px;font-size:1.1rem;color:#0f172a"><?= e((string) $p['name']) ?></h3>
                            <?php if ($ex !== ''): ?>
                                <p style="margin:0 0 8px;font-size:14px;color:#64748b;line-height:1.5"><?= e($ex) ?></p>
                            <?php endif; ?>
                            <span class="btn btn--primary" style="display:inline-block;padding:6px 14px;font-size:14px">Voir la fiche</span>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</section>
<script type="application/ld+json">
<?php
$jsonLd = [
    '@context'     => 'https://schema.org',
    '@type'        => 'CollectionPage',
    'name'         => 'Annuaire ' . $nomVille,
    'description'  => strip_tags($metaDesc),
    'url'          => $baseU . '/guide-local/annuaire/' . rawurlencode($villeSlug),
    'isPartOf'     => ['@type' => 'WebSite', 'name' => (defined('APP_NAME') ? APP_NAME : ''), 'url' => $baseU . '/'],
    'hasPart'      => [],
];
foreach ($pois as $p) {
    $jsonLd['hasPart'][] = [
        '@type' => 'ListItem',
        'name'  => (string) $p['name'],
        'url'   => $baseU . '/commerces/' . rawurlencode($villeSlug) . '/' . rawurlencode((string) $p['slug']),
    ];
}
echo json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>
<?php
/* Annuler une éventuelle ancre "map-section" : ajoutée en dur sur l’index plus bas — id cohérent côté index. */
