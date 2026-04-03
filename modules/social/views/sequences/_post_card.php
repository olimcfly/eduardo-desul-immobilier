<article class="post-card" data-status="<?= e((string) ($post['statut'] ?? 'brouillon')) ?>">
    <div class="post-card-top">
        <strong><?= e((string) ($post['titre'] ?? 'Post')) ?></strong>
        <span><?= e(strtoupper((string) ($post['statut'] ?? 'brouillon'))) ?></span>
    </div>
    <p><?= e(truncate((string) ($post['contenu'] ?? ''), 120)) ?></p>
    <a href="/admin?module=social&action=post&id=<?= (int) ($post['id'] ?? 0) ?>">Voir détail</a>
</article>
