<?php

declare(strict_types=1);

final class GmbService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retourne les compteurs du HUB GMB.
     *
     * TODO: Remplacer les mocks par des requêtes SQL réelles
     * (tables gmb_listing, gmb_reviews, gmb_sync_logs, etc.).
     */
    public function getHubStats(int $userId): array
    {
        if ($userId <= 0) {
            return [
                'listing_exists' => false,
                'reviews_count' => 0,
                'reviews_rating' => 0.0,
                'last_sync' => null,
                'last_crawl_score' => null,
            ];
        }

        // TODO: Lire listing/reviews/sync depuis la DB ou API Google Business Profile.
        return [
            'listing_exists' => true,
            'reviews_count' => 24,
            'reviews_rating' => 4.7,
            'last_sync' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'last_crawl_score' => 86,
        ];
    }

    /**
     * Lance une synchronisation immédiate (mock).
     *
     * TODO: Brancher un job queue + persistance de statut (queued/running/done/failed).
     */
    public function syncNow(int $userId): array
    {
        return [
            'ok' => $userId > 0,
            'job_id' => 'gmb-sync-' . $userId . '-' . time(),
            'status' => 'queued',
        ];
    }

    /**
     * Envoie une demande d'avis de test (mock).
     *
     * TODO: Implémenter l'envoi réel (email/SMS/API partenaire) avec trace en base.
     */
    public function requestReviewTest(int $userId): array
    {
        return [
            'ok' => $userId > 0,
            'sent' => $userId > 0,
            'channel' => 'email',
        ];
    }
}
