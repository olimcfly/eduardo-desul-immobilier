<?php

class SecteurSeoService
{
    public function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string)$value, '-');

        return $value !== '' ? $value : 'secteur';
    }

    public function ensureUniqueSlug(PDO $db, int $websiteId, string $slug, ?int $excludeId = null): string
    {
        $slug = $this->slugify($slug);
        if ($slug === '') {
            $slug = 'secteur';
        }

        $base = $slug;
        $i = 2;
        while ($this->slugExists($db, $websiteId, $slug, $excludeId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function buildDefaultSeoTitle(string $name, string $cityName = ''): string
    {
        $city = trim($cityName);
        if ($city === '') {
            return trim($name);
        }

        return trim($name) . ' - Immobilier à ' . $city;
    }

    public function buildDefaultSeoDescription(string $name, string $cityName = ''): string
    {
        $city = trim($cityName);
        $base = 'Découvrez le secteur ' . trim($name);

        if ($city !== '') {
            $base .= ' à ' . $city;
        }

        return mb_substr($base . ': tendances, cadre de vie, prix et conseils locaux.', 0, 320);
    }

    private function slugExists(PDO $db, int $websiteId, string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM secteurs WHERE website_id = ? AND slug = ?';
        $params = [$websiteId, $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (bool)$stmt->fetchColumn();
    }
}
