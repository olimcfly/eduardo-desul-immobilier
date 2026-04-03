<?php

declare(strict_types=1);

class AvisManager
{
    public function __construct(private readonly PDO $pdo, private readonly int $userId)
    {
    }

    public function list(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM gmb_avis WHERE user_id = ? ORDER BY avis_at DESC LIMIT ?');
        $stmt->bindValue(1, $this->userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function upsertFromApi(array $reviews): int
    {
        $sql = 'INSERT INTO gmb_avis (user_id, gmb_review_id, auteur, photo_auteur, note, commentaire, avis_at, sentiment, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    auteur = VALUES(auteur),
                    photo_auteur = VALUES(photo_auteur),
                    note = VALUES(note),
                    commentaire = VALUES(commentaire),
                    avis_at = VALUES(avis_at),
                    sentiment = VALUES(sentiment)';

        $stmt = $this->pdo->prepare($sql);
        $count = 0;
        foreach ($reviews as $review) {
            $stmt->execute([
                $this->userId,
                $review['gmb_review_id'],
                $review['auteur'] ?? 'Client',
                $review['photo_auteur'] ?? null,
                max(1, min(5, (int) ($review['note'] ?? 5))),
                $review['commentaire'] ?? '',
                $review['avis_at'] ?? date('Y-m-d H:i:s'),
                $this->computeSentiment((int) ($review['note'] ?? 5)),
                'nouveau',
            ]);
            $count++;
        }
        return $count;
    }

    public function saveReply(int $avisId, string $reply): bool
    {
        $stmt = $this->pdo->prepare('UPDATE gmb_avis SET reponse = ?, reponse_at = NOW(), statut = "repondu" WHERE id = ? AND user_id = ?');
        return $stmt->execute([$reply, $avisId, $this->userId]);
    }

    private function computeSentiment(int $note): string
    {
        return $note >= 4 ? 'positif' : ($note === 3 ? 'neutre' : 'negatif');
    }
}
