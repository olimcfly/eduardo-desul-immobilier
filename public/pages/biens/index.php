<?php
$pageTitle = 'Nos biens immobiliers à ' . APP_CITY . ' — ' . ADVISOR_NAME;
$metaDesc = 'Découvrez notre sélection exclusive de biens immobiliers à Bordeaux et dans la Métropole bordelaise.';
$extraCss = ['/assets/css/style.css', '/assets/css/biens.css'];

require_once __DIR__ . '/../../../core/Database.php';

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Service indisponible.");
}

// ✅ RÉCUPÉRATION DES BIENS
$biens = [];
$searchQuery = trim((string) ($_GET['query'] ?? ''));
$searchType = strtolower(trim((string) ($_GET['type'] ?? '')));
$searchBudget = trim((string) ($_GET['budget'] ?? ''));
$filtersApplied = $searchQuery !== '' || $searchType !== '' || $searchBudget !== '';

$allowedTypes = [
    'bastide' => 'bastide',
    'villa' => 'villa',
    'viager' => 'viager',
];
$normalizedType = $allowedTypes[$searchType] ?? '';

$minBudget = null;
$maxBudget = null;
if (preg_match('/^\d+\-\d+$/', $searchBudget) === 1) {
    [$minBudget, $maxBudget] = array_map('intval', explode('-', $searchBudget, 2));
    if ($minBudget > $maxBudget) {
        [$minBudget, $maxBudget] = [$maxBudget, $minBudget];
    }
}

try {
    $sql = "SELECT id, slug, titre, type_transaction, type_bien, prix, surface, pieces, chambres, ville, secteur, photo_principale, statut
            FROM biens
            WHERE statut = 'actif'";

    $params = [];
    if ($searchQuery !== '') {
        $sql .= " AND (
            titre LIKE :query
            OR ville LIKE :query
            OR secteur LIKE :query
            OR description LIKE :query
        )";
        $params[':query'] = '%' . $searchQuery . '%';
    }

    if ($normalizedType !== '') {
        $sql .= " AND LOWER(type_bien) LIKE :type";
        $params[':type'] = '%' . $normalizedType . '%';
    }

    if ($minBudget !== null && $maxBudget !== null) {
        $sql .= " AND prix BETWEEN :min_budget AND :max_budget";
        $params[':min_budget'] = $minBudget;
        $params[':max_budget'] = $maxBudget;
    }

    $sql .= " ORDER BY sort_order ASC, created_at DESC LIMIT 50";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    if ($stmt !== false) {
        $biens = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Throwable $e) {
    $biens = [];
}

$nbBiensTotal = count($biens);
?>

<!-- HERO -->
<section class="hero hero--premium hero--biens">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92), rgba(15,38,68,.86)), url(‘/assets/images/hero-bg.jpg’);"></div>
    <div class="container">
        <div class="hero__content">
            <span class="section-label">Biens immobiliers</span>
            <h1>Maisons et appartements à Bordeaux</h1>
            <p class="hero__subtitle">
                Découvrez notre sélection de biens disponibles à la vente et à la location dans Bordeaux Métropole.
            </p>
            <div class="hero__stats">
                <div class="stat-item">
                    <span class="stat-value"><?= $nbBiensTotal ?></span>
                    <span class="stat-label">Biens disponibles</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GRID -->
<section class="section">
    <div class="container">
        <h1>Nos biens immobiliers</h1>
        <?php if ($filtersApplied): ?>
            <p>Résultats de votre recherche.</p>
        <?php endif; ?>
        <p class="biens-count"><?= count($biens) ?> bien(s) disponible(s)</p>

        <div class="biens-grid">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>

                    <article class="bien-card">

                        <div class="bien-card__header">

                            <?php
                                $photoSrc = (!empty($bien['photo_principale']) && strpos($bien['photo_principale'], 'default.jpg') === false)
                                    ? htmlspecialchars($bien['photo_principale'])
                                    : '/assets/images/placeholder.php?type=' . urlencode($bien['type_bien'] ?? 'bien') . '&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                            ?>
                            <?php
                                $placeholderFallback = '/assets/images/placeholder.php?type=' . urlencode($bien['type_bien'] ?? 'bien') . '&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                            ?>
                            <img class="bien-card__img"
                                 src="<?= $photoSrc ?>"
                                 alt="<?= htmlspecialchars($bien['titre'] ?? '') ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='<?= $placeholderFallback ?>'">

                            <?php $txType = $bien['type_transaction'] ?? ''; ?>
                            <span class="bien-badge <?= strtolower($txType) === 'vente' ? 'vente' : 'location' ?>">
                                <?= htmlspecialchars($txType) ?>
                            </span>

                        </div>

                        <div class="bien-card__body">

                            <h3 class="bien-card__title">
                                <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>">
                                    <?= htmlspecialchars($bien['titre']) ?>
                                </a>
                            </h3>

                            <div class="bien-card__location">
                                📍 <?= htmlspecialchars($bien['ville']) ?>
                            </div>

                            <div class="bien-card__specs">
                                <div class="spec-item">📐 <?= number_format($bien['surface'], 0) ?> m²</div>
                                <div class="spec-item">🛏️ <?= $bien['pieces'] ?> pièces</div>
                            </div>

                            <div class="bien-card__price">
                                <?= number_format($bien['prix'], 0, ',', ' ') ?> €
                                <?php if (($bien['type_transaction'] ?? '') === 'location'): ?>
                                    <span>/mois</span>
                                <?php endif; ?>
                            </div>

                            <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>" class="btn btn--primary" style="width:100%;">
                                Voir le bien
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:3rem;">
                    <p>Aucun bien disponible actuellement.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- CTA -->
<div class="container">
    <section class="cta-section">
        <h3>Vous ne trouvez pas votre bonheur ?</h3>
        <p>Nos conseillers peuvent vous proposer des biens exclusifs.</p>
        <div class="cta-buttons">
            <a href="/contact" class="btn btn--primary btn--lg">Nous contacter</a>
            <a href="tel:+33600000000" class="btn btn--outline-white btn--lg">Appeler</a>
        </div>
    </section>
</div>

<?php
?>
