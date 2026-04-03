<?php

class LinkedinClient
{
    private string $accessToken;
    private string $personId;

    public function __construct(int $userId)
    {
        $this->accessToken = (string) setting('li_access_token', '', $userId);
        $this->personId = (string) setting('li_person_id', '', $userId);
    }

    public function publishPost(array $data): string|false
    {
        $payload = [
            'author' => $this->personId,
            'commentary' => (string) ($data['contenu'] ?? ''),
            'visibility' => (string) ($data['visibility'] ?? 'PUBLIC'),
            'distribution' => ['feedDistribution' => 'MAIN_FEED', 'targetEntities' => [], 'thirdPartyDistributionChannels' => []],
            'lifecycleState' => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false,
        ];
        $res = $this->request('POST', 'https://api.linkedin.com/rest/posts', $payload);
        return $res['id'] ?? false;
    }

    public function publishArticle(array $data): string|false
    {
        $content = trim((string) ($data['title'] ?? '') . "\n\n" . (string) ($data['contenu'] ?? ''));
        return $this->publishPost(['contenu' => $content, 'visibility' => $data['visibility'] ?? 'PUBLIC']);
    }

    public function uploadMedia(string $path): string|false
    {
        if (!is_file($path)) {
            return false;
        }
        return false;
    }

    public function getPostStats(string $postId): array
    {
        return $this->request('GET', 'https://api.linkedin.com/rest/socialActions/' . rawurlencode($postId));
    }

    public function getProfileStats(): array
    {
        return $this->request('GET', 'https://api.linkedin.com/v2/me');
    }

    public function isConnected(): bool
    {
        return $this->accessToken !== '' && $this->personId !== '';
    }

    private function request(string $method, string $url, array $payload = []): array
    {
        if (!$this->isConnected()) {
            return [];
        }
        $ch = curl_init();
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'X-Restli-Protocol-Version: 2.0.0',
            'LinkedIn-Version: 202401',
        ];
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
        ]);
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }
        $raw = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
