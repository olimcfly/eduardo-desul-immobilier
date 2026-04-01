<?php
/**
 * MODULE SÉQUENCES EMAIL CRM
 * /admin/modules/marketing/sequences/index.php
 * Séquences automatisées de nurturing pour leads immobiliers
 */

if (!defined('ADMIN_ROUTER')) {
    die("Accès direct interdit.");
}

$page_title = "Séquences Email";
$current_module = "sequences";

if (!function_exists('seq_log_sql_error')) {
    function seq_log_sql_error(string $context, PDOException $e): void
    {
        error_log(sprintf(
            '[sequences][%s] SQLSTATE=%s CODE=%s MESSAGE=%s',
            $context,
            (string) $e->getCode(),
            isset($e->errorInfo[1]) ? (string) $e->errorInfo[1] : 'n/a',
            $e->getMessage()
        ));
    }
}

// ====================================================
// INIT DB — pattern standard IMMO LOCAL+
// ====================================================
if (!isset($pdo) && !isset($db)) {
    try {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (PDOException $e) {
        seq_log_sql_error('db_init', $e);
        echo '<div style="padding:20px;color:#ef4444">Connexion base de données impossible. Vérifiez la configuration puis réessayez.</div>';
        return;
    }
}
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db = $pdo;

// ====================================================
// VÉRIFICATION / CRÉATION TABLES
// ====================================================
$tablesExist = true;
try {
    $db->query("SELECT 1 FROM crm_sequences LIMIT 1");
    $db->query("SELECT 1 FROM crm_sequence_steps LIMIT 1");
    $db->query("SELECT 1 FROM crm_sequence_enrollments LIMIT 1");
    $db->query("SELECT 1 FROM crm_sequence_sends LIMIT 1");
} catch (PDOException $e) {
    $tablesExist = false;
}

if (!$tablesExist) {
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `crm_sequences` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT DEFAULT NULL,
                `trigger_type` ENUM('manual','new_lead','status_change','tag_added','form_submit') DEFAULT 'manual',
                `trigger_value` VARCHAR(255) DEFAULT NULL,
                `target_segment` VARCHAR(100) DEFAULT NULL,
                `is_active` TINYINT(1) DEFAULT 0,
                `send_window_start` TIME DEFAULT '09:00:00',
                `send_window_end` TIME DEFAULT '19:00:00',
                `send_days` VARCHAR(50) DEFAULT '1,2,3,4,5',
                `from_name` VARCHAR(255) DEFAULT NULL,
                `from_email` VARCHAR(255) DEFAULT NULL,
                `reply_to` VARCHAR(255) DEFAULT NULL,
                `total_enrolled` INT(11) DEFAULT 0,
                `total_completed` INT(11) DEFAULT 0,
                `total_unsubscribed` INT(11) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_active` (`is_active`),
                KEY `idx_trigger` (`trigger_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `crm_sequence_steps` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `sequence_id` INT(11) NOT NULL,
                `step_order` INT(11) NOT NULL DEFAULT 1,
                `step_type` ENUM('email','sms','wait','condition','task') DEFAULT 'email',
                `delay_days` INT(11) DEFAULT 0,
                `delay_hours` INT(11) DEFAULT 0,
                `subject` VARCHAR(255) DEFAULT NULL,
                `body_html` LONGTEXT DEFAULT NULL,
                `body_text` TEXT DEFAULT NULL,
                `sms_text` VARCHAR(480) DEFAULT NULL,
                `condition_field` VARCHAR(100) DEFAULT NULL,
                `condition_operator` VARCHAR(20) DEFAULT NULL,
                `condition_value` VARCHAR(255) DEFAULT NULL,
                `task_description` TEXT DEFAULT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_sequence_order` (`sequence_id`, `step_order`),
                CONSTRAINT `fk_css_sequence` FOREIGN KEY (`sequence_id`) REFERENCES `crm_sequences` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `crm_sequence_enrollments` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `sequence_id` INT(11) NOT NULL,
                `lead_id` INT(11) NOT NULL,
                `current_step` INT(11) DEFAULT 1,
                `status` ENUM('active','paused','completed','unsubscribed','bounced','failed') DEFAULT 'active',
                `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `next_action_at` DATETIME DEFAULT NULL,
                `completed_at` DATETIME DEFAULT NULL,
                `unsubscribed_at` DATETIME DEFAULT NULL,
                `metadata` JSON DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_seq_lead` (`sequence_id`, `lead_id`),
                KEY `idx_status` (`status`),
                KEY `idx_next_action` (`next_action_at`),
                KEY `idx_lead` (`lead_id`),
                CONSTRAINT `fk_cse_sequence` FOREIGN KEY (`sequence_id`) REFERENCES `crm_sequences` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `crm_sequence_sends` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `enrollment_id` INT(11) NOT NULL,
                `step_id` INT(11) NOT NULL,
                `lead_id` INT(11) NOT NULL,
                `sequence_id` INT(11) NOT NULL,
                `subject` VARCHAR(255) DEFAULT NULL,
                `status` ENUM('queued','scheduled','sent','delivered','opened','clicked','replied','bounced','failed','cancelled') DEFAULT 'queued',
                `scheduled_at` DATETIME DEFAULT NULL,
                `sent_at` DATETIME DEFAULT NULL,
                `opened_at` DATETIME DEFAULT NULL,
                `clicked_at` DATETIME DEFAULT NULL,
                `replied_at` DATETIME DEFAULT NULL,
                `bounced_at` DATETIME DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `tracking_id` VARCHAR(100) DEFAULT NULL,
                `open_count` INT(11) DEFAULT 0,
                `click_count` INT(11) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_enrollment` (`enrollment_id`),
                KEY `idx_step` (`step_id`),
                KEY `idx_lead` (`lead_id`),
                KEY `idx_status` (`status`),
                KEY `idx_tracking` (`tracking_id`),
                KEY `idx_scheduled` (`scheduled_at`),
                CONSTRAINT `fk_cssd_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `crm_sequence_enrollments` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_cssd_step` FOREIGN KEY (`step_id`) REFERENCES `crm_sequence_steps` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $tablesExist = true;
    } catch (PDOException $e) {
        seq_log_sql_error('schema_bootstrap', $e);
        echo '<div style="padding:20px;color:#ef4444">Impossible d’initialiser les tables CRM. Contactez un administrateur.</div>';
        return;
    }
}

// ====================================================
// GESTION ACTIONS (POST)
// ====================================================
$action = $_GET['action'] ?? 'list';
$sequenceId = (int)($_GET['id'] ?? $_POST['sequence_id'] ?? 0);
$message = isset($_GET['flash']) ? (string) $_GET['flash'] : '';
$messageType = isset($_GET['flash_type']) ? (string) $_GET['flash_type'] : 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    try {
        $redirectTo = '';

        switch ($postAction) {
            case 'create_sequence':
                $stmt = $db->prepare("
                    INSERT INTO crm_sequences (name, description, trigger_type, trigger_value, target_segment, from_name, from_email, reply_to, send_window_start, send_window_end, send_days)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['description'] ?? ''),
                    $_POST['trigger_type'] ?? 'manual',
                    trim($_POST['trigger_value'] ?? ''),
                    $_POST['target_segment'] ?? null,
                    trim($_POST['from_name'] ?? ''),
                    trim($_POST['from_email'] ?? ''),
                    trim($_POST['reply_to'] ?? ''),
                    $_POST['send_window_start'] ?? '09:00:00',
                    $_POST['send_window_end'] ?? '19:00:00',
                    $_POST['send_days'] ?? '1,2,3,4,5',
                ]);
                $newId = $db->lastInsertId();
                $message = 'Séquence créée avec succès.';
                $redirectTo = '?page=sequences&action=edit&id=' . (int) $newId . '&flash=' . rawurlencode($message) . '&flash_type=success';
                break;

            case 'update_sequence':
                $stmt = $db->prepare("
                    UPDATE crm_sequences SET
                        name = ?, description = ?, trigger_type = ?, trigger_value = ?,
                        target_segment = ?, from_name = ?, from_email = ?, reply_to = ?,
                        send_window_start = ?, send_window_end = ?, send_days = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['description'] ?? ''),
                    $_POST['trigger_type'] ?? 'manual',
                    trim($_POST['trigger_value'] ?? ''),
                    $_POST['target_segment'] ?? null,
                    trim($_POST['from_name'] ?? ''),
                    trim($_POST['from_email'] ?? ''),
                    trim($_POST['reply_to'] ?? ''),
                    $_POST['send_window_start'] ?? '09:00:00',
                    $_POST['send_window_end'] ?? '19:00:00',
                    $_POST['send_days'] ?? '1,2,3,4,5',
                    (int)$_POST['sequence_id'],
                ]);
                $message = 'Séquence mise à jour.';
                $sequenceId = (int)$_POST['sequence_id'];
                $redirectTo = '?page=sequences&action=edit&id=' . $sequenceId . '&flash=' . rawurlencode($message) . '&flash_type=success';
                break;

            case 'toggle_sequence':
                $stmt = $db->prepare("UPDATE crm_sequences SET is_active = NOT is_active WHERE id = ?");
                $sequenceId = (int)$_POST['sequence_id'];
                $stmt->execute([$sequenceId]);
                $message = 'Statut modifié.';
                if ($action === 'edit') {
                    $redirectTo = '?page=sequences&action=edit&id=' . $sequenceId . '&flash=' . rawurlencode($message) . '&flash_type=success';
                } else {
                    $redirectTo = '?page=sequences&flash=' . rawurlencode($message) . '&flash_type=success';
                }
                break;

            case 'delete_sequence':
                $stmt = $db->prepare("DELETE FROM crm_sequences WHERE id = ?");
                $stmt->execute([(int)$_POST['sequence_id']]);
                $message = 'Séquence supprimée.';
                $redirectTo = '?page=sequences&flash=' . rawurlencode($message) . '&flash_type=success';
                break;

            case 'add_step':
                $seqId = (int)$_POST['sequence_id'];
                $stepType = (string)($_POST['step_type'] ?? 'email');
                $subject = trim((string)($_POST['subject'] ?? ''));
                $bodyHtml = trim((string)($_POST['body_html'] ?? ''));
                if ($stepType === 'email') {
                    if ($subject === '') {
                        throw new InvalidArgumentException('Le sujet est obligatoire pour une étape email.');
                    }
                    if ($bodyHtml === '') {
                        throw new InvalidArgumentException('Le corps du message est obligatoire pour une étape email.');
                    }
                }
                $maxOrder = $db->prepare("SELECT COALESCE(MAX(step_order), 0) + 1 FROM crm_sequence_steps WHERE sequence_id = ?");
                $maxOrder->execute([$seqId]);
                $nextOrder = $maxOrder->fetchColumn();
                $stmt = $db->prepare("
                    INSERT INTO crm_sequence_steps (sequence_id, step_order, step_type, delay_days, delay_hours, subject, body_html, sms_text, task_description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $seqId,
                    $nextOrder,
                    $stepType,
                    (int)($_POST['delay_days'] ?? 0),
                    (int)($_POST['delay_hours'] ?? 0),
                    $subject,
                    $_POST['body_html'] ?? '',
                    trim($_POST['sms_text'] ?? ''),
                    trim($_POST['task_description'] ?? ''),
                ]);
                $message = 'Étape ajoutée.';
                $redirectTo = '?page=sequences&action=edit&id=' . $seqId . '&flash=' . rawurlencode($message) . '&flash_type=success';
                break;

            case 'update_step':
                $stepType = (string)($_POST['step_type'] ?? 'email');
                $subject = trim((string)($_POST['subject'] ?? ''));
                $bodyHtml = trim((string)($_POST['body_html'] ?? ''));
                if ($stepType === 'email') {
                    if ($subject === '') {
                        throw new InvalidArgumentException('Le sujet est obligatoire pour une étape email.');
                    }
                    if ($bodyHtml === '') {
                        throw new InvalidArgumentException('Le corps du message est obligatoire pour une étape email.');
                    }
                }
                $stmt = $db->prepare("
                    UPDATE crm_sequence_steps SET
                        step_type = ?, delay_days = ?, delay_hours = ?,
                        subject = ?, body_html = ?, sms_text = ?, task_description = ?, is_active = ?
                    WHERE id = ? AND sequence_id = ?
                ");
                $stmt->execute([
                    $stepType,
                    (int)($_POST['delay_days'] ?? 0),
                    (int)($_POST['delay_hours'] ?? 0),
                    $subject,
                    $_POST['body_html'] ?? '',
                    trim($_POST['sms_text'] ?? ''),
                    trim($_POST['task_description'] ?? ''),
                    isset($_POST['step_active']) ? 1 : 0,
                    (int)$_POST['step_id'],
                    (int)$_POST['sequence_id'],
                ]);
                $message = 'Étape mise à jour.';
                $sequenceId = (int)$_POST['sequence_id'];
                $redirectTo = '?page=sequences&action=edit&id=' . $sequenceId . '&flash=' . rawurlencode($message) . '&flash_type=success';
                break;

            case 'delete_step':
                $stmt = $db->prepare("DELETE FROM crm_sequence_steps WHERE id = ? AND sequence_id = ?");
                $stmt->execute([(int)$_POST['step_id'], (int)$_POST['sequence_id']]);
                $steps_reorder = $db->prepare("SELECT id FROM crm_sequence_steps WHERE sequence_id = ? ORDER BY step_order ASC");
                $steps_reorder->execute([(int)$_POST['sequence_id']]);
                $order = 1;
                $upd = $db->prepare("UPDATE crm_sequence_steps SET step_order = ? WHERE id = ?");
                while ($row = $steps_reorder->fetch()) {
                    $upd->execute([$order++, $row['id']]);
                }
                $message = 'Étape supprimée.';
                $sequenceId = (int)$_POST['sequence_id'];
                $redirectTo = '?page=sequences&action=edit&id=' . $sequenceId . '&flash=' . rawurlencode($message) . '&flash_type=success';
                break;

            case 'enroll_leads':
                $seqId = (int)$_POST['sequence_id'];
                $leadIds = $_POST['lead_ids'] ?? [];
                $enrolled = 0;
                $stmt = $db->prepare("
                    INSERT IGNORE INTO crm_sequence_enrollments (sequence_id, lead_id, status, next_action_at)
                    VALUES (?, ?, 'active', NOW())
                ");
                foreach ($leadIds as $lid) {
                    $stmt->execute([$seqId, (int)$lid]);
                    if ($stmt->rowCount() > 0) $enrolled++;
                }
                $db->prepare("UPDATE crm_sequences SET total_enrolled = total_enrolled + ? WHERE id = ?")->execute([$enrolled, $seqId]);
                $message = "$enrolled lead(s) inscrit(s) dans la séquence.";
                $redirectTo = '?page=sequences&action=edit&id=' . $seqId . '&flash=' . rawurlencode($message) . '&flash_type=success';
                break;
        }

        if ($redirectTo !== '' && !headers_sent()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    } catch (PDOException $e) {
        seq_log_sql_error('post_action:' . ($postAction !== '' ? $postAction : 'unknown'), $e);
        $message = 'Une erreur technique est survenue lors de l’enregistrement. Merci de réessayer dans quelques instants.';
        $messageType = 'danger';
    }
}

