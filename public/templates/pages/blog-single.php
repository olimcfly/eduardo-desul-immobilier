<?php

declare(strict_types=1);

/**
 * Template page CMS : article de blog
 *
 * Variables attendues si disponibles :
 * - $page : array
 * - $sections : array
 * - $article : array
 * - $post : array
 *
 * Le template essaie d'être tolérant :
 * - il récupère l'article via $article ou $post
 * - il affiche les sections CMS si elles existent
 * - il garde un fallback HTML si le contenu article existe directement
 */

function blog_single_safe_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

function blog_single_safe_string(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function blog_single_raw_string(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

function blog_single_format_date(mixed $value): string
{
    $date = trim((string) ($value ?? ''));

    if ($date === '') {
        return '';
    }

    try {
        $dt = new DateTime($date);
        return $dt->format('d/m/Y');
    } catch (Throwable) {
        return $date;
    }
}

function blog_single_reading_time(string $content): int
{
    $text = trim(strip_tags($content));

    if ($text === '') {
        return 0;
    }

    $words = str_word_count(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    return max(1, (int) ceil($words / 200));
}

$pageData = blog_single_safe_array($page ?? null);
$sectionsData = blog_single_safe_array($sections ?? null);
$articleData = blog_single_safe_array($article ?? ($post ?? null));

$title = blog_single_raw_string(
    $articleData['title']
    ?? $articleData['name']
    ?? $pageData['title']
    ?? 'Article'
);

$excerpt = blog_single_raw_string(
    $articleData['excerpt']
    ?? $articleData['summary']
    ?? $articleData['description']
    ?? $pageData['meta_description']
    ?? ''
);

$content = (string) (
    $articleData['content_html']
    ?? $articleData['content']
    ?? $articleData['body']
    ?? ''
);

$coverImage = blog_single_raw_string(
    $articleData['cover_image']
    ?? $articleData['featured_image']
    ?? $articleData['image']
    ?? $articleData['thumbnail']
    ?? ''
);

$author = blog_single_raw_string(
    $articleData['author_name']
    ?? $articleData['author']
    ?? $pageData['author']
    ?? ''
);

$publishedAt = blog_single_format_date(
    $articleData['published_at']
    ?? $articleData['created_at']
    ?? $pageData['published_at']
    ?? ''
);

$updatedAt = blog_single_format_date(
    $articleData['updated_at']
    ?? $pageData['updated_at']
    ?? ''
);

$category = blog_single_raw_string(
    $articleData['category_name']
    ?? $articleData['category']
    ?? ''
);

$tags = blog_single_safe_array($articleData['tags'] ?? null);
$readingTime = blog_single_reading_time($content);

/**
 * Détection du renderer de sections
 * Adapte automatiquement plusieurs conventions possibles.
 */
$sectionRendererCallable = null;

if (isset($sectionRenderer) && is_callable($sectionRenderer)) {
    $sectionRendererCallable = $sectionRenderer;
} elseif (class_exists('\\App\\Support\\SectionRenderer') && method_exists('\\App\\Support\\SectionRenderer', 'render')) {
    $sectionRendererCallable = static fn (array $section): string => \App\Support\SectionRenderer::render($section);
} elseif (class_exists('\\App\\Cms\\SectionRenderer') && method_exists('\\App\\Cms\\SectionRenderer', 'render')) {
    $sectionRendererCallable = static fn (array $section): string => \App\Cms\SectionRenderer::render($section);
}

$hasSections = $sectionsData !== [];
$hasArticleContent = trim($content) !== '';
?>

<main class="page page-blog-single">
    <article class="blog-single">
        <header class="blog-single__hero">
            <div class="container">
                <nav class="breadcrumb" aria-label="Fil d’Ariane">
                    <ol class="breadcrumb__list">
                        <li class="breadcrumb__item"><a href="/">Accueil</a></li>
                        <li class="breadcrumb__item"><a href="/blog">Blog</a></li>
                        <li class="breadcrumb__item" aria-current="page"><?= blog_single_safe_string($title) ?></li>
                    </ol>
                </nav>

                <?php if ($category !== ''): ?>
                    <p class="blog-single__category"><?= blog_single_safe_string($category) ?></p>
                <?php endif; ?>

                <h1 class="blog-single__title"><?= blog_single_safe_string($title) ?></h1>

                <?php if ($excerpt !== ''): ?>
                    <p class="blog-single__excerpt"><?= blog_single_safe_string($excerpt) ?></p>
                <?php endif; ?>

                <div class="blog-single__meta">
                    <?php if ($author !== ''): ?>
                        <span>Par <?= blog_single_safe_string($author) ?></span>
                    <?php endif; ?>

                    <?php if ($publishedAt !== ''): ?>
                        <span>Publié le <?= blog_single_safe_string($publishedAt) ?></span>
                    <?php endif; ?>

                    <?php if ($readingTime > 0): ?>
                        <span><?= $readingTime ?> min de lecture</span>
                    <?php endif; ?>

                    <?php if ($updatedAt !== '' && $updatedAt !== $publishedAt): ?>
                        <span>Mise à jour le <?= blog_single_safe_string($updatedAt) ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($coverImage !== ''): ?>
                    <figure class="blog-single__cover">
                        <img
                            src="<?= blog_single_safe_string($coverImage) ?>"
                            alt="<?= blog_single_safe_string($title) ?>"
                            loading="lazy"
                        >
                    </figure>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($hasSections && $sectionRendererCallable !== null): ?>
            <div class="blog-single__sections">
                <?php foreach ($sectionsData as $section): ?>
                    <?php
                    $sectionArray = blog_single_safe_array($section);
                    if ($sectionArray === []) {
                        continue;
                    }

                    echo (string) $sectionRendererCallable($sectionArray);
                    ?>
                <?php endforeach; ?>
            </div>
        <?php elseif ($hasArticleContent): ?>
            <div class="container">
                <div class="blog-single__layout">
                    <div class="blog-single__main card">
                        <div class="blog-single__content content-editor">
                            <?= $content ?>
                        </div>

                        <?php if ($tags !== []): ?>
                            <footer class="blog-single__footer">
                                <h2 class="blog-single__footer-title">Mots-clés</h2>
                                <div class="blog-single__tags">
                                    <?php foreach ($tags as $tag): ?>
                                        <?php $tagLabel = trim((string) $tag); ?>
                                        <?php if ($tagLabel === '') continue; ?>
                                        <span class="blog-tag"><?= blog_single_safe_string($tagLabel) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </footer>
                        <?php endif; ?>
                    </div>

                    <aside class="blog-single__sidebar">
                        <div class="card blog-single__sidebar-card">
                            <h2>Besoin d’aide pour votre projet immobilier ?</h2>
                            <p>
                                Profitez d’un accompagnement clair, local et concret pour avancer
                                dans votre vente, votre achat ou votre estimation.
                            </p>
                            <div class="blog-single__sidebar-actions">
                                <a class="btn btn-primary" href="/contact">Parler de mon projet</a>
                                <a class="btn btn-secondary" href="/estimation">Demander une estimation</a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        <?php else: ?>
            <div class="container">
                <section class="card blog-single__empty">
                    <h2>Article en préparation</h2>
                    <p>Le contenu de cet article n’est pas encore disponible.</p>
                </section>
            </div>
        <?php endif; ?>
    </article>
</main>