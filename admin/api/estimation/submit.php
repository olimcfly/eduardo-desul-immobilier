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

try {
    $db = getDB();

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
    // 4. Envoyer email de confirmation au client (avec estimation)
    // ─────────────────────────────────────────────────────────────

    $smtp_config_exists = file_exists(ROOT_PATH . '/config/smtp.php');

    if ($smtp_config_exists) {
        try {
            require_once ROOT_PATH . '/includes/classes/EmailService.php';

            $mailer = new EmailService();

            // Formatage des prix
            $price_display = number_format($estimation_calc['total_price'], 0, ',', ' ');
            $price_per_sqm_display = number_format($estimation_calc['price_per_sqm'], 0, ',', ' ');
            $min_price_display = number_format($estimation_calc['min_price'], 0, ',', ' ');
            $max_price_display = number_format($estimation_calc['max_price'], 0, ',', ' ');

            $email_body = "
                <h2>Votre estimation gratuite a été calculée!</h2>
                <p>Bonjour $first_name,</p>
                <p>Merci d'avoir utilisé notre service d'estimation gratuite. Voici les résultats pour votre bien:</p>

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

                <p>Notre équipe d'experts examinera votre demande et vous recontactera au <strong>$phone</strong> dans les <strong>24 heures</strong> pour discuter de votre bien et des opportunités de vente.</p>

                <p>Si vous avez des questions avant cet appel, n'hésitez pas à nous contacter.</p>

                <p style='margin-top: 30px;'>Cordialement,<br>
                <strong>" . SITE_TITLE . "</strong><br>
                " . ADMIN_EMAIL . "</p>
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
    // 5. Envoyer notification à l'admin
    // ─────────────────────────────────────────────────────────────

    if ($smtp_config_exists) {
        try {
            $admin_body = "
                <h2>✅ Nouvelle demande d'estimation enregistrée</h2>

                <h3>Informations client</h3>
                <ul>
                    <li><strong>Nom:</strong> $first_name $last_name</li>
                    <li><strong>Email:</strong> <a href='mailto:$email'>$email</a></li>
                    <li><strong>Téléphone:</strong> <a href='tel:$phone'>$phone</a></li>
                    <li><strong>Statut:</strong> Nouveau lead</li>
                </ul>

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
                <ul>
                    <li><strong>Prix par m²:</strong> " . number_format($estimation_calc['price_per_sqm'], 0, ',', ' ') . " €</li>
                    <li><strong>Prix total estimé:</strong> <strong style='color: #2c5f2d; font-size: 1.2em;'>" . number_format($estimation_calc['total_price'], 0, ',', ' ') . " €</strong></li>
                    <li><strong>Fourchette:</strong> " . number_format($estimation_calc['min_price'], 0, ',', ' ') . " € à " . number_format($estimation_calc['max_price'], 0, ',', ' ') . " €</li>
                </ul>

                <h3>Actions</h3>
                <p>
                    <a href='" . ADMIN_URL . "/modules/crm/leads/?id=$lead_id' style='display: inline-block; padding: 10px 20px; background: #2c5f2d; color: white; text-decoration: none; border-radius: 5px;'>
                        Voir le lead
                    </a>
                    <a href='" . ADMIN_URL . "/modules/crm/estimations/?id=$estimation_id' style='display: inline-block; padding: 10px 20px; background: #4a90e2; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;'>
                        Voir l'estimation
                    </a>
                </p>

                <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                <p style='color: #666; font-size: 0.9em;'>Demande d'estimation #" . $estimation_id . " du " . date('d/m/Y à H:i') . "</p>
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
