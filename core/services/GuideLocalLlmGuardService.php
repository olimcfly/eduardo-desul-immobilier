<?php
declare(strict_types=1);

/**
 * Rate limiting fichier + cache des réponses LLM guide local (évite coûts API et abus).
 */
final class GuideLocalLlmGuardService
{
    private const CACHE_SUBDIR = 'responses';
    private const RL_SUBDIR    = 'rl';

    public static function baseDir(): string
    {
        $d = rtrim((string) (defined('STORAGE_PATH') ? STORAGE_PATH : ''), '/') . '/cache/guide-local-llm';
        if (!is_dir($d) && !@mkdir($d, 0775, true) && !is_dir($d)) {
            throw new RuntimeException('Impossible de créer le répertoire cache guide-local-llm');
        }

        return $d;
    }

    public static function clientKeyFromRequest(): string
    {
        $raw = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0');
        if ($raw !== '' && str_contains($raw, ',')) {
            $raw = trim(explode(',', $raw, 2)[0]);
        }
        if ($raw === '') {
            $raw = '0';
        }

        return hash('sha256', $raw);
    }

    public static function intEnv(string $key, int $default): int
    {
        if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
            return $default;
        }
        $v = filter_var($_ENV[$key], FILTER_VALIDATE_INT);

        return $v !== false && $v >= 0 ? $v : $default;
    }

    public static function responseCacheKey(string $operation, array $params): string
    {
        ksort($params);

        return hash('sha256', $operation . "\0" . json_encode($params, JSON_UNESCAPED_UNICODE));
    }

    public static function getCached(string $cacheKey): ?string
    {
        $path = self::baseDir() . '/' . self::CACHE_SUBDIR . '/' . $cacheKey . '.json';
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['expires_at'], $data['text'])) {
            return null;
        }
        if ((int) $data['expires_at'] < time()) {
            @unlink($path);

            return null;
        }

        return (string) $data['text'];
    }

    public static function setCached(string $cacheKey, string $text, int $ttlSeconds): void
    {
        $dir = self::baseDir() . '/' . self::CACHE_SUBDIR;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }
        $path = $dir . '/' . $cacheKey . '.json';
        $payload = json_encode(
            [
                'expires_at' => time() + max(60, $ttlSeconds),
                'text'       => $text,
                'saved_at'   => time(),
            ],
            JSON_UNESCAPED_UNICODE
        );
        if ($payload !== false) {
            @file_put_contents($path, $payload, LOCK_EX);
        }
    }

    /**
     * @param callable(): string $generator
     * @return array{text: string, cached: bool}
     */
    public static function rememberWithLimits(string $clientKey, string $operation, array $params, callable $generator): array
    {
        $cacheKey = self::responseCacheKey($operation, $params);
        $ttl      = self::intEnv('GUIDE_LOCAL_LLM_CACHE_TTL_SEC', 86400 * 30);

        $hit = self::getCached($cacheKey);
        if ($hit !== null) {
            return ['text' => $hit, 'cached' => true];
        }

        self::assertGenerationAllowed($clientKey);

        $text = $generator();
        self::setCached($cacheKey, $text, $ttl);

        return ['text' => $text, 'cached' => false];
    }

    /** Limite toutes les requêtes HTTP (y compris cache hit) — appelée par l’API avant tout travail. */
    public static function assertApiAllowed(string $clientKey): void
    {
        $maxHour = self::intEnv('GUIDE_LOCAL_LLM_RL_API_PER_HOUR', 120);
        $maxMin  = self::intEnv('GUIDE_LOCAL_LLM_RL_API_PER_MINUTE', 15);
        self::consumeSlotsOrThrow($clientKey, 'api', $maxHour, $maxMin);
    }

    private static function assertGenerationAllowed(string $clientKey): void
    {
        $maxHour = self::intEnv('GUIDE_LOCAL_LLM_RL_GEN_PER_HOUR', 24);
        $maxMin  = self::intEnv('GUIDE_LOCAL_LLM_RL_GEN_PER_MINUTE', 6);
        self::consumeSlotsOrThrow($clientKey, 'gen', $maxHour, $maxMin);
    }

    private static function rlPath(string $clientKey): string
    {
        $h = preg_replace('/[^a-f0-9]/', '', $clientKey) ?: 'na';
        $dir = self::baseDir() . '/' . self::RL_SUBDIR;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Impossible de créer le répertoire rate-limit');
        }

        return $dir . '/' . $h . '.json';
    }

    private static function consumeSlotsOrThrow(string $clientKey, string $prefix, int $maxHour, int $maxMin): void
    {
        $path = self::rlPath($clientKey);
        $fp = @fopen($path, 'c+');
        if (!$fp) {
            return;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);

            return;
        }
        try {
            rewind($fp);
            $raw = stream_get_contents($fp);
            $state = ($raw !== false && $raw !== '') ? json_decode($raw, true) : [];
            if (!is_array($state)) {
                $state = [];
            }
            $now  = time();
            $winH = (int) floor($now / 3600);
            $winM = (int) floor($now / 60);
            $kh   = $prefix . '_h';
            $km   = $prefix . '_m';
            if (!isset($state[$kh]) || !is_array($state[$kh])) {
                $state[$kh] = ['w' => $winH, 'c' => 0];
            }
            if (!isset($state[$km]) || !is_array($state[$km])) {
                $state[$km] = ['w' => $winM, 'c' => 0];
            }
            if ((int) $state[$kh]['w'] !== $winH) {
                $state[$kh] = ['w' => $winH, 'c' => 0];
            }
            if ((int) $state[$km]['w'] !== $winM) {
                $state[$km] = ['w' => $winM, 'c' => 0];
            }
            if ((int) $state[$km]['c'] >= $maxMin) {
                throw new RuntimeException('Trop de requêtes. Réessayez dans une minute.', 429);
            }
            if ((int) $state[$kh]['c'] >= $maxHour) {
                throw new RuntimeException('Trop de requêtes. Réessayez dans une heure.', 429);
            }
            $state[$km]['c'] = (int) $state[$km]['c'] + 1;
            $state[$kh]['c'] = (int) $state[$kh]['c'] + 1;
            $out = json_encode($state, JSON_UNESCAPED_UNICODE);
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $out !== false ? $out : '{}');
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
