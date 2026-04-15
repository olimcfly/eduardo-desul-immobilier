<?php

declare(strict_types=1);

function handleHelpApi(HelpCenterService $service, string $context): void
{
    header('Content-Type: application/json; charset=utf-8');

    $query = trim((string) ($_GET['q'] ?? $_POST['q'] ?? ''));
    $category = trim((string) ($_GET['category'] ?? $_POST['category'] ?? ''));
    $id = trim((string) ($_GET['id'] ?? $_POST['id'] ?? ''));

    if ($id !== '') {
        $article = $service->getArticle($id);
        if ($article === null) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Article introuvable']);
            return;
        }

        $user = Auth::user();
        $service->recordView((int) ($user['id'] ?? 0), $article, $context);
        echo json_encode(['success' => true, 'article' => $article], JSON_UNESCAPED_UNICODE);
        return;
    }

    $articles = $service->searchArticles($query, $category, $context);
    echo json_encode([
        'success' => true,
        'context' => $context,
        'count' => count($articles),
        'articles' => $articles,
    ], JSON_UNESCAPED_UNICODE);
}
