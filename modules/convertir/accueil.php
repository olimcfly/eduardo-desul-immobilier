<?php
$pageTitle = 'Convertir';
$pageDescription = 'Transformez vos contacts en clients signés';

const CONVERTIR_CRM_STATUSES = [
    'nouveau' => 'Nouveau',
    'contacte' => 'Contacté',
    'rdv_planifie' => 'RDV planifié',
    'mandat_signe' => 'Mandat signé',
    'perdu' => 'Perdu',
];

ensureConvertirCrmTables();
ensureConvertirPostRdvTables();
handleConvertirCrmPost();
handleConvertirPostRdvPost();

$action = strtolower((string)($_GET['action'] ?? ''));
$crmContactsView = $action === 'crm-contacts';
$postRdvView = $action === 'suivi-post-rdv';

$crmLeads = [];
$selectedLead = null;
$selectedLeadHistory = [];
$postRdvLeads = [];
$postRdvSequences = [];
$selectedPostRdvLead = null;
$selectedPostRdvLog = [];

if ($crmContactsView) {
    $crmLeads = listConvertirCrmLeads();

    $leadId = (int)($_GET['lead_id'] ?? 0);
    if ($leadId <= 0 && $crmLeads) {
        $leadId = (int)$crmLeads[0]['id'];
    }

    if ($leadId > 0) {
        $selectedLead = findConvertirCrmLeadById($leadId);
        if ($selectedLead !== null) {
            $selectedLeadHistory = listConvertirCrmHistory($leadId);
        }
    }
}

if ($postRdvView) {
    $postRdvLeads = listConvertirPostRdvLeads();
    $postRdvSequences = listConvertirPostRdvSequences();

    $leadId = (int)($_GET['lead_id'] ?? 0);
    if ($leadId <= 0 && $postRdvLeads) {
        $leadId = (int)$postRdvLeads[0]['id'];
    }

    if ($leadId > 0) {
        $selectedPostRdvLead = findConvertirPostRdvLeadById($leadId);
        if ($selectedPostRdvLead !== null) {
            $selectedPostRdvLog = listConvertirPostRdvLog($leadId);
        }
    }
}

function handleConvertirCrmPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $intent = (string)($_POST['crm_intent'] ?? '');
    if (!in_array($intent, ['update_status', 'add_note'], true)) {
        return;
    }

    verifyCsrf();

    $leadId = (int)($_POST['lead_id'] ?? 0);
    if ($leadId <= 0 || !convertirLeadExists($leadId)) {
        Session::flash('error', 'Lead introuvable.');
        redirect('/admin?module=convertir&action=crm-contacts');
    }

    if ($intent === 'update_status') {
        $status = normalizeConvertirStatus((string)($_POST['status'] ?? 'nouveau'));
        $note = trim((string)($_POST['note'] ?? ''));
        updateConvertirLeadStatus($leadId, $status, $note);
        Session::flash('success', 'Statut du lead mis à jour.');
    }

    if ($intent === 'add_note') {
        $note = trim((string)($_POST['note'] ?? ''));
        if ($note === '') {
            Session::flash('error', 'Ajoutez une note avant de valider.');
        } else {
            addConvertirLeadNote($leadId, $note);
            Session::flash('success', 'Note ajoutée à l\'historique.');
        }
    }

    redirect('/admin?module=convertir&action=crm-contacts&lead_id=' . $leadId);
}

