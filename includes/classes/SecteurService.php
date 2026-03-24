<?php

class SecteurService
{
    private PDO $db;
    private SecteurSeoService $seoService;
    private SecteurPublishService $publishService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->seoService = new SecteurSeoService();
        $this->publishService = new SecteurPublishService();
    }

    public function ensureSchema(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS secteurs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            website_id INT UNSIGNED NOT NULL,
            city_name VARCHAR(191) DEFAULT NULL,
            city_id INT UNSIGNED DEFAULT NULL,
            name VARCHAR(191) NOT NULL,
            slug VARCHAR(191) NOT NULL,
            status ENUM('draft','data_ready','ai_generated','reviewed','published','archived') NOT NULL DEFAULT 'draft',
            excerpt TEXT NULL,
            intro MEDIUMTEXT NULL,
            is_primary TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            seo_title VARCHAR(191) DEFAULT NULL,
            seo_description VARCHAR(320) DEFAULT NULL,
            canonical_url VARCHAR(500) DEFAULT NULL,
            published_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_secteurs_website_slug (website_id, slug),
            KEY idx_secteurs_website_status (website_id, status),
            KEY idx_secteurs_website_sort (website_id, sort_order, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->exec("CREATE TABLE IF NOT EXISTS secteur_sections (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            secteur_id INT UNSIGNED NOT NULL,
            section_key VARCHAR(100) NOT NULL,
            section_label VARCHAR(191) NOT NULL,
            content MEDIUMTEXT NULL,
            source_type ENUM('manual','ai','imported_market_analysis') NOT NULL DEFAULT 'manual',
            is_enabled TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_section_key (secteur_id, section_key),
            KEY idx_sections_sort (secteur_id, is_enabled, sort_order),
            CONSTRAINT fk_secteur_sections_secteur FOREIGN KEY (secteur_id) REFERENCES secteurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->exec("CREATE TABLE IF NOT EXISTS secteur_generation_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            secteur_id INT UNSIGNED NOT NULL,
            provider VARCHAR(100) DEFAULT NULL,
            model_name VARCHAR(100) DEFAULT NULL,
            trigger_type ENUM('manual','market_analysis_prefill','ai_generation') NOT NULL DEFAULT 'manual',
            input_payload JSON DEFAULT NULL,
            output_payload JSON DEFAULT NULL,
            status ENUM('success','error','skipped') NOT NULL DEFAULT 'success',
            message TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_secteur_generation_logs (secteur_id, created_at),
            CONSTRAINT fk_secteur_generation_logs_secteur FOREIGN KEY (secteur_id) REFERENCES secteurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->exec("CREATE TABLE IF NOT EXISTS secteur_market_data (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            secteur_id INT UNSIGNED NOT NULL,
            source_name VARCHAR(100) NOT NULL DEFAULT 'analyse_marche',
            payload JSON DEFAULT NULL,
            imported_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_secteur_market_data (secteur_id, source_name),
            CONSTRAINT fk_secteur_market_data_secteur FOREIGN KEY (secteur_id) REFERENCES secteurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function getStatuses(): array
    {
        return SecteurPublishService::allowedStatuses();
    }

    public function getStatusLabel(string $status): string
    {
        $labels = [
            'draft' => 'Brouillon',
            'data_ready' => 'Données prêtes',
            'ai_generated' => 'IA généré',
            'reviewed' => 'Relu',
            'published' => 'Publié',
            'archived' => 'Archivé',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    public function listSecteurs(int $websiteId, string $search = '', string $status = 'all'): array
    {
        $where = ['website_id = ?'];
        $params = [$websiteId];

        if ($search !== '') {
            $where[] = '(name LIKE ? OR city_name LIKE ? OR slug LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($status !== 'all' && in_array($status, $this->getStatuses(), true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $sql = 'SELECT * FROM secteurs WHERE ' . implode(' AND ', $where) . ' ORDER BY is_primary DESC, sort_order ASC, updated_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSecteur(int $id, int $websiteId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM secteurs WHERE id = ? AND website_id = ? LIMIT 1');
        $stmt->execute([$id, $websiteId]);
        $secteur = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$secteur) {
            return null;
        }

        $secteur['sections'] = $this->getSections((int)$secteur['id']);

        return $secteur;
    }

    public function findPublishedBySlug(string $slug, int $websiteId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM secteurs WHERE slug = ? AND website_id = ? AND status = ? LIMIT 1');
        $stmt->execute([$slug, $websiteId, SecteurPublishService::STATUS_PUBLISHED]);
        $secteur = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$secteur) {
            return null;
        }

        $secteur['sections'] = $this->getSections((int)$secteur['id'], true);

        return $secteur;
    }

    public function createSecteur(int $websiteId, array $data): int
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            $name = 'Nouveau secteur';
        }

        $cityName = trim((string)($data['city_name'] ?? ''));
        $slugSeed = trim((string)($data['slug'] ?? $name));
        $slug = $this->seoService->ensureUniqueSlug($this->db, $websiteId, $slugSeed);

        $seoTitle = trim((string)($data['seo_title'] ?? '')) ?: $this->seoService->buildDefaultSeoTitle($name, $cityName);
        $seoDescription = trim((string)($data['seo_description'] ?? '')) ?: $this->seoService->buildDefaultSeoDescription($name, $cityName);

        $stmt = $this->db->prepare('INSERT INTO secteurs (website_id, city_name, city_id, name, slug, status, excerpt, intro, is_primary, sort_order, seo_title, seo_description, canonical_url, published_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NOW(), NOW())');
        $stmt->execute([
            $websiteId,
            $cityName ?: null,
            !empty($data['city_id']) ? (int)$data['city_id'] : null,
            $name,
            $slug,
            SecteurPublishService::STATUS_DRAFT,
            null,
            null,
            !empty($data['is_primary']) ? 1 : 0,
            (int)($data['sort_order'] ?? 0),
            $seoTitle,
            $seoDescription,
            trim((string)($data['canonical_url'] ?? '')) ?: null,
        ]);

        $id = (int)$this->db->lastInsertId();
        $this->createDefaultSections($id);

        return $id;
    }

    public function updateSecteur(int $id, int $websiteId, array $data): array
    {
        $current = $this->getSecteur($id, $websiteId);
        if (!$current) {
            return ['success' => false, 'message' => 'Secteur introuvable'];
        }

        $name = trim((string)($data['name'] ?? $current['name']));
        $cityName = trim((string)($data['city_name'] ?? $current['city_name'] ?? ''));
        $slugInput = trim((string)($data['slug'] ?? $current['slug']));
        $slug = $this->seoService->ensureUniqueSlug($this->db, $websiteId, $slugInput !== '' ? $slugInput : $name, $id);

        $status = $this->publishService->normalizeStatus((string)($data['status'] ?? $current['status']));
        $publishedAt = $this->publishService->computePublishedAt((string)$current['status'], $status, $current['published_at'] ?? null);

        $seoTitle = trim((string)($data['seo_title'] ?? '')) ?: $this->seoService->buildDefaultSeoTitle($name, $cityName);
        $seoDescription = trim((string)($data['seo_description'] ?? '')) ?: $this->seoService->buildDefaultSeoDescription($name, $cityName);

        $stmt = $this->db->prepare('UPDATE secteurs SET city_name = ?, city_id = ?, name = ?, slug = ?, status = ?, excerpt = ?, intro = ?, is_primary = ?, sort_order = ?, seo_title = ?, seo_description = ?, canonical_url = ?, published_at = ?, updated_at = NOW() WHERE id = ? AND website_id = ?');
        $stmt->execute([
            $cityName ?: null,
            !empty($data['city_id']) ? (int)$data['city_id'] : null,
            $name,
            $slug,
            $status,
            trim((string)($data['excerpt'] ?? '')) ?: null,
            trim((string)($data['intro'] ?? '')) ?: null,
            !empty($data['is_primary']) ? 1 : 0,
            (int)($data['sort_order'] ?? 0),
            $seoTitle,
            $seoDescription,
            trim((string)($data['canonical_url'] ?? '')) ?: null,
            $publishedAt,
            $id,
            $websiteId,
        ]);

        if (isset($data['sections']) && is_array($data['sections'])) {
            $this->saveSections($id, $data['sections']);
        }

        return ['success' => true, 'message' => 'Secteur enregistré'];
    }

    public function updateStatus(int $id, int $websiteId, string $status): bool
    {
        $current = $this->getSecteur($id, $websiteId);
        if (!$current) {
            return false;
        }

        $status = $this->publishService->normalizeStatus($status);
        $publishedAt = $this->publishService->computePublishedAt((string)$current['status'], $status, $current['published_at'] ?? null);

        $stmt = $this->db->prepare('UPDATE secteurs SET status = ?, published_at = ?, updated_at = NOW() WHERE id = ? AND website_id = ?');
        $stmt->execute([$status, $publishedAt, $id, $websiteId]);

        return $stmt->rowCount() > 0;
    }

    public function getSections(int $secteurId, bool $enabledOnly = false): array
    {
        $sql = 'SELECT * FROM secteur_sections WHERE secteur_id = ?';
        if ($enabledOnly) {
            $sql .= ' AND is_enabled = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$secteurId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function saveSections(int $secteurId, array $sections): void
    {
        $stmt = $this->db->prepare('UPDATE secteur_sections SET section_label = ?, content = ?, source_type = ?, is_enabled = ?, sort_order = ?, updated_at = NOW() WHERE id = ? AND secteur_id = ?');

        foreach ($sections as $section) {
            $sectionId = (int)($section['id'] ?? 0);
            if ($sectionId <= 0) {
                continue;
            }

            $sourceType = (string)($section['source_type'] ?? 'manual');
            if (!in_array($sourceType, ['manual', 'ai', 'imported_market_analysis'], true)) {
                $sourceType = 'manual';
            }

            $stmt->execute([
                trim((string)($section['section_label'] ?? '')),
                (string)($section['content'] ?? ''),
                $sourceType,
                !empty($section['is_enabled']) ? 1 : 0,
                (int)($section['sort_order'] ?? 0),
                $sectionId,
                $secteurId,
            ]);
        }
    }

    private function createDefaultSections(int $secteurId): void
    {
        $defaults = [
            ['overview', 'Présentation du secteur'],
            ['buyer_profile', 'Pour les acheteurs'],
            ['seller_profile', 'Pour les vendeurs'],
            ['market_snapshot', 'Marché local'],
            ['lifestyle', 'Cadre de vie'],
        ];

        $stmt = $this->db->prepare('INSERT INTO secteur_sections (secteur_id, section_key, section_label, content, source_type, is_enabled, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');

        foreach ($defaults as $i => $row) {
            $stmt->execute([$secteurId, $row[0], $row[1], '', 'manual', 1, ($i + 1) * 10]);
        }
    }
}
