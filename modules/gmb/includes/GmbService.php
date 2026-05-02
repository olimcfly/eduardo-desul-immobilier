<?php

declare(strict_types=1);

require_once __DIR__ . '/gmb_schema.php';
require_once __DIR__ . '/GmbApiClient.php';
require_once __DIR__ . '/AvisManager.php';
require_once __DIR__ . '/DemandeAvisManager.php';
require_once __DIR__ . '/../../../includes/settings.php';

class GmbService
{
    private PDO $pdo;
    private GmbApiClient $api;
    private AvisManager $avisManager;
    private DemandeAvisManager $demandeManager;

    public function __construct(private readonly int $userId)
    {
        $this->pdo = db();
        gmb_ensure_schema($this->pdo);
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
        $siteWeb = trim((string) ($payload['site_web'] ?? ''));
        if ($siteWeb !== '' && filter_var($siteWeb, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('URL du site web invalide.');
        }

        $telephone = trim((string) ($payload['telephone'] ?? ''));
        if ($telephone !== '' && !preg_match('/^[0-9+().\\s-]{6,30}$/', $telephone)) {
            throw new InvalidArgumentException('Téléphone invalide.');
        }

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
            'nom_etablissement' => mb_substr(trim((string) ($payload['nom_etablissement'] ?? '')), 0, 200),
            'categorie' => mb_substr(trim((string) ($payload['categorie'] ?? '')), 0, 200),
            'adresse' => mb_substr(trim((string) ($payload['adresse'] ?? '')), 0, 500),
            'ville' => mb_substr(trim((string) ($payload['ville'] ?? '')), 0, 100),
            'code_postal' => mb_substr(trim((string) ($payload['code_postal'] ?? '')), 0, 10),
            'telephone' => $telephone,
            'site_web' => $siteWeb,
            'description' => mb_substr(trim((string) ($payload['description'] ?? '')), 0, 2000),
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

    public function enqueueSyncJob(string $source = 'manual'): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO gmb_sync_jobs
            (user_id, status, source, attempts, payload, created_at, updated_at)
            VALUES (?, 'pending', ?, 0, ?, NOW(), NOW())");

        $stmt->execute([
            $this->userId,
            $source,
            json_encode(['requested_at' => date('c')], JSON_UNESCAPED_UNICODE),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getLatestSyncJob(): array
    {
        $stmt = $this->pdo->prepare('SELECT id, status, source, attempts, started_at, finished_at, error_message, created_at, updated_at
            FROM gmb_sync_jobs
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 1');
        $stmt->execute([$this->userId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            return [
                'id' => null,
                'status' => 'pending',
                'source' => null,
                'attempts' => 0,
                'started_at' => null,
                'finished_at' => null,
                'error_message' => null,
                'created_at' => null,
                'updated_at' => null,
            ];
        }

        return $job;
    }

    public function processSyncQueue(int $maxJobs = 5): int
    {
        $processed = 0;

        for ($i = 0; $i < $maxJobs; $i++) {
            $job = $this->lockNextPendingJob();
            if ($job === null) {
                break;
            }

            $jobId = (int) $job['id'];

            try {
                $this->syncFicheFromGoogle();
                $avisSynced = $this->syncAvisFromGoogle();
                $this->markJobDone($jobId, ['avis_synced' => $avisSynced]);
            } catch (Throwable $e) {
                $this->markJobError($jobId, $e->getMessage());
            }

            $processed++;
        }

        return $processed;
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
        $clientNom = mb_substr(trim((string) ($payload['client_nom'] ?? '')), 0, 200);
        $clientEmail = trim((string) ($payload['client_email'] ?? ''));
        $clientTel = trim((string) ($payload['client_tel'] ?? ''));

        if ($clientNom === '') {
            throw new InvalidArgumentException('Nom client obligatoire.');
        }
        if ($clientEmail !== '' && !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email client invalide.');
        }
        if ($clientTel !== '' && !preg_match('/^[0-9+().\\s-]{6,30}$/', $clientTel)) {
            throw new InvalidArgumentException('Téléphone client invalide.');
        }

        $payload['client_nom'] = $clientNom;
        $payload['client_email'] = $clientEmail;
        $payload['client_tel'] = $clientTel;
        $payload['bien_adresse'] = mb_substr(trim((string) ($payload['bien_adresse'] ?? '')), 0, 300);

        $demandeId = $this->demandeManager->create($payload);
        $demande = $this->demandeManager->findById($demandeId);

        $clientEmail = trim((string) ($demande['client_email'] ?? ''));
        if ($clientEmail === '' || !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->demandeManager->trackReviewRequest($demandeId, $clientEmail, 'echec', 'Email client invalide');
            throw new RuntimeException('Adresse email client invalide.');
        }

        $reviewLink = $this->buildGoogleReviewLink();
        $template = !empty($demande['template_id'])
            ? $this->demandeManager->findTemplateById((int) $demande['template_id'], 'email')
            : [];

        $subject = trim((string) ($template['sujet'] ?? 'Votre avis Google compte beaucoup pour nous'));
        $content = trim((string) ($template['contenu'] ?? "Bonjour {client_nom},

Merci pour votre confiance.
Pouvez-vous laisser un avis sur notre fiche Google ?

{lien_avis}

Merci infiniment."));

        $replacements = [
            '{client_nom}' => (string) ($demande['client_nom'] ?? 'Client'),
            '{bien_adresse}' => (string) ($demande['bien_adresse'] ?? ''),
            '{lien_avis}' => $reviewLink,
            '{advisor_firstname}' => (string) setting('advisor_firstname', '', $this->userId),
            '{advisor_lastname}' => (string) setting('advisor_lastname', '', $this->userId),
            '{advisor_phone}' => (string) setting('advisor_phone', '', $this->userId),
        ];

        $subject = strtr($subject, $replacements);
        $textBody = strtr($content, $replacements);
        $htmlBody = nl2br(htmlspecialchars($textBody, ENT_QUOTES, 'UTF-8'));

        $sent = MailService::send($clientEmail, $subject, $textBody, $htmlBody);
        if (!$sent) {
            $this->demandeManager->trackReviewRequest($demandeId, $clientEmail, 'echec', 'Envoi email échoué');
            throw new RuntimeException("Impossible d'envoyer l'email de demande d'avis.");
        }

        $this->demandeManager->markSent($demandeId);
        $this->demandeManager->trackReviewRequest($demandeId, $clientEmail, 'envoye');

        return $demandeId;
    }

    private function buildGoogleReviewLink(): string
    {
        $fiche = $this->getFiche();
        $placeQuery = trim((string) ($fiche['nom_etablissement'] ?? '') . ' ' . (string) ($fiche['adresse'] ?? '') . ' ' . (string) ($fiche['ville'] ?? ''));

        if ($placeQuery === '') {
            $placeQuery = (string) setting('agency_name', 'Agence immobilière', $this->userId);
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($placeQuery);
    }

    public function saveTemplate(array $payload): bool
    {
        $payload['nom'] = mb_substr(trim((string) ($payload['nom'] ?? '')), 0, 200);
        $payload['sujet'] = mb_substr(trim((string) ($payload['sujet'] ?? '')), 0, 300);
        $payload['contenu'] = mb_substr(trim((string) ($payload['contenu'] ?? '')), 0, 4000);

        if ($payload['nom'] === '' || $payload['contenu'] === '') {
            throw new InvalidArgumentException('Nom et contenu du template obligatoires.');
        }

        return $this->demandeManager->saveTemplate($payload);
    }

    public function templates(string $canal = 'email'): array
    {
        return $this->demandeManager->templates($canal);
    }

    public function getStats(?string $startDate = null, ?string $endDate = null): array
    {
        $stmt = $this->pdo->prepare('SELECT date_stat, impressions, clics_site, appels, itineraires, photos_vues, recherches_dir, recherches_disc
            FROM gmb_statistiques
            WHERE user_id = ?
            ORDER BY date_stat DESC
            LIMIT 1');
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : [];
    }

    public function saveManualStats(array $payload): array
    {
        $month = trim((string) ($payload['stats_month'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw new InvalidArgumentException('Mois statistique invalide.');
        }

        $date = $month . '-01';
        $fields = ['impressions', 'clics_site', 'appels', 'itineraires', 'photos_vues', 'recherches_dir', 'recherches_disc'];
        $stats = ['date_stat' => $date];
        foreach ($fields as $field) {
            $raw = $payload[$field] ?? 0;
            if (!is_numeric($raw) || (int) $raw < 0) {
                throw new InvalidArgumentException('Les statistiques doivent être des nombres positifs.');
            }
            $stats[$field] = (int) $raw;
        }

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
            $stats['date_stat'],
            $stats['impressions'],
            $stats['clics_site'],
            $stats['appels'],
            $stats['itineraires'],
            $stats['photos_vues'],
            $stats['recherches_dir'],
            $stats['recherches_disc'],
        ]);

        return $stats;
    }

    private function lockNextPendingJob(): ?array
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare("SELECT *
            FROM gmb_sync_jobs
            WHERE user_id = ? AND status = 'pending'
            ORDER BY id ASC
            LIMIT 1
            FOR UPDATE");
        $stmt->execute([$this->userId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if ($job === null) {
            $this->pdo->commit();
            return null;
        }

        $update = $this->pdo->prepare("UPDATE gmb_sync_jobs
            SET status = 'running', attempts = attempts + 1, started_at = NOW(), updated_at = NOW()
            WHERE id = ?");
        $update->execute([(int) $job['id']]);

        $this->pdo->commit();

        return $job;
    }

    private function markJobDone(int $jobId, array $result): void
    {
        $stmt = $this->pdo->prepare("UPDATE gmb_sync_jobs
            SET status = 'done', result = ?, error_message = NULL, finished_at = NOW(), updated_at = NOW()
            WHERE id = ?");

        $stmt->execute([
            json_encode($result, JSON_UNESCAPED_UNICODE),
            $jobId,
        ]);
    }

    private function markJobError(int $jobId, string $message): void
    {
        $stmt = $this->pdo->prepare("UPDATE gmb_sync_jobs
            SET status = 'error', error_message = ?, finished_at = NOW(), updated_at = NOW()
            WHERE id = ?");

        $stmt->execute([
            mb_substr($message, 0, 5000),
            $jobId,
        ]);
    }
}
