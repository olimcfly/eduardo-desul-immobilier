<?php
declare(strict_types=1);

/**
 * Settings helper centralisé (scope utilisateur).
 */

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = '', int $userId = 0): mixed
    {
        static $cache = [];

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
        static $groupCache = [];

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

            $result = [];
            foreach ($rows as $row) {
                $value = $row['setting_value'];
                if ((int)($row['is_encrypted'] ?? 0) === 1 && !empty($value)) {
                    $value = decryptSetting((string)$value);
                }
                $result[$row['setting_key']] = castSettingValue($value, (string)$row['setting_type']);
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
        // Le cache est request-scope (static). Rien à faire ici.
        // Hook conservé pour compatibilité API.
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
            '{{advisor_name}}' => $advisorFull,
            '{{agency_name}}' => (string)setting('agency_name', APP_NAME, $userId),
            '{{advisor_email}}' => (string)setting('advisor_email', APP_EMAIL, $userId),
            '{{advisor_phone}}' => (string)setting('advisor_phone', APP_PHONE, $userId),
            '{{zone_city}}' => $zoneCity,
            '{{zone_neighborhood_1}}' => $neighborhoodA,
            '{{zone_neighborhood_2}}' => $neighborhoodB,
            '{{app_url}}' => (string)setting('tech_app_url', APP_URL, $userId),
            '{{advisor_photo}}' => (string)setting('advisor_photo', '/assets/images/eduardo-portrait.jpg', $userId),

            // migration legacy hardcodes
            'Eduardo Desul' => $advisorFull,
            'Eduardo De Sul' => $advisorFull,
            'Eduardo Desul Immobilier' => (string)setting('agency_name', APP_NAME, $userId),
            'contact@eduardo-desul-immobilier.fr' => (string)setting('advisor_email', APP_EMAIL, $userId),
            'https://eduardo-desul-immobilier.fr' => (string)setting('tech_app_url', APP_URL, $userId),
            '/assets/images/eduardo-portrait.jpg' => (string)setting('advisor_photo', '/assets/images/eduardo-portrait.jpg', $userId),
            'Bordeaux' => $zoneCity,
            'Chartrons' => $neighborhoodA,
            'Mérignac' => $neighborhoodB,
            'Pessac' => (is_array($zoneNeighborhoods) && isset($zoneNeighborhoods[2])) ? (string)$zoneNeighborhoods[2] : $neighborhoodB,
        ];

        return strtr($template, $map);
    }
}

