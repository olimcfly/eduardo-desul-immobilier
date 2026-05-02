<?php

declare(strict_types=1);

/**
 * Extraction d’informations publiques depuis une URL Google Maps (fiche lieu).
 * Les pages Maps sont souvent hydratées en JS : les résultats peuvent être partiels.
 * Usage : compléter la fiche GMB locale, pas remplacer l’API officielle Google Business.
 */
final class GmbMapScraper
{
    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /**
     * @return array{ok:bool,error?:string,data?:array<string,mixed>}
     */
    public static function fetchAndParse(string $rawUrl): array
    {
        $rawUrl = trim($rawUrl);
        if ($rawUrl === '') {
            return ['ok' => false, 'error' => 'Collez d’abord un lien Google Maps.'];
        }

        $normalized = self::normalizeToHttpsUrl($rawUrl);
        if ($normalized === null) {
            return ['ok' => false, 'error' => 'URL non autorisée ou invalide. Utilisez un lien https://www.google.com/maps/... ou https://maps.app.goo.gl/...'];
        }

        $html = self::httpGet($normalized);
        if ($html === null || $html === '') {
            return ['ok' => false, 'error' => 'Impossible de télécharger la page (timeout, blocage ou réseau). Réessayez ou copiez une autre URL « Partager » depuis Google Maps.'];
        }

        $data = self::parseHtml($html, $normalized);

        return ['ok' => true, 'data' => $data];
    }

    private static function normalizeToHttpsUrl(string $input): ?string
    {
        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . ltrim($input, '/');
        }

        $parts = parse_url($input);
        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        $host = strtolower((string) $parts['host']);
        if (!self::isAllowedHost($host)) {
            return null;
        }

        $scheme = ($parts['scheme'] ?? 'https') === 'http' ? 'http' : 'https';
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return $scheme . '://' . $host . $path . $query;
    }

    private static function isAllowedHost(string $host): bool
    {
        $suffixes = [
            'google.com',
            'google.fr',
            'google.be',
            'g.page',
            'maps.app.goo.gl',
            'goo.gl',
            'business.google.com',
        ];

        foreach ($suffixes as $s) {
            if ($host === $s || str_ends_with($host, '.' . $s)) {
                return true;
            }
        }

        return false;
    }

    private static function httpGet(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 15,
                    'header' => "User-Agent: " . self::UA . "\r\nAccept: text/html\r\nAccept-Language: fr-FR,fr;q=0.9,en;q=0.8\r\n",
                    'follow_location' => 1,
                    'max_redirects' => 5,
                ],
            ]);
            $body = @file_get_contents($url, false, $ctx);

            return is_string($body) && $body !== '' ? $body : null;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 6,
            CURLOPT_TIMEOUT => 18,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml',
                'Accept-Language: fr-FR,fr;q=0.9',
            ],
            CURLOPT_USERAGENT => self::UA,
        ]);

        $body = curl_exec($ch);
        curl_close($ch);

        if (!is_string($body) || $body === '') {
            return null;
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private static function parseHtml(string $html, string $sourceUrl): array
    {
        $name = '';
        if (preg_match('/<meta\s+property="og:title"\s+content="([^"]+)"/i', $html, $m)) {
            $name = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        if ($name === '' && preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
            $name = html_entity_decode(trim(preg_replace('/\s*-\s*Google\s+Maps.*$/i', '', $m[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $description = '';
        if (preg_match('/<meta\s+property="og:description"\s+content="([^"]*)"/i', $html, $m)) {
            $description = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $rating = null;
        $reviewCount = null;
        if (preg_match('/"ratingValue"\s*:\s*"?([\d.]+)"?/i', $html, $m)) {
            $rating = (float) $m[1];
        }
        if (preg_match('/"reviewCount"\s*:\s*"?(\d+)"?/i', $html, $m)) {
            $reviewCount = (int) $m[1];
        }

        $placeId = null;
        if (preg_match('/ChIJ[A-Za-z0-9_-]{20,}/', $html, $m)) {
            $placeId = $m[0];
        }

        $phone = '';
        if (preg_match('/(?:\+33|0)\s*[1-9](?:[\s.-]*\d{2}){4}/', $html, $m)) {
            $phone = preg_replace('/\s+/', ' ', trim($m[0]));
        }

        $addressHint = '';
        if (preg_match('/"streetAddress"\s*:\s*"([^"]+)"/i', $html, $m)) {
            $addressHint = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return [
            'source_url' => $sourceUrl,
            'nom_etablissement' => $name,
            'description_snippet' => $description,
            'rating' => $rating,
            'review_count' => $reviewCount,
            'place_id_guess' => $placeId,
            'telephone_guess' => $phone,
            'adresse_guess' => $addressHint,
        ];
    }
}
