<?php
$slug      = $slug ?? 'bordeaux-chartrons';
$pageTitle = 'Guide du quartier — Eduardo Desul Immobilier';
$metaDesc  = '';
$extraCss  = ['/assets/css/guide.css', '/assets/css/biens.css'];

// Données de démonstration
$ville = [
    'nom'   => 'Les Chartrons',
    'prix'  => '4 600',
    'desc'  => 'Quartier historique des marchands de vin, les Chartrons sont aujourd\'hui l\'un des secteurs les plus prisés de Bordeaux. Mêlant patrimoine architectural et vie de quartier animée, il attire aussi bien les familles que les jeunes actifs.',
    'img'   => '/assets/images/chartrons.jpg',
    'biens' => 8,
];
$pageTitle = 'Immobilier ' . $ville['nom'] . ' — Eduardo Desul';
$metaDesc  = 'Guide immobilier ' . $ville['nom'] . ' : prix au m², tendances, quartier. Par Eduardo Desul, expert local.';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a>
            <a href="/guide-local">Guide local</a>
            <span><?= e($ville['nom']) ?></span>
        </nav>
        <h1>Immobilier <?= e($ville['nom']) ?></h1>
        <p><?= e(mb_strimwidth($ville['desc'], 0, 120, '…')) ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="article-layout">
            <div>
                <div class="article-cover"><img src="<?= e($ville['img']) ?>" alt="<?= e($ville['nom']) ?>" width="800" height="400"></div>

                <!-- Stats quartier -->
                <div class="grid-4" style="margin-bottom:2.5rem" data-animate>
                    <?php foreach ([
                        ['💰', $ville['prix'] . ' €/m²', 'Prix médian'],
                        ['📈', '↗ +3%', 'Tendance 12 mois'],
                        ['⏱', '45 jours', 'Délai de vente moyen'],
                        ['🏠', $ville['biens'], 'Biens disponibles'],
                    ] as [$icon, $val, $lab]): ?>
                    <div style="background:var(--clr-white);border-radius:var(--radius-lg);border:1px solid var(--clr-border);padding:1.25rem;text-align:center">
                        <div style="font-size:1.5rem"><?= $icon ?></div>
                        <div style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--clr-primary)"><?= $val ?></div>
                        <div style="font-size:.75rem;color:var(--clr-text-muted)"><?= $lab ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="article-content">
                    <h2>Présentation du quartier</h2>
                    <p><?= e($ville['desc']) ?></p>
                    <h2>Le marché immobilier</h2>
                    <p>Les prix immobiliers aux Chartrons ont connu une légère correction en 2024-2025 (-5 à -8%), mais restent parmi les plus élevés de Bordeaux. La demande est soutenue, notamment pour les appartements avec caractère (moulures, parquet, hauteur sous plafond).</p>
                    <h2>Transports & accessibilité</h2>
                    <p>Excellente desserte par le tramway (lignes A et B). Le quartier est également très cyclable, avec de nombreuses pistes aménagées. Parking difficile mais peu nécessaire.</p>
                    <h2>Commerces & services</h2>
                    <p>Le quartier dispose de tous les commerces du quotidien, marchés hebdomadaires, nombreux restaurants et cafés. Plusieurs écoles publiques et privées à proximité.</p>
                </div>

                <div style="margin-top:2rem">
                    <h2>Biens disponibles aux <?= e($ville['nom']) ?></h2>
                    <a href="/biens?secteur=<?= e($slug) ?>" class="btn btn--primary" style="margin-top:1rem">Voir les <?= $ville['biens'] ?> annonces →</a>
                </div>
            </div>

            <aside class="blog-sidebar">
                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem">
                    <h4 style="color:white;margin-bottom:.75rem">Estimer votre bien ici</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Vous avez un bien aux <?= e($ville['nom']) ?> ? Obtenez une estimation gratuite.</p>
                    <a href="/estimation-gratuite?secteur=<?= e($slug) ?>" class="btn btn--accent btn--sm btn--full">Estimation gratuite</a>
                </div>
                <div class="sidebar-box">
                    <div class="sidebar-box__head">Autres quartiers</div>
                    <div class="sidebar-box__body">
                        <?php foreach ([['Bordeaux Centre', 'bordeaux-centre'], ['Cauderan', 'bordeaux-cauderan'], ['Mérignac', 'merignac'], ['Pessac', 'pessac']] as [$n, $s]): ?>
                        <a href="/guide-local/<?= e($s) ?>" style="display:block;padding:.5rem 0;font-size:.875rem;border-bottom:1px solid var(--clr-border);color:var(--clr-text)">📍 <?= e($n) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
