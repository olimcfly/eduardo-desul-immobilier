<?php
/**
 * Générateur de placeholder SVG pour les biens immobiliers
 * Usage : /assets/images/placeholder.php?type=appartement&pieces=3&surface=72
 */

$type    = htmlspecialchars(strtolower($_GET['type']    ?? 'bien'),    ENT_XML1);
$pieces  = (int) ($_GET['pieces']  ?? 0);
$surface = (int) ($_GET['surface'] ?? 0);

// Icône SVG selon le type
$icons = [
    'appartement' => 'M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z M9 21V12h6v9',
    'maison'      => 'M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z M9 21V12h6v9',
    'terrain'     => 'M3 20h18M5 20V10l7-7 7 7v10M10 20v-5h4v5',
    'local'       => 'M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h2v4m4 0v-4h2v4',
    'article'     => 'M4 6h16M4 10h16M4 14h10M4 18h7',
    'default'     => 'M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z M9 21V12h6v9',
];
$iconPath = $icons[$type] ?? $icons['default'];

// Infos surface / pièces
$infoParts = [];
if ($pieces  > 0) $infoParts[] = $pieces  . ' pièce' . ($pieces > 1 ? 's' : '');
if ($surface > 0) $infoParts[] = $surface . ' m²';
$infoLine = implode('  •  ', $infoParts);

$typeLabels = [
    'appartement' => 'Appartement',
    'maison'      => 'Maison',
    'terrain'     => 'Terrain',
    'local'       => 'Local commercial',
    'article'     => 'Article',
    'bien'        => 'Bien immobilier',
];
$typeLabel = $typeLabels[$type] ?? 'Bien immobilier';

header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');
?>
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="500" viewBox="0 0 800 500" role="img" aria-label="Photo à venir — <?= $typeLabel ?>">
  <defs>
    <linearGradient id="bgGrad" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#1e4a74"/>
      <stop offset="100%" stop-color="#0f2540"/>
    </linearGradient>
    <linearGradient id="goldLine" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%"   stop-color="#c9a84c" stop-opacity="0"/>
      <stop offset="30%"  stop-color="#c9a84c" stop-opacity=".9"/>
      <stop offset="70%"  stop-color="#e8c76a" stop-opacity=".9"/>
      <stop offset="100%" stop-color="#c9a84c" stop-opacity="0"/>
    </linearGradient>
    <linearGradient id="shimmer" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#c9a84c" stop-opacity=".06"/>
      <stop offset="100%" stop-color="#c9a84c" stop-opacity="0"/>
    </linearGradient>
    <filter id="glow">
      <feGaussianBlur stdDeviation="3" result="blur"/>
      <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
    </filter>
  </defs>

  <!-- Fond dégradé bleu nuit -->
  <rect width="800" height="500" fill="url(#bgGrad)"/>

  <!-- Motif grille géométrique subtil -->
  <g stroke="#ffffff" stroke-opacity=".025" stroke-width="1">
    <line x1="0"   y1="125" x2="800" y2="125"/>
    <line x1="0"   y1="250" x2="800" y2="250"/>
    <line x1="0"   y1="375" x2="800" y2="375"/>
    <line x1="200" y1="0"   x2="200" y2="500"/>
    <line x1="400" y1="0"   x2="400" y2="500"/>
    <line x1="600" y1="0"   x2="600" y2="500"/>
  </g>

  <!-- Cercles décoratifs halo -->
  <circle cx="400" cy="210" r="90" fill="#ffffff" fill-opacity=".04"/>
  <circle cx="400" cy="210" r="70" fill="#ffffff" fill-opacity=".04"/>
  <circle cx="400" cy="210" r="52" fill="#c9a84c"  fill-opacity=".07"/>

  <!-- Icône maison (centrée, scalée depuis viewBox 24×24) -->
  <g transform="translate(352, 162) scale(4)" stroke="#c9a84c" stroke-width="1.2"
     fill="none" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)">
    <path d="<?= $iconPath ?>"/>
  </g>

  <!-- Trait doré séparateur -->
  <rect x="300" y="310" width="200" height="2" rx="1" fill="url(#goldLine)"/>

  <!-- Nom du type -->
  <text x="400" y="345"
        font-family="Georgia, 'Playfair Display', serif"
        font-size="22" font-weight="600" letter-spacing="1"
        fill="#ffffff" text-anchor="middle" opacity=".92">
    <?= $typeLabel ?>
  </text>

  <!-- Infos surface/pièces -->
  <?php if ($infoLine): ?>
  <text x="400" y="374"
        font-family="Arial, Helvetica, sans-serif"
        font-size="14" letter-spacing=".5"
        fill="#c9a84c" text-anchor="middle" opacity=".85">
    <?= $infoLine ?>
  </text>
  <?php endif; ?>

  <!-- Mention discrète -->
  <text x="400" y="420"
        font-family="Arial, Helvetica, sans-serif"
        font-size="12" letter-spacing="2"
        fill="#ffffff" text-anchor="middle" opacity=".28"
        text-decoration="none">
    PHOTO À VENIR
  </text>

  <!-- Signature Eduardo Desul Immobilier -->
  <text x="400" y="460"
        font-family="Georgia, serif"
        font-size="11" letter-spacing=".8"
        fill="#c9a84c" text-anchor="middle" opacity=".45">
    Eduardo Desul Immobilier • Bordeaux
  </text>

  <!-- Shimmer bas de page -->
  <rect x="0" y="440" width="800" height="60" fill="url(#shimmer)"/>
</svg>