function ensureConvertirCrmTables(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS crm_lead_tracking (
        lead_id INT UNSIGNED NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT "nouveau",
        note TEXT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (lead_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    db()->exec('CREATE TABLE IF NOT EXISTS crm_lead_interactions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lead_id INT UNSIGNED NOT NULL,
        interaction_type VARCHAR(20) NOT NULL,
        old_value VARCHAR(80) NULL,
        new_value VARCHAR(80) NULL,
        note TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_lead_created (lead_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function ensureConvertirPostRdvTables(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS crm_post_rdv_sequences (
        lead_id INT UNSIGNED NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT "inactif",
        step_index SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        next_action_at DATETIME NULL,
        last_action_at DATETIME NULL,
        preferred_channel VARCHAR(20) NOT NULL DEFAULT "email",
        cadence_days SMALLINT UNSIGNED NOT NULL DEFAULT 3,
        notes TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (lead_id),
        INDEX idx_status_next (status, next_action_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    db()->exec('CREATE TABLE IF NOT EXISTS crm_post_rdv_logs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lead_id INT UNSIGNED NOT NULL,
        event_type VARCHAR(30) NOT NULL,
        channel VARCHAR(20) NOT NULL DEFAULT "email",
        message TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_lead_created (lead_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function handleConvertirPostRdvPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $intent = (string)($_POST['post_rdv_intent'] ?? '');
    if (!in_array($intent, ['start_sequence', 'log_followup', 'update_sequence_state'], true)) {
        return;
    }

    verifyCsrf();

    $leadId = (int)($_POST['lead_id'] ?? 0);
    if ($leadId <= 0 || !convertirPostRdvLeadExists($leadId)) {
        Session::flash('error', 'Lead introuvable pour le suivi post-RDV.');
        redirect('/admin?module=convertir&action=suivi-post-rdv');
    }

    if ($intent === 'start_sequence') {
        $channel = normalizePostRdvChannel((string)($_POST['preferred_channel'] ?? 'email'));
        $cadence = max(1, min(21, (int)($_POST['cadence_days'] ?? 3)));
        $notes = trim((string)($_POST['notes'] ?? ''));
        startConvertirPostRdvSequence($leadId, $channel, $cadence, $notes);
        Session::flash('success', 'Séquence post-RDV activée.');
    }

    if ($intent === 'log_followup') {
        $channel = normalizePostRdvChannel((string)($_POST['channel'] ?? 'email'));
        $message = trim((string)($_POST['message'] ?? ''));
        if ($message === '') {
            Session::flash('error', 'Ajoutez un message de relance avant de valider.');
        } else {
            logConvertirPostRdvFollowup($leadId, $channel, $message);
            Session::flash('success', 'Relance enregistrée et prochaine action recalculée.');
        }
    }

    if ($intent === 'update_sequence_state') {
        $status = normalizePostRdvStatus((string)($_POST['status'] ?? 'actif'));
        updateConvertirPostRdvSequenceState($leadId, $status);
        Session::flash('success', 'Statut de la séquence mis à jour.');
    }

    redirect('/admin?module=convertir&action=suivi-post-rdv&lead_id=' . $leadId);
}

function normalizeConvertirStatus(string $status): string
{
    $status = strtolower(trim($status));
    return array_key_exists($status, CONVERTIR_CRM_STATUSES) ? $status : 'nouveau';
}

function convertirLeadExists(int $leadId): bool
{
    $leadSource = convertirLeadSource();
    if ($leadSource === null) {
        return false;
    }

    $stmt = db()->prepare('SELECT id FROM ' . $leadSource['table'] . ' WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $leadId]);
    return (bool)$stmt->fetchColumn();
}

function updateConvertirLeadStatus(int $leadId, string $newStatus, string $note = ''): void
{
    $stmt = db()->prepare('SELECT status FROM crm_lead_tracking WHERE lead_id = :lead_id LIMIT 1');
    $stmt->execute([':lead_id' => $leadId]);
    $oldStatus = (string)($stmt->fetchColumn() ?: 'nouveau');

    $upsert = db()->prepare('INSERT INTO crm_lead_tracking (lead_id, status, note, updated_at)
        VALUES (:lead_id, :status, :note, NOW())
        ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note), updated_at = NOW()');
    $upsert->execute([
        ':lead_id' => $leadId,
        ':status' => $newStatus,
        ':note' => $note,
    ]);

    if ($oldStatus !== $newStatus || $note !== '') {
        $log = db()->prepare('INSERT INTO crm_lead_interactions (lead_id, interaction_type, old_value, new_value, note, created_at)
            VALUES (:lead_id, "status", :old_value, :new_value, :note, NOW())');
        $log->execute([
            ':lead_id' => $leadId,
            ':old_value' => $oldStatus,
            ':new_value' => $newStatus,
            ':note' => $note,
        ]);
    }
}

function addConvertirLeadNote(int $leadId, string $note): void
{
    $upsert = db()->prepare('INSERT INTO crm_lead_tracking (lead_id, status, note, updated_at)
        VALUES (:lead_id, "nouveau", :note, NOW())
        ON DUPLICATE KEY UPDATE note = VALUES(note), updated_at = NOW()');
    $upsert->execute([
        ':lead_id' => $leadId,
        ':note' => $note,
    ]);

    $log = db()->prepare('INSERT INTO crm_lead_interactions (lead_id, interaction_type, note, created_at)
        VALUES (:lead_id, "note", :note, NOW())');
    $log->execute([
        ':lead_id' => $leadId,
        ':note' => $note,
    ]);
}

function listConvertirCrmLeads(): array
{
    $leadSource = convertirLeadSource();
    if ($leadSource === null) {
        return [];
    }

    $sql = 'SELECT ' . $leadSource['select'] . ',
                   COALESCE(t.status, "nouveau") AS crm_status,
                   t.note AS crm_note,
                   t.updated_at AS crm_updated_at
            FROM ' . $leadSource['table'] . ' l
            LEFT JOIN crm_lead_tracking t ON t.lead_id = l.id
            ORDER BY l.created_at DESC
            LIMIT 500';

    return db()->query($sql)->fetchAll();
}

function findConvertirCrmLeadById(int $leadId): ?array
{
    $leadSource = convertirLeadSource();
    if ($leadSource === null) {
        return null;
    }

    $stmt = db()->prepare('SELECT ' . $leadSource['select'] . ',
                                  COALESCE(t.status, "nouveau") AS crm_status,
                                  t.note AS crm_note,
                                  t.updated_at AS crm_updated_at
                           FROM ' . $leadSource['table'] . ' l
                           LEFT JOIN crm_lead_tracking t ON t.lead_id = l.id
                           WHERE l.id = :id
                           LIMIT 1');
    $stmt->execute([':id' => $leadId]);
    $lead = $stmt->fetch();

    return $lead ?: null;
}

function convertirLeadSource(): ?array
{
    static $source = null;
    static $loaded = false;

    if ($loaded) {
        return $source;
    }
    $loaded = true;

    if (convertirTableExists('leads')) {
        $source = [
            'table' => 'leads',
            'label' => 'leads',
            'select' => 'l.id, l.nom, l.email, l.telephone, l.message, l.source, l.persona, l.guide_titre, l.created_at',
        ];
        return $source;
    }

    if (convertirTableExists('crm_leads')) {
        $source = [
            'table' => 'crm_leads',
            'label' => 'crm_leads',
            'select' => 'l.id,
                         TRIM(CONCAT(COALESCE(l.first_name, ""), " ", COALESCE(l.last_name, ""))) AS nom,
                         l.email,
                         l.phone AS telephone,
                         l.notes AS message,
                         l.source_type AS source,
                         l.pipeline AS persona,
                         l.intent AS guide_titre,
                         l.created_at',
        ];
        return $source;
    }

    return null;
}

function convertirTableExists(string $table): bool
{
    $stmt = db()->prepare('SELECT 1
                           FROM information_schema.tables
                           WHERE table_schema = DATABASE() AND table_name = :table
                           LIMIT 1');
    $stmt->execute([':table' => $table]);
    return (bool)$stmt->fetchColumn();
}

function listConvertirCrmHistory(int $leadId): array
{
    $stmt = db()->prepare('SELECT interaction_type, old_value, new_value, note, created_at
                           FROM crm_lead_interactions
                           WHERE lead_id = :lead_id
                           ORDER BY created_at DESC, id DESC
                           LIMIT 100');
    $stmt->execute([':lead_id' => $leadId]);
    return $stmt->fetchAll();
}

function convertirPostRdvLeadExists(int $leadId): bool
{
    $stmt = db()->prepare('SELECT id FROM crm_leads WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $leadId]);
    return (bool)$stmt->fetchColumn();
}

function normalizePostRdvChannel(string $channel): string
{
    $channel = strtolower(trim($channel));
    return in_array($channel, ['email', 'sms', 'appel'], true) ? $channel : 'email';
}

function normalizePostRdvStatus(string $status): string
{
    $status = strtolower(trim($status));
    return in_array($status, ['actif', 'pause', 'termine', 'inactif'], true) ? $status : 'actif';
}

function startConvertirPostRdvSequence(int $leadId, string $channel, int $cadenceDays, string $notes = ''): void
{
    $stmt = db()->prepare('INSERT INTO crm_post_rdv_sequences
        (lead_id, status, step_index, next_action_at, last_action_at, preferred_channel, cadence_days, notes, created_at, updated_at)
        VALUES (:lead_id, "actif", 1, DATE_ADD(NOW(), INTERVAL :cadence DAY), NOW(), :channel, :cadence, :notes, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            status = "actif",
            preferred_channel = VALUES(preferred_channel),
            cadence_days = VALUES(cadence_days),
            notes = VALUES(notes),
            next_action_at = DATE_ADD(NOW(), INTERVAL VALUES(cadence_days) DAY),
            updated_at = NOW()');
    $stmt->execute([
        ':lead_id' => $leadId,
        ':channel' => $channel,
        ':cadence' => $cadenceDays,
        ':notes' => $notes,
    ]);

    $log = db()->prepare('INSERT INTO crm_post_rdv_logs (lead_id, event_type, channel, message, created_at)
        VALUES (:lead_id, "sequence_started", :channel, :message, NOW())');
    $log->execute([
        ':lead_id' => $leadId,
        ':channel' => $channel,
        ':message' => $notes !== '' ? $notes : 'Séquence post-RDV démarrée.',
    ]);
}

function logConvertirPostRdvFollowup(int $leadId, string $channel, string $message): void
{
    $log = db()->prepare('INSERT INTO crm_post_rdv_logs (lead_id, event_type, channel, message, created_at)
        VALUES (:lead_id, "followup_sent", :channel, :message, NOW())');
    $log->execute([
        ':lead_id' => $leadId,
        ':channel' => $channel,
        ':message' => $message,
    ]);

    $sequence = db()->prepare('INSERT INTO crm_post_rdv_sequences
        (lead_id, status, step_index, next_action_at, last_action_at, preferred_channel, cadence_days, created_at, updated_at)
        VALUES (:lead_id, "actif", 1, DATE_ADD(NOW(), INTERVAL 3 DAY), NOW(), :channel, 3, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            status = IF(status = "termine", "termine", "actif"),
            step_index = step_index + 1,
            preferred_channel = VALUES(preferred_channel),
            last_action_at = NOW(),
            next_action_at = DATE_ADD(NOW(), INTERVAL cadence_days DAY),
            updated_at = NOW()');
    $sequence->execute([
        ':lead_id' => $leadId,
        ':channel' => $channel,
    ]);
}

function updateConvertirPostRdvSequenceState(int $leadId, string $status): void
{
    $stmt = db()->prepare('INSERT INTO crm_post_rdv_sequences
        (lead_id, status, step_index, preferred_channel, cadence_days, created_at, updated_at)
        VALUES (:lead_id, :status, 0, "email", 3, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            next_action_at = IF(VALUES(status) = "pause" OR VALUES(status) = "termine", NULL, next_action_at),
            updated_at = NOW()');
    $stmt->execute([
        ':lead_id' => $leadId,
        ':status' => $status,
    ]);

    $log = db()->prepare('INSERT INTO crm_post_rdv_logs (lead_id, event_type, channel, message, created_at)
        VALUES (:lead_id, "sequence_status", "email", :message, NOW())');
    $log->execute([
        ':lead_id' => $leadId,
        ':message' => 'Statut séquence: ' . $status,
    ]);
}

function listConvertirPostRdvLeads(): array
{
    return LeadService::list();
}

function listConvertirPostRdvSequences(): array
{
    $rows = db()->query('SELECT lead_id, status, step_index, next_action_at, last_action_at, preferred_channel, cadence_days, notes
        FROM crm_post_rdv_sequences
        ORDER BY updated_at DESC')->fetchAll();

    $indexed = [];
    foreach ($rows as $row) {
        $indexed[(int)$row['lead_id']] = $row;
    }

    return $indexed;
}

function findConvertirPostRdvLeadById(int $leadId): ?array
{
    foreach (LeadService::list() as $lead) {
        if ((int)($lead['id'] ?? 0) === $leadId) {
            return $lead;
        }
    }
    return null;
}

function listConvertirPostRdvLog(int $leadId): array
{
    $stmt = db()->prepare('SELECT event_type, channel, message, created_at
        FROM crm_post_rdv_logs
        WHERE lead_id = :lead_id
        ORDER BY created_at DESC, id DESC
        LIMIT 100');
    $stmt->execute([':lead_id' => $leadId]);
    return $stmt->fetchAll();
}

function renderConvertirHubCards(): void
{
    $steps = [
        [
            'number' => '1',
            'title' => 'Organiser les contacts',
            'module' => 'CRM',
            'description' => 'Centralisez chaque lead, qualifiez rapidement et gardez un historique clair pour ne plus perdre d\'opportunités.',
            'href' => '/admin?module=convertir&action=crm-contacts',
            'state' => 'actif',
            'state_label' => 'Actif',
            'icon' => 'fa-address-book',
        ],
        [
            'number' => '2',
            'title' => 'Obtenir un rendez-vous',
            'module' => 'RDV',
            'description' => 'Passez des leads qualifiés à des créneaux planifiés avec une vue simple des demandes et des priorités.',
            'href' => '/admin?module=convertir&action=rdv',
            'state' => 'actif',
            'state_label' => 'Actif',
            'icon' => 'fa-calendar-check',
        ],
        [
            'number' => '3',
            'title' => 'Convaincre',
            'module' => 'Argumentaire',
            'description' => 'Structurez vos scripts de vente pour mieux gérer les objections et augmenter vos signatures en rendez-vous.',
            'href' => null,
            'state' => 'soon',
            'state_label' => 'Bientôt',
            'icon' => 'fa-file-signature',
        ],
        [
            'number' => '4',
            'title' => 'Relancer & signer',
            'module' => 'Suivi',
            'description' => 'Activez des relances régulières après rendez-vous pour faire progresser vos prospects jusqu\'au mandat.',
            'href' => '/admin?module=convertir&action=suivi-post-rdv',
            'state' => 'actif',
            'state_label' => 'Actif',
            'icon' => 'fa-handshake',
        ],
    ];
    ?>
    <style>
        .convertir-sales-flow{display:grid;gap:1.1rem;}
        .convertir-hero{background:linear-gradient(120deg,#0f172a,#1e293b);color:#fff;padding:1.35rem;border-radius:16px;box-shadow:0 12px 26px rgba(15,23,42,.22);}
        .convertir-hero h1{margin:0;font-size:1.55rem;line-height:1.2;}
        .convertir-hero p{margin:.55rem 0 0;color:#cbd5e1;max-width:760px;}

        .convertir-core{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem 1.05rem;display:grid;gap:.7rem;}
        .convertir-core h2{margin:0;font-size:1.02rem;color:#0f172a;}
        .convertir-core-grid{display:grid;gap:.6rem;}
        .convertir-core-item{border:1px solid #e2e8f0;background:#f8fafc;border-radius:10px;padding:.72rem .78rem;}
        .convertir-core-label{display:block;font-weight:800;font-size:.78rem;letter-spacing:.02em;text-transform:uppercase;color:#475569;margin-bottom:.25rem;}
        .convertir-core-item ul{margin:.35rem 0 0 1rem;padding:0;display:grid;gap:.15rem;}

        .convertir-steps{position:relative;display:grid;gap:.8rem;}
        .convertir-step{position:relative;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:.95rem;box-shadow:0 8px 18px rgba(15,23,42,.05);}
        .convertir-step::after{content:'➜';position:absolute;left:50%;bottom:-18px;transform:translateX(-50%);color:#94a3b8;font-size:1rem;font-weight:700;}
        .convertir-step:last-child::after{display:none;}
        .convertir-step-head{display:flex;justify-content:space-between;gap:.8rem;align-items:flex-start;}
        .convertir-step-kicker{font-size:.76rem;font-weight:800;letter-spacing:.02em;text-transform:uppercase;color:#64748b;}
        .convertir-step-title{margin:.22rem 0 0;font-size:1.05rem;}
        .convertir-module{display:inline-flex;align-items:center;gap:.4rem;margin-top:.35rem;font-weight:700;color:#334155;}
        .convertir-state{display:inline-flex;align-items:center;border-radius:999px;padding:.22rem .55rem;font-size:.76rem;font-weight:800;}
        .convertir-state.actif{background:#dcfce7;color:#166534;}
        .convertir-state.soon{background:#ffedd5;color:#9a3412;}
        .convertir-step p{margin:.58rem 0 0;color:#475569;font-size:.92rem;}
        .convertir-step-cta{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;margin-top:.75rem;padding:.56rem .82rem;border-radius:10px;text-decoration:none;font-weight:700;background:#0f172a;color:#fff;}
        .convertir-step-cta.is-disabled{background:#94a3b8;cursor:not-allowed;pointer-events:none;}

        .convertir-final-cta{display:flex;flex-direction:column;align-items:flex-start;gap:.72rem;background:linear-gradient(120deg,#eff6ff,#eef2ff);border:1px solid #c7d2fe;border-radius:14px;padding:1rem;}
        .convertir-final-cta h2{margin:0;font-size:1.1rem;color:#1e1b4b;}
        .convertir-final-cta-btn{display:inline-flex;align-items:center;gap:.45rem;background:#1d4ed8;color:#fff;text-decoration:none;padding:.62rem .88rem;border-radius:10px;font-weight:700;}

        @media (min-width: 900px){
            .convertir-hero{padding:1.8rem;}
            .convertir-hero h1{font-size:2rem;}
            .convertir-core-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
            .convertir-steps{grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;}
            .convertir-step::after{content:'➜';left:auto;right:-16px;top:50%;bottom:auto;transform:translateY(-50%);}
            .convertir-step:nth-child(2)::after{display:none;}
            .convertir-step:nth-child(3)::after{display:block;}
            .convertir-step:last-child::after{display:none;}
        }
    </style>

    <section class="convertir-sales-flow">
        <header class="convertir-hero">
            <h1>Transformer vos contacts en clients</h1>
            <p>Mettez en place un système simple pour qualifier, relancer et signer vos prospects.</p>
        </header>

        <section class="convertir-core">
            <h2>Méthode mère</h2>
            <div class="convertir-core-grid">
                <article class="convertir-core-item">
                    <span class="convertir-core-label">Point de départ</span>
                    <div>Sans suivi clair, des contacts chauds se perdent.</div>
                </article>
                <article class="convertir-core-item">
                    <span class="convertir-core-label">Méthode simple</span>
                    <ul>
                        <li>CRM</li>
                        <li>RDV</li>
                        <li>Argumentaire</li>
                        <li>Suivi</li>
                    </ul>
                </article>
                <article class="convertir-core-item">
                    <span class="convertir-core-label">Impact direct</span>
                    <div>Plus de signatures et plus de mandats.</div>
                </article>
                <article class="convertir-core-item">
                    <span class="convertir-core-label">Prochaine étape</span>
                    <div>Suivez les modules dans l’ordre ci-dessous.</div>
                </article>
            </div>
        </section>

        <section class="convertir-steps" aria-label="Étapes du processus de conversion">
            <?php foreach ($steps as $step): ?>
                <article class="convertir-step">
                    <div class="convertir-step-head">
                        <div>
                            <div class="convertir-step-kicker">Étape <?= e($step['number']) ?></div>
                            <h3 class="convertir-step-title"><?= e($step['title']) ?></h3>
                            <div class="convertir-module"><i class="fas <?= e($step['icon']) ?>"></i> Module : <?= e($step['module']) ?></div>
                        </div>
                        <span class="convertir-state <?= e($step['state']) ?>"><?= e($step['state_label']) ?></span>
                    </div>
                    <p><?= e($step['description']) ?></p>
                    <?php if (!empty($step['href'])): ?>
                        <a href="<?= e((string)$step['href']) ?>" class="convertir-step-cta">Commencer <i class="fas fa-arrow-right"></i></a>
                    <?php else: ?>
                        <span class="convertir-step-cta is-disabled">Commencer</span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="convertir-final-cta">
            <h2>Passez à l’action</h2>
            <a href="/admin?module=convertir&action=crm-contacts" class="convertir-final-cta-btn">Structurer mon suivi client <i class="fas fa-arrow-right"></i></a>
        </section>
    </section>
    <?php
}

function renderConvertirPostRdv(array $leads, array $sequences, ?array $selectedLead, array $log): void
{
    $flash = Session::getFlash();
    $leadName = $selectedLead ? trim(((string)($selectedLead['first_name'] ?? '')) . ' ' . ((string)($selectedLead['last_name'] ?? ''))) : '';
    $leadName = $leadName !== '' ? $leadName : ($selectedLead ? 'Lead #' . (int)$selectedLead['id'] : '');
    $selectedSequence = $selectedLead ? ($sequences[(int)$selectedLead['id']] ?? null) : null;
    ?>
    <style>
        .postrdv-link{display:inline-flex;margin-top:.85rem;background:#475569;color:#fff;text-decoration:none;padding:.55rem .85rem;border-radius:10px;font-weight:700;}
        .postrdv-layout{display:grid;grid-template-columns:minmax(500px,1.45fr) minmax(340px,1fr);gap:1rem;align-items:start;}
        .postrdv-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 25px rgba(15,23,42,.05);overflow:hidden;}
        .postrdv-head{padding:1rem 1.1rem;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;align-items:center;gap:.6rem;}
        .postrdv-body{padding:1rem 1.1rem;}
        .postrdv-table{width:100%;border-collapse:collapse;min-width:680px;}
        .postrdv-table th,.postrdv-table td{padding:.68rem .65rem;border-bottom:1px solid #f1f5f9;text-align:left;vertical-align:top;font-size:.9rem;}
        .postrdv-status{display:inline-block;padding:.2rem .55rem;border-radius:999px;background:#e2e8f0;font-size:.78rem;font-weight:700;}
        .postrdv-status.actif{background:#dcfce7;color:#166534;}
        .postrdv-status.pause{background:#fde68a;color:#92400e;}
        .postrdv-status.termine{background:#ddd6fe;color:#5b21b6;}
        .postrdv-status.inactif{background:#e2e8f0;color:#475569;}
        .postrdv-form{display:grid;gap:.65rem;margin-top:1rem;}
        .postrdv-form select,.postrdv-form textarea,.postrdv-form input,.postrdv-form button{width:100%;padding:.62rem .7rem;border-radius:10px;border:1px solid #cbd5e1;font:inherit;}
        .postrdv-form button{background:#0f172a;color:#fff;font-weight:700;border-color:#0f172a;cursor:pointer;}
        .postrdv-log{display:grid;gap:.6rem;max-height:320px;overflow:auto;padding-right:.2rem;}
        .postrdv-log-item{border:1px solid #e2e8f0;background:#f8fafc;border-radius:10px;padding:.65rem .7rem;}
        .postrdv-muted{color:#64748b;font-size:.84rem;}
        .postrdv-flash{padding:.75rem .9rem;border-radius:10px;margin-bottom:1rem;font-weight:600;}
        .postrdv-flash.success{background:#dcfce7;color:#166534;}
        .postrdv-flash.error{background:#fee2e2;color:#991b1b;}
        .postrdv-table-wrap{overflow:auto;max-height:70vh;}
        @media (max-width: 1100px) {.postrdv-layout{grid-template-columns:1fr;}}
    </style>

    <?php if ($flash): ?>
        <div class="postrdv-flash <?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div>
    <?php endif; ?>

    <div class="convertir-toolbar">
        <a href="/admin?module=convertir" class="postrdv-link">← Retour au hub</a>
        <div class="convertir-count"><?= count($leads) ?> leads disponibles pour la relance post-RDV.</div>
    </div>

    <div class="postrdv-layout">
        <section class="postrdv-panel">
            <div class="postrdv-head"><strong>Relances & nurturing</strong></div>
            <div class="postrdv-table-wrap">
                <table class="postrdv-table">
                    <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Pipeline</th>
                        <th>Séquence</th>
                        <th>Prochaine relance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$leads): ?>
                        <tr><td colspan="4">Aucun lead disponible.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($leads as $lead): ?>
                        <?php
                        $seq = $sequences[(int)$lead['id']] ?? null;
                        $status = (string)($seq['status'] ?? 'inactif');
                        $name = trim(((string)($lead['first_name'] ?? '')) . ' ' . ((string)($lead['last_name'] ?? '')));
                        ?>
                        <tr>
                            <td>
                                <a href="/admin?module=convertir&action=suivi-post-rdv&lead_id=<?= (int)$lead['id'] ?>"><strong><?= e($name !== '' ? $name : ('Lead #' . (int)$lead['id'])) ?></strong></a>
                                <div class="postrdv-muted"><?= e((string)($lead['email'] ?? '')) ?></div>
                            </td>
                            <td><?= e(LeadService::sourceLabel((string)($lead['pipeline'] ?? 'autre'))) ?></td>
                            <td><span class="postrdv-status <?= e($status) ?>"><?= e(ucfirst($status)) ?></span></td>
                            <td><?= !empty($seq['next_action_at']) ? e(date('d/m/Y H:i', strtotime((string)$seq['next_action_at']))) : '<span class="postrdv-muted">Non planifiée</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="postrdv-panel">
            <div class="postrdv-head"><strong>Fiche suivi</strong></div>
            <div class="postrdv-body">
                <?php if (!$selectedLead): ?>
                    <div class="postrdv-muted">Sélectionnez un lead pour lancer une séquence de relance.</div>
                <?php else: ?>
                    <h3 style="margin:0"><?= e($leadName) ?></h3>
                    <div class="postrdv-muted" style="margin-top:.3rem;"><?= e((string)($selectedLead['email'] ?? '')) ?> · <?= e((string)($selectedLead['phone'] ?? '')) ?></div>
                    <div class="postrdv-muted" style="margin-top:.3rem;">Étape actuelle CRM : <?= e(LeadService::stageLabel((string)($selectedLead['stage'] ?? 'nouveau'))) ?></div>

                    <form method="post" class="postrdv-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="post_rdv_intent" value="start_sequence">
                        <input type="hidden" name="lead_id" value="<?= (int)$selectedLead['id'] ?>">
                        <label>
                            <strong>Canal préféré</strong>
                            <select name="preferred_channel">
                                <option value="email" <?= (($selectedSequence['preferred_channel'] ?? 'email') === 'email') ? 'selected' : '' ?>>Email</option>
                                <option value="sms" <?= (($selectedSequence['preferred_channel'] ?? '') === 'sms') ? 'selected' : '' ?>>SMS</option>
                                <option value="appel" <?= (($selectedSequence['preferred_channel'] ?? '') === 'appel') ? 'selected' : '' ?>>Appel</option>
                            </select>
                        </label>
                        <label>
                            <strong>Cadence de relance (jours)</strong>
                            <input type="number" min="1" max="21" name="cadence_days" value="<?= (int)($selectedSequence['cadence_days'] ?? 3) ?>">
                        </label>
                        <label>
                            <strong>Note de nurturing (optionnel)</strong>
                            <textarea name="notes" rows="2" placeholder="Ex : envoyer étude de marché du quartier, puis rappel J+3."></textarea>
                        </label>
                        <button type="submit">Activer / mettre à jour la séquence</button>
                    </form>

                    <form method="post" class="postrdv-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="post_rdv_intent" value="log_followup">
                        <input type="hidden" name="lead_id" value="<?= (int)$selectedLead['id'] ?>">
                        <label>
                            <strong>Canal utilisé</strong>
                            <select name="channel">
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="appel">Appel</option>
                            </select>
                        </label>
                        <label>
                            <strong>Message de relance envoyé</strong>
                            <textarea name="message" rows="2" required placeholder="Ex : relance J+3 avec comparables du quartier et proposition de créneau."></textarea>
                        </label>
                        <button type="submit">Journaliser la relance</button>
                    </form>

                    <form method="post" class="postrdv-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="post_rdv_intent" value="update_sequence_state">
                        <input type="hidden" name="lead_id" value="<?= (int)$selectedLead['id'] ?>">
                        <label>
                            <strong>Statut de séquence</strong>
                            <select name="status">
                                <option value="actif">Actif</option>
                                <option value="pause">Pause</option>
                                <option value="termine">Terminé</option>
                            </select>
                        </label>
                        <button type="submit">Mettre à jour le statut</button>
                    </form>

                    <h4 style="margin:1rem 0 .5rem;">Historique des relances</h4>
                    <?php if (!$log): ?>
                        <div class="postrdv-muted">Aucune relance enregistrée pour ce lead.</div>
                    <?php else: ?>
                        <div class="postrdv-log">
                            <?php foreach ($log as $event): ?>
                                <article class="postrdv-log-item">
                                    <div><strong><?= e((string)$event['event_type']) ?></strong> · <?= e(strtoupper((string)$event['channel'])) ?></div>
                                    <?php if (!empty($event['message'])): ?>
                                        <div class="postrdv-muted" style="margin-top:.35rem;"><?= nl2br(e((string)$event['message'])) ?></div>
                                    <?php endif; ?>
                                    <div class="postrdv-muted" style="margin-top:.35rem;"><?= e(date('d/m/Y H:i', strtotime((string)$event['created_at']))) ?></div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </aside>
    </div>
    <?php
}

function renderConvertirCrmContacts(array $crmLeads, ?array $selectedLead, array $selectedLeadHistory): void
{
    $flash = Session::getFlash();
    $leadSource = convertirLeadSource();
    $leadSourceLabel = $leadSource['label'] ?? 'leads / crm_leads';
    ?>
    <style>
        .crm-link-btn{display:inline-flex;margin-top:.85rem;background:#0f172a;color:#fff;text-decoration:none;padding:.55rem .85rem;border-radius:10px;font-weight:700;}
        .convertir-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:1rem 0;flex-wrap:wrap;}
        .convertir-count{color:#64748b;font-size:.92rem;}
        .crm-layout{display:grid;grid-template-columns:minmax(520px,1.5fr) minmax(340px,1fr);gap:1rem;align-items:start;}
        .crm-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 25px rgba(15,23,42,.05);overflow:hidden;}
        .crm-panel-head{padding:1rem 1.1rem;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;align-items:center;gap:.6rem;}
        .crm-panel-body{padding:1rem 1.1rem;}
        .crm-table-wrap{overflow:auto;max-height:72vh;}
        .crm-table{width:100%;border-collapse:collapse;min-width:700px;}
        .crm-table th,.crm-table td{padding:.72rem .65rem;border-bottom:1px solid #f1f5f9;text-align:left;vertical-align:top;font-size:.9rem;}
        .crm-table tr.is-selected{background:#eff6ff;}
        .crm-status{display:inline-block;padding:.2rem .55rem;border-radius:999px;background:#e2e8f0;font-size:.78rem;font-weight:700;}
        .crm-status.nouveau{background:#dbeafe;color:#1d4ed8;}
        .crm-status.contacte{background:#fde68a;color:#92400e;}
        .crm-status.rdv_planifie{background:#ddd6fe;color:#5b21b6;}
        .crm-status.mandat_signe{background:#bbf7d0;color:#166534;}
        .crm-status.perdu{background:#fecaca;color:#991b1b;}
        .crm-side-grid{display:grid;gap:1rem;}
        .crm-lead-meta{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-top:.85rem;}
        .crm-lead-meta > div{font-size:.85rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.55rem .65rem;}
        .crm-form{display:grid;gap:.7rem;margin-top:1rem;}
        .crm-form select,.crm-form textarea,.crm-form button{width:100%;padding:.62rem .7rem;border-radius:10px;border:1px solid #cbd5e1;font:inherit;}
        .crm-form button{background:#0f172a;color:#fff;font-weight:700;border-color:#0f172a;cursor:pointer;}
        .crm-history{display:grid;gap:.6rem;max-height:320px;overflow:auto;padding-right:.2rem;}
        .crm-history-item{border:1px solid #e2e8f0;background:#f8fafc;border-radius:10px;padding:.65rem .7rem;}
        .crm-muted{color:#64748b;font-size:.84rem;}
        .crm-flash{padding:.75rem .9rem;border-radius:10px;margin-bottom:1rem;font-weight:600;}
        .crm-flash.success{background:#dcfce7;color:#166534;}
        .crm-flash.error{background:#fee2e2;color:#991b1b;}
        @media (max-width: 1100px) {.crm-layout{grid-template-columns:1fr;}}
    </style>

    <?php if ($flash): ?>
        <div class="crm-flash <?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div>
    <?php endif; ?>

    <div class="convertir-toolbar">
        <a href="/admin?module=convertir" class="crm-link-btn" style="margin:0;background:#475569;">← Retour au hub</a>
        <div class="convertir-count"><?= count($crmLeads) ?> leads capturés depuis la table <code><?= e($leadSourceLabel) ?></code>.</div>
    </div>

    <div class="crm-layout">
        <section class="crm-panel">
            <div class="crm-panel-head">
                <strong>Leads capturés</strong>
            </div>
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Contact</th>
                        <th>Source</th>
                        <th>Statut CRM</th>
                        <th>Créé le</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$crmLeads): ?>
                        <tr><td colspan="5">Aucun lead dans la table <code><?= e($leadSourceLabel) ?></code>.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($crmLeads as $lead): ?>
                        <?php $isSelected = $selectedLead && (int)$selectedLead['id'] === (int)$lead['id']; ?>
                        <tr class="<?= $isSelected ? 'is-selected' : '' ?>">
                            <td>
                                <a href="/admin?module=convertir&action=crm-contacts&lead_id=<?= (int)$lead['id'] ?>"><strong><?= e((string)($lead['nom'] ?: 'Lead #' . $lead['id'])) ?></strong></a>
                                <div class="crm-muted"><?= e((string)($lead['guide_titre'] ?: ($lead['persona'] ?: '—'))) ?></div>
                            </td>
                            <td>
                                <div><?= e((string)$lead['email']) ?></div>
                                <div class="crm-muted"><?= e((string)($lead['telephone'] ?: 'Pas de téléphone')) ?></div>
                            </td>
                            <td><?= e((string)($lead['source'] ?: '—')) ?></td>
                            <td><span class="crm-status <?= e((string)$lead['crm_status']) ?>"><?= e(CONVERTIR_CRM_STATUSES[(string)$lead['crm_status']] ?? 'Nouveau') ?></span></td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$lead['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="crm-side-grid">
            <section class="crm-panel">
                <div class="crm-panel-head"><strong>Fiche lead</strong></div>
                <div class="crm-panel-body">
                    <?php if (!$selectedLead): ?>
                        <div class="crm-muted">Sélectionnez un lead pour modifier son statut et ses notes.</div>
                    <?php else: ?>
                        <h3 style="margin:0"><?= e((string)($selectedLead['nom'] ?: ('Lead #' . $selectedLead['id']))) ?></h3>
                        <div class="crm-muted" style="margin-top:.3rem;"><?= e((string)$selectedLead['email']) ?></div>
                        <div class="crm-lead-meta">
                            <div><strong>Téléphone</strong><br><?= e((string)($selectedLead['telephone'] ?: '—')) ?></div>
                            <div><strong>Source</strong><br><?= e((string)($selectedLead['source'] ?: '—')) ?></div>
                            <div><strong>Persona</strong><br><?= e((string)($selectedLead['persona'] ?: '—')) ?></div>
                            <div><strong>Capture</strong><br><?= e(date('d/m/Y H:i', strtotime((string)$selectedLead['created_at']))) ?></div>
                        </div>
                        <div style="margin-top:.75rem;"><strong>Message initial</strong><div class="crm-muted" style="margin-top:.2rem;"><?= nl2br(e((string)($selectedLead['message'] ?: 'Aucun message.'))) ?></div></div>

                        <form method="post" class="crm-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="crm_intent" value="update_status">
                            <input type="hidden" name="lead_id" value="<?= (int)$selectedLead['id'] ?>">
                            <label>
                                <strong>Statut</strong>
                                <select name="status" required>
                                    <?php foreach (CONVERTIR_CRM_STATUSES as $statusKey => $statusLabel): ?>
                                        <option value="<?= e($statusKey) ?>" <?= (string)$selectedLead['crm_status'] === $statusKey ? 'selected' : '' ?>><?= e($statusLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <strong>Note de suivi (optionnel)</strong>
                                <textarea name="note" rows="3" placeholder="Ex: appel effectué, client souhaite rappeler lundi..."><?= e((string)($selectedLead['crm_note'] ?? '')) ?></textarea>
                            </label>
                            <button type="submit">Enregistrer le statut</button>
                        </form>

                        <form method="post" class="crm-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="crm_intent" value="add_note">
                            <input type="hidden" name="lead_id" value="<?= (int)$selectedLead['id'] ?>">
                            <label>
                                <strong>Ajouter une interaction</strong>
                                <textarea name="note" rows="3" placeholder="Ex: relance SMS envoyée, rappel prévu vendredi" required></textarea>
                            </label>
                            <button type="submit">Ajouter à l'historique</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>

            <section class="crm-panel">
                <div class="crm-panel-head"><strong>Historique des interactions</strong></div>
                <div class="crm-panel-body">
                    <?php if (!$selectedLead): ?>
                        <div class="crm-muted">Aucun lead sélectionné.</div>
                    <?php elseif (!$selectedLeadHistory): ?>
                        <div class="crm-muted">Aucune interaction enregistrée pour ce lead.</div>
                    <?php else: ?>
                        <div class="crm-history">
                            <?php foreach ($selectedLeadHistory as $event): ?>
                                <article class="crm-history-item">
                                    <div>
                                        <?php if ((string)$event['interaction_type'] === 'status'): ?>
                                            <strong>Statut :</strong>
                                            <?= e(CONVERTIR_CRM_STATUSES[(string)($event['old_value'] ?? 'nouveau')] ?? 'Nouveau') ?>
                                            →
                                            <?= e(CONVERTIR_CRM_STATUSES[(string)($event['new_value'] ?? 'nouveau')] ?? 'Nouveau') ?>
                                        <?php else: ?>
                                            <strong>Note ajoutée</strong>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['note'])): ?>
                                        <div class="crm-muted" style="margin-top:.35rem;"><?= nl2br(e((string)$event['note'])) ?></div>
                                    <?php endif; ?>
                                    <div class="crm-muted" style="margin-top:.35rem;"><?= e(date('d/m/Y H:i', strtotime((string)$event['created_at']))) ?></div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
    <?php
}

function renderContent(): void
{
    global $crmContactsView, $crmLeads, $selectedLead, $selectedLeadHistory;
    global $postRdvView, $postRdvLeads, $postRdvSequences, $selectedPostRdvLead, $selectedPostRdvLog;
    ?>
    <?php if ($crmContactsView): ?>
        <div class="page-header">
            <h1><i class="fas fa-arrow-trend-up page-icon"></i> HUB <span class="page-title-accent">Convertir</span></h1>
            <p>Transformez vos contacts en clients signés</p>
        </div>
        <?php renderConvertirCrmContacts($crmLeads, $selectedLead, $selectedLeadHistory); ?>
    <?php elseif ($postRdvView): ?>
        <div class="page-header">
            <h1><i class="fas fa-arrow-trend-up page-icon"></i> HUB <span class="page-title-accent">Convertir</span></h1>
            <p>Transformez vos contacts en clients signés</p>
        </div>
        <?php renderConvertirPostRdv($postRdvLeads, $postRdvSequences, $selectedPostRdvLead, $selectedPostRdvLog); ?>
    <?php else: ?>
        <?php renderConvertirHubCards(); ?>
    <?php endif; ?>
    <?php
}
