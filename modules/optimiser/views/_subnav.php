<?php

declare(strict_types=1);

/** @var string $current one of: hub, parcours, analytics, rapport-mensuel, ab-testing, recommandations */
$current = $current ?? 'hub';
$base = '/admin?module=optimiser';
$items = [
    'hub' => ['label' => 'Accueil', 'href' => $base],
    'parcours' => ['label' => 'Parcours', 'href' => $base . '&view=parcours'],
    'analytics' => ['label' => 'Analytics', 'href' => $base . '&view=analytics'],
    'rapport-mensuel' => ['label' => 'Rapport', 'href' => $base . '&view=rapport-mensuel'],
    'ab-testing' => ['label' => 'A/B', 'href' => $base . '&view=ab-testing'],
    'recommandations' => ['label' => 'IA', 'href' => $base . '&view=recommandations'],
];
?>
<nav class="optimiser-subnav" aria-label="Sous-section Optimiser">
    <?php foreach ($items as $key => $item): ?>
        <?php if ($key === $current): ?>
            <span class="optimiser-subnav__pill optimiser-subnav__pill--active"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php else: ?>
            <a class="optimiser-subnav__pill" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<style>
.optimiser-subnav{display:flex;flex-wrap:wrap;gap:.5rem;margin:0 0 1.25rem}
.optimiser-subnav__pill{display:inline-flex;align-items:center;padding:.45rem .85rem;border-radius:999px;font-size:.8rem;font-weight:600;text-decoration:none;border:1px solid rgba(148,163,184,.35);color:#64748b;background:#fff}
.optimiser-subnav__pill:hover{border-color:#6366f1;color:#4f46e5}
.optimiser-subnav__pill--active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-color:transparent}
</style>
