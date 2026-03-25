<?php
/**
 * SequenceTriggerService
 * Déclencheurs automatiques de séquences (capture pages, events CRM).
 */
class SequenceTriggerService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTables();
    }

    /**
     * @return array{matched:int,enrolled:int,logs:int,errors:array<int,string>}
     */
    public function processLeadEvent(string $eventKey, int $leadId, array $context = []): array
    {
        $out = ['matched' => 0, 'enrolled' => 0, 'logs' => 0, 'errors' => []];

        $lead = $this->loadLead($leadId);
        if (!$lead) {
            $out['errors'][] = 'Lead introuvable';
            return $out;
        }

        $triggers = $this->loadActiveTriggers($eventKey);
        foreach ($triggers as $trigger) {
            try {
                $conditions = json_decode((string)($trigger['conditions_json'] ?? '{}'), true);
                if (!is_array($conditions)) {
                    $conditions = [];
                }

                if (!$this->matchesConditions($conditions, $lead, $context)) {
                    continue;
                }

                $out['matched']++;

                $enrolled = $this->enrollLead((int)$trigger['sequence_id'], $leadId, [
                    'trigger_id' => (int)$trigger['id'],
                    'event' => $eventKey,
                    'context' => $context,
                ]);
                if ($enrolled) {
                    $out['enrolled']++;
                }

                $this->logTriggerExecution((int)$trigger['id'], $leadId, $eventKey, $conditions, $context, $enrolled ? 'enrolled' : 'already_enrolled', null);
                $out['logs']++;
            } catch (Throwable $e) {
                $out['errors'][] = $e->getMessage();
                $this->logTriggerExecution((int)$trigger['id'], $leadId, $eventKey, [], $context, 'error', $e->getMessage());
                $out['logs']++;
            }
        }

        return $out;
    }

    private function loadLead(int $leadId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM leads WHERE id = ? LIMIT 1");
        $stmt->execute([$leadId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function loadActiveTriggers(string $eventKey): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM crm_triggers WHERE is_active = 1 AND event_key = ? ORDER BY priority DESC, id ASC");
        $stmt->execute([$eventKey]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function matchesConditions(array $conditions, array $lead, array $context): bool
    {
        if (isset($conditions['source_contains'])) {
            $src = strtolower((string)($lead['source'] ?? ''));
            $needle = strtolower((string)$conditions['source_contains']);
            if ($needle !== '' && strpos($src, $needle) === false) {
                return false;
            }
        }

        if (isset($conditions['capture_slug_in']) && is_array($conditions['capture_slug_in'])) {
            $captureSlug = strtolower((string)($context['capture_slug'] ?? ''));
            $allowed = array_map(fn($v) => strtolower((string)$v), $conditions['capture_slug_in']);
            if ($captureSlug === '' || !in_array($captureSlug, $allowed, true)) {
                return false;
            }
        }

        if (isset($conditions['has_email']) && (bool)$conditions['has_email'] === true) {
            if (empty($lead['email'])) {
                return false;
            }
        }

        if (isset($conditions['lead_status_in']) && is_array($conditions['lead_status_in'])) {
            $status = strtolower((string)($lead['status'] ?? $lead['statut'] ?? ''));
            $allowedStatus = array_map(fn($v) => strtolower((string)$v), $conditions['lead_status_in']);
            if (!in_array($status, $allowedStatus, true)) {
                return false;
            }
        }

        return true;
    }

    private function enrollLead(int $sequenceId, int $leadId, array $metadata): bool
    {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO crm_sequence_enrollments (sequence_id, lead_id, current_step, status, enrolled_at, next_action_at, metadata) VALUES (?, ?, 1, 'active', NOW(), NOW(), ?)");
        $stmt->execute([$sequenceId, $leadId, json_encode($metadata, JSON_UNESCAPED_UNICODE)]);
        return $stmt->rowCount() > 0;
    }

    private function logTriggerExecution(int $triggerId, int $leadId, string $eventKey, array $conditions, array $context, string $result, ?string $error): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO crm_trigger_logs (trigger_id, lead_id, event_key, conditions_json, context_json, result, error_message, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $triggerId,
            $leadId,
            $eventKey,
            json_encode($conditions, JSON_UNESCAPED_UNICODE),
            json_encode($context, JSON_UNESCAPED_UNICODE),
            $result,
            $error,
        ]);
    }

    private function ensureTables(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS crm_triggers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(190) NOT NULL,
            event_key VARCHAR(80) NOT NULL DEFAULT 'capture_submitted',
            sequence_id INT NOT NULL,
            conditions_json JSON DEFAULT NULL,
            priority INT NOT NULL DEFAULT 100,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_event_active (event_key, is_active),
            KEY idx_sequence (sequence_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS crm_trigger_logs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            trigger_id INT NOT NULL,
            lead_id INT NOT NULL,
            event_key VARCHAR(80) NOT NULL,
            conditions_json JSON DEFAULT NULL,
            context_json JSON DEFAULT NULL,
            result VARCHAR(50) NOT NULL,
            error_message TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_trigger (trigger_id),
            KEY idx_lead (lead_id),
            KEY idx_event (event_key),
            KEY idx_result (result)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
