<?php
/*
 * Template partagé pour les pages secteur (villes et quartiers)
 * Variables attendues : $ville (array avec les données du secteur)
 */
$nom         = $ville['nom']         ?? 'Bordeaux';
$type        = $ville['type']        ?? 'ville'; // 'ville' ou 'quartier'
$prix        = $ville['prix']        ?? '3 500';
$tendance    = $ville['tendance']    ?? '+2%';
$delai       = $ville['delai']       ?? '40 jours';
$description = $ville['description'] ?? '';
$marche      = $ville['marche']      ?? '';
$image       = $ville['image']       ?? '/assets/images/bordeaux-centre.jpg';
$quartiers   = $ville['quartiers']   ?? [];
$villes      = $ville['villes']      ?? [];
$faq         = $ville['faq']         ?? [];

$typeLabel   = $type === 'quartier' ? 'Quartier de Bordeaux' : 'Gironde Métropole';
$idPrefix    = strtolower(preg_replace('/[^a-z0-9]/', '-', $nom));

$pageTitle = 'Immobilier ' . $nom . ' — ' . ADVISOR_NAME;
$metaDesc  = $ville['metaDesc'] ?? 'Expert immobilier à ' . $nom . '. Estimation gratuite, vente et achat de biens. Connaissance du marché local par Eduardo Desul, conseiller indépendant.';
$extraCss  = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="' . $idPrefix . '-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg,rgba(26,60,94,.92) 0%,rgba(15,38,68,.86) 58%,rgba(26,60,94,.92) 100%),url(\'' . $image . '\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier ' . htmlspecialchars($nom) . '</span>
            <h1 id="' . $idPrefix . '-title">Vendre et acheter à ' . htmlspecialchars($nom) . ' avec un expert local</h1>
            <p class="hero__subtitle">' . htmlspecialchars($description) . '</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Estimation gratuite</a>
                <a href="/contact" class="btn btn--outline-white">Prendre rendez-vous</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header">
            <span class="section-label">' . htmlspecialchars($typeLabel) . '</span>
            <h2 class="section-title">Le marché immobilier à ' . htmlspecialchars($nom) . '</h2>
        </div>
        <div class="grid-3" style="margin-bottom:2.5rem">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">' . htmlspecialchars($prix) . ' €/m²</div>
                <div class="stat-card__label">Prix moyen</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">' . htmlspecialchars($tendance) . '</div>
                <div class="stat-card__label">Évolution annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">' . htmlspecialchars($delai) . '</div>
                <div class="stat-card__label">Délai moyen de vente</div>
            </div>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h3 class="card__title">Expertise locale</h3>
                <p class="card__text">Je connais chaque rue de ' . htmlspecialchars($nom) . ' et les spécificités de son marché. Un avantage concret pour estimer juste et vendre vite.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <h3 class="card__title">Réseau d\'acquéreurs qualifiés</h3>
                <p class="card__text">Un portefeuille d\'acheteurs actifs et financés sur ' . htmlspecialchars($nom) . ' pour des ventes rapides au bon prix.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3 class="card__title">Accompagnement personnalisé</h3>
                <p class="card__text">De l\'estimation à la signature chez le notaire, je vous accompagne à chaque étape.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <h3 class="card__title">Honoraires transparents</h3>
                <p class="card__text">Des honoraires clairs et justifiés. Je suis mandataire indépendant : votre intérêt passe avant tout.</p>
            </div>
        </div>
    </div>
</section>

' . ($marche ? '
<section class="section">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Analyse</span>
            <h2 class="section-title">Le marché en détail</h2>
        </div>
        <div class="card" data-animate style="max-width:800px;margin:0 auto">
            <p class="card__text" style="line-height:1.8">' . nl2br(htmlspecialchars($marche)) . '</p>
        </div>
    </div>
</section>
' : '') . '

<section class="section section--alt">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Services</span>
            <h2 class="section-title">Mes services à ' . htmlspecialchars($nom) . '</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Évaluation précise basée sur les ventes récentes à ' . htmlspecialchars($nom) . ' et une analyse comparative approfondie du marché local.</p>
                <a href="/estimation-gratuite" class="btn btn--outline" style="margin-top:1rem">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h3 class="card__title">Vendre à ' . htmlspecialchars($nom) . '</h3>
                <p class="card__text">Stratégie de vente sur mesure, photos professionnelles, diffusion multicanale et négociation pour obtenir le meilleur prix.</p>
                <a href="/contact" class="btn btn--outline" style="margin-top:1rem">Vendre mon bien</a>
            </div>
            <div class="card" data-animate>
                <div class="card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="card__title">Acheter à ' . htmlspecialchars($nom) . '</h3>
                <p class="card__text">Recherche sur mesure, accès aux biens off-market et accompagnement de la visite jusqu\'à l\'acte notarié.</p>
                <a href="/biens" class="btn btn--outline" style="margin-top:1rem">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet immobilier à ' . htmlspecialchars($nom) . '</h2>
            <p class="cta-banner__text">Contactez Eduardo Desul pour une consultation gratuite et sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--accent">Me contacter</a>
                <a href="/estimation-gratuite" class="btn btn--outline-white">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>

' . (!empty($faq) ? '
<section class="section">
    <div class="container">
        <div class="section__header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Questions fréquentes — ' . htmlspecialchars($nom) . '</h2>
        </div>
        <div class="accordion" data-animate>' .
    implode('', array_map(fn($q) => '
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">' . htmlspecialchars($q['q']) . '</span>
                    <svg class="accordion__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                </button>
                <div class="accordion__content"><p>' . htmlspecialchars($q['a']) . '</p></div>
            </div>', $faq)) . '
        </div>
    </div>
</section>
' : '') . '

<section class="section section--alt">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Secteurs</span>
            <h2 class="section-title">Autres secteurs à découvrir</h2>
        </div>
        <div class="cities-grid">
            <a href="/secteurs/villes/bordeaux" class="city-card">
                <img src="/assets/images/bordeaux-centre.jpg" alt="Bordeaux centre immobilier" loading="lazy">
                <h3>Bordeaux</h3>
            </a>
            <a href="/secteurs/villes/merignac" class="city-card">
                <img src="/assets/images/merignac.jpg" alt="Mérignac immobilier" loading="lazy">
                <h3>Mérignac</h3>
            </a>
            <a href="/secteurs/villes/pessac" class="city-card">
                <img src="/assets/images/pessac.jpg" alt="Pessac immobilier" loading="lazy">
                <h3>Pessac</h3>
            </a>
            <a href="/secteurs/villes/talence" class="city-card">
                <img src="/assets/images/talence.jpg" alt="Talence immobilier" loading="lazy">
                <h3>Talence</h3>
            </a>
        </div>
    </div>
</section>
';
