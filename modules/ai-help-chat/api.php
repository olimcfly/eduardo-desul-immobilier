<?php

declare(strict_types=1);

function handleAiHelpChatApi(AiHelpChatService $service): void
{
    header('Content-Type: application/json; charset=utf-8');

    if (!Auth::check()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Non autorisé']);
        return;
    }

    $user = Auth::user() ?? ['id' => 0, 'role' => 'guest'];
    $role = (string) ($user['role'] ?? 'guest');

    $action = preg_replace('/[^a-z_]/', '', (string) ($_GET['api_action'] ?? $_POST['api_action'] ?? 'get_settings'));

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'CSRF invalide']);
        return;
    }

    switch ($action) {
        case 'send_message':
            if (!$service->canUserUseChat($role)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Chat non autorisé pour ce rôle']);
                return;
            }

            $message = trim((string) ($_POST['message'] ?? ''));
            if ($message === '') {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Message vide']);
                return;
            }

            $context = [
                'module' => preg_replace('/[^a-z0-9_-]/', '', (string) ($_POST['context_module'] ?? 'dashboard')),
                'page' => mb_substr(trim((string) ($_POST['context_page'] ?? '')), 0, 120),
                'category' => mb_substr(trim((string) ($_POST['context_category'] ?? '')), 0, 80),
                'step' => mb_substr(trim((string) ($_POST['context_step'] ?? '')), 0, 80),
            ];

            $conversationId = (int) ($_POST['conversation_id'] ?? 0);
            if ($conversationId <= 0) {
                $conversationId = $service->createConversation((int) ($user['id'] ?? 0), $role, $context);
            }

            $service->saveMessage($conversationId, 'user', $message, ['context' => $context]);
            $result = $service->buildAssistantResponse($message, $context);
            $service->saveMessage($conversationId, 'assistant', (string) ($result['answer'] ?? ''), $result);
            $service->logUsage((int) ($user['id'] ?? 0), (string) ($context['module'] ?? ''), 'send_message', [
                'conversation_id' => $conversationId,
                'query_length' => mb_strlen($message),
            ]);

            echo json_encode([
                'success' => true,
                'conversation_id' => $conversationId,
                'assistant' => $result,
            ], JSON_UNESCAPED_UNICODE);
            return;

        case 'get_settings':
            $settings = $service->getSettings();
            if (!aiHelpChatCanConfigure()) {
                unset($settings['system_prompt']);
            }

            echo json_encode([
                'success' => true,
                'can_use_chat' => $service->canUserUseChat($role),
                'can_configure' => aiHelpChatCanConfigure(),
                'settings' => $settings,
            ], JSON_UNESCAPED_UNICODE);
            return;

        case 'get_sources':
            $sources = $service->getSources();
            echo json_encode(['success' => true, 'sources' => $sources], JSON_UNESCAPED_UNICODE);
            return;

        case 'get_suggestions':
            if (!$service->canUserUseChat($role)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Chat non autorisé']);
                return;
            }

            $query = trim((string) ($_GET['q'] ?? $_POST['q'] ?? ''));
            $context = [
                'module' => preg_replace('/[^a-z0-9_-]/', '', (string) ($_GET['context_module'] ?? $_POST['context_module'] ?? 'dashboard')),
            ];

            $resources = $service->suggestResources($query, $context, 3);
            echo json_encode(['success' => true, 'resources' => $resources], JSON_UNESCAPED_UNICODE);
            return;

        case 'usage_logs':
            aiHelpChatRequireSuperuser();
            echo json_encode(['success' => true, 'logs' => $service->getUsageLogs(120)], JSON_UNESCAPED_UNICODE);
            return;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Action API inconnue']);
            return;
    }
}
