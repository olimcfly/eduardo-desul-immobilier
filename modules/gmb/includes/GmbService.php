<?php

declare(strict_types=1);

require_once __DIR__ . '/GmbApiClient.php';
require_once __DIR__ . '/AvisManager.php';
require_once __DIR__ . '/DemandeAvisManager.php';

class GmbService
{
    private PDO $pdo;
    private GmbApiClient $api;
    private AvisManager $avisManager;
    private DemandeAvisManager $demandeManager;

    public function __construct(private readonly int $userId)
    {
        $this->pdo = db();
        $this->api = new GmbApiClient($this->userId);
        $this->avisManager = new AvisManager($this->pdo, $this->userId);
        $this->demandeManager = new DemandeAvisManager($this->pdo, $this->userId);
    }

    public function getFiche(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM gmb_fiche WHERE user_id = ? LIMIT 1');
        $stmt->execute([$this->userId]);
        $fiche = $stmt->fetch();
        return $fiche ?: [];
    }

    public function saveFiche(array $payload): bool
    {
        $sql = 'INSERT INTO gmb_fiche
            (user_id, gmb_location_id, gmb_account_id, nom_etablissement, categorie, adresse, ville, code_postal, telephone, site_web, description, horaires, photos, statut, last_sync)
            VALUES (:user_id, :gmb_location_id, :gmb_account_id, :nom_etablissement, :categorie, :adresse, :ville, :code_postal, :telephone, :site_web, :description, :horaires, :photos, :statut, :last_sync)
            ON DUPLICATE KEY UPDATE
                gmb_location_id = VALUES(gmb_location_id),
                gmb_account_id = VALUES(gmb_account_id),
                nom_etablissement = VALUES(nom_etablissement),
                categorie = VALUES(categorie),
                adresse = VALUES(adresse),
                ville = VALUES(ville),
                code_postal = VALUES(code_postal),
                telephone = VALUES(telephone),
                site_web = VALUES(site_web),
                description = VALUES(description),
                horaires = VALUES(horaires),
                photos = VALUES(photos),
                statut = VALUES(statut),
                last_sync = VALUES(last_sync)';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'user_id' => $this->userId,
            'gmb_location_id' => trim((string) ($payload['gmb_location_id'] ?? '')),
            'gmb_account_id' => trim((string) ($payload['gmb_account_id'] ?? '')),
            'nom_etablissement' => trim((string) ($payload['nom_etablissement'] ?? '')),
            'categorie' => trim((string) ($payload['categorie'] ?? '')),
            'adresse' => trim((string) ($payload['adresse'] ?? '')),
            'ville' => trim((string) ($payload['ville'] ?? '')),
            'code_postal' => trim((string) ($payload['code_postal'] ?? '')),
            'telephone' => trim((string) ($payload['telephone'] ?? '')),
            'site_web' => trim((string) ($payload['site_web'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'horaires' => json_encode($payload['horaires'] ?? [], JSON_UNESCAPED_UNICODE),
            'photos' => json_encode($payload['photos'] ?? [], JSON_UNESCAPED_UNICODE),
            'statut' => in_array(($payload['statut'] ?? ''), ['actif', 'suspendu', 'non_verifie'], true) ? $payload['statut'] : 'non_verifie',
            'last_sync' => date('Y-m-d H:i:s'),
        ]);
    }

    public function syncFicheFromGoogle(): array
    {
        $profile = $this->api->fetchLocationProfile();
        $this->saveFiche($profile);
        return $profile;
    }

    public function syncAvisFromGoogle(): int
    {
        $reviews = $this->api->fetchReviews();
        return $this->avisManager->upsertFromApi($reviews);
    }

    public function replyToAvis(int $avisId, string $reply): bool
    {
        $stmt = $this->pdo->prepare('SELECT gmb_review_id FROM gmb_avis WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$avisId, $this->userId]);
        $reviewId = (string) $stmt->fetchColumn();
        if ($reviewId === '') {
            return false;
        }

        if (!$this->api->publishReviewReply($reviewId, $reply)) {
            return false;
        }

        return $this->avisManager->saveReply($avisId, $reply);
    }

    public function avis(): array
    {
        return $this->avisManager->list();
    }

    public function createDemandeAvis(array $payload): int
    {
        $demandeId = $this->demandeManager->create($payload);
        $this->demandeManager->markSent($demandeId);
        return $demandeId;
    }

    public function saveTemplate(array $payload): bool
    {
        return $this->demandeManager->saveTemplate($payload);
    }

    public function templates(string $canal = 'email'): array
    {
        return $this->demandeManager->templates($canal);
    }

    public function getStats(string $startDate, string $endDate): array
    {
        $apiStats = $this->api->fetchStats($startDate, $endDate);
        $stmt = $this->pdo->prepare('INSERT INTO gmb_statistiques
            (user_id, date_stat, impressions, clics_site, appels, itineraires, photos_vues, recherches_dir, recherches_disc)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                impressions = VALUES(impressions),
                clics_site = VALUES(clics_site),
                appels = VALUES(appels),
                itineraires = VALUES(itineraires),
                photos_vues = VALUES(photos_vues),
                recherches_dir = VALUES(recherches_dir),
                recherches_disc = VALUES(recherches_disc)');

        $stmt->execute([
            $this->userId,
            $apiStats['date_stat'],
            $apiStats['impressions'],
            $apiStats['clics_site'],
            $apiStats['appels'],
            $apiStats['itineraires'],
            $apiStats['photos_vues'],
            $apiStats['recherches_dir'],
            $apiStats['recherches_disc'],
        ]);

        return $apiStats;
    }
}
