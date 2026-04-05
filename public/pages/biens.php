<?php
$pageTitle = 'Annonces immobilières à Bordeaux — Eduardo Desul';
$metaDesc  = 'Découvrez toutes les annonces immobilières de Eduardo Desul à Bordeaux et alentours. Appartements, maisons, terrains.';
$extraCss  = ['/assets/css/biens.css'];
$extraJs   = ['/assets/js/biens.js'];

$cmsHelper = ROOT_PATH . '/core/helpers/cms.php';
if (file_exists($cmsHelper)) {
    require_once $cmsHelper;
}

$filters = function_exists('get_page_content')
    ? (get_page_content('biens', 'filters') ?: [])
    : [];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a><span>Biens immobiliers</span>
        </nav>
        <h1><?= e($filters['title'] ?? 'Trouvez votre bien idéal') ?></h1>
        <p><?= e($filters['default_text'] ?? 'Utilisez les filtres ci-dessous pour affiner votre recherche.') ?></p>
    </div>
</div>

<section class="section">
    <div class="container">

        <!-- Filtres -->
        <form id="filter-form" method="GET" action="/biens" class="biens-filters">
            <div class="filters-row">
                <div class="filter-group">
                    <div class="filter-label">Transaction</div>
                    <select name="type" class="filter-select">
                        <option value="">Toutes</option>
                        <option value="vente" <?= ($_GET['type'] ?? '') === 'vente' ? 'selected' : '' ?>>Vente</option>
                        <option value="location" <?= ($_GET['type'] ?? '') === 'location' ? 'selected' : '' ?>>Location</option>
                    </select>
                </div>
                <div class="filter-group">
                    <div class="filter-label">Type de bien</div>
                    <select name="bien" class="filter-select">
                        <option value="">Tous les biens</option>
                        <option value="appartement">Appartement</option>
                        <option value="maison">Maison</option>
                        <option value="terrain">Terrain</option>
                        <option value="local">Local commercial</option>
                    </select>
                </div>
                <div class="filter-group">
                    <div class="filter-label">Secteur</div>
                    <select name="secteur" class="filter-select">
                        <option value="">Tous les secteurs</option>
                        <option value="bordeaux">Bordeaux</option>
                        <option value="merignac">Mérignac</option>
                        <option value="pessac">Pessac</option>
                        <option value="talence">Talence</option>
                        <option value="begles">Bègles</option>
                    </select>
                </div>
                <div class="filter-group">
                    <div class="filter-label">Budget max</div>
                    <select name="budget" class="filter-select">
                        <option value="">Sans limite</option>
                        <option value="200000">200 000 €</option>
                        <option value="300000">300 000 €</option>
                        <option value="400000">400 000 €</option>
                        <option value="500000">500 000 €</option>
                        <option value="750000">750 000 €</option>
                        <option value="1000000">1 000 000 €</option>
                    </select>
                </div>
                <div class="filter-group">
                    <div class="filter-label">Pièces min.</div>
                    <select name="pieces" class="filter-select">
                        <option value="">Toutes</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="filter-group filter-btn">
                    <button type="submit" class="btn btn--primary">🔍 Filtrer</button>
                </div>
            </div>
        </form>

        <!-- Résultats -->
        <div class="biens-count">Affichage de <strong>4</strong> biens sur <strong>4</strong></div>

        <div class="biens-grid">
            <?php
            // Données de démonstration — sélection Eduardo De Sul
            $biens = [
                [
                    'id' => 1,
                    'titre' => 'Maison de caractère avec jardin - Bordeaux Centre',
                    'type' => 'Vente',
                    'typeBien' => 'maison',
                    'prix' => '895 000',
                    'loc' => 'Bordeaux Chartrons',
                    'surface' => 185,
                    'pieces' => 7,
                    'img' => '/assets/images/bien-2.jpg',
                    'badge' => 'exclusif',
                ],
                [
                    'id' => 2,
                    'titre' => 'Appartement contemporain avec vue mer - Arcachon Centre',
                    'type' => 'Vente',
                    'typeBien' => 'appartement',
                    'prix' => '980 000',
                    'loc' => 'Arcachon Le Moulleau',
                    'surface' => 120,
                    'pieces' => 6,
                    'img' => '/assets/images/bien-1.jpg',
                    'badge' => 'vente',
                ],
                [
                    'id' => 3,
                    'titre' => 'Villa contemporaine avec piscine - Saint-Émilion',
                    'type' => 'Vente',
                    'typeBien' => 'maison',
                    'prix' => '2 450 000',
                    'loc' => 'Saint-Émilion',
                    'surface' => 220,
                    'pieces' => 10,
                    'img' => '/assets/images/bien-6.jpg',
                    'badge' => 'exclusif',
                ],
                [
                    'id' => 4,
                    'titre' => 'Terrain constructible vue océan - Cap Ferret',
                    'type' => 'Vente',
                    'typeBien' => 'terrain',
                    'prix' => '1 250 000',
                    'loc' => 'Lège-Cap Ferret',
                    'surface' => 1200,
                    'pieces' => 0,
                    'img' => '/assets/images/bien-4.jpg',
                    'badge' => 'vente',
                ],
            ];
            foreach ($biens as $b):
                $imgFile = defined('PUBLIC_PATH') ? PUBLIC_PATH . $b['img'] : __DIR__ . '/..' . $b['img'];
                if (file_exists($imgFile)) {
                    $imgSrc = e($b['img']);
                } else {
                    $imgSrc = '/assets/images/placeholder.php?type=' . urlencode($b['typeBien'])
                            . '&pieces=' . $b['pieces']
                            . '&surface=' . $b['surface']
                            . '&label=' . urlencode($b['type']);
                }
            ?>
            <article class="bien-card">
                <a href="/biens/bien-<?= $b['id'] ?>" class="bien-card__img" tabindex="-1" aria-hidden="true">
                    <img src="<?= $imgSrc ?>" alt="<?= e($b['titre']) ?>" loading="lazy" width="400" height="300">
                    <span class="bien-card__badge badge--<?= e($b['badge']) ?>"><?= e($b['type']) ?></span>
                </a>
                <div class="bien-card__body">
                    <div class="bien-card__prix"><?= e($b['prix']) ?> €</div>
                    <h2 class="bien-card__titre">
                        <a href="/biens/bien-<?= $b['id'] ?>"><?= e($b['titre']) ?></a>
                    </h2>
                    <p class="bien-card__loc"><?= e($b['loc']) ?></p>
                    <div class="bien-card__specs">
                        <span class="spec-item">📐 <?= $b['surface'] ?> m²</span>
                        <?php if (($b['pieces'] ?? 0) > 0): ?>
                            <span class="spec-item">🚪 <?= $b['pieces'] ?> pièces</span>
                        <?php else: ?>
                            <span class="spec-item">🌿 Terrain constructible</span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav class="pagination" aria-label="Pagination">
            <a href="?page=1" class="page-btn active" aria-current="page">1</a>
        </nav>

    </div>
</section>

<!-- CTA -->
<section class="cta-banner">
    <div class="container">
        <h2>Vous ne trouvez pas votre bonheur ?</h2>
        <p>Décrivez votre projet et Eduardo recherche pour vous parmi ses contacts et son réseau.</p>
        <div class="cta-banner__actions">
            <a href="/contact" class="btn btn--accent btn--lg">Décrire mon projet</a>
        </div>
    </div>
</section>
