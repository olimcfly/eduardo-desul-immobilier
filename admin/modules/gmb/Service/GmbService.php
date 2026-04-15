<?php

declare(strict_types=1);

final class GmbService
{
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureInfrastructureTables();
    }

    /**
     * Génère l'URL OAuth Google Business Profile.
     */
    public function getOAuthAuthorizationUrl(int $userId): array
    {
        if ($userId <= 0) {
            return ['ok' => false, 'error' => 'Utilisateur invalide.'];
        }

        $clientId = (string) setting('api_gmb_client_id', '', $userId);
        $clientSecret = (string) setting('api_gmb_client_secret', '', $userId);

        if ($clientId === '' || $clientSecret === '') {
            return ['ok' => false, 'error' => 'Client ID / Client Secret GMB manquants.'];
        }

        $state = bin2hex(random_bytes(16));
        setting_set('api_gmb_oauth_state', $state, $userId);
        setting_set('api_gmb_oauth_state_expires', (string) (time() + 600), $userId);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $this->buildOAuthRedirectUri(),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/business.manage',
            ]),
            'state' => $state,
        ]);

        return [
            'ok' => true,
            'url' => self::GOOGLE_AUTH_URL . '?' . $query,
            'state' => $state,
        ];
    }

    /**
     * Échange un code OAuth Google contre des tokens persistés en base.
     */
    public function handleOAuthCallback(int $userId, string $code, string $state): array
    {
        if ($userId <= 0 || trim($code) === '') {
            return ['ok' => false, 'error' => 'Paramètres OAuth invalides.'];
        }

        $savedState = (string) setting('api_gmb_oauth_state', '', $userId);
        $stateExpires = (int) setting('api_gmb_oauth_state_expires', 0, $userId);
        if ($savedState === '' || !hash_equals($savedState, $state) || $stateExpires < time()) {
            return ['ok' => false, 'error' => 'État OAuth invalide ou expiré.'];
        }

        $clientId = (string) setting('api_gmb_client_id', '', $userId);
        $clientSecret = (string) setting('api_gmb_client_secret', '', $userId);
        if ($clientId === '' || $clientSecret === '') {
            return ['ok' => false, 'error' => 'Configuration OAuth GMB incomplète.'];
        }

        $tokenPayload = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $this->buildOAuthRedirectUri(),
            'grant_type' => 'authorization_code',
        ];

        $tokenResponse = $this->httpPostForm(self::GOOGLE_TOKEN_URL, $tokenPayload);
        if (!$tokenResponse['ok']) {
            return ['ok' => false, 'error' => $tokenResponse['error']];
        }

        $data = $tokenResponse['data'];
        if (empty($data['refresh_token']) && empty($data['access_token'])) {
            return ['ok' => false, 'error' => 'Réponse OAuth invalide.'];
        }

        if (!empty($data['refresh_token'])) {
            setting_set('api_gmb_refresh_token', (string) $data['refresh_token'], $userId);
        }

        setting_set('api_gmb_access_token', (string) ($data['access_token'] ?? ''), $userId);
        setting_set('api_gmb_token_expires', (string) (time() + (int) ($data['expires_in'] ?? 3600)), $userId);
        setting_delete('api_gmb_oauth_state', $userId);
        setting_delete('api_gmb_oauth_state_expires', $userId);

        return ['ok' => true];
    }

    /**
     * Retourne les compteurs du HUB GMB avec données persistées.
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

        $this->hydrateFromGoogleIfStale($userId);

        $listing = $this->getListing($userId);
        $reviewsSummary = $this->getReviewsSummary($userId);

        $syncStmt = $this->pdo->prepare('SELECT synced_at, crawl_score FROM gmb_sync_logs WHERE user_id = ? ORDER BY synced_at DESC LIMIT 1');
        $syncStmt->execute([$userId]);
        $sync = $syncStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        return [
            'listing_exists' => $listing !== null,
            'reviews_count' => (int) ($reviewsSummary['reviews_count'] ?? 0),
            'reviews_rating' => round((float) ($reviewsSummary['reviews_rating'] ?? 0), 1),
            'last_sync' => $sync['synced_at'] ?? ($listing['last_sync'] ?? null),
            'last_crawl_score' => isset($sync['crawl_score']) ? (int) $sync['crawl_score'] : null,
        ];
    }

    /**
     * Point 1 TODO: lit la fiche GMB depuis la base.
     */
    public function getListing(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT
                id,
                user_id,
                gmb_location_id,
                gmb_account_id,
                nom_etablissement,
                categorie,
                adresse,
                ville,
                code_postal,
                telephone,
                site_web,
                description,
                horaires,
                photos,
                statut,
                last_sync,
                created_at
            FROM gmb_fiche
            WHERE user_id = ?
            LIMIT 1');
        $stmt->execute([$userId]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($listing)) {
            return null;
        }

        $listing['horaires'] = $this->decodeJsonField($listing['horaires'] ?? null);
        $listing['photos'] = $this->decodeJsonField($listing['photos'] ?? null);

        return $listing;
    }

    /**
     * Point 2 TODO: lit les avis GMB depuis la base.
     */
    public function getReviews(int $userId, int $limit = 50, int $offset = 0): array
    {
        if ($userId <= 0) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);

        $sql = 'SELECT
                id,
                user_id,
                gmb_review_id,
                auteur,
                photo_auteur,
                note,
                commentaire,
                reponse,
                reponse_at,
                avis_at,
                statut,
                sentiment,
                created_at
            FROM gmb_avis
            WHERE user_id = :user_id
            ORDER BY avis_at DESC, id DESC
            LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Ajoute un job de synchronisation GMB dans la file d'attente.
     */
    public function syncNow(int $userId): array
    {
        if ($userId <= 0) {
            return [
                'ok' => false,
                'job_id' => null,
                'status' => 'failed',
            ];
        }

        $jobId = $this->enqueueJob($userId, 'sync_profile', ['trigger' => 'manual']);

        return [
            'ok' => $jobId > 0,
            'job_id' => 'gmb-sync-' . $jobId,
            'status' => 'queued',
        ];
    }

    /**
     * Envoie une demande d'avis de test avec trace en base et job queue.
     */
    public function requestReviewTest(int $userId): array
    {
        if ($userId <= 0) {
            return ['ok' => false, 'sent' => false, 'channel' => 'email'];
        }

        $token = bin2hex(random_bytes(24));
        $stmt = $this->pdo->prepare('INSERT INTO gmb_demandes_avis
            (user_id, client_nom, client_email, client_tel, bien_adresse, canal, statut, envoye_at, token)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)');

        $sent = $stmt->execute([
            $userId,
            'Client test',
            (string) setting('advisor_email', '', $userId),
            (string) setting('advisor_phone', '', $userId),
            (string) setting('agency_address', '', $userId),
            'email',
            'envoye',
            $token,
        ]);

        if ($sent) {
            $this->enqueueJob($userId, 'send_review_request', [
                'demande_id' => (int) $this->pdo->lastInsertId(),
                'channel' => 'email',
            ]);
        }

        return [
            'ok' => $sent,
            'sent' => $sent,
            'channel' => 'email',
        ];
    }

    /**
     * Worker minimal: traite les jobs GMB en attente.
     */
    public function processQueuedJobs(int $maxJobs = 10): array
    {
        $processed = 0;

        for ($i = 0; $i < $maxJobs; $i++) {
            $job = $this->lockNextQueuedJob();
            if ($job === null) {
                break;
            }

            $jobId = (int) $job['id'];
            $userId = (int) $job['user_id'];
            $jobType = (string) $job['job_type'];
            $payload = json_decode((string) ($job['payload_json'] ?? '{}'), true);
            $payload = is_array($payload) ? $payload : [];

            try {
                if ($jobType === 'sync_profile') {
                    $syncResult = $this->syncFromGoogle($userId);
                    $this->markJobDone($jobId, $syncResult);
                } elseif ($jobType === 'send_review_request') {
                    $this->markJobDone($jobId, ['provider' => 'internal', 'queued' => true]);
                } else {
                    $this->markJobFailed($jobId, 'Type de job inconnu: ' . $jobType);
                }
                $processed++;
            } catch (Throwable $e) {
                $this->markJobFailed($jobId, $e->getMessage());
            }
        }

        return ['processed' => $processed];
    }

    private function hydrateFromGoogleIfStale(int $userId): void
    {
        $stmt = $this->pdo->prepare('SELECT MAX(synced_at) FROM gmb_sync_logs WHERE user_id = ?');
        $stmt->execute([$userId]);
        $lastSync = $stmt->fetchColumn();

        $isStale = $lastSync === false || $lastSync === null || strtotime((string) $lastSync) < (time() - 3600 * 12);
        if ($isStale && $this->hasGoogleOAuth($userId)) {
            $this->enqueueJob($userId, 'sync_profile', ['trigger' => 'auto']);
            $this->processQueuedJobs(1);
        }
    }

    private function syncFromGoogle(int $userId): array
    {
        $locationName = (string) setting('api_gmb_location_name', '', $userId);
        if ($locationName === '') {
            $locationName = $this->discoverPrimaryLocation($userId);
            if ($locationName !== '') {
                setting_set('api_gmb_location_name', $locationName, $userId);
            }
        }

        if ($locationName === '') {
            throw new RuntimeException('Aucune fiche Google Business Profile trouvée.');
        }

        $location = $this->fetchLocation($userId, $locationName);
        $reviews = $this->fetchReviews($userId, $locationName);

        $this->persistLocation($userId, $locationName, $location);
        $savedReviews = $this->persistReviews($userId, $reviews);

        $rating = (float) ($location['metadata']['averageRating'] ?? 0.0);
        $rating = $rating > 0 ? $rating : $this->computeAverageRating($reviews);
        $crawlScore = $this->computeCrawlScore($rating, count($reviews));

        $logStmt = $this->pdo->prepare('INSERT INTO gmb_sync_logs
            (user_id, job_type, status, message, crawl_score, reviews_synced, synced_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $logStmt->execute([
            $userId,
            'sync_profile',
            'done',
            'Synchronisation Google Business Profile OK',
            $crawlScore,
            $savedReviews,
        ]);

        return [
            'location_name' => $locationName,
            'reviews_synced' => $savedReviews,
            'crawl_score' => $crawlScore,
        ];
    }

    private function hasGoogleOAuth(int $userId): bool
    {
        return (string) setting('api_gmb_refresh_token', '', $userId) !== ''
            || (string) setting('api_gmb_access_token', '', $userId) !== '';
    }

    private function discoverPrimaryLocation(int $userId): string
    {
        $accounts = $this->googleGet($userId, 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts');
        $accountName = '';

        foreach (($accounts['accounts'] ?? []) as $account) {
            $name = (string) ($account['name'] ?? '');
            if ($name !== '') {
                $accountName = $name;
                break;
            }
        }

        if ($accountName === '') {
            return '';
        }

        $locations = $this->googleGet(
            $userId,
            'https://mybusinessbusinessinformation.googleapis.com/v1/'
            . rawurlencode($accountName)
            . '/locations?readMask=name,title,metadata'
        );

        foreach (($locations['locations'] ?? []) as $location) {
            $name = (string) ($location['name'] ?? '');
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }

    private function fetchLocation(int $userId, string $locationName): array
    {
        $url = 'https://mybusinessbusinessinformation.googleapis.com/v1/'
            . rawurlencode($locationName)
            . '?readMask=name,title,primaryCategory,storefrontAddress,phoneNumbers,websiteUri,profile,metadata,regularHours';

        return $this->googleGet($userId, $url);
    }

    private function fetchReviews(int $userId, string $locationName): array
    {
        $url = 'https://mybusiness.googleapis.com/v4/' . rawurlencode($locationName) . '/reviews?pageSize=50';
        $data = $this->googleGet($userId, $url);
        return is_array($data['reviews'] ?? null) ? $data['reviews'] : [];
    }

    private function persistLocation(int $userId, string $locationName, array $location): void
    {
        $address = $location['storefrontAddress']['addressLines'] ?? [];
        $addressLine = is_array($address) ? implode(', ', $address) : '';

        $stmt = $this->pdo->prepare('INSERT INTO gmb_fiche
            (user_id, gmb_location_id, gmb_account_id, nom_etablissement, categorie, adresse, ville, code_postal, telephone, site_web, description, horaires, photos, statut, last_sync)
            VALUES (:user_id, :gmb_location_id, :gmb_account_id, :nom_etablissement, :categorie, :adresse, :ville, :code_postal, :telephone, :site_web, :description, :horaires, :photos, :statut, NOW())
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
                last_sync = VALUES(last_sync)');

        $stmt->execute([
            'user_id' => $userId,
            'gmb_location_id' => $locationName,
            'gmb_account_id' => (string) ($location['name'] ?? ''),
            'nom_etablissement' => (string) ($location['title'] ?? ''),
            'categorie' => (string) ($location['primaryCategory']['displayName'] ?? ''),
            'adresse' => $addressLine,
            'ville' => (string) ($location['storefrontAddress']['locality'] ?? ''),
            'code_postal' => (string) ($location['storefrontAddress']['postalCode'] ?? ''),
            'telephone' => (string) ($location['phoneNumbers']['primaryPhone'] ?? ''),
            'site_web' => (string) ($location['websiteUri'] ?? ''),
            'description' => (string) ($location['profile']['description'] ?? ''),
            'horaires' => json_encode($location['regularHours'] ?? [], JSON_UNESCAPED_UNICODE),
            'photos' => json_encode([], JSON_UNESCAPED_UNICODE),
            'statut' => 'actif',
        ]);
    }

    private function persistReviews(int $userId, array $reviews): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO gmb_avis
            (user_id, gmb_review_id, auteur, photo_auteur, note, commentaire, avis_at, sentiment, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                auteur = VALUES(auteur),
                photo_auteur = VALUES(photo_auteur),
                note = VALUES(note),
                commentaire = VALUES(commentaire),
                avis_at = VALUES(avis_at),
                sentiment = VALUES(sentiment)');

        $count = 0;
        foreach ($reviews as $review) {
            $star = $this->normalizeStarRating((string) ($review['starRating'] ?? 'FIVE'));
            $stmt->execute([
                $userId,
                (string) ($review['reviewId'] ?? ''),
                (string) ($review['reviewer']['displayName'] ?? 'Client'),
                (string) ($review['reviewer']['profilePhotoUrl'] ?? ''),
                $star,
                (string) ($review['comment'] ?? ''),
                $this->normalizeGoogleDate((string) ($review['createTime'] ?? '')),
                $this->computeSentiment($star),
                'nouveau',
            ]);
            $count++;
        }

        return $count;
    }

    private function normalizeGoogleDate(string $value): string
    {
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
    }

    private function normalizeStarRating(string $rating): int
    {
        return match (strtoupper($rating)) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            default => 5,
        };
    }

    private function computeSentiment(int $note): string
    {
        return $note >= 4 ? 'positif' : ($note === 3 ? 'neutre' : 'negatif');
    }

    private function getReviewsSummary(int $userId): array
    {
        $reviewsStmt = $this->pdo->prepare('SELECT COUNT(*) AS reviews_count, AVG(note) AS reviews_rating FROM gmb_avis WHERE user_id = ?');
        $reviewsStmt->execute([$userId]);

        return $reviewsStmt->fetch(PDO::FETCH_ASSOC) ?: ['reviews_count' => 0, 'reviews_rating' => null];
    }

    private function decodeJsonField(mixed $raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }


    private function computeAverageRating(array $reviews): float
    {
        if ($reviews === []) {
            return 0.0;
        }

        $sum = 0;
        foreach ($reviews as $review) {
            $sum += $this->normalizeStarRating((string) ($review['starRating'] ?? 'FIVE'));
        }

        return $sum / max(1, count($reviews));
    }

    private function computeCrawlScore(float $rating, int $count): int
    {
        $ratingScore = (int) round(max(0.0, min(5.0, $rating)) * 16);
        $volumeScore = min(20, (int) floor(log(max(1, $count), 2) * 4));
        return max(0, min(100, $ratingScore + $volumeScore));
    }

    private function enqueueJob(int $userId, string $jobType, array $payload): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO gmb_sync_jobs
            (user_id, job_type, payload_json, status, attempts, available_at, created_at)
            VALUES (?, ?, ?, ?, 0, NOW(), NOW())');
        $stmt->execute([
            $userId,
            $jobType,
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            'queued',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function lockNextQueuedJob(): ?array
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->query('SELECT * FROM gmb_sync_jobs
            WHERE status = "queued" AND available_at <= NOW()
            ORDER BY id ASC
            LIMIT 1
            FOR UPDATE');

        $job = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($job === null) {
            $this->pdo->commit();
            return null;
        }

        $update = $this->pdo->prepare('UPDATE gmb_sync_jobs
            SET status = "running", attempts = attempts + 1, started_at = NOW(), updated_at = NOW()
            WHERE id = ?');
        $update->execute([(int) $job['id']]);
        $this->pdo->commit();

        return $job;
    }

    private function markJobDone(int $jobId, array $result): void
    {
        $stmt = $this->pdo->prepare('UPDATE gmb_sync_jobs
            SET status = "done", result_json = ?, finished_at = NOW(), updated_at = NOW(), last_error = NULL
            WHERE id = ?');
        $stmt->execute([json_encode($result, JSON_UNESCAPED_UNICODE), $jobId]);
    }

    private function markJobFailed(int $jobId, string $error): void
    {
        $stmt = $this->pdo->prepare('UPDATE gmb_sync_jobs
            SET status = "failed", last_error = ?, finished_at = NOW(), updated_at = NOW()
            WHERE id = ?');
        $stmt->execute([mb_substr($error, 0, 1000), $jobId]);

        $stmtLog = $this->pdo->prepare('INSERT INTO gmb_sync_logs
            (user_id, job_type, status, message, synced_at)
            SELECT user_id, job_type, "failed", ?, NOW() FROM gmb_sync_jobs WHERE id = ?');
        $stmtLog->execute([mb_substr($error, 0, 1000), $jobId]);
    }

    private function googleGet(int $userId, string $url): array
    {
        $token = $this->getValidAccessToken($userId);
        if ($token === '') {
            throw new RuntimeException('Token OAuth Google Business Profile indisponible.');
        }

        $response = $this->httpRequest($url, 'GET', [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ]);

        if (!$response['ok']) {
            throw new RuntimeException($response['error']);
        }

        return $response['data'];
    }

    private function getValidAccessToken(int $userId): string
    {
        $accessToken = (string) setting('api_gmb_access_token', '', $userId);
        $expiresAt = (int) setting('api_gmb_token_expires', 0, $userId);

        if ($accessToken !== '' && $expiresAt > (time() + 60)) {
            return $accessToken;
        }

        $refreshToken = (string) setting('api_gmb_refresh_token', '', $userId);
        if ($refreshToken === '') {
            return '';
        }

        $clientId = (string) setting('api_gmb_client_id', '', $userId);
        $clientSecret = (string) setting('api_gmb_client_secret', '', $userId);
        if ($clientId === '' || $clientSecret === '') {
            return '';
        }

        $payload = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ];

        $refresh = $this->httpPostForm(self::GOOGLE_TOKEN_URL, $payload);
        if (!$refresh['ok']) {
            return '';
        }

        $data = $refresh['data'];
        $newToken = (string) ($data['access_token'] ?? '');
        if ($newToken === '') {
            return '';
        }

        setting_set('api_gmb_access_token', $newToken, $userId);
        setting_set('api_gmb_token_expires', (string) (time() + (int) ($data['expires_in'] ?? 3600)), $userId);

        return $newToken;
    }

    private function httpPostForm(string $url, array $payload): array
    {
        return $this->httpRequest(
            $url,
            'POST',
            ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query($payload)
        );
    }

    private function httpRequest(string $url, string $method, array $headers = [], string $body = ''): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        $statusCode = 0;

        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', (string) $http_response_header[0], $m)) {
            $statusCode = (int) $m[1];
        }

        $data = json_decode((string) $raw, true);
        $data = is_array($data) ? $data : [];

        if ($statusCode < 200 || $statusCode >= 300) {
            $error = (string) ($data['error']['message'] ?? $data['error_description'] ?? $data['error'] ?? 'Erreur HTTP Google API');
            return ['ok' => false, 'error' => $error, 'status' => $statusCode, 'data' => $data];
        }

        return ['ok' => true, 'status' => $statusCode, 'data' => $data];
    }

    private function buildOAuthRedirectUri(): string
    {
        $scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return $scheme . '://' . $host . '/admin/api/settings/gmb-callback.php';
    }

    private function ensureInfrastructureTables(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS gmb_sync_jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            job_type VARCHAR(64) NOT NULL,
            payload_json JSON DEFAULT NULL,
            status ENUM("queued","running","done","failed") NOT NULL DEFAULT "queued",
            attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            available_at DATETIME NOT NULL,
            started_at DATETIME DEFAULT NULL,
            finished_at DATETIME DEFAULT NULL,
            last_error VARCHAR(1000) DEFAULT NULL,
            result_json JSON DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status_available (status, available_at),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS gmb_sync_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            job_type VARCHAR(64) NOT NULL,
            status ENUM("done","failed") NOT NULL,
            message VARCHAR(1000) DEFAULT NULL,
            crawl_score TINYINT UNSIGNED DEFAULT NULL,
            reviews_synced INT UNSIGNED DEFAULT NULL,
            synced_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_synced (user_id, synced_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
}
