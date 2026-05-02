<?php
$pageTitle    = 'Secteurs immobiliers à Bordeaux Métropole | Eduardo Desul';
$metaDesc     = 'Découvrez les secteurs d’intervention d’Eduardo Desul à Bordeaux, Mérignac, Pessac, Talence et dans la métropole bordelaise.';
$metaKeywords = 'secteurs immobiliers, expert immobilier ' . APP_CITY . ', conseiller immobilier indépendant';
$extraCss     = ['/assets/css/villes.css'];
?>

<section class="hero hero--primary" aria-labelledby="secteurs-hero-title" style="padding-block: 1.5rem;">
    <div class="container">
        <div class="hero__content" style="max-width:500px">
            <span class="section-label" style="color:rgba(255,255,255,0.8);font-size:0.7rem;">Secteurs</span>
            <h1 id="secteurs-hero-title" style="color:white;font-size:1.75rem;margin-bottom:0.5rem;">Secteurs couverts</h1>
            <p class="hero__subtitle" style="color:rgba(255,255,255,0.85);font-size:0.95rem;">Expertise locale dans toute la région de Bordeaux et la Métropole bordelaise.</p>
            <p style="margin-top:1rem"><a href="#reference-secteurs" class="btn btn--outline btn--outline-white" style="font-size:.85rem">Listes commune / slug (copier)</a></p>
        </div>
    </div>
</section>

<!-- Villes -->
<section class="section" aria-labelledby="villes-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Villes</span>
            <h2 id="villes-title" class="section-title">Villes couvertes</h2>
            <p class="section-subtitle"><?= ADVISOR_NAME ?> intervient sur l'ensemble des communes de la zone avec une connaissance approfondie de chaque secteur.</p>
        </div>
        <div class="cities-grid">
            <a href="<?= url('/immobilier/bordeaux') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Bordeaux" loading="lazy">
                <div class="city-card__body">
                    <h3>Bordeaux</h3>
                    <p class="city-card__desc">Capitale girondine, patrimoine UNESCO, dynamisme économique et qualité de vie — cœur du marché.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/merignac') ?>" class="city-card">
                <img src="/assets/images/merignac.jpg" alt="Mérignac" loading="lazy">
                <div class="city-card__body">
                    <h3>Mérignac</h3>
                    <p class="city-card__desc">Ville aéroportuaire, résidentielle, familles, zones d’activités (Aéroparc).</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/talence') ?>" class="city-card">
                <img src="/assets/images/talence.jpg" alt="Talence" loading="lazy">
                <div class="city-card__body">
                    <h3>Talence</h3>
                    <p class="city-card__desc">Ville étudiante (campus universitaire), mixité résidentielle et étudiante.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/pessac') ?>" class="city-card">
                <img src="/assets/images/pessac.jpg" alt="Pessac" loading="lazy">
                <div class="city-card__body">
                    <h3>Pessac</h3>
                    <p class="city-card__desc">Proche de Bordeaux, zones pavillonnaires, technopole (Inria, université).</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/floirac') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Floirac" loading="lazy">
                <div class="city-card__body">
                    <h3>Floirac</h3>
                    <p class="city-card__desc">Vue sur Bordeaux, parc des sports, mixité sociale.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/lormont') ?>" class="city-card">
                <img src="/assets/images/chartrons.jpg" alt="Lormont" loading="lazy">
                <div class="city-card__body">
                    <h3>Lormont</h3>
                    <p class="city-card__desc">Quartiers populaires et résidentiels, proximité de la Garonne.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/eysines') ?>" class="city-card">
                <img src="/assets/images/cauderan.jpg" alt="Eysines" loading="lazy">
                <div class="city-card__body">
                    <h3>Eysines</h3>
                    <p class="city-card__desc">Ville verte, familles, zones commerciales (Rives d’Arcins).</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/saint-medard') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Saint-Médard-en-Jalles" loading="lazy">
                <div class="city-card__body">
                    <h3>Saint-Médard-en-Jalles</h3>
                    <p class="city-card__desc">Ville nature, base aérienne 106, résidences calmes.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/villenave-dornon') ?>" class="city-card">
                <img src="/assets/images/chartrons.jpg" alt="Villenave-d'Ornon" loading="lazy">
                <div class="city-card__body">
                    <h3>Villenave-d'Ornon</h3>
                    <p class="city-card__desc">Proche de Bordeaux, zones viticoles, mixité urbaine et rurale.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/bouliac') ?>" class="city-card">
                <img src="/assets/images/pessac.jpg" alt="Bouliac" loading="lazy">
                <div class="city-card__body">
                    <h3>Bouliac</h3>
                    <p class="city-card__desc">Village chic, vue sur Bordeaux, restauration de qualité.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/carbon-blanc') ?>" class="city-card">
                <img src="/assets/images/talence.jpg" alt="Carbon-Blanc" loading="lazy">
                <div class="city-card__body">
                    <h3>Carbon-Blanc</h3>
                    <p class="city-card__desc">Petite ville résidentielle, proximité de Lormont et Bordeaux.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/immobilier/blanquefort') ?>" class="city-card">
                <img src="/assets/images/cauderan.jpg" alt="Blanquefort" loading="lazy">
                <div class="city-card__body">
                    <h3>Blanquefort</h3>
                    <p class="city-card__desc">Ville historique, château, zones industrielles (Ford), familles.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Quartiers Bordeaux -->
