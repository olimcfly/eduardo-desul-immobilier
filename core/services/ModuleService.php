<?php

class ModuleService
{
    public static function ensureSchema(): void
    {
        $db = Database::getInstance();

        $db->exec(
            'CREATE TABLE IF NOT EXISTS module_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                module_name VARCHAR(100) NOT NULL,
                enabled_for_users TINYINT(1) DEFAULT 1,
                enabled_for_admins TINYINT(1) DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_module_name (module_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public static function getAllSettings(array $moduleNames = []): array
    {
        self::ensureSchema();
        $db = Database::getInstance();

        $stmt = $db->query('SELECT module_name, enabled_for_users, enabled_for_admins, updated_at FROM module_settings ORDER BY module_name ASC');
        $rows = $stmt->fetchAll() ?: [];

        $byModule = [];
        foreach ($rows as $row) {
            $byModule[$row['module_name']] = [
                'module_name' => $row['module_name'],
                'enabled_for_users' => (int) $row['enabled_for_users'] === 1,
                'enabled_for_admins' => (int) $row['enabled_for_admins'] === 1,
                'updated_at' => $row['updated_at'] ?? null,
            ];
        }

        foreach ($moduleNames as $name) {
            if (!isset($byModule[$name])) {
                $byModule[$name] = [
                    'module_name' => $name,
                    'enabled_for_users' => true,
                    'enabled_for_admins' => true,
                    'updated_at' => null,
                ];
            }
        }

        ksort($byModule);
        return array_values($byModule);
    }

    public static function setModuleState(string $moduleName, bool $enabledForUsers, bool $enabledForAdmins): bool
    {
        self::ensureSchema();
        $db = Database::getInstance();

        $stmt = $db->prepare(
            'INSERT INTO module_settings (module_name, enabled_for_users, enabled_for_admins)
             VALUES (:module_name, :enabled_for_users, :enabled_for_admins)
             ON DUPLICATE KEY UPDATE
                enabled_for_users = VALUES(enabled_for_users),
                enabled_for_admins = VALUES(enabled_for_admins),
                updated_at = CURRENT_TIMESTAMP'
        );

        return $stmt->execute([
            'module_name' => self::sanitizeModuleName($moduleName),
            'enabled_for_users' => $enabledForUsers ? 1 : 0,
            'enabled_for_admins' => $enabledForAdmins ? 1 : 0,
        ]);
    }

    public static function isEnabledForRole(string $moduleName, string $role): bool
    {
        if ($role === 'superadmin') {
            return true;
        }

        self::ensureSchema();
        $moduleName = self::sanitizeModuleName($moduleName);

        if ($moduleName === '') {
            return true;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT enabled_for_users, enabled_for_admins FROM module_settings WHERE module_name = :module_name LIMIT 1');
        $stmt->execute(['module_name' => $moduleName]);
        $row = $stmt->fetch();

        if (!$row) {
            return true;
        }

        if ($role === 'admin') {
            return (int) $row['enabled_for_admins'] === 1;
        }

        if ($role === 'user') {
            return (int) $row['enabled_for_users'] === 1;
        }

        return true;
    }

    public static function renderUnavailablePage(string $moduleName): void
    {
        http_response_code(403);
        echo '<div style="padding:32px;max-width:720px;margin:40px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;font-family:Inter,Arial,sans-serif">';
        echo '<h1 style="margin:0 0 8px;color:#111827">Module indisponible</h1>';
        echo '<p style="margin:0;color:#4b5563">Le module <strong>' . htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8') . '</strong> est temporairement désactivé pour votre compte.</p>';
        echo '</div>';
    }

    public static function trackUserPagePresence(int $userId, string $pageUrl): void
    {
        self::ensurePresenceSchema();
        $db = Database::getInstance();

        $stmt = $db->prepare(
            'INSERT INTO user_page_presence (user_id, page_url, last_seen_at)
             VALUES (:user_id, :page_url, NOW())
             ON DUPLICATE KEY UPDATE
                page_url = VALUES(page_url),
                last_seen_at = NOW()'
        );

        $stmt->execute([
            'user_id' => $userId,
            'page_url' => mb_substr($pageUrl, 0, 255),
        ]);
    }

    public static function getActiveUserPages(int $minutes = 5): array
    {
        self::ensurePresenceSchema();
        $db = Database::getInstance();

        $stmt = $db->prepare(
            'SELECT u.id AS user_id, u.name, u.email, upp.page_url, upp.last_seen_at
             FROM user_page_presence upp
             INNER JOIN users u ON u.id = upp.user_id
             WHERE upp.last_seen_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
               AND u.role = "user"
             ORDER BY upp.last_seen_at DESC'
        );
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    private static function ensurePresenceSchema(): void
    {
        $db = Database::getInstance();
        $db->exec(
            'CREATE TABLE IF NOT EXISTS user_page_presence (
                user_id INT NOT NULL PRIMARY KEY,
                page_url VARCHAR(255) DEFAULT NULL,
                last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_user_presence_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    private static function sanitizeModuleName(string $moduleName): string
    {
        return preg_replace('/[^a-z0-9_-]/', '', mb_strtolower(trim($moduleName)));
    }
}
