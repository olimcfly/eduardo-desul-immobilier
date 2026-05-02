<?php
declare(strict_types=1);

$pageTitle = 'CMS - Pages';
$pageDescription = 'Edition des pages CMS';

/**
 * MVP CMS: on commence par la page Accueil.
 */
$managedPages = [
    'home' => [
        'label' => 'Accueil',
        'template' => 'pages/core/home',
    ],
];

function cmsEnsureHomePageExists(): void
{
    $pdo = db();
    $slug = 'home';

    $stmt = $pdo->prepare('SELECT id FROM cms_pages WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($exists) {
        return;
    }

    $defaultData = [
        'home_hero_label' => '',
        'home_hero_title' => '',
        'home_hero_subtitle' => '',
        'home_hero_primary_label' => '',
        'home_hero_primary_url' => '',
        'home_hero_secondary_label' => '',
        'home_hero_secondary_url' => '',
        'home_hero_pillars' => [],
        'home_services' => [],
        'home_stats' => [],
        'home_reality_cards' => [],
        'home_comparison' => [
            'with' => ['tag' => 'Avec accompagnement', 'title' => '', 'items' => []],
            'without' => ['tag' => 'Sans accompagnement', 'title' => '', 'items' => []],
        ],
        'home_about_title' => '',
        'home_about_text' => '',
        'home_about_benefits' => [],
        'home_steps' => [],
        'home_testimonials' => [],
        'featured_properties' => [],
        'home_market_cards' => [],
        'home_sell_guide' => [],
        'home_faq' => [],
        'home_final_cta_title' => '',
        'home_final_cta_text' => '',
    ];

    $insert = $pdo->prepare(
        'INSERT INTO cms_pages (slug, title, template, status, data_json, created_at, updated_at)
         VALUES (:slug, :title, :template, :status, :data_json, NOW(), NOW())'
    );
    $insert->execute([
        ':slug' => $slug,
        ':title' => 'Accueil',
        ':template' => 'pages/core/home',
        ':status' => 'published',
        ':data_json' => json_encode($defaultData, JSON_UNESCAPED_UNICODE),
    ]);
}

function cmsLoadPage(string $slug): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM cms_pages WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function cmsHomeViewsPath(): string
{
    return __DIR__ . '/../../storage/cms-home-views.txt';
}

function cmsHomeViewsCount(): int
{
    $path = cmsHomeViewsPath();
    if (!is_file($path)) {
        return 0;
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return 0;
    }
    return max(0, (int)trim($raw));
}

