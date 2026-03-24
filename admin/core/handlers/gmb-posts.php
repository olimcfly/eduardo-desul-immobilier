<?php
/**
 * API Handler: gmb-posts
 * Called via: /admin/api/router.php?module=gmb-posts&action=...
 * Table: gmb_article_posts
 * Gère les posts Google My Business liés aux articles de blog.
 *
 * Actions :
 *   - generate    : Génère un post GMB via IA pour un article
 *   - list        : Liste les posts GMB d'un article
 *   - delete      : Supprime un post GMB
 *   - republish   : Re-publie un post en échec/brouillon sur GMB
 *   - counts      : Retourne les compteurs GMB pour une liste d'articles
 */

$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = CURRENT_ACTION;

// Charger le service
$servicePath = dirname(__DIR__, 2) . '/includes/classes/GmbArticlePostService.php';
if (!file_exists($servicePath)) {
    echo json_encode(['success' => false, 'message' => 'GmbArticlePostService introuvable']);
    exit;
}
require_once $servicePath;

try {
    $service = new GmbArticlePostService($pdo);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur init service : ' . $e->getMessage()]);
    exit;
}

switch ($action) {

    // ── Générer un post GMB pour un article ──────────────────
    case 'generate':
        $articleId = (int)($input['article_id'] ?? 0);
        if (!$articleId) {
            echo json_encode(['success' => false, 'message' => 'article_id requis']);
            break;
        }

        // Charger l'article
        try {
            $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$articleId]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur chargement article']);
            break;
        }

        if (!$article) {
            echo json_encode(['success' => false, 'message' => 'Article introuvable']);
            break;
        }

        $result = $service->generateFromArticle($article);
        echo json_encode($result);
        break;

    // ── Lister les posts GMB d'un article ────────────────────
    case 'list':
        $articleId = (int)($input['article_id'] ?? $_GET['article_id'] ?? 0);
        if (!$articleId) {
            echo json_encode(['success' => false, 'message' => 'article_id requis']);
            break;
        }
        $posts  = $service->getByArticle($articleId);
        $counts = $service->countByArticle($articleId);
        echo json_encode(['success' => true, 'data' => $posts, 'counts' => $counts]);
        break;

    // ── Supprimer un post GMB ────────────────────────────────
    case 'delete':
        $postId = (int)($input['post_id'] ?? 0);
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'post_id requis']);
            break;
        }
        $service->delete($postId);
        echo json_encode(['success' => true, 'message' => 'Post GMB supprimé']);
        break;

    // ── Re-publier un post en échec/brouillon ────────────────
    case 'republish':
        $postId = (int)($input['post_id'] ?? 0);
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'post_id requis']);
            break;
        }
        $result = $service->tryPublish($postId);
        echo json_encode([
            'success'   => true,
            'published' => $result['published'] ?? false,
            'error'     => $result['error'] ?? null,
            'post'      => $service->getById($postId),
        ]);
        break;

    // ── Compteurs batch pour la liste d'articles ─────────────
    case 'counts':
        $articleIds = $input['article_ids'] ?? [];
        if (!is_array($articleIds) || empty($articleIds)) {
            echo json_encode(['success' => true, 'data' => []]);
            break;
        }
        $counts = $service->countByArticles($articleIds);
        echo json_encode(['success' => true, 'data' => $counts]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => "Action '{$action}' non supportée pour gmb-posts"]);
}
