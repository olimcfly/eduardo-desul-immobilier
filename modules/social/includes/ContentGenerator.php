<?php

class ContentGenerator
{
    private string $openAiKey;

    public function __construct(int $userId)
    {
        $this->openAiKey = (string) setting('tech_openai_key', '', $userId);
    }

    public function generatePost(string $reseau, string $categorie, array $context): string
    {
        $prompt = "Réseau: {$reseau}\nCatégorie: {$categorie}\nContexte: " . json_encode($context, JSON_UNESCAPED_UNICODE);
        return $this->askAi($prompt) ?: $this->fallback($reseau, $categorie, $context);
    }

    public function generatePostBien(string $reseau, array $bien): string
    {
        return $this->generatePost($reseau, 'bien', $bien);
    }

    public function generatePostMarche(string $reseau, array $marche): string
    {
        return $this->generatePost($reseau, 'marche', $marche);
    }

    public function generateTemoignage(string $reseau, string $temoignage): string
    {
        return $this->generatePost($reseau, 'temoignage', ['temoignage' => $temoignage]);
    }

    public function adaptContent(string $contenu, string $reseauSource, string $reseauCible): string
    {
        return $this->generatePost($reseauCible, 'autre', ['source' => $reseauSource, 'contenu' => $contenu]);
    }

    public function suggestHashtags(string $contenu, string $reseau, int $max = 20): array
    {
        $stmt = db()->prepare('SELECT hashtag FROM social_hashtags WHERE user_id = :u AND (reseau = :r OR reseau = "all") AND actif = 1 ORDER BY nb_uses DESC, id DESC LIMIT :m');
        $stmt->bindValue(':u', socialUserId(), PDO::PARAM_INT);
        $stmt->bindValue(':r', $reseau, PDO::PARAM_STR);
        $stmt->bindValue(':m', $max, PDO::PARAM_INT);
        $stmt->execute();
        $tags = array_column($stmt->fetchAll(), 'hashtag');
        return array_values(array_unique($tags));
    }

    public function scoreEngagement(string $contenu, string $reseau): int
    {
        $len = mb_strlen($contenu);
        $base = $len > 0 ? min(60, (int) floor($len / 35)) : 0;
        $hashtags = preg_match_all('/#\w+/u', $contenu);
        $bonus = min(25, $hashtags * 3);
        $networkFactor = match ($reseau) {
            'instagram' => 10,
            'facebook' => 8,
            'linkedin' => 12,
            default => 5,
        };
        return max(0, min(100, $base + $bonus + $networkFactor));
    }

    private function askAi(string $prompt): string
    {
        if ($this->openAiKey === '') {
            return '';
        }
        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un expert immobilier social media.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.8,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->openAiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode((string) $raw, true);
        return trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));
    }

    private function fallback(string $reseau, string $categorie, array $context): string
    {
        $city = (string) ($context['ville'] ?? setting('zone_city', 'votre secteur', socialUserId()));
        return sprintf("[%s] %s : Découvrez nos conseils immobilier à %s.", ucfirst($reseau), ucfirst($categorie), $city);
    }
}
