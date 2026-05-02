<?php
declare(strict_types=1);

$pageTitle = 'Checklist verification';
$pageDescription = 'Notes agent, commandes et suivi des modifications.';

if (!function_exists('checklistStoragePath')) {
    function checklistStoragePath(): string
    {
        $dir = ROOT_PATH . '/storage/checklists';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . '/verification-modules.json';
    }
}

if (!function_exists('checklistDefaultData')) {
    function checklistDefaultData(): array
    {
        return [
            'title' => 'Verification modules',
            'token' => bin2hex(random_bytes(16)),
            'updated_at' => date('c'),
            'items' => [],
        ];
    }
}

if (!function_exists('checklistLoadData')) {
    function checklistLoadData(): array
    {
        $path = checklistStoragePath();
        if (!is_file($path)) {
            $data = checklistDefaultData();
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

            return $data;
        }

        $raw = file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            return checklistDefaultData();
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return checklistDefaultData();
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            $data['items'] = [];
        }
        if (empty($data['token']) || !is_string($data['token'])) {
            $data['token'] = bin2hex(random_bytes(16));
        }
        if (empty($data['title']) || !is_string($data['title'])) {
            $data['title'] = 'Verification modules';
        }

        return $data;
    }
}

if (!function_exists('checklistSaveData')) {
    function checklistSaveData(array $data): void
    {
        $data['updated_at'] = date('c');
        file_put_contents(
            checklistStoragePath(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}

if (!function_exists('checklistStatusLabel')) {
    function checklistStatusLabel(string $status): string
    {
        return match ($status) {
            'agent_requested' => 'Agent demande',
            'verification_done' => 'Verification faite',
            'reviewing' => 'En verification',
            'approved' => 'Valide',
            'blocked' => 'Bloque',
            default => 'A faire',
        };
    }
}

if (!function_exists('checklistStatusClass')) {
    function checklistStatusClass(string $status): string
    {
        return match ($status) {
            'agent_requested' => 'status-agent-requested',
            'verification_done' => 'status-verification-done',
            'reviewing' => 'status-reviewing',
            'approved' => 'status-approved',
            'blocked' => 'status-blocked',
            default => 'status-todo',
        };
    }
}

if (!function_exists('checklistJobsPath')) {
    function checklistJobsPath(): string
    {
        $dir = ROOT_PATH . '/storage/checklists';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . '/agent-jobs.json';
    }
}

if (!function_exists('checklistLoadJobs')) {
    function checklistLoadJobs(): array
    {
        $path = checklistJobsPath();
        if (!is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $jobs = json_decode($raw, true);
        if (!is_array($jobs)) {
            return [];
        }

        return $jobs;
    }
}

if (!function_exists('checklistSaveJobs')) {
    function checklistSaveJobs(array $jobs): void
    {
        file_put_contents(
            checklistJobsPath(),
            json_encode($jobs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}

if (!function_exists('checklistQueueAgentJob')) {
    function checklistQueueAgentJob(array &$item): void
    {
        $jobs = checklistLoadJobs();
        $jobId = 'job_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);

        $jobs[] = [
            'job_id' => $jobId,
            'item_id' => (string)($item['id'] ?? ''),
            'module' => (string)($item['module'] ?? ''),
            'note' => (string)($item['note'] ?? ''),
            'command' => (string)($item['command'] ?? ''),
            'status' => 'queued',
            'created_at' => date('c'),
        ];
        checklistSaveJobs($jobs);

        $item['agent_job_id'] = $jobId;
        $item['agent_state'] = 'queued';
        $item['agent_status_text'] = 'Demande envoyee a l agent (en attente de prise en charge).';
        $item['agent_updated_at'] = date('c');
    }
}

if (!function_exists('checklistAgentStateLabel')) {
    function checklistAgentStateLabel(string $state): string
    {
        return match ($state) {
            'queued' => 'En attente agent',
            'picked' => 'Pris en charge',
            'running' => 'En cours',
            'done' => 'Termine',
            'failed' => 'Echec',
            default => 'Non lance',
        };
    }
}

if (!function_exists('checklistAgentStateClass')) {
    function checklistAgentStateClass(string $state): string
    {
        return match ($state) {
            'queued' => 'agent-queued',
            'picked' => 'agent-picked',
            'running' => 'agent-running',
            'done' => 'agent-done',
            'failed' => 'agent-failed',
            default => 'agent-idle',
        };
    }
}

if (!function_exists('checklistDispatchWorker')) {
    function checklistDispatchWorker(): bool
    {
        $script = ROOT_PATH . '/scripts/checklist-agent-worker.php';
        if (!is_file($script)) {
            return false;
        }

        $phpBin = defined('PHP_BINARY') && PHP_BINARY !== '' ? PHP_BINARY : 'php';
        $cmd = sprintf(
            '%s %s --max-jobs=1 > /dev/null 2>&1 &',
            escapeshellarg($phpBin),
            escapeshellarg($script)
        );

        @exec($cmd, $output, $exitCode);

        return $exitCode === 0;
    }
}

if (!function_exists('checklistModulesList')) {
    function checklistModulesList(): array
    {
        $modulesDir = ROOT_PATH . '/modules';
        if (!is_dir($modulesDir)) {
            return [];
        }

        $entries = scandir($modulesDir);
        if (!is_array($entries)) {
            return [];
        }

        $modules = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            $path = $modulesDir . '/' . $entry;
            if (!is_dir($path) || !is_file($path . '/accueil.php')) {
                continue;
            }

            $modules[] = $entry;
        }

        sort($modules, SORT_NATURAL | SORT_FLAG_CASE);

        return $modules;
    }
}

if (!function_exists('renderContent')) {
    function renderContent(): void
    {
        $data = checklistLoadData();
        $modules = checklistModulesList();
        $notice = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = (string)($_POST['action'] ?? '');

            if ($action === 'add') {
                $module = trim((string)($_POST['module'] ?? ''));
                $note = trim((string)($_POST['note'] ?? ''));
                $command = trim((string)($_POST['command'] ?? ''));

                if ($module !== '' && $note !== '') {
                    $data['items'][] = [
                        'id' => bin2hex(random_bytes(8)),
                        'module' => $module,
                        'note' => $note,
                        'command' => $command,
                        'status' => 'todo',
                        'agent_state' => 'idle',
                        'agent_status_text' => 'Aucun lancement agent.',
                        'created_at' => date('c'),
                    ];
                    checklistSaveData($data);
                    $notice = 'Note ajoutee.';
                } else {
                    $notice = 'Selectionne un module et ajoute une demande.';
                }
            }

            if ($action === 'set_status') {
                $id = (string)($_POST['id'] ?? '');
                $status = (string)($_POST['status'] ?? 'todo');
                $allowed = ['todo', 'agent_requested', 'reviewing', 'verification_done', 'approved', 'blocked'];
                if (!in_array($status, $allowed, true)) {
                    $status = 'todo';
                }

                foreach ($data['items'] as &$item) {
                    if (($item['id'] ?? '') === $id) {
                        $item['status'] = $status;
                        $item['updated_at'] = date('c');
                        break;
                    }
                }
                unset($item);
                checklistSaveData($data);
                $notice = 'Statut mis a jour.';
            }

            if ($action === 'launch_agent') {
                $id = (string)($_POST['id'] ?? '');
                $found = false;
                foreach ($data['items'] as &$item) {
                    if (($item['id'] ?? '') === $id) {
                        checklistQueueAgentJob($item);
                        $item['status'] = 'agent_requested';
                        $item['updated_at'] = date('c');
                        $found = true;
                        break;
                    }
                }
                unset($item);
                checklistSaveData($data);
                if ($found) {
                    $dispatched = checklistDispatchWorker();
                    $notice = $dispatched
                        ? 'Job agent cree. Traitement lance en arriere-plan.'
                        : 'Job agent cree. Worker non demarre automatiquement (lancer via cron/dispatch).';
                } else {
                    $notice = 'Element introuvable.';
                }
            }

            if ($action === 'set_agent_state') {
                $id = (string)($_POST['id'] ?? '');
                $state = (string)($_POST['agent_state'] ?? 'idle');
                $allowedStates = ['idle', 'queued', 'picked', 'running', 'done', 'failed'];
                if (!in_array($state, $allowedStates, true)) {
                    $state = 'idle';
                }
                foreach ($data['items'] as &$item) {
                    if (($item['id'] ?? '') === $id) {
                        $item['agent_state'] = $state;
                        $item['agent_status_text'] = match ($state) {
                            'picked' => 'Agent a pris la tache.',
                            'running' => 'Agent en cours de modification.',
                            'done' => 'Agent a termine la modification.',
                            'failed' => 'Agent a echoue, verifier les logs.',
                            'queued' => 'En attente de prise en charge.',
                            default => 'Aucun lancement agent.',
                        };
                        $item['agent_updated_at'] = date('c');
                        break;
                    }
                }
                unset($item);
                checklistSaveData($data);
                $notice = 'Etat agent mis a jour.';
            }

            if ($action === 'delete') {
                $id = (string)($_POST['id'] ?? '');
                $data['items'] = array_values(array_filter(
                    $data['items'],
                    static fn(array $item): bool => ($item['id'] ?? '') !== $id
                ));
                checklistSaveData($data);
                $notice = 'Note supprimee.';
            }
        }

        $shareLink = '/public/checklist-share.php?token=' . rawurlencode((string)$data['token']);
        ?>
        <div class="checklist-wrap">
            <div class="checklist-head">
                <h1>Checklist verification client</h1>
                <p>Selectionne un module, note la demande client, puis suis le workflow: Agent -> Verification -> Validation finale.</p>
            </div>

            <?php if ($notice !== ''): ?>
                <div class="checklist-notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="checklist-share">
                <label>Lien partageable (lecture seule client)</label>
                <div class="share-row">
                    <input id="share-link-input" type="text" readonly value="<?= htmlspecialchars($shareLink, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="button" onclick="copyShareLink()">Copier le lien</button>
                    <button type="button" onclick="dispatchAgentWorker()">Lancer worker</button>
                </div>
            </div>

            <div class="modules-reference">
                <label>Modules disponibles (precharges)</label>
                <div class="module-chips">
                    <?php foreach ($modules as $moduleName): ?>
                        <span class="module-chip"><?= htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="post" class="checklist-form">
                <input type="hidden" name="action" value="add">
                <select name="module" required>
                    <option value="">Choisir un module...</option>
                    <?php foreach ($modules as $moduleName): ?>
                        <option value="<?= htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="note" rows="3" placeholder="Note agent / bug / demande client"></textarea>
                <textarea name="command" rows="2" placeholder="Commande ou action a executer (optionnel)"></textarea>
                <button type="submit">Ajouter la note</button>
            </form>

            <div class="checklist-list">
                <?php if (empty($data['items'])): ?>
                    <div class="empty-state">Aucune note pour le moment.</div>
                <?php else: ?>
                    <?php foreach (array_reverse($data['items']) as $item): ?>
                        <?php
                        $status = (string)($item['status'] ?? 'todo');
                        $module = (string)($item['module'] ?? 'general');
                        $note = (string)($item['note'] ?? '');
                        $command = (string)($item['command'] ?? '');
                        $id = (string)($item['id'] ?? '');
                        ?>
                        <div class="item-card">
                            <div class="item-meta">
                                <span class="module"><?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="status <?= checklistStatusClass($status) ?>"><?= checklistStatusLabel($status) ?></span>
                            </div>

                            <div class="item-note"><?= nl2br(htmlspecialchars($note, ENT_QUOTES, 'UTF-8')) ?></div>

                            <?php
                            $agentState = (string)($item['agent_state'] ?? 'idle');
                            $agentStatusText = (string)($item['agent_status_text'] ?? 'Aucun lancement agent.');
                            $agentJobId = (string)($item['agent_job_id'] ?? '');
                            $agentUpdatedAt = (string)($item['agent_updated_at'] ?? '');
                            ?>
                            <div class="agent-box <?= checklistAgentStateClass($agentState) ?>">
                                <div class="agent-box-head">
                                    <strong>Etat agent:</strong> <?= htmlspecialchars(checklistAgentStateLabel($agentState), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="agent-box-text"><?= htmlspecialchars($agentStatusText, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($agentJobId !== ''): ?>
                                    <div class="agent-box-meta">Job ID: <code><?= htmlspecialchars($agentJobId, ENT_QUOTES, 'UTF-8') ?></code></div>
                                <?php endif; ?>
                                <?php if ($agentUpdatedAt !== ''): ?>
                                    <div class="agent-box-meta">Maj: <?= htmlspecialchars($agentUpdatedAt, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>

                            <?php if ($command !== ''): ?>
                                <div class="item-command">
                                    <code><?= htmlspecialchars($command, ENT_QUOTES, 'UTF-8') ?></code>
                                </div>
                            <?php endif; ?>

                            <div class="item-actions">
                                <?php if ($command !== ''): ?>
                                    <button type="button" class="btn-light" onclick="copyCommand(this)" data-command="<?= htmlspecialchars($command, ENT_QUOTES, 'UTF-8') ?>">
                                        Copier commande
                                    </button>
                                <?php endif; ?>

                                <form method="post">
                                    <input type="hidden" name="action" value="launch_agent">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn-light">Lancer agent</button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="set_status">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="status" value="reviewing">
                                    <button type="submit" class="btn-light">Verifier</button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="set_agent_state">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="agent_state" value="picked">
                                    <button type="submit" class="btn-light">Agent pris en charge</button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="set_agent_state">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="agent_state" value="running">
                                    <button type="submit" class="btn-light">Agent en cours</button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="set_agent_state">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="agent_state" value="done">
                                    <button type="submit" class="btn-light">Agent termine</button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="set_status">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="status" value="verification_done">
                                    <button type="submit" class="btn-primary">Verification faite</button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="set_status">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn-success">Je valide</button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="set_status">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="status" value="blocked">
                                    <button type="submit" class="btn-warning">Bloque</button>
                                </form>

                                <form method="post" onsubmit="return confirm('Supprimer cette note ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <style>
            .checklist-wrap { display: grid; gap: 16px; max-width: 980px; }
            .checklist-head h1 { margin: 0 0 6px; font-size: 26px; }
            .checklist-head p { margin: 0; color: #475569; }
            .checklist-notice { background: #e0f2fe; color: #0c4a6e; border: 1px solid #bae6fd; padding: 10px 12px; border-radius: 8px; }
            .checklist-share, .checklist-form, .item-card, .modules-reference {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 14px;
            }
            .checklist-share label { display: block; margin-bottom: 8px; font-weight: 600; color: #0f172a; }
            .share-row { display: flex; gap: 10px; }
            .share-row input {
                flex: 1; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; font-size: 13px;
            }
            .modules-reference label { display: block; margin-bottom: 10px; font-weight: 600; color: #0f172a; }
            .module-chips { display: flex; flex-wrap: wrap; gap: 8px; }
            .module-chip {
                background: #eef2ff;
                color: #1e3a8a;
                border: 1px solid #c7d2fe;
                border-radius: 999px;
                font-size: 12px;
                padding: 5px 10px;
                font-weight: 600;
            }
            .share-row button, .checklist-form button { border: 0; background: #2563eb; color: #fff; border-radius: 8px; padding: 10px 14px; cursor: pointer; font-weight: 600; }
            .checklist-form { display: grid; gap: 10px; }
            .checklist-form input, .checklist-form textarea, .checklist-form select {
                width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; font-size: 14px;
            }
            .checklist-list { display: grid; gap: 12px; }
            .empty-state { color: #64748b; padding: 20px; text-align: center; }
            .item-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
            .module { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: #334155; font-weight: 700; }
            .status { font-size: 12px; border-radius: 999px; padding: 4px 10px; font-weight: 700; }
            .status-todo { background: #eef2ff; color: #3730a3; }
            .status-agent-requested { background: #fee2e2; color: #991b1b; }
            .status-reviewing { background: #e0f2fe; color: #0c4a6e; }
            .status-verification-done { background: #fef3c7; color: #92400e; }
            .status-approved { background: #dcfce7; color: #166534; }
            .status-blocked { background: #fee2e2; color: #991b1b; }
            .item-note { color: #0f172a; margin-bottom: 10px; }
            .agent-box { border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 8px; padding: 10px; margin-bottom: 10px; }
            .agent-box-head { font-size: 13px; margin-bottom: 4px; color: #0f172a; }
            .agent-box-text { font-size: 13px; color: #334155; }
            .agent-box-meta { margin-top: 6px; font-size: 12px; color: #64748b; }
            .agent-idle { border-color: #cbd5e1; background: #f8fafc; }
            .agent-queued { border-color: #fca5a5; background: #fff1f2; }
            .agent-picked { border-color: #93c5fd; background: #eff6ff; }
            .agent-running { border-color: #fde68a; background: #fffbeb; }
            .agent-done { border-color: #86efac; background: #f0fdf4; }
            .agent-failed { border-color: #fca5a5; background: #fef2f2; }
            .item-command { background: #0b1020; color: #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 10px; overflow-x: auto; }
            .item-actions { display: flex; flex-wrap: wrap; gap: 8px; }
            .item-actions form { margin: 0; }
            .item-actions button { border: 0; border-radius: 8px; padding: 8px 12px; cursor: pointer; font-size: 13px; }
            .btn-primary { background: #16a34a; color: #fff; }
            .btn-success { background: #15803d; color: #fff; }
            .btn-light { background: #e2e8f0; color: #0f172a; }
            .btn-warning { background: #f59e0b; color: #111827; }
            .btn-danger { background: #ef4444; color: #fff; }
        </style>

        <script>
            function copyShareLink() {
                const input = document.getElementById('share-link-input');
                if (!input) return;
                input.select();
                input.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(input.value).then(() => {
                    alert('Lien copie');
                });
            }

            function copyCommand(button) {
                const cmd = button.getAttribute('data-command') || '';
                if (!cmd) return;
                navigator.clipboard.writeText(cmd).then(() => {
                    button.textContent = 'Commande copiee';
                    setTimeout(() => { button.textContent = 'Copier commande'; }, 1200);
                });
            }

            function dispatchAgentWorker() {
                fetch('/admin/api/checklist/dispatch.php', { method: 'POST' })
                    .then(r => r.json())
                    .then(data => {
                        alert(data.success ? 'Worker lance.' : ('Erreur: ' + (data.error || 'dispatch')));
                    })
                    .catch(() => alert('Erreur reseau dispatch worker'));
            }
        </script>
        <?php
    }
}
