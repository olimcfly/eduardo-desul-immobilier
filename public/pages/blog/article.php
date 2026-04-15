<?php
require_once ROOT_PATH . '/core/helpers/articles.php';

$slug = $GLOBALS['articleSlug'] ?? '';

$row = get_article_by_slug($slug);

if (!$row) {
    http_response_code(404);
    $pageTitle   = 'Article introuvable';
    $pageContent = '<section class="section"><div class="container text-center"><h1 style="font-size:4rem;color:var(--clr-primary)">404</h1><p>Cet article est introuvable.</p><a href="/blog" class="btn btn--primary" style="margin-top:1.5rem">Retour au blog</a></div></section>';
    require ROOT_PATH . '/public/templates/layout.php';
    exit;
}

$titre      = $row['h1'] ?: $row['titre'];
$pageTitle  = ($row['seo_title'] ?: $row['titre']) . ' — Pascal Hamm';
$metaDesc   = $row['meta_desc'] ?: '';
$extraCss   = ['/assets/css/guide.css'];
$extraJs    = ['/assets/js/guide.js'];
$contenu    = $row['contenu'] ?? '';
$dateRaw    = $row['date_publication'] ?: $row['created_at'];
$dateFormat = $dateRaw ? date('d F Y', strtotime($dateRaw)) : '';
$mots       = (int) ($row['mots'] ?? 0);
$lecture    = $mots > 0 ? ceil($mots / 200) . ' min' : null;
$image      = !empty($row['image']) ? $row['image'] : null;
$imageUrl   = $image ? e($image) : '/assets/images/placeholder.php?type=article&label=' . rawurlencode($row['titre']);
?>

<div class="page-header" style="padding-bottom:2rem">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a>
            <a href="/blog">Blog</a>
            <span><?= e($row['type'] === 'pilier' ? 'Guide complet' : 'Article') ?></span>
        </nav>
    </div>
</div>

<?php
$jsonLd = json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'headline'      => $row['titre'],
    'author'        => ['@type' => 'Person', 'name' => 'Pascal Hamm'],
    'datePublished' => $dateRaw,
    'publisher'     => ['@type' => 'Organization', 'name' => APP_NAME, 'url' => APP_URL],
    'url'           => APP_URL . '/blog/' . $slug,
    'description'   => $metaDesc,
    'image'         => $image ? APP_URL . $image : null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<script type="application/ld+json"><?= $jsonLd ?></script>

<section class="section" style="padding-top:2rem">
    <div class="container">
        <!-- Progress bar -->
        <div id="reading-progress" style="position:fixed;top:var(--header-h);left:0;height:3px;background:var(--clr-accent);z-index:99;transition:width .1s;width:0"></div>

        <div class="article-layout">
            <!-- Contenu principal -->
            <div>
                <div class="article-header">
                    <span class="article-card__cat"><?= e($row['type'] === 'pilier' ? 'Guide complet' : 'Article') ?></span>
                    <h1><?= e($titre) ?></h1>
                    <div class="article-meta">
                        <span>✍️ Pascal Hamm</span>
                        <?php if ($dateFormat): ?><span>📅 <?= e($dateFormat) ?></span><?php endif; ?>
                        <?php if ($lecture): ?><span>⏱ <?= e($lecture) ?> de lecture</span><?php endif; ?>
                    </div>
                </div>

                <!-- Image de couverture -->
                <div class="article-cover" style="margin-bottom:2rem;border-radius:var(--radius-lg);overflow:hidden;line-height:0">
                    <img src="<?= $imageUrl ?>"
                         alt="<?= e($titre) ?>"
                         width="800" height="450"
                         style="width:100%;height:auto;object-fit:cover;aspect-ratio:16/9"
                         loading="eager">
                </div>

                <div class="article-content">
                    <?= $contenu ?>
                </div>

                <!-- Partage -->
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding:1.5rem 0;border-top:1px solid var(--clr-border);margin-top:2rem">
                    <span style="font-weight:600;font-size:.875rem">Partager cet article :</span>
                    <button data-share="facebook" class="btn btn--outline btn--sm">Facebook</button>
                    <button data-share="linkedin" class="btn btn--outline btn--sm">LinkedIn</button>
                    <button data-share="copy"     class="btn btn--outline btn--sm">Copier le lien</button>
                </div>

                <!-- CTA article -->
                <div style="background:linear-gradient(135deg,var(--clr-primary),#0f2644);color:white;border-radius:var(--radius-lg);padding:2rem;text-align:center;margin-top:2rem">
                    <h3 style="color:white;margin-bottom:.75rem">Un projet immobilier à Aix-en-Provence ?</h3>
                    <p style="opacity:.8;margin-bottom:1.5rem">Obtenez une estimation gratuite et un conseil personnalisé de Pascal Hamm.</p>
                    <a href="/estimation-gratuite" class="btn btn--accent">Estimation gratuite →</a>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <div class="sidebar-box">
                    <div class="sidebar-box__head">Table des matières</div>
                    <div class="sidebar-box__body">
                        <ul id="toc-list" style="list-style:none;display:flex;flex-direction:column;gap:.5rem;font-size:.875rem"></ul>
                    </div>
                </div>

                <?php
                // Articles récents (sidebar)
                $recents = array_filter(get_articles_list(6), fn($a) => $a['slug'] !== $slug);
                $recents = array_slice(array_values($recents), 0, 3);
                if ($recents):
                ?>
                <div class="sidebar-box">
                    <div class="sidebar-box__head">Articles récents</div>
                    <div class="sidebar-box__body">
                        <?php foreach ($recents as $recent): ?>
                        <a href="/blog/<?= e(rawurlencode($recent['slug'])) ?>" class="recent-post" style="text-decoration:none">
                            <div>
                                <div class="recent-post__title"><?= e($recent['title']) ?></div>
                                <div class="recent-post__date" style="font-size:.75rem;color:var(--clr-text-muted)"><?= e(truncate($recent['excerpt'], 80)) ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem;text-align:center">
                    <h4 style="color:white;margin-bottom:.75rem">Estimation gratuite</h4>
                    <p style="font-size:.8rem;opacity:.8;margin-bottom:1rem">Découvrez la valeur de votre bien en 48h.</p>
                    <a href="/estimation-gratuite" class="btn btn--accent btn--sm btn--full">Estimer mon bien</a>
                </div>
            </aside>
        </div>
    </div>
</section>
