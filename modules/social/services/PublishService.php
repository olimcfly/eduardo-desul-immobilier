<?php

declare(strict_types=1);

final class PublishService
{
    public function publish(array $post): array
    {
        return [
            'facebook' => in_array('facebook', $this->extractNetworks($post), true),
            'instagram' => in_array('instagram', $this->extractNetworks($post), true),
            'linkedin' => in_array('linkedin', $this->extractNetworks($post), true),
        ];
    }

    private function extractNetworks(array $post): array
    {
        $decoded = json_decode((string) ($post['reseaux'] ?? '[]'), true);
        return is_array($decoded) ? $decoded : [];
    }
}