// ====================================================
// STATS GLOBALES
// ====================================================
$stats = [
    'total_sequences' => 0, 'active_sequences' => 0,
    'total_enrolled'  => 0, 'total_sent'       => 0,
    'total_opened'    => 0, 'total_clicked'    => 0,
    'total_replied'   => 0, 'avg_open_rate'    => 0,
];
try {
    $stats['total_sequences']  = (int)$db->query("SELECT COUNT(*) FROM crm_sequences")->fetchColumn();
    $stats['active_sequences'] = (int)$db->query("SELECT COUNT(*) FROM crm_sequences WHERE is_active = 1")->fetchColumn();
    $stats['total_enrolled']   = (int)$db->query("SELECT COUNT(*) FROM crm_sequence_enrollments")->fetchColumn();
    $stats['total_sent']       = (int)$db->query("SELECT COUNT(*) FROM crm_sequence_sends WHERE status IN ('sent','delivered','opened','clicked','replied')")->fetchColumn();
    $stats['total_opened']     = (int)$db->query("SELECT COUNT(*) FROM crm_sequence_sends WHERE opened_at IS NOT NULL")->fetchColumn();
    $stats['total_clicked']    = (int)$db->query("SELECT COUNT(*) FROM crm_sequence_sends WHERE clicked_at IS NOT NULL")->fetchColumn();
    $stats['total_replied']    = (int)$db->query("SELECT COUNT(*) FROM crm_sequence_sends WHERE replied_at IS NOT NULL")->fetchColumn();
    if ($stats['total_sent'] > 0) {
        $stats['avg_open_rate'] = round(($stats['total_opened'] / $stats['total_sent']) * 100, 1);
    }
} catch (PDOException $e) {}

// ====================================================
// DONNÉES LISTE / ÉDITION
// ====================================================
$sequences = [];
if ($action === 'list') {
    try {
        $sequences = $db->query("
            SELECT s.*,
                (SELECT COUNT(*) FROM crm_sequence_steps ss WHERE ss.sequence_id = s.id) as steps_count,
                (SELECT COUNT(*) FROM crm_sequence_enrollments se WHERE se.sequence_id = s.id AND se.status = 'active') as active_enrolled,
                (SELECT COUNT(*) FROM crm_sequence_sends snd WHERE snd.sequence_id = s.id AND snd.status IN ('sent','delivered','opened','clicked','replied')) as emails_sent,
                (SELECT COUNT(*) FROM crm_sequence_sends snd WHERE snd.sequence_id = s.id AND snd.opened_at IS NOT NULL) as emails_opened
            FROM crm_sequences s
            ORDER BY s.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $sequences = []; }
}

$sequence = null;
$steps = [];
$enrollments = [];
$availableLeads = [];

if ($action === 'edit' && $sequenceId > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM crm_sequences WHERE id = ?");
        $stmt->execute([$sequenceId]);
        $sequence = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sequence) {
            $stepsStmt = $db->prepare("SELECT * FROM crm_sequence_steps WHERE sequence_id = ? ORDER BY step_order ASC");
            $stepsStmt->execute([$sequenceId]);
            $steps = $stepsStmt->fetchAll(PDO::FETCH_ASSOC);

            $enrollStmt = $db->prepare("
                SELECT se.*, l.first_name, l.last_name, l.email, l.phone, l.status as lead_status
                FROM crm_sequence_enrollments se
                LEFT JOIN leads l ON l.id = se.lead_id
                WHERE se.sequence_id = ?
                ORDER BY se.enrolled_at DESC
                LIMIT 50
            ");
            $enrollStmt->execute([$sequenceId]);
            $enrollments = $enrollStmt->fetchAll(PDO::FETCH_ASSOC);

            try {
                $availStmt = $db->prepare("
                    SELECT l.id, l.first_name, l.last_name, l.email, l.source, l.status
                    FROM leads l
                    WHERE l.email IS NOT NULL AND l.email != ''
                    AND l.id NOT IN (SELECT lead_id FROM crm_sequence_enrollments WHERE sequence_id = ?)
                    ORDER BY l.created_at DESC
                    LIMIT 200
                ");
                $availStmt->execute([$sequenceId]);
                $availableLeads = $availStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) { $availableLeads = []; }
        } else {
            $action = 'list';
        }
    } catch (PDOException $e) {
        $action = 'list';
    }
}

$templateVars = [
    '{{prenom}}'            => 'Prénom du lead',
    '{{nom}}'               => 'Nom du lead',
    '{{email}}'             => 'Email du lead',
    '{{telephone}}'         => 'Téléphone',
    '{{source}}'            => 'Source du lead',
    '{{agent_nom}}'         => 'Nom de l\'agent',
    '{{agent_tel}}'         => 'Téléphone de l\'agent',
    '{{site_url}}'          => 'URL du site',
    '{{lien_desinscription}}' => 'Lien désinscription',
];
?>

<!-- ===========================
     STYLES
     =========================== -->
<style>
.seq-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:24px; flex-wrap:wrap; gap:12px;
}
.seq-header h2 {
    font-size:1.5rem; font-weight:700; color:var(--text); margin:0;
    display:flex; align-items:center; gap:10px;
}
.seq-header h2 i { color:var(--accent); }

