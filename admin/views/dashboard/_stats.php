<?php
/**
 * Partial réutilisable — barre de stats
 * Variables attendues : $stats (array)
 */
$items = [
    ['icon' => '🏠', 'key' => 'biens_total',   'label' => 'Biens total',     'href' => '/admin/biens/'],
    ['icon' => '✅', 'key' => 'biens_actifs',   'label' => 'Actifs',          'href' => '/admin/biens/?statut=actif'],
    ['icon' => '🕓', 'key' => 'biens_pending',  'label' => 'En attente',      'href' => '/admin/biens/?statut=pending'],
    ['icon' => '⭐', 'key' => 'gmb_note',       'label' => 'Note Google',     'href' => '/admin/gmb/'],
    ['icon' => '💬', 'key' => 'avis_total',     'label' => 'Avis',            'href' => '/admin/gmb/reviews.php'],
    ['icon' => '🔑', 'key' => 'keywords_top',   'label' => 'Top 10 SEO',      'href' => '/admin/seo/keywords.php'],
    ['icon' => '📱', 'key' => 'social_queued',  'label' => 'Posts en file',   'href' => '/admin/social/'],
];
?>
<div class="stats-bar">
    <?php foreach ($items as $item): ?>
        <a href="<?= $item['href'] ?>" class="stat-chip">
            <span class="stat-chip-icon"><?= $item['icon'] ?></span>
            <span class="stat-chip-value">
                <?= htmlspecialchars((string)($stats[$item['key']] ?? '—')) ?>
            </span>
            <span class="stat-chip-label"><?= $item['label'] ?></span>
        </a>
    <?php endforeach; ?>
</div>
