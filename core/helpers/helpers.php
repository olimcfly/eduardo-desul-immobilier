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

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }
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

function generateRef(string $type, int $id): string
{
    $prefix = strtoupper(substr($type, 0, 3));
    return $prefix . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
}
