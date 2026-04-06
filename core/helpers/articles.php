<?php

declare(strict_types=1);

if (!function_exists('blog_articles_pdo')) {
    /**
     * Initialise une connexion PDO dédiée au blog public.
     * Retourne null si la DB n'est pas accessible (site doit rester affichable).
     */
    function blog_articles_pdo(): ?PDO
    {
        try {
            $host = $_ENV['DB_HOST'] ?? $_ENV['DATABASE_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? $_ENV['DATABASE_PORT'] ?? '';
            $socket = $_ENV['DB_SOCKET'] ?? $_ENV['DATABASE_SOCKET'] ?? '';
            $dbName = $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? $_ENV['DATABASE_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? $_ENV['DATABASE_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? $_ENV['DATABASE_PASSWORD'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            if ($dbName === '' || $user === '') {
                return null;
            }

            $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
            if ($port !== '') {
                $dsn .= ';port=' . (int) $port;
            }
            if ($socket !== '') {
                $dsn .= ';unix_socket=' . $socket;
            }

            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Throwable $e) {
            error_log('Blog DB connection failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('get_articles_list')) {
    /**
     * Retourne la liste des articles publiés pour la page blog publique.
     *
     * @return array<int, array{title:string, excerpt:string, slug:string}>
     */
    function get_articles_list(int $limit = 50, int $websiteId = 1): array
    {
        $limit = max(1, min($limit, 100));

        $pdo = blog_articles_pdo();
        if (!$pdo) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT titre, slug, contenu, meta_desc
                 FROM blog_articles
                 WHERE website_id = :website_id
                   AND statut = 'publié'
                 ORDER BY COALESCE(date_publication, created_at) DESC
                 LIMIT {$limit}"
            );
            $stmt->bindValue(':website_id', $websiteId, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('Blog articles query failed: ' . $e->getMessage());
            return [];
        }

        return array_map(
            static function (array $row): array {
                $title = trim((string) ($row['titre'] ?? ''));
                $slug = trim((string) ($row['slug'] ?? ''));
                $meta = trim((string) ($row['meta_desc'] ?? ''));
                $content = trim(strip_tags((string) ($row['contenu'] ?? '')));

                return [
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $meta !== '' ? $meta : truncate($content, 160),
                ];
            },
            $rows
        );
    }
}
