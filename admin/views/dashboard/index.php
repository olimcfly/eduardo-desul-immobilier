<div class="section-header">
    <h1>HUB <span class="section-title">Tableau de bord</span></h1>
    <p class="section-subtitle">Vue d'ensemble de votre activité</p>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <div class="card-icon">🏠</div>
            <div class="card-title">Biens en gestion</div>
        </div>
        <div class="card-value"><?= $stats['biens'] ?></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon">📞</div>
            <div class="card-title">Leads ce mois</div>
        </div>
        <div class="card-value"><?= $stats['leads'] ?></div>
    </div>
</div>
