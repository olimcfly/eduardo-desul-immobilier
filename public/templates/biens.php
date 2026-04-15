<?php
// biens.php

// Définir les métadonnées spécifiques à la page
$pageTitle = 'Nos biens immobiliers à Bordeaux — Eduardo De Sul | Vente & Location';
$metaDesc = 'Découvrez notre sélection de biens immobiliers à Bordeaux et dans la métropole bordelaise : appartements, maisons, immeubles, terrains et biens de prestige.';
$metaKeywords = 'biens immobiliers Bordeaux, appartements à vendre Bordeaux, maisons Bordeaux, immobilier Bordeaux Métropole, acheter à Bordeaux, location Bordeaux';

// Définir le template à utiliser
$template = 'biens';

// Inclure l'en-tête
require_once __DIR__ . '/../../templates/header.php';
?>

<!-- Contenu spécifique à la page -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Nos biens immobiliers</h1>
        <p class="page-subtitle">À Bordeaux et dans la métropole bordelaise</p>
    </div>
</section>

<!-- Contenu principal de la page -->
<div class="container">
    <!-- Votre contenu ici -->
</div>

<?php
// Inclure le pied de page
require_once __DIR__ . '/../../templates/footer.php';
?>