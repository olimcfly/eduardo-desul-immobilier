<?php

declare(strict_types=1);

class LandingPageController
{
    private const ALLOWED_TYPES = ['estimation', 'financement'];

    public function show(string $slug): void
    {
        $page = $this->findPage($slug);
        if (!$page) {
            $this->render404();
            return;
        }

        $errors = [];
        $old = [];
        $success = !empty($_GET['ok']);

        $template = ROOT_PATH . '/public/templates/lp/' . $page['type'] . '.php';
        if (!is_file($template)) {
            $this->render404();
            return;
        }

        require $template;
    }

    public function submit(string $slug): void
    {
        $page = $this->findPage($slug);
        if (!$page) {
            $this->render404();
            return;
        }

        $errors = [];
        $old = $_POST;

        $consent = !empty($_POST['rgpd_consent']);
        if (!$consent) {
            $errors[] = 'Veuillez accepter le consentement RGPD pour continuer.';
        }

        if ($page['type'] === 'estimation') {
            $propertyType = trim((string)($_POST['property_type'] ?? ''));
            $surface = (int)($_POST['surface'] ?? 0);
            $contact = trim((string)($_POST['contact'] ?? ''));

            if (!in_array($propertyType, ['maison', 'appartement', 'terrain'], true)) {
                $errors[] = 'Sélectionnez un type de bien valide.';
            }
            if ($surface < 8 || $surface > 2000) {
                $errors[] = 'La surface doit être comprise entre 8 et 2000 m².';
            }
            if ($contact === '' || mb_strlen($contact) < 6) {
                $errors[] = 'Renseignez un email ou un téléphone valide.';
            }
        } else {
            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $projectType = trim((string)($_POST['project_type'] ?? ''));

            if ($firstName === '' || mb_strlen($firstName) < 2) {
                $errors[] = 'Le prénom est obligatoire.';
            }
            if ($phone === '' || mb_strlen(preg_replace('/\D+/', '', $phone)) < 10) {
                $errors[] = 'Le téléphone est obligatoire pour un rappel.';
            }
            if (!in_array($projectType, ['acheter', 'renegocier', 'investir'], true)) {
                $errors[] = 'Sélectionnez un projet valide.';
            }
        }

        if ($errors) {
            $success = false;
            $template = ROOT_PATH . '/public/templates/lp/' . $page['type'] . '.php';
            require $template;
            return;
        }

        $metadata = [
            'landing_page_slug' => (string)$page['slug'],
            'landing_page_type' => (string)$page['type'],
            'utm_source' => $this->utm('utm_source', (string)($page['utm_source_default'] ?? 'google')),
            'utm_medium' => $this->utm('utm_medium'),
            'utm_campaign' => $this->utm('utm_campaign'),
            'utm_content' => $this->utm('utm_content'),
            'source_tag' => (string)$page['slug'],
        ];

        $payload = [
            'source_type' => LeadService::SOURCE_CONTACT,
            'pipeline' => LeadService::SOURCE_CONTACT,
            'stage' => 'nouveau',
            'consent' => true,
            'metadata' => array_filter($metadata, static fn($v) => $v !== '' && $v !== null),
        ];

        if ($page['type'] === 'estimation') {
            $payload['source_type'] = LeadService::SOURCE_ESTIMATION;
            $payload['pipeline'] = LeadService::SOURCE_ESTIMATION;
            $payload['intent'] = 'avis_de_valeur';
            $payload['property_type'] = trim((string)$_POST['property_type']);
            $payload['property_address'] = trim((string)($page['ville'] ?? ''));

            $contact = trim((string)$_POST['contact']);
            if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                $payload['email'] = $contact;
                $payload['phone'] = '';
            } else {
                $payload['phone'] = $contact;
                $payload['email'] = 'inconnu+' . time() . '@local.invalid';
            }

            $payload['notes'] = 'Surface estimée: ' . (int)$_POST['surface'] . ' m²';
            $payload['first_name'] = 'Prospect';
        } else {
            $payload['intent'] = trim((string)$_POST['project_type']);
            $payload['first_name'] = trim((string)$_POST['first_name']);
            $payload['phone'] = trim((string)$_POST['phone']);
            $payload['email'] = 'inconnu+' . time() . '@local.invalid';
            $payload['notes'] = trim((string)($_POST['message'] ?? ''));
        }

        LeadService::capture($payload);

        header('Location: /lp/' . rawurlencode($slug) . '?ok=1', true, 303);
        exit;
    }

    private function findPage(string $slug): ?array
    {
        $this->ensureTable();

        $slug = strtolower(trim($slug));
        if ($slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            return null;
        }

        $websiteId = $this->resolveWebsiteId();
        $stmt = db()->prepare('SELECT * FROM landing_pages WHERE website_id = :website_id AND slug = :slug AND active = 1 LIMIT 1');
        $stmt->execute([':website_id' => $websiteId, ':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        if (!in_array((string)$row['type'], self::ALLOWED_TYPES, true)) {
            return null;
        }

        return $row;
    }

    private function ensureTable(): void
    {
        db()->exec('CREATE TABLE IF NOT EXISTS landing_pages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            website_id INT UNSIGNED NOT NULL DEFAULT 1,
            slug VARCHAR(160) NOT NULL,
            type ENUM("estimation", "financement") NOT NULL DEFAULT "estimation",
            headline VARCHAR(255) NOT NULL,
            sous_titre VARCHAR(255) NOT NULL,
            ville VARCHAR(120) NOT NULL,
            advisor_name VARCHAR(180) NOT NULL DEFAULT "",
            advisor_phone VARCHAR(40) NOT NULL DEFAULT "",
            advisor_zone VARCHAR(255) NOT NULL DEFAULT "",
            advisor_photo_webp VARCHAR(255) NOT NULL DEFAULT "",
            advisor_bio TEXT NULL,
            company_name VARCHAR(180) NOT NULL DEFAULT "",
            legal_url VARCHAR(255) NOT NULL DEFAULT "/mentions-legales",
            privacy_url VARCHAR(255) NOT NULL DEFAULT "/politique-confidentialite",
            review_1_firstname VARCHAR(80) NULL,
            review_1_city VARCHAR(120) NULL,
            review_1_text VARCHAR(255) NULL,
            review_2_firstname VARCHAR(80) NULL,
            review_2_city VARCHAR(120) NULL,
            review_2_text VARCHAR(255) NULL,
            utm_source_default VARCHAR(40) NOT NULL DEFAULT "google",
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_landing_page_slug (website_id, slug),
            KEY idx_landing_page_active (website_id, active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function resolveWebsiteId(): int
    {
        if (function_exists('getSiteConfig')) {
            $config = getSiteConfig();
            if (is_array($config) && isset($config['website_id']) && (int)$config['website_id'] > 0) {
                return (int)$config['website_id'];
            }
        }

        if (!empty($_SESSION['website_id']) && (int)$_SESSION['website_id'] > 0) {
            return (int)$_SESSION['website_id'];
        }

        return 1;
    }

    private function utm(string $key, string $fallback = ''): string
    {
        return trim((string)($_POST[$key] ?? $_GET[$key] ?? $fallback));
    }

    private function render404(): void
    {
        http_response_code(404);
        echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>LP introuvable</title></head><body><h1>Landing page introuvable</h1></body></html>';
    }
}
