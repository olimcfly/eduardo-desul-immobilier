<?php
/**
 * API ENDPOINT: Formulaire d'Estimation Gratuite
 * POST /admin/api/estimation/submit
 *
 * Enregistre une demande d'estimation et crée un lead
 * Envoie confirmation email au client et notification admin
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ─────────────────────────────────────────────────────────────
// Vérifier méthode HTTP
// ─────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode([
        'success' => false,
        'error' => 'Method not allowed (POST required)'
    ]));
}

// ─────────────────────────────────────────────────────────────
// Bootstrap
// ─────────────────────────────────────────────────────────────

define('ROOT_PATH', dirname(__DIR__, 3));

try {
    require_once ROOT_PATH . '/config/config.php';
    require_once ROOT_PATH . '/config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'error' => 'Configuration error'
    ]));
}

// ─────────────────────────────────────────────────────────────
// Récupérer et valider les données
// ─────────────────────────────────────────────────────────────

$data = $_POST;
$errors = [];

// Champs requis
$required_fields = [
    'first_name' => 'Prénom',
    'email' => 'Email',
    'phone' => 'Téléphone',
    'property_type' => 'Type de bien',
    'address' => 'Adresse',
    'surface' => 'Surface (m²)'
];

foreach ($required_fields as $field => $label) {
    if (empty($data[$field])) {
        $errors[$field] = "$label est requis";
    }
}

// Validations spécifiques
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email invalide';
}

if (!empty($data['phone']) && !preg_match('/^[\d\s\-\+\.]+$/', $data['phone'])) {
    $errors['phone'] = 'Numéro de téléphone invalide';
}

if (!empty($data['surface']) && !is_numeric($data['surface'])) {
    $errors['surface'] = 'Surface doit être un nombre';
}

// Retourner les erreurs
if (!empty($errors)) {
    http_response_code(400);
    exit(json_encode([
        'success' => false,
        'errors' => $errors
    ]));
}

// ─────────────────────────────────────────────────────────────
// Connexion DB et traitement
// ─────────────────────────────────────────────────────────────

if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
try {
    $db = Database::getInstance();

    // Nettoyer les données
    $first_name = trim($data['first_name']);
    $last_name = trim($data['last_name'] ?? '');
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $phone = trim($data['phone']);
    $property_type = trim($data['property_type']);
    $address = trim($data['address']);
    $surface = (int) $data['surface'];
    $rooms = (int) ($data['rooms'] ?? 0);
    $year_built = (int) ($data['year_built'] ?? 0);
    $notes = trim($data['notes'] ?? '');

    // ─────────────────────────────────────────────────────────────
    // 1. Créer ou récupérer le lead
    // ─────────────────────────────────────────────────────────────

    $lead_check = $db->prepare(
        "SELECT id FROM leads WHERE email = ? LIMIT 1"
    );
    $lead_check->execute([$email]);
    $existing_lead = $lead_check->fetch();

    if ($existing_lead) {
        $lead_id = $existing_lead['id'];
    } else {
        // Créer nouveau lead
        $lead_insert = $db->prepare(
            "INSERT INTO leads (first_name, last_name, email, phone, source, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );

        $lead_insert->execute([
            $first_name,
            $last_name,
            $email,
            $phone,
            'estimation',  // source
            'new'          // status
        ]);

        $lead_id = $db->lastInsertId();
    }

    // ─────────────────────────────────────────────────────────────
    // 2. Créer la demande d'estimation
    // ─────────────────────────────────────────────────────────────

    // Vérifier si table estimation_requests existe
    $table_check = $db->query(
        "SHOW TABLES LIKE 'estimation_requests'"
    )->fetch();

    $estimation_id = null;

    if ($table_check) {
        $estimation_insert = $db->prepare(
            "INSERT INTO estimation_requests
             (lead_id, property_type, address, surface, rooms, year_built, notes, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $estimation_insert->execute([
            $lead_id,
            $property_type,
            $address,
            $surface,
            $rooms,
            $year_built,
            $notes,
            'pending'
        ]);

        $estimation_id = $db->lastInsertId();
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Envoyer email de confirmation au client
    // ─────────────────────────────────────────────────────────────

    $smtp_config_exists = file_exists(ROOT_PATH . '/config/smtp.php');

    if ($smtp_config_exists) {
        try {
            require_once ROOT_PATH . '/includes/classes/EmailService.php';

            $mailer = new EmailService();
            $email_body = "
                <h2>Merci pour votre demande d'estimation!</h2>
                <p>Bonjour $first_name,</p>
                <p>Nous avons bien reçu votre demande d'estimation pour votre bien immobilier.</p>
                <p><strong>Détails de votre demande:</strong></p>
                <ul>
                    <li>Type de bien: $property_type</li>
                    <li>Adresse: $address</li>
                    <li>Surface: ${surface}m²</li>
                </ul>
                <p>Notre équipe traitera votre demande dans les <strong>24 heures</strong> et vous recontactera au numéro suivant: $phone</p>
                <p>Cordialement,<br>" . SITE_TITLE . "</p>
            ";

            $mailer->sendEmail(
                $email,
                'Votre demande d\'estimation a été reçue',
                $email_body,
                [
                    'from_name' => SITE_TITLE,
                    'reply_to' => ADMIN_EMAIL
                ]
            );
        } catch (Exception $e) {
            // Log l'erreur mais ne bloque pas la réponse
            error_log('Estimation confirmation email failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Envoyer notification à l'admin
    // ─────────────────────────────────────────────────────────────

    if ($smtp_config_exists) {
        try {
            $admin_body = "
                <h2>Nouvelle demande d'estimation</h2>
                <p><strong>Client:</strong> $first_name $last_name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Téléphone:</strong> $phone</p>
                <p><strong>Bien:</strong> $property_type - $address</p>
                <p><strong>Surface:</strong> ${surface}m²</p>
                <p><strong>Pièces:</strong> $rooms</p>
                <p><strong>Année construction:</strong> $year_built</p>
                <p><strong>Notes:</strong> $notes</p>
                <p><a href='" . ADMIN_URL . "/dashboard.php?page=leads'>Voir le lead en admin</a></p>
            ";

            $mailer->sendEmail(
                ADMIN_EMAIL,
                'Nouvelle demande d\'estimation - ' . SITE_TITLE,
                $admin_body,
                ['from_name' => SITE_TITLE]
            );
        } catch (Exception $e) {
            error_log('Estimation admin notification failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Répondre avec succès
    // ─────────────────────────────────────────────────────────────

    http_response_code(200);
    exit(json_encode([
        'success' => true,
        'message' => 'Demande d\'estimation enregistrée avec succès',
        'data' => [
            'lead_id' => $lead_id,
            'estimation_id' => $estimation_id,
            'reference' => 'EST-' . $lead_id . '-' . date('YmdHis')
        ]
    ]));

} catch (Exception $e) {
    error_log('Estimation API Error: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'error' => 'Erreur serveur lors du traitement',
        'debug' => (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : null
    ]));
}

?>
