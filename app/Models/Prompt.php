<?php

class Prompt
{
    private PDO $db;

    private array $allowedTypes = ['article', 'secteur', 'reseaux', 'image', 'email', 'seo', 'gmb'];
    private array $allowedPlatforms = ['facebook', 'google', 'tiktok', 'linkedin'];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS prompts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(190) NOT NULL,
            type ENUM('article', 'secteur', 'reseaux', 'image', 'email', 'seo', 'gmb') NOT NULL,
            plateforme ENUM('facebook', 'google', 'tiktok', 'linkedin') NULL,
            template TEXT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_type (type),
            INDEX idx_plateforme (plateforme),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    public function all(?string $type = null): array
    {
        if ($type !== null && in_array($type, $this->allowedTypes, true)) {
            $stmt = $this->db->prepare('SELECT * FROM prompts WHERE type = :type ORDER BY updated_at DESC');
            $stmt->execute([':type' => $type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->db->query('SELECT * FROM prompts ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM prompts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO prompts (name, type, plateforme, template, is_active) VALUES (:name, :type, :plateforme, :template, :is_active)');
        $stmt->execute([
            ':name' => $data['name'],
            ':type' => $this->sanitizeType($data['type']),
            ':plateforme' => $this->sanitizePlatform($data['plateforme']),
            ':template' => $data['template'],
            ':is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE prompts SET name = :name, type = :type, plateforme = :plateforme, template = :template, is_active = :is_active WHERE id = :id');
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':type' => $this->sanitizeType($data['type']),
            ':plateforme' => $this->sanitizePlatform($data['plateforme']),
            ':template' => $data['template'],
            ':is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM prompts WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM prompts')->fetchColumn();
    }

    private function sanitizeType(?string $type): string
    {
        return in_array($type, $this->allowedTypes, true) ? $type : 'article';
    }

    private function sanitizePlatform(?string $platform): ?string
    {
        return in_array($platform, $this->allowedPlatforms, true) ? $platform : null;
    }
}
