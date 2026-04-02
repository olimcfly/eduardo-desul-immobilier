<?php
$pageTitle = 'Annonces immobilières à Bordeaux — Eduardo Desul';
$metaDesc  = 'Découvrez toutes les annonces immobilières de Eduardo Desul à Bordeaux et alentours. Appartements, maisons, terrains.';
$extraCss  = ['/assets/css/biens.css'];
$extraJs   = ['/assets/js/biens.js'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a><span>Biens immobiliers</span>
        </nav>
        <h1>Biens immobiliers</h1>
        <p>Découvrez notre sélection de biens à vendre et à louer à Bordeaux et dans le Bordelais.</p>
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
        <div class="biens-count">Affichage de <strong>12</strong> biens sur <strong>24</strong></div>

        <div class="biens-grid">
            <?php
            // Données de démonstration
            $biens = [
                ['id' => 1, 'titre' => 'Appartement T3 lumineux Chartrons', 'type' => 'Vente', 'prix' => '295 000', 'loc' => 'Bordeaux Chartrons', 'surface' => 72, 'pieces' => 3, 'img' => '/assets/images/placeholder-bien-1.jpg', 'badge' => 'vente'],
                ['id' => 2, 'titre' => 'Maison familiale avec jardin', 'type' => 'Vente', 'prix' => '485 000', 'loc' => 'Mérignac', 'surface' => 145, 'pieces' => 5, 'img' => '/assets/images/placeholder-bien-2.jpg', 'badge' => 'vente'],
                ['id' => 3, 'titre' => 'Studio meublé centre historique', 'type' => 'Location', 'prix' => '750 /mois', 'loc' => 'Bordeaux Centre', 'surface' => 28, 'pieces' => 1, 'img' => '/assets/images/placeholder-bien-3.jpg', 'badge' => 'location'],
                ['id' => 4, 'titre' => 'T2 avec balcon et parking', 'type' => 'Vente', 'prix' => '189 000', 'loc' => 'Talence', 'surface' => 48, 'pieces' => 2, 'img' => '/assets/images/placeholder-bien-1.jpg', 'badge' => 'vente'],
                ['id' => 5, 'titre' => 'Loft atypique Saint-Michel', 'type' => 'Vente', 'prix' => '320 000', 'loc' => 'Bordeaux Saint-Michel', 'surface' => 85, 'pieces' => 3, 'img' => '/assets/images/placeholder-bien-2.jpg', 'badge' => 'exclusif'],
                ['id' => 6, 'titre' => 'Maison de ville avec patio', 'type' => 'Vente', 'prix' => '560 000', 'loc' => 'Bordeaux Victoire', 'surface' => 120, 'pieces' => 4, 'img' => '/assets/images/placeholder-bien-3.jpg', 'badge' => 'vente'],
            ];
            foreach ($biens as $b): ?>
            <article class="bien-card">
                <a href="/biens/bien-<?= $b['id'] ?>" class="bien-card__img" tabindex="-1" aria-hidden="true">
                    <img src="<?= e($b['img']) ?>" alt="<?= e($b['titre']) ?>" loading="lazy" width="400" height="300">
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
                        <span class="spec-item">🚪 <?= $b['pieces'] ?> pièces</span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav class="pagination" aria-label="Pagination">
            <a href="?page=1" class="page-btn active" aria-current="page">1</a>
            <a href="?page=2" class="page-btn">2</a>
            <a href="?page=3" class="page-btn">3</a>
            <a href="?page=2" class="page-btn">→</a>
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
