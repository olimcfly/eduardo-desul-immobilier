<?php
/**
 * MODÈLE LEAD - Gestion des prospects/leads
 * Handles lead creation from various sources (website, estimation, events, etc.)
 */

class Lead {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Créer un lead depuis formulaire standard
     */
    public function create($data) {
        $sql = "INSERT INTO leads (email, phone, first_name, last_name, city, interest, source, capture_page_id, gdpr_consent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['city'] ?? null,
            $data['interest'] ?? null,
            $data['source'] ?? 'website',
            $data['capture_page_id'] ?? null,
            $data['gdpr_consent'] ?? 0
        ]);
    }

    /**
     * Créer un lead depuis une demande d'estimation
     *
     * @param array $estimation_data - Données du formulaire d'estimation
     * @return array|false - ['id' => lead_id, 'created' => bool] ou false si erreur
     */
    public function createFromEstimation($estimation_data) {
        try {
            // Valider les données requises
            $required = ['email', 'first_name', 'phone'];
            foreach ($required as $field) {
                if (empty($estimation_data[$field])) {
                    throw new Exception("Field required: $field");
                }
            }

            // Nettoyer et valider email
            $email = filter_var($estimation_data['email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            // Vérifier si le lead existe déjà
            $existing = $this->findByEmail($email);
            if ($existing) {
                // Le lead existe déjà, retourner son ID
                return [
                    'id' => $existing['id'],
                    'created' => false,
                    'message' => 'Lead already exists',
                    'is_duplicate' => true
                ];
            }

            // Préparer les données du lead
            $first_name = trim($estimation_data['first_name']);
            $last_name = trim($estimation_data['last_name'] ?? '');
            $phone = trim($estimation_data['phone']);
            $address = trim($estimation_data['address'] ?? '');
            $property_type = trim($estimation_data['property_type'] ?? '');
            $surface = (int) ($estimation_data['surface'] ?? 0);

            // Construire la description/intérêt
            $interest = $property_type;
            if ($surface > 0) {
                $interest .= " ($surface m²)";
            }

            // Insérer le lead
            $sql = "INSERT INTO leads
                    (first_name, last_name, email, phone, source, status, interest, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $first_name,
                $last_name,
                $email,
                $phone,
                'estimation',           // source toujours 'estimation'
                'nouveau'               // status toujours 'nouveau' pour estimation
            ]);

            if (!$result) {
                throw new Exception('Failed to insert lead');
            }

            $lead_id = $this->db->lastInsertId();

            // Envoyer email de confirmation au prospect
            $this->sendConfirmationEmail($email, $first_name, $property_type, $surface);

            // Envoyer notification à l'admin
            $this->sendAdminNotification($first_name, $last_name, $email, $phone, $estimation_data);

            return [
                'id' => $lead_id,
                'created' => true,
                'message' => 'Lead created successfully from estimation',
                'is_duplicate' => false
            ];

        } catch (Exception $e) {
            error_log('Lead::createFromEstimation - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouver un lead par email
     */
    public function findByEmail($email) {
        $sql = "SELECT id, email, first_name, last_name, status FROM leads WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Trouver un lead par ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM leads WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les leads avec filtres
     */
    public function getAll($limit = 50, $offset = 0, $status = null) {
        $sql = "SELECT * FROM leads ";
        $params = [];

        if ($status) {
            $sql .= "WHERE status = ? ";
            $params[] = $status;
        }

        $sql .= "ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter les leads
     */
    public function count($status = null) {
        $sql = "SELECT COUNT(*) as total FROM leads ";
        $params = [];

        if ($status) {
            $sql .= "WHERE status = ?";
            $params[] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Mettre à jour un lead
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];

        $allowed_fields = ['first_name', 'last_name', 'email', 'phone', 'status', 'interest', 'city'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE leads SET " . implode(', ', $updates) . " WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Envoyer un email de confirmation au prospect (depuis estimation)
     */
    private function sendConfirmationEmail($email, $first_name, $property_type, $surface) {
        try {
            if (!file_exists(ROOT_PATH . '/includes/classes/EmailService.php')) {
                return false;
            }

            require_once ROOT_PATH . '/includes/classes/EmailService.php';
            $mailer = new EmailService();

            $subject = "Merci pour votre demande d'estimation - " . SITE_TITLE;

            $body = "
                <h2>Merci pour votre intérêt!</h2>
                <p>Bonjour $first_name,</p>
                <p>Nous avons bien enregistré votre demande d'estimation pour votre bien immobilier.</p>

                <p><strong>Récapitulatif:</strong></p>
                <ul>
                    <li>Type de bien: " . ucfirst($property_type) . "</li>
                    <li>Surface: $surface m²</li>
                </ul>

                <p>Notre équipe d'experts vous contactera dans les <strong>24 heures</strong> pour discuter de votre bien et des meilleures opportunités.</p>

                <p>Cordialement,<br>
                <strong>" . SITE_TITLE . "</strong></p>
            ";

            return $mailer->sendEmail($email, $subject, $body, [
                'from_name' => SITE_TITLE,
                'reply_to' => ADMIN_EMAIL
            ]);

        } catch (Exception $e) {
            error_log('Lead::sendConfirmationEmail - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification à l'admin (nouveau lead depuis estimation)
     */
    private function sendAdminNotification($first_name, $last_name, $email, $phone, $estimation_data) {
        try {
            if (!file_exists(ROOT_PATH . '/includes/classes/EmailService.php')) {
                return false;
            }

            require_once ROOT_PATH . '/includes/classes/EmailService.php';
            $mailer = new EmailService();

            $property_type = ucfirst($estimation_data['property_type'] ?? 'Non spécifié');
            $address = $estimation_data['address'] ?? 'Non spécifiée';
            $surface = $estimation_data['surface'] ?? '0';

            $subject = "🔔 Nouveau lead - Estimation " . SITE_TITLE;

            $body = "
                <h2>Nouveau lead enregistré</h2>

                <h3>Informations client</h3>
                <ul>
                    <li><strong>Nom:</strong> $first_name $last_name</li>
                    <li><strong>Email:</strong> <a href='mailto:$email'>$email</a></li>
                    <li><strong>Téléphone:</strong> <a href='tel:$phone'>$phone</a></li>
                    <li><strong>Source:</strong> Formulaire d'estimation</li>
                </ul>

                <h3>Propriété</h3>
                <ul>
                    <li><strong>Type:</strong> $property_type</li>
                    <li><strong>Adresse:</strong> $address</li>
                    <li><strong>Surface:</strong> $surface m²</li>
                </ul>

                <p><a href='" . ADMIN_URL . "/modules/crm/leads/' style='display: inline-block; padding: 10px 20px; background: #2c5f2d; color: white; text-decoration: none; border-radius: 5px;'>
                    Voir le lead en admin
                </a></p>

                <p style='color: #666; font-size: 0.9em; margin-top: 20px;'>
                    Cet email a été généré automatiquement. Date: " . date('d/m/Y H:i') . "
                </p>
            ";

            return $mailer->sendEmail(ADMIN_EMAIL, $subject, $body, [
                'from_name' => SITE_TITLE
            ]);

        } catch (Exception $e) {
            error_log('Lead::sendAdminNotification - Error: ' . $e->getMessage());
            return false;
        }
    }
}
