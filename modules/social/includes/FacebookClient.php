<?php

class FacebookClient
{
    private string $accessToken;
    private string $pageId;

    public function __construct(int $userId)
    {
        $this->accessToken = (string) setting('fb_page_access_token', '', $userId);
        $this->pageId = (string) setting('fb_page_id', '', $userId);
    }

    public function publishPost(array $data): string|false
    {
        $payload = ['message' => (string) ($data['contenu'] ?? '')];
        if (!empty($data['media_url'])) {
            $payload['link'] = (string) $data['media_url'];
        }
        $res = $this->request('POST', "/{$this->pageId}/feed", $payload);
        return $res['id'] ?? false;
    }

    public function schedulePost(array $data, string $datetime): string|false
    {
        $payload = [
            'message' => (string) ($data['contenu'] ?? ''),
            'published' => 'false',
            'scheduled_publish_time' => (string) strtotime($datetime),
        ];
        $res = $this->request('POST', "/{$this->pageId}/feed", $payload);
        return $res['id'] ?? false;
    }

    public function deletePost(string $postId): bool
    {
        $res = $this->request('DELETE', '/' . rawurlencode($postId));
        return (bool) ($res['success'] ?? false);
    }

    public function getPostStats(string $postId): array
    {
        $res = $this->request('GET', '/' . rawurlencode($postId), ['fields' => 'reactions.summary(true),comments.summary(true),shares']);
        return [
            'likes' => (int) ($res['reactions']['summary']['total_count'] ?? 0),
            'comments' => (int) ($res['comments']['summary']['total_count'] ?? 0),
            'shares' => (int) ($res['shares']['count'] ?? 0),
        ];
    }

    public function getPageStats(string $from, string $to): array
    {
        $res = $this->request('GET', "/{$this->pageId}/insights", [
            'metric' => 'page_fans,page_post_engagements,page_impressions,page_consumptions',
            'since' => $from,
            'until' => $to,
        ]);
        return $res['data'] ?? [];
    }

    public function uploadPhoto(string $path): string|false
    {
        if (!is_file($path)) {
            return false;
        }
        $payload = ['published' => 'false', 'source' => new CURLFile($path)];
        $res = $this->request('POST', "/{$this->pageId}/photos", $payload, true);
        return $res['id'] ?? false;
    }

    public function isConnected(): bool
    {
        return $this->accessToken !== '' && $this->pageId !== '';
    }

    private function request(string $method, string $endpoint, array $params = [], bool $multipart = false): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        $url = 'https://graph.facebook.com/v19.0' . $endpoint;
        $params['access_token'] = $this->accessToken;

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
        ];
        if ($method === 'GET') {
            $options[CURLOPT_URL] = $url . '?' . http_build_query($params);
        } else {
            $options[CURLOPT_POSTFIELDS] = $multipart ? $params : http_build_query($params);
        }

        curl_setopt_array($ch, $options);
        $raw = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
