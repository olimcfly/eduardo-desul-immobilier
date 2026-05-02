<?php
declare(strict_types=1);

/**
 * Worker checklist (cron-ready)
 * Usage:
 *   php scripts/checklist-agent-worker.php
 *   php scripts/checklist-agent-worker.php --max-jobs=3
 */

const CHECKLIST_STORAGE_DIR = __DIR__ . '/../storage/checklists';
const CHECKLIST_DATA_FILE = CHECKLIST_STORAGE_DIR . '/verification-modules.json';
const CHECKLIST_JOBS_FILE = CHECKLIST_STORAGE_DIR . '/agent-jobs.json';
const CHECKLIST_WORKER_LOG = CHECKLIST_STORAGE_DIR . '/agent-worker.log';

if (!is_dir(CHECKLIST_STORAGE_DIR)) {
    @mkdir(CHECKLIST_STORAGE_DIR, 0775, true);
}

function logLine(string $message): void
{
    $line = sprintf("[%s] %s\n", date('c'), $message);
    @file_put_contents(CHECKLIST_WORKER_LOG, $line, FILE_APPEND | LOCK_EX);
}

function loadJsonArray(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function saveJsonArray(string $path, array $payload): void
{
    file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function loadChecklistData(): array
{
    $data = loadJsonArray(CHECKLIST_DATA_FILE);
    if (!isset($data['items']) || !is_array($data['items'])) {
        $data['items'] = [];
    }
    return $data;
}

function saveChecklistData(array $data): void
{
    $data['updated_at'] = date('c');
    saveJsonArray(CHECKLIST_DATA_FILE, $data);
}

function loadJobs(): array
{
    $jobs = loadJsonArray(CHECKLIST_JOBS_FILE);
    return array_values(array_filter($jobs, static fn($j): bool => is_array($j)));
}

function saveJobs(array $jobs): void
{
    saveJsonArray(CHECKLIST_JOBS_FILE, array_values($jobs));
}

function updateChecklistItem(array &$checklistData, string $itemId, callable $mutator): void
{
    foreach ($checklistData['items'] as &$item) {
        if ((string)($item['id'] ?? '') === $itemId) {
            $mutator($item);
            break;
        }
    }
    unset($item);
}

function isCommandAllowed(string $command): bool
{
    $allowedPrefixes = [
        'php ',
        'python ',
        'bash ',
        'sh ',
        'composer ',
        'npm ',
    ];
    $trimmed = ltrim($command);
    foreach ($allowedPrefixes as $prefix) {
        if (str_starts_with($trimmed, $prefix)) {
            return true;
        }
    }
    return false;
}

function runCommand(string $command): array
{
    $tmp = tempnam(sys_get_temp_dir(), 'chk_agent_');
    $wrapped = sprintf('%s ; printf "\n__EXIT_CODE__:%s\n" "$?"', $command, '%s');
    $finalCommand = sprintf('bash -lc %s > %s 2>&1', escapeshellarg($wrapped), escapeshellarg((string)$tmp));
    @exec($finalCommand);
    $output = is_file((string)$tmp) ? (string)file_get_contents((string)$tmp) : '';
    if (is_file((string)$tmp)) {
        @unlink((string)$tmp);
    }

    $exitCode = 1;
    if (preg_match('/__EXIT_CODE__:(\d+)/', $output, $m) === 1) {
        $exitCode = (int)$m[1];
        $output = str_replace($m[0], '', $output);
    }
    $output = trim($output);

    return [
        'exit_code' => $exitCode,
        'output' => mb_substr($output, 0, 4000),
    ];
}

$maxJobs = 1;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--max-jobs=')) {
        $maxJobs = max(1, (int)substr($arg, strlen('--max-jobs=')));
    }
}

$processed = 0;
$jobs = loadJobs();
if ($jobs === []) {
    logLine('No jobs in queue.');
    exit(0);
}

