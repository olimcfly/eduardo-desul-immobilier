<?php
declare(strict_types=1);

require_once ROOT_PATH . '/core/helpers/articles.php';

$slug = trim((string) ($slug ?? ''));
$preview = isset($_GET['preview']) && ($_GET['preview'] === '1' || $_GET['preview'] === 'true');
$canPreview = $preview && !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
$article = get_article_by_slug($slug, $canPreview);

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article introuvable | ' . APP_NAME;
    $metaDesc = 'Cet article n’existe pas ou n’est plus disponible.';
    require ROOT_PATH . '/public/pages/404.php';
    return;
}

$articleTitle = trim((string) ($article['seo_title'] ?: $article['h1'] ?: $article['title']));
$articleDescription = trim((string) ($article['meta_description'] ?: $article['excerpt']));
$canonicalPath = '/blog/' . rawurlencode((string) $article['slug']);

$pageTitle = $articleTitle;
$metaDesc = $articleDescription;
$canonical = $article['canonical_url'] !== ''
    ? (str_starts_with($article['canonical_url'], 'http') ? $article['canonical_url'] : rtrim(APP_URL, '/') . '/' . ltrim($article['canonical_url'], '/'))
    : rtrim(APP_URL, '/') . $canonicalPath;
$metaRobots = ((int) $article['robots_index'] === 1 ? 'index' : 'noindex')
    . ', '
    . ((int) $article['robots_follow'] === 1 ? 'follow' : 'nofollow');
$ogType = 'article';
$ogTitle = trim((string) ($article['og_title'] ?: $articleTitle));
$ogDescription = trim((string) ($article['og_description'] ?: $articleDescription));
$ogImage = $article['og_image'] !== ''
    ? (str_starts_with($article['og_image'], 'http') ? $article['og_image'] : rtrim(APP_URL, '/') . '/' . ltrim($article['og_image'], '/'))
    : '';
$bodyClass = 'page-blog-article';
$extraCss = ['/assets/css/guide.css'];

$schemaImage = $ogImage !== '' ? [$ogImage] : [];
$articleSchema = [
    '@context' => 'https://schema.org',
    '@type' => $article['schema_type'] ?: 'Article',
    'headline' => $article['h1'] ?: $article['title'],
    'description' => $articleDescription,
    'image' => $schemaImage,
    'author' => [
        '@type' => 'Person',
        'name' => $article['author_name'] ?: ADVISOR_NAME,
    ],
    'datePublished' => $article['published_at'] ?: $article['created_at'],
    'dateModified' => $article['updated_at'] ?: $article['published_at'] ?: $article['created_at'],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $canonical,
    ],
];

$jsonLdBlocks = [$articleSchema];
if (!empty($article['faq']) && is_array($article['faq'])) {
    $faqItems = [];
    foreach ($article['faq'] as $item) {
        $question = trim((string) ($item['question'] ?? $item['q'] ?? ''));
        $answer = trim((string) ($item['answer'] ?? $item['a'] ?? ''));
        if ($question === '' || $answer === '') {
            continue;
        }
        $faqItems[] = [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];
    }
    if ($faqItems !== []) {
        $jsonLdBlocks[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqItems,
        ];
    }
}
?>

<?php if ($canPreview && $article['status'] !== 'published'): ?>
    <div class="container" style="margin-top:1rem">
        <div class="card" style="padding:1rem;border-left:4px solid #c9a84c">
            Prévisualisation admin : cet article est actuellement en statut <strong><?= e($article['status']) ?></strong>.
        </div>
    </div>
<?php endif; ?>

<?php $extraHead = ($extraHead ?? '') . implode("\n", array_map(static fn (array $jsonLdBlock): string => '<script type="application/ld+json">' . json_encode($jsonLdBlock, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>', $jsonLdBlocks)); ?>

<?php require ROOT_PATH . '/public/templates/pages/blog-single.php'; ?>
