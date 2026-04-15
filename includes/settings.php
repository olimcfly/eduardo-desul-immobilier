<?php
declare(strict_types=1);

/**
 * Settings helper centralisé (scope utilisateur).
 */

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = '', int $userId = 0): mixed
    {
        $cache = &$GLOBALS['__settings_cache'];
        if (!is_array($cache)) {
            $cache = [];
        }

        $userId = resolveSettingsUserId($userId);
        if ($userId <= 0) {
            return $default;
        }

        $cacheKey = $userId . '_' . $key;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey] ?? $default;
        }

        try {
            $pdo = settingsPdo();
            if (!$pdo) {
                return $default;
            }

            $stmt = $pdo->prepare(
                'SELECT setting_value, setting_type, is_encrypted
                 FROM settings
                 WHERE user_id = ? AND setting_key = ?
                 LIMIT 1'
            );
            $stmt->execute([$userId, $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $cache[$cacheKey] = null;
                return $default;
            }

            $value = $row['setting_value'];
            if ((int)($row['is_encrypted'] ?? 0) === 1 && !empty($value)) {
                $value = decryptSetting((string)$value);
            }

            $typed = castSettingValue($value, (string)($row['setting_type'] ?? 'text'));
            $cache[$cacheKey] = $typed;

            return $typed ?? $default;
        } catch (Throwable $e) {
            error_log('Settings error [' . $key . ']: ' . $e->getMessage());
            return $default;
        }
    }
}

