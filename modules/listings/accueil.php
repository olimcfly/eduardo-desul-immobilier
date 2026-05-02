<?php

declare(strict_types=1);

$pageTitle = 'Listings';
$pageDescription = 'Catalogue des propriétés';

function renderContent(): void {
    ?>
    <style>
        .hub-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
        .hub-hero h1 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .hub-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; }
        .hub-actions { display: flex; gap: 12px; margin-top: 16px; }
        .hub-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all .2s; }
        .hub-btn-primary { background: #c9a84c; color: #10253c; }
        .hub-btn-primary:hover { background: #b8962d; }
        .listings-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .section-header { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: #f9fafb; }
        .section-header h2 { margin: 0; font-size: 16px; font-weight: 600; color: #0f172a; }
        .listings-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .listings-table th { padding: 12px 16px; text-align: left; font-weight: 600; color: #374151; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .listings-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
        .listings-table tr:hover { background: #f9fafb; }
        .property-title { font-weight: 600; color: #0f172a; }
        .property-addr { font-size: 12px; color: #64748b; }
        .empty-state { padding: 32px 16px; text-align: center; color: #9ca3af; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .status-available { background: #dcfce7; color: #166534; }
        .status-sold { background: #fee2e2; color: #991b1b; }
        .status-rented { background: #fef3c7; color: #92400e; }
        .action-links { display: flex; gap: 8px; }
        .action-link { padding: 4px 8px; text-decoration: none; color: #3498db; font-size: 12px; border-radius: 4px; }
        .action-link:hover { background: #ecf0f1; }
        .delete-link { color: #dc2626; }
    </style>

    <div>
        <header class="hub-hero">
            <h1>Listings</h1>
            <p>Gérez votre catalogue de propriétés</p>
            <div class="hub-actions">
                <a href="/admin?module=listings&action=new" class="hub-btn hub-btn-primary">
                    <i class="fas fa-plus"></i> Nouveau listing
                </a>
            </div>
        </header>

        <div class="listings-section">
            <div class="section-header">
                <h2>🏠 Propriétés</h2>
            </div>
            <table class="listings-table">
                <thead>
                    <tr>
                        <th>Propriété</th>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Créée</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = db()->prepare('SELECT id, title, address, type, price, status, created_at FROM properties ORDER BY created_at DESC LIMIT 50');
                        $stmt->execute();
                        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (empty($properties)):
                    ?>
                        <tr><td colspan="6" class="empty-state">Aucune propriété créée. <a href="/admin?module=listings&action=new" style="color: #c9a84c; font-weight: 600;">Créer la première</a></td></tr>
                    <?php else: foreach ($properties as $prop):
                        $statusClass = match($prop['status']) {
                            'available' => 'status-available',
                            'sold' => 'status-sold',
                            'rented' => 'status-rented',
                            default => 'status-available'
                        };
                    ?>
                        <tr>
                            <td>
                                <div class="property-title"><?= htmlspecialchars($prop['title']) ?></div>
                                <div class="property-addr"><?= htmlspecialchars($prop['address'] ?? '') ?></div>
                            </td>
                            <td><?= htmlspecialchars($prop['type'] ?? '') ?></td>
                            <td><?= number_format((int)($prop['price'] ?? 0), 0, ',', ' ') ?> €</td>
                            <td><span class="status-badge <?= $statusClass ?>"><?= ucfirst($prop['status']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($prop['created_at'])) ?></td>
                            <td>
                                <div class="action-links">
                                    <a href="/admin?module=listings&action=edit&id=<?= $prop['id'] ?>" class="action-link">Éditer</a>
                                    <a href="#" onclick="deleteProperty(<?= $prop['id'] ?>); return false;" class="action-link delete-link">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    <?php } catch (Throwable $e) {
                        error_log('Listings Error: ' . $e->getMessage());
                    ?>
                        <tr><td colspan="6" class="empty-state">Erreur lors du chargement des propriétés</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deleteProperty(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette propriété ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="delete_property" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    <?php
}
