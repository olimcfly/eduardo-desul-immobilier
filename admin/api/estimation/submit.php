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
    $errors['email'] = 'Email invalide (ex: user@example.com)';
}

// Validation téléphone français (plus stricte)
if (!empty($data['phone'])) {
    $phone_clean = preg_replace('/[\s\-\+\.]/', '', $data['phone']);
    if (!preg_match('/^(?:(?:\+|00)33|0)[1-9](?:[0-9]{8})$/', $phone_clean)) {
        $errors['phone'] = 'Téléphone invalide (format: 06 12 34 56 78 ou +33 6 12 34 56 78)';
    }
}

// Validation surface
if (!empty($data['surface'])) {
    if (!is_numeric($data['surface']) || (float)$data['surface'] <= 0) {
        $errors['surface'] = 'Surface doit être un nombre positif';
    } elseif ((float)$data['surface'] > 10000) {
        $errors['surface'] = 'Surface invalide (maximum 10 000 m²)';
    }
}

// Validation adresse
if (!empty($data['address']) && strlen($data['address']) < 5) {
    $errors['address'] = 'Adresse doit faire au moins 5 caractères';
}

// Validation type de bien
$valid_property_types = ['maison', 'appartement', 'terrain', 'commercial', 'bureau'];
if (!empty($data['property_type']) && !in_array(strtolower($data['property_type']), $valid_property_types)) {
    $errors['property_type'] = 'Type de bien invalide';
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
// Fonction: Calculer estimation au m²
// ─────────────────────────────────────────────────────────────

/**
 * Calcule une estimation de prix basée sur:
 * - Type de bien
 * - Surface
 * - Nombre de pièces
 * - Année de construction
 * - Région (par défaut Bordeaux/Aquitaine)
 */
function calculatePropertyEstimate($property_type, $surface, $rooms, $year_built) {
    // Prix de base au m² par type de bien (marché Bordeaux/région)
    $base_prices = [
        'maison'      => 4500,      // €/m²
        'appartement' => 4200,      // €/m²
        'terrain'     => 800,       // €/m² (plus bas pour terrain)
        'commercial'  => 5000,      // €/m²
        'bureau'      => 3500       // €/m²
    ];

    $type_lower = strtolower($property_type);
    $base_price = $base_prices[$type_lower] ?? 4000;

    // Ajustement par année de construction
    $year_adjust = 1.0;
    if ($year_built > 0) {
        $age = date('Y') - $year_built;

        if ($age < 10) {
            $year_adjust = 1.15;  // Neuf/récent +15%
        } elseif ($age < 25) {
            $year_adjust = 1.05;  // Récent +5%
        } elseif ($age < 50) {
            $year_adjust = 0.95;  // Normal -5%
        } elseif ($age < 80) {
            $year_adjust = 0.85;  // Ancien -15%
        } else {
            $year_adjust = 0.75;  // Très ancien -25%
        }
    }

    // Ajustement par nombre de pièces
    $rooms_adjust = 1.0;
    if ($rooms > 0) {
        if ($rooms >= 5) {
            $rooms_adjust = 1.10;  // Plus pièces = plus cher
        } elseif ($rooms >= 4) {
            $rooms_adjust = 1.05;
        }
    }

    // Ajustement par surface (économies d'échelle)
    $surface_adjust = 1.0;
    if ($surface > 300) {
        $surface_adjust = 1.05;  // Grandes surfaces +5%
    } elseif ($surface < 50) {
        $surface_adjust = 1.08;  // Petites surfaces +8% (au m²)
    }

    // Calcul final
    $price_per_sqm = $base_price * $year_adjust * $rooms_adjust * $surface_adjust;
    $total_price = $price_per_sqm * $surface;

    return [
        'price_per_sqm' => round($price_per_sqm, 0),
        'total_price' => round($total_price, 0),
        'min_price' => round($total_price * 0.85, 0),    // -15% pour fourchette basse
        'max_price' => round($total_price * 1.15, 0)     // +15% pour fourchette haute
    ];
}

// ─────────────────────────────────────────────────────────────
// Connexion DB et traitement
// ─────────────────────────────────────────────────────────────

if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
try {
    // Charger la classe Database
    require_once ROOT_PATH . '/includes/classes/Database.php';

    $db = Database::getInstance();

    // Charger le modèle Lead
    require_once ROOT_PATH . '/includes/classes/Lead.php';

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
    // 1. Créer le lead via le modèle Lead::createFromEstimation()
    // ─────────────────────────────────────────────────────────────

    $leadModel = new Lead();

    $lead_result = $leadModel->createFromEstimation([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'property_type' => $property_type,
        'address' => $address,
        'surface' => $surface,
        'rooms' => $rooms,
        'year_built' => $year_built
    ]);

    if (!$lead_result) {
        throw new Exception('Failed to create lead');
    }

    $lead_id = $lead_result['id'];
    $is_new_lead = $lead_result['created'] ?? false;

    // ─────────────────────────────────────────────────────────────
    // 2. Calculer l'estimation au m²
    // ─────────────────────────────────────────────────────────────

    $estimation_calc = calculatePropertyEstimate(
        $property_type,
        $surface,
        $rooms,
        $year_built
    );

    // ─────────────────────────────────────────────────────────────
    // 3. Créer/mettre à jour la table estimations
    // ─────────────────────────────────────────────────────────────

    // Créer la table si n'existe pas
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS estimations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                lead_id INT NOT NULL,
                property_type VARCHAR(50) NOT NULL,
                address VARCHAR(255) NOT NULL,
                surface DECIMAL(10, 2) NOT NULL,
                rooms INT DEFAULT 0,
                year_built YEAR DEFAULT NULL,
                notes LONGTEXT,
                price_per_sqm INT DEFAULT 0,
                total_price INT DEFAULT 0,
                min_price INT DEFAULT 0,
                max_price INT DEFAULT 0,
                status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
                INDEX idx_lead (lead_id),
                INDEX idx_status (status),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } catch (Exception $e) {
        // Table existe déjà, on continue
    }

    // Insérer l'estimation
    $estimation_insert = $db->prepare(
        "INSERT INTO estimations
         (lead_id, property_type, address, surface, rooms, year_built, notes,
          price_per_sqm, total_price, min_price, max_price, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );

    $estimation_insert->execute([
        $lead_id,
        $property_type,
        $address,
        $surface,
        $rooms,
        $year_built ?: null,
        $notes,
        $estimation_calc['price_per_sqm'],
        $estimation_calc['total_price'],
        $estimation_calc['min_price'],
        $estimation_calc['max_price'],
        'pending'
    ]);

    $estimation_id = $db->lastInsertId();

    // ─────────────────────────────────────────────────────────────
    // 4. Envoyer email supplémentaire avec détails d'estimation
    //    (email de base déjà envoyé par Lead::createFromEstimation)
    // ─────────────────────────────────────────────────────────────

    // Optionnel: envoyer un email supplémentaire avec les détails d'estimation si nouveau lead
    if ($smtp_config_exists && $is_new_lead) {
        try {
            require_once ROOT_PATH . '/includes/classes/EmailService.php';

            $mailer = new EmailService();

            // Formatage des prix
            $price_display = number_format($estimation_calc['total_price'], 0, ',', ' ');
            $price_per_sqm_display = number_format($estimation_calc['price_per_sqm'], 0, ',', ' ');
            $min_price_display = number_format($estimation_calc['min_price'], 0, ',', ' ');
            $max_price_display = number_format($estimation_calc['max_price'], 0, ',', ' ');

            $email_body = "
                <h2>Détails de votre estimation</h2>
                <p>Bonjour $first_name,</p>
                <p>Voici les détails complets de l'estimation pour votre bien immobilier:</p>

                <h3>Détails du bien</h3>
                <ul>
                    <li><strong>Type:</strong> " . ucfirst($property_type) . "</li>
                    <li><strong>Adresse:</strong> $address</li>
                    <li><strong>Surface:</strong> ${surface} m²</li>
                    " . ($rooms ? "<li><strong>Pièces:</strong> $rooms</li>" : "") . "
                    " . ($year_built ? "<li><strong>Année construction:</strong> $year_built</li>" : "") . "
                </ul>

                <h3>Estimation de prix</h3>
                <table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>
                    <tr style='background: #f5f5f5;'>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>Prix par m²:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>$price_per_sqm_display €</strong></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Prix estimé:</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong style='color: #2c5f2d; font-size: 1.3em;'>$price_display €</strong></td>
                    </tr>
                    <tr style='background: #f5f5f5;'>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Fourchette basse (-15%):</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>$min_price_display €</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Fourchette haute (+15%):</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>$max_price_display €</td>
                    </tr>
                </table>

                <p style='color: #666; font-size: 0.9em;'><em>Cette estimation est basée sur les données du marché immobilier local et les caractéristiques de votre bien. Elle peut varier selon les conditions spécifiques de votre propriété.</em></p>

                <p>Notre équipe d'experts vous recontactera prochainement pour discuter des opportunités.</p>

                <p style='margin-top: 30px;'>Cordialement,<br>
                <strong>" . SITE_TITLE . "</strong></p>
            ";

            $mailer->sendEmail(
                $email,
                'Détails de votre estimation immobilière',
                $email_body,
                [
                    'from_name' => SITE_TITLE,
                    'reply_to' => ADMIN_EMAIL
                ]
            );
        } catch (Exception $e) {
            error_log('Estimation details email failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Notification admin supplémentaire avec détails d'estimation
    //    (notification de base déjà envoyée par Lead::createFromEstimation)
    // ─────────────────────────────────────────────────────────────

    // Optionnel: envoyer notification supplémentaire si SMTP configuré
    if ($smtp_config_exists && $is_new_lead) {
        try {
            $admin_body = "
                <h2>📊 Estimation détaillée - " . ucfirst($property_type) . "</h2>

                <h3>Client: $first_name $last_name</h3>
                <p><a href='mailto:$email'>$email</a> | <a href='tel:$phone'>$phone</a></p>

                <h3>Détails du bien</h3>
                <ul>
                    <li><strong>Type:</strong> " . ucfirst($property_type) . "</li>
                    <li><strong>Adresse:</strong> $address</li>
                    <li><strong>Surface:</strong> ${surface} m²</li>
                    <li><strong>Pièces:</strong> $rooms</li>
                    <li><strong>Année construction:</strong> $year_built</li>
                    " . ($notes ? "<li><strong>Notes client:</strong> $notes</li>" : "") . "
                </ul>

                <h3>Estimation calculée</h3>
                <table style='border-collapse: collapse; margin: 10px 0;'>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Prix par m²:</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'><strong>" . number_format($estimation_calc['price_per_sqm'], 0, ',', ' ') . " €</strong></td>
                    </tr>
                    <tr style='background: #f0f0f0;'>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Prix total:</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'><strong style='color: #2c5f2d; font-size: 1.1em;'>" . number_format($estimation_calc['total_price'], 0, ',', ' ') . " €</strong></td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border: 1px solid #ddd;'>Fourchette:</td>
                        <td style='padding: 8px; border: 1px solid #ddd;'>" . number_format($estimation_calc['min_price'], 0, ',', ' ') . " € à " . number_format($estimation_calc['max_price'], 0, ',', ' ') . " €</td>
                    </tr>
                </table>

                <p style='color: #666; font-size: 0.9em; margin-top: 15px;'>
                    Référence: #$estimation_id<br>
                    Date: " . date('d/m/Y à H:i') . "
                </p>
            ";

            $mailer->sendEmail(
                ADMIN_EMAIL,
                '📊 Estimation détaillée - ' . $property_type . ' - ' . SITE_TITLE,
                $admin_body,
                ['from_name' => SITE_TITLE]
            );
        } catch (Exception $e) {
            error_log('Estimation detailed admin email failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Répondre avec succès (incluant l'estimation calculée)
    // ─────────────────────────────────────────────────────────────

    http_response_code(200);
    exit(json_encode([
        'success' => true,
        'message' => 'Demande d\'estimation enregistrée avec succès',
        'data' => [
            'lead_id' => $lead_id,
            'estimation_id' => $estimation_id,
            'reference' => 'EST-' . $estimation_id . '-' . date('YmdHis'),
            'estimation' => [
                'price_per_sqm' => $estimation_calc['price_per_sqm'],
                'total_price' => $estimation_calc['total_price'],
                'min_price' => $estimation_calc['min_price'],
                'max_price' => $estimation_calc['max_price'],
                'currency' => 'EUR',
                'note' => 'Estimation fournie à titre informatif. Peut varier selon les conditions spécifiques du bien.'
            ],
            'contact' => [
                'email_sent' => true,
                'notification_sent' => true,
                'message' => 'Un email de confirmation a été envoyé. Notre équipe vous contactera dans les 24 heures.'
            ]
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
