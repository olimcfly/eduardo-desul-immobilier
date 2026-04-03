<?php
$sequences = $sequences ?? [];
$postBySequence = $postBySequence ?? [];
?>
<div class="social-sequences-list">
    <?php if ($sequences === []): ?>
        <article class="social-empty-card">
            <h3>Aucune séquence trouvée</h3>
            <p>Commencez par créer votre première séquence social pour Bordeaux Métropole.</p>
        </article>
    <?php endif; ?>

    <?php foreach ($sequences as $sequence): ?>
        <?php include __DIR__ . '/_row.php'; ?>
    <?php endforeach; ?>
</div>