function cmsUpdateHome(array $post): string
{
    $pdo = db();
    $slug = 'home';
    $row = cmsLoadPage($slug);
    if (!$row) {
        throw new RuntimeException('Page CMS introuvable: home');
    }

    $status = (string)($post['status'] ?? 'published');
    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }

    $title = trim((string)($post['title'] ?? 'Accueil'));
    $metaTitle = trim((string)($post['meta_title'] ?? ''));
    $metaDescription = trim((string)($post['meta_description'] ?? ''));
    $linesToList = static function (string $text): array {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $items = [];
        foreach ($lines as $line) {
            $clean = trim((string)$line);
            if ($clean !== '') {
                $items[] = $clean;
            }
        }
        return $items;
    };
    $parseBlocks = static function (string $text): array {
        $rawBlocks = preg_split("/\R{2,}/u", trim($text)) ?: [];
        $blocks = [];
        foreach ($rawBlocks as $rawBlock) {
            $lines = preg_split('/\r\n|\r|\n/', trim($rawBlock)) ?: [];
            $cleanLines = [];
            foreach ($lines as $line) {
                $line = trim((string)$line);
                if ($line !== '') {
                    $cleanLines[] = $line;
                }
            }
            if ($cleanLines !== []) {
                $blocks[] = $cleanLines;
            }
        }
        return $blocks;
    };
    $parseTitleTextBlocks = static function (string $text) use ($parseBlocks): array {
        $result = [];
        foreach ($parseBlocks($text) as $blockLines) {
            $first = (string)($blockLines[0] ?? '');
            $second = implode("\n", array_slice($blockLines, 1));
            if (strpos($first, '::') !== false) {
                [$title, $desc] = array_map('trim', explode('::', $first, 2));
                if ($desc !== '') {
                    $second = $desc . ($second !== '' ? "\n" . $second : '');
                }
                $first = $title;
            }
            if ($first === '' && $second === '') {
                continue;
            }
            $result[] = ['title' => $first, 'text' => trim($second)];
        }
        return $result;
    };
    $toTitleTextEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $title = trim((string)($item['title'] ?? ''));
            $text = trim((string)($item['text'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }
            $rows[] = $title . ($text !== '' ? "\n" . $text : '');
        }
        return implode("\n\n", $rows);
    };
    $toFaqEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $q = trim((string)($item['question'] ?? ''));
            $a = trim((string)($item['answer'] ?? ''));
            if ($q === '' && $a === '') {
                continue;
            }
            $rows[] = $q . ($a !== '' ? "\n" . $a : '');
        }
        return implode("\n\n", $rows);
    };
    $toTestimonialsEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $stars = trim((string)($item['stars'] ?? '★★★★★'));
            $author = trim((string)($item['author'] ?? ''));
            $text = trim((string)($item['text'] ?? ''));
            if ($text === '' && $author === '') {
                continue;
            }
            $rows[] = $stars . ($author !== '' ? ' | ' . $author : '') . ($text !== '' ? "\n" . $text : '');
        }
        return implode("\n\n", $rows);
    };
    $normalizeKeywordInput = static function (string $input, int $maxItems = 10): string {
        $raw = preg_split('/[\r\n,;|]+/', $input) ?: [];
        $items = [];
        foreach ($raw as $part) {
            $clean = trim(preg_replace('/\s+/', ' ', (string)$part));
            if ($clean === '') {
                continue;
            }
            if (mb_strlen($clean) > 50) {
                // Ignore suspiciously long chunks (often pasted content blocks)
                continue;
            }
            $items[] = $clean;
            if (count($items) >= $maxItems) {
                break;
            }
        }
        $items = array_values(array_unique($items));
        return implode(', ', $items);
    };
    $sanitizeFocusKeyword = static function (string $input): string {
        $clean = trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $input)));
        return mb_substr($clean, 0, 70);
    };
    $focusKeyword = $sanitizeFocusKeyword((string)($post['home_seo_focus_keyword'] ?? ''));
    $secondaryKeywords = $normalizeKeywordInput((string)($post['home_seo_secondary_keywords'] ?? ''), 8);
    $semanticTerms = $normalizeKeywordInput((string)($post['home_seo_semantic_terms'] ?? ''), 10);
    if ($focusKeyword === '') {
        $focusKeyword = 'immobilier {{city}}';
    }
    if ($secondaryKeywords === '') {
        $secondaryKeywords = 'estimation immobiliere {{city}}, vente immobiliere {{city}}, achat immobilier {{city}}';
    }
    if ($semanticTerms === '') {
        $semanticTerms = 'notaire, mandat, compromis, acquereur, vendeur';
    }

    if (($post['empty_template'] ?? '') === '1') {
        $homeData = [
            'home_hero_label' => '',
            'home_hero_title' => '',
            'home_hero_subtitle' => '',
            'home_hero_primary_label' => '',
            'home_hero_primary_url' => '',
            'home_hero_secondary_label' => '',
            'home_hero_secondary_url' => '',
            'home_hero_pillars' => [],
            'home_services' => [],
            'home_stats' => [],
            'home_reality_cards' => [],
            'home_comparison' => ['with' => ['tag' => 'Avec accompagnement', 'title' => '', 'items' => []], 'without' => ['tag' => 'Sans accompagnement', 'title' => '', 'items' => []]],
            'home_about_title' => '',
            'home_about_text' => '',
            'home_about_benefits' => [],
            'home_steps' => [],
            'home_testimonials' => [],
            'featured_properties' => [],
            'home_market_cards' => [],
            'home_sell_guide' => [],
            'home_faq' => [],
            'home_final_cta_title' => '',
            'home_final_cta_text' => '',
            'home_services_section_label' => '',
            'home_services_section_title' => '',
            'home_reality_section_label' => '',
            'home_reality_section_title' => '',
            'home_reality_section_subtitle' => '',
            'home_comparison_section_label' => '',
            'home_comparison_section_title' => '',
            'home_comparison_section_subtitle' => '',
            'home_about_section_label' => '',
            'home_about_cta_label' => '',
            'home_about_cta_url' => '',
            'home_method_section_label' => '',
            'home_method_section_title' => '',
            'home_method_section_subtitle' => '',
            'home_method_primary_cta_label' => '',
            'home_method_primary_cta_url' => '',
            'home_method_secondary_cta_label' => '',
            'home_method_secondary_cta_url' => '',
            'home_testimonials_section_label' => '',
            'home_testimonials_section_title' => '',
            'home_testimonials_cta_label' => '',
            'home_testimonials_cta_url' => '',
            'home_featured_section_label' => '',
            'home_featured_section_title' => '',
            'home_featured_section_subtitle' => '',
            'home_featured_item_cta_label' => '',
            'home_featured_item_cta_url' => '',
            'home_featured_section_cta_label' => '',
            'home_featured_section_cta_url' => '',
            'home_market_section_label' => '',
            'home_market_section_title' => '',
            'home_market_section_subtitle' => '',
            'home_market_cta_label' => '',
            'home_market_cta_url' => '',
            'home_sell_section_label' => '',
            'home_sell_section_title' => '',
            'home_sell_section_subtitle' => '',
            'home_sell_cta_label' => '',
            'home_sell_cta_url' => '',
            'home_faq_section_label' => '',
            'home_faq_section_title' => '',
            'home_faq_section_subtitle' => '',
            'home_final_primary_cta_label' => '',
            'home_final_primary_cta_url' => '',
            'home_final_secondary_cta_label' => '',
            'home_final_secondary_cta_url' => '',
            'home_final_third_cta_label' => '',
            'home_final_third_cta_url' => '',
            'home_final_fourth_cta_label' => '',
            'home_final_fourth_cta_url' => '',
            'home_final_fifth_cta_label' => '',
            'home_final_fifth_cta_url' => '',
            'home_seo_focus_keyword' => '',
            'home_seo_secondary_keywords' => '',
            'home_seo_semantic_terms' => '',
        ];
    } else {
        $services = $parseTitleTextBlocks((string)($post['home_services_text'] ?? ''));
        $statsText = $parseTitleTextBlocks((string)($post['home_stats_text'] ?? ''));
        $stats = [];
        foreach ($statsText as $item) {
            $stats[] = ['value' => (string)$item['title'], 'label' => (string)$item['text']];
        }
        $stepsText = $parseTitleTextBlocks((string)($post['home_steps_text'] ?? ''));
        $steps = [];
        foreach ($stepsText as $idx => $item) {
            $num = str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT);
            $steps[] = ['num' => $num, 'title' => (string)$item['title'], 'text' => (string)$item['text']];
        }
        $faqBlocks = $parseBlocks((string)($post['home_faq_text'] ?? ''));
        $faq = [];
        foreach ($faqBlocks as $block) {
            $faq[] = [
                'question' => (string)($block[0] ?? ''),
                'answer' => trim(implode("\n", array_slice($block, 1))),
            ];
        }
        $testBlocks = $parseBlocks((string)($post['home_testimonials_text'] ?? ''));
        $testimonials = [];
        foreach ($testBlocks as $block) {
            $header = (string)($block[0] ?? '★★★★★');
            $stars = '★★★★★';
            $author = '';
            if (strpos($header, '|') !== false) {
                [$starsParsed, $authorParsed] = array_map('trim', explode('|', $header, 2));
                $stars = $starsParsed !== '' ? $starsParsed : '★★★★★';
                $author = $authorParsed;
            } else {
                $stars = $header !== '' ? $header : '★★★★★';
                $author = (string)($block[2] ?? '');
            }
            $text = trim((string)($block[1] ?? ''));
            if ($text === '' && isset($block[2]) && strpos($header, '|') !== false) {
                $text = trim(implode("\n", array_slice($block, 1, -1)));
            }
            $testimonials[] = ['stars' => $stars, 'text' => $text, 'author' => $author];
        }
        $comparisonWithItems = $linesToList((string)($post['home_comparison_with_items_text'] ?? ''));
        $comparisonWithoutItems = $linesToList((string)($post['home_comparison_without_items_text'] ?? ''));
        $properties = $parseTitleTextBlocks((string)($post['featured_properties_text'] ?? ''));
        $featuredProperties = [];
        foreach ($properties as $property) {
            $featuredProperties[] = [
                'title' => (string)$property['title'],
                'city' => '{{city}}',
                'price' => (string)$property['text'],
                'surface' => '',
                'rooms' => '',
                'badge' => 'Sélection',
                'image' => '[URL_IMAGE]',
            ];
        }

        $homeData = [
            'home_hero_label' => trim((string)($post['home_hero_label'] ?? '')),
            'home_hero_title' => trim((string)($post['home_hero_title'] ?? '')),
            'home_hero_subtitle' => trim((string)($post['home_hero_subtitle'] ?? '')),
            'home_hero_primary_label' => trim((string)($post['home_hero_primary_label'] ?? '')),
            'home_hero_primary_url' => trim((string)($post['home_hero_primary_url'] ?? '')),
            'home_hero_secondary_label' => trim((string)($post['home_hero_secondary_label'] ?? '')),
            'home_hero_secondary_url' => trim((string)($post['home_hero_secondary_url'] ?? '')),
            'home_hero_pillars' => $linesToList((string)($post['home_hero_pillars_text'] ?? '')),
            'home_services' => $services,
            'home_stats' => $stats,
            'home_reality_cards' => $parseTitleTextBlocks((string)($post['home_reality_cards_text'] ?? '')),
            'home_comparison' => [
                'with' => [
                    'tag' => trim((string)($post['home_comparison_with_tag'] ?? 'Avec accompagnement')),
                    'title' => trim((string)($post['home_comparison_with_title'] ?? '')),
                    'items' => $comparisonWithItems,
                ],
                'without' => [
                    'tag' => trim((string)($post['home_comparison_without_tag'] ?? 'Sans accompagnement')),
                    'title' => trim((string)($post['home_comparison_without_title'] ?? '')),
                    'items' => $comparisonWithoutItems,
                ],
            ],
            'home_about_title' => trim((string)($post['home_about_title'] ?? '')),
            'home_about_text' => trim((string)($post['home_about_text'] ?? '')),
            'home_about_benefits' => $linesToList((string)($post['home_about_benefits_text'] ?? '')),
            'home_steps' => $steps,
            'home_testimonials' => $testimonials,
            'featured_properties' => $featuredProperties,
            'home_market_cards' => $parseTitleTextBlocks((string)($post['home_market_cards_text'] ?? '')),
            'home_sell_guide' => $parseTitleTextBlocks((string)($post['home_sell_guide_text'] ?? '')),
            'home_faq' => $faq,
            'home_final_cta_title' => trim((string)($post['home_final_cta_title'] ?? '')),
            'home_final_cta_text' => trim((string)($post['home_final_cta_text'] ?? '')),
            'home_services_section_label' => trim((string)($post['home_services_section_label'] ?? '')),
            'home_services_section_title' => trim((string)($post['home_services_section_title'] ?? '')),
            'home_reality_section_label' => trim((string)($post['home_reality_section_label'] ?? '')),
            'home_reality_section_title' => trim((string)($post['home_reality_section_title'] ?? '')),
            'home_reality_section_subtitle' => trim((string)($post['home_reality_section_subtitle'] ?? '')),
            'home_comparison_section_label' => trim((string)($post['home_comparison_section_label'] ?? '')),
            'home_comparison_section_title' => trim((string)($post['home_comparison_section_title'] ?? '')),
            'home_comparison_section_subtitle' => trim((string)($post['home_comparison_section_subtitle'] ?? '')),
            'home_about_section_label' => trim((string)($post['home_about_section_label'] ?? '')),
            'home_about_cta_label' => trim((string)($post['home_about_cta_label'] ?? '')),
            'home_about_cta_url' => trim((string)($post['home_about_cta_url'] ?? '')),
            'home_method_section_label' => trim((string)($post['home_method_section_label'] ?? '')),
            'home_method_section_title' => trim((string)($post['home_method_section_title'] ?? '')),
            'home_method_section_subtitle' => trim((string)($post['home_method_section_subtitle'] ?? '')),
            'home_method_primary_cta_label' => trim((string)($post['home_method_primary_cta_label'] ?? '')),
            'home_method_primary_cta_url' => trim((string)($post['home_method_primary_cta_url'] ?? '')),
            'home_method_secondary_cta_label' => trim((string)($post['home_method_secondary_cta_label'] ?? '')),
            'home_method_secondary_cta_url' => trim((string)($post['home_method_secondary_cta_url'] ?? '')),
            'home_testimonials_section_label' => trim((string)($post['home_testimonials_section_label'] ?? '')),
            'home_testimonials_section_title' => trim((string)($post['home_testimonials_section_title'] ?? '')),
            'home_testimonials_cta_label' => trim((string)($post['home_testimonials_cta_label'] ?? '')),
            'home_testimonials_cta_url' => trim((string)($post['home_testimonials_cta_url'] ?? '')),
            'home_featured_section_label' => trim((string)($post['home_featured_section_label'] ?? '')),
            'home_featured_section_title' => trim((string)($post['home_featured_section_title'] ?? '')),
            'home_featured_section_subtitle' => trim((string)($post['home_featured_section_subtitle'] ?? '')),
            'home_featured_item_cta_label' => trim((string)($post['home_featured_item_cta_label'] ?? '')),
            'home_featured_item_cta_url' => trim((string)($post['home_featured_item_cta_url'] ?? '')),
            'home_featured_section_cta_label' => trim((string)($post['home_featured_section_cta_label'] ?? '')),
            'home_featured_section_cta_url' => trim((string)($post['home_featured_section_cta_url'] ?? '')),
            'home_market_section_label' => trim((string)($post['home_market_section_label'] ?? '')),
            'home_market_section_title' => trim((string)($post['home_market_section_title'] ?? '')),
            'home_market_section_subtitle' => trim((string)($post['home_market_section_subtitle'] ?? '')),
            'home_market_cta_label' => trim((string)($post['home_market_cta_label'] ?? '')),
            'home_market_cta_url' => trim((string)($post['home_market_cta_url'] ?? '')),
            'home_sell_section_label' => trim((string)($post['home_sell_section_label'] ?? '')),
            'home_sell_section_title' => trim((string)($post['home_sell_section_title'] ?? '')),
            'home_sell_section_subtitle' => trim((string)($post['home_sell_section_subtitle'] ?? '')),
            'home_sell_cta_label' => trim((string)($post['home_sell_cta_label'] ?? '')),
            'home_sell_cta_url' => trim((string)($post['home_sell_cta_url'] ?? '')),
            'home_faq_section_label' => trim((string)($post['home_faq_section_label'] ?? '')),
            'home_faq_section_title' => trim((string)($post['home_faq_section_title'] ?? '')),
            'home_faq_section_subtitle' => trim((string)($post['home_faq_section_subtitle'] ?? '')),
            'home_final_primary_cta_label' => trim((string)($post['home_final_primary_cta_label'] ?? '')),
            'home_final_primary_cta_url' => trim((string)($post['home_final_primary_cta_url'] ?? '')),
            'home_final_secondary_cta_label' => trim((string)($post['home_final_secondary_cta_label'] ?? '')),
            'home_final_secondary_cta_url' => trim((string)($post['home_final_secondary_cta_url'] ?? '')),
            'home_final_third_cta_label' => trim((string)($post['home_final_third_cta_label'] ?? '')),
            'home_final_third_cta_url' => trim((string)($post['home_final_third_cta_url'] ?? '')),
            'home_final_fourth_cta_label' => trim((string)($post['home_final_fourth_cta_label'] ?? '')),
            'home_final_fourth_cta_url' => trim((string)($post['home_final_fourth_cta_url'] ?? '')),
            'home_final_fifth_cta_label' => trim((string)($post['home_final_fifth_cta_label'] ?? '')),
            'home_final_fifth_cta_url' => trim((string)($post['home_final_fifth_cta_url'] ?? '')),
            'home_seo_focus_keyword' => $focusKeyword,
            'home_seo_secondary_keywords' => $secondaryKeywords,
            'home_seo_semantic_terms' => $semanticTerms,
        ];
    }

    if ($metaTitle === '') {
        $metaTitle = trim((string)($homeData['home_hero_title'] ?? ''));
        if ($metaTitle === '') {
            $metaTitle = 'Immobilier {{city}} | Vente, achat, estimation';
        }
        $metaTitle = mb_substr($metaTitle, 0, 60);
    }
    if ($metaDescription === '') {
        $metaDescription = trim((string)($homeData['home_hero_subtitle'] ?? ''));
        if ($metaDescription === '') {
            $metaDescription = 'Conseiller immobilier local : estimation, vente et achat avec accompagnement complet.';
        }
        $metaDescription = mb_substr($metaDescription, 0, 160);
    }

    $stmt = $pdo->prepare(
        'UPDATE cms_pages
         SET title = :title,
             template = :template,
             status = :status,
             meta_title = :meta_title,
             meta_description = :meta_description,
             data_json = :data_json,
             updated_at = NOW()
         WHERE id = :id'
    );
    $stmt->execute([
        ':title' => $title !== '' ? $title : 'Accueil',
        ':template' => 'pages/core/home',
        ':status' => $status,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':data_json' => json_encode($homeData, JSON_UNESCAPED_UNICODE),
        ':id' => (int)$row['id'],
    ]);

    return ($post['empty_template'] ?? '') === '1'
        ? 'Page Accueil vidée (template prêt à dupliquer).'
        : 'Page Accueil enregistrée.';
}

