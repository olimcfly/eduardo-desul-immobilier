<?php

class InstagramClient
{
    private string $accessToken;
    private string $igUserId;

    public function __construct(int $userId)
    {
        $this->accessToken = (string) setting('ig_access_token', '', $userId);
        $this->igUserId = (string) setting('ig_user_id', '', $userId);
    }

    public function createContainer(array $data): string|false
    {
        $payload = [
            'caption' => (string) ($data['caption'] ?? ''),
            'image_url' => (string) ($data['image_url'] ?? ''),
        ];
        $res = $this->request('POST', "/{$this->igUserId}/media", $payload);
        return $res['id'] ?? false;
    }

    public function publishContainer(string $containerId): string|false
    {
        $res = $this->request('POST', "/{$this->igUserId}/media_publish", ['creation_id' => $containerId]);
        return $res['id'] ?? false;
    }

    public function createCarrousel(array $medias, string $caption): string|false
    {
        $children = implode(',', array_filter($medias));
        $res = $this->request('POST', "/{$this->igUserId}/media", [
            'media_type' => 'CAROUSEL',
            'children' => $children,
            'caption' => $caption,
        ]);
        return $res['id'] ?? false;
    }

    public function getMediaStats(string $mediaId): array
    {
        $res = $this->request('GET', '/' . rawurlencode($mediaId) . '/insights', ['metric' => 'impressions,reach,likes,comments,saved']);
        return $res['data'] ?? [];
    }

    public function getAccountStats(string $from, string $to): array
    {
        $res = $this->request('GET', "/{$this->igUserId}/insights", [
            'metric' => 'impressions,reach,profile_views,follower_count',
            'period' => 'day',
            'since' => $from,
            'until' => $to,
        ]);
        return $res['data'] ?? [];
    }

    public function isConnected(): bool
    {
        return $this->accessToken !== '' && $this->igUserId !== '';
    }

    private function request(string $method, string $endpoint, array $params = []): array
    {
        if (!$this->isConnected()) {
            return [];
        }
        $url = 'https://graph.facebook.com/v19.0' . $endpoint;
        $params['access_token'] = $this->accessToken;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $method === 'GET' ? $url . '?' . http_build_query($params) : $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $method === 'GET' ? null : http_build_query($params),
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
