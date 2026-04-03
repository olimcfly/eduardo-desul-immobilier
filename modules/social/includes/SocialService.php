<?php

class SocialService
{
    public function getHubStats(int $userId): array
    {
        $pdo = db();
        $networks = ['facebook', 'instagram', 'linkedin'];
        $stats = [];

        foreach ($networks as $reseau) {
            $monthly = $pdo->prepare('SELECT COUNT(*) FROM social_posts WHERE user_id = :u AND JSON_CONTAINS(reseaux, :r) AND statut = "publie" AND MONTH(publie_at) = MONTH(CURRENT_DATE()) AND YEAR(publie_at) = YEAR(CURRENT_DATE())');
            $monthly->execute([':u' => $userId, ':r' => json_encode($reseau)]);

            $subs = $pdo->prepare('SELECT abonnes FROM social_stats WHERE user_id = :u AND reseau = :r ORDER BY date_stat DESC LIMIT 1');
            $subs->execute([':u' => $userId, ':r' => $reseau]);

            $upcoming = $pdo->prepare('SELECT COUNT(*) FROM social_posts WHERE user_id = :u AND JSON_CONTAINS(reseaux, :r) AND statut = "planifie" AND planifie_at >= NOW()');
            $upcoming->execute([':u' => $userId, ':r' => json_encode($reseau)]);

            $last = $pdo->prepare('SELECT publie_at FROM social_posts WHERE user_id = :u AND JSON_CONTAINS(reseaux, :r) AND publie_at IS NOT NULL ORDER BY publie_at DESC LIMIT 1');
            $last->execute([':u' => $userId, ':r' => json_encode($reseau)]);

            $stats[$reseau] = [
                'posts_ce_mois' => (int) $monthly->fetchColumn(),
                'abonnes' => (int) ($subs->fetchColumn() ?: 0),
                'planifies_a_venir' => (int) $upcoming->fetchColumn(),
                'derniere_publication' => (string) ($last->fetchColumn() ?: ''),
            ];
        }

        return $stats;
    }

    public function publishPost(int $postId, array $reseaux): array
    {
        $post = $this->getPost($postId);
        if (!$post) {
            return [];
        }

        $userId = (int) $post['user_id'];
        $results = ['facebook' => false, 'instagram' => false, 'linkedin' => false];

        if (in_array('facebook', $reseaux, true)) {
            $client = new FacebookClient($userId);
            $results['facebook'] = $client->isConnected() ? (bool) $client->publishPost($post) : false;
        }
        if (in_array('instagram', $reseaux, true)) {
            $client = new InstagramClient($userId);
            $containerId = $client->isConnected() ? $client->createContainer(['caption' => $post['contenu'], 'image_url' => $this->firstMediaUrl($post)]) : false;
            $results['instagram'] = $containerId ? (bool) $client->publishContainer((string) $containerId) : false;
        }
        if (in_array('linkedin', $reseaux, true)) {
            $client = new LinkedinClient($userId);
            $results['linkedin'] = $client->isConnected() ? (bool) $client->publishPost(['contenu' => $post['contenu']]) : false;
        }

        $successOne = in_array(true, $results, true);
        $stmt = db()->prepare('UPDATE social_posts SET statut = :s, publie_at = :p WHERE id = :id');
        $stmt->execute([
            ':s' => $successOne ? 'publie' : 'erreur',
            ':p' => $successOne ? date('Y-m-d H:i:s') : null,
            ':id' => $postId,
        ]);
        return $results;
    }

    public function schedulePost(int $postId, string $datetime, array $reseaux): bool
    {
        $stmt = db()->prepare('UPDATE social_posts SET statut = "planifie", planifie_at = :d, reseaux = :r WHERE id = :id');
        return $stmt->execute([':d' => $datetime, ':r' => json_encode(array_values($reseaux)), ':id' => $postId]);
    }

    public function processScheduled(): int
    {
        $stmt = db()->query('SELECT id, reseaux FROM social_posts WHERE statut = "planifie" AND planifie_at <= NOW()');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        foreach ($rows as $row) {
            $reseaux = json_decode((string) $row['reseaux'], true) ?: [];
            $res = $this->publishPost((int) $row['id'], $reseaux);
            if (in_array(true, $res, true)) {
                $count++;
            }
        }
        return $count;
    }

    public function syncStats(int $userId): void
    {
        $today = date('Y-m-d');
        $hub = $this->getHubStats($userId);
        foreach ($hub as $reseau => $values) {
            $stmt = db()->prepare('INSERT INTO social_stats (user_id, reseau, date_stat, abonnes, posts_count) VALUES (:u,:r,:d,:a,:p) ON DUPLICATE KEY UPDATE abonnes=VALUES(abonnes), posts_count=VALUES(posts_count)');
            $stmt->execute([':u' => $userId, ':r' => $reseau, ':d' => $today, ':a' => $values['abonnes'], ':p' => $values['posts_ce_mois']]);
        }
    }

    public function duplicatePost(int $postId, string $reseau): int
    {
        $post = $this->getPost($postId);
        if (!$post) {
            return 0;
        }
        $stmt = db()->prepare('INSERT INTO social_posts (user_id, titre, contenu, medias, reseaux, type_post, statut, categorie, tags, created_at) VALUES (:u,:t,:c,:m,:r,:tp,"brouillon",:cat,:tags,NOW())');
        $stmt->execute([
            ':u' => (int) $post['user_id'],
            ':t' => (string) $post['titre'],
            ':c' => (string) $post['contenu'],
            ':m' => $post['medias'],
            ':r' => json_encode([$reseau]),
            ':tp' => (string) $post['type_post'],
            ':cat' => (string) $post['categorie'],
            ':tags' => $post['tags'],
        ]);
        return (int) db()->lastInsertId();
    }

    public function getBestHours(string $reseau): array
    {
        $defaults = [
            'facebook' => ['Mercredi 13:00', 'Jeudi 14:00', 'Vendredi 15:00'],
            'instagram' => ['Mardi 11:00', 'Mercredi 11:00', 'Vendredi 10:30'],
            'linkedin' => ['Mardi 08:30', 'Mercredi 09:00', 'Jeudi 09:30'],
        ];
        $key = 'social_' . substr($reseau, 0, 2) . '_best_hours';
        $raw = (string) setting($key, '', (int) ($_SESSION['user_id'] ?? 0));
        $decoded = json_decode($raw, true);
        return is_array($decoded) && $decoded ? $decoded : ($defaults[$reseau] ?? []);
    }

    public function searchModules(string $q): array
    {
        $items = [
            ['name' => 'Facebook', 'url' => '/admin/?module=social&action=facebook'],
            ['name' => 'Instagram', 'url' => '/admin/?module=social&action=instagram'],
            ['name' => 'LinkedIn', 'url' => '/admin/?module=social&action=linkedin'],
            ['name' => 'Calendrier éditorial', 'url' => '/admin/?module=social&action=calendrier'],
        ];
        $q = mb_strtolower(trim($q));
        return array_values(array_filter($items, fn($item) => $q === '' || str_contains(mb_strtolower($item['name']), $q)));
    }

    private function getPost(int $postId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM social_posts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $postId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function firstMediaUrl(array $post): string
    {
        $medias = json_decode((string) ($post['medias'] ?? '[]'), true) ?: [];
        return (string) ($medias[0]['url'] ?? '');
    }
}
