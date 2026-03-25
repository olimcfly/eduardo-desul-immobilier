<?php
/**
 * API — Guide Local Prospection partenaires (Perplexity + CRM)
 * /admin/api/content/guide-local-prospection.php
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session expirée']);
    exit;
}

define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
require_once ROOT_PATH . '/includes/classes/Database.php';
require_once ROOT_PATH . '/includes/functions/api-keys.php';

$db = Database::getInstance();
$action = $_POST['action'] ?? '';

function glpJsonDecode(string $raw): array {
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function glpExtractJson(string $text): array {
    $json = trim($text);
    if (preg_match('/```json\s*(\[.*\])\s*```/is', $json, $m)) {
        $json = $m[1];
    } elseif (preg_match('/(\[.*\])/is', $json, $m)) {
        $json = $m[1];
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function glpPerplexitySearch(string $city, string $category, int $limit): array {
    $key = get_perplexity_key();
    if (!$key) {
        return ['success' => false, 'error' => 'Clé Perplexity absente. Activez-la dans Paramètres > API Keys.'];
    }

    $prompt = "Trouve {$limit} partenaires locaux pertinents pour un agent immobilier à {$city}, catégorie {$category}.\n"
        . "Réponds uniquement en JSON valide (tableau) sans texte avant/après.\n"
        . "Chaque objet doit contenir: nom, categorie, adresse, ville, site_web, email, telephone, raison.\n"
        . "Contraintes: email et telephone si trouvables publiquement, sinon chaîne vide.";

    $payload = [
        'model' => 'sonar-pro',
        'temperature' => 0.2,
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un assistant de prospection locale. Retour JSON strict uniquement.'],
            ['role' => 'user', 'content' => $prompt],
        ],
    ];

    $ch = curl_init('https://api.perplexity.ai/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $key,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 45,
    ]);
    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($res === false || $err) {
        return ['success' => false, 'error' => 'Erreur réseau Perplexity: ' . $err];
    }

    $json = glpJsonDecode($res);
    if ($code >= 400 || empty($json)) {
        $msg = $json['error']['message'] ?? 'Réponse invalide Perplexity';
        return ['success' => false, 'error' => $msg];
    }

    $content = (string)($json['choices'][0]['message']['content'] ?? '');
    $rows = glpExtractJson($content);
    if (empty($rows)) {
        return ['success' => false, 'error' => 'Aucun résultat exploitable retourné par Perplexity.'];
    }

    $clean = [];
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $name = trim((string)($row['nom'] ?? ''));
        if ($name === '') continue;
        $clean[] = [
            'nom' => $name,
            'categorie' => trim((string)($row['categorie'] ?? $category)),
            'adresse' => trim((string)($row['adresse'] ?? '')),
            'ville' => trim((string)($row['ville'] ?? $city)),
            'site_web' => trim((string)($row['site_web'] ?? '')),
            'email' => trim((string)($row['email'] ?? '')),
            'telephone' => trim((string)($row['telephone'] ?? '')),
            'raison' => trim((string)($row['raison'] ?? 'Partenaire local pertinent')),
        ];
        if (count($clean) >= $limit) break;
    }

    return ['success' => true, 'partners' => $clean];
}

function glpEnsureCrmActionTable(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS crm_partner_actions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contact_id INT NULL,
        partner_name VARCHAR(255) NOT NULL,
        preferred_contact_method ENUM('email','telephone','both') DEFAULT 'email',
        channel_suggestion VARCHAR(80) NOT NULL,
        action_note TEXT NULL,
        status ENUM('todo','done') DEFAULT 'todo',
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_contact_id (contact_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

if ($action === 'search_partners') {
    $city = trim((string)($_POST['city'] ?? 'Bordeaux'));
    $category = trim((string)($_POST['category'] ?? 'services locaux'));
    $limit = max(3, min(20, (int)($_POST['limit'] ?? 8)));
    echo json_encode(glpPerplexitySearch($city, $category, $limit), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'create_crm_actions') {
    $rows = glpJsonDecode((string)($_POST['partners'] ?? '[]'));
    $method = (string)($_POST['preferred_contact_method'] ?? 'email');
    $method = in_array($method, ['email', 'telephone', 'both'], true) ? $method : 'email';

    if (empty($rows)) {
        echo json_encode(['success' => false, 'error' => 'Aucun partenaire sélectionné.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        glpEnsureCrmActionTable($db);
        $db->beginTransaction();
        $created = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) continue;
            $name = trim((string)($row['nom'] ?? ''));
            if ($name === '') continue;

            $email = trim((string)($row['email'] ?? ''));
            $phone = trim((string)($row['telephone'] ?? ''));
            $city = trim((string)($row['ville'] ?? ''));
            $company = $name;

            $contactId = 0;
            if ($email !== '') {
                $s = $db->prepare("SELECT id FROM contacts WHERE email = ? LIMIT 1");
                $s->execute([$email]);
                $contactId = (int)($s->fetchColumn() ?: 0);
            }
            if ($contactId === 0 && $phone !== '') {
                $s = $db->prepare("SELECT id FROM contacts WHERE phone = ? LIMIT 1");
                $s->execute([$phone]);
                $contactId = (int)($s->fetchColumn() ?: 0);
            }

            if ($contactId === 0) {
                $ins = $db->prepare("INSERT INTO contacts
                    (first_name, last_name, email, phone, company, source, status, pipeline_stage, notes, gdpr_consent)
                    VALUES (?, ?, ?, ?, ?, 'guide_local_perplexity', 'new', 'contacted', ?, 0)");
                $ins->execute([$name, '', $email ?: null, $phone ?: null, $company, 'Prospect importé depuis Guide Local IA']);
                $contactId = (int)$db->lastInsertId();
            } else {
                $upd = $db->prepare("UPDATE contacts SET company = COALESCE(NULLIF(company,''), ?), phone = COALESCE(NULLIF(phone,''), ?)
                                     WHERE id = ?");
                $upd->execute([$company, $phone ?: null, $contactId]);
            }

            $channel = $method === 'both' ? 'Email + Téléphone' : ($method === 'telephone' ? 'Téléphone' : 'Email');
            $note = "Prospection partenaire local ({$channel})\n"
                . "Ville: {$city}\n"
                . "Raison: " . trim((string)($row['raison'] ?? '')) . "\n"
                . "Message conseillé: Bonjour {$name}, je vous propose un partenariat local (recommandation mutuelle).";

            $a = $db->prepare("INSERT INTO crm_partner_actions
                (contact_id, partner_name, preferred_contact_method, channel_suggestion, action_note, created_by)
                VALUES (?, ?, ?, ?, ?, ?)");
            $a->execute([
                $contactId ?: null,
                $name,
                $method,
                $channel,
                $note,
                (int)($_SESSION['admin_id'] ?? 0) ?: null
            ]);
            $created++;
        }

        $db->commit();
        echo json_encode([
            'success' => true,
            'created_actions' => $created,
            'message' => "{$created} action(s) CRM créée(s)."
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Action non reconnue'], JSON_UNESCAPED_UNICODE);
