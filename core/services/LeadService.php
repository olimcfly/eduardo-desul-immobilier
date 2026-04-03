<?php

class LeadService
{
    public const SOURCE_ESTIMATION = 'estimation';
    public const SOURCE_RESSOURCE = 'telechargement';
    public const SOURCE_CONTACT = 'contact';
    public const SOURCE_AUTRE = 'autre';

    private static bool $tableReady = false;

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

        return (int)db()->lastInsertId();
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

    public static function stageMatrix(): array
    {
        return [
            self::SOURCE_ESTIMATION => ['nouveau', 'a_qualifier', 'rdv_a_planifier', 'rdv_planifie', 'converti', 'perdu'],
            self::SOURCE_RESSOURCE => ['nouveau', 'nurturing', 'a_relancer', 'rdv_propose', 'converti', 'inactif'],
            self::SOURCE_CONTACT => ['nouveau', 'a_traiter', 'en_discussion', 'rdv_planifie', 'converti', 'archive'],
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
        ];

        return $labels[$stage] ?? ucfirst(str_replace('_', ' ', $stage));
    }

    public static function sourceLabel(string $source): string
    {
        return [
            self::SOURCE_ESTIMATION => 'Estimation',
            self::SOURCE_RESSOURCE => 'Téléchargement',
            self::SOURCE_CONTACT => 'Contact',
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

    private static function sanitizeSource(string $source): string
    {
        $source = strtolower(trim($source));
        $allowed = [self::SOURCE_ESTIMATION, self::SOURCE_RESSOURCE, self::SOURCE_CONTACT, self::SOURCE_AUTRE];
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
}