<section class="section section--alt" aria-labelledby="quartiers-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Quartiers de Bordeaux</span>
            <h2 id="quartiers-title" class="section-title">Quartiers couverts</h2>
            <p class="section-subtitle">Fiches : <code>/secteurs/quartiers/[slug]</code> ou <code>/quartier/[slug]</code>. Les onze quartiers du menu sont listés ci-dessous ; d’autres fiches complètent la couverture.</p>
        </div>
        <div class="cities-grid">
            <a href="<?= url('/quartier/chartrons') ?>" class="city-card">
                <img src="/assets/images/chartrons.jpg" alt="Chartrons" loading="lazy">
                <div class="city-card__body">
                    <h3>Chartrons</h3>
                    <p class="city-card__desc">Négociants en vin, galeries, marché — ambiance village.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/cauderan') ?>" class="city-card">
                <img src="/assets/images/cauderan.jpg" alt="Caudéran" loading="lazy">
                <div class="city-card__body">
                    <h3>Caudéran</h3>
                    <p class="city-card__desc">Résidentiel chic, villas, proche du Jardin Public.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/belcier') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Belcier" loading="lazy">
                <div class="city-card__body">
                    <h3>Belcier</h3>
                    <p class="city-card__desc">Euratlantique : rénovation, mixité bureaux et logements.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/saint-augustin') ?>" class="city-card">
                <img src="/assets/images/pessac.jpg" alt="Saint-Augustin" loading="lazy">
                <div class="city-card__body">
                    <h3>Saint-Augustin</h3>
                    <p class="city-card__desc">Proche gare, ambiance village, écoles réputées.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/bacalan') ?>" class="city-card">
                <img src="/assets/images/talence.jpg" alt="Bacalan" loading="lazy">
                <div class="city-card__body">
                    <h3>Bacalan</h3>
                    <p class="city-card__desc">Portuaire en développement, Bassins à Flot, lofts.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/capucins') ?>" class="city-card">
                <img src="/assets/images/merignac.jpg" alt="Capucins" loading="lazy">
                <div class="city-card__body">
                    <h3>Capucins</h3>
                    <p class="city-card__desc">Marché couvert, ambiance populaire, gare Saint-Jean.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/saint-michel') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Saint-Michel" loading="lazy">
                <div class="city-card__body">
                    <h3>Saint-Michel</h3>
                    <p class="city-card__desc">Multiculturel, Capucins, vie nocturne.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/bastide') ?>" class="city-card">
                <img src="/assets/images/chartrons.jpg" alt="La Bastide" loading="lazy">
                <div class="city-card__body">
                    <h3>La Bastide</h3>
                    <p class="city-card__desc">Rive droite, vue sur Bordeaux, Darwin, familles.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/saint-seurin') ?>" class="city-card">
                <img src="/assets/images/cauderan.jpg" alt="Saint-Seurin" loading="lazy">
                <div class="city-card__body">
                    <h3>Saint-Seurin</h3>
                    <p class="city-card__desc">Basilique, calme, proche du centre.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/nansouty') ?>" class="city-card">
                <img src="/assets/images/pessac.jpg" alt="Nansouty" loading="lazy">
                <div class="city-card__body">
                    <h3>Nansouty</h3>
                    <p class="city-card__desc">Résidentiel, Chaban-Delmas, écoles.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/bordeaux-centre') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Bordeaux centre" loading="lazy">
                <div class="city-card__body">
                    <h3>Bordeaux centre</h3>
                    <p class="city-card__desc">Cœur historique, Miroir d’eau, commerce et tourisme.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
        </div>

        <div class="section__header" style="margin-top:2.5rem">
            <span class="section-label">Autres quartiers</span>
            <h3 class="section-title" style="font-size:1.25rem">Fiches complémentaires</h3>
            <p class="section-subtitle">L’URL <code>/quartier/centre-ville</code> redirige en 301 vers <code>bordeaux-centre</code>.</p>
        </div>
        <div class="cities-grid">
            <a href="<?= url('/quartier/bordeaux-centre') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Centre-ville" loading="lazy">
                <div class="city-card__body">
                    <h3>Centre-ville → Bordeaux centre</h3>
                    <p class="city-card__desc">Fusion éditoriale : même fiche que Bordeaux centre.</p>
                    <span class="city-card__cta">Voir la fiche →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/puyricard') ?>" class="city-card">
                <img src="/assets/images/cauderan.jpg" alt="Puyricard" loading="lazy">
                <div class="city-card__body">
                    <h3>Puyricard</h3>
                    <p class="city-card__desc">Résidentiel, proche Caudéran, villas.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/mazarin') ?>" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Mazarin" loading="lazy">
                <div class="city-card__body">
                    <h3>Mazarin</h3>
                    <p class="city-card__desc">Proche centre, hôtels particuliers.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/jas-de-bouffan') ?>" class="city-card">
                <img src="/assets/images/pessac.jpg" alt="Jas-de-Bouffan" loading="lazy">
                <div class="city-card__body">
                    <h3>Jas-de-Bouffan</h3>
                    <p class="city-card__desc">Résidentiel, proche Mérignac, familles.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/luynes') ?>" class="city-card">
                <img src="/assets/images/talence.jpg" alt="Luynes" loading="lazy">
                <div class="city-card__body">
                    <h3>Luynes</h3>
                    <p class="city-card__desc">Proche Talence, village, écoles.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/les-milles') ?>" class="city-card">
                <img src="/assets/images/talence.jpg" alt="Les Milles" loading="lazy">
                <div class="city-card__body">
                    <h3>Les Milles</h3>
                    <p class="city-card__desc">Zone activités ; vérifier le périmètre (homonyme connu ailleurs).</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Référence éditoriale (copier-coller) -->
