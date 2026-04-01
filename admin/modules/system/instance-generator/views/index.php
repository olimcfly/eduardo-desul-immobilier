<?php
$flash = $_SESSION['auth_instance_generator_flash'] ?? null;
unset($_SESSION['auth_instance_generator_flash']);
?>
<div class="card">
    <div class="card-hd" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
        <h3>Générateur d’instance client</h3>
        <a class="btn btn-p btn-sm" href="/admin/dashboard.php?page=instance-generator&action=create">
            <i class="fas fa-plus"></i> Créer une instance
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success' ?>" style="margin-bottom:12px;">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Client</th>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Domaine</th>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Statut</th>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">ZIP</th>
                <th style="padding:8px;border-bottom:1px solid #e2e8f0;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$instances): ?>
                <tr><td colspan="5" style="padding:10px;">Aucune instance pour le moment.</td></tr>
            <?php else: ?>
                <?php foreach ($instances as $item): ?>
                    <tr>
                        <td style="padding:8px;"><?= htmlspecialchars($item['client_name']) ?></td>
                        <td style="padding:8px;"><?= htmlspecialchars($item['domain']) ?></td>
                        <td style="padding:8px;"><?= htmlspecialchars($item['status']) ?></td>
                        <td style="padding:8px;font-size:12px;"><?= htmlspecialchars((string) ($item['zip_path'] ?? '-')) ?></td>
                        <td style="padding:8px;text-align:center;">
                            <a class="btn btn-s btn-sm" href="/admin/dashboard.php?page=instance-generator&action=show&id=<?= (int) $item['id'] ?>">Détail</a>
                            <a class="btn btn-s btn-sm" href="/admin/dashboard.php?page=instance-generator&action=edit&id=<?= (int) $item['id'] ?>">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
