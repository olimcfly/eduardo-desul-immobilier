<?php

declare(strict_types=1);

$site_id = 1;
$article = ['id' => 0, 'title' => '', 'h1' => '', 'slug' => '', 'status' => 'draft', 'content_html' => '', 'meta_title' => '', 'meta_desc' => '', 'cover_image' => '', 'article_type' => 'article'];
$isNew = $action === 'new';

// Charger l'article existant
if (!$isNew && $id > 0) {
    try {
        $stmt = db()->prepare('SELECT id, title, h1, slug, status, content_html, meta_title, meta_desc, cover_image, article_type, published_at FROM seo_articles_plan WHERE id = ? AND site_id = ?');
        $stmt->execute([$id, $site_id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC) ?: $article;
    } catch (Throwable) {}
}

// Sauvegarder l'article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_article'])) {
    $title = trim((string) ($_POST['title'] ?? ''));
    $h1 = trim((string) ($_POST['h1'] ?? $title));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $status = in_array($_POST['status'] ?? 'draft', ['published', 'draft', 'scheduled', 'archived'], true) ? $_POST['status'] : 'draft';
    $content = $_POST['content_html'] ?? '';
    $meta_title = trim((string) ($_POST['meta_title'] ?? $title));
    $meta_desc = trim((string) ($_POST['meta_desc'] ?? ''));
    $cover_image = trim((string) ($_POST['cover_image'] ?? ''));
    $article_type = trim((string) ($_POST['article_type'] ?? 'article'));

    if (empty($title) || empty($slug)) {
        $error = 'Le titre et le slug sont obligatoires';
    } else {
        try {
            if ($isNew || $id === 0) {
                $stmt = db()->prepare('INSERT INTO seo_articles_plan (site_id, title, h1, slug, status, content_html, meta_title, meta_desc, cover_image, article_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                $stmt->execute([$site_id, $title, $h1, $slug, $status, $content, $meta_title, $meta_desc, $cover_image, $article_type]);
                $success = 'Article créé avec succès';
                $id = (int) db()->lastInsertId();
                $isNew = false;
                header('Location: /admin?module=blog&action=edit&id=' . $id, false, 303);
                exit;
            } else {
                $stmt = db()->prepare('UPDATE seo_articles_plan SET title=?, h1=?, slug=?, status=?, content_html=?, meta_title=?, meta_desc=?, cover_image=?, article_type=?, updated_at=NOW() WHERE id=? AND site_id=?');
                $stmt->execute([$title, $h1, $slug, $status, $content, $meta_title, $meta_desc, $cover_image, $article_type, $id, $site_id]);
                $success = 'Article mis à jour avec succès';
                $article = array_merge($article, compact('title', 'h1', 'slug', 'status', 'content', 'meta_title', 'meta_desc', 'cover_image', 'article_type'));
            }
        } catch (Throwable $e) {
            $error = 'Erreur: ' . $e->getMessage();
        }
    }
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
