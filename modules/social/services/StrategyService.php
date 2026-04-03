<?php

declare(strict_types=1);

final class StrategyService
{
    public function buildFromPost(array $post): array
    {
        $content = strtolower((string) ($post['contenu'] ?? ''));
        $persona = str_contains($content, 'invest') ? 'Investisseur' : 'Vendeur Senior';

        return [
            'persona' => $persona,
            'niveau' => str_contains($content, 'estimation') ? 'Niveau 2' : 'Niveau 3',
            'objectif' => 'Faire progresser le prospect vers une demande de contact.',
            'mots_magiques' => $this->magicWords($content),
            'score' => min(95, 55 + (int) floor(strlen((string) ($post['contenu'] ?? '')) / 40)),
        ];
    }

    public function storeSnapshot(int $postId, array $payload): void
    {
        if ($postId <= 0) {
            return;
        }

        $strategy = json_encode($this->buildFromPost($payload), JSON_UNESCAPED_UNICODE);
        try {
            $stmt = db()->prepare('INSERT INTO social_logs (post_id, action, payload, created_at) VALUES (:post_id, :action, :payload, NOW())');
            $stmt->execute([
                ':post_id' => $postId,
                ':action' => 'strategy_snapshot',
                ':payload' => $strategy,
            ]);
        } catch (Throwable) {
            // no-op: table absente en environnement local.
        }
    }

    private function magicWords(string $content): array
    {
        $pool = ['vous', 'gratuit', 'résultat', 'solution', 'nouveau', 'gagner', 'garantie'];
        $found = [];
        foreach ($pool as $word) {
            if (str_contains($content, $word)) {
                $found[] = $word;
            }
        }

        return $found;
    }
}
