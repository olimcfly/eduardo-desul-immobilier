<?php
declare(strict_types=1);

$storageFile = dirname(__DIR__) . '/storage/checklists/verification-modules.json';
$token = (string)($_GET['token'] ?? '');

$data = null;
if (is_file($storageFile)) {
    $raw = file_get_contents($storageFile);
    if (is_string($raw) && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
}

$allowed = is_array($data)
    && !empty($data['token'])
    && is_string($data['token'])
    && hash_equals((string)$data['token'], $token);

http_response_code($allowed ? 200 : 403);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist verification - Partage</title>
    <style>
        body { font-family: Inter, Arial, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 900px; margin: 0 auto; display: grid; gap: 14px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; }
        h1 { margin: 0 0 6px; font-size: 26px; }
        p { margin: 0; color: #475569; }
        .item { border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 12px; }
        .meta { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px; }
        .module { text-transform: uppercase; letter-spacing: .06em; font-weight: 700; color: #334155; }
        .status { border-radius: 999px; padding: 4px 10px; font-weight: 700; }
        .status-todo { background: #eef2ff; color: #3730a3; }
        .status-agentrequested { background: #fee2e2; color: #991b1b; }
        .status-reviewing { background: #e0f2fe; color: #0c4a6e; }
        .status-verificationdone { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-blocked { background: #fee2e2; color: #991b1b; }
        code { background: #0b1020; color: #e2e8f0; display: block; border-radius: 8px; padding: 8px; overflow-x: auto; margin-top: 8px; }
    </style>
</head>
<body>
<div class="wrap">
    <?php if (!$allowed): ?>
        <div class="card">
            <h1>Acces refuse</h1>
            <p>Le lien de partage est invalide ou expire.</p>
        </div>
    <?php else: ?>
        <div class="card">
            <h1>Checklist verification client</h1>
            <p>Vue partagee en lecture seule. Derniere mise a jour: <?= htmlspecialchars((string)($data['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="card">
            <?php
            $items = $data['items'] ?? [];
            if (!is_array($items) || $items === []) {
                echo '<p>Aucune note pour le moment.</p>';
            } else {
                foreach (array_reverse($items) as $item) {
                    $status = (string)($item['status'] ?? 'todo');
                    $module = (string)($item['module'] ?? 'general');
                    $note = (string)($item['note'] ?? '');
                    $command = (string)($item['command'] ?? '');
                    $statusClass = 'status-' . preg_replace('/[^a-z]/', '', $status);
                    $statusLabel = match ($status) {
                        'agent_requested' => 'Agent demande',
                        'reviewing' => 'En verification',
                        'verification_done' => 'Verification faite',
                        'approved' => 'Valide',
                        'blocked' => 'Bloque',
                        default => 'A faire',
                    };
                    echo '<div class="item">';
                    echo '<div class="meta"><span class="module">' . htmlspecialchars($module, ENT_QUOTES, 'UTF-8') . '</span>';
                    echo '<span class="status ' . htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') . '</span></div>';
                    echo '<div>' . nl2br(htmlspecialchars($note, ENT_QUOTES, 'UTF-8')) . '</div>';
                    if ($command !== '') {
                        echo '<code>' . htmlspecialchars($command, ENT_QUOTES, 'UTF-8') . '</code>';
                    }
                    echo '</div>';
                }
            }
            ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
