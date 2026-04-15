<?php
// public/api/demande-financement.php
declare(strict_types=1);

// Charge bootstrap (session, constantes, autoloaders...)
require_once __DIR__ . '/../../core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// On n'accepte que POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Récupère le corps JSON
$raw = file_get_contents('php://input');
$payload = json_decode((string) $raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Payload JSON invalide.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// CSRF
$csrf = (string) ($payload['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'message' => 'Session expirée, rechargez la page.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Helper : nettoyage simple
function cleanStr($v): ?string {
    if ($v === null) return null;
    $s = trim((string) $v);
    return $s === '' ? null : $s;
}

// Récupère et nettoie champs attendus
$fullname = cleanStr($payload['fullname'] ?? null);
$email = cleanStr($payload['email'] ?? null);
$phone = cleanStr($payload['phone'] ?? null);
$project_type = cleanStr($payload['project_type'] ?? null);
$amount = isset($payload['amount']) && is_numeric($payload['amount']) ? (float) $payload['amount'] : null;
$surface = isset($payload['surface']) && is_numeric($payload['surface']) ? (float) $payload['surface'] : null;
$city = cleanStr($payload['city'] ?? null);
$message = cleanStr($payload['message'] ?? null);
$source = cleanStr($payload['source'] ?? 'page_financement');

// Validation minimale
$errors = [];

if ($fullname === null) {
    $errors[] = 'Le nom et prénom sont requis.';
}
if ($email === null) {
    $errors[] = 'L\'email est requis.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email invalide.';
}
if ($phone === null) {
    $errors[] = 'Le téléphone est requis.';
}

// Si erreurs -> 422
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
}

// Prépare l'enregistrement
$now = (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d H:i:s');

$record = [
    'fullname' => $fullname,
    'email' => $email,
    'phone' => $phone,
    'project_type' => $project_type,
    'amount' => $amount,
    'surface' => $surface,
    'city' => $city,
    'message' => $message,
    'source' => $source,
    'created_at' => $now,
];

// Essayer d'enregistrer en base de données si DSN configuré
// Configure ces constantes dans core/bootstrap.php ou remplace ici par tes valeurs
$dbDsn = defined('APP_DB_DSN') ? APP_DB_DSN : null;
$dbUser = defined('APP_DB_USER') ? APP_DB_USER : null;
$dbPass = defined('APP_DB_PASS') ? APP_DB_PASS : null;

$saved = false;
$insertId = null;

if ($dbDsn) {
    try {
        $pdo = new PDO($dbDsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // Structure SQL attendue : table demandes_financement (id, fullname, email, phone, project_type, amount, surface, city, message, source, status, created_at)
        $stmt = $pdo->prepare('
            INSERT INTO demandes_financement
            (fullname, email, phone, project_type, amount, surface, city, message, source, status, created_at)
            VALUES
            (:fullname, :email, :phone, :project_type, :amount, :surface, :city, :message, :source, :status, :created_at)
        ');

        $status = 'new';
        $stmt->execute([
            ':fullname' => $record['fullname'],
            ':email' => $record['email'],
            ':phone' => $record['phone'],
            ':project_type' => $record['project_type'],
            ':amount' => $record['amount'],
            ':surface' => $record['surface'],
            ':city' => $record['city'],
            ':message' => $record['message'],
            ':source' => $record['source'],
            ':status' => $status,
            ':created_at' => $record['created_at'],
        ]);

        $insertId = (int) $pdo->lastInsertId();
        $saved = true;
    } catch (Throwable $e) {
        // Ne pas exposer l'exception au client ; journaliser côté serveur si possible
        error_log('demande-financement DB error: ' . $e->getMessage());
        $saved = false;
    }
}

// Si pas de DB ou échec, journaliser en fichier
if (!$saved) {
    try {
        $storageDir = ROOT_PATH . '/storage';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0750, true);
        }
        $logFile = $storageDir . '/demandes_financement.log';
        $entry = json_encode([
            'created_at' => $record['created_at'],
            'data' => $record
        ], JSON_UNESCAPED_UNICODE);
        file_put_contents($logFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
        $saved = true;
    } catch (Throwable $e) {
        error_log('demande-financement file error: ' . $e->getMessage());
        $saved = false;
    }
}

// Optionnel : envoyer une notification e-mail à l'admin si configuré
// Configure ADMIN_ALERT_EMAIL dans bootstrap.php si tu veux cette fonctionnalité
if ($saved && defined('ADMIN_ALERT_EMAIL') && filter_var(ADMIN_ALERT_EMAIL, FILTER_VALIDATE_EMAIL)) {
    $subject = 'Nouvelle demande de financement — ' . $record['fullname'];
    $body = "Nouvelle demande reçue via {$record['source']}.\n\n"
        . "Nom : {$record['fullname']}\n"
        . "Email : {$record['email']}\n"
        . "Téléphone : {$record['phone']}\n"
        . "Type de projet : {$record['project_type']}\n"
        . "Montant : " . ($record['amount'] !== null ? $record['amount'] : 'N/A') . "\n"
        . "Surface : " . ($record['surface'] !== null ? $record['surface'] : 'N/A') . "\n"
        . "Ville : " . ($record['city'] ?? 'N/A') . "\n\n"
        . "Message : " . ($record['message'] ?? '') . "\n\n"
        . "Reçu le : {$record['created_at']}\n";

    // En-têtes basiques
    $headers = 'From: noreply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n"
             . 'Reply-To: ' . $record['email'] . "\r\n"
             . 'Content-Type: text/plain; charset=utf-8' . "\r\n";

    // Ne pas bloquer l'exécution : suppression d'erreur potentielle
    @mail(ADMIN_ALERT_EMAIL, $subject, $body, $headers);
}

// Si tout ok -> réponse 201
if ($saved) {
    http_response_code(201);
    echo json_encode([
        'ok' => true,
        'message' => 'Demande reçue.',
        'id' => $insertId,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sinon erreur serveur
http_response_code(500);
echo json_encode(['ok' => false, 'message' => 'Impossible d\'enregistrer la demande.'], JSON_UNESCAPED_UNICODE);
exit;
