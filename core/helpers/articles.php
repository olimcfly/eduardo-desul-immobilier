<?php
declare(strict_types=1);

if (!function_exists('blog_articles_pdo')) {
    function blog_articles_pdo(): ?PDO
    {
        if (function_exists('db')) {
            return db();
        }

        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            return $GLOBALS['pdo'];
        }

        try {
            $host    = $_ENV['DB_HOST'] ?? $_ENV['DATABASE_HOST'] ?? 'localhost';
            $port    = $_ENV['DB_PORT'] ?? $_ENV['DATABASE_PORT'] ?? '';
            $socket  = $_ENV['DB_SOCKET'] ?? $_ENV['DATABASE_SOCKET'] ?? '';
            $dbName  = $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? $_ENV['DATABASE_NAME'] ?? '';
            $user    = $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? $_ENV['DATABASE_USER'] ?? '';
            $pass    = $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? $_ENV['DATABASE_PASSWORD'] ?? '';
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
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (Throwable $e) {
            error_log('Blog DB connection failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('blog_site_id')) {
    function blog_site_id(): int
    {
        return max(1, (int) ($_ENV['BLOG_SITE_ID'] ?? $_ENV['SITE_ID'] ?? 1));
    }
}

if (!function_exists('blog_slugify')) {
    function blog_slugify(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if (is_string($ascii) && $ascii !== '') {
            $text = $ascii;
        }
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        return trim($text, '-');
    }
}

if (!function_exists('blog_word_count')) {
    function blog_word_count(string $html): int
    {
        $text = trim(strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if ($text === '') {
            return 0;
        }
        preg_match_all('/[\p{L}\p{N}\']+/u', $text, $matches);
        return count($matches[0] ?? []);
    }
}

if (!function_exists('blog_excerpt')) {
    function blog_excerpt(string $html, int $length = 180): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?? '');
        if (function_exists('mb_strlen') && mb_strlen($text) > $length) {
            return rtrim((string) mb_substr($text, 0, $length - 1)) . '…';
        }
        if (!function_exists('mb_strlen') && strlen($text) > $length) {
            return rtrim(substr($text, 0, $length - 1)) . '...';
        }
        return $text;
    }
}

if (!function_exists('blog_decode_list')) {
    function blog_decode_list(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', array_map('strval', $value))));
        }

        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', array_map('strval', $decoded))));
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $raw) ?: [])));
    }
}

if (!function_exists('blog_article_from_row')) {
    function blog_article_from_row(array $row): array
    {
        $content = (string) ($row['content_html'] ?? $row['content_brief'] ?? '');
        $title = trim((string) ($row['title'] ?? ''));
        $h1 = trim((string) ($row['h1'] ?? ''));
        $seoTitle = trim((string) ($row['meta_title'] ?? ''));
        $metaDesc = trim((string) ($row['meta_desc'] ?? ''));
        $coverImage = trim((string) ($row['cover_image'] ?? ''));
        $ogImage = trim((string) ($row['og_image'] ?? '')) ?: $coverImage;
        $publishedAt = $row['published_at'] ?? null;
        $createdAt = $row['created_at'] ?? null;
        $wordCount = (int) ($row['word_count'] ?? 0);
        if ($wordCount <= 0) {
            $wordCount = blog_word_count($content);
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'site_id' => (int) ($row['site_id'] ?? blog_site_id()),
            'title' => $title,
            'h1' => $h1 !== '' ? $h1 : $title,
            'slug' => trim((string) ($row['slug'] ?? '')),
            'excerpt' => $metaDesc !== '' ? $metaDesc : blog_excerpt((string) ($row['content_brief'] ?? $content)),
            'content' => $content,
            'content_html' => $content,
            'featured_image' => $coverImage,
            'cover_image' => $coverImage,
            'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
            'category_name' => trim((string) ($row['topic_family'] ?? '')),
            'author_name' => trim((string) ($row['author_name'] ?? '')) ?: (defined('ADVISOR_NAME') ? ADVISOR_NAME : APP_NAME),
            'status' => trim((string) ($row['status'] ?? 'draft')),
            'published_at' => $publishedAt,
            'date' => $publishedAt ?: $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $row['updated_at'] ?? null,
            'seo_title' => $seoTitle !== '' ? $seoTitle : ($h1 !== '' ? $h1 : $title),
            'meta_description' => $metaDesc,
            'meta_desc' => $metaDesc,
            'focus_keyword' => trim((string) ($row['primary_keyword'] ?? '')),
            'primary_keyword' => trim((string) ($row['primary_keyword'] ?? '')),
            'secondary_keywords' => blog_decode_list($row['secondary_keywords_json'] ?? ''),
            'canonical_url' => trim((string) ($row['canonical_url'] ?? '')),
            'robots_index' => (int) ($row['robots_index'] ?? 1),
            'robots_follow' => (int) ($row['robots_follow'] ?? 1),
            'og_title' => trim((string) ($row['og_title'] ?? '')),
            'og_description' => trim((string) ($row['og_description'] ?? '')),
            'og_image' => $ogImage,
            'schema_type' => trim((string) ($row['schema_type'] ?? 'Article')) ?: 'Article',
            'word_count' => $wordCount,
            'reading_time' => max(1, (int) ceil($wordCount / 200)),
            'seo_score' => isset($row['seo_score']) ? (int) $row['seo_score'] : null,
            'seo_checks' => json_decode((string) ($row['seo_checks_json'] ?? ''), true) ?: [],
            'faq' => json_decode((string) ($row['faq_json'] ?? ''), true) ?: [],
            'article_type' => trim((string) ($row['article_type'] ?? 'article')),
            'topic_family' => trim((string) ($row['topic_family'] ?? '')),
        ];
    }
}