/**
 * POST save for page texts must run before admin layout outputs HTML (see admin/index.php flow).
 */
function cms_admin_handle_page_text_post_early(): void
{
    if (
        ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
        || (string) ($_POST['cms_page_text_action'] ?? '') !== 'save'
    ) {
        return;
    }

    require_once __DIR__ . '/page_text_admin.inc.php';
    if (!function_exists('csrfToken')) {
        require_once ROOT_PATH . '/core/helpers/helpers.php';
    }

    $saveErr = cms_page_text_try_save_post();
    $rk = preg_replace('/[^a-z0-9-]/', '', (string) ($_POST['cms_page_key'] ?? ''));
    if ($saveErr === null) {
        header('Location: /admin?module=cms&text_action=edit&cms_page=' . rawurlencode($rk) . '&saved=1', true, 302);
        exit;
    }
    $_SESSION['_cms_page_text_err'] = $saveErr;
    header('Location: /admin?module=cms&text_action=edit&cms_page=' . rawurlencode($rk), true, 302);
    exit;
}

function renderContent(): void
{
    global $managedPages;

    require_once __DIR__ . '/page_text_admin.inc.php';
    require_once __DIR__ . '/cms_registry_helpers.inc.php';
    if (!function_exists('csrfToken')) {
        require_once ROOT_PATH . '/core/helpers/helpers.php';
    }

    $pageTextRegistry = cms_page_text_load_registry();
    $textAction = (string) ($_GET['text_action'] ?? '');
    $cmsPageKey = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['cms_page'] ?? ''));
    if ($textAction === 'edit' && $cmsPageKey !== '' && isset($pageTextRegistry[$cmsPageKey])) {
        require __DIR__ . '/cms_shared_styles.inc.php';
        ?>
    <section class="cms-wrap">
        <header class="cms-hero">
            <h1>CMS — Textes des pages</h1>
            <p>Même présentation que l’édition Accueil : contenu à gauche, assistant SEO à droite lorsque la page expose des champs « SEO &amp; métadonnées ». Données en base (<code>page_contents</code>). Les champs listés correspondent aux appels <code>pcms()</code> et aux lignes <code>$siteSettings['clé'] ?? &quot;…&quot;</code> détectées dans le gabarit.</p>
        </header>
        <?php
        $cmsPageTextFlash = [
            'saved' => !empty($_GET['saved']),
            'error' => '',
        ];
        if (!empty($_SESSION['_cms_page_text_err'])) {
            $cmsPageTextFlash['error'] = (string) $_SESSION['_cms_page_text_err'];
            unset($_SESSION['_cms_page_text_err']);
        }
        cms_page_text_render_editor($cmsPageKey, $pageTextRegistry[$cmsPageKey], $cmsPageTextFlash);
        ?>
    </section>
        <?php
        return;
    }

    cmsEnsureHomePageExists();

    $action = preg_replace('/[^a-z_-]/', '', (string)($_GET['action'] ?? 'list'));
    $slug = preg_replace('/[^a-z0-9-]/', '', (string)($_GET['slug'] ?? 'home'));
    if (!isset($managedPages[$slug])) {
        $slug = 'home';
    }

    $notice = '';
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit' && $slug === 'home') {
        try {
            $notice = cmsUpdateHome($_POST);
        } catch (Throwable $e) {
            $error = 'Erreur sauvegarde: ' . $e->getMessage();
        }
    }

    $page = cmsLoadPage($slug);
    $homeViewsCount = cmsHomeViewsCount();
    $toTitleTextEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $title = trim((string)($item['title'] ?? ''));
            $text = trim((string)($item['text'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }
            $rows[] = $title . ($text !== '' ? "\n" . $text : '');
        }
        return implode("\n\n", $rows);
    };
    $data = [];
    if (is_array($page) && !empty($page['data_json'])) {
        $decoded = json_decode((string)$page['data_json'], true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
    if (empty($data['home_seo_focus_keyword'])) {
        $data['home_seo_focus_keyword'] = 'immobilier {{city}}';
    }
    if (empty($data['home_seo_secondary_keywords'])) {
        $data['home_seo_secondary_keywords'] = 'estimation immobiliere {{city}}, vente immobiliere {{city}}, achat immobilier {{city}}';
    }
    if (empty($data['home_seo_semantic_terms'])) {
        $data['home_seo_semantic_terms'] = 'notaire, mandat, compromis, acquereur, vendeur';
    }
    ?>
    <?php require __DIR__ . '/cms_shared_styles.inc.php'; ?>

    <section class="cms-wrap">
        <header class="cms-hero">
            <h1>CMS Pages</h1>
            <p>Textes et gabarits : pages front (niveau 1), puis erreurs, maintenance et remerciements (niveau 2).</p>
        </header>

        <?php if ($action !== 'edit'): ?>
            <?php
            $tierLists = cms_registry_split_by_tier($pageTextRegistry);
            $renderRegistryRow = static function (string $pkey, array $pageTextRegistry): void {
                $entry = $pageTextRegistry[$pkey] ?? null;
                if (!is_array($entry)) {
                    return;
                }
                $plab = (string) ($entry['label'] ?? $pkey);
                $tplReg = (string) ($entry['template'] ?? '');
                ?>
                <a href="/admin?module=cms&text_action=edit&cms_page=<?= urlencode($pkey) ?>">
                    <span class="cms-list-main">
                        <span class="cms-list-title"><?= htmlspecialchars($plab, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($tplReg !== ''): ?>
                            <span class="cms-list-template-label">Template utilisé</span>
                            <code class="cms-list-template"><?= htmlspecialchars($tplReg, ENT_QUOTES, 'UTF-8') ?>.php</code>
                        <?php endif; ?>
                    </span>
                    <span class="cms-list-cta">Éditer les textes</span>
                </a>
                <?php
            };
            ?>
            <div class="cms-card">
                <h2>Pages du site — textes &amp; gabarits</h2>
                <p class="cms-card-intro">
                    <?= count($pageTextRegistry) ?> gabarit(s) dans le registre.
                    Régénérer : <code>php scripts/generate_page_content_registry.php</code><br>
                    <strong>Affichage front :</strong> seuls les textes passés par <code>pcms()</code> dans le fichier PHP du gabarit sont pilotés ici.
                    L’accueil utilise l’édition « Accueil » (blocs structurés).                     Les placeholders <code>{{city}}</code>, <code>{{advisor_name}}</code>, etc. sont remplacés à l’affichage.
                    Les zones « contenu » ouvrent un <strong>éditeur riche</strong> (TinyMCE : gras, listes, liens, code source) ; la meta description SEO reste en texte brut.
                </p>

                <div class="cms-list-section">
                    <h3 class="cms-list-section__title">Pages front — niveau 1</h3>
                    <p class="cms-list-section__desc">Navigation principale, contenus publics, tunnels et pages « métier ».</p>
                    <div class="cms-list cms-list--scroll">
                        <?php foreach ($managedPages as $pageSlug => $cfg): ?>
                            <?php $tplManaged = (string) ($cfg['template'] ?? ''); ?>
                            <a href="/admin?module=cms&action=edit&slug=<?= urlencode($pageSlug) ?>">
                                <span class="cms-list-main">
                                    <span class="cms-list-title"><?= htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="cms-list-badge">CMS structuré</span>
                                    <?php if ($tplManaged !== ''): ?>
                                        <span class="cms-list-template-label">Template utilisé</span>
                                        <code class="cms-list-template"><?= htmlspecialchars($tplManaged, ENT_QUOTES, 'UTF-8') ?>.php</code>
                                    <?php endif; ?>
                                </span>
                                <span class="cms-list-cta">Éditer</span>
                            </a>
                        <?php endforeach; ?>
                        <?php foreach ($tierLists['primary'] as $pkey): ?>
                            <?php $renderRegistryRow($pkey, $pageTextRegistry); ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cms-list-section cms-list-section--secondary">
                    <h3 class="cms-list-section__title">Erreurs, maintenance &amp; remerciements — niveau 2</h3>
                    <p class="cms-list-section__desc">404, pages de confirmation, maintenance ou indisponibilité.</p>
                    <?php if ($tierLists['secondary'] === []): ?>
                        <p class="cms-list-section__empty">Aucune page de ce type dans le registre actuel.</p>
                    <?php else: ?>
                        <div class="cms-list cms-list--scroll">
                            <?php foreach ($tierLists['secondary'] as $pkey): ?>
                                <?php $renderRegistryRow($pkey, $pageTextRegistry); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="cms-card">
                <h2>Édition - Accueil</h2>
                <?php
                $dbTemplate = is_array($page) ? ($page['template'] ?? null) : null;
                $homeTemplatePath = (string) ($dbTemplate ?? $managedPages['home']['template'] ?? 'pages/core/home');
                ?>
                <?php if ($notice !== ''): ?><div class="notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <form method="post" class="cms-form">
                    <div class="cms-editor-layout">
                    <div class="cms-editor-main">
                    <section class="cms-section">
                    <h3>Paramètres de page</h3>
                    <div class="cms-meta-grid">
                    <div>
                        <label>Titre page</label>
                        <input type="text" name="title" value="<?= htmlspecialchars((string)($page['title'] ?? 'Accueil'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Statut</label>
                        <select name="status">
                            <?php $status = (string)($page['status'] ?? 'published'); ?>
                            <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Publié</option>
                            <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Brouillon</option>
                        </select>
                    </div>
                    </div>
                    <div>
                        <label>Meta title</label>
                        <input type="text" id="meta-title" name="meta_title" value="<?= htmlspecialchars((string)($page['meta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Meta description</label>
                        <textarea id="meta-description" name="meta_description"><?= htmlspecialchars((string)($page['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    </section>
                    <section class="cms-section">
                    <h3>Contenu de la page</h3>
                    <div>
                        <label>Hero label</label>
                        <input type="text" name="home_hero_label" value="<?= htmlspecialchars((string)($data['home_hero_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Hero title</label>
                        <input type="text" id="home-hero-title" name="home_hero_title" value="<?= htmlspecialchars((string)($data['home_hero_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Hero subtitle</label>
                        <textarea id="home-hero-subtitle" name="home_hero_subtitle"><?= htmlspecialchars((string)($data['home_hero_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA principal - Label</label>
                            <input type="text" name="home_hero_primary_label" value="<?= htmlspecialchars((string)($data['home_hero_primary_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA principal - URL</label>
                            <input type="text" name="home_hero_primary_url" value="<?= htmlspecialchars((string)($data['home_hero_primary_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA secondaire - Label</label>
                            <input type="text" name="home_hero_secondary_label" value="<?= htmlspecialchars((string)($data['home_hero_secondary_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA secondaire - URL</label>
                            <input type="text" name="home_hero_secondary_url" value="<?= htmlspecialchars((string)($data['home_hero_secondary_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <hr>
                    <h3>Piliers Hero (1 ligne = 1 élément)</h3>
                    <textarea name="home_hero_pillars_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_hero_pillars'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <h3>En-tête Services</h3>
                    <div class="row">
                        <div>
                            <label>Label section</label>
                            <input type="text" name="home_services_section_label" value="<?= htmlspecialchars((string)($data['home_services_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>Titre section</label>
                            <input type="text" name="home_services_section_title" value="<?= htmlspecialchars((string)($data['home_services_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>Services (bloc par service: titre puis texte)</h3>
                    <textarea name="home_services_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_services'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <h3>KPI (bloc: valeur puis libellé)</h3>
                    <textarea name="home_stats_text"><?php
                        $statsRows = [];
                        foreach ((array)($data['home_stats'] ?? []) as $stat) {
                            $statsRows[] = trim((string)($stat['value'] ?? '')) . "\n" . trim((string)($stat['label'] ?? ''));
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($statsRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <h3>Problématiques clients (bloc: titre puis texte)</h3>
                    <textarea name="home_reality_cards_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_reality_cards'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <h3>En-tête Réalité</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_reality_section_label" value="<?= htmlspecialchars((string)($data['home_reality_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <textarea name="home_reality_section_title"><?= htmlspecialchars((string)($data['home_reality_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_reality_section_subtitle"><?= htmlspecialchars((string)($data['home_reality_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Comparaison - Avec accompagnement</h3>
                    <div>
                        <label>Label section comparaison</label>
                        <input type="text" name="home_comparison_section_label" value="<?= htmlspecialchars((string)($data['home_comparison_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section comparaison</label>
                        <input type="text" name="home_comparison_section_title" value="<?= htmlspecialchars((string)($data['home_comparison_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section comparaison</label>
                        <textarea name="home_comparison_section_subtitle"><?= htmlspecialchars((string)($data['home_comparison_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label>Tag</label>
                        <input type="text" name="home_comparison_with_tag" value="<?= htmlspecialchars((string)($data['home_comparison']['with']['tag'] ?? 'Avec accompagnement'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre</label>
                        <input type="text" name="home_comparison_with_title" value="<?= htmlspecialchars((string)($data['home_comparison']['with']['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Points (1 ligne = 1 point)</label>
                        <textarea name="home_comparison_with_items_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_comparison']['with']['items'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Comparaison - Sans accompagnement</h3>
                    <div>
                        <label>Tag</label>
                        <input type="text" name="home_comparison_without_tag" value="<?= htmlspecialchars((string)($data['home_comparison']['without']['tag'] ?? 'Sans accompagnement'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre</label>
                        <input type="text" name="home_comparison_without_title" value="<?= htmlspecialchars((string)($data['home_comparison']['without']['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Points (1 ligne = 1 point)</label>
                        <textarea name="home_comparison_without_items_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_comparison']['without']['items'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Conseiller</h3>
                    <div>
                        <label>Label section conseiller</label>
                        <input type="text" name="home_about_section_label" value="<?= htmlspecialchars((string)($data['home_about_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section conseiller</label>
                        <input type="text" name="home_about_title" value="<?= htmlspecialchars((string)($data['home_about_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Texte section conseiller</label>
                        <textarea name="home_about_text"><?= htmlspecialchars((string)($data['home_about_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label>Bénéfices conseiller (1 ligne = 1 bénéfice)</label>
                        <textarea name="home_about_benefits_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_about_benefits'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="row">
                        <div>
                            <label>Bouton conseiller - Label</label>
                            <input type="text" name="home_about_cta_label" value="<?= htmlspecialchars((string)($data['home_about_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>Bouton conseiller - URL</label>
                            <input type="text" name="home_about_cta_url" value="<?= htmlspecialchars((string)($data['home_about_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Méthode</h3>
                    <div>
                        <label>Label section méthode</label>
                        <input type="text" name="home_method_section_label" value="<?= htmlspecialchars((string)($data['home_method_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section méthode</label>
                        <input type="text" name="home_method_section_title" value="<?= htmlspecialchars((string)($data['home_method_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section méthode</label>
                        <textarea name="home_method_section_subtitle"><?= htmlspecialchars((string)($data['home_method_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Méthode (bloc: titre puis texte, numérotation automatique)</h3>
                    <textarea name="home_steps_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_steps'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA méthode principal - Label</label>
                            <input type="text" name="home_method_primary_cta_label" value="<?= htmlspecialchars((string)($data['home_method_primary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA méthode principal - URL</label>
                            <input type="text" name="home_method_primary_cta_url" value="<?= htmlspecialchars((string)($data['home_method_primary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA méthode secondaire - Label</label>
                            <input type="text" name="home_method_secondary_cta_label" value="<?= htmlspecialchars((string)($data['home_method_secondary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA méthode secondaire - URL</label>
                            <input type="text" name="home_method_secondary_cta_url" value="<?= htmlspecialchars((string)($data['home_method_secondary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Témoignages</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_testimonials_section_label" value="<?= htmlspecialchars((string)($data['home_testimonials_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_testimonials_section_title" value="<?= htmlspecialchars((string)($data['home_testimonials_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <h3>Témoignages (bloc: "★★★★★ | Auteur" puis texte)</h3>
                    <textarea name="home_testimonials_text"><?php
                        $testRows = [];
                        foreach ((array)($data['home_testimonials'] ?? []) as $t) {
                            $header = trim((string)($t['stars'] ?? '★★★★★')) . ' | ' . trim((string)($t['author'] ?? ''));
                            $text = trim((string)($t['text'] ?? ''));
                            $testRows[] = trim($header) . ($text !== '' ? "\n" . $text : '');
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($testRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA témoignages - Label</label>
                            <input type="text" name="home_testimonials_cta_label" value="<?= htmlspecialchars((string)($data['home_testimonials_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA témoignages - URL</label>
                            <input type="text" name="home_testimonials_cta_url" value="<?= htmlspecialchars((string)($data['home_testimonials_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Biens</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_featured_section_label" value="<?= htmlspecialchars((string)($data['home_featured_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_featured_section_title" value="<?= htmlspecialchars((string)($data['home_featured_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_featured_section_subtitle"><?= htmlspecialchars((string)($data['home_featured_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Biens sélectionnés (bloc: titre puis prix/texte)</h3>
                    <textarea name="featured_properties_text"><?php
                        $propertyRows = [];
                        foreach ((array)($data['featured_properties'] ?? []) as $property) {
                            $propertyRows[] = trim((string)($property['title'] ?? '')) . "\n" . trim((string)($property['price'] ?? ''));
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($propertyRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA carte bien - Label</label>
                            <input type="text" name="home_featured_item_cta_label" value="<?= htmlspecialchars((string)($data['home_featured_item_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA carte bien - URL</label>
                            <input type="text" name="home_featured_item_cta_url" value="<?= htmlspecialchars((string)($data['home_featured_item_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA section biens - Label</label>
                            <input type="text" name="home_featured_section_cta_label" value="<?= htmlspecialchars((string)($data['home_featured_section_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA section biens - URL</label>
                            <input type="text" name="home_featured_section_cta_url" value="<?= htmlspecialchars((string)($data['home_featured_section_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Marché local</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_market_section_label" value="<?= htmlspecialchars((string)($data['home_market_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_market_section_title" value="<?= htmlspecialchars((string)($data['home_market_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_market_section_subtitle"><?= htmlspecialchars((string)($data['home_market_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Marché local (bloc: titre puis texte)</h3>
                    <textarea name="home_market_cards_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_market_cards'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA marché - Label</label>
                            <input type="text" name="home_market_cta_label" value="<?= htmlspecialchars((string)($data['home_market_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA marché - URL</label>
                            <input type="text" name="home_market_cta_url" value="<?= htmlspecialchars((string)($data['home_market_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Guide vendre</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_sell_section_label" value="<?= htmlspecialchars((string)($data['home_sell_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_sell_section_title" value="<?= htmlspecialchars((string)($data['home_sell_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_sell_section_subtitle"><?= htmlspecialchars((string)($data['home_sell_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Guide vendre (bloc: titre puis texte)</h3>
                    <textarea name="home_sell_guide_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_sell_guide'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA guide - Label</label>
                            <input type="text" name="home_sell_cta_label" value="<?= htmlspecialchars((string)($data['home_sell_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA guide - URL</label>
                            <input type="text" name="home_sell_cta_url" value="<?= htmlspecialchars((string)($data['home_sell_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête FAQ</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_faq_section_label" value="<?= htmlspecialchars((string)($data['home_faq_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_faq_section_title" value="<?= htmlspecialchars((string)($data['home_faq_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_faq_section_subtitle"><?= htmlspecialchars((string)($data['home_faq_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>FAQ (bloc: question puis réponse)</h3>
                    <textarea name="home_faq_text"><?php
                        $faqRows = [];
                        foreach ((array)($data['home_faq'] ?? []) as $faq) {
                            $faqRows[] = trim((string)($faq['question'] ?? '')) . "\n" . trim((string)($faq['answer'] ?? ''));
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($faqRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <h3>CTA final</h3>
                    <div>
                        <label>Titre CTA final</label>
                        <input type="text" name="home_final_cta_title" value="<?= htmlspecialchars((string)($data['home_final_cta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Texte CTA final</label>
                        <textarea name="home_final_cta_text"><?= htmlspecialchars((string)($data['home_final_cta_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 1 - Label</label>
                            <input type="text" name="home_final_primary_cta_label" value="<?= htmlspecialchars((string)($data['home_final_primary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 1 - URL</label>
                            <input type="text" name="home_final_primary_cta_url" value="<?= htmlspecialchars((string)($data['home_final_primary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 2 - Label</label>
                            <input type="text" name="home_final_secondary_cta_label" value="<?= htmlspecialchars((string)($data['home_final_secondary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 2 - URL</label>
                            <input type="text" name="home_final_secondary_cta_url" value="<?= htmlspecialchars((string)($data['home_final_secondary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 3 - Label</label>
                            <input type="text" name="home_final_third_cta_label" value="<?= htmlspecialchars((string)($data['home_final_third_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 3 - URL</label>
                            <input type="text" name="home_final_third_cta_url" value="<?= htmlspecialchars((string)($data['home_final_third_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 4 - Label</label>
                            <input type="text" name="home_final_fourth_cta_label" value="<?= htmlspecialchars((string)($data['home_final_fourth_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 4 - URL</label>
                            <input type="text" name="home_final_fourth_cta_url" value="<?= htmlspecialchars((string)($data['home_final_fourth_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 5 - Label</label>
                            <input type="text" name="home_final_fifth_cta_label" value="<?= htmlspecialchars((string)($data['home_final_fifth_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 5 - URL</label>
                            <input type="text" name="home_final_fifth_cta_url" value="<?= htmlspecialchars((string)($data['home_final_fifth_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="cms-actions">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="submit" name="empty_template" value="1" class="btn btn-light" onclick="return confirm('Vider tout le contenu de cette page CMS ?')">Vider le contenu (template)</button>
                        <a class="btn btn-light" href="/admin?module=cms">Retour liste</a>
                    </div>
                    </section>
                    </div>
                    <div class="cms-editor-rail">
                        <aside class="cms-seo-panel" id="cms-seo-panel" aria-labelledby="cms-seo-heading">
                            <div class="cms-seo-panel__head">
                                <h3 id="cms-seo-heading">Assistant SEO</h3>
                                <span class="cms-seo-panel__badge">Live</span>
                            </div>
                            <div class="cms-seo-score">
                                <div class="cms-seo-score-badge" id="seo-score-badge">0</div>
                                <div class="cms-seo-score-text">
                                    <span class="cms-seo-score-label">Score</span>
                                    <strong id="seo-score-label">À optimiser</strong>
                                </div>
                            </div>
                            <div class="cms-seo-metrics">
                                <div class="cms-seo-metric"><span>Mots (hero)</span><strong id="seo-hero-words">0</strong></div>
                                <div class="cms-seo-metric"><span>Meta title</span><strong id="seo-title-count">0</strong></div>
                                <div class="cms-seo-metric"><span>Meta description</span><strong id="seo-description-count">0</strong></div>
                                <div class="cms-seo-metric"><span>Visites page</span><strong><?= (int)$homeViewsCount ?></strong></div>
                                <div class="cms-seo-metric cms-seo-metric--full">
                                    <span>Modèle utilisé (gabarit)</span>
                                    <strong class="cms-seo-metric-path"><?= htmlspecialchars($homeTemplatePath, ENT_QUOTES, 'UTF-8') ?>.php</strong>
                                    <span class="cms-seo-metric-sub">Rendu depuis <code>public/</code> — page d’accueil.</span>
                                </div>
                            </div>
                            <div class="cms-seo-field">
                                <label for="home-seo-focus-keyword">Mot-clé principal</label>
                                <input type="text" id="home-seo-focus-keyword" name="home_seo_focus_keyword" maxlength="70" value="<?= htmlspecialchars((string)($data['home_seo_focus_keyword'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex. immobilier {{city}}">
                            </div>
                            <div class="cms-seo-field">
                                <label for="home-seo-secondary-keywords">Mots-clés secondaires</label>
                                <input type="text" id="home-seo-secondary-keywords" name="home_seo_secondary_keywords" maxlength="350" value="<?= htmlspecialchars((string)($data['home_seo_secondary_keywords'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="estimation, achat, vente">
                                <span class="cms-field-hint">Séparés par des virgules</span>
                            </div>
                            <div class="cms-seo-field">
                                <label for="home-seo-semantic-terms">Champ sémantique</label>
                                <input type="text" id="home-seo-semantic-terms" name="home_seo_semantic_terms" maxlength="350" value="<?= htmlspecialchars((string)($data['home_seo_semantic_terms'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="notaire, mandat, compromis">
                                <span class="cms-field-hint">Termes à faire apparaître dans le corps</span>
                            </div>
                            <ul class="cms-seo-checklist" id="seo-checklist" role="list"></ul>
                            <p class="cms-seo-help">Analyse en direct : meta title, meta description, H1 et couverture des mots-clés.</p>
                        </aside>
                    </div>
                    </div>
                </form>
                <datalist id="cms-link-suggestions">
                    <option value="/"></option>
                    <option value="/contact"></option>
                    <option value="/biens"></option>
                    <option value="/estimation-gratuite"></option>
                    <option value="/avis-de-valeur"></option>
                    <option value="/avis-clients"></option>
                    <option value="/a-propos"></option>
                    <option value="/secteurs"></option>
                    <option value="/guide-offert"></option>
                    <option value="/prendre-rendez-vous"></option>
                </datalist>
            </div>
        <?php endif; ?>
    </section>
    <script>
    (function () {
        var metaTitle = document.getElementById('meta-title');
        var metaDescription = document.getElementById('meta-description');
        var heroTitle = document.getElementById('home-hero-title');
        var heroSubtitle = document.getElementById('home-hero-subtitle');
        var focusKeyword = document.getElementById('home-seo-focus-keyword');
        var secondaryKeywords = document.getElementById('home-seo-secondary-keywords');
        var semanticTerms = document.getElementById('home-seo-semantic-terms');
        var checklist = document.getElementById('seo-checklist');
        var badge = document.getElementById('seo-score-badge');
        var scoreLabel = document.getElementById('seo-score-label');

        if (!metaTitle || !metaDescription || !heroTitle || !focusKeyword || !checklist || !badge || !scoreLabel) {
            return;
        }

        function normalize(value) {
            return (value || '').toLowerCase().trim();
        }

        function splitKeywords(value) {
            return String(value || '')
                .split(',')
                .map(function (x) { return normalize(x); })
                .filter(function (x) { return x.length > 0; });
        }

        function containsKeyword(text, keyword) {
            if (!keyword) return false;
            return normalize(text).indexOf(keyword) !== -1;
        }

        function buildItem(label, ok) {
            return '<li class="' + (ok ? 'ok' : 'bad') + '">' + (ok ? 'OK - ' : 'A corriger - ') + label + '</li>';
        }

        function countWords(value) {
            var text = String(value || '').trim();
            if (!text) return 0;
            return text.split(/\s+/).filter(Boolean).length;
        }

        function refreshSeo() {
            var mt = metaTitle.value || '';
            var md = metaDescription.value || '';
            var h1 = heroTitle.value || '';
            var hs = heroSubtitle ? heroSubtitle.value : '';
            var bodyText = [h1, heroSubtitle ? heroSubtitle.value : '', md].join(' ');
            var focus = normalize(focusKeyword.value);
            var secondary = splitKeywords(secondaryKeywords ? secondaryKeywords.value : '');
            var semantic = splitKeywords(semanticTerms ? semanticTerms.value : '');

            var checks = [];
            var score = 0;

            var titleCounter = document.getElementById('seo-title-count');
            var descriptionCounter = document.getElementById('seo-description-count');
            var heroWordsCounter = document.getElementById('seo-hero-words');
            if (titleCounter) titleCounter.textContent = String(mt.length);
            if (descriptionCounter) descriptionCounter.textContent = String(md.length);
            if (heroWordsCounter) heroWordsCounter.textContent = String(countWords(h1 + ' ' + hs));

            var mtOk = mt.length >= 50 && mt.length <= 60;
            checks.push(buildItem('Meta title entre 50 et 60 caracteres (' + mt.length + ')', mtOk));
            if (mtOk) score += 20;

            var mdOk = md.length >= 120 && md.length <= 160;
            checks.push(buildItem('Meta description entre 120 et 160 caracteres (' + md.length + ')', mdOk));
            if (mdOk) score += 20;

            var focusInH1 = containsKeyword(h1, focus);
            checks.push(buildItem('Mot-cle principal present dans le H1', focusInH1));
            if (focusInH1) score += 15;

            var focusInMetaTitle = containsKeyword(mt, focus);
            checks.push(buildItem('Mot-cle principal present dans le meta title', focusInMetaTitle));
            if (focusInMetaTitle) score += 15;

            var focusInMetaDescription = containsKeyword(md, focus);
            checks.push(buildItem('Mot-cle principal present dans la meta description', focusInMetaDescription));
            if (focusInMetaDescription) score += 10;

            var secondaryHit = 0;
            secondary.forEach(function (k) {
                if (containsKeyword(bodyText, k)) secondaryHit += 1;
            });
            var secondaryRatio = secondary.length > 0 ? secondaryHit / secondary.length : 1;
            var secondaryOk = secondaryRatio >= 0.5;
            checks.push(buildItem('Au moins 50% des mots-cles secondaires trouves', secondaryOk));
            if (secondaryOk) score += 10;

            var semanticHit = 0;
            semantic.forEach(function (k) {
                if (containsKeyword(bodyText, k)) semanticHit += 1;
            });
            var semanticRatio = semantic.length > 0 ? semanticHit / semantic.length : 1;
            var semanticOk = semanticRatio >= 0.4;
            checks.push(buildItem('Couverture semantique >= 40%', semanticOk));
            if (semanticOk) score += 10;

            checklist.innerHTML = checks.join('');
            badge.textContent = String(score);
            badge.className = 'cms-seo-score-badge';
            if (score >= 80) {
                badge.classList.add('is-good');
                scoreLabel.textContent = 'Excellent';
            } else if (score >= 60) {
                badge.classList.add('is-warn');
                scoreLabel.textContent = 'Bon';
            } else {
                badge.classList.add('is-bad');
                scoreLabel.textContent = 'À optimiser';
            }
        }

        [metaTitle, metaDescription, heroTitle, heroSubtitle, focusKeyword, secondaryKeywords, semanticTerms].forEach(function (el) {
            if (!el) return;
            el.addEventListener('input', refreshSeo);
        });

        Array.prototype.forEach.call(document.querySelectorAll('input[name$=\"_url\"]'), function (urlInput) {
            urlInput.setAttribute('list', 'cms-link-suggestions');
            urlInput.setAttribute('autocomplete', 'off');
        });

        refreshSeo();
    })();
    </script>
    <?php
}

cms_admin_handle_page_text_post_early();
