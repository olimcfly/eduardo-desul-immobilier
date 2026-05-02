<?php

declare(strict_types=1);

final class SeoKeywordPilotService
{
    public function __construct(private PDO $pdo, private int $userId)
    {
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS seo_keywords (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                advisor_id BIGINT UNSIGNED NULL,
                website_id BIGINT UNSIGNED NULL,
                keyword VARCHAR(190) NOT NULL,
                city_name VARCHAR(160) NULL,
                intent ENUM('estimation','vente','achat','quartier','commune','blog') NOT NULL DEFAULT 'estimation',
                target_url VARCHAR(255) NULL,
                status ENUM('active','paused','archived') NOT NULL DEFAULT 'active',
                current_position INT NULL,
                previous_position INT NULL,
                last_checked_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_keyword_city (user_id, keyword, city_name),
                KEY idx_keywords_user (user_id),
                KEY idx_keywords_status_intent (status, intent),
                KEY idx_keywords_position (current_position)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS seo_keyword_positions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                keyword_id BIGINT UNSIGNED NOT NULL,
                position_value INT NULL,
                checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                source VARCHAR(40) NOT NULL DEFAULT 'manual',
                notes VARCHAR(255) NULL,
                KEY idx_keyword_positions_keyword_date (keyword_id, checked_at),
                CONSTRAINT fk_keyword_positions_keyword FOREIGN KEY (keyword_id)
                    REFERENCES seo_keywords(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->tryAlter('ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS advisor_id BIGINT UNSIGNED NULL AFTER user_id');
        $this->tryAlter('ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS website_id BIGINT UNSIGNED NULL AFTER advisor_id');
        $this->tryAlter('ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS city_name VARCHAR(160) NULL AFTER keyword');
        $this->tryAlter("ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS intent ENUM('estimation','vente','achat','quartier','commune','blog') NOT NULL DEFAULT 'estimation' AFTER city_name");
        $this->tryAlter('ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS status ENUM(\'active\',\'paused\',\'archived\') NOT NULL DEFAULT \'active\' AFTER target_url');
        $this->tryAlter('ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS current_position INT NULL AFTER status');
        $this->tryAlter('ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS previous_position INT NULL AFTER current_position');
        $this->tryAlter('ALTER TABLE seo_keywords ADD COLUMN IF NOT EXISTS last_checked_at DATETIME NULL AFTER previous_position');
    }

    private function tryAlter(string $sql): void
    {
        try {
            $this->pdo->exec($sql);
        } catch (Throwable) {
            // Compatibilité MySQL versions différentes.
        }
    }

    public function getDashboard(array $filters = []): array
    {
        $where = ['k.user_id = :user_id'];
        $params = ['user_id' => $this->userId];

        if (!empty($filters['city'])) {
            $where[] = 'k.city_name = :city';
            $params['city'] = (string)$filters['city'];
        }
        if (!empty($filters['intent'])) {
            $where[] = 'k.intent = :intent';
            $params['intent'] = (string)$filters['intent'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'k.status = :status';
            $params['status'] = (string)$filters['status'];
        }
        if (!empty($filters['top10'])) {
            $where[] = 'k.current_position IS NOT NULL AND k.current_position <= 10';
        }
        if (!empty($filters['trend'])) {
            if ($filters['trend'] === 'progression') {
                $where[] = 'k.current_position IS NOT NULL AND k.previous_position IS NOT NULL AND (k.previous_position - k.current_position) > 0';
            }
            if ($filters['trend'] === 'regression') {
                $where[] = 'k.current_position IS NOT NULL AND k.previous_position IS NOT NULL AND (k.previous_position - k.current_position) < 0';
            }
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);
        $listSql = "SELECT k.*,
                        (CASE WHEN k.current_position IS NOT NULL AND k.previous_position IS NOT NULL THEN (k.previous_position - k.current_position) ELSE NULL END) AS delta
                    FROM seo_keywords k
                    {$sqlWhere}
                    ORDER BY COALESCE(k.current_position, 999), k.keyword ASC";

        $stmt = $this->pdo->prepare($listSql);
        $stmt->execute($params);
        $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'keywords' => $keywords,
            'stats' => $this->getStats(),
            'opportunities' => $this->getOpportunities(),
            'filters' => $this->getFilterValues(),
        ];
    }

    public function getStats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN current_position BETWEEN 1 AND 3 THEN 1 ELSE 0 END) AS top3,
                    SUM(CASE WHEN current_position BETWEEN 1 AND 10 THEN 1 ELSE 0 END) AS top10,
                    AVG(CASE WHEN current_position IS NOT NULL AND previous_position IS NOT NULL
                         THEN (previous_position - current_position) END) AS avg_progress
                FROM seo_keywords
                WHERE user_id = :user_id AND status <> 'archived'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'top3' => (int)($row['top3'] ?? 0),
            'top10' => (int)($row['top10'] ?? 0),
            'avg_progress' => round((float)($row['avg_progress'] ?? 0), 2),
            'opportunities' => count($this->getOpportunities()['position_4_20']),
        ];
    }

    public function getOpportunities(): array
    {
        $positionStmt = $this->pdo->prepare(
            'SELECT * FROM seo_keywords
             WHERE user_id = ? AND status = ? AND current_position BETWEEN 4 AND 20
             ORDER BY current_position ASC LIMIT 8'
        );
        $positionStmt->execute([$this->userId, 'active']);

        $missingUrlStmt = $this->pdo->prepare(
            "SELECT * FROM seo_keywords
             WHERE user_id = ? AND (target_url IS NULL OR target_url = '')
             ORDER BY updated_at DESC LIMIT 8"
        );
        $missingUrlStmt->execute([$this->userId]);

        $progressStmt = $this->pdo->prepare(
            "SELECT * FROM seo_keywords
             WHERE user_id = ? AND previous_position IS NOT NULL AND current_position IS NOT NULL
               AND (previous_position - current_position) > 0
             ORDER BY (previous_position - current_position) DESC LIMIT 8"
        );
        $progressStmt->execute([$this->userId]);

        $cityGapStmt = $this->pdo->prepare(
            "SELECT k.* FROM seo_keywords k
             LEFT JOIN seo_city_pages cp ON cp.user_id = k.user_id AND LOWER(cp.city) = LOWER(k.city_name)
             WHERE k.user_id = ?
               AND k.intent IN ('estimation','vente','achat','quartier','commune')
               AND k.city_name IS NOT NULL AND k.city_name <> ''
               AND cp.id IS NULL
             ORDER BY k.city_name ASC LIMIT 8"
        );
        $cityGapStmt->execute([$this->userId]);

        return [
            'position_4_20' => $positionStmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'missing_url' => $missingUrlStmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'progressing' => $progressStmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'city_gap' => $cityGapStmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
        ];
    }

    public function findKeyword(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seo_keywords WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $this->userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    public function saveKeyword(array $payload, ?int $id = null): int
    {
        $keyword = trim((string)($payload['keyword'] ?? ''));
        if ($keyword === '') {
            throw new InvalidArgumentException('Le mot-clé est obligatoire.');
        }

        $cityName = trim((string)($payload['city_name'] ?? ''));
        $intent = (string)($payload['intent'] ?? 'estimation');
        $status = (string)($payload['status'] ?? 'active');
        if ($status === 'pause') {
            $status = 'paused';
        }
        $targetUrl = trim((string)($payload['target_url'] ?? ''));

        $allowedIntent = ['estimation', 'vente', 'achat', 'quartier', 'commune', 'blog'];
        $allowedStatus = ['active', 'paused', 'archived'];

        if (!in_array($intent, $allowedIntent, true)) {
            $intent = 'estimation';
        }
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'active';
        }

        if ($targetUrl !== '' && !str_starts_with($targetUrl, '/')) {
            throw new InvalidArgumentException('URL cible invalide (format attendu: /mon-url).');
        }

        if ($id !== null && $id > 0) {
            $stmt = $this->pdo->prepare(
                'UPDATE seo_keywords
                 SET keyword = ?, city_name = ?, intent = ?, target_url = ?, status = ?, updated_at = NOW()
                 WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([$keyword, $cityName ?: null, $intent, $targetUrl ?: null, $status, $id, $this->userId]);
            return $id;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO seo_keywords (user_id, advisor_id, keyword, city_name, intent, target_url, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$this->userId, $this->userId, $keyword, $cityName ?: null, $intent, $targetUrl ?: null, $status]);

        return (int)$this->pdo->lastInsertId();
    }

    public function deleteKeyword(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM seo_keywords WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->userId]);
    }

    public function recordPosition(int $keywordId, ?int $position, string $source = 'manual', string $notes = ''): void
    {
        $keyword = $this->findKeyword($keywordId);
        if (!$keyword) {
            throw new InvalidArgumentException('Mot-clé introuvable.');
        }

        $positionValue = $position !== null ? max(1, min(100, $position)) : null;
        $notes = trim($notes);
        $source = trim($source) !== '' ? trim($source) : 'manual';

        $insert = $this->pdo->prepare(
            'INSERT INTO seo_keyword_positions (keyword_id, position_value, checked_at, source, notes)
             VALUES (?, ?, NOW(), ?, ?)'
        );
        $insert->execute([$keywordId, $positionValue, $source, $notes !== '' ? $notes : null]);

        $update = $this->pdo->prepare(
            'UPDATE seo_keywords
             SET previous_position = current_position,
                 current_position = ?,
                 last_checked_at = NOW(),
                 updated_at = NOW()
             WHERE id = ? AND user_id = ?'
        );
        $update->execute([$positionValue, $keywordId, $this->userId]);
    }

    public function getPositionHistory(int $keywordId, int $limit = 30): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->pdo->prepare(
            'SELECT id, position_value, checked_at, source, notes
             FROM seo_keyword_positions
             WHERE keyword_id = ?
             ORDER BY checked_at DESC
             LIMIT ' . $limit
        );
        $stmt->execute([$keywordId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTargetUrlOptions(): array
    {
        $options = [];

        $collector = [
            ['table' => 'pages', 'sql' => "SELECT CONCAT('/', slug) AS url, titre AS label, 'Page' AS source FROM pages WHERE statut = 'publie' ORDER BY updated_at DESC LIMIT 20"],
            ['table' => 'articles', 'sql' => "SELECT CONCAT('/blog/', slug) AS url, titre AS label, 'Article' AS source FROM articles WHERE statut = 'publie' ORDER BY updated_at DESC LIMIT 20"],
            ['table' => 'seo_city_pages', 'sql' => "SELECT CONCAT('/ville/', slug) AS url, city AS label, 'Fiche ville' AS source FROM seo_city_pages WHERE user_id = {$this->userId} ORDER BY updated_at DESC LIMIT 20"],
            ['table' => 'guide_local', 'sql' => "SELECT CONCAT('/guide/', slug) AS url, nom AS label, 'Guide local' AS source FROM guide_local WHERE statut = 'publie' ORDER BY updated_at DESC LIMIT 20"],
            ['table' => 'secteurs', 'sql' => "SELECT CONCAT('/secteur/', slug) AS url, nom AS label, 'Secteur' AS source FROM secteurs ORDER BY id DESC LIMIT 20"],
        ];

        foreach ($collector as $entry) {
            if (!$this->tableExists($entry['table'])) {
                continue;
            }
            try {
                $rows = $this->pdo->query($entry['sql'])->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($rows as $row) {
                    if (!empty($row['url'])) {
                        $options[] = $row;
                    }
                }
            } catch (Throwable) {
                // Ignore sources non disponibles.
            }
        }

        return $options;
    }

    private function tableExists(string $table): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $stmt->execute([$table]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function getFilterValues(): array
    {
        $stmt = $this->pdo->prepare('SELECT DISTINCT city_name FROM seo_keywords WHERE user_id = ? AND city_name IS NOT NULL AND city_name <> "" ORDER BY city_name ASC');
        $stmt->execute([$this->userId]);

        return [
            'cities' => $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [],
            'intents' => ['estimation', 'vente', 'achat', 'quartier', 'commune', 'blog'],
            'statuses' => ['active', 'paused', 'archived'],
        ];
    }
}
