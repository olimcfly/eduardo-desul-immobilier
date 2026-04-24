<?php
// core/helpers/articles.php

function _get_pdo(): \PDO
{
    if (function_exists('db'))           return db();
    if (isset($GLOBALS['pdo']))          return $GLOBALS['pdo'];
    if (class_exists('\Core\Database'))  return \Core\Database::getInstance();
    throw new \RuntimeException('PDO introuvable');
}

// ─────────────────────────────────────────────
// LISTE — page /blog
// ─────────────────────────────────────────────
function get_articles_list(int $limit = 12): array
{
    $pdo = _get_pdo();

    $sql = "
        SELECT
            id,
            COALESCE(h1, title)                     AS title,
            slug,
            article_type                            AS type,
            topic_family,
            COALESCE(
                meta_desc,
                LEFT(content_brief, 160),
                ''
            )                                       AS excerpt,
            cover_image                             AS image,
            COALESCE(published_at, created_at)      AS date
        FROM seo_articles_plan
        WHERE status    = 'published'
          AND site_id   = 1
        ORDER BY COALESCE(published_at, created_at) DESC
        LIMIT :limit
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log('[get_articles_list] ' . $e->getMessage());
        return [];
    }
}

// ─────────────────────────────────────────────
// ARTICLE INDIVIDUEL — page /blog/{slug}
// ─────────────────────────────────────────────
function get_article_by_slug(string $slug): ?array
{
    if (empty(trim($slug))) return null;

    $pdo = _get_pdo();

    $sql = "
        SELECT
            id,
            article_type                                AS type,
            topic_family,

            -- SEO
            COALESCE(meta_title, h1, title)             AS seo_title,
            COALESCE(h1, title)                         AS titre,
            meta_desc,
            primary_keyword,
            secondary_keywords_json,

            slug,

            -- Contenu
            COALESCE(content_html, content_brief, '')   AS contenu,
            word_count                                  AS mots,

            -- Image
            cover_image                                 AS image,

            -- Dates
            published_at                                AS date_publication,
            created_at,
            updated_at

        FROM seo_articles_plan
        WHERE slug   = :slug
          AND status = 'published'
          AND site_id = 1
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', trim($slug));
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (\PDOException $e) {
        error_log('[get_article_by_slug] ' . $e->getMessage());
        return null;
    }
}
