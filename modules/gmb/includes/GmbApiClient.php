<?php

declare(strict_types=1);

class GmbApiClient
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function isConfigured(): bool
    {
        $token = (string) setting('gmb_access_token', '', $this->userId);
        return $token !== '';
    }

    public function fetchLocationProfile(): array
    {
        return [
            'gmb_location_id' => (string) setting('gmb_location_id', '', $this->userId),
            'gmb_account_id' => (string) setting('gmb_account_id', '', $this->userId),
            'nom_etablissement' => (string) setting('agency_name', 'Mon agence immobilière', $this->userId),
            'categorie' => 'Agence immobilière',
            'adresse' => (string) setting('agency_address', '', $this->userId),
            'ville' => (string) setting('zone_city', '', $this->userId),
            'code_postal' => (string) setting('zone_postal_code', '', $this->userId),
            'telephone' => (string) setting('advisor_phone', '', $this->userId),
            'site_web' => (string) setting('agency_website', '', $this->userId),
            'description' => (string) setting('advisor_bio', '', $this->userId),
            'horaires' => ['Lun-Ven' => '09:00-19:00', 'Sam' => '09:00-12:00'],
            'photos' => [],
            'statut' => $this->isConfigured() ? 'actif' : 'non_verifie',
        ];
    }

    public function fetchReviews(): array
    {
        // Stub API: conservé volontairement simple pour intégrer le vrai endpoint Google plus tard.
        return [];
    }

    public function publishReviewReply(string $googleReviewId, string $reply): bool
    {
        return $googleReviewId !== '' && trim($reply) !== '';
    }

    public function fetchStats(string $startDate, string $endDate): array
    {
        return [
            'impressions' => random_int(120, 600),
            'clics_site' => random_int(10, 140),
            'appels' => random_int(4, 40),
            'itineraires' => random_int(2, 30),
            'photos_vues' => random_int(40, 300),
            'recherches_dir' => random_int(20, 220),
            'recherches_disc' => random_int(15, 180),
            'date_stat' => $endDate,
        ];
    }
}
