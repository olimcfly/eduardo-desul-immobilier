<?php
// ============================================================
// HELPERS GLOBAUX
// ============================================================

function formatPrice(int|float $price, string $suffix = '€'): string
{
    return number_format($price, 0, ',', ' ') . ' ' . $suffix;
}

function formatSurface(int|float $m2): string
{
    return number_format($m2, 0, ',', ' ') . ' m²';
}

function formatDate(string $date, string $format = 'd/m/Y'): string
{
    return (new DateTime($date))->format($format);
}

function timeAgo(string $date): string
{
    $diff = time() - strtotime($date);
    if ($diff < 60)     return 'A l instant';
    if ($diff < 3600)   return floor($diff/60) . ' min';
    if ($diff < 86400)  return floor($diff/3600) . 'h';
    if ($diff < 604800) return floor($diff/86400) . 'j';
    return formatDate($date);
}

function slugify(string $text): string
{
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function truncate(string $text, int $length = 150): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function asset(string $path): string
{
    return APP_URL . '/public/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf(?string $token = null): bool
{
    if ($token !== null) {
        return hash_equals(csrfToken(), $token);
    }

    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $postedToken)) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }

    return true;
}

function paginate(int $total, int $perPage, int $current): array
{
    $pages = (int) ceil($total / $perPage);
    return [
        'total'    => $total,
        'per_page' => $perPage,
        'current'  => $current,
        'pages'    => $pages,
        'offset'   => ($current - 1) * $perPage,
        'has_prev' => $current > 1,
        'has_next' => $current < $pages,
    ];
}



function asset_url(string $path): string
{
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $normalizedPath = '/' . ltrim($path, '/');

    static $manifest = null;
    if ($manifest === null) {
        $manifestPath = ROOT_PATH . '/storage/cache/assets-manifest.json';
        if (is_file($manifestPath)) {
            $decoded = json_decode((string) file_get_contents($manifestPath), true);
            $manifest = is_array($decoded) ? $decoded : [];
        } else {
            $manifest = [];
        }
    }

    if (isset($manifest[$normalizedPath])) {
        return (string) $manifest[$normalizedPath];
    }

    $localPath = asset_local_path($normalizedPath);
    if ($localPath !== null && is_file($localPath)) {
        return $normalizedPath . '?v=' . filemtime($localPath);
    }

    return $normalizedPath;
}

function asset_local_path(string $normalizedPath): ?string
{
    if (str_starts_with($normalizedPath, '/assets/')) {
        return ROOT_PATH . '/public' . $normalizedPath;
    }

    if (str_starts_with($normalizedPath, '/admin/assets/')) {
        return ROOT_PATH . '/public' . $normalizedPath;
    }

    if (str_starts_with($normalizedPath, '/modules/')) {
        return ROOT_PATH . $normalizedPath;
    }

    return null;
}

function generateRef(string $type, int $id): string
{
    $prefix = strtoupper(substr($type, 0, 3));
    return $prefix . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
}

function get_ia_status(?int $userId = null): string
{
    $resolvedUserId = $userId ?? (int) ($_SESSION['user_id'] ?? 0);
    if ($resolvedUserId <= 0) {
        return 'disconnected';
    }

    try {
        $stmt = db()->prepare(
            'SELECT provider, api_key, model
             FROM ia_configurations
             WHERE user_id = :user_id AND is_active = 1
             ORDER BY updated_at DESC, id DESC
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $resolvedUserId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return 'disconnected';
    }

    if (!is_array($config)) {
        return 'disconnected';
    }

    $provider = trim((string) ($config['provider'] ?? ''));
    $apiKey = trim((string) ($config['api_key'] ?? ''));
    $model = trim((string) ($config['model'] ?? ''));

    return ($provider !== '' && $apiKey !== '' && $model !== '')
        ? 'connected'
        : 'disconnected';
}
