<?php
declare(strict_types=1);

$siloId = (int)($_GET['silo_id'] ?? 1);
$silos = $seoService->getSilos();
$structure = $seoService->getSiloStructure($siloId);
$opportunities = $seoService->getSiloOpportunities($siloId);
$pillar = $structure['pillar'];
$satellites = $structure['satellites'];
$completion = min(100, (int)round((count($satellites) / 8) * 100));

require __DIR__ . '/_layout_top.php';
?>
<section class="card">
    <form method="get" class="inline-form">
        <input type="hidden" name="action" value="silo">
        <select name="silo_id" aria-label="Sélectionner un silo">
            <?php foreach ($silos as $silo): ?>
                <option value="<?= (int)$silo['id'] ?>" <?= $siloId === (int)$silo['id'] ? 'selected' : '' ?>><?= seo_h($silo['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary">Charger</button>
    </form>
</section>

<div class="silo-view" data-silo-id="<?= $siloId ?>">
    <div class="silo-header card">
        <h3>Silo Pilier</h3>
        <div class="completion-ring" data-completion="<?= $completion ?>">
            <svg width="80" height="80" aria-hidden="true">
                <circle cx="40" cy="40" r="37" stroke="var(--border)" stroke-width="6" fill="none"/>
                <circle cx="40" cy="40" r="37" stroke="var(--gold)" stroke-width="6" fill="none"
                        stroke-dasharray="232" stroke-dashoffset="<?= 232 - ($completion / 100 * 232) ?>" stroke-linecap="round"/>
            </svg>
            <div class="completion-text"><strong><?= $completion ?>%</strong><small>Complétion</small></div>
        </div>
    </div>

    <div class="silo-visualization card">
        <div class="pillar-node" draggable="true" data-article-id="<?= (int)($pillar['id'] ?? 0) ?>">
            <strong>🏛️ <?= seo_h($pillar['title'] ?? 'Ajouter un pilier') ?></strong>
            <div class="muted"><?= seo_h($pillar['persona'] ?? 'Aucun persona') ?> · Niveau <?= (int)($pillar['consciousness_level'] ?? 1) ?></div>
            <button class="btn-ghost" type="button">Éditer</button>
        </div>
        <div class="satellites-container">
            <?php foreach ($satellites as $satellite): ?>
                <div class="sat-node" draggable="true" data-position="<?= (int)$satellite['silo_position'] ?>">
                    <strong>🛰️ <?= seo_h($satellite['title']) ?></strong>
                    <div class="muted"><?= seo_h($satellite['persona'] ?? 'N/A') ?> · Niveau <?= (int)$satellite['consciousness_level'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h3>Opportunités d'optimisation</h3>
        <?php foreach ($opportunities as $opp): ?>
            <div class="opportunity">
                <div class="opp-header">
                    <span class="badge">Impact <?= (int)$opp['impact'] ?>/10</span>
                    <strong><?= seo_h($opp['title']) ?></strong>
                </div>
                <p><?= seo_h($opp['description']) ?></p>
                <button class="btn-primary" type="button">Créer l'article</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
