<?php
// ============================================================
// AI SERVICE — Appel API Claude (Anthropic)
// ============================================================

class AiService
{
    private static string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private static string $model  = 'claude-haiku-4-5-20251001';

    public static function ask(string $systemPrompt, string $userMessage): string
    {
        $apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? '';

        if (empty($apiKey)) {
            throw new RuntimeException('Clé API Anthropic manquante. Ajoutez ANTHROPIC_API_KEY dans le .env');
        }

        $payload = json_encode([
            'model'      => self::$model,
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        $ch = curl_init(self::$apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new RuntimeException('Erreur API Anthropic (HTTP ' . $httpCode . ')');
        }

        $data = json_decode($response, true);

        return $data['content'][0]['text'] ?? '';
    }
}
