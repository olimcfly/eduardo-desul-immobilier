<?php

declare(strict_types=1);

// ── Gérer les actions ────────────────────────────────────────
$action = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['action'] ?? 'index'));

if ($action === 'vue') {
    require_once __DIR__ . '/vue.php';
} else {

$pageTitle = 'Contacts';
$pageDescription = 'Demandes reçues depuis le site';

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
        .hub-btn-secondary { background: rgba(255,255,255,.2); color: #fff; }
        .hub-btn-secondary:hover { background: rgba(255,255,255,.3); }
        .contacts-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .section-header { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: #f9fafb; display: flex; justify-content: space-between; align-items: center; }
        .section-header h2 { margin: 0; font-size: 16px; font-weight: 600; color: #0f172a; }
        .contacts-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .contacts-table th { padding: 12px 16px; text-align: left; font-weight: 600; color: #374151; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .contacts-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
        .contacts-table tr:hover { background: #f9fafb; }
        .contact-name { font-weight: 600; color: #0f172a; }
        .contact-email { font-size: 12px; color: #64748b; }
        .source-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .source-estimation { background: #dbeafe; color: #1e40af; }
        .source-contact { background: #ddd6fe; color: #5b21b6; }
        .source-telechargement { background: #dcfce7; color: #166534; }
        .source-financement { background: #fef08a; color: #713f12; }
        .source-avis_valeur { background: #cffafe; color: #0e7490; }
        .source-autre { background: #f3f4f6; color: #374151; }
        .stage-badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 10px; background: #f0f0f0; color: #666; }
        .empty-state { padding: 32px 16px; text-align: center; color: #9ca3af; }
        .action-links { display: flex; gap: 8px; }
        .action-link { padding: 4px 8px; text-decoration: none; color: #3498db; font-size: 12px; border-radius: 4px; }
        .action-link:hover { background: #ecf0f1; }
    </style>

    <div>
        <header class="hub-hero">
            <h1>Contacts</h1>
            <p>Les demandes reçues depuis le site, regroupées par contact.</p>
            <div class="hub-actions">
                <a href="/admin?module=crm-hub&action=conversions" class="hub-btn hub-btn-secondary">
                    <i class="fas fa-chart-bar"></i> Voir les conversions
                </a>
            </div>
        </header>

        <?php
        $srcFilter = strtolower(trim((string) ($_GET['source'] ?? '')));
        $validSrc  = ['estimation', 'contact', 'telechargement', 'financement', 'avis_valeur', 'autre'];
        if (!in_array($srcFilter, $validSrc, true)) {
            $srcFilter = '';
        }
        $qSearch = trim((string) ($_GET['q'] ?? ''));
        $qLike   = $qSearch !== '' ? '%' . $qSearch . '%' : '';
        ?>

        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;align-items:center">
            <span style="font-size:12px;font-weight:600;color:#64748b;margin-right:4px">Source :</span>
            <a href="/admin?module=contacts<?= $qSearch !== '' ? '&amp;q=' . rawurlencode($qSearch) : '' ?>" class="source-badge <?= $srcFilter === '' ? 'source-contact' : '' ?>" style="text-decoration:none;<?= $srcFilter === '' ? 'outline:2px solid #1a3c5e' : 'opacity:.85' ?>">Toutes</a>
            <?php foreach (['financement' => 'Financement', 'avis_valeur' => 'Avis de valeur', 'estimation' => 'Estimation', 'contact' => 'Contact', 'telechargement' => 'Téléchargement', 'autre' => 'Autre'] as $sk => $lab): ?>
                <a href="/admin?module=contacts&amp;source=<?= e($sk) ?><?= $qSearch !== '' ? '&amp;q=' . rawurlencode($qSearch) : '' ?>"
                   class="source-badge source-<?= e($sk) ?>"
                   style="text-decoration:none;<?= $srcFilter === $sk ? 'outline:2px solid #1a3c5e;font-weight:700' : '' ?>"><?= e($lab) ?></a>
            <?php endforeach; ?>
        </div>
        <form method="get" action="/admin" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;align-items:center">
            <input type="hidden" name="module" value="contacts">
            <?php if ($srcFilter !== ''): ?><input type="hidden" name="source" value="<?= e($srcFilter) ?>"><?php endif; ?>
            <label for="lead-q" class="sr-only">Rechercher</label>
            <input id="lead-q" name="q" type="search" value="<?= e($qSearch) ?>" placeholder="E-mail, prénom ou nom…" style="min-width:220px;max-width:360px;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px">
            <button type="submit" class="hub-btn hub-btn-primary" style="padding:8px 16px;font-size:14px">Rechercher</button>
        </form>

        <div class="contacts-section">
            <div class="section-header">
                <h2>📇 Contacts reçus</h2>
            </div>
            <table class="contacts-table">
                <thead>
                    <tr>
                        <th>Nom & Email</th>
                        <th>Téléphone</th>
                        <th>Source</th>
                        <th>Intent/Propriété</th>
                        <th>Créé</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $sql = 'SELECT id, first_name, last_name, email, phone, source_type, stage, intent, property_type, created_at FROM crm_leads WHERE 1=1';
                        $params = [];
                        if ($srcFilter !== '') {
                            $sql .= ' AND source_type = ?';
                            $params[] = $srcFilter;
                        }
                        if ($qLike !== '') {
                            $sql .= ' AND (email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)';
                            $params[] = $qLike;
                            $params[] = $qLike;
                            $params[] = $qLike;
                        }
                        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT 300';
                        $stmt = db()->prepare($sql);
                        $stmt->execute($params);
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $leads = [];
                        $seenContacts = [];
                        foreach ($rows as $row) {
                            $emailKey = strtolower(trim((string) ($row['email'] ?? '')));
                            $phoneKey = preg_replace('/\D+/', '', (string) ($row['phone'] ?? ''));
                            $contactKey = $emailKey !== ''
                                ? 'email:' . $emailKey
                                : ($phoneKey !== '' ? 'phone:' . $phoneKey : 'id:' . (string) ($row['id'] ?? ''));

                            if (isset($seenContacts[$contactKey])) {
                                continue;
                            }

                            $seenContacts[$contactKey] = true;
                            $leads[] = $row;

                            if (count($leads) >= 100) {
                                break;
                            }
                        }

                        if (empty($leads)):
                    ?>
                        <tr><td colspan="6" class="empty-state">Aucun contact pour le moment</td></tr>
                    <?php else:
                        $sourceIcons = [
                            'estimation' => '📊',
                            'contact' => '💬',
                            'telechargement' => '📚',
                            'financement' => '💰',
                            'avis_valeur' => '📋',
                            'autre' => '•'
                        ];

                        foreach ($leads as $lead):
                            $sourceType = $lead['source_type'] ?? 'autre';
                            $sourceIcon = $sourceIcons[$sourceType] ?? '•';
                    ?>
                        <tr>
                            <td>
                                <div class="contact-name"><?= htmlspecialchars(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')) ?></div>
                                <div class="contact-email"><?= htmlspecialchars($lead['email'] ?? '') ?></div>
                            </td>
                            <td><?= htmlspecialchars($lead['phone'] ?? '—') ?></td>
                            <td>
                                <span class="source-badge source-<?= htmlspecialchars($sourceType) ?>">
                                    <?= $sourceIcon ?> <?= htmlspecialchars(ucfirst($sourceType)) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: #666;">
                                <?php if ($lead['intent']): ?>
                                    <strong><?= htmlspecialchars(substr($lead['intent'], 0, 25)) ?></strong>
                                <?php endif; ?>
                                <?php if ($lead['property_type']): ?>
                                    <div><?= htmlspecialchars($lead['property_type']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: #888;">
                                <?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?>
                            </td>
                            <td>
                                <div class="action-links">
                                    <a href="/admin?module=contacts&action=vue&id=<?= $lead['id'] ?>" class="action-link">Voir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    <?php } catch (Throwable $e) {
                        error_log('Leads Error: ' . $e->getMessage());
                    ?>
                        <tr><td colspan="6" class="empty-state">Erreur lors du chargement des leads</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deleteContact(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce lead ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="delete_lead" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    <?php
}

} // Fermer la condition else
