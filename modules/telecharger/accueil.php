<?php

declare(strict_types=1);

$pageTitle = 'Téléchargements';
$pageDescription = 'Ressources et documents';

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
        .downloads-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
        .download-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; text-align: center; transition: all .2s; }
        .download-card:hover { border-color: #c9a84c; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        .download-icon { font-size: 32px; margin-bottom: 8px; }
        .download-card h3 { margin: 0 0 8px; font-size: 14px; font-weight: 600; color: #0f172a; }
        .download-card p { margin: 0 0 12px; font-size: 12px; color: #64748b; }
        .download-btn { display: inline-block; padding: 6px 12px; background: #3498db; color: #fff; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .download-btn:hover { background: #2980b9; }
        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; grid-column: 1/-1; }
    </style>

    <div>
        <header class="hub-hero">
            <h1>Téléchargements</h1>
            <p>Ressources, modèles et documents à partager</p>
            <div class="hub-actions">
                <a href="/admin?module=telecharger&action=new" class="hub-btn hub-btn-primary">
                    <i class="fas fa-plus"></i> Ajouter ressource
                </a>
            </div>
        </header>

        <div class="downloads-grid">
            <?php
            try {
                $stmt = db()->prepare('SELECT id, title, description, file_type, created_at FROM resources ORDER BY created_at DESC LIMIT 12');
                $stmt->execute();
                $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($resources)):
            ?>
                <div class="empty-state">
                    <i class="fas fa-cloud-download-alt" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
                    <p>Aucune ressource disponible</p>
                    <a href="/admin?module=telecharger&action=new" class="hub-btn hub-btn-primary" style="margin-top: 16px;">
                        <i class="fas fa-plus"></i> Ajouter ressource
                    </a>
                </div>
            <?php else: foreach ($resources as $res):
                $icons = [
                    'pdf' => 'fas fa-file-pdf',
                    'doc' => 'fas fa-file-word',
                    'docx' => 'fas fa-file-word',
                    'xls' => 'fas fa-file-excel',
                    'xlsx' => 'fas fa-file-excel',
                    'zip' => 'fas fa-file-archive',
                    'jpg' => 'fas fa-file-image',
                    'png' => 'fas fa-file-image',
                ];
                $icon = $icons[$res['file_type']] ?? 'fas fa-file';
            ?>
                <div class="download-card">
                    <div class="download-icon"><i class="<?= $icon ?>"></i></div>
                    <h3><?= htmlspecialchars($res['title']) ?></h3>
                    <p><?= htmlspecialchars(substr($res['description'] ?? '', 0, 50)) ?></p>
                    <a href="/admin?module=telecharger&action=download&id=<?= $res['id'] ?>" class="download-btn">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                </div>
            <?php endforeach; endif; ?>
            <?php } catch (Throwable $e) {
                error_log('Downloads Error: ' . $e->getMessage());
            ?>
                <div class="empty-state">
                    <p>Erreur lors du chargement des ressources</p>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
