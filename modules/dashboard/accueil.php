<?php
$pageTitle = "Tableau de bord";
$pageDescription = "Vue d'ensemble de votre activité immobilière";


function renderContent() {
    ?>
    <div class="section-header">
        <h1>HUB <span class="section-title">Tableau de bord</span></h1>
        <p class="section-subtitle">Vue d'ensemble de votre activité</p>
    </div>

    <div class="dashboard-grid">
        <!-- Statistiques principales -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">🏠</div>
                <div class="card-title">Biens en gestion</div>
            </div>
            <div class="card-value" id="biens-count">Chargement...</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon">📞</div>
                <div class="card-title">Leads ce mois</div>
            </div>
            <div class="card-value" id="leads-count">Chargement...</div>
        </div>

        <!-- Autres widgets -->
    </div>

    <script>
    // Charger les statistiques via AJAX
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/admin/api/dashboard/stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('biens-count').textContent = data.biens;
                document.getElementById('leads-count').textContent = data.leads;
            });
    });
    </script>
    <?php
}
