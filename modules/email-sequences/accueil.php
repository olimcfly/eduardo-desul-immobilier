<?php

declare(strict_types=1);

$action = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['action'] ?? 'index'));

if ($action === 'set_view') {
    $viewType = sanitizeString($_POST['view_type'] ?? 'list');
    if (in_array($viewType, ['card', 'list'])) {
        $_SESSION['sequences_view_type'] = $viewType;
    }
    header('Location: /admin?module=email-sequences');
    exit;
}

if ($action === 'new' || $action === 'create') {
    require_once __DIR__ . '/new.php';
    return;
}

if ($action === 'edit') {
    require_once __DIR__ . '/edit.php';
    return;
}

if ($action === 'get-email') {
    header('Content-Type: application/json');
    $emailId = (int)($_GET['id'] ?? 0);

    try {
        $stmt = db()->prepare('SELECT id, email_number, subject, body_html, preview_text FROM email_sequence_emails WHERE id = ?');
        $stmt->execute([$emailId]);
        $email = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($email) {
            echo json_encode($email);
        } else {
            echo json_encode(['error' => 'Email not found']);
        }
    } catch (Throwable $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'update-email') {
    header('Content-Type: application/json');
    $emailId = (int)($_POST['email_id'] ?? 0);
    $subject = sanitizeString($_POST['subject'] ?? '');
    $body = sanitizeString($_POST['body_html'] ?? '');
    $preview = sanitizeString($_POST['preview'] ?? '');

    try {
        $success = EmailSequenceService::updateSequenceEmail($emailId, $subject, $body, $preview);
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'update-destination') {
    header('Content-Type: application/json');
    $sequenceId = (int)($_POST['sequence_id'] ?? 0);
    $destinationType = sanitizeString($_POST['destination_type'] ?? '');
    $destinationUrl = sanitizeString($_POST['destination_url'] ?? '');
    $destinationLabel = sanitizeString($_POST['destination_label'] ?? '');
    $destinationContactType = sanitizeString($_POST['destination_contact_type'] ?? '');

    try {
        $success = EmailSequenceService::updateSequenceDestination(
            $sequenceId,
            $destinationType ?: null,
            $destinationUrl ?: null,
            $destinationLabel ?: null,
            $destinationContactType ?: null
        );
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'activate') {
    header('Content-Type: application/json');
    $sequenceId = (int)($_GET['id'] ?? 0);

    try {
        $success = EmailSequenceService::activateSequence($sequenceId);
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'deactivate') {
    header('Content-Type: application/json');
    $sequenceId = (int)($_GET['id'] ?? 0);

    try {
        $success = EmailSequenceService::deactivateSequence($sequenceId);
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// N'afficher la liste que si action est 'index' (pas new, edit, create, etc)
if ($action === 'index' || $action === '') {
    $pageTitle = 'Séquences Email';
    $pageDescription = 'Automations et campagnes d\'email';

    // Initialiser la préférence de vue (par défaut: list)
    if (!isset($_SESSION['sequences_view_type'])) {
        $_SESSION['sequences_view_type'] = 'list';
    }
    $viewType = $_SESSION['sequences_view_type'];

    function renderContent(): void {
        global $viewType;
        ?>
    <style>
        .hub-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
        .hub-hero h1 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .hub-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; }
        .hub-actions { display: flex; gap: 12px; margin-top: 16px; align-items: center; }
        .hub-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all .2s; }
        .hub-btn-primary { background: #c9a84c; color: #10253c; }
        .hub-btn-primary:hover { background: #b8962d; }
        .view-toggle { display: flex; gap: 4px; border: 1px solid rgba(255,255,255,.3); border-radius: 6px; padding: 2px; }
        .view-btn { padding: 8px 12px; border: none; background: transparent; color: rgba(255,255,255,.7); font-weight: 600; cursor: pointer; border-radius: 4px; transition: all .2s; font-size: 12px; }
        .view-btn.active { background: rgba(255,255,255,.2); color: #fff; }
        .view-btn:hover { color: #fff; }
        .sequences-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .sequences-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .sequences-table th { background: #f3f4f6; padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; }
        .sequences-table td { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; }
        .sequences-table tbody tr:hover { background: #f9fafb; }
        .sequences-table tbody tr:last-child td { border-bottom: none; }
        .sequence-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; transition: all .2s; }
        .sequence-card:hover { border-color: #c9a84c; box-shadow: 0 4px 12px rgba(201,168,76,.15); }
        .sequence-card h3 { margin: 0 0 8px; font-size: 15px; font-weight: 600; color: #0f172a; }
        .sequence-card p { margin: 0 0 12px; font-size: 13px; color: #64748b; }
        .sequence-badge { display: inline-block; padding: 4px 8px; background: #dbeafe; color: #1d4ed8; border-radius: 4px; font-size: 11px; font-weight: 600; margin-bottom: 12px; }
        .sequence-actions { display: flex; gap: 8px; }
        .sequence-btn { padding: 6px 12px; border-radius: 6px; border: none; font-size: 12px; font-weight: 600; cursor: pointer; }
        .sequence-btn-primary { background: #3498db; color: #fff; }
        .sequence-btn-primary:hover { background: #2980b9; }
        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; }
    </style>

    <div>
        <header class="hub-hero">
            <h1>Séquences Email</h1>
            <p>Créez et gérez vos automations d'email marketing</p>
            <div class="hub-actions">
                <a href="/admin?module=email-sequences&action=new" class="hub-btn hub-btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle séquence
                </a>
                <form method="POST" style="display: none;" id="viewToggleForm">
                    <input type="hidden" name="view_type" id="viewTypeInput">
                </form>
                <div class="view-toggle">
                    <button type="button" class="view-btn <?= $viewType === 'list' ? 'active' : '' ?>" onclick="setView('list')" title="Vue liste">
                        <i class="fas fa-list"></i>
                    </button>
                    <button type="button" class="view-btn <?= $viewType === 'card' ? 'active' : '' ?>" onclick="setView('card')" title="Vue cartes">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
            </div>
        </header>

        <?php if ($viewType === 'list'): ?>
        <table class="sequences-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th style="width: 150px;">Créée le</th>
                    <th style="width: 100px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            try {
                $stmt = db()->prepare('SELECT id, name, description, status, created_at FROM email_sequences ORDER BY created_at DESC LIMIT 12');
                $stmt->execute();
                $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($sequences)):
            ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                        <i class="fas fa-envelope" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
                        <p style="margin: 0;">Aucune séquence email créée</p>
                        <a href="/admin?module=email-sequences&action=new" class="hub-btn hub-btn-primary" style="margin-top: 16px;">
                            <i class="fas fa-plus"></i> Créer la première séquence
                        </a>
                    </td>
                </tr>
            <?php else: foreach ($sequences as $seq):
                $statusClass = $seq['status'] === 'active' ? 'dbeafe' : 'fee2e2';
                $statusColor = $seq['status'] === 'active' ? '#1d4ed8' : '#dc2626';
                $statusLabel = $seq['status'] === 'active' ? '🟢 Actif' : '🔴 Inactif';
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($seq['name']) ?></strong></td>
                    <td><?= htmlspecialchars(substr($seq['description'], 0, 60)) ?></td>
                    <td><span class="sequence-badge" style="background: #<?= $statusClass ?>; color: <?= $statusColor ?>;"><?= htmlspecialchars($statusLabel) ?></span></td>
                    <td><small style="color: #9ca3af;"><?= date('d/m/Y', strtotime($seq['created_at'])) ?></small></td>
                    <td style="text-align: right;">
                        <a href="/admin?module=email-sequences&action=edit&id=<?= $seq['id'] ?>" class="sequence-btn sequence-btn-primary">✏️ Éditer</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            <?php } catch (Throwable $e) {
                error_log('Email Sequences Error: ' . $e->getMessage());
            ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #9ca3af;">
                        Erreur lors du chargement des séquences
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="sequences-grid">
            <?php
            try {
                $stmt = db()->prepare('SELECT id, name, description, status, created_at FROM email_sequences ORDER BY created_at DESC LIMIT 12');
                $stmt->execute();
                $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($sequences)):
            ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-envelope" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
                    <p>Aucune séquence email créée</p>
                    <a href="/admin?module=email-sequences&action=new" class="hub-btn hub-btn-primary" style="margin-top: 16px;">
                        <i class="fas fa-plus"></i> Créer la première séquence
                    </a>
                </div>
            <?php else: foreach ($sequences as $seq):
                $statusClass = $seq['status'] === 'active' ? 'dbeafe' : 'fee2e2';
                $statusColor = $seq['status'] === 'active' ? '#1d4ed8' : '#dc2626';
            ?>
                <div class="sequence-card">
                    <span class="sequence-badge" style="background: #<?= $statusClass ?>; color: <?= $statusColor ?>;">
                        <?= ucfirst($seq['status']) ?>
                    </span>
                    <h3><?= htmlspecialchars($seq['name']) ?></h3>
                    <p><?= htmlspecialchars(substr($seq['description'], 0, 60)) ?></p>
                    <small style="color: #9ca3af; display: block; margin-bottom: 12px;">Créée le <?= date('d/m/Y', strtotime($seq['created_at'])) ?></small>
                    <div class="sequence-actions">
                        <a href="/admin?module=email-sequences&action=edit&id=<?= $seq['id'] ?>" class="sequence-btn sequence-btn-primary">Éditer</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
            <?php
            } catch (Throwable $e) {
                error_log('Email Sequences Error: ' . $e->getMessage());
            ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <p>Erreur lors du chargement des séquences</p>
                </div>
            <?php } ?>
        </div>
        <?php endif; ?>
    </div>
    <script>
    function setView(viewType) {
        document.getElementById('viewTypeInput').value = viewType;
        const form = document.getElementById('viewToggleForm');
        form.action = '/admin?module=email-sequences&action=set_view';
        form.submit();
    }
    </script>
    <?php
    }
}
