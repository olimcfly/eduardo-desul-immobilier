<?php
/**
 * BLOCK RENDERER: Filters
 * Affiche les filtres de recherche de propriétés (intégré avec module Biens)
 */

if (!isset($blockData)) $blockData = [];

$description = htmlspecialchars($blockData['description'] ?? 'Recherchez la propriété qui vous convient');
?>

<section style="padding: 60px 24px; background: #f9f6f3;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <h2 style="text-align: center; font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1a4d7a; margin-bottom: 12px;">
      Trouver un bien
    </h2>

    <?php if ($description): ?>
    <p style="text-align: center; color: #718096; font-size: 16px; margin-bottom: 40px;">
      <?php echo $description; ?>
    </p>
    <?php endif; ?>

    <!-- Formulaire de filtres -->
    <form method="GET" style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.07);">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <input type="text" name="search" placeholder="Lieu, rue..." style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px;">
        <select name="type" style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px;">
          <option value="">Type de bien</option>
          <option value="maison">Maison</option>
          <option value="appartement">Appartement</option>
          <option value="terrain">Terrain</option>
        </select>
        <select name="price" style="padding: 12px; border: 1px solid #d4a574; border-radius: 8px;">
          <option value="">Fourchette de prix</option>
          <option value="0-300000">0 - 300 000 €</option>
          <option value="300000-600000">300 000 - 600 000 €</option>
          <option value="600000+">600 000 € +</option>
        </select>
      </div>
      <button type="submit" style="width: 100%; margin-top: 20px; background: #1a4d7a; color: #fff; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
        Rechercher
      </button>
    </form>
  </div>
</section>
