<?php

declare(strict_types=1);

if (!function_exists('db')) {
    require_once __DIR__ . '/../../core/config/database.php';
}
require_once __DIR__ . '/../../core/helpers/articles.php';

$pageTitle = 'Gestion des articles';
$pageDescription = 'Créez et gérez les articles de votre blog';

$action = preg_replace('/[^a-z_]/', '', (string) ($_GET['action'] ?? 'index'));
$id = (int) ($_GET['id'] ?? 0);
$site_id = 1;

function blog_admin_status(string $status): string
{
    $status = $status === 'scheduled' ? 'planned' : $status;
    return in_array($status, ['published', 'draft', 'planned', 'approved', 'generated', 'archived'], true) ? $status : 'draft';
}

function blog_admin_article_payload(array $post, array $current = []): array
{
    $title = trim((string) ($post['title'] ?? ''));
    $slug = blog_slugify((string) ($post['slug'] ?? $title));
    $content = (string) ($post['content_html'] ?? '');
    $excerpt = trim((string) ($post['excerpt'] ?? ''));
    $metaDesc = trim((string) ($post['meta_desc'] ?? ''));
    $focusKeyword = trim((string) ($post['focus_keyword'] ?? ''));
    $secondaryKeywords = blog_decode_list($post['secondary_keywords'] ?? '');
    $status = blog_admin_status((string) ($post['status'] ?? 'draft'));
    $wordCount = blog_word_count($content);
    $article = [
        'title' => $title,
        'h1' => trim((string) ($post['h1'] ?? $title)),
        'slug' => $slug,
        'status' => $status,
        'content_html' => $content,
        'content' => $content,
        'excerpt' => $excerpt !== '' ? $excerpt : ($metaDesc !== '' ? $metaDesc : blog_excerpt($content)),
        'meta_title' => trim((string) ($post['meta_title'] ?? $title)),
        'seo_title' => trim((string) ($post['meta_title'] ?? $title)),
        'meta_desc' => $metaDesc,
        'meta_description' => $metaDesc,
        'cover_image' => trim((string) ($post['featured_image'] ?? $post['cover_image'] ?? '')),
        'featured_image' => trim((string) ($post['featured_image'] ?? $post['cover_image'] ?? '')),
        'article_type' => in_array(($post['article_type'] ?? 'satellite'), ['pilier', 'satellite', 'intention', 'support'], true) ? (string) $post['article_type'] : 'satellite',
        'topic_family' => trim((string) ($post['topic_family'] ?? '')),
        'category_id' => (int) ($post['category_id'] ?? 0) ?: null,
        'author_name' => trim((string) ($post['author_name'] ?? ADVISOR_NAME)),
        'focus_keyword' => $focusKeyword,
        'primary_keyword' => $focusKeyword,
        'secondary_keywords' => $secondaryKeywords,
        'secondary_keywords_json' => $secondaryKeywords !== [] ? json_encode($secondaryKeywords, JSON_UNESCAPED_UNICODE) : null,
        'canonical_url' => trim((string) ($post['canonical_url'] ?? '')),
        'robots_index' => isset($post['robots_index']) ? 1 : 0,
        'robots_follow' => isset($post['robots_follow']) ? 1 : 0,
        'og_title' => trim((string) ($post['og_title'] ?? '')),
        'og_description' => trim((string) ($post['og_description'] ?? '')),
        'og_image' => trim((string) ($post['og_image'] ?? '')),
        'schema_type' => trim((string) ($post['schema_type'] ?? 'Article')) ?: 'Article',
        'faq_json' => trim((string) ($post['faq_json'] ?? '')),
        'word_count' => $wordCount,
        'reading_time' => max(1, (int) ceil(max(1, $wordCount) / 200)),
    ];

    $seo = blog_seo_analysis($article);
    $article['seo_score'] = $seo['score'];
    $article['seo_checks_json'] = json_encode($seo['checks'], JSON_UNESCAPED_UNICODE);

    if ($article['faq_json'] !== '' && json_decode($article['faq_json'], true) === null) {
        $article['faq_json'] = (string) ($current['faq_json'] ?? '');
    }

    return $article;
}

