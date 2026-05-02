<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/GmbService.php';

$user = Auth::user();
$service = new GmbService((int) ($user['id'] ?? 0));
$latestStats = $service->getStats();
$statsMonth = !empty($latestStats['date_stat']) ? date('Y-m', strtotime((string) $latestStats['date_stat'])) : date('Y-m');
?>
<section class="gmb-panel">
    <div class="gmb-panel-head">
        <h2>Statistiques GMB</h2>
        <button class="btn-gmb" data-action="get-stats">Afficher les dernières données</button>
    </div>

    <p class="gmb-mode-note">Données à renseigner manuellement pour le moment. Aucune donnée Google n’est inventée.</p>

    <div id="gmb-stats-grid" class="gmb-stats-grid">
        <div><strong>Impressions</strong><span data-stat="impressions"><?= isset($latestStats['impressions']) ? (int) $latestStats['impressions'] : '-' ?></span></div>
        <div><strong>Clics site</strong><span data-stat="clics_site"><?= isset($latestStats['clics_site']) ? (int) $latestStats['clics_site'] : '-' ?></span></div>
        <div><strong>Appels</strong><span data-stat="appels"><?= isset($latestStats['appels']) ? (int) $latestStats['appels'] : '-' ?></span></div>
        <div><strong>Itinéraires</strong><span data-stat="itineraires"><?= isset($latestStats['itineraires']) ? (int) $latestStats['itineraires'] : '-' ?></span></div>
        <div><strong>Photos vues</strong><span data-stat="photos_vues"><?= isset($latestStats['photos_vues']) ? (int) $latestStats['photos_vues'] : '-' ?></span></div>
        <div><strong>Rech. directes</strong><span data-stat="recherches_dir"><?= isset($latestStats['recherches_dir']) ? (int) $latestStats['recherches_dir'] : '-' ?></span></div>
        <div><strong>Rech. découvertes</strong><span data-stat="recherches_disc"><?= isset($latestStats['recherches_disc']) ? (int) $latestStats['recherches_disc'] : '-' ?></span></div>
    </div>

    <?php if (!$latestStats): ?>
        <p class="gmb-empty-state">Aucune statistique enregistrée. Renseignez votre premier mois ci-dessous.</p>
    <?php endif; ?>

    <form id="gmb-stats-form" class="gmb-form gmb-stats-form">
        <?= csrfField() ?>
        <label>Mois<input type="month" name="stats_month" value="<?= htmlspecialchars($statsMonth, ENT_QUOTES, 'UTF-8') ?>" required></label>
        <label>Impressions<input type="number" name="impressions" min="0" step="1" value="<?= (int) ($latestStats['impressions'] ?? 0) ?>"></label>
        <label>Clics site<input type="number" name="clics_site" min="0" step="1" value="<?= (int) ($latestStats['clics_site'] ?? 0) ?>"></label>
        <label>Appels<input type="number" name="appels" min="0" step="1" value="<?= (int) ($latestStats['appels'] ?? 0) ?>"></label>
        <label>Itinéraires<input type="number" name="itineraires" min="0" step="1" value="<?= (int) ($latestStats['itineraires'] ?? 0) ?>"></label>
        <label>Photos vues<input type="number" name="photos_vues" min="0" step="1" value="<?= (int) ($latestStats['photos_vues'] ?? 0) ?>"></label>
        <label>Recherches directes<input type="number" name="recherches_dir" min="0" step="1" value="<?= (int) ($latestStats['recherches_dir'] ?? 0) ?>"></label>
        <label>Recherches découvertes<input type="number" name="recherches_disc" min="0" step="1" value="<?= (int) ($latestStats['recherches_disc'] ?? 0) ?>"></label>
        <button type="submit" class="btn-gmb">Enregistrer les statistiques manuelles</button>
    </form>
</section>
