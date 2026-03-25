<?php

require_once __DIR__ . '/services/MarketAnalysisService.php';

class MarketAnalysisController
{
    private MarketAnalysisService $service;
    private int $userId;

    public function __construct(PDO $pdo, int $userId)
    {
        $this->service = new MarketAnalysisService($pdo);
        $this->userId = $userId;
    }

    public function handleRequest(): array
    {
        $action = $_GET['action'] ?? 'list';
        $flash = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->ensureCsrf();
            $postAction = $_POST['action'] ?? '';

            if ($postAction === 'create') {
                $result = $this->service->createDraft($this->userId, $_POST);
                if ($result['success']) {
                    header('Location: ?page=market-analysis&action=show&id=' . (int) $result['analysis_id'] . '&created=1');
                    exit;
                }
                return [
                    'view' => 'form',
                    'errors' => $result['errors'] ?? ['Erreur inconnue'],
                    'old' => $_POST,
                ];
            }

            if ($postAction === 'delete') {
                $analysisId = (int) ($_POST['id'] ?? 0);
                if ($analysisId > 0) {
                    $this->service->deleteForUser($analysisId, $this->userId);
                }
                header('Location: ?page=market-analysis&deleted=1');
                exit;
            }

            $analysisId = (int) ($_POST['id'] ?? 0);
            if ($analysisId > 0) {
                if ($postAction === 'run-analysis') {
                    $result = $this->service->runAnalysis($analysisId, $this->userId);
                    return $this->showResponse($analysisId, $result);
                }
                if ($postAction === 'recalculate-keywords') {
                    $result = $this->service->recalculateKeywords($analysisId, $this->userId);
                    return $this->showResponse($analysisId, $result);
                }
                if ($postAction === 'generate-cluster') {
                    $result = $this->service->generateCluster($analysisId, $this->userId);
                    return $this->showResponse($analysisId, $result);
                }
                if ($postAction === 'send-to-articles') {
                    $result = $this->service->sendToArticles($analysisId, $this->userId);
                    return $this->showResponse($analysisId, $result);
                }
            }
        }

        if (isset($_GET['created'])) {
            $flash = ['type' => 'success', 'message' => 'Analyse créée avec succès.'];
        }
        if (isset($_GET['deleted'])) {
            $flash = ['type' => 'success', 'message' => 'Analyse supprimée.'];
        }

        if ($action === 'create') {
            return [
                'view' => 'form',
                'errors' => [],
                'old' => [],
                'flash' => $flash,
            ];
        }

        if ($action === 'show') {
            $analysisId = (int) ($_GET['id'] ?? 0);
            $analysis = $analysisId > 0 ? $this->service->getForUser($analysisId, $this->userId) : null;
            if (!$analysis) {
                return [
                    'view' => 'list',
                    'flash' => ['type' => 'error', 'message' => 'Analyse introuvable.'],
                    'pagination' => $this->service->listForUser($this->userId, (int) ($_GET['p'] ?? 1)),
                ];
            }

            return [
                'view' => 'show',
                'analysis' => $analysis,
                'flash' => $flash,
            ];
        }

        return [
            'view' => 'list',
            'pagination' => $this->service->listForUser($this->userId, (int) ($_GET['p'] ?? 1)),
            'flash' => $flash,
        ];
    }

    private function ensureCsrf(): void
    {
        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        $sentToken = (string) ($_POST['csrf_token'] ?? '');

        if ($sessionToken !== '' && !hash_equals($sessionToken, $sentToken)) {
            http_response_code(403);
            exit('Token CSRF invalide.');
        }
    }

    private function showResponse(int $analysisId, array $result): array
    {
        $analysis = $this->service->getForUser($analysisId, $this->userId);
        return [
            'view' => $analysis ? 'show' : 'list',
            'analysis' => $analysis,
            'pagination' => $analysis ? null : $this->service->listForUser($this->userId, (int) ($_GET['p'] ?? 1)),
            'flash' => [
                'type' => !empty($result['success']) ? 'success' : 'error',
                'message' => (string) ($result['message'] ?? $result['error'] ?? 'Action exécutée'),
            ],
            'action_result' => $result,
        ];
    }
}