// Gestion de la sauvegarde d'article (AVANT le layout)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_article'])) {
    $isNew = $action === 'new';
    $current = [];
    if (!$isNew && $id > 0) {
        $stmt = db()->prepare('SELECT * FROM seo_articles_plan WHERE id = ? AND site_id = ?');
        $stmt->execute([$id, $site_id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    $payload = blog_admin_article_payload($_POST, $current);

    if ($payload['title'] === '' || $payload['slug'] === '') {
        $_GET['error'] = 'Le titre et le slug sont obligatoires';
    } else {
        try {
            if ($isNew || $id === 0) {
                $stmt = db()->prepare('
                    INSERT INTO seo_articles_plan
                    (site_id, cluster_id, category_id, title, h1, slug, status, content_brief, content_html,
                     meta_title, meta_desc, canonical_url, robots_index, robots_follow, og_title, og_description,
                     og_image, schema_type, cover_image, author_name, article_type, topic_family, primary_keyword,
                     secondary_keywords_json, word_count, reading_time, seo_score, seo_checks_json, faq_json,
                     published_at, created_at, updated_at)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                     IF(? = "published", NOW(), NULL), NOW(), NOW())
                ');
                $stmt->execute([
                    $site_id, 1, $payload['category_id'], $payload['title'], $payload['h1'], $payload['slug'],
                    $payload['status'], $payload['excerpt'], $payload['content_html'], $payload['meta_title'],
                    $payload['meta_desc'], $payload['canonical_url'], $payload['robots_index'], $payload['robots_follow'],
                    $payload['og_title'], $payload['og_description'], $payload['og_image'], $payload['schema_type'],
                    $payload['cover_image'], $payload['author_name'], $payload['article_type'], $payload['topic_family'],
                    $payload['primary_keyword'], $payload['secondary_keywords_json'], $payload['word_count'],
                    $payload['reading_time'], $payload['seo_score'], $payload['seo_checks_json'], $payload['faq_json'],
                    $payload['status'],
                ]);
                $newId = (int) db()->lastInsertId();
                header('Location: /admin?module=blog&action=edit&id=' . $newId . '&success=created', false, 303);
                exit;
            } else {
                $stmt = db()->prepare('
                    UPDATE seo_articles_plan SET
                        category_id=?, title=?, h1=?, slug=?, status=?, content_brief=?, content_html=?,
                        meta_title=?, meta_desc=?, canonical_url=?, robots_index=?, robots_follow=?,
                        og_title=?, og_description=?, og_image=?, schema_type=?, cover_image=?, author_name=?,
                        article_type=?, topic_family=?, primary_keyword=?, secondary_keywords_json=?,
                        word_count=?, reading_time=?, seo_score=?, seo_checks_json=?, faq_json=?,
                        published_at = CASE WHEN ? = "published" AND published_at IS NULL THEN NOW() ELSE published_at END,
                        updated_at=NOW()
                    WHERE id=? AND site_id=?
                ');
                $stmt->execute([
                    $payload['category_id'], $payload['title'], $payload['h1'], $payload['slug'], $payload['status'],
                    $payload['excerpt'], $payload['content_html'], $payload['meta_title'], $payload['meta_desc'],
                    $payload['canonical_url'], $payload['robots_index'], $payload['robots_follow'], $payload['og_title'],
                    $payload['og_description'], $payload['og_image'], $payload['schema_type'], $payload['cover_image'],
                    $payload['author_name'], $payload['article_type'], $payload['topic_family'], $payload['primary_keyword'],
                    $payload['secondary_keywords_json'], $payload['word_count'], $payload['reading_time'], $payload['seo_score'],
                    $payload['seo_checks_json'], $payload['faq_json'], $payload['status'], $id, $site_id,
                ]);
                $_GET['success'] = 'updated';
            }
        } catch (Throwable $e) {
            $_GET['error'] = 'Erreur: ' . $e->getMessage();
        }
    }
}

// Actions rapides non destructives
if (in_array($action, ['delete', 'archive', 'publish', 'unpublish'], true) && $id > 0) {
    try {
        if ($action === 'publish') {
            $stmt = db()->prepare('UPDATE seo_articles_plan SET status = "published", published_at = COALESCE(published_at, NOW()), updated_at = NOW() WHERE id = ? AND site_id = ?');
            $stmt->execute([$id, $site_id]);
            $_GET['success'] = 'published';
        } elseif ($action === 'unpublish') {
            $stmt = db()->prepare('UPDATE seo_articles_plan SET status = "draft", updated_at = NOW() WHERE id = ? AND site_id = ?');
            $stmt->execute([$id, $site_id]);
            $_GET['success'] = 'unpublished';
        } else {
            $stmt = db()->prepare('UPDATE seo_articles_plan SET status = "archived", updated_at = NOW() WHERE id = ? AND site_id = ?');
            $stmt->execute([$id, $site_id]);
            $_GET['success'] = 'archived';
        }
    } catch (Throwable) {}
}

function renderContent(): void
{
    global $action, $id, $site_id;

    // Afficher le formulaire de création/édition
    if ($action === 'edit' || $action === 'new') {
        $isNew = $action === 'new';
        $article = ['id' => 0, 'title' => '', 'h1' => '', 'slug' => '', 'status' => 'draft', 'content_html' => '', 'meta_title' => '', 'meta_desc' => '', 'cover_image' => '', 'article_type' => 'article'];
        $success = isset($_GET['success']) && $_GET['success'] === 'created' ? 'Article créé avec succès' : (isset($_GET['success']) && $_GET['success'] === 'updated' ? 'Article mis à jour avec succès' : '');
        $error = isset($_GET['error']) ? $_GET['error'] : '';

        if (!$isNew && $id > 0) {
            try {
                $stmt = db()->prepare('SELECT id, title, h1, slug, status, content_html, meta_title, meta_desc, cover_image, article_type, published_at FROM seo_articles_plan WHERE id = ? AND site_id = ?');
                $stmt->execute([$id, $site_id]);
                $article = $stmt->fetch(PDO::FETCH_ASSOC) ?: $article;
            } catch (Throwable) {}
        }
        ?>

        <style>
            .edit-page{max-width:900px;margin:0 auto;display:grid;gap:22px}
            .edit-hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);border-radius:16px;padding:24px 20px;color:#fff}
            .edit-hero h1{margin:0;font-size:2rem;color:#fff}
            .edit-form{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:24px;display:grid;gap:18px}
            .edit-group{display:grid;gap:6px}
            .edit-group label{font-weight:600;color:#1f2937;font-size:.9rem}
            .edit-group input,.edit-group textarea,.edit-group select{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-family:inherit;font-size:.9rem}
            .edit-group textarea{resize:vertical;min-height:200px;font-family:'Monaco','Courier New',monospace}
            .edit-group input:focus,.edit-group textarea:focus,.edit-group select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
            .edit-row{display:grid;grid-template-columns:1fr 1fr;gap:18px}
            @media (max-width:640px){.edit-row{grid-template-columns:1fr}}
            .edit-actions{display:flex;gap:12px;justify-content:flex-start;padding-top:12px}
            .edit-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;border:none;font-weight:600;cursor:pointer;transition:all .18s ease;text-decoration:none}
            .edit-btn-primary{background:#c9a84c;color:#10253c}
            .edit-btn-primary:hover{background:#b8962d}
            .edit-btn-secondary{background:#e5e7eb;color:#374151}
            .edit-btn-secondary:hover{background:#d1d5db}
            .edit-alert{padding:12px 16px;border-radius:8px;margin-bottom:16px}
            .edit-alert.success{background:#dcfce7;color:#166534;border:1px solid #86efac}
            .edit-alert.error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
        </style>

        <div class="edit-page">
            <header class="edit-hero">
                <h1><?= $isNew ? 'Nouvel article' : 'Éditer l\'article' ?></h1>
                <p style="margin:8px 0 0;color:rgba(255,255,255,.78)">Créez et gérez le contenu de vos articles blog</p>
            </header>

            <?php if (!empty($success)): ?>
                <div class="edit-alert success"><i class="fas fa-check"></i> <?= e($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="edit-alert error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="edit-form">
                <input type="hidden" name="save_article" value="1">

                <div class="edit-row">
                    <div class="edit-group">
                        <label for="title">Titre de l'article *</label>
                        <input type="text" id="title" name="title" value="<?= e($article['title'] ?? '') ?>" required>
                    </div>
                    <div class="edit-group">
                        <label for="slug">Slug (URL) *</label>
                        <input type="text" id="slug" name="slug" value="<?= e($article['slug'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="edit-group">
                    <label for="h1">Titre H1</label>
                    <input type="text" id="h1" name="h1" value="<?= e($article['h1'] ?? '') ?>">
                </div>

                <div class="edit-row">
                    <div class="edit-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="draft" <?= ($article['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                            <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publié</option>
                            <option value="scheduled" <?= ($article['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Planifié</option>
                            <option value="archived" <?= ($article['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archivé</option>
                        </select>
                    </div>
                    <div class="edit-group">
                        <label for="article_type">Type d'article</label>
                        <input type="text" id="article_type" name="article_type" value="<?= e($article['article_type'] ?? 'article') ?>">
                    </div>
                </div>

                <div class="edit-group">
                    <label for="content_html">Contenu HTML</label>
                    <textarea id="content_html" name="content_html"><?= e($article['content_html'] ?? '') ?></textarea>
                </div>

                <div class="edit-row">
                    <div class="edit-group">
                        <label for="meta_title">Titre SEO</label>
                        <input type="text" id="meta_title" name="meta_title" value="<?= e($article['meta_title'] ?? '') ?>" placeholder="Titre pour les moteurs de recherche">
                    </div>
                    <div class="edit-group">
                        <label for="meta_desc">Description SEO</label>
                        <input type="text" id="meta_desc" name="meta_desc" value="<?= e($article['meta_desc'] ?? '') ?>" placeholder="Description pour les moteurs de recherche" maxlength="160">
                    </div>
                </div>

                <div class="edit-group">
                    <label for="cover_image">Image de couverture (URL)</label>
                    <input type="text" id="cover_image" name="cover_image" value="<?= e($article['cover_image'] ?? '') ?>" placeholder="https://exemple.com/image.jpg">
                </div>

                <div class="edit-actions">
                    <button type="submit" class="edit-btn edit-btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="/admin?module=blog" class="edit-btn edit-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
            </form>
        </div>

        <?php
        return;
    }

    // Afficher la liste des articles
    $stats = ['total' => 0, 'published' => 0, 'draft' => 0, 'scheduled' => 0, 'archived' => 0];
    $articles = [];
    $success = isset($_GET['success']) && $_GET['success'] === 'deleted' ? 'Article supprimé avec succès' : '';
    $error = '';

    try {
        $stmt = db()->prepare('SELECT status, COUNT(*) as nb FROM seo_articles_plan WHERE site_id = ? GROUP BY status');
        $stmt->execute([$site_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_KEY_PAIR) as $status => $count) {
            if (isset($stats[$status])) {
                $stats[$status] = (int)$count;
            }
            $stats['total'] += (int)$count;
        }
    } catch (Throwable) {}

    try {
        $stmt = db()->prepare('SELECT id, COALESCE(h1, title) as titre, slug, status, word_count as mots, COALESCE(article_type, "article") as type, updated_at FROM seo_articles_plan WHERE site_id = ? ORDER BY updated_at DESC LIMIT 10');
        $stmt->execute([$site_id]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable) {}
    ?>
    <style>
        .blog-page{display:grid;gap:22px}
        .blog-hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);border-radius:16px;padding:24px 20px;color:#fff;box-shadow:0 4px 20px rgba(15,34,55,.18)}
        .blog-hero h1{margin:0 0 10px;font-size:clamp(24px,4vw,30px);line-height:1.24;color:#fff}
        .blog-hero p{margin:0;color:rgba(255,255,255,.78);font-size:15px;line-height:1.65}
        .blog-toolbar{display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;margin:0 0 1rem}
        .blog-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.85rem;margin-bottom:1.2rem}
        .blog-stat{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:.9rem 1rem}
        .blog-stat strong{font-size:1.4rem;color:#0f172a;display:block}
        .blog-stat small{color:#64748b;font-size:.85rem;display:block;margin-top:.25rem}
        .blog-articles-grid{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:auto}
        .blog-articles-grid table{width:100%;border-collapse:collapse;min-width:900px}
        .blog-articles-grid th,.blog-articles-grid td{padding:.75rem .8rem;border-bottom:1px solid #f1f5f9;text-align:left;font-size:.9rem}
        .blog-articles-grid th{background:#f9fafb;font-weight:600;color:#374151}
        .blog-status{display:inline-block;padding:.2rem .5rem;border-radius:999px;font-size:.76rem;font-weight:700}
        .blog-status.published{background:#dcfce7;color:#166534}
        .blog-status.draft{background:#fef3c7;color:#92400e}
        .blog-status.scheduled{background:#dbeafe;color:#1d4ed8}
        .blog-status.archived{background:#f3f4f6;color:#6b7280}
        .blog-score{display:inline-block;width:40px;height:20px;background:#e5e7eb;border-radius:4px;position:relative;overflow:hidden}
        .blog-score-fill{position:absolute;height:100%;background:#10b981;border-radius:4px}
        .blog-final-cta{background:#fff;border:1px solid #e8edf4;border-radius:14px;padding:1.05rem 1rem;display:grid;gap:.7rem}
        .blog-final-cta h2{margin:0;font-size:1.2rem;color:#111827;font-weight:700}
        .blog-btn{display:inline-flex;align-items:center;gap:.5rem;text-decoration:none;background:#c9a84c;color:#10253c;font-weight:700;border-radius:10px;padding:.58rem .92rem;margin-top:.7rem;border:none;cursor:pointer;transition:background .18s ease}
        .blog-btn:hover{background:#b8962d}
        .blog-alert{padding:12px 16px;border-radius:8px;margin-bottom:16px}
        .blog-alert.success{background:#dcfce7;color:#166534;border:1px solid #86efac}
        .blog-actions{display:flex;gap:8px;font-size:.9rem}
        .blog-actions a{cursor:pointer;color:#2563eb;text-decoration:none;padding:4px 8px;border-radius:4px;transition:background .18s ease}
        .blog-actions a:hover{background:rgba(37,99,235,.1)}
        .blog-actions a.delete{color:#dc2626}
        .blog-actions a.delete:hover{background:rgba(220,38,38,.1)}
        @media (min-width:768px){.blog-hero{padding:2rem 2.1rem}.blog-hero h1{font-size:2rem}}
    </style>

    <section class="blog-page">
        <header class="blog-hero">
            <h1>Gestion du blog</h1>
            <p>Créez, éditez et publiez les articles de votre blog.</p>
        </header>

        <?php if (!empty($success)): ?>
            <div class="blog-alert success"><i class="fas fa-check"></i> <?= e($success) ?></div>
        <?php endif; ?>

        <div class="blog-toolbar">
            <p style="margin:0;color:#64748b">Vous avez <?= $stats['total'] ?> article(s) au total</p>
            <a href="/admin?module=blog&action=new" class="blog-btn"><i class="fas fa-plus"></i> Nouvel article</a>
        </div>

        <div class="blog-stats">
            <div class="blog-stat"><strong><?= $stats['total'] ?></strong><small>Total articles</small></div>
            <div class="blog-stat"><strong><?= $stats['published'] ?></strong><small>Publiés</small></div>
            <div class="blog-stat"><strong><?= $stats['draft'] ?></strong><small>Brouillons</small></div>
            <div class="blog-stat"><strong><?= $stats['scheduled'] ?></strong><small>Planifiés</small></div>
        </div>

        <div class="blog-articles-grid">
            <table>
                <thead><tr><th>Titre</th><th>Type</th><th>Statut</th><th>Mots</th><th>SEO</th><th>Modifié</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!$articles): ?>
                        <tr><td colspan="7" style="text-align:center;padding:2rem;color:#9ca3af;">Aucun article pour le moment</td></tr>
                    <?php else: foreach ($articles as $a): $status = strtolower($a['status'] ?? 'draft'); ?>
                        <tr>
                            <td><strong><?= e($a['titre']) ?></strong></td>
                            <td><?= e($a['type'] ?? 'article') ?></td>
                            <td><span class="blog-status <?= e($status) ?>"><?= e($status) ?></span></td>
                            <td><?= (int)($a['mots'] ?? 0) ?></td>
                            <td><div class="blog-score"><div class="blog-score-fill" style="width:50%"></div></div></td>
                            <td><?= date('d/m/Y', strtotime((string)$a['updated_at'])) ?></td>
                            <td class="blog-actions">
                                <a href="/admin?module=blog&action=edit&id=<?= (int)$a['id'] ?>" title="Éditer"><i class="fas fa-edit"></i></a>
                                <a href="/admin?module=blog&action=delete&id=<?= (int)$a['id'] ?>" class="delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <section class="blog-final-cta">
            <div><h2>Créer un nouvel article</h2><p>Commencez à ajouter du contenu au blog.</p></div>
            <a href="/admin?module=blog&action=new" class="blog-btn"><i class="fas fa-rocket"></i> Créer un article</a>
        </section>
    </section>
    <?php
}
