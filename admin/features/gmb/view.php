<?php

declare(strict_types=1);

$listingExists = (bool)($stats['listing_exists'] ?? false);
$reviewsCount = (int)($stats['reviews_count'] ?? 0);
$reviewsRating = (float)($stats['reviews_rating'] ?? 0.0);
$lastSync = $stats['last_sync'] ?? null;
$lastSyncText = $lastSync ? htmlspecialchars((string)$lastSync, ENT_QUOTES, 'UTF-8') : 'Jamais';
$lastCrawlScore = $stats['last_crawl_score'];
?>
<link rel="stylesheet" href="/assets/admin/gmb/gmb-hub.css">

<div class="seo-hub gmb-hub">
    <div class="seo-breadcrumb">Accueil › GMB</div>
    <h1>📍 HUB Google My Business</h1>
    <p class="seo-subtitle">
        Pilotez votre fiche Google Business Profile, vos avis, vos demandes et vos performances locales.
    </p>

    <div class="seo-search-wrap">
        <input type="text"
               id="gmb-module-search"
               placeholder="Rechercher un module GMB…"
               oninput="filterModules(this.value)">
    </div>

    <div class="seo-grid" id="gmb-modules-grid">

        <article class="seo-card"
                 data-module="ma fiche gmb profile établissement"
                 style="--accent:#3b82f6;--icon-bg:#dbeafe;">
            <div class="seo-card-head"><span class="icon">🏢</span><h3>Ma fiche</h3></div>
            <p>Consultez et mettez à jour les informations de votre établissement.</p>
            <div class="badges"><span>Profil</span><span>Visibilité</span></div>
            <a href="/admin?module=gmb&amp;action=listing" class="btn btn-sm">Ouvrir</a>
            <small id="gmb-listing-status">
                <?= $listingExists ? 'Fiche connectée' : 'Fiche non connectée' ?>
            </small>
        </article>

        <article class="seo-card"
                 data-module="avis google rating réponses"
                 style="--accent:#10b981;--icon-bg:#d1fae5;">
            <div class="seo-card-head"><span class="icon">⭐</span><h3>Avis</h3></div>
            <p>Suivez vos derniers avis Google et répondez rapidement.</p>
            <div class="badges"><span>Réputation</span><span>Réponses</span></div>
            <a href="/admin?module=gmb&amp;action=reviews" class="btn btn-sm">Gérer</a>
            <small id="gmb-reviews-meta">
                <?= htmlspecialchars((string)$reviewsCount, ENT_QUOTES, 'UTF-8') ?> avis ·
                <?= htmlspecialchars(number_format($reviewsRating, 1), ENT_QUOTES, 'UTF-8') ?>/5
            </small>
        </article>

        <article class="seo-card"
                 data-module="demandes avis relance test"
                 style="--accent:#ef4444;--icon-bg:#fee2e2;">
            <div class="seo-card-head"><span class="icon">✉️</span><h3>Demandes d'avis</h3></div>
            <p>Lancez vos relances et testez un envoi de demande d'avis.</p>
            <div class="badges"><span>Automation</span><span>Relance</span></div>
            <a href="/admin?module=gmb&amp;action=review-requests" class="btn btn-sm" id="gmb-request-review-btn">Envoyer test</a>
            <small>
                Dernière synchro : <span id="gmb-last-sync"><?= $lastSyncText ?></span>
            </small>
        </article>

        <article class="seo-card"
                 data-module="statistiques local seo appels itinéraires"
                 style="--accent:#f59e0b;--icon-bg:#fef3c7;">
            <div class="seo-card-head"><span class="icon">📊</span><h3>Statistiques</h3></div>
            <p>Visualisez vos performances locales (appels, vues, itinéraires).</p>
            <div class="badges"><span>Insights</span><span>Local SEO</span></div>
            <a href="/admin?module=gmb&amp;action=stats" class="btn btn-sm" id="gmb-sync-now-btn">Synchroniser maintenant</a>
            <small id="gmb-crawl-score">
                Dernier score : <?= $lastCrawlScore !== null ? (int)$lastCrawlScore . '/100' : 'N/A' ?>
            </small>
        </article>

    </div>
</div>

<script src="/assets/admin/gmb/gmb-hub.js" defer></script>
