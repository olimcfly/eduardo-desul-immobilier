<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../modules/biens/services/PhotoService.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals(csrfToken(), $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
    exit;
}

$bienId = isset($_POST['bien_id']) ? (int) $_POST['bien_id'] : 0;
if ($bienId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Identifiant du bien invalide.']);
    exit;
}

$action = (string) ($_POST['action'] ?? 'upload');
$service = new PhotoService(db());

try {
    if ($action === 'upload') {
        $uploaded = $service->uploadPhotos($bienId, $_FILES['photos'] ?? []);

        echo json_encode([
            'success' => true,
            'message' => count($uploaded) . ' photo(s) importée(s).',
            'photos' => $service->getPhotos($bienId),
        ]);
        exit;
    }

    if ($action === 'reorder') {
        $photoIds = $_POST['photo_ids'] ?? [];
        if (!is_array($photoIds)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Format de tri invalide.']);
            exit;
        }

        $service->reorderPhotos($bienId, array_map('intval', $photoIds));
        echo json_encode([
            'success' => true,
            'message' => 'Ordre des photos mis à jour.',
            'photos' => $service->getPhotos($bienId),
        ]);
        exit;
    }

    if ($action === 'delete') {
        $photoId = isset($_POST['photo_id']) ? (int) $_POST['photo_id'] : 0;
        if ($photoId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Photo invalide.']);
            exit;
        }

        $service->deletePhoto($photoId);
        echo json_encode([
            'success' => true,
            'message' => 'Photo supprimée.',
            'photos' => $service->getPhotos($bienId),
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du traitement des photos.',
        'details' => APP_DEBUG ? $e->getMessage() : null,
    ]);
}
