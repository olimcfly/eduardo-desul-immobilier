<?php
$user = Auth::user();
$userId = (int)($user['id'] ?? 0);
$seoService = new SeoService(db());
$stats = array_merge([
    'keywords_count' => 0,
    'top10_count' => 0,
    'villes_count' => 0,
    'villes_published' => 0,
    'sitemap_last_generated' => null,
    'sitemap_status' => 'idle',
    'sitemap_issues_count' => 0,
    'last_audit_score' => null,
], $seoService->getHubStats($userId));
?>
<div class="seo-hub">
    <div class="seo-breadcrumb">Accueil › SEO</div>
    <h1>Être visible sur Google localement</h1>
    <p class="seo-subtitle">Créez des pages optimisées et attirez des vendeurs depuis les recherches locales.</p>

    <section class="seo-mother-block" aria-label="Synthèse visibilité locale">
        <div class="seo-mother-item">
            <span class="mother-label">Contexte</span>
            <p>Peu de visibilité = perte de leads.</p>
        </div>
        <div class="seo-mother-item">
            <span class="mother-label">Plan d'action</span>
            <p>3 piliers : pages locales, mots-clés, technique.</p>
        </div>
        <div class="seo-mother-item">
            <span class="mother-label">Bénéfices</span>
            <p>Plus de trafic local et plus de leads vendeurs.</p>
        </div>
        <div class="seo-mother-item">
            <span class="mother-label">Action</span>
            <a href="/admin?module=seo&action=ville-edit" class="btn btn-sm">Créer mes pages</a>
        </div>
    </section>

    <div class="seo-search-wrap">
        <input type="text" id="seo-module-search" placeholder="Rechercher un module SEO..." oninput="filterModules(this.value)">
    </div>

    <div class="seo-grid" id="seo-modules-grid">
        <article class="seo-card seo-card-priority" data-module="créer votre présence locale fiches villes local seo communes" style="--accent:#10b981;--icon-bg:#d1fae5;">
            <div class="seo-card-head"><span class="icon">📍</span><h3>Créer votre présence locale</h3></div>
            <p>Structurez vos fiches villes pour capter les vendeurs sur vos zones clés.</p>
            <div class="badges"><span>Important</span><span>Fiches villes</span></div>
            <a href="/admin?module=seo&action=villes">Gérer</a>
            <small><?= (int) ($stats['villes_count'] ?? 0) ?> fiches / <?= (int) ($stats['villes_published'] ?? 0) ?> publiées</small>
        </article>

        <article class="seo-card" data-module="cibler les recherches mots-clés top 10 positions" style="--accent:#3b82f6;--icon-bg:#dbeafe;">
            <div class="seo-card-head"><span class="icon">🔑</span><h3>Cibler les recherches</h3></div>
            <p>Suivez les mots-clés qui déclenchent des demandes de vendeurs localement.</p>
            <div class="badges"><span>Important</span><span>Mots-clés</span></div>
            <a href="/admin?module=seo&action=keywords">Consulter</a>
            <small><?= (int) ($stats['keywords_count'] ?? 0) ?> mots-clés suivis</small>
        </article>

        <article class="seo-card" data-module="être visible sur google sitemap indexation" style="--accent:#ef4444;--icon-bg:#fee2e2;">
            <div class="seo-card-head"><span class="icon">🗺️</span><h3>Être visible sur Google</h3></div>
            <p>Maintenez un sitemap propre pour accélérer l’indexation de vos pages locales.</p>
            <div class="badges"><span>Important</span><span>Sitemap</span></div>
            <a href="/admin?module=seo&action=sitemap">Gérer</a>
            <small>Dernière génération : <?= !empty($stats['sitemap_last_generated']) ? htmlspecialchars((string) $stats['sitemap_last_generated']) : 'Jamais' ?> · <?= (int) ($stats['sitemap_issues_count'] ?? 0) ?> alerte(s)</small>
        </article>

        <article class="seo-card" data-module="optimiser votre site performance technique core web vitals" style="--accent:#f59e0b;--icon-bg:#fef3c7;">
            <div class="seo-card-head"><span class="icon">🎯</span><h3>Optimiser votre site</h3></div>
            <p>Renforcez la performance technique pour soutenir votre visibilité locale durablement.</p>
            <div class="badges"><span>Important</span><span>Performance technique</span></div>
            <a href="/admin?module=seo&action=performance">Auditer</a>
            <small>Dernier score : <?= ($las = $stats['last_audit_score'] ?? null) !== null ? (int) $las . '/100' : 'N/A' ?></small>
        </article>
    </div>

    <section class="seo-cta-final" aria-label="Action finale visibilité locale">
        <h2>Commencez votre visibilité locale</h2>
        <a href="/admin?module=seo&action=ville-edit" class="btn btn-sm">Créer ma première fiche ville</a>
    </section>
</div>

