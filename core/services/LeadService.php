<?php

class LeadService
{
    public const SOURCE_ESTIMATION = 'estimation';
    public const SOURCE_RESSOURCE = 'telechargement';
    public const SOURCE_CONTACT = 'contact';
    public const SOURCE_FINANCEMENT = 'financement';
    public const SOURCE_AUTRE = 'autre';

    private static bool $tableReady = false;
    private static bool $interactionTableReady = false;

    public static function capture(array $payload): int
    {
        self::ensureTable();

        $source = self::sanitizeSource((string)($payload['source_type'] ?? self::SOURCE_AUTRE));
        $pipeline = self::sanitizePipeline((string)($payload['pipeline'] ?? $source));
        $stage = self::sanitizeStage((string)($payload['stage'] ?? self::defaultStage($source)));

        $stmt = db()->prepare('INSERT INTO crm_leads
            (source_type, pipeline, stage, priority, first_name, last_name, email, phone, intent, property_type, property_address, metadata_json, notes, consent, created_at, updated_at)
            VALUES
            (:source_type, :pipeline, :stage, :priority, :first_name, :last_name, :email, :phone, :intent, :property_type, :property_address, :metadata_json, :notes, :consent, NOW(), NOW())');

        $stmt->execute([
            ':source_type' => $source,
            ':pipeline' => $pipeline,
            ':stage' => $stage,
            ':priority' => self::sanitizePriority((string)($payload['priority'] ?? 'normal')),
            ':first_name' => trim((string)($payload['first_name'] ?? '')),
            ':last_name' => trim((string)($payload['last_name'] ?? '')),
            ':email' => trim((string)($payload['email'] ?? '')),
            ':phone' => trim((string)($payload['phone'] ?? '')),
            ':intent' => trim((string)($payload['intent'] ?? '')),
            ':property_type' => trim((string)($payload['property_type'] ?? '')),
            ':property_address' => trim((string)($payload['property_address'] ?? '')),
            ':metadata_json' => json_encode($payload['metadata'] ?? [], JSON_UNESCAPED_UNICODE),
            ':notes' => trim((string)($payload['notes'] ?? '')),
            ':consent' => !empty($payload['consent']) ? 1 : 0,
        ]);

        $leadId = (int)db()->lastInsertId();

        // Créer un thread + message dans la messagerie CRM
        if ($source === self::SOURCE_CONTACT) {
            self::createMessageThread($leadId, $payload);
        }

        return $leadId;
    }

    public static function list(array $filters = []): array
    {
        self::ensureTable();

        $params = [];
        $where = [];

        if (!empty($filters['source_type'])) {
            $where[] = 'source_type = :source';
            $params[':source'] = self::sanitizeSource((string)$filters['source_type']);
        }

        if (!empty($filters['pipeline'])) {
            $where[] = 'pipeline = :pipeline';
            $params[':pipeline'] = self::sanitizePipeline((string)$filters['pipeline']);
        }

        if (!empty($filters['stage_like'])) {
            $where[] = 'stage LIKE :stage_like';
            $params[':stage_like'] = '%' . self::sanitizeStageLike((string)$filters['stage_like']) . '%';
        }

        $sql = 'SELECT * FROM crm_leads';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 300';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['metadata'] = json_decode((string)($row['metadata_json'] ?? '{}'), true) ?: [];
        }

        return $rows;
    }