if (!function_exists('blog_select_columns')) {
    function blog_select_columns(): string
    {
        return 'id, site_id, category_id, article_type, topic_family, title, slug, h1, primary_keyword, '
            . 'secondary_keywords_json, meta_title, meta_desc, canonical_url, robots_index, robots_follow, '
            . 'og_title, og_description, og_image, schema_type, content_brief, content_html, cover_image, '
            . 'author_name, word_count, reading_time, seo_score, seo_checks_json, faq_json, status, '
            . 'published_at, created_at, updated_at';
    }
}

if (!function_exists('get_articles_list')) {
    function get_articles_list(int $limit = 50, int $websiteId = 1): array
    {
        $limit = max(1, min($limit, 100));
        $siteId = $websiteId > 0 ? $websiteId : blog_site_id();
        $pdo = blog_articles_pdo();
        if (!$pdo) {
            return [];
        }

        try {
            $sql = '
                SELECT ' . blog_select_columns() . '
                FROM seo_articles_plan
                WHERE site_id = :site_id
                  AND status = "published"
                  AND (published_at IS NULL OR published_at <= NOW())
                ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                LIMIT ' . $limit;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':site_id', $siteId, PDO::PARAM_INT);
            $stmt->execute();

            return array_map('blog_article_from_row', $stmt->fetchAll() ?: []);
        } catch (Throwable $e) {
            error_log('Blog articles query failed: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('get_article_by_slug')) {
    function get_article_by_slug(string $slug, bool $preview = false, int $websiteId = 1): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $siteId = $websiteId > 0 ? $websiteId : blog_site_id();
        $pdo = blog_articles_pdo();
        if (!$pdo) {
            return null;
        }

        try {
            $where = 'site_id = :site_id AND slug = :slug';
            if (!$preview) {
                $where .= ' AND status = "published" AND (published_at IS NULL OR published_at <= NOW())';
            }

            $stmt = $pdo->prepare('SELECT ' . blog_select_columns() . ' FROM seo_articles_plan WHERE ' . $where . ' LIMIT 1');
            $stmt->bindValue(':site_id', $siteId, PDO::PARAM_INT);
            $stmt->bindValue(':slug', $slug);
            $stmt->execute();
            $row = $stmt->fetch();

            return $row ? blog_article_from_row($row) : null;
        } catch (Throwable $e) {
            error_log('Blog article query failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('blog_seo_analysis')) {
    function blog_seo_analysis(array $article): array
    {
        $title = trim((string) ($article['title'] ?? ''));
        $seoTitle = trim((string) ($article['seo_title'] ?? $article['meta_title'] ?? ''));
        $meta = trim((string) ($article['meta_description'] ?? $article['meta_desc'] ?? ''));
        $slug = trim((string) ($article['slug'] ?? ''));
        $keyword = trim((string) ($article['focus_keyword'] ?? $article['primary_keyword'] ?? ''));
        $content = (string) ($article['content_html'] ?? $article['content'] ?? '');
        $plain = trim(strip_tags($content));
        $intro = function_exists('mb_substr') ? mb_substr($plain, 0, 500) : substr($plain, 0, 500);
        $wordCount = (int) ($article['word_count'] ?? blog_word_count($content));
        $internalLinks = preg_match_all('/<a\s+[^>]*href=["\']\/(?!\/)[^"\']*["\']/i', $content, $m1);
        $externalLinks = preg_match_all('/<a\s+[^>]*href=["\']https?:\/\/(?!' . preg_quote(parse_url(APP_URL, PHP_URL_HOST) ?: '', '/') . ')[^"\']*["\']/i', $content, $m2);
        $hasFaq = stripos($content, 'faq') !== false || !empty($article['faq']);
        $checks = [];

        $add = static function (string $label, int $points, bool $ok) use (&$checks): void {
            $checks[] = ['label' => $label, 'points' => $points, 'ok' => $ok];
        };

        $add('Title présent', 5, $title !== '');
        $add('SEO title présent', 10, $seoTitle !== '');
        $add('SEO title entre 50 et 60 caractères', 10, strlen($seoTitle) >= 50 && strlen($seoTitle) <= 60);
        $add('Meta description présente', 10, $meta !== '');
        $add('Meta description entre 150 et 160 caractères', 10, strlen($meta) >= 150 && strlen($meta) <= 160);
        $add('Slug présent et propre', 10, $slug !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1);
        $add('Focus keyword présent', 5, $keyword !== '');
        $add('Focus keyword dans le title ou SEO title', 10, $keyword !== '' && (stripos($title, $keyword) !== false || stripos($seoTitle, $keyword) !== false));
        $add('Focus keyword dans l’introduction', 10, $keyword !== '' && stripos($intro, $keyword) !== false);
        $add('Article supérieur à 800 mots', 10, $wordCount > 800);
        $add('Au moins 3 liens internes', 5, $internalLinks >= 3);
        $add('Au moins 1 lien externe', 5, $externalLinks >= 1);
        $add('Image mise en avant présente', 5, trim((string) ($article['featured_image'] ?? $article['cover_image'] ?? '')) !== '');
        $add('FAQ présente si structure existante', 5, $hasFaq);

        $score = 0;
        foreach ($checks as $check) {
            if ($check['ok']) {
                $score += (int) $check['points'];
            }
        }

        return [
            'score' => min(100, $score),
            'checks' => $checks,
            'passed' => array_values(array_filter($checks, static fn (array $check): bool => (bool) $check['ok'])),
            'failed' => array_values(array_filter($checks, static fn (array $check): bool => !(bool) $check['ok'])),
        ];
    }
}