.seq-stats-grid {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(150px,1fr));
    gap:16px; margin-bottom:28px;
}
.seq-stat-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius-lg); padding:20px; text-align:center;
    transition:transform .2s, box-shadow .2s;
}
.seq-stat-card:hover { transform:translateY(-2px); box-shadow:var(--shadow); }
.seq-stat-card .sv { font-size:1.8rem; font-weight:800; color:var(--text); line-height:1.2; }
.seq-stat-card .sl { font-size:.75rem; color:var(--text-3); text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
.seq-stat-card.c-accent .sv { color:var(--accent); }
.seq-stat-card.c-green  .sv { color:var(--green); }
.seq-stat-card.c-amber  .sv { color:var(--amber); }

.btn-seq {
    display:inline-flex; align-items:center; gap:6px;
    padding:10px 20px; border:none; border-radius:8px;
    font-size:.875rem; font-weight:600; cursor:pointer;
    text-decoration:none; transition:all .2s;
}
.btn-seq-primary  { background:var(--accent); color:#fff; }
.btn-seq-primary:hover  { opacity:.9; color:#fff; }
.btn-seq-success  { background:var(--green); color:#fff; }
.btn-seq-success:hover  { opacity:.9; }
.btn-seq-danger   { background:var(--red); color:#fff; }
.btn-seq-danger:hover   { opacity:.9; }
.btn-seq-outline  { background:transparent; color:var(--accent); border:1px solid var(--accent); }
.btn-seq-outline:hover  { background:var(--accent); color:#fff; }
.btn-seq-sm { padding:6px 12px; font-size:.8rem; }

.seq-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius-lg); padding:24px; margin-bottom:20px;
    transition:box-shadow .2s;
}
.seq-card:hover { box-shadow:var(--shadow); }
.seq-card-header {
    display:flex; justify-content:space-between; align-items:flex-start;
    margin-bottom:16px; gap:12px;
}
.seq-card-title { font-size:1.1rem; font-weight:700; color:var(--text); margin:0; }
.seq-card-desc  { color:var(--text-3); font-size:.875rem; margin-top:4px; }

.seq-badge {
    display:inline-flex; align-items:center; padding:4px 10px;
    border-radius:20px; font-size:.75rem; font-weight:600; gap:4px;
}
.seq-badge-active   { background:#d1fae5; color:#065f46; }
.seq-badge-inactive { background:#fee2e2; color:#991b1b; }
.seq-badge-manual   { background:#dbeafe; color:#1e40af; }
.seq-badge-auto     { background:#fef3c7; color:#92400e; }

.seq-mini-stats {
    display:flex; gap:20px; flex-wrap:wrap;
    padding-top:12px; border-top:1px solid var(--border); margin-top:12px;
}
.seq-mini-stat .val { font-size:1.1rem; font-weight:700; color:var(--text); }
.seq-mini-stat .lbl { font-size:.7rem; color:var(--text-3); text-transform:uppercase; }

.seq-timeline { position:relative; padding-left:40px; }
.seq-timeline::before {
    content:''; position:absolute; left:16px; top:0; bottom:0;
    width:2px; background:var(--border);
}
.seq-step {
    position:relative; margin-bottom:24px;
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius-lg); padding:20px;
}
.seq-step::before {
    content:''; position:absolute; left:-32px; top:24px;
    width:12px; height:12px; border-radius:50%;
    background:var(--accent); border:3px solid var(--surface-2);
    box-shadow:0 0 0 2px var(--accent);
}
.seq-step.step-wait::before      { background:var(--amber); box-shadow:0 0 0 2px var(--amber); }
.seq-step.step-condition::before { background:#0891b2; box-shadow:0 0 0 2px #0891b2; }
.seq-step.step-task::before      { background:var(--green); box-shadow:0 0 0 2px var(--green); }
.seq-step.step-inactive { opacity:.5; }
.seq-step-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.seq-step-number { font-weight:800; color:var(--accent); font-size:.85rem; }
.seq-step-delay  { font-size:.8rem; color:var(--text-3); display:flex; align-items:center; gap:4px; }
.seq-step-subject { font-weight:600; font-size:1rem; color:var(--text); margin-bottom:8px; }
.seq-step-preview { color:var(--text-3); font-size:.85rem; line-height:1.5; max-height:60px; overflow:hidden; }
.seq-step-actions { display:flex; gap:8px; margin-top:12px; padding-top:12px; border-top:1px solid var(--border); }

.seq-form-group { margin-bottom:16px; }
.seq-form-group label { display:block; font-size:.85rem; font-weight:600; color:var(--text); margin-bottom:6px; }
.seq-form-group input,
.seq-form-group select,
.seq-form-group textarea {
    width:100%; padding:10px 14px; border:1px solid var(--border);
    border-radius:8px; font-size:.9rem; color:var(--text);
    background:var(--surface); transition:border-color .2s; box-sizing:border-box;
}
.seq-form-group input:focus,
.seq-form-group select:focus,
.seq-form-group textarea:focus {
    outline:none; border-color:var(--accent);
    box-shadow:0 0 0 3px rgba(99,102,241,.1);
}
.seq-form-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:16px; }

.seq-modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.5); z-index:9999;
    align-items:center; justify-content:center;
}
.seq-modal-overlay.active { display:flex; }
.seq-modal {
    background:var(--surface); border-radius:16px;
    width:90%; max-width:700px; max-height:85vh;
    overflow-y:auto; padding:32px;
    box-shadow:0 20px 60px rgba(0,0,0,.2);
}
.seq-modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.seq-modal-header h3 { font-size:1.2rem; font-weight:700; margin:0; }
.seq-modal-close { background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-3); }

.seq-alert {
    padding:14px 20px; border-radius:8px; margin-bottom:20px;
    font-size:.9rem; display:flex; align-items:center; gap:8px;
}
.seq-alert-success { background:#d1fae5; color:#065f46; }
.seq-alert-danger  { background:#fee2e2; color:#991b1b; }

.seq-table { width:100%; border-collapse:collapse; }
.seq-table th, .seq-table td {
    padding:12px 16px; text-align:left;
    border-bottom:1px solid var(--border); font-size:.875rem;
}
.seq-table th {
    background:var(--surface-2); font-weight:600; color:var(--text-3);
    text-transform:uppercase; font-size:.75rem; letter-spacing:.5px;
}
.seq-table tr:hover td { background:var(--surface-2); }

.seq-tabs { display:flex; border-bottom:2px solid var(--border); margin-bottom:24px; gap:4px; }
.seq-tab {
    padding:12px 20px; cursor:pointer; font-weight:600; font-size:.9rem;
    color:var(--text-3); border-bottom:3px solid transparent; margin-bottom:-2px;
    transition:all .2s; background:none;
    border-top:none; border-left:none; border-right:none;
}
.seq-tab:hover { color:var(--accent); }
.seq-tab.active { color:var(--accent); border-bottom-color:var(--accent); }
.seq-tab-content { display:none; }
.seq-tab-content.active { display:block; }

.seq-empty { text-align:center; padding:60px 20px; color:var(--text-3); }
.seq-empty i { font-size:3rem; margin-bottom:16px; opacity:.4; display:block; }
.seq-empty h3 { font-size:1.2rem; color:var(--text); margin-bottom:8px; }

.seq-vars-list { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.seq-var-tag {
    display:inline-flex; align-items:center;
    background:var(--surface-2); color:var(--accent);
    padding:4px 10px; border-radius:6px; font-size:.75rem;
    font-family:monospace; cursor:pointer; transition:all .2s;
}
.seq-var-tag:hover { background:var(--accent); color:#fff; }

/* Enhanced Modal */
.seq-modal-large {
    width:90%; max-width:900px; max-height:90vh; overflow-y:auto;
}

/* Editor Wrapper */
.seq-editor-wrapper {
    border:1px solid var(--border); border-radius:var(--radius-lg);
    overflow:hidden; background:var(--surface);
}
.seq-editor-tabs {
    display:flex; gap:0; border-bottom:1px solid var(--border);
    background:var(--surface-2);
}
.seq-editor-tab {
    padding:8px 16px; font-size:.8rem; font-weight:600;
    border:none; background:none; cursor:pointer;
    color:var(--text-3); transition:all .2s;
    border-bottom:2px solid transparent;
}
.seq-editor-tab:hover { color:var(--accent); }
.seq-editor-tab.active {
    color:var(--accent); border-bottom-color:var(--accent);
    background:var(--surface);
}

/* Rich Text Toolbar */
.seq-toolbar {
    display:flex; flex-wrap:wrap; gap:2px; padding:6px 8px;
    border-bottom:1px solid var(--border); background:var(--surface-2);
    align-items:center;
}
.seq-toolbar button {
    width:32px; height:32px; border:none; background:none;
    border-radius:6px; cursor:pointer; font-size:.8rem;
    color:var(--text-2); transition:all .15s;
    display:inline-flex; align-items:center; justify-content:center;
}
.seq-toolbar button:hover {
    background:var(--accent); color:#fff;
}
.seq-toolbar-sep {
    width:1px; height:20px; background:var(--border); margin:0 4px;
}

/* Visual Editor (contenteditable) */
.seq-editor-visual {
    min-height:280px; max-height:450px; overflow-y:auto;
    padding:20px; font-size:.9rem; line-height:1.7;
    color:var(--text); outline:none; font-family:system-ui, sans-serif;
}
.seq-editor-visual:empty::before {
    content:'Bonjour {{prenom}},\A\AVotre email ici...';
    white-space:pre-line; color:var(--text-3); pointer-events:none;
}
.seq-editor-visual h2 { font-size:1.3rem; font-weight:700; margin:16px 0 8px; }
.seq-editor-visual h3 { font-size:1.1rem; font-weight:600; margin:12px 0 6px; }
.seq-editor-visual a { color:var(--accent); text-decoration:underline; }
.seq-editor-visual ul, .seq-editor-visual ol { padding-left:24px; margin:8px 0; }
.seq-editor-visual li { margin-bottom:4px; }
.seq-editor-visual .email-cta-btn {
    display:inline-block; padding:12px 28px; background:var(--accent);
    color:#fff !important; text-decoration:none !important;
    border-radius:8px; font-weight:600; margin:12px 0;
}
.seq-editor-visual .email-signature {
    margin-top:24px; padding-top:16px;
    border-top:1px solid var(--border); color:var(--text-3); font-size:.85rem;
}
.seq-editor-visual img { max-width:100%; height:auto; border-radius:8px; margin:8px 0; }

/* HTML Source Editor */
.seq-editor-source {
    width:100%; border:none; padding:16px; font-family:'Fira Code',monospace,monospace;
    font-size:.8rem; line-height:1.6; resize:vertical;
    min-height:280px; background:var(--surface); color:var(--text);
    outline:none;
}

/* Email Preview */
.seq-editor-preview {
    min-height:280px; max-height:500px; overflow-y:auto;
    padding:0; background:#f8fafc;
}
.seq-preview-frame {
    max-width:600px; margin:24px auto; background:#fff;
    border-radius:8px; box-shadow:0 2px 12px rgba(0,0,0,.08);
    overflow:hidden; font-family:system-ui, sans-serif;
}
.seq-preview-header {
    padding:16px 24px; background:var(--surface-2); border-bottom:1px solid var(--border);
    font-size:.8rem; color:var(--text-3);
}
.seq-preview-header strong { color:var(--text); display:block; margin-bottom:4px; }
.seq-preview-body {
    padding:24px; font-size:.9rem; line-height:1.7; color:#1e293b;
}
.seq-preview-body a { color:var(--accent); }
.seq-preview-body .email-cta-btn {
    display:inline-block; padding:12px 28px; background:#6366f1;
    color:#fff !important; text-decoration:none !important;
    border-radius:8px; font-weight:600; margin:12px 0;
}
.seq-preview-footer {
    padding:12px 24px; background:#f1f5f9; text-align:center;
    font-size:.75rem; color:#94a3b8;
}

/* Template selector styling */
#emailTemplateSelect {
    border:1px dashed var(--accent); background:var(--surface-2);
}
#emailTemplateSelect:focus { border-style:solid; }

@media (max-width:768px) {
    .seq-stats-grid { grid-template-columns:repeat(2,1fr); }
    .seq-form-row { grid-template-columns:1fr; }
    .seq-card-header { flex-direction:column; }
    .seq-tabs { overflow-x:auto; }
    .seq-modal-large { width:95%; max-width:none; }
    .seq-toolbar { gap:1px; padding:4px; }
    .seq-toolbar button { width:28px; height:28px; }
}
</style>

<!-- ===========================
     CONTENU
     =========================== -->

<?php if ($message): ?>
<div class="seq-alert seq-alert-<?= $messageType ?>">
    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<!-- ==================== VUE LISTE ==================== -->

<div class="seq-header">
    <h2><i class="fas fa-layer-group"></i> Séquences Email</h2>
    <a href="?page=sequences&action=create" class="btn-seq btn-seq-primary">
        <i class="fas fa-plus"></i> Nouvelle séquence
    </a>
</div>

<div class="seq-stats-grid">
    <div class="seq-stat-card c-accent">
        <div class="sv"><?= $stats['total_sequences'] ?></div>
        <div class="sl">Séquences</div>
    </div>
    <div class="seq-stat-card c-green">
        <div class="sv"><?= $stats['active_sequences'] ?></div>
        <div class="sl">Actives</div>
    </div>
    <div class="seq-stat-card">
        <div class="sv"><?= $stats['total_enrolled'] ?></div>
        <div class="sl">Inscrits</div>
    </div>
    <div class="seq-stat-card">
        <div class="sv"><?= $stats['total_sent'] ?></div>
        <div class="sl">Envoyés</div>
    </div>
    <div class="seq-stat-card c-amber">
        <div class="sv"><?= $stats['avg_open_rate'] ?>%</div>
        <div class="sl">Taux ouverture</div>
    </div>
    <div class="seq-stat-card c-green">
        <div class="sv"><?= $stats['total_replied'] ?></div>
        <div class="sl">Réponses</div>
    </div>
</div>

<?php if (empty($sequences)): ?>
<div class="seq-empty">
    <i class="fas fa-layer-group"></i>
    <h3>Aucune séquence créée</h3>
    <p>Créez votre première séquence d'emails automatisés pour engager vos leads.</p>
    <a href="?page=sequences&action=create" class="btn-seq btn-seq-primary" style="margin-top:16px">
        <i class="fas fa-plus"></i> Créer une séquence
    </a>
</div>
<?php else: ?>
<?php foreach ($sequences as $seq): ?>
<div class="seq-card">
    <div class="seq-card-header">
        <div>
            <h3 class="seq-card-title">
                <a href="?page=sequences&action=edit&id=<?= $seq['id'] ?>" style="color:inherit;text-decoration:none">
                    <?= htmlspecialchars($seq['name']) ?>
                </a>
            </h3>
            <?php if ($seq['description']): ?>
            <div class="seq-card-desc"><?= htmlspecialchars(mb_substr($seq['description'], 0, 120)) ?></div>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <span class="seq-badge <?= $seq['is_active'] ? 'seq-badge-active' : 'seq-badge-inactive' ?>">
                <i class="fas fa-circle" style="font-size:6px"></i>
                <?= $seq['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
            <span class="seq-badge <?= $seq['trigger_type'] === 'manual' ? 'seq-badge-manual' : 'seq-badge-auto' ?>">
                <?= $seq['trigger_type'] === 'manual' ? 'Manuel' : ucfirst(str_replace('_',' ',$seq['trigger_type'])) ?>
            </span>
            <?php if ($seq['target_segment']): ?>
            <span class="seq-badge" style="background:#f3e8ff;color:#6b21a8">
                <?= htmlspecialchars(ucfirst($seq['target_segment'])) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="seq-mini-stats">
        <div class="seq-mini-stat">
            <div class="val"><?= $seq['steps_count'] ?></div>
            <div class="lbl">Étapes</div>
        </div>
        <div class="seq-mini-stat">
            <div class="val"><?= $seq['active_enrolled'] ?></div>
            <div class="lbl">Inscrits actifs</div>
        </div>
        <div class="seq-mini-stat">
            <div class="val"><?= $seq['emails_sent'] ?></div>
            <div class="lbl">Envoyés</div>
        </div>
        <div class="seq-mini-stat">
            <div class="val"><?= $seq['emails_sent'] > 0 ? round(($seq['emails_opened']/$seq['emails_sent'])*100).'%' : '—' ?></div>
            <div class="lbl">Ouverture</div>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
            <a href="?page=sequences&action=edit&id=<?= $seq['id'] ?>" class="btn-seq btn-seq-outline btn-seq-sm">
                <i class="fas fa-edit"></i> Éditer
            </a>
            <a href="?page=sequences&action=edit&id=<?= $seq['id'] ?>&open_add_step=1&step_type=email" class="btn-seq btn-seq-primary btn-seq-sm">
                <i class="fas fa-envelope"></i> Créer un email
            </a>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle_sequence">
                <input type="hidden" name="sequence_id" value="<?= $seq['id'] ?>">
                <button type="submit" class="btn-seq btn-seq-sm <?= $seq['is_active'] ? 'btn-seq-danger' : 'btn-seq-success' ?>">
                    <i class="fas fa-<?= $seq['is_active'] ? 'pause' : 'play' ?>"></i>
                    <?= $seq['is_active'] ? 'Pause' : 'Activer' ?>
                </button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php elseif ($action === 'create'): ?>
<!-- ==================== CRÉATION ==================== -->

<div class="seq-header">
    <h2><i class="fas fa-plus-circle"></i> Nouvelle séquence</h2>
    <a href="?page=sequences" class="btn-seq btn-seq-outline"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<div class="seq-card">
    <form method="POST">
        <input type="hidden" name="action" value="create_sequence">
        <div class="seq-form-group">
            <label>Nom de la séquence *</label>
            <input type="text" name="name" required placeholder="Ex: Nurturing acheteur Bordeaux">
        </div>
        <div class="seq-form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Objectif de cette séquence..."></textarea>
        </div>
        <div class="seq-form-row">
            <div class="seq-form-group">
                <label>Déclencheur</label>
                <select name="trigger_type">
                    <option value="manual">Manuel</option>
                    <option value="new_lead">Nouveau lead</option>
                    <option value="status_change">Changement de statut</option>
                    <option value="tag_added">Tag ajouté</option>
                    <option value="form_submit">Formulaire soumis</option>
                </select>
            </div>
            <div class="seq-form-group">
                <label>Valeur du déclencheur</label>
                <input type="text" name="trigger_value" placeholder="Ex: source=google_ads">
            </div>
            <div class="seq-form-group">
                <label>Segment cible</label>
                <select name="target_segment">
                    <option value="">Tous</option>
                    <option value="acheteur">Acheteur</option>
                    <option value="vendeur">Vendeur</option>
                    <option value="investisseur">Investisseur</option>
                    <option value="estimation">Estimation</option>
                    <option value="locataire">Locataire</option>
                </select>
            </div>
        </div>
        <div class="seq-form-row">
            <div class="seq-form-group">
                <label>Nom expéditeur</label>
                <input type="text" name="from_name" placeholder="Eduardo De Sul" value="Eduardo De Sul">
            </div>
            <div class="seq-form-group">
                <label>Email d'expédition</label>
                <input type="email" name="from_email" placeholder="contact@eduardodesul.fr">
            </div>
            <div class="seq-form-group">
                <label>Répondre à</label>
                <input type="email" name="reply_to" placeholder="contact@eduardodesul.fr">
            </div>
        </div>
        <div class="seq-form-row">
            <div class="seq-form-group">
                <label>Fenêtre envoi — Début</label>
                <input type="time" name="send_window_start" value="09:00">
            </div>
            <div class="seq-form-group">
                <label>Fenêtre envoi — Fin</label>
                <input type="time" name="send_window_end" value="19:00">
            </div>
            <div class="seq-form-group">
                <label>Jours d'envoi</label>
                <select name="send_days">
                    <option value="1,2,3,4,5">Lundi – Vendredi</option>
                    <option value="1,2,3,4,5,6">Lundi – Samedi</option>
                    <option value="1,2,3,4,5,6,7">Tous les jours</option>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:24px">
            <button type="submit" class="btn-seq btn-seq-primary"><i class="fas fa-save"></i> Créer</button>
            <a href="?page=sequences" class="btn-seq btn-seq-outline">Annuler</a>
        </div>
    </form>
</div>

<?php elseif ($action === 'edit' && $sequence): ?>
<!-- ==================== ÉDITION ==================== -->

<div class="seq-header">
    <h2>
        <i class="fas fa-edit"></i>
        <?= htmlspecialchars($sequence['name']) ?>
        <span class="seq-badge <?= $sequence['is_active'] ? 'seq-badge-active' : 'seq-badge-inactive' ?>">
            <?= $sequence['is_active'] ? 'Active' : 'Inactive' ?>
        </span>
    </h2>
    <div style="display:flex;gap:10px">
        <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="toggle_sequence">
            <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
            <button type="submit" class="btn-seq btn-seq-sm <?= $sequence['is_active'] ? 'btn-seq-danger' : 'btn-seq-success' ?>">
                <i class="fas fa-<?= $sequence['is_active'] ? 'pause' : 'play' ?>"></i>
                <?= $sequence['is_active'] ? 'Désactiver' : 'Activer' ?>
            </button>
        </form>
        <a href="?page=sequences" class="btn-seq btn-seq-outline btn-seq-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="seq-tabs">
    <button class="seq-tab active" onclick="switchTab('steps',this)"><i class="fas fa-list-ol"></i> Étapes (<?= count($steps) ?>)</button>
    <button class="seq-tab" onclick="switchTab('settings',this)"><i class="fas fa-cog"></i> Paramètres</button>
    <button class="seq-tab" onclick="switchTab('enrollments',this)"><i class="fas fa-users"></i> Inscrits (<?= count($enrollments) ?>)</button>
    <button class="seq-tab" onclick="switchTab('enroll',this)"><i class="fas fa-user-plus"></i> Inscrire des leads</button>
</div>

<!-- TAB ÉTAPES -->
<div id="tab-steps" class="seq-tab-content active">
    <?php if (empty($steps)): ?>
    <div class="seq-empty" style="padding:40px">
        <i class="fas fa-list-ol"></i>
        <h3>Aucune étape</h3>
        <p>Ajoutez la première étape de votre séquence.</p>
    </div>
    <?php else: ?>
    <div class="seq-timeline">
        <?php foreach ($steps as $step): ?>
        <?php $icons = ['email'=>'envelope','sms'=>'comment-sms','wait'=>'clock','condition'=>'code-branch','task'=>'tasks']; ?>
        <div class="seq-step step-<?= $step['step_type'] ?> <?= !$step['is_active'] ? 'step-inactive' : '' ?>">
            <div class="seq-step-header">
                <span class="seq-step-number">
                    <i class="fas fa-<?= $icons[$step['step_type']] ?? 'circle' ?>"></i>
                    Étape <?= $step['step_order'] ?> — <?= ucfirst($step['step_type']) ?>
                    <?php if (!$step['is_active']): ?>
                    <span style="color:var(--red);font-size:.75rem">(désactivée)</span>
                    <?php endif; ?>
                </span>
                <span class="seq-step-delay">
                    <i class="fas fa-hourglass-half"></i>
                    <?php if ($step['delay_days'] > 0 || $step['delay_hours'] > 0): ?>
                        <?= $step['delay_days'] > 0 ? $step['delay_days'].'j ' : '' ?><?= $step['delay_hours'] > 0 ? $step['delay_hours'].'h' : '' ?> après l'étape précédente
                    <?php else: ?>
                        Immédiat
                    <?php endif; ?>
                </span>
            </div>
            <?php if ($step['step_type'] === 'email'): ?>
            <div class="seq-step-subject">📧 <?= htmlspecialchars($step['subject'] ?: '(Sans objet)') ?></div>
            <div class="seq-step-preview"><?= htmlspecialchars(mb_substr(strip_tags($step['body_html']), 0, 200)) ?></div>
            <?php elseif ($step['step_type'] === 'sms'): ?>
            <div class="seq-step-subject">📱 SMS</div>
            <div class="seq-step-preview"><?= htmlspecialchars(mb_substr($step['sms_text'], 0, 160)) ?></div>
            <?php elseif ($step['step_type'] === 'task'): ?>
            <div class="seq-step-subject">📋 Tâche</div>
            <div class="seq-step-preview"><?= htmlspecialchars($step['task_description']) ?></div>
            <?php endif; ?>
            <div class="seq-step-actions">
                <button type="button" class="btn-seq btn-seq-outline btn-seq-sm" onclick="openEditStepModal(<?= htmlspecialchars(json_encode($step)) ?>)">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette étape ?')">
                    <input type="hidden" name="action" value="delete_step">
                    <input type="hidden" name="step_id" value="<?= $step['id'] ?>">
                    <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
                    <button type="submit" class="btn-seq btn-seq-danger btn-seq-sm"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <button type="button" class="btn-seq btn-seq-primary" onclick="openAddStepModal()" style="margin-top:16px">
        <i class="fas fa-plus"></i> Ajouter une étape
    </button>
</div>

<!-- TAB PARAMÈTRES -->
<div id="tab-settings" class="seq-tab-content">
    <div class="seq-card">
        <form method="POST">
            <input type="hidden" name="action" value="update_sequence">
            <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
            <div class="seq-form-group">
                <label>Nom *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($sequence['name']) ?>">
            </div>
            <div class="seq-form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= htmlspecialchars($sequence['description'] ?? '') ?></textarea>
            </div>
            <div class="seq-form-row">
                <div class="seq-form-group">
                    <label>Déclencheur</label>
                    <select name="trigger_type">
                        <?php foreach (['manual'=>'Manuel','new_lead'=>'Nouveau lead','status_change'=>'Changement statut','tag_added'=>'Tag ajouté','form_submit'=>'Formulaire'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= $sequence['trigger_type']===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="seq-form-group">
                    <label>Valeur déclencheur</label>
                    <input type="text" name="trigger_value" value="<?= htmlspecialchars($sequence['trigger_value'] ?? '') ?>">
                </div>
                <div class="seq-form-group">
                    <label>Segment cible</label>
                    <select name="target_segment">
                        <option value="">Tous</option>
                        <?php foreach (['acheteur','vendeur','investisseur','estimation','locataire'] as $seg): ?>
                        <option value="<?= $seg ?>" <?= ($sequence['target_segment']??'')===$seg?'selected':'' ?>><?= ucfirst($seg) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="seq-form-row">
                <div class="seq-form-group">
                    <label>Nom expéditeur</label>
                    <input type="text" name="from_name" value="<?= htmlspecialchars($sequence['from_name'] ?? '') ?>">
                </div>
                <div class="seq-form-group">
                    <label>Email expédition</label>
                    <input type="email" name="from_email" value="<?= htmlspecialchars($sequence['from_email'] ?? '') ?>">
                </div>
                <div class="seq-form-group">
                    <label>Répondre à</label>
                    <input type="email" name="reply_to" value="<?= htmlspecialchars($sequence['reply_to'] ?? '') ?>">
                </div>
            </div>
            <div class="seq-form-row">
                <div class="seq-form-group">
                    <label>Fenêtre — Début</label>
                    <input type="time" name="send_window_start" value="<?= substr($sequence['send_window_start']??'09:00:00',0,5) ?>">
                </div>
                <div class="seq-form-group">
                    <label>Fenêtre — Fin</label>
                    <input type="time" name="send_window_end" value="<?= substr($sequence['send_window_end']??'19:00:00',0,5) ?>">
                </div>
                <div class="seq-form-group">
                    <label>Jours d'envoi</label>
                    <select name="send_days">
                        <?php foreach (['1,2,3,4,5'=>'Lun – Ven','1,2,3,4,5,6'=>'Lun – Sam','1,2,3,4,5,6,7'=>'Tous les jours'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= ($sequence['send_days']??'')===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:24px">
                <button type="submit" class="btn-seq btn-seq-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer définitivement cette séquence ?')">
                    <input type="hidden" name="action" value="delete_sequence">
                    <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
                    <button type="submit" class="btn-seq btn-seq-danger"><i class="fas fa-trash"></i> Supprimer</button>
                </form>
            </div>
        </form>
    </div>
</div>

<!-- TAB INSCRITS -->
<div id="tab-enrollments" class="seq-tab-content">
    <?php if (empty($enrollments)): ?>
    <div class="seq-empty" style="padding:40px">
        <i class="fas fa-users"></i>
        <h3>Aucun lead inscrit</h3>
        <p>Inscrivez des leads via l'onglet "Inscrire des leads".</p>
    </div>
    <?php else: ?>
    <div class="seq-card" style="padding:0;overflow:hidden">
        <table class="seq-table">
            <thead>
                <tr>
                    <th>Lead</th><th>Email</th><th>Étape</th>
                    <th>Statut</th><th>Inscrit le</th><th>Prochaine action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sBadge = ['active'=>'seq-badge-active','paused'=>'seq-badge-manual','completed'=>'seq-badge-active','unsubscribed'=>'seq-badge-inactive','bounced'=>'seq-badge-inactive','failed'=>'seq-badge-inactive'];
                $sLabel = ['active'=>'Actif','paused'=>'En pause','completed'=>'Terminé','unsubscribed'=>'Désinscrit','bounced'=>'Bounced','failed'=>'Échoué'];
                foreach ($enrollments as $enr): ?>
                <tr>
                    <td><strong><?= htmlspecialchars(trim(($enr['first_name']??'').' '.($enr['last_name']??''))) ?></strong></td>
                    <td><?= htmlspecialchars($enr['email']??'—') ?></td>
                    <td><strong style="color:var(--accent)"><?= $enr['current_step'] ?></strong> / <?= count($steps) ?></td>
                    <td><span class="seq-badge <?= $sBadge[$enr['status']]??'' ?>"><?= $sLabel[$enr['status']]??$enr['status'] ?></span></td>
                    <td><?= $enr['enrolled_at'] ? date('d/m/Y H:i', strtotime($enr['enrolled_at'])) : '—' ?></td>
                    <td><?= $enr['next_action_at'] ? date('d/m/Y H:i', strtotime($enr['next_action_at'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- TAB INSCRIRE -->
<div id="tab-enroll" class="seq-tab-content">
    <?php if (empty($availableLeads)): ?>
    <div class="seq-empty" style="padding:40px">
        <i class="fas fa-user-plus"></i>
        <h3>Aucun lead disponible</h3>
        <p>Tous les leads avec email sont déjà inscrits, ou il n'y a pas encore de leads dans le CRM.</p>
    </div>
    <?php else: ?>
    <div class="seq-card">
        <form method="POST" id="enrollForm">
            <input type="hidden" name="action" value="enroll_leads">
            <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <strong><?= count($availableLeads) ?></strong>&nbsp;leads disponibles
                <div style="display:flex;gap:10px">
                    <button type="button" class="btn-seq btn-seq-outline btn-seq-sm" onclick="toggleAllLeads()">
                        <i class="fas fa-check-double"></i> Tout sélectionner
                    </button>
                    <button type="submit" class="btn-seq btn-seq-success btn-seq-sm">
                        <i class="fas fa-user-plus"></i> Inscrire la sélection
                    </button>
                </div>
            </div>
            <div style="max-height:400px;overflow-y:auto">
                <table class="seq-table">
                    <thead>
                        <tr>
                            <th style="width:40px"><input type="checkbox" id="selectAll" onchange="toggleAllLeads()"></th>
                            <th>Nom</th><th>Email</th><th>Source</th><th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availableLeads as $lead): ?>
                        <tr>
                            <td><input type="checkbox" name="lead_ids[]" value="<?= $lead['id'] ?>" class="lead-checkbox"></td>
                            <td><?= htmlspecialchars(trim(($lead['first_name']??'').' '.($lead['last_name']??''))) ?></td>
                            <td><?= htmlspecialchars($lead['email']) ?></td>
                            <td><?= htmlspecialchars($lead['source']??'—') ?></td>
                            <td><span class="seq-badge" style="background:var(--surface-2);color:var(--accent)"><?= htmlspecialchars(ucfirst($lead['status']??'new')) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL ÉTAPE (Enhanced Email Editor) -->
<div class="seq-modal-overlay" id="addStepModal">
    <div class="seq-modal seq-modal-large">
        <div class="seq-modal-header">
            <h3 id="stepModalTitle"><i class="fas fa-plus"></i> Ajouter une étape</h3>
            <button class="seq-modal-close" onclick="closeModal('addStepModal')">&times;</button>
        </div>
        <form method="POST" id="stepForm">
            <input type="hidden" name="action" value="add_step" id="stepFormAction">
            <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
            <input type="hidden" name="step_id" value="" id="stepFormId">
            <div class="seq-form-row">
                <div class="seq-form-group">
                    <label>Type</label>
                    <select name="step_type" id="stepType" onchange="toggleStepFields()">
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="wait">Attente</option>
                        <option value="task">Tache</option>
                    </select>
                </div>
                <div class="seq-form-group">
                    <label>Delai (jours)</label>
                    <input type="number" name="delay_days" id="stepDelayDays" value="1" min="0" max="365">
                </div>
                <div class="seq-form-group">
                    <label>Delai (heures)</label>
                    <input type="number" name="delay_hours" id="stepDelayHours" value="0" min="0" max="23">
                </div>
            </div>
            <div id="emailFields">
                <!-- Template Selector -->
                <div class="seq-form-group">
                    <label><i class="fas fa-magic"></i> Modele d'email</label>
                    <select id="emailTemplateSelect" onchange="loadEmailTemplate()">
                        <option value="">-- Choisir un modele ou ecrire de zero --</option>
                        <optgroup label="Bienvenue / Premier contact">
                            <option value="welcome_buyer">Bienvenue acheteur</option>
                            <option value="welcome_seller">Bienvenue vendeur</option>
                            <option value="welcome_investor">Bienvenue investisseur</option>
                        </optgroup>
                        <optgroup label="Suivi / Relance">
                            <option value="followup_1">Relance J+3 - Prise de nouvelles</option>
                            <option value="followup_2">Relance J+7 - Nouveaux biens</option>
                            <option value="followup_3">Relance J+14 - Valeur ajoutee</option>
                        </optgroup>
                        <optgroup label="Estimation">
                            <option value="estimation_offer">Proposition estimation gratuite</option>
                            <option value="estimation_result">Resultat estimation</option>
                        </optgroup>
                        <optgroup label="Nurturing">
                            <option value="market_update">Actualite du marche</option>
                            <option value="testimonial">Temoignage client</option>
                            <option value="tips_buyer">Conseils acheteur</option>
                            <option value="tips_seller">Conseils vendeur</option>
                        </optgroup>
                    </select>
                </div>

                <div class="seq-form-group" style="background:var(--surface-2);padding:12px;border:1px solid var(--border);border-radius:10px">
                    <label><i class="fas fa-robot"></i> Assistant IA (Étape 2)</label>
                    <div class="seq-form-row">
                        <div>
                            <label style="font-size:.75rem;color:var(--text-3);margin-bottom:4px">Type de contact</label>
                            <select id="aiLeadType">
                                <option value="acheteur">Acheteur</option>
                                <option value="vendeur">Vendeur</option>
                                <option value="investisseur">Investisseur</option>
                                <option value="locataire">Locataire</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:.75rem;color:var(--text-3);margin-bottom:4px">Position séquence</label>
                            <input type="number" id="aiSequencePosition" min="1" max="30" value="1">
                        </div>
                    </div>
                    <div style="margin-top:8px">
                        <label style="font-size:.75rem;color:var(--text-3);margin-bottom:4px">Objectif de l'email</label>
                        <input type="text" id="aiObjective" placeholder="Ex: obtenir une réponse et planifier un appel" data-user-edited="0">
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap">
                        <button type="button" class="btn-seq btn-seq-outline btn-seq-sm" onclick="generateAiEmailDraft()">
                            <i class="fas fa-wand-magic-sparkles"></i> Générer avec IA
                        </button>
                        <small id="aiGenStatus" style="color:var(--text-3)">Remplit automatiquement l'objet + le corps HTML.</small>
                    </div>
                </div>

                <div class="seq-form-group">
                    <label>Objet de l'email</label>
                    <input type="text" name="subject" id="stepSubject" placeholder="Ex: {{prenom}}, votre projet immobilier a Bordeaux">
                </div>

                <!-- Rich Text Toolbar -->
                <div class="seq-form-group">
                    <label>Corps de l'email</label>
                    <div class="seq-editor-wrapper">
                        <div class="seq-editor-tabs">
                            <button type="button" class="seq-editor-tab active" onclick="switchEditorMode('edit', this)">
                                <i class="fas fa-edit"></i> Editeur
                            </button>
                            <button type="button" class="seq-editor-tab" onclick="switchEditorMode('html', this)">
                                <i class="fas fa-code"></i> HTML
                            </button>
                            <button type="button" class="seq-editor-tab" onclick="switchEditorMode('preview', this)">
                                <i class="fas fa-eye"></i> Apercu
                            </button>
                        </div>
                        <div class="seq-toolbar" id="seqToolbar">
                            <button type="button" onclick="editorCmd('bold')" title="Gras"><i class="fas fa-bold"></i></button>
                            <button type="button" onclick="editorCmd('italic')" title="Italique"><i class="fas fa-italic"></i></button>
                            <button type="button" onclick="editorCmd('underline')" title="Souligne"><i class="fas fa-underline"></i></button>
                            <span class="seq-toolbar-sep"></span>
                            <button type="button" onclick="editorCmd('h2')" title="Titre">H2</button>
                            <button type="button" onclick="editorCmd('h3')" title="Sous-titre">H3</button>
                            <span class="seq-toolbar-sep"></span>
                            <button type="button" onclick="editorCmd('ul')" title="Liste a puces"><i class="fas fa-list-ul"></i></button>
                            <button type="button" onclick="editorCmd('ol')" title="Liste numerotee"><i class="fas fa-list-ol"></i></button>
                            <span class="seq-toolbar-sep"></span>
                            <button type="button" onclick="editorCmd('link')" title="Lien"><i class="fas fa-link"></i></button>
                            <button type="button" onclick="editorCmd('image')" title="Image"><i class="fas fa-image"></i></button>
                            <button type="button" onclick="editorCmd('button')" title="Bouton CTA"><i class="fas fa-mouse-pointer"></i></button>
                            <span class="seq-toolbar-sep"></span>
                            <button type="button" onclick="editorCmd('hr')" title="Separateur"><i class="fas fa-minus"></i></button>
                            <button type="button" onclick="editorCmd('signature')" title="Signature"><i class="fas fa-signature"></i></button>
                            <span class="seq-toolbar-sep"></span>
                            <select onchange="editorInsertColor(this.value); this.selectedIndex=0;" title="Couleur" style="padding:4px;border-radius:4px;border:1px solid var(--border);font-size:.75rem">
                                <option value="">Couleur</option>
                                <option value="#6366f1">Violet</option>
                                <option value="#3b82f6">Bleu</option>
                                <option value="#10b981">Vert</option>
                                <option value="#f59e0b">Orange</option>
                                <option value="#ef4444">Rouge</option>
                                <option value="#1e293b">Fonce</option>
                            </select>
                        </div>
                        <!-- Visual Editor -->
                        <div id="seqEditorVisual" contenteditable="true" class="seq-editor-visual" oninput="syncEditorToTextarea()"></div>
                        <!-- HTML Source -->
                        <textarea name="body_html" id="stepBodyHtml" rows="12" class="seq-editor-source" style="display:none" placeholder="<p>Bonjour {{prenom}},</p>&#10;&#10;<p>...</p>"></textarea>
                        <!-- Preview -->
                        <div id="seqEditorPreview" class="seq-editor-preview" style="display:none"></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;margin-top:8px;flex-wrap:wrap">
                        <small style="color:var(--text-3)">Variables :</small>
                        <div class="seq-vars-list" style="margin-top:0">
                            <?php foreach ($templateVars as $var => $desc): ?>
                            <span class="seq-var-tag" onclick="insertVar('<?= $var ?>')" title="<?= $desc ?>"><?= $var ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="smsFields" style="display:none">
                <div class="seq-form-group">
                    <label>Texte SMS (max 480 car.)</label>
                    <textarea name="sms_text" id="stepSmsText" rows="4" maxlength="480"></textarea>
                    <small style="color:var(--text-3)"><span id="smsCharCount">0</span>/480</small>
                </div>
            </div>
            <div id="taskFields" style="display:none">
                <div class="seq-form-group">
                    <label>Description de la tache</label>
                    <textarea name="task_description" id="stepTaskDesc" rows="4" placeholder="Appeler le lead..."></textarea>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:24px">
                <button type="submit" class="btn-seq btn-seq-primary" id="stepSubmitBtn">
                    <i class="fas fa-save"></i> Ajouter l'etape
                </button>
                <button type="button" class="btn-seq btn-seq-outline" onclick="closeModal('addStepModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php endif; // fin action=edit ?>

<script>
// ====================================================
// Tab Navigation
// ====================================================
function switchTab(tab, btn) {
    document.querySelectorAll('.seq-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.seq-tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    if (btn) btn.classList.add('active');
}

// ====================================================
// Editor Mode (Edit / HTML / Preview)
// ====================================================
let currentEditorMode = 'edit';
function switchEditorMode(mode, btn) {
    currentEditorMode = mode;
    document.querySelectorAll('.seq-editor-tab').forEach(t => t.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const visual  = document.getElementById('seqEditorVisual');
    const source  = document.getElementById('stepBodyHtml');
    const preview = document.getElementById('seqEditorPreview');
    const toolbar = document.getElementById('seqToolbar');

    visual.style.display  = mode === 'edit'    ? 'block' : 'none';
    source.style.display  = mode === 'html'    ? 'block' : 'none';
    preview.style.display = mode === 'preview' ? 'block' : 'none';
    toolbar.style.display = mode === 'edit'    ? 'flex'  : 'none';

    if (mode === 'html') {
        source.value = visual.innerHTML;
    } else if (mode === 'edit') {
        visual.innerHTML = source.value;
    } else if (mode === 'preview') {
        renderPreview();
    }
}

function syncEditorToTextarea() {
    document.getElementById('stepBodyHtml').value = document.getElementById('seqEditorVisual').innerHTML;
}

function renderPreview() {
    const html = document.getElementById('seqEditorVisual').innerHTML;
    const subject = document.getElementById('stepSubject').value || '(Sans objet)';
    const fromName = <?= json_encode($sequence['from_name'] ?? 'Eduardo De Sul') ?>;
    const fromEmail = <?= json_encode($sequence['from_email'] ?? 'contact@eduardodesul.fr') ?>;

    document.getElementById('seqEditorPreview').innerHTML = `
        <div class="seq-preview-frame">
            <div class="seq-preview-header">
                <strong>Objet : ${subject.replace(/</g,'&lt;')}</strong>
                De : ${fromName} &lt;${fromEmail}&gt;
            </div>
            <div class="seq-preview-body">${html}</div>
            <div class="seq-preview-footer">
                <a href="#" style="color:#94a3b8">Se desinscrire</a> | Eduardo De Sul Immobilier
            </div>
        </div>`;
}

// ====================================================
// Rich Text Toolbar Commands
// ====================================================
function editorCmd(cmd) {
    const editor = document.getElementById('seqEditorVisual');
    editor.focus();
    const sel = window.getSelection();

    switch(cmd) {
        case 'bold':
            document.execCommand('bold', false, null);
            break;
        case 'italic':
            document.execCommand('italic', false, null);
            break;
        case 'underline':
            document.execCommand('underline', false, null);
            break;
        case 'h2':
            document.execCommand('formatBlock', false, '<h2>');
            break;
        case 'h3':
            document.execCommand('formatBlock', false, '<h3>');
            break;
        case 'ul':
            document.execCommand('insertUnorderedList', false, null);
            break;
        case 'ol':
            document.execCommand('insertOrderedList', false, null);
            break;
        case 'link': {
            const url = prompt('URL du lien :', 'https://');
            if (url) document.execCommand('createLink', false, url);
            break;
        }
        case 'image': {
            const imgUrl = prompt('URL de l\'image :', 'https://');
            if (imgUrl) document.execCommand('insertHTML', false, '<img src="'+imgUrl+'" alt="Image" style="max-width:100%;border-radius:8px;margin:8px 0">');
            break;
        }
        case 'button': {
            const btnUrl  = prompt('URL du bouton :', 'https://eduardo-desul-immobilier.fr');
            const btnText = prompt('Texte du bouton :', 'Decouvrir nos biens');
            if (btnUrl && btnText) {
                document.execCommand('insertHTML', false,
                    '<p><a href="'+btnUrl+'" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">'+btnText+'</a></p>');
            }
            break;
        }
        case 'hr':
            document.execCommand('insertHTML', false, '<hr style="border:none;border-top:1px solid #e2e8f0;margin:16px 0">');
            break;
        case 'signature':
            document.execCommand('insertHTML', false,
                '<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px">' +
                '<strong>{{agent_nom}}</strong><br>' +
                'Eduardo De Sul Immobilier<br>' +
                'Tel : {{agent_tel}}<br>' +
                '<a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a>' +
                '</div>');
            break;
    }
    syncEditorToTextarea();
}

function editorInsertColor(color) {
    if (!color) return;
    document.getElementById('seqEditorVisual').focus();
    document.execCommand('foreColor', false, color);
    syncEditorToTextarea();
}

// ====================================================
// Email Templates
// ====================================================
const emailTemplates = {
    welcome_buyer: {
        subject: "{{prenom}}, bienvenue chez Eduardo De Sul Immobilier",
        body: `<p>Bonjour {{prenom}},</p>
<p>Merci pour votre interet pour nos services ! Je suis ravi de vous accompagner dans votre <strong>projet d'achat immobilier a Bordeaux</strong>.</p>
<p>En tant qu'expert du marche bordelais, je peux vous aider a :</p>
<ul>
<li>Trouver le bien ideal selon vos criteres et votre budget</li>
<li>Negocier les meilleures conditions d'achat</li>
<li>Vous accompagner de A a Z jusqu'a la remise des cles</li>
</ul>
<p>N'hesitez pas a me partager vos criteres de recherche pour que je puisse commencer a travailler pour vous.</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Voir nos biens disponibles</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    welcome_seller: {
        subject: "{{prenom}}, estimons ensemble la valeur de votre bien",
        body: `<p>Bonjour {{prenom}},</p>
<p>Merci de nous avoir contactes concernant la <strong>vente de votre bien immobilier</strong>.</p>
<p>Le marche immobilier a Bordeaux est actuellement tres dynamique, et c'est le moment ideal pour valoriser votre patrimoine.</p>
<p>Ce que je vous propose :</p>
<ul>
<li><strong>Une estimation gratuite et precise</strong> de votre bien basee sur les transactions recentes du quartier</li>
<li>Une strategie de mise en vente optimisee pour obtenir le meilleur prix</li>
<li>Un accompagnement personnalise jusqu'a la signature chez le notaire</li>
</ul>
<p>Quand seriez-vous disponible pour un premier echange telephonique ?</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Demander une estimation gratuite</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    welcome_investor: {
        subject: "{{prenom}}, decouvrez nos opportunites d'investissement",
        body: `<p>Bonjour {{prenom}},</p>
<p>Merci pour votre interet pour l'<strong>investissement immobilier a Bordeaux</strong>.</p>
<p>Bordeaux offre d'excellentes opportunites de rendement grace a :</p>
<ul>
<li>Une forte demande locative (ville universitaire, bassin d'emploi dynamique)</li>
<li>Des quartiers en pleine transformation avec un fort potentiel de plus-value</li>
<li>Des dispositifs fiscaux avantageux (Pinel, LMNP, Denormandie)</li>
</ul>
<p>Je serais ravi d'echanger sur votre strategie d'investissement et vos objectifs de rendement.</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Voir les opportunites</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    followup_1: {
        subject: "{{prenom}}, ou en est votre projet immobilier ?",
        body: `<p>Bonjour {{prenom}},</p>
<p>Je me permets de prendre de vos nouvelles concernant votre projet immobilier.</p>
<p>Avez-vous eu le temps de reflechir a nos echanges ? Je reste a votre entiere disposition pour :</p>
<ul>
<li>Repondre a vos questions</li>
<li>Organiser des visites supplementaires</li>
<li>Ajuster vos criteres de recherche si necessaire</li>
</ul>
<p>N'hesitez pas a me rappeler ou a repondre directement a cet email.</p>
<p>A tres bientot,</p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    followup_2: {
        subject: "{{prenom}}, nouveaux biens qui pourraient vous interesser",
        body: `<p>Bonjour {{prenom}},</p>
<p>De <strong>nouveaux biens</strong> viennent d'arriver sur le marche et correspondent a vos criteres de recherche !</p>
<p>Je vous invite a decouvrir notre selection actualisee :</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Voir les nouveaux biens</a></p>
<p>Certains de ces biens sont en exclusivite et ne resteront pas longtemps sur le marche. Si l'un d'entre eux vous interesse, n'hesitez pas a me contacter rapidement pour organiser une visite.</p>
<p>A bientot,</p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    followup_3: {
        subject: "{{prenom}}, 3 conseils pour reussir votre projet immobilier",
        body: `<p>Bonjour {{prenom}},</p>
<p>Je souhaitais partager avec vous <strong>3 conseils essentiels</strong> pour reussir votre projet immobilier :</p>
<h3>1. Definissez bien votre budget</h3>
<p>Incluez tous les frais annexes (notaire, travaux eventuels, demenagement) dans votre calcul global.</p>
<h3>2. Ne negligez pas le quartier</h3>
<p>Visitez a differentes heures de la journee, renseignez-vous sur les projets urbains a venir et les commodites.</p>
<h3>3. Faites-vous accompagner</h3>
<p>Un professionnel de l'immobilier vous fait gagner du temps, de l'argent et vous securise juridiquement.</p>
<p>Je suis la pour vous accompagner a chaque etape. Contactez-moi quand vous le souhaitez !</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Me contacter</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    estimation_offer: {
        subject: "{{prenom}}, combien vaut votre bien a Bordeaux ?",
        body: `<p>Bonjour {{prenom}},</p>
<p>Savez-vous combien vaut votre bien immobilier aujourd'hui ?</p>
<p>Le marche bordelais a beaucoup evolue ces derniers mois. Je vous propose une <strong>estimation gratuite et sans engagement</strong> de votre bien, basee sur :</p>
<ul>
<li>Les transactions recentes dans votre quartier</li>
<li>L'etat et les specificites de votre bien</li>
<li>Les tendances actuelles du marche local</li>
</ul>
<p>Cette estimation est entierement gratuite et confidentielle.</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Demander mon estimation gratuite</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    estimation_result: {
        subject: "{{prenom}}, voici l'estimation de votre bien",
        body: `<p>Bonjour {{prenom}},</p>
<p>Suite a notre echange, voici les elements cles de l'<strong>estimation de votre bien</strong> :</p>
<p><em>[A completer avec les details de l'estimation]</em></p>
<p>Cette estimation est basee sur une analyse comparative des transactions recentes dans votre secteur.</p>
<p>Pour aller plus loin, je vous propose :</p>
<ul>
<li>Un rendez-vous pour discuter de votre strategie de vente</li>
<li>Une presentation de notre plan marketing personnalise</li>
<li>Un accompagnement complet jusqu'a la signature</li>
</ul>
<p>Quand seriez-vous disponible pour un echange ?</p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    market_update: {
        subject: "{{prenom}}, les tendances du marche immobilier a Bordeaux",
        body: `<p>Bonjour {{prenom}},</p>
<p>Voici un point sur le <strong>marche immobilier bordelais</strong> :</p>
<h3>Tendances actuelles</h3>
<ul>
<li>Les prix restent stables dans les quartiers recherches</li>
<li>La demande locative est toujours forte grace a l'attractivite de la metropole</li>
<li>De nouveaux projets urbains valorisent certains secteurs</li>
</ul>
<h3>Quartiers a surveiller</h3>
<p>Certains quartiers offrent encore un excellent rapport qualite/prix avec un fort potentiel de valorisation.</p>
<p>Envie d'en savoir plus ? Je suis disponible pour echanger sur les opportunites du moment.</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Decouvrir nos analyses</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    testimonial: {
        subject: "{{prenom}}, decouvrez comment nous avons aide nos clients",
        body: `<p>Bonjour {{prenom}},</p>
<p>Rien ne vaut le temoignage de nos clients satisfaits :</p>
<blockquote style="border-left:3px solid #6366f1;padding:12px 20px;margin:16px 0;background:#f8fafc;border-radius:0 8px 8px 0;font-style:italic;color:#475569">
"Eduardo nous a accompagnes tout au long de notre achat. Professionnel, reactif et toujours de bon conseil. Nous recommandons vivement ses services !"<br>
<strong style="font-style:normal;color:#1e293b">— Marie et Pierre D., Bordeaux Chartrons</strong>
</blockquote>
<p>Comme eux, faites confiance a un professionnel pour votre projet immobilier.</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Voir plus de temoignages</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    tips_buyer: {
        subject: "{{prenom}}, 5 erreurs a eviter quand on achete un bien",
        body: `<p>Bonjour {{prenom}},</p>
<p>L'achat immobilier est un moment important. Voici <strong>5 erreurs courantes</strong> a eviter :</p>
<h3>1. Ne pas definir son budget en amont</h3>
<p>Faites valider votre capacite d'emprunt avant de visiter pour eviter les deceptions.</p>
<h3>2. Se precipiter sur le premier bien</h3>
<p>Prenez le temps de comparer, revisitez si necessaire.</p>
<h3>3. Negliger les charges et travaux</h3>
<p>Renseignez-vous sur les charges de copropriete, la taxe fonciere et les travaux prevus.</p>
<h3>4. Ne pas verifier le DPE</h3>
<p>Le diagnostic energetique impacte directement vos futures factures et la valeur de revente.</p>
<h3>5. Acheter sans accompagnement</h3>
<p>Un agent immobilier vous protege et vous fait gagner du temps et de l'argent.</p>
<p>Je suis la pour vous guider. N'hesitez pas !</p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    },
    tips_seller: {
        subject: "{{prenom}}, comment vendre votre bien au meilleur prix",
        body: `<p>Bonjour {{prenom}},</p>
<p>Vous envisagez de vendre ? Voici nos <strong>conseils pour maximiser la valeur</strong> de votre bien :</p>
<h3>Soignez la premiere impression</h3>
<p>Un bien propre, range et lumineux se vend plus vite et plus cher. Pensez au home staging !</p>
<h3>Fixez le bon prix des le depart</h3>
<p>Un bien survalue reste en ligne trop longtemps et finit par se vendre en dessous du marche.</p>
<h3>Preparez vos diagnostics</h3>
<p>Avoir tous les diagnostics prets rassure les acheteurs et accelere la vente.</p>
<h3>Choisissez le bon partenaire</h3>
<p>Un agent immobilier local connait les acheteurs potentiels et sait mettre en valeur votre bien.</p>
<p><a href="{{site_url}}" class="email-cta-btn" style="display:inline-block;padding:12px 28px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600">Estimer mon bien gratuitement</a></p>
<div class="email-signature" style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:14px"><strong>{{agent_nom}}</strong><br>Eduardo De Sul Immobilier<br>Tel : {{agent_tel}}<br><a href="{{site_url}}" style="color:#6366f1">{{site_url}}</a></div>`
    }
};


const sequenceContext = {
    targetSegment: <?= json_encode($sequence['target_segment'] ?? '') ?>,
    totalSteps: <?= json_encode(count($steps)) ?>
};

const aiObjectivePresets = {
    acheteur: {
        early: 'Qualifier les critères de recherche et proposer un premier échange téléphonique.',
        mid: 'Partager une sélection ciblée de biens et obtenir une réponse sur les préférences.',
        late: 'Lever les derniers freins et planifier une visite ou une offre.'
    },
    vendeur: {
        early: 'Cadrer le projet de vente et proposer un rendez-vous estimation.',
        mid: 'Présenter la stratégie de mise en vente et valider les prochaines actions.',
        late: 'Accélérer la décision de mandat avec un plan de commercialisation concret.'
    },
    investisseur: {
        early: 'Qualifier les objectifs de rendement et le niveau de risque accepté.',
        mid: 'Proposer des opportunités alignées et obtenir un retour sur les critères financiers.',
        late: 'Faire avancer la décision avec un plan d’action chiffré.'
    },
    locataire: {
        early: 'Comprendre le besoin logement et cadrer les critères prioritaires.',
        mid: 'Proposer des options adaptées et confirmer les disponibilités de visite.',
        late: 'Faciliter la prise de décision et sécuriser les prochaines démarches.'
    }
};

let aiObjectiveTouchedByUser = false;

function resolveLeadTypeFromSequence(defaultType = 'acheteur') {
    const candidate = String(sequenceContext.targetSegment || '').toLowerCase();
    return ['acheteur', 'vendeur', 'investisseur', 'locataire'].includes(candidate) ? candidate : defaultType;
}

function resolvePresetObjective(leadType, position) {
    const profile = aiObjectivePresets[leadType] || aiObjectivePresets.acheteur;
    if (position <= 1) return profile.early;
    if (position <= 3) return profile.mid;
    return profile.late;
}

function autoPrefillAiAssistant(options = {}) {
    const { forceObjective = false, stepOrder = null } = options;
    const leadTypeInput = document.getElementById('aiLeadType');
    const positionInput = document.getElementById('aiSequencePosition');
    const objectiveInput = document.getElementById('aiObjective');
    if (!leadTypeInput || !positionInput || !objectiveInput) return;

    const resolvedLeadType = resolveLeadTypeFromSequence(leadTypeInput.value || 'acheteur');
    leadTypeInput.value = resolvedLeadType;

    const computedPosition = Math.max(1, parseInt(stepOrder || (sequenceContext.totalSteps + 1), 10) || 1);
    positionInput.value = computedPosition;

    const shouldFillObjective = forceObjective || !aiObjectiveTouchedByUser || !objectiveInput.value.trim();
    if (shouldFillObjective) {
        objectiveInput.value = resolvePresetObjective(resolvedLeadType, computedPosition);
        objectiveInput.dataset.userEdited = '0';
        aiObjectiveTouchedByUser = false;
    }
}

function loadEmailTemplate() {
    const sel = document.getElementById('emailTemplateSelect');
    const key = sel.value;
    if (!key || !emailTemplates[key]) return;

    const tpl = emailTemplates[key];
    document.getElementById('stepSubject').value = tpl.subject;
    document.getElementById('seqEditorVisual').innerHTML = tpl.body;
    document.getElementById('stepBodyHtml').value = tpl.body;

    // Reset to edit mode
    switchEditorMode('edit', document.querySelector('.seq-editor-tab'));
}

async function generateAiEmailDraft() {
    const status = document.getElementById('aiGenStatus');
    const leadType = document.getElementById('aiLeadType').value;
    const position = parseInt(document.getElementById('aiSequencePosition').value || '1', 10);
    const objective = (document.getElementById('aiObjective').value || '').trim();

    if (!objective) {
        status.textContent = "Ajoutez un objectif pour générer un brouillon IA.";
        status.style.color = 'var(--red)';
        return;
    }

    status.textContent = 'Génération IA en cours...';
    status.style.color = 'var(--text-3)';

    try {
        const response = await fetch('/admin/api/router.php?module=ai&action=generate_sequence_email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': <?= json_encode($_SESSION['auth_csrf_token'] ?? '') ?>
            },
            body: JSON.stringify({
                lead_type: leadType,
                sequence_position: position,
                objective: objective,
                language: 'fr'
            })
        });

        const data = await response.json();
        if (!data.success || !data.data) {
            throw new Error(data.message || 'Erreur IA');
        }

        document.getElementById('stepSubject').value = data.data.subject || '';
        document.getElementById('seqEditorVisual').innerHTML = data.data.body_html || '';
        document.getElementById('stepBodyHtml').value = data.data.body_html || '';
        switchEditorMode('edit', document.querySelector('.seq-editor-tab'));

        status.textContent = 'Brouillon IA injecté dans l’éditeur.';
        status.style.color = 'var(--green)';
    } catch (error) {
        status.textContent = 'Génération impossible : ' + error.message;
        status.style.color = 'var(--red)';
    }
}

// ====================================================
// Modal Open / Close / Step Management
// ====================================================
function openAddStepModal() {
    document.getElementById('stepModalTitle').innerHTML = '<i class="fas fa-plus"></i> Ajouter une etape';
    document.getElementById('stepFormAction').value = 'add_step';
    document.getElementById('stepFormId').value = '';
    document.getElementById('stepType').value = 'email';
    document.getElementById('stepDelayDays').value = 1;
    document.getElementById('stepDelayHours').value = 0;
    document.getElementById('stepSubject').value = '';
    document.getElementById('stepBodyHtml').value = '';
    document.getElementById('seqEditorVisual').innerHTML = '';
    document.getElementById('stepSmsText').value = '';
    document.getElementById('stepTaskDesc').value = '';
    document.getElementById('emailTemplateSelect').selectedIndex = 0;
    document.getElementById('stepSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Ajouter l\'etape';
    aiObjectiveTouchedByUser = false;
    toggleStepFields();
    autoPrefillAiAssistant({ forceObjective: true, stepOrder: sequenceContext.totalSteps + 1 });
    switchEditorMode('edit', document.querySelector('.seq-editor-tab'));
    document.getElementById('addStepModal').classList.add('active');
}

function maybeOpenStepModalFromQuery() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('open_add_step') !== '1') return;

    openAddStepModal();

    const requestedType = params.get('step_type');
    if (requestedType && ['email', 'sms', 'wait', 'task'].includes(requestedType)) {
        document.getElementById('stepType').value = requestedType;
        toggleStepFields();
    }

    if (requestedType === 'email') {
        document.getElementById('stepSubject').focus();
    }
}

function openEditStepModal(step) {
    document.getElementById('stepModalTitle').innerHTML = '<i class="fas fa-edit"></i> Modifier l\'etape ' + step.step_order;
    document.getElementById('stepFormAction').value = 'update_step';
    document.getElementById('stepFormId').value = step.id;
    document.getElementById('stepType').value = step.step_type;
    document.getElementById('stepDelayDays').value = step.delay_days;
    document.getElementById('stepDelayHours').value = step.delay_hours;
    document.getElementById('stepSubject').value = step.subject || '';
    document.getElementById('stepBodyHtml').value = step.body_html || '';
    document.getElementById('seqEditorVisual').innerHTML = step.body_html || '';
    document.getElementById('stepSmsText').value = step.sms_text || '';
    document.getElementById('stepTaskDesc').value = step.task_description || '';
    document.getElementById('emailTemplateSelect').selectedIndex = 0;
    document.getElementById('stepSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Mettre a jour';
    aiObjectiveTouchedByUser = false;
    toggleStepFields();
    autoPrefillAiAssistant({ forceObjective: true, stepOrder: step.step_order || 1 });
    switchEditorMode('edit', document.querySelector('.seq-editor-tab'));
    document.getElementById('addStepModal').classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function toggleStepFields() {
    const type = document.getElementById('stepType').value;
    document.getElementById('emailFields').style.display = type === 'email' ? 'block' : 'none';
    document.getElementById('smsFields').style.display  = type === 'sms'   ? 'block' : 'none';
    document.getElementById('taskFields').style.display = type === 'task'  ? 'block' : 'none';
}


(function initAiAutofillBindings() {
    const leadTypeInput = document.getElementById('aiLeadType');
    const positionInput = document.getElementById('aiSequencePosition');
    const objectiveInput = document.getElementById('aiObjective');
    if (!leadTypeInput || !positionInput || !objectiveInput) return;

    leadTypeInput.addEventListener('change', () => autoPrefillAiAssistant({ forceObjective: true, stepOrder: parseInt(positionInput.value || '1', 10) || 1 }));
    positionInput.addEventListener('change', () => autoPrefillAiAssistant({ forceObjective: true, stepOrder: parseInt(positionInput.value || '1', 10) || 1 }));
    objectiveInput.addEventListener('input', () => {
        aiObjectiveTouchedByUser = true;
        objectiveInput.dataset.userEdited = '1';
    });

    autoPrefillAiAssistant({ forceObjective: true, stepOrder: sequenceContext.totalSteps + 1 });
})();

// ====================================================
// Variable Insertion
// ====================================================
function insertVar(varName) {
    const type = document.getElementById('stepType').value;

    if (type === 'sms') {
        const target = document.getElementById('stepSmsText');
        const s = target.selectionStart, e = target.selectionEnd;
        target.value = target.value.substring(0,s) + varName + target.value.substring(e);
        target.selectionStart = target.selectionEnd = s + varName.length;
        target.focus();
    } else if (type === 'task') {
        const target = document.getElementById('stepTaskDesc');
        const s = target.selectionStart, e = target.selectionEnd;
        target.value = target.value.substring(0,s) + varName + target.value.substring(e);
        target.selectionStart = target.selectionEnd = s + varName.length;
        target.focus();
    } else {
        // Insert into visual editor
        const editor = document.getElementById('seqEditorVisual');
        editor.focus();
        document.execCommand('insertText', false, varName);
        syncEditorToTextarea();
    }
}

// ====================================================
// Form submission - sync editor
// ====================================================
document.getElementById('stepForm').addEventListener('submit', function() {
    if (currentEditorMode === 'edit') {
        document.getElementById('stepBodyHtml').value = document.getElementById('seqEditorVisual').innerHTML;
    }
});

// ====================================================
// SMS Character Counter
// ====================================================
const smsTa = document.getElementById('stepSmsText');
if (smsTa) smsTa.addEventListener('input', function() {
    document.getElementById('smsCharCount').textContent = this.value.length;
});

// ====================================================
// Lead Selection
// ====================================================
function toggleAllLeads() {
    const boxes = document.querySelectorAll('.lead-checkbox');
    const all = [...boxes].every(c => c.checked);
    boxes.forEach(c => c.checked = !all);
    const sa = document.getElementById('selectAll');
    if (sa) sa.checked = !all;
}

// ====================================================
// Global Event Listeners
// ====================================================
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.seq-modal-overlay.active').forEach(m => m.classList.remove('active'));
});
document.querySelectorAll('.seq-modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('active'); });
});

maybeOpenStepModalFromQuery();
</script>
