<?php

declare(strict_types=1);

/**
 * Audit « launch checklist » : vérifications automatiques + clés pour suivi manuel (sauvegardées en settings).
 */
final class SeoLaunchChecklistService
{
    public const SETTING_KEY = 'seo_launch_checklist_manual';

    /** @return array<int, array{id:string, title:string, hint:string, auto:bool}> */
    public static function definitions(): array
    {
        return [
            ['id' => 'redirects_preferred', 'title' => 'Site redirects to preferred version', 'hint' => 'HTTPS, www vs non-www alignés avec APP_URL.', 'auto' => true],
            ['id' => 'ssl_certificate', 'title' => 'SSL certificate installed', 'hint' => 'Connexion sécurisée côté serveur.', 'auto' => true],
            ['id' => 'page_speed_2s', 'title' => 'Page speed faster than 2 seconds (TTFB approx.)', 'hint' => 'Test HTTP simple sur la page d’accueil ; compléter avec Lighthouse.', 'auto' => true],
            ['id' => 'google_analytics', 'title' => 'Google Analytics exists', 'hint' => 'Variable d’environnement GA_MEASUREMENT_ID.', 'auto' => true],
            ['id' => 'conversion_tracking', 'title' => 'Conversion tracking online and offline', 'hint' => 'Événements GA, Meta, appels, formulaires — à valider manuellement.', 'auto' => false],
            ['id' => 'robots_txt', 'title' => 'Robots.txt exists', 'hint' => 'URL /robots.txt (dynamique, basée sur APP_URL).', 'auto' => true],
            ['id' => 'xml_sitemap', 'title' => 'XML sitemap exists', 'hint' => 'URL /sitemap.xml.', 'auto' => true],
            ['id' => 'xml_sitemap_search_console', 'title' => 'XML sitemap submitted to Search Console', 'hint' => 'Google Search Console.', 'auto' => false],
            ['id' => 'xml_sitemap_in_robots', 'title' => 'XML sitemap included in robots.txt', 'hint' => 'Directive Sitemap: …', 'auto' => true],
            ['id' => 'html_sitemap', 'title' => 'HTML sitemap created', 'hint' => 'Route /plan-du-site.', 'auto' => true],
            ['id' => 'html_sitemap_footer', 'title' => 'HTML sitemap in footer', 'hint' => 'Lien « Plan du site » dans le pied de page.', 'auto' => true],
            ['id' => 'search_console_submitted', 'title' => 'Website submitted to Search Console', 'hint' => 'Propriété + sitemap.', 'auto' => false],
            ['id' => 'content_budget', 'title' => 'Content budget exists', 'hint' => 'Plan éditorial / calendrier.', 'auto' => false],
            ['id' => 'easy_to_edit', 'title' => 'Website is easy to edit', 'hint' => 'CMS admin, fiches, paramètres.', 'auto' => false],
            ['id' => 'keywords_phase_two', 'title' => 'Keywords and anchors mapped properly in phase two', 'hint' => 'Phase SEO mots-clés / ancres.', 'auto' => false],
            ['id' => 'internal_links_home', 'title' => 'Internal links pointing to home page on main pages', 'hint' => 'Logo + fil d’Ariane — revue manuelle.', 'auto' => false],
            ['id' => 'header_footer_links', 'title' => 'All footer & header links work?', 'hint' => 'Test HTTP sur un échantillon d’URLs du menu et du footer.', 'auto' => true],
            ['id' => 'website_sandboxed', 'title' => 'Website sandboxed (staging)', 'hint' => 'Environnement de préprod si applicable.', 'auto' => false],
            ['id' => 'about_page', 'title' => 'About page exists', 'hint' => '/a-propos', 'auto' => true],
            ['id' => 'contact_page', 'title' => 'Contact page exists', 'hint' => '/contact', 'auto' => true],
            ['id' => 'no_mixed_content', 'title' => 'No insecure mixed content (HTTP assets on HTTPS)', 'hint' => 'Scan basique des templates layout.', 'auto' => true],
            ['id' => 'favicon', 'title' => 'Favicon', 'hint' => 'Fichier /assets/images/favicon.svg référencé dans le layout.', 'auto' => true],
            ['id' => 'terms_page', 'title' => 'Terms of service page', 'hint' => 'CGV /conditions : /cgv', 'auto' => true],
            ['id' => 'privacy_page', 'title' => 'Privacy policy page', 'hint' => '/politique-confidentialite', 'auto' => true],
        ];
    }

