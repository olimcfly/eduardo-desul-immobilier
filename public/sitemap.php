<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = rtrim(APP_URL, '/');
$today = date('Y-m-d');

/**
 * Vérifie si une table existe dans la base courante.
 */
function tableExists(PDO $pdo, string $table): bool
{
    static $cache = [];

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
             LIMIT 1'
        );
        $stmt->execute([':table' => $table]);

        $cache[$table] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$table] = false;
    }

    return $cache[$table];
}

/**
 * Vérifie si une colonne existe dans une table.
 */
function columnExists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
               AND COLUMN_NAME = :column
             LIMIT 1'
        );
        $stmt->execute([
            ':table' => $table,
            ':column' => $column,
        ]);

        $cache[$key] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$key] = false;
    }

    return $cache[$key];
}

/**
 * Ajoute une URL dans la map en évitant les doublons.
 */
function addSitemapUrl(array &$urls, string $baseUrl, string $path, ?string $lastmod = null): void
{
    $path = '/' . ltrim(trim($path), '/');
    $loc = $baseUrl . ($path === '/' ? '' : $path);

    if (isset($urls[$loc])) {
        if ($lastmod !== null && $lastmod !== '' && ($urls[$loc]['lastmod'] === null || $lastmod > $urls[$loc]['lastmod'])) {
            $urls[$loc]['lastmod'] = $lastmod;
        }
        return;
    }

    $urls[$loc] = [
        'loc' => $loc,
        'lastmod' => $lastmod,
    ];
}

try {
    $pdo = db();

    $urls = [];

    // Pages statiques principales.
    $staticPages = [
        '/',
        '/a-propos',
        '/contact',
        '/biens',
        '/biens/maisons',
        '/biens/appartements',
        '/biens/prestige',
        '/biens/vendus',
        '/estimation-gratuite',
        '/avis-de-valeur',
        '/prendre-rendez-vous',
        '/financement',
        '/blog',
        '/secteurs',
        '/avis-clients',
        '/mentions-legales',
        '/politique-confidentialite',
        '/politique-cookies',
        '/cgv',
        '/plan-du-site',
    ];

    foreach ($staticPages as $path) {
        addSitemapUrl($urls, $baseUrl, $path, $today);
    }

    // Pages CMS statiques pilotées via la base (table pages).
    if (tableExists($pdo, 'pages')) {
        $stmt = $pdo->query(
            "SELECT slug, DATE_FORMAT(COALESCE(updated_at, created_at), '%Y-%m-%d') AS lastmod
             FROM pages
             WHERE statut = 'publie'"
        );

        foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '' || $slug === 'home') {
                continue;
            }

            addSitemapUrl($urls, $baseUrl, '/' . $slug, (string) ($row['lastmod'] ?? $today));
        }
    }

    // Annonces immobilières publiées.
    if (tableExists($pdo, 'biens')) {
        $statusConditions = [];
        if (columnExists($pdo, 'biens', 'statut')) {
            $statusConditions[] = "statut IN ('actif', 'pending')";
        }
        if (columnExists($pdo, 'biens', 'active')) {
            $statusConditions[] = 'active = 1';
        }

        $whereStatus = $statusConditions !== [] ? 'AND (' . implode(' OR ', $statusConditions) . ')' : '';

        $stmt = $pdo->query(
            "SELECT slug,
                    DATE_FORMAT(COALESCE(updated_at, created_at), '%Y-%m-%d') AS lastmod
             FROM biens
             WHERE slug IS NOT NULL
               AND slug <> ''
               {$whereStatus}"
        );

        foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            addSitemapUrl($urls, $baseUrl, '/bien/' . $slug, (string) ($row['lastmod'] ?? $today));
        }
    }

    // Articles de blog (table actuelle blog_articles).
    if (tableExists($pdo, 'blog_articles')) {
        $stmt = $pdo->query(
            "SELECT slug, DATE_FORMAT(COALESCE(updated_at, date_publication, created_at), '%Y-%m-%d') AS lastmod
             FROM blog_articles
             WHERE slug IS NOT NULL
               AND slug <> ''
               AND statut = 'publié'"
        );

        foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            addSitemapUrl($urls, $baseUrl, '/blog/' . $slug, (string) ($row['lastmod'] ?? $today));
        }
    }

    // Fallback legacy blog (table articles) si besoin.
    if (tableExists($pdo, 'articles')) {
        $stmt = $pdo->query(
            "SELECT slug, DATE_FORMAT(COALESCE(updated_at, published_at, created_at), '%Y-%m-%d') AS lastmod
             FROM articles
             WHERE slug IS NOT NULL
               AND slug <> ''
               AND statut = 'publie'"
        );

        foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            addSitemapUrl($urls, $baseUrl, '/blog/' . $slug, (string) ($row['lastmod'] ?? $today));
        }
    }

    // Pages secteurs depuis une table dédiée si présente.
    if (tableExists($pdo, 'secteurs')) {
        $typeSelect = columnExists($pdo, 'secteurs', 'type') ? 'type' : "'villes' AS type";

        $stmt = $pdo->query(
            "SELECT slug,
                    {$typeSelect},
                    DATE_FORMAT(COALESCE(updated_at, created_at), '%Y-%m-%d') AS lastmod
             FROM secteurs
             WHERE slug IS NOT NULL
               AND slug <> ''"
        );

        foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $type = trim((string) ($row['type'] ?? ''));
            $type = in_array($type, ['villes', 'quartiers', 'regions'], true) ? $type : 'villes';

            addSitemapUrl($urls, $baseUrl, '/secteurs/' . $type . '/' . $slug, (string) ($row['lastmod'] ?? $today));
        }
    }

    // Fallback secteurs via guide_local (communes/quartiers).
    if (tableExists($pdo, 'guide_local')) {
        $stmt = $pdo->query(
            "SELECT slug,
                    type,
                    DATE_FORMAT(COALESCE(updated_at, created_at), '%Y-%m-%d') AS lastmod
             FROM guide_local
             WHERE slug IS NOT NULL
               AND slug <> ''
               AND statut = 'publie'"
        );

        foreach (($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $rawType = trim((string) ($row['type'] ?? ''));
            $type = $rawType === 'quartier' ? 'quartiers' : 'villes';

            addSitemapUrl($urls, $baseUrl, '/secteurs/' . $type . '/' . $slug, (string) ($row['lastmod'] ?? $today));
        }
    }

    ksort($urls);

    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $entry) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($entry['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . "</loc>\n";
        if (!empty($entry['lastmod'])) {
            echo '    <lastmod>' . htmlspecialchars((string) $entry['lastmod'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . "</lastmod>\n";
        }
        echo "  </url>\n";
    }

    echo "</urlset>\n";
} catch (Throwable $e) {
    http_response_code(500);

    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    echo '</urlset>';

    error_log('Sitemap generation error: ' . $e->getMessage());
}
