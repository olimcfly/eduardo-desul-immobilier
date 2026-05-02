<?php

declare(strict_types=1);

// Webhook pour recevoir les mises à jour de Telegram
// URL: https://votre-domaine.com/telegram-webhook.php

require_once __DIR__ . '/core/bootstrap.php';

// Vérifier le token pour la sécurité (URL ou header)
$webhookToken = $_ENV['TELEGRAM_WEBHOOK_TOKEN'] ?? '';

if (empty($webhookToken)) {
    http_response_code(403);
    exit('Forbidden: No webhook token configured');
}

// Vérifier via URL query parameter
$tokenFromUrl = $_GET['token'] ?? '';

// Vérifier via header X-Telegram-Bot-Api-Secret-Token
$tokenFromHeader = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';

// Au moins une méthode doit matcher
$tokenValid = (!empty($tokenFromUrl) && $tokenFromUrl === $webhookToken) ||
              (!empty($tokenFromHeader) && $tokenFromHeader === $webhookToken);

if (!$tokenValid) {
    http_response_code(403);
    exit('Forbidden: Invalid token');
}

// Récupérer la mise à jour JSON
$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Traiter la mise à jour
try {
    TelegramBotService::handleWebhook($update);
    http_response_code(200);
    echo 'OK';
} catch (Throwable $e) {
    error_log('Telegram webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error';
}
