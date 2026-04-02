<?php
/**
 * Générateur de placeholder SVG pour les biens immobiliers
 * Usage : /assets/images/placeholder.php?type=appartement&pieces=3&surface=72&label=Vente
 */

// Paramètres
$type    = htmlspecialchars(strtolower($_GET['type']    ?? 'bien'),    ENT_XML1);
$pieces  = (int) ($_GET['pieces']  ?? 0);
$surface = (int) ($_GET['surface'] ?? 0);
$label   = htmlspecialchars($_GET['label'] ?? '',                      ENT_XML1);

// Icône SVG selon le type (paths SVG)
$icons = [
    'appartement' => 'M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z M9 21V12h6v9',
    'maison'      => 'M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z M9 21V12h6v9',
    'terrain'     => 'M3 20h18M5 20V10l7-7 7 7v10M10 20v-5h4v5',
    'local'       => 'M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h2v4m4 0v-4h2v4',
    'default'     => 'M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z M9 21V12h6v9',
];
$iconPath = $icons[$type] ?? $icons['default'];

// Ligne d'infos
$infoParts = [];
if ($pieces > 0) $infoParts[] = $pieces . ' pièce' . ($pieces > 1 ? 's' : '');
if ($surface > 0) $infoParts[] = $surface . ' m²';
if ($label)       $infoParts[] = $label;
$infoLine = implode('  •  ', $infoParts);

// Label type formaté
$typeLabel = match($type) {
    'appartement' => 'Appartement',
    'maison'      => 'Maison',
    'terrain'     => 'Terrain',
    'local'       => 'Local commercial',
    default       => 'Bien immobilier',
};

header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');
?>
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300" role="img" aria-label="Image non disponible — <?= $typeLabel ?>">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#1e4a74"/>
      <stop offset="100%" stop-color="#1a3c5e"/>
    </linearGradient>
    <linearGradient id="shimmer" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%"   stop-color="#c9a84c" stop-opacity="0"/>
      <stop offset="50%"  stop-color="#c9a84c" stop-opacity=".15"/>
      <stop offset="100%" stop-color="#c9a84c" stop-opacity="0"/>
    </linearGradient>
  </defs>

  <!-- Fond -->
  <rect width="400" height="300" fill="url(#bg)"/>

  <!-- Motif de grille subtil -->
  <line x1="0"   y1="150" x2="400" y2="150" stroke="#ffffff" stroke-opacity=".04" stroke-width="1"/>
  <line x1="200" y1="0"   x2="200" y2="300" stroke="#ffffff" stroke-opacity=".04" stroke-width="1"/>

  <!-- Cercle décoratif derrière l'icône -->
  <circle cx="200" cy="130" r="52" fill="#ffffff" fill-opacity=".06"/>
  <circle cx="200" cy="130" r="44" fill="#ffffff" fill-opacity=".05"/>

  <!-- Icône maison (scalée depuis 24×24 viewBox, centrée en 200,130) -->
  <g transform="translate(176, 106) scale(2)" stroke="#c9a84c" stroke-width="1.5"
     fill="none" stroke-linecap="round" stroke-linejoin="round">
    <path d="<?= $iconPath ?>"/>
  </g>

  <!-- Séparateur doré -->
  <rect x="170" y="172" width="60" height="2" rx="1" fill="#c9a84c" opacity=".8"/>

  <!-- Nom du type de bien -->
  <text x="200" y="195"
        font-family="Georgia, 'Times New Roman', serif"
        font-size="16" font-weight="600"
        fill="#ffffff" text-anchor="middle" opacity=".9">
    <?= $typeLabel ?>
  </text>

  <!-- Infos surface / pièces -->
  <?php if ($infoLine): ?>
  <text x="200" y="216"
        font-family="Arial, Helvetica, sans-serif"
        font-size="12"
        fill="#c9a84c" text-anchor="middle" opacity=".85">
    <?= $infoLine ?>
  </text>
  <?php endif; ?>

  <!-- Mention "Photo à venir" -->
  <text x="200" y="248"
        font-family="Arial, Helvetica, sans-serif"
        font-size="11"
        fill="#ffffff" text-anchor="middle" opacity=".4"
        letter-spacing=".5">
    Photo à venir
  </text>

  <!-- Shimmer décoratif bas -->
  <rect x="0" y="260" width="400" height="40" fill="url(#shimmer)"/>
</svg>
