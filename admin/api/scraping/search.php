<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function scrapingJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function scrapingInput(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function scrapingFetch(string $url, int $timeout = 10): string
{
    $context = stream_context_create([
        'http' => [
            'timeout' => $timeout,
            'header' => "User-Agent: Mozilla/5.0 (compatible; EduardoImmoBot/1.0)\r\nAccept: text/html,application/xml;q=0.9,*/*;q=0.8\r\n",
        ],
    ]);

    $html = @file_get_contents($url, false, $context);

    return is_string($html) ? $html : '';
}

function scrapingText(string $value): string
{
    return trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function scrapingType(string $type): string
{
    $type = mb_strtolower($type, 'UTF-8');
    if (str_contains($type, 'maison') || str_contains($type, 'house')) {
        return 'maison';
    }
    if (str_contains($type, 'terrain') || str_contains($type, 'land')) {
        return 'terrain';
    }
    if (str_contains($type, 'commerce') || str_contains($type, 'business') || str_contains($type, 'bureau')) {
        return 'local';
    }
    if (str_contains($type, 'immeuble') || str_contains($type, 'building')) {
        return 'immeuble';
    }

    return 'appartement';
}

function scrapingNumber(mixed $value): float
{
    if (is_int($value) || is_float($value)) {
        return (float) $value;
    }

    return (float) preg_replace('/[^\d.,]/', '', str_replace(',', '.', (string) $value));
}

function scrapingParseListing(string $html, string $url): ?array
{
    if (!preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches)) {
        return null;
    }

    foreach ($matches[1] as $json) {
        $data = json_decode(html_entity_decode($json, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
        if (!is_array($data) || (($data['@type'] ?? '') !== 'RealEstateListing')) {
            continue;
        }

        $address = is_array($data['address'] ?? null) ? $data['address'] : [];
        $geo = is_array($data['geo'] ?? null) ? $data['geo'] : [];
        $offers = is_array($data['offers'] ?? null) ? $data['offers'] : [];
        $floorSize = is_array($data['floorSize'] ?? null) ? $data['floorSize'] : [];
        $images = $data['image'] ?? [];
        if (is_string($images)) {
            $images = [$images];
        }
        if (!is_array($images)) {
            $images = [];
        }

        $sourceId = basename(parse_url($url, PHP_URL_PATH) ?: sha1($url));
        $title = scrapingText((string) ($data['name'] ?? 'Bien eXp France'));
        $city = scrapingText((string) ($address['addressLocality'] ?? ''));
        $type = scrapingType((string) ($data['additionalType'] ?? ''));

        return [
            'id' => sha1($url),
            'source_provider' => 'exp_france',
            'source_id' => $sourceId,
            'source_url' => $url,
            'titre' => $title,
            'description' => scrapingText((string) ($data['description'] ?? '')),
            'property_type' => $type,
            'prix' => (int) scrapingNumber($offers['price'] ?? 0),
            'surface' => scrapingNumber($floorSize['value'] ?? 0),
            'pieces' => (int) ($data['numberOfRooms'] ?? 0),
            'chambres' => (int) ($data['numberOfBedrooms'] ?? 0),
            'sdb' => (int) ($data['numberOfBathroomsTotal'] ?? 0),
            'ville' => $city,
            'code_postal' => scrapingText((string) ($address['postalCode'] ?? '')),
            'lat' => isset($geo['latitude']) ? (float) $geo['latitude'] : null,
            'lng' => isset($geo['longitude']) ? (float) $geo['longitude'] : null,
            'cover_url' => (string) ($images[0] ?? ''),
            'photos' => array_values(array_filter(array_map('strval', $images))),
            'nb_photos' => count($images),
            'reference' => 'EXP-' . substr($sourceId, 0, 12),
            'agent_first_name' => '',
            'agent_last_name' => '',
        ];
    }

    return null;
}

if (!Auth::check()) {
    scrapingJson(['success' => false, 'message' => 'Authentification requise.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    scrapingJson(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$input = scrapingInput();
if (!hash_equals(csrfToken(), (string) ($input['csrf_token'] ?? ''))) {
    scrapingJson(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
}

$ville = trim((string) ($input['ville'] ?? ''));
$type = trim((string) ($input['type'] ?? ''));
$query = mb_strtolower($ville . ' ' . $type, 'UTF-8');

if ($ville === '' && trim((string) ($input['agent'] ?? '')) === '') {
    scrapingJson(['success' => false, 'message' => 'Saisissez une ville ou un agent.'], 422);
}

$sitemap = scrapingFetch('https://www.expfrance.fr/sitemap.xml', 15);
if ($sitemap === '') {
    scrapingJson(['success' => false, 'message' => 'Impossible de lire le sitemap eXp France.'], 502);
}

preg_match_all('#<loc>(https://www\.expfrance\.fr/property/[^<]+)</loc>#i', $sitemap, $urlMatches);
$urls = array_slice(array_values(array_unique($urlMatches[1] ?? [])), 0, 140);
$results = [];

foreach ($urls as $url) {
    if (count($results) >= 30) {
        break;
    }

    $html = scrapingFetch($url, 8);
    if ($html === '') {
        continue;
    }

    $listing = scrapingParseListing($html, $url);
    if (!$listing) {
        continue;
    }

    $haystack = mb_strtolower(($listing['titre'] ?? '') . ' ' . ($listing['ville'] ?? '') . ' ' . ($listing['property_type'] ?? ''), 'UTF-8');
    if ($ville !== '' && !str_contains($haystack, mb_strtolower($ville, 'UTF-8'))) {
        continue;
    }
    if ($type !== '' && !str_contains($haystack, mb_strtolower(scrapingType($type), 'UTF-8'))) {
        continue;
    }

    $stmt = db()->prepare("SELECT statut FROM biens WHERE source_provider = 'exp_france' AND source_id = :source_id LIMIT 1");
    $stmt->execute([':source_id' => $listing['source_id']]);
    $status = $stmt->fetchColumn();
    $listing['already_imported'] = $status !== false;
    $listing['imported_source'] = $status !== false ? 'own' : null;
    $listing['imported_status'] = $status !== false ? (string) $status : null;

    $results[] = $listing;
}

$_SESSION['scraping_exp_results'] = [];
foreach ($results as $result) {
    $_SESSION['scraping_exp_results'][(string) $result['id']] = $result;
}

scrapingJson([
    'success' => true,
    'message' => count($results) . ' bien(s) trouvé(s).',
    'biens' => $results,
    'query' => $query,
]);
