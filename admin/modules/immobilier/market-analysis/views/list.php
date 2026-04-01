<?php
$pagination = $state['pagination'] ?? ['items' => [], 'pages' => 1, 'page' => 1];
$items = $pagination['items'] ?? [];
?>

<div class="ma2-card">
    <?php if (!$items): ?>
        <p style="margin:0;color:#6b7280;">Aucune analyse pour le moment. Créez votre première analyse de marché locale.</p>
    <?php else: ?>
        <table class="ma2-table">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Cible</th>
                    <th>Statut</th>
                    <th>Créée le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $analysis): ?>
                    <?php $status = (string) ($analysis['status'] ?? 'draft'); ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars((string) ($analysis['city'] ?? '')) ?></strong>
                            <?php if (!empty($analysis['postal_code'])): ?>
                                <div style="color:#6b7280;font-size:12px;"><?= htmlspecialchars((string) $analysis['postal_code']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string) ($analysis['target_type'] ?? 'mixte')) ?></td>
                        <td><span class="ma2-status <?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></span></td>
                        <td><?= htmlspecialchars((string) ($analysis['created_at'] ?? '')) ?></td>
                        <td>
                            <div class="ma2-actions">
                                <a class="ma2-link" href="?page=market-analysis&action=show&id=<?= (int) $analysis['id'] ?>">Voir</a>
                                <form method="post" onsubmit="return confirm('Supprimer cette analyse ?');" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['auth_csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $analysis['id'] ?>">
                                    <button class="ma2-danger" type="submit"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