<section class="section" id="reference-secteurs" aria-labelledby="ref-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Référence</span>
            <h2 id="ref-title" class="section-title">Listes Commune / Slug / accroche</h2>
            <p class="section-subtitle">Villes : <code>/secteurs/villes/[slug]</code> ou <code>/immobilier/[slug]</code> — quartiers : <code>/secteurs/quartiers/[slug]</code> ou <code>/quartier/[slug]</code>.</p>
        </div>

        <div class="secteurs-ref-block" style="margin-bottom:2rem">
            <h3 class="section-title" style="font-size:1.1rem;margin-bottom:.75rem">📌 Villes (12)</h3>
            <p style="margin:0 0 .75rem">
                <button type="button" class="btn btn--outline js-copy-table" data-target="table-villes-ref">Copier le tableau</button>
            </p>
            <div style="overflow-x:auto;border:1px solid var(--clr-border);border-radius:var(--radius-lg)">
                <table class="secteurs-ref-table" id="table-villes-ref" style="width:100%;border-collapse:collapse;font-size:.875rem">
                    <thead>
                    <tr style="background:var(--clr-bg-alt,#f8fafc)">
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Commune</th>
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Slug</th>
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Accroche</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $villesRef = [
                        ['Bordeaux', 'bordeaux', 'Métropole, patrimoine UNESCO, cœur du marché.'],
                        ['Mérignac', 'merignac', 'Ville aéroportuaire, résidentielle, familles, zones d’activités (Aéroparc).'],
                        ['Talence', 'talence', 'Ville étudiante (campus), mixité résidentielle / étudiante.'],
                        ['Pessac', 'pessac', 'Proche Bordeaux, pavillonnaire, technopole (Inria, université).'],
                        ['Floirac', 'floirac', 'Vue sur Bordeaux, parc des sports, mixité sociale.'],
                        ['Lormont', 'lormont', 'Populaire et résidentiel, proximité Garonne.'],
                        ['Eysines', 'eysines', 'Ville verte, familles, commerces (Rives d’Arcins).'],
                        ['Saint-Médard-en-Jalles', 'saint-medard', 'Nature, base aérienne 106, résidences calmes.'],
                        ['Villenave-d’Ornon', 'villenave-dornon', 'Proche Bordeaux, viticole, mixité urbaine / rurale.'],
                        ['Bouliac', 'bouliac', 'Village chic, vue Bordeaux, restauration.'],
                        ['Carbon-Blanc', 'carbon-blanc', 'Résidentiel, proximité Lormont et Bordeaux.'],
                        ['Blanquefort', 'blanquefort', 'Historique, château, zones industrielles (Ford), familles.'],
                    ];
                    foreach ($villesRef as $row): ?>
                        <tr>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><code><?= htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><?= htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="secteurs-ref-block" style="margin-bottom:2rem">
            <h3 class="section-title" style="font-size:1.1rem;margin-bottom:.75rem">📌 Quartiers menu (11)</h3>
            <p style="margin:0 0 .75rem">
                <button type="button" class="btn btn--outline js-copy-table" data-target="table-q-menu-ref">Copier le tableau</button>
            </p>
            <div style="overflow-x:auto;border:1px solid var(--clr-border);border-radius:var(--radius-lg)">
                <table class="secteurs-ref-table" id="table-q-menu-ref" style="width:100%;border-collapse:collapse;font-size:.875rem">
                    <thead>
                    <tr style="background:var(--clr-bg-alt,#f8fafc)">
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Quartier</th>
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Slug</th>
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Accroche</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $qMenu = [
                        ['Chartrons', 'chartrons', 'Négociants en vin, galeries, marché.'],
                        ['Caudéran', 'cauderan', 'Chic résidentiel, villas, Jardin Public.'],
                        ['Belcier', 'belcier', 'Euratlantique, bureaux / logements.'],
                        ['Saint-Augustin', 'saint-augustin', 'Gare, village, écoles.'],
                        ['Bacalan', 'bacalan', 'Bassins à Flot, lofts.'],
                        ['Capucins', 'capucins', 'Marché, populaire, gare.'],
                        ['Saint-Michel', 'saint-michel', 'Multiculturel, vie nocturne.'],
                        ['La Bastide', 'bastide', 'Rive droite, Darwin, familles.'],
                        ['Saint-Seurin', 'saint-seurin', 'Basilique, calme, centre.'],
                        ['Nansouty', 'nansouty', 'Résidentiel, Chaban, écoles.'],
                        ['Bordeaux centre', 'bordeaux-centre', 'Cœur historique, Miroir d’eau.'],
                    ];
                    foreach ($qMenu as $row): ?>
                        <tr>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><code><?= htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><?= htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="secteurs-ref-block">
            <h3 class="section-title" style="font-size:1.1rem;margin-bottom:.75rem">📌 Autres quartiers (fiches)</h3>
            <p style="margin:0 0 .75rem">
                <button type="button" class="btn btn--outline js-copy-table" data-target="table-q-autres-ref">Copier le tableau</button>
            </p>
            <div style="overflow-x:auto;border:1px solid var(--clr-border);border-radius:var(--radius-lg)">
                <table class="secteurs-ref-table" id="table-q-autres-ref" style="width:100%;border-collapse:collapse;font-size:.875rem">
                    <thead>
                    <tr style="background:var(--clr-bg-alt,#f8fafc)">
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Quartier</th>
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Slug</th>
                        <th style="text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--clr-border)">Note</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $qAutres = [
                        ['Centre-ville', 'centre-ville', '301 → bordeaux-centre.'],
                        ['Puyricard', 'puyricard', 'Résidentiel, proche Caudéran.'],
                        ['Mazarin', 'mazarin', 'Centre, hôtels particuliers.'],
                        ['Jas-de-Bouffan', 'jas-de-bouffan', 'Résidentiel, proche Mérignac.'],
                        ['Luynes', 'luynes', 'Proche Talence, village.'],
                        ['Les Milles', 'les-milles', 'Secteur à valider localement avant publication.'],
                    ];
                    foreach ($qAutres as $row): ?>
                        <tr>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><code><?= htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td style="padding:.55rem .75rem;border-bottom:1px solid var(--clr-border)"><?= htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.js-copy-table').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-target');
        var table = id ? document.getElementById(id) : null;
        if (!table) return;
        var lines = [];
        table.querySelectorAll('tr').forEach(function (tr) {
            var cells = [];
            tr.querySelectorAll('th, td').forEach(function (c) {
                cells.push(c.innerText.replace(/\s+/g, ' ').trim());
            });
            if (cells.length) lines.push(cells.join('\t'));
        });
        var text = lines.join('\n');
        var label = btn.textContent;
        function ok() { btn.textContent = 'Copié !'; setTimeout(function () { btn.textContent = label; }, 2000); }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(ok).catch(function () { prompt('Copier :', text); });
        } else {
            prompt('Copier :', text);
        }
    });
});
</script>

