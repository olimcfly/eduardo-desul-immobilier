<?php if ($post === null): ?>
    <article class="social-empty-card">
        <h3>Publication introuvable</h3>
        <a class="social-btn" href="/admin?module=social&action=journal">Retour au journal</a>
    </article>
<?php else: ?>
    <article class="post-detail">
        <header>
            <h2><?= e((string) ($post['titre'] ?? 'Détail publication')) ?></h2>
            <p><?= e((string) ($post['sequence_nom'] ?? 'Sans séquence')) ?> · <?= e((string) ($post['statut'] ?? 'brouillon')) ?></p>
        </header>

        <section class="post-preview">
            <h3>Aperçu</h3>
            <p><?= nl2br(e((string) ($post['contenu'] ?? ''))) ?></p>
        </section>

        <section class="post-strategy">
            <h3>Bloc stratégique</h3>
            <p><strong>Persona:</strong> <?= e((string) ($strategy['persona'] ?? '-')) ?></p>
            <p><strong>Niveau:</strong> <?= e((string) ($strategy['niveau'] ?? '-')) ?></p>
            <p><strong>Objectif:</strong> <?= e((string) ($strategy['objectif'] ?? '-')) ?></p>
            <p><strong>Score:</strong> <?= (int) ($strategy['score'] ?? 0) ?>/100</p>
        </section>

        <div class="post-detail-actions">
            <a class="social-btn" href="/admin?module=social&action=post-form&id=<?= (int) ($post['id'] ?? 0) ?>">Modifier</a>
            <form method="post" action="/admin?module=social&action=delete-post" onsubmit="return confirm('Supprimer ce post ?');">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= (int) ($post['id'] ?? 0) ?>">
                <button type="submit" class="social-btn social-btn-danger">Supprimer</button>
            </form>
        </div>
    </article>
<?php endif; ?>