    public static function siteOwnerUserId(): int
    {
        try {
            $row = db()->query("SELECT id FROM users WHERE role = 'user' ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            return (int) ($row['id'] ?? 0);
        } catch (Throwable) {
            return 0;
        }
    }

    /** @return array<string, bool> */
    public static function loadManual(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }
        $raw = setting(self::SETTING_KEY, '', $userId);
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_map(static fn ($v) => (bool) $v, $decoded) : [];
    }

    /** @param array<string, bool> $flags */
    public static function saveManual(int $userId, array $flags): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return saveSetting(self::SETTING_KEY, json_encode($flags, JSON_UNESCAPED_UNICODE), $userId);
    }

    /** @return array<string, array{state:string, detail:string}> */
    public static function runAutoChecks(): array
    {
        $out = [];
        $base = rtrim(defined('APP_URL') ? (string) APP_URL : '', '/');

        $httpsRequest = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $appHttps = str_starts_with($base, 'https://');
        $redirectsOk = $appHttps && ($base === '' || $httpsRequest || php_sapi_name() === 'cli');
        if ($base !== '' && !$appHttps) {
            $redirectsOk = false;
        }
        $out['redirects_preferred'] = [
            'state' => $redirectsOk ? 'ok' : 'warn',
            'detail' => $appHttps ? 'APP_URL en HTTPS.' : 'Définir APP_URL en https:// pour la production.',
        ];
        $out['ssl_certificate'] = [
            'state' => $httpsRequest || $appHttps || $base === '' ? 'ok' : 'warn',
            'detail' => $httpsRequest ? 'Requête actuelle en HTTPS.' : 'Vérifier le certificat sur le domaine public.',
        ];

        $out['page_speed_2s'] = self::checkHomeLatency($base);

        $ga = trim((string) ($_ENV['GA_MEASUREMENT_ID'] ?? ''));
        $out['google_analytics'] = [
            'state' => $ga !== '' ? 'ok' : 'warn',
            'detail' => $ga !== '' ? 'G-' . preg_replace('/^G-/i', '', $ga) : 'Ajouter GA_MEASUREMENT_ID dans .env',
        ];

        $out['robots_txt'] = self::httpCheck($base . '/robots.txt', [200]);
        $out['xml_sitemap'] = self::httpCheck($base . '/sitemap.xml', [200]);
        $robotsBody = self::httpGetBody($base . '/robots.txt', 8000);
        $sitemapInRobots = $robotsBody !== null
            && stripos($robotsBody, 'Sitemap:') !== false
            && ($base === '' || stripos($robotsBody, $base) !== false || stripos($robotsBody, 'sitemap.xml') !== false);
        $out['xml_sitemap_in_robots'] = [
            'state' => $sitemapInRobots ? 'ok' : 'warn',
            'detail' => $sitemapInRobots ? 'Directive Sitemap détectée.' : 'Ajouter une ligne Sitemap: vers l’URL absolue du sitemap.',
        ];

        $out['html_sitemap'] = self::httpCheck($base . '/plan-du-site', [200]);

        $footerPath = defined('ROOT_PATH') ? ROOT_PATH . '/public/templates/footer.php' : '';
        $footer = is_readable($footerPath) ? (string) file_get_contents($footerPath) : '';
        $footerOk = str_contains($footer, '/plan-du-site');
        $out['html_sitemap_footer'] = [
            'state' => $footerOk ? 'ok' : 'warn',
            'detail' => $footerOk ? 'Lien présent dans footer.php.' : 'Ajouter le lien vers /plan-du-site dans le footer.',
        ];

        $paths = ['/', '/contact', '/a-propos', '/biens', '/blog', '/services', '/financement', '/mentions-legales', '/politique-confidentialite', '/cgv'];
        $broken = [];
        foreach ($paths as $p) {
            $st = self::httpCheck($base . $p, [200, 301, 302, 303, 307, 308]);
            if ($st['state'] !== 'ok') {
                $broken[] = $p . ' → ' . ($st['detail'] ?? '');
            }
        }
        $out['header_footer_links'] = [
            'state' => $broken === [] ? 'ok' : 'warn',
            'detail' => $broken === [] ? 'Échantillon d’URLs principales répond OK.' : 'Problèmes : ' . implode(' ; ', array_slice($broken, 0, 4)),
        ];

        $out['about_page'] = self::httpCheck($base . '/a-propos', [200]);
        $out['contact_page'] = self::httpCheck($base . '/contact', [200, 302]);
        $out['terms_page'] = self::httpCheck($base . '/cgv', [200]);
        $out['privacy_page'] = self::httpCheck($base . '/politique-confidentialite', [200]);

        $layoutPath = defined('ROOT_PATH') ? ROOT_PATH . '/public/templates/layout.php' : '';
        $layout = is_readable($layoutPath) ? (string) file_get_contents($layoutPath) : '';
        $badHttp = preg_match('#["\']http://(?!localhost)#i', $layout . $footer) > 0;
        $out['no_mixed_content'] = [
            'state' => !$badHttp ? 'ok' : 'warn',
            'detail' => !$badHttp ? 'Pas de http:// évident dans layout/footer.' : 'Ressources http:// détectées — passer en https:// ou relatif.',
        ];

        $fav = defined('ROOT_PATH') && is_file(ROOT_PATH . '/public/assets/images/favicon.svg');
        $out['favicon'] = [
            'state' => $fav ? 'ok' : 'warn',
            'detail' => $fav ? 'favicon.svg présent.' : 'Ajouter public/assets/images/favicon.svg',
        ];

        return $out;
    }

    /** @param array<string, array{state:string, detail:string}> $auto @param array<string, bool> $manual */
    public static function score(array $auto, array $manual): array
    {
        $defs = self::definitions();
        $autoOk = 0;
        $autoTotal = 0;
        $manualOk = 0;
        $manualTotal = 0;
        foreach ($defs as $d) {
            $id = $d['id'];
            if ($d['auto']) {
                $autoTotal++;
                if (($auto[$id]['state'] ?? '') === 'ok') {
                    $autoOk++;
                }
            } else {
                $manualTotal++;
                if (!empty($manual[$id])) {
                    $manualOk++;
                }
            }
        }
        $noteAuto = $autoTotal > 0 ? (int) round($autoOk / $autoTotal * 100) : 0;
        $noteManual = $manualTotal > 0 ? (int) round($manualOk / $manualTotal * 100) : 0;
        $noteGlobal = $manualTotal > 0
            ? (int) round(($noteAuto * 0.65) + ($noteManual * 0.35))
            : $noteAuto;

        return [
            'auto_ok' => $autoOk,
            'auto_total' => $autoTotal,
            'manual_ok' => $manualOk,
            'manual_total' => $manualTotal,
            'note_auto_pct' => $noteAuto,
            'note_manual_pct' => $noteManual,
            'note_global_pct' => $noteGlobal,
        ];
    }

    private static function checkHomeLatency(string $base): array
    {
        if ($base === '') {
            return ['state' => 'na', 'detail' => 'APP_URL non défini : impossible de mesurer depuis l’admin.'];
        }
        $url = $base . '/';
        $t0 = microtime(true);
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => "Accept: text/html\r\n",
            ],
        ]);
        $len = @strlen((string) @file_get_contents($url, false, $ctx, 0, 65536));
        $ms = (int) round((microtime(true) - $t0) * 1000);
        if ($len === 0) {
            return ['state' => 'warn', 'detail' => 'Impossible de joindre la page d’accueil (timeout ou erreur).'];
        }
        if ($ms < 2000) {
            return ['state' => 'ok', 'detail' => "Temps d’aller-retour ~{$ms} ms (échantillon HTML, pas LCP complet)."];
        }

        return ['state' => 'warn', 'detail' => "Réponse lente (~{$ms} ms). Cible &lt; 2000 ms — optimiser serveur / cache / images."];
    }

    /** @param list<int> $okCodes */
    private static function httpCheck(string $url, array $okCodes): array
    {
        if ($url === '' || !str_starts_with($url, 'http')) {
            return ['state' => 'na', 'detail' => 'URL invalide ou APP_URL manquant.'];
        }
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 6,
                'ignore_errors' => true,
            ],
        ]);
        $headers = @get_headers($url, true, $ctx);
        $code = 0;
        if (is_array($headers)) {
            $first = is_array($headers[0] ?? null) ? ($headers[0][0] ?? '') : ($headers[0] ?? '');
            if (preg_match('#\s(\d{3})\s#', (string) $first, $m)) {
                $code = (int) $m[1];
            }
        }
        $okCodes = array_merge($okCodes, [206]);
        if (in_array($code, $okCodes, true)) {
            return ['state' => 'ok', 'detail' => "HTTP {$code}"];
        }
        if ($code === 0) {
            return ['state' => 'warn', 'detail' => 'Pas de réponse HTTP (réseau, DNS, ou restrictions serveur).'];
        }

        return ['state' => 'warn', 'detail' => "HTTP {$code} (attendu : " . implode('/', array_unique($okCodes)) . ')'];
    }

    private static function httpGetBody(string $url, int $maxLen): ?string
    {
        if ($url === '' || !str_starts_with($url, 'http')) {
            return null;
        }
        $ctx = stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true],
        ]);
        $body = @file_get_contents($url, false, $ctx, 0, $maxLen);

        return $body === false ? null : (string) $body;
    }
}