<!-- Pourquoi un expert local -->
<section class="section" aria-labelledby="expertise-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Notre expertise</span>
            <h2 id="expertise-title" class="section-title">Pourquoi choisir un expert local ?</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Connaissance précise</h3>
                <p class="card__text">Chaque secteur a ses particularités de prix, de demande et de types de biens. Notre expertise hyperlocale vous garantit une évaluation juste.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Réseau local solide</h3>
                <p class="card__text">Notaires, courtiers, artisans — notre réseau de partenaires locaux facilite chaque étape de votre transaction immobilière.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Accès off-market</h3>
                <p class="card__text">Des biens non publiés sur les portails, disponibles en exclusivité grâce à notre ancrage local et notre portefeuille de vendeurs.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-banner">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Vous avez un projet dans l'un de ces secteurs ?</h2>
            <p class="cta-banner__text">Contactez <?= htmlspecialchars(ADVISOR_NAME, ENT_QUOTES, 'UTF-8') ?> pour une expertise locale et des conseils personnalisés.</p>
            <div class="cta-banner__actions">
                <a href="<?= url('/estimation-gratuite') ?>" class="btn btn--accent">Estimation gratuite</a>
                <a href="<?= url('/contact') ?>" class="btn btn--outline-white">Nous contacter</a>
            </div>
        </div>
    </div>
</section>