if (!function_exists('settingsGroup')) {
    function settingsGroup(string $group, int $userId = 0): array
    {
        $groupCache = &$GLOBALS['__settings_group_cache'];
        if (!is_array($groupCache)) {
            $groupCache = [];
        }

        $userId = resolveSettingsUserId($userId);
        if ($userId <= 0) {
            return [];
        }

        $cacheKey = $userId . '_' . $group;
        if (isset($groupCache[$cacheKey])) {
            return $groupCache[$cacheKey];
        }

        try {
            $pdo = settingsPdo();
            if (!$pdo) {
                return [];
            }

            // Essai 1 : clés définies dans les templates pour ce groupe
            $stmt = $pdo->prepare(
                'SELECT s.setting_key, s.setting_value, s.setting_type, s.is_encrypted
                 FROM settings s
                 WHERE s.user_id = ?
                   AND s.setting_key IN (
                       SELECT st.setting_key
                       FROM settings_templates st
                       WHERE st.setting_group = ?
                   )'
            );
            $stmt->execute([$userId, $group]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fallback : lecture directe par préfixe (ex: profil_*, site_*, smtp_*)
            if (empty($rows)) {
                $stmt = $pdo->prepare(
                    'SELECT setting_key, setting_value, setting_type, is_encrypted
                     FROM settings
                     WHERE user_id = ? AND setting_key LIKE ?'
                );
                $stmt->execute([$userId, $group . '_%']);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $result = [];
            foreach ($rows as $row) {
                $value = $row['setting_value'];
                if ((int)($row['is_encrypted'] ?? 0) === 1 && !empty($value)) {
                    $value = decryptSetting((string)$value);
                }
                $result[$row['setting_key']] = castSettingValue($value, (string)($row['setting_type'] ?? 'text'));
            }

            $groupCache[$cacheKey] = $result;
            return $result;
        } catch (Throwable $e) {
            error_log('Settings group error [' . $group . ']: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('saveSetting')) {
    function saveSetting(string $key, mixed $value, int $userId = 0): bool
    {
        $userId = resolveSettingsUserId($userId);
        if ($userId <= 0) {
            return false;
        }

        try {
            $pdo = settingsPdo();
            if (!$pdo) {
                return false;
            }

            $stmt = $pdo->prepare(
                'SELECT setting_type
                 FROM settings_templates
                 WHERE setting_key = ?
                 LIMIT 1'
            );
            $stmt->execute([$key]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['setting_type' => 'text'];

            $preparedValue = prepareSettingValue($value, (string)$template['setting_type']);
            $oldValue = setting($key, '', $userId);

            $isEncrypted = (($template['setting_type'] ?? 'text') === 'password') ? 1 : 0;
            if ($isEncrypted === 1 && $preparedValue !== '') {
                $preparedValue = encryptSetting($preparedValue);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO settings (user_id, setting_key, setting_value, setting_type, is_encrypted)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    setting_type = VALUES(setting_type),
                    is_encrypted = VALUES(is_encrypted),
                    updated_at = CURRENT_TIMESTAMP'
            );
            $stmt->execute([
                $userId,
                $key,
                $preparedValue,
                $template['setting_type'],
                $isEncrypted,
            ]);

            logSettingChange($userId, $key, $oldValue, $preparedValue);
            clearSettingCache($userId, $key);

            return true;
        } catch (Throwable $e) {
            error_log('Save setting error [' . $key . ']: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('saveSettingsBatch')) {
    function saveSettingsBatch(array $settings, int $userId = 0): bool
    {
        foreach ($settings as $key => $value) {
            if (!saveSetting((string)$key, $value, $userId)) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('initUserSettings')) {
    function initUserSettings(PDO $pdo, int $userId): void
    {
        $stmt = $pdo->query('SELECT setting_key, default_value, setting_type, 0 as is_encrypted FROM settings_templates');
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $insert = $pdo->prepare(
            'INSERT IGNORE INTO settings (user_id, setting_key, setting_value, setting_type, is_encrypted)
             VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($templates as $template) {
            $insert->execute([
                $userId,
                $template['setting_key'],
                $template['default_value'],
                $template['setting_type'],
                (int)$template['is_encrypted'],
            ]);
        }
    }
}

if (!function_exists('getProfileCompletion')) {
    function getProfileCompletion(int $userId = 0): array
    {
        $userId = resolveSettingsUserId($userId);
        if ($userId <= 0) {
            return ['percent' => 0, 'filled' => 0, 'total' => 0, 'missing' => [], 'is_complete' => false];
        }

        $required = [
            'advisor_firstname',
            'advisor_lastname',
            'advisor_phone',
            'advisor_email',
            'advisor_photo',
            'advisor_title',
            'advisor_tagline',
            'advisor_bio',
            'agency_name',
            'zone_city',
            'zone_postal_code',
            'business_specialties',
            'business_usp',
            'tech_openai_key',
        ];

        $filled = 0;
        $missing = [];
        foreach ($required as $key) {
            $value = setting($key, '', $userId);
            if (!empty($value) && $value !== '[]') {
                $filled++;
            } else {
                $missing[] = $key;
            }
        }

        $total = count($required);
        $percent = $total > 0 ? (int)round(($filled / $total) * 100) : 0;

        return [
            'percent' => $percent,
            'filled' => $filled,
            'total' => $total,
            'missing' => $missing,
            'is_complete' => $percent >= 80,
        ];
    }
}

if (!function_exists('castSettingValue')) {
    function castSettingValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool)(int)$value,
            'number' => is_numeric($value) ? (float)$value : 0,
            'json' => json_decode((string)($value ?? '[]'), true) ?? [],
            default => $value,
        };
    }
}

if (!function_exists('prepareSettingValue')) {
    function prepareSettingValue(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => is_array($value)
                ? (string)json_encode($value, JSON_UNESCAPED_UNICODE)
                : (string)$value,
            'number' => (string)(float)$value,
            default => (string)$value,
        };
    }
}

if (!function_exists('encryptSetting')) {
    function encryptSetting(string $value): string
    {
        $key = $_ENV['APP_ENCRYPT_KEY'] ?? 'change-me-with-32chars-minimum-key';
        $key = hash('sha256', $key, true);
        $iv = random_bytes(16);
        $cipherRaw = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($cipherRaw === false) {
            return '';
        }

        return base64_encode($iv . $cipherRaw);
    }
}

if (!function_exists('decryptSetting')) {
    function decryptSetting(string $encrypted): string
    {
        $raw = base64_decode($encrypted, true);
        if ($raw === false || strlen($raw) <= 16) {
            return '';
        }

        $key = $_ENV['APP_ENCRYPT_KEY'] ?? 'change-me-with-32chars-minimum-key';
        $key = hash('sha256', $key, true);
        $iv = substr($raw, 0, 16);
        $cipherRaw = substr($raw, 16);

        $decrypted = openssl_decrypt($cipherRaw, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted === false ? '' : $decrypted;
    }
}

if (!function_exists('logSettingChange')) {
    function logSettingChange(int $userId, string $key, mixed $old, mixed $new): void
    {
        try {
            $pdo = settingsPdo();
            if (!$pdo) {
                return;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO settings_history (user_id, setting_key, old_value, new_value, ip_address)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $key,
                is_array($old) ? json_encode($old, JSON_UNESCAPED_UNICODE) : (string)$old,
                is_array($new) ? json_encode($new, JSON_UNESCAPED_UNICODE) : (string)$new,
                $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (Throwable $e) {
            // Non-bloquant
        }
    }
}

if (!function_exists('clearSettingCache')) {
    function clearSettingCache(int $userId = 0, string $key = ''): void
    {
        if (!isset($GLOBALS['__settings_cache']) || !is_array($GLOBALS['__settings_cache'])) {
            $GLOBALS['__settings_cache'] = [];
        }
        if (!isset($GLOBALS['__settings_group_cache']) || !is_array($GLOBALS['__settings_group_cache'])) {
            $GLOBALS['__settings_group_cache'] = [];
        }

        if ($userId <= 0) {
            $GLOBALS['__settings_cache'] = [];
            $GLOBALS['__settings_group_cache'] = [];
            return;
        }

        if ($key !== '') {
            unset($GLOBALS['__settings_cache'][$userId . '_' . $key]);
        } else {
            foreach (array_keys($GLOBALS['__settings_cache']) as $cacheKey) {
                if (str_starts_with((string)$cacheKey, $userId . '_')) {
                    unset($GLOBALS['__settings_cache'][$cacheKey]);
                }
            }
        }

        foreach (array_keys($GLOBALS['__settings_group_cache']) as $groupKey) {
            if (str_starts_with((string)$groupKey, $userId . '_')) {
                unset($GLOBALS['__settings_group_cache'][$groupKey]);
            }
        }
    }
}

if (!function_exists('resolveSettingsUserId')) {
    function resolveSettingsUserId(int $userId): int
    {
        if ($userId > 0) {
            return $userId;
        }
        return (int)($_SESSION['user_id'] ?? 0);
    }
}

if (!function_exists('settingsPdo')) {
    function settingsPdo(): ?PDO
    {
        if (function_exists('db')) {
            try {
                return db();
            } catch (Throwable $e) {
                return null;
            }
        }

        global $pdo;
        return $pdo instanceof PDO ? $pdo : null;
    }
}



if (!function_exists('setting_flush')) {
    function setting_flush(int $userId = 0, string $key = ''): void
    {
        clearSettingCache($userId, $key);
    }
}

if (!function_exists('settings_save')) {
    function settings_save(array $data, string $group = 'general', int $userId = 0): bool
    {
        $userId = resolveSettingsUserId($userId);
        if ($userId <= 0) {
            return false;
        }

        $success = saveSettingsBatch($data, $userId);
        if ($success) {
            clearSettingCache($userId);
        }

        return $success;
    }
}

if (!function_exists('settings_group')) {
    function settings_group(string $group, int $userId = 0): array
    {
        return settingsGroup($group, $userId);
    }
}


if (!function_exists('get_ia_status')) {
    function get_ia_status(int $userId = 0): string
    {
        $userId = resolveSettingsUserId($userId);

        $configKeys = [
            (string)($_ENV['ANTHROPIC_API_KEY'] ?? ''),
            (string)($_ENV['OPENAI_API_KEY'] ?? ''),
            (string)($_ENV['MISTRAL_API_KEY'] ?? ''),
        ];

        if ($userId > 0) {
            $configKeys[] = (string)setting('tech_openai_key', '', $userId);
        }

        foreach ($configKeys as $key) {
            if (trim($key) !== '') {
                return 'connected';
            }
        }

        try {
            $pdo = settingsPdo();
            if ($pdo) {
                $stmt = $pdo->prepare(
                    'SELECT api_key
                     FROM ia_configurations
                     WHERE user_id = :user_id AND is_active = 1
                     ORDER BY updated_at DESC, id DESC
                     LIMIT 1'
                );
                $stmt->execute(['user_id' => $userId > 0 ? $userId : (int)($_SESSION['user_id'] ?? 0)]);
                $apiKey = (string)$stmt->fetchColumn();

                if (trim($apiKey) !== '') {
                    return 'connected';
                }
            }
        } catch (Throwable $e) {
            // Table absente ou indisponible => statut déconnecté.
        }

        return 'disconnected';
    }
}

if (!function_exists('replacePlaceholders')) {
    function replacePlaceholders(string $template, int $userId = 0): string
    {
        $userId = resolveSettingsUserId($userId);

        $advisorFirst = (string)setting('advisor_firstname', '', $userId);
        $advisorLast = (string)setting('advisor_lastname', '', $userId);
        $advisorFull = trim($advisorFirst . ' ' . $advisorLast);
        if ($advisorFull === '') {
            $advisorFull = ADVISOR_NAME ?: APP_NAME;
        }

        $zoneCity = (string)setting('zone_city', APP_CITY, $userId);
        $zoneNeighborhoods = setting('zone_neighborhoods', [], $userId);
        $neighborhoodA = is_array($zoneNeighborhoods) && isset($zoneNeighborhoods[0]) ? (string)$zoneNeighborhoods[0] : 'Centre';
        $neighborhoodB = is_array($zoneNeighborhoods) && isset($zoneNeighborhoods[1]) ? (string)$zoneNeighborhoods[1] : 'Quartier 2';

        $map = [
            '{{advisor_name}}'        => $advisorFull,
            '{{agency_name}}'         => (string)setting('agency_name', APP_NAME, $userId),
            '{{advisor_email}}'       => (string)setting('advisor_email', APP_EMAIL, $userId),
            '{{advisor_phone}}'       => (string)setting('advisor_phone', APP_PHONE, $userId),
            '{{zone_city}}'           => $zoneCity,
            '{{zone_neighborhood_1}}' => $neighborhoodA,
            '{{zone_neighborhood_2}}' => $neighborhoodB,
            '{{app_url}}'             => (string)setting('tech_app_url', APP_URL, $userId),
            '{{advisor_photo}}'       => (string)setting('advisor_photo', '/assets/images/advisor-photo.jpg', $userId),
        ];

        return strtr($template, $map);
    }
}
if (!function_exists('setting_set')) {
    function setting_set(string $key, mixed $value, int $userId = 0): bool
    {
        return saveSetting($key, $value, $userId);
    }
}

if (!function_exists('setting_delete')) {
    function setting_delete(string $key, int $userId = 0): bool
    {
        $userId = resolveSettingsUserId($userId);
        if ($userId <= 0) {
            return false;
        }

        try {
            $pdo = settingsPdo();
            if (!$pdo) {
                return false;
            }

            $stmt = $pdo->prepare(
                'DELETE FROM settings WHERE user_id = ? AND setting_key = ?'
            );
            $stmt->execute([$userId, $key]);

            clearSettingCache($userId, $key);
            return true;

        } catch (Throwable $e) {
            error_log('setting_delete error [' . $key . ']: ' . $e->getMessage());
            return false;
        }
    }
}

