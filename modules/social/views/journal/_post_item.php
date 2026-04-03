<article class="journal-item">
    <div>
        <h4><?= e((string) ($post['titre'] ?? 'Publication')) ?></h4>
        <small><?= e((string) ($post['sequence_nom'] ?? 'Sans séquence')) ?></small>
    </div>
    <p><?= e(truncate((string) ($post['contenu'] ?? ''), 180)) ?></p>
    <div class="journal-meta">
        <span><?= e((string) ($post['planifie_at'] ?? $post['created_at'] ?? '')) ?></span>
        <a href="/admin?module=social&action=post&id=<?= (int) ($post['id'] ?? 0) ?>">Ouvrir</a>
    </div>
</article>