    public static function updateRdvStatus(int $leadId, string $action, ?string $scheduledAt = null, string $comment = ''): bool
    {
        self::ensureTable();

        $leadId = max(0, $leadId);
        if ($leadId <= 0) {
            return false;
        }

        $action = strtolower(trim($action));
        if (!in_array($action, ['confirm', 'cancel', 'reschedule'], true)) {
            return false;
        }

        $targetStage = match ($action) {
            'confirm' => 'rdv_planifie',
            'cancel' => 'perdu',
            'reschedule' => 'rdv_a_planifier',
        };

        $stmt = db()->prepare('SELECT metadata_json, notes FROM crm_leads WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $leadId]);
        $lead = $stmt->fetch();
        if (!$lead) {
            return false;
        }

        $metadata = json_decode((string)($lead['metadata_json'] ?? '{}'), true) ?: [];
        $existingNotes = trim((string)($lead['notes'] ?? ''));

        if ($scheduledAt !== null && $scheduledAt !== '') {
            $metadata['appointment_at'] = $scheduledAt;
        }
        $metadata['appointment_status'] = $action;
        $metadata['appointment_updated_at'] = date('c');

        $comment = trim($comment);
        $nextNotes = $existingNotes;
        if ($comment !== '') {
            $prefix = '[' . date('d/m/Y H:i') . '] ';
            $nextNotes = trim($existingNotes . PHP_EOL . $prefix . $comment);
        }

        $update = db()->prepare('UPDATE crm_leads
            SET stage = :stage,
                metadata_json = :metadata_json,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id
            LIMIT 1');

        return $update->execute([
            ':stage' => $targetStage,
            ':metadata_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
            ':notes' => $nextNotes,
            ':id' => $leadId,
        ]);
    }

    public static function stageMatrix(): array
    {
        return [
            self::SOURCE_ESTIMATION => ['nouveau', 'a_qualifier', 'rdv_a_planifier', 'rdv_planifie', 'converti', 'perdu'],
            self::SOURCE_RESSOURCE => ['nouveau', 'nurturing', 'a_relancer', 'rdv_propose', 'converti', 'inactif'],
            self::SOURCE_CONTACT => ['nouveau', 'a_traiter', 'en_discussion', 'rdv_planifie', 'converti', 'archive'],
            self::SOURCE_FINANCEMENT => ['nouveau', 'en_cours', 'traite'],
            self::SOURCE_AUTRE => ['nouveau', 'a_qualifier', 'en_cours', 'converti', 'archive'],
        ];
    }

    public static function stageLabel(string $stage): string
    {
        $labels = [
            'nouveau' => 'Nouveau',
            'a_qualifier' => 'À qualifier',
            'rdv_a_planifier' => 'RDV à planifier',
            'rdv_planifie' => 'RDV planifié',
            'converti' => 'Converti',
            'perdu' => 'Perdu',
            'nurturing' => 'Nurturing',
            'a_relancer' => 'À relancer',
            'rdv_propose' => 'RDV proposé',
            'inactif' => 'Inactif',
            'a_traiter' => 'À traiter',
            'en_discussion' => 'En discussion',
            'archive' => 'Archivé',
            'en_cours' => 'En cours',
            'traite' => 'Traité',
        ];

        return $labels[$stage] ?? ucfirst(str_replace('_', ' ', $stage));
    }

    public static function sourceLabel(string $source): string
    {
        return [
            self::SOURCE_ESTIMATION => 'Estimation',
            self::SOURCE_RESSOURCE => 'Téléchargement',
            self::SOURCE_CONTACT => 'Contact',
            self::SOURCE_FINANCEMENT => 'Financement',
            self::SOURCE_AUTRE => 'Autre',
        ][$source] ?? ucfirst($source);
    }

    private static function ensureTable(): void
    {
        if (self::$tableReady) {
            return;
        }

        db()->exec('CREATE TABLE IF NOT EXISTS crm_leads (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            source_type VARCHAR(40) NOT NULL DEFAULT "autre",
            pipeline VARCHAR(60) NOT NULL DEFAULT "autre",
            stage VARCHAR(60) NOT NULL DEFAULT "nouveau",
            priority VARCHAR(16) NOT NULL DEFAULT "normal",
            first_name VARCHAR(80) NOT NULL,
            last_name VARCHAR(80) NOT NULL DEFAULT "",
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NULL,
            intent VARCHAR(120) NULL,
            property_type VARCHAR(60) NULL,
            property_address VARCHAR(255) NULL,
            metadata_json JSON NULL,
            notes TEXT NULL,
            consent TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_source_stage (source_type, stage),
            INDEX idx_pipeline (pipeline),
            INDEX idx_email (email),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$tableReady = true;
    }

    public static function logInteraction(int $leadId, string $type, string $note = '', array $meta = []): bool
    {
        self::ensureTable();
        self::ensureInteractionTable();

        if ($leadId <= 0) {
            return false;
        }

        $type = self::sanitizeInteractionType($type);
        $note = trim($note);
        $oldValue = isset($meta['old']) ? (string)$meta['old'] : null;
        $newValue = isset($meta['new']) ? (string)$meta['new'] : null;

        $stmt = db()->prepare('INSERT INTO crm_lead_interactions
            (lead_id, interaction_type, old_value, new_value, note, created_at)
            VALUES (:lead_id, :interaction_type, :old_value, :new_value, :note, NOW())');

        return $stmt->execute([
            ':lead_id' => $leadId,
            ':interaction_type' => $type,
            ':old_value' => $oldValue,
            ':new_value' => $newValue,
            ':note' => $note !== '' ? $note : null,
        ]);
    }

    public static function latestInteractionsByLead(array $leadIds, string $type = 'appel'): array
    {
        self::ensureTable();
        self::ensureInteractionTable();

        $leadIds = array_values(array_filter(array_map('intval', $leadIds), static fn(int $id): bool => $id > 0));
        if ($leadIds === []) {
            return [];
        }

        $type = self::sanitizeInteractionType($type);
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));

        $sql = 'SELECT i.*
                FROM crm_lead_interactions i
                INNER JOIN (
                    SELECT lead_id, MAX(id) AS max_id
                    FROM crm_lead_interactions
                    WHERE lead_id IN (' . $placeholders . ') AND interaction_type = ?
                    GROUP BY lead_id
                ) latest ON latest.max_id = i.id';

        $stmt = db()->prepare($sql);
        $stmt->execute([...$leadIds, $type]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['lead_id']] = $row;
        }

        return $indexed;
    }

    private static function ensureInteractionTable(): void
    {
        if (self::$interactionTableReady) {
            return;
        }

        db()->exec('CREATE TABLE IF NOT EXISTS crm_lead_interactions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            lead_id INT UNSIGNED NOT NULL,
            interaction_type VARCHAR(20) NOT NULL,
            old_value VARCHAR(80) NULL,
            new_value VARCHAR(80) NULL,
            note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_lead_created (lead_id, created_at),
            INDEX idx_lead_type (lead_id, interaction_type, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$interactionTableReady = true;
    }

    private static function sanitizeInteractionType(string $type): string
    {
        $type = strtolower(trim($type));
        $allowed = ['status', 'note', 'email', 'appel', 'sms', 'rdv', 'autre'];
        return in_array($type, $allowed, true) ? $type : 'autre';
    }

    private static function sanitizeSource(string $source): string
    {
        $source = strtolower(trim($source));
        $allowed = [self::SOURCE_ESTIMATION, self::SOURCE_RESSOURCE, self::SOURCE_CONTACT, self::SOURCE_FINANCEMENT, self::SOURCE_AUTRE];
        return in_array($source, $allowed, true) ? $source : self::SOURCE_AUTRE;
    }

    private static function sanitizePipeline(string $pipeline): string
    {
        $pipeline = strtolower(trim($pipeline));
        return preg_replace('/[^a-z0-9_\-]/', '', $pipeline) ?: self::SOURCE_AUTRE;
    }

    private static function sanitizeStage(string $stage): string
    {
        $stage = strtolower(trim($stage));
        return preg_replace('/[^a-z0-9_\-]/', '', $stage) ?: 'nouveau';
    }

    private static function sanitizeStageLike(string $stage): string
    {
        $stage = strtolower(trim($stage));
        return preg_replace('/[^a-z0-9_\-]/', '', $stage) ?: 'rdv';
    }

    private static function sanitizePriority(string $priority): string
    {
        $allowed = ['basse', 'normal', 'haute'];
        return in_array($priority, $allowed, true) ? $priority : 'normal';
    }

    private static function defaultStage(string $source): string
    {
        $matrix = self::stageMatrix();
        return $matrix[$source][0] ?? 'nouveau';
    }

    private static function createMessageThread(int $leadId, array $payload): void
    {
        try {
            $repoFile = ROOT_PATH . '/modules/messagerie/repositories/MessageRepository.php';
            if (!is_file($repoFile)) return;
            require_once $repoFile;

            // Récupère le user_id du premier admin actif
            $adminUser = db()->query(
                "SELECT id FROM users WHERE role IN ('admin','superadmin') AND status='active' ORDER BY id ASC LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            $userId = (int)($adminUser['id'] ?? 1);

            $repo        = new MessageRepository(db());
            $email       = strtolower(trim((string)($payload['email'] ?? '')));
            $firstName   = trim((string)($payload['first_name'] ?? ''));
            $lastName    = trim((string)($payload['last_name'] ?? ''));
            $name        = trim("$firstName $lastName") ?: $email;
            $intent      = trim((string)($payload['intent'] ?? 'Contact général'));
            $notes       = trim((string)($payload['notes'] ?? ''));
            $phone       = trim((string)($payload['phone'] ?? ''));

            $subject = $intent ?: 'Nouveau message de contact';
            $snippet = mb_substr($notes, 0, 120);

            $threadId = $repo->upsertThread($userId, $email, $name, $subject, $snippet);

            $bodyParts = [];
            if ($notes !== '')  $bodyParts[] = nl2br(htmlspecialchars($notes));
            if ($phone !== '')  $bodyParts[] = '<p><strong>Téléphone :</strong> ' . htmlspecialchars($phone) . '</p>';

            $bodyHtml = implode("\n", $bodyParts);
            $bodyText = $notes . ($phone ? "\nTéléphone : $phone" : '');

            $repo->insertMessage([
                'thread_id'       => $threadId,
                'user_id'         => $userId,
                'gmail_message_id'=> 'contact_form_lead_' . $leadId,
                'direction'       => 'inbound',
                'from_email'      => $email,
                'from_name'       => $name,
                'to_email'        => (string)(setting('smtp_user', '') ?: APP_EMAIL ?? ''),
                'subject'         => $subject,
                'body_html'       => $bodyHtml,
                'body_text'       => $bodyText,
                'status'          => 'received',
                'is_read'         => 0,
                'sent_at'         => date('Y-m-d H:i:s'),
            ]);

            $repo->incrementUnread($threadId);
        } catch (Throwable) {
            // Ne jamais bloquer la soumission du formulaire
        }
    }
}
