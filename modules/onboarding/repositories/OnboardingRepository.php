<?php

declare(strict_types=1);

final class OnboardingRepository
{
    public function ensureSchema(): void
    {
        db()->exec('CREATE TABLE IF NOT EXISTS onboarding_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            status ENUM("draft","in_progress","completed","archived") NOT NULL DEFAULT "draft",
            current_step TINYINT UNSIGNED NOT NULL DEFAULT 1,
            started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_onboarding_sessions_user_status (user_id, status),
            INDEX idx_onboarding_sessions_updated_at (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS onboarding_answers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id INT UNSIGNED NOT NULL,
            step_key VARCHAR(50) NOT NULL,
            answers_json JSON NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_onboarding_answers_session_step (session_id, step_key),
            INDEX idx_onboarding_answers_session (session_id),
            CONSTRAINT fk_onboarding_answers_session FOREIGN KEY (session_id) REFERENCES onboarding_sessions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS onboarding_blueprints (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id INT UNSIGNED NOT NULL,
            version VARCHAR(20) NOT NULL DEFAULT "1.0",
            blueprint_json JSON NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_onboarding_blueprints_session_version (session_id, version),
            INDEX idx_onboarding_blueprints_session (session_id),
            CONSTRAINT fk_onboarding_blueprints_session FOREIGN KEY (session_id) REFERENCES onboarding_sessions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function findActiveSessionByUserId(int $userId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM onboarding_sessions WHERE user_id = :user_id AND status IN ("draft", "in_progress") ORDER BY updated_at DESC, id DESC LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function createSession(int $userId): array
    {
        $stmt = db()->prepare('INSERT INTO onboarding_sessions (user_id, status, current_step, started_at, created_at, updated_at) VALUES (:user_id, "draft", 1, NOW(), NOW(), NOW())');
        $stmt->execute(['user_id' => $userId]);
        $id = (int) db()->lastInsertId();

        return $this->findSessionById($id) ?? [];
    }

    public function findSessionById(int $sessionId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM onboarding_sessions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function updateSessionProgress(int $sessionId, int $currentStep, string $status = 'in_progress'): void
    {
        $stmt = db()->prepare('UPDATE onboarding_sessions SET current_step = :current_step, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'current_step' => $currentStep,
            'status' => $status,
            'id' => $sessionId,
        ]);
    }

    public function markCompleted(int $sessionId): void
    {
        $stmt = db()->prepare('UPDATE onboarding_sessions SET status = "completed", current_step = 6, completed_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $sessionId]);
    }

    public function upsertStepAnswers(int $sessionId, string $stepKey, array $answers): void
    {
        $stmt = db()->prepare('INSERT INTO onboarding_answers (session_id, step_key, answers_json, created_at, updated_at)
            VALUES (:session_id, :step_key, :answers_json, NOW(), NOW())
            ON DUPLICATE KEY UPDATE answers_json = VALUES(answers_json), updated_at = NOW()');

        $stmt->execute([
            'session_id' => $sessionId,
            'step_key' => $stepKey,
            'answers_json' => json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAnswersBySessionId(int $sessionId): array
    {
        $stmt = db()->prepare('SELECT step_key, answers_json FROM onboarding_answers WHERE session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);

        $mapped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $stepKey = (string) ($row['step_key'] ?? '');
            if ($stepKey === '') {
                continue;
            }

            $decoded = json_decode((string) ($row['answers_json'] ?? '{}'), true);
            $mapped[$stepKey] = is_array($decoded) ? $decoded : [];
        }

        return $mapped;
    }

    public function upsertBlueprint(int $sessionId, string $version, array $blueprint): void
    {
        $stmt = db()->prepare('INSERT INTO onboarding_blueprints (session_id, version, blueprint_json, created_at, updated_at)
            VALUES (:session_id, :version, :blueprint_json, NOW(), NOW())
            ON DUPLICATE KEY UPDATE blueprint_json = VALUES(blueprint_json), updated_at = NOW()');

        $stmt->execute([
            'session_id' => $sessionId,
            'version' => $version,
            'blueprint_json' => json_encode($blueprint, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function getBlueprintBySessionId(int $sessionId, string $version = '1.0'): ?array
    {
        $stmt = db()->prepare('SELECT blueprint_json FROM onboarding_blueprints WHERE session_id = :session_id AND version = :version LIMIT 1');
        $stmt->execute([
            'session_id' => $sessionId,
            'version' => $version,
        ]);
        $payload = $stmt->fetchColumn();
        if (!is_string($payload) || $payload === '') {
            return null;
        }

        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : null;
    }
}
