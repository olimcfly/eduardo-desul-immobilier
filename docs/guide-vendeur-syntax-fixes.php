<?php
/**
 * Correctifs de syntaxe appliqués au snippet "guide vendeur" fourni.
 * Ce fichier contient les sections qui empêchaient la compilation PHP.
 */

// 1) Variables PHP avec espaces supprimés + foreach corrigé.
$dpeClasses = [
    'A' => ['#009a44', 'Excellent', '< 70 kWh'],
    'B' => ['#51b848', 'Très bon', '70-110 kWh'],
    'C' => ['#c8d400', 'Bon', '111-180 kWh'],
    'D' => ['#f7a600', 'Moyen', '181-250 kWh'],
    'E' => ['#ef7d00', 'Médiocre', '251-330 kWh'],
    'F' => ['#e52320', 'Mauvais', '331-420 kWh'],
    'G' => ['#9b1915', 'Très mauvais', '> 420 kWh'],
];

foreach ($dpeClasses as $letter => [$color, $label, $range]) : ?>
    <div class="dpe-bar" style="--dpe-color: <?= $color ?>">
        <div class="dpe-bar__letter"><?= $letter ?></div>
        <div class="dpe-bar__fill"></div>
        <div class="dpe-bar__info">
            <span><?= $label ?></span>
            <span><?= $range ?></span>
        </div>
    </div>
<?php endforeach; ?>

<?php
// 2) Bloc checklist #4 réparé (balises HTML/PHP fermées correctement).
$checks4 = [
    'c17' => "Processus de qualification des acheteurs défini",
    'c18' => "Fiche récapitulative bien préparée pour les visites",
    'c19' => "Critères d'acceptation d'une offre définis",
    'c20' => "Offre écrite reçue et analysée",
    'c21' => "Solidité financière de l'acquéreur vérifiée",
];

foreach ($checks4 as $id => $label) : ?>
    <label class="checklist-item" for="<?= $id ?>">
        <input type="checkbox" id="<?= $id ?>" class="checklist-check" data-id="<?= $id ?>">
        <div class="checklist-item__box">
            <i class="fas fa-check"></i>
        </div>
        <span><?= $label ?></span>
    </label>
<?php endforeach; ?>
