<?php
$sequenceId = (int) ($sequence['id'] ?? 0);
$posts = $postBySequence[$sequenceId] ?? [];
?>
<article class="sequence-row">
    <header class="sequence-row-head">
        <div>
            <h3><?= e((string) ($sequence['nom'] ?? 'Séquence')) ?></h3>
            <p><?= e((string) ($sequence['persona'] ?? 'Persona')) ?> · <?= e((string) ($sequence['objectif'] ?? 'Objectif')) ?></p>
        </div>
        <div class="sequence-metrics">
            <span><?= count($posts) ?> posts</span>
            <span class="badge-status badge-<?= e((string) ($sequence['statut'] ?? 'active')) ?>"><?= e(ucfirst((string) ($sequence['statut'] ?? 'active'))) ?></span>
        </div>
    </header>

    <div class="sequence-post-grid">
        <?php foreach ($posts as $post): ?>
            <?php include __DIR__ . '/_post_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <footer class="sequence-row-foot">
        <form method="post" action="/admin?module=social&action=duplicate-sequence">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $sequenceId ?>">
            <button type="submit" class="social-btn">Dupliquer</button>
        </form>
        <form method="post" action="/admin?module=social&action=toggle-sequence">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $sequenceId ?>">
            <button type="submit" class="social-btn social-btn-ghost">Pause / Reprendre</button>
        </form>
    </footer>
</article>
