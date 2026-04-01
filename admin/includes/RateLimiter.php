<?php
/**
 * Rate Limiter Utility
 * /admin/includes/RateLimiter.php
 *
 * Simple rate limiting by IP address using filesystem storage
 * No Redis or external dependencies required
 */

class RateLimiter {

    private static $maxRequests = 100;
    private static $windowSeconds = 60;

    /**
     * Check if client IP has exceeded rate limit
     *
     * @param int $maxRequests Maximum requests allowed in time window
     * @param int $windowSeconds Time window in seconds
     * @return bool true if request is allowed, false if rate limit exceeded
     */
    public static function check($maxRequests = 100, $windowSeconds = 60): bool {
        $clientIp = self::getClientIp();
        $rateKey = 'ratelimit_' . md5($clientIp);
        $rateFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $rateKey . '.tmp';

        // Read existing rate limit data
        $rateLimitData = [];
        if (file_exists($rateFile)) {
            $content = @file_get_contents($rateFile);
            $rateLimitData = $content ? json_decode($content, true) : [];
            if (!is_array($rateLimitData)) {
                $rateLimitData = [];
            }
        }

        $now = time();
        $cutoffTime = $now - $windowSeconds;

        // Filter out old requests outside the time window
        $rateLimitData = array_filter($rateLimitData, function($timestamp) use ($cutoffTime) {
            return $timestamp > $cutoffTime;
        });

        // Check if limit exceeded
        if (count($rateLimitData) >= $maxRequests) {
            return false;
        }

        // Record this request
        $rateLimitData[] = $now;
        @file_put_contents($rateFile, json_encode($rateLimitData), LOCK_EX);

        // Cleanup expired files randomly
        if (rand(1, 100) === 1) {
            self::cleanup($windowSeconds);
        }

        return true;
    }

    /**
     * Get client IP address (handles proxies and Cloudflare)
     */
    private static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Cleanup expired rate limit files
     */
    private static function cleanup($windowSeconds): void {
        $tempDir = sys_get_temp_dir();
        $files = @glob($tempDir . '/ratelimit_*.tmp');

        if (!$files) return;

        $now = time();
        foreach ($files as $file) {
            $mtime = @filemtime($file);
            if ($mtime && ($now - $mtime) > ($windowSeconds * 2)) {
                @unlink($file);
            }
        }
    }

    /**
     * Get remaining requests for current IP
     */
    public static function getRemaining($maxRequests = 100, $windowSeconds = 60): int {
        $clientIp = self::getClientIp();
        $rateKey = 'ratelimit_' . md5($clientIp);
        $rateFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $rateKey . '.tmp';

        $rateLimitData = [];
        if (file_exists($rateFile)) {
            $content = @file_get_contents($rateFile);
            $rateLimitData = $content ? json_decode($content, true) : [];
            if (!is_array($rateLimitData)) {
                $rateLimitData = [];
            }
        }

        $now = time();
        $cutoffTime = $now - $windowSeconds;

        $rateLimitData = array_filter($rateLimitData, function($timestamp) use ($cutoffTime) {
            return $timestamp > $cutoffTime;
        });

        return max(0, $maxRequests - count($rateLimitData));
    }
}