for ($i = 0; $i < count($jobs) && $processed < $maxJobs; $i++) {
    $job = $jobs[$i];
    if ((string)($job['status'] ?? '') !== 'queued') {
        continue;
    }

    $jobId = (string)($job['job_id'] ?? '');
    $itemId = (string)($job['item_id'] ?? '');
    $command = trim((string)($job['command'] ?? ''));

    $jobs[$i]['status'] = 'picked';
    $jobs[$i]['picked_at'] = date('c');

    $checklistData = loadChecklistData();
    updateChecklistItem($checklistData, $itemId, static function (array &$item): void {
        $item['agent_state'] = 'picked';
        $item['agent_status_text'] = 'Agent a pris la tache.';
        $item['agent_updated_at'] = date('c');
        $item['status'] = 'agent_requested';
    });
    saveChecklistData($checklistData);
    saveJobs($jobs);
    logLine("Picked job {$jobId}");

    $jobs[$i]['status'] = 'running';
    $jobs[$i]['started_at'] = date('c');
    $checklistData = loadChecklistData();
    updateChecklistItem($checklistData, $itemId, static function (array &$item): void {
        $item['agent_state'] = 'running';
        $item['agent_status_text'] = 'Agent en cours de modification.';
        $item['agent_updated_at'] = date('c');
    });
    saveChecklistData($checklistData);
    saveJobs($jobs);

    if ($command === '') {
        $jobs[$i]['status'] = 'failed';
        $jobs[$i]['finished_at'] = date('c');
        $jobs[$i]['error'] = 'Aucune commande fournie.';
        $checklistData = loadChecklistData();
        updateChecklistItem($checklistData, $itemId, static function (array &$item): void {
            $item['agent_state'] = 'failed';
            $item['agent_status_text'] = 'Agent: commande absente.';
            $item['agent_updated_at'] = date('c');
        });
        saveChecklistData($checklistData);
        saveJobs($jobs);
        logLine("Failed job {$jobId}: missing command");
        $processed++;
        continue;
    }

    if (!isCommandAllowed($command)) {
        $jobs[$i]['status'] = 'failed';
        $jobs[$i]['finished_at'] = date('c');
        $jobs[$i]['error'] = 'Commande non autorisee.';
        $checklistData = loadChecklistData();
        updateChecklistItem($checklistData, $itemId, static function (array &$item): void {
            $item['agent_state'] = 'failed';
            $item['agent_status_text'] = 'Agent: commande non autorisee.';
            $item['agent_updated_at'] = date('c');
        });
        saveChecklistData($checklistData);
        saveJobs($jobs);
        logLine("Failed job {$jobId}: command not allowed");
        $processed++;
        continue;
    }

    $result = runCommand($command);
    $jobs[$i]['finished_at'] = date('c');
    $jobs[$i]['output'] = $result['output'];
    $jobs[$i]['exit_code'] = $result['exit_code'];

    if ((int)$result['exit_code'] === 0) {
        $jobs[$i]['status'] = 'done';
        $checklistData = loadChecklistData();
        updateChecklistItem($checklistData, $itemId, static function (array &$item): void {
            $item['agent_state'] = 'done';
            $item['agent_status_text'] = 'Agent a termine la commande.';
            $item['agent_updated_at'] = date('c');
        });
        saveChecklistData($checklistData);
        logLine("Done job {$jobId}");
    } else {
        $jobs[$i]['status'] = 'failed';
        $checklistData = loadChecklistData();
        updateChecklistItem($checklistData, $itemId, static function (array &$item): void {
            $item['agent_state'] = 'failed';
            $item['agent_status_text'] = 'Agent a echoue. Voir logs/output.';
            $item['agent_updated_at'] = date('c');
        });
        saveChecklistData($checklistData);
        logLine("Failed job {$jobId}: exit " . (int)$result['exit_code']);
    }

    saveJobs($jobs);
    $processed++;
}

logLine("Processed jobs: {$processed}");
exit(0);
