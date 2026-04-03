<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/GmbService.php';

$user = Auth::user();
$service = new GmbService((int) ($user['id'] ?? 0));
$avis = $service->avis();
?>
<section class="gmb-panel">
    <div class="gmb-panel-head">
        <h2>Avis clients</h2>
        <button class="btn-gmb" data-action="get-avis">Actualiser les avis</button>
    </div>

    <div class="gmb-avis-list">
        <?php if (!$avis): ?>
            <p>Aucun avis disponible pour le moment.</p>
        <?php endif; ?>

        <?php foreach ($avis as $row): ?>
            <article class="gmb-avis-item" data-avis-id="<?= (int) $row['id'] ?>">
                <div class="gmb-avis-head">
                    <strong><?= htmlspecialchars($row['auteur'] ?: 'Client Google') ?></strong>
                    <span><?= str_repeat('★', (int) $row['note']) ?><?= str_repeat('☆', 5 - (int) $row['note']) ?></span>
                </div>
                <p><?= nl2br(htmlspecialchars((string) $row['commentaire'])) ?></p>
                <small><?= htmlspecialchars((string) $row['avis_at']) ?></small>
                <textarea class="reply-input" placeholder="Répondre à cet avis..."><?= htmlspecialchars((string) ($row['reponse'] ?? '')) ?></textarea>
                <button class="btn-gmb" data-action="reply-avis">Publier la réponse</button>
            </article>
        <?php endforeach; ?>
    </div>
</section>
