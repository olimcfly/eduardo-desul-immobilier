<?php $posts = $posts ?? []; ?>
<section class="social-journal-list">
    <?php if ($posts === []): ?>
        <article class="social-empty-card">
            <h3>Journal vide</h3>
            <p>Programmez des posts pour afficher votre timeline social.</p>
        </article>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <?php include __DIR__ . '/_post_item.php'; ?>
    <?php endforeach; ?>
</section>
