<?php

declare(strict_types=1);

$current = $current ?? 'hub';
$base = '/admin?module=convertir';
$items = [
    'hub' => ['label' => 'Accueil', 'href' => $base],
    'parcours' => ['label' => 'Parcours', 'href' => $base . '&action=parcours'],
    'rdv' => ['label' => 'Prise de RDV', 'href' => $base . '&action=rdv'],
    'suivi-post-rdv' => ['label' => 'Suivi post-RDV', 'href' => $base . '&action=suivi-post-rdv'],
];
?>
<nav class="convertir-subnav" aria-label="Sous-sections Convertir">
    <?php foreach ($items as $key => $item): ?>
        <?php if ($key === $current): ?>
            <span class="convertir-subnav__pill convertir-subnav__pill--active"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php else: ?>
            <a class="convertir-subnav__pill" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<style>
.convertir-subnav{display:flex;flex-wrap:wrap;gap:.5rem;margin:0 0 1.25rem}
.convertir-subnav__pill{display:inline-flex;align-items:center;padding:.45rem .85rem;border-radius:999px;font-size:.8rem;font-weight:600;text-decoration:none;border:1px solid rgba(148,163,184,.35);color:#64748b;background:#fff}
.convertir-subnav__pill:hover{border-color:#c9a84c;color:#7c5d1d}
.convertir-subnav__pill--active{background:linear-gradient(135deg,#c9a84c,#b8943d);color:#0f2237;border-color:transparent}
</style>
