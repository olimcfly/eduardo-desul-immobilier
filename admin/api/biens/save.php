<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.',
        'errors' => ['method' => 'Utilisez une requête POST.'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!Auth::check() || !Auth::isAdmin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Accès refusé.',
        'errors' => ['auth' => 'Vous devez être connecté en tant qu\'administrateur.'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Token CSRF invalide.',
        'errors' => ['csrf_token' => 'Le token CSRF est invalide ou expiré.'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = $_POST;
if ($payload === []) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$errors = [];
$data = normalizeBienPayload($payload, $errors);

if ($errors !== []) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Le formulaire contient des erreurs.',
        'errors' => $errors,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();
$id = isset($payload['id']) && ctype_digit((string) $payload['id']) ? (int) $payload['id'] : null;

try {
    if (!isReferenceUnique($pdo, $data['reference'], $id)) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'La référence existe déjà.',
            'errors' => ['reference' => 'Cette référence est déjà utilisée par un autre bien.'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $slug = buildUniqueSlug($pdo, slugify($data['title']), $id);

    $pdo->beginTransaction();

    if ($id !== null) {
        $exists = $pdo->prepare('SELECT id FROM biens WHERE id = :id LIMIT 1');
        $exists->execute([':id' => $id]);
        if (!$exists->fetchColumn()) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Bien introuvable.',
                'errors' => ['id' => 'Impossible de modifier un bien inexistant.'],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $sql = 'UPDATE biens SET
            slug = :slug,
            reference = :reference,
            titre = :titre,
            type_transaction = :type_transaction,
            type_bien = :type_bien,
            prix = :prix,
            surface = :surface,
            pieces = :pieces,
            chambres = :chambres,
            sdb = :sdb,
            etage = :etage,
            adresse = :adresse,
            ville = :ville,
            code_postal = :code_postal,
            secteur = :secteur,
            latitude = :latitude,
            longitude = :longitude,
            description = :description,
            caracteristiques = :caracteristiques,
            dpe_classe = :dpe_classe,
            mode_chauffage = :mode_chauffage,
            visite_virtuelle_url = :visite_virtuelle_url,
            annee_construction = :annee_construction,
            statut = :statut,
            etat_bien = :etat_bien,
            exclusif = :exclusif,
            a_parking = :a_parking,
            a_jardin = :a_jardin,
            a_piscine = :a_piscine,
            a_terrasse = :a_terrasse,
            a_balcon = :a_balcon,
            a_ascenseur = :a_ascenseur
        WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(buildSqlPayload($data, $slug) + [':id' => $id]);
        $savedId = $id;
        $message = 'Bien mis à jour avec succès.';
    } else {
        $sql = 'INSERT INTO biens (
            slug, reference, titre, type_transaction, type_bien, prix, surface, pieces, chambres, sdb,
            etage, adresse, ville, code_postal, secteur, latitude, longitude, description,
            caracteristiques, dpe_classe, mode_chauffage, visite_virtuelle_url, annee_construction,
            statut, etat_bien, exclusif, a_parking, a_jardin, a_piscine, a_terrasse, a_balcon, a_ascenseur
        ) VALUES (
            :slug, :reference, :titre, :type_transaction, :type_bien, :prix, :surface, :pieces, :chambres, :sdb,
            :etage, :adresse, :ville, :code_postal, :secteur, :latitude, :longitude, :description,
            :caracteristiques, :dpe_classe, :mode_chauffage, :visite_virtuelle_url, :annee_construction,
            :statut, :etat_bien, :exclusif, :a_parking, :a_jardin, :a_piscine, :a_terrasse, :a_balcon, :a_ascenseur
        )';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(buildSqlPayload($data, $slug));
        $savedId = (int) $pdo->lastInsertId();
        $message = 'Bien créé avec succès.';
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'id' => $savedId,
            'slug' => $slug,
            'reference' => $data['reference'],
            'title' => $data['title'],
            'status' => $data['status'],
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur pendant la sauvegarde du bien.',
        'errors' => ['server' => APP_DEBUG ? $e->getMessage() : 'Une erreur est survenue.'],
    ], JSON_UNESCAPED_UNICODE);
}

function normalizeBienPayload(array $input, array &$errors): array
{
    $required = [
        'title' => 'Le titre est requis.',
        'reference' => 'La référence est requise.',
        'type' => 'Le type de bien est requis.',
        'status' => 'Le statut est requis.',
        'price' => 'Le prix est requis.',
        'surface' => 'La surface est requise.',
        'description' => 'La description est requise.',
    ];

    foreach ($required as $field => $message) {
        if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
            $errors[$field] = $message;
        }
    }

    $allowedTypes = ['appartement', 'maison', 'terrain', 'local', 'immeuble', 'autre'];
    $allowedStatuses = ['actif', 'pending', 'vendu', 'archive'];
    $allowedTransactions = ['vente', 'location'];
    $allowedConditions = ['new', 'renovated', 'good', 'to_renovate'];

    $title = trim((string) ($input['title'] ?? ''));
    $reference = trim((string) ($input['reference'] ?? ''));
    $type = trim((string) ($input['type'] ?? ''));
    $status = trim((string) ($input['status'] ?? ''));

    if ($type !== '' && !in_array($type, $allowedTypes, true)) {
        $errors['type'] = 'Le type de bien est invalide.';
    }

    if ($status !== '' && !in_array($status, $allowedStatuses, true)) {
        $errors['status'] = 'Le statut est invalide.';
    }

    $transactionType = trim((string) ($input['transaction_type'] ?? 'vente'));
    if (!in_array($transactionType, $allowedTransactions, true)) {
        $errors['transaction_type'] = 'Le type de transaction est invalide.';
    }

    $condition = trim((string) ($input['condition'] ?? 'good'));
    if (!in_array($condition, $allowedConditions, true)) {
        $errors['condition'] = 'L\'état du bien est invalide.';
    }

    $price = filter_var($input['price'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($price === false || $price <= 0) {
        $errors['price'] = 'Le prix doit être un nombre supérieur à 0.';
    }

    $surface = filter_var($input['surface'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($surface === false || $surface <= 0) {
        $errors['surface'] = 'La surface doit être un nombre supérieur à 0.';
    }

    $rooms = normalizeOptionalInt($input['rooms'] ?? null, 'rooms', $errors, 0);
    $bedrooms = normalizeOptionalInt($input['bedrooms'] ?? null, 'bedrooms', $errors, 0);
    $bathrooms = normalizeOptionalInt($input['bathrooms'] ?? null, 'bathrooms', $errors, 0);
    $floor = normalizeOptionalInt($input['floor'] ?? null, 'floor', $errors, -5);

    $lat = normalizeOptionalFloat($input['lat'] ?? null, 'lat', $errors, -90, 90);
    $lng = normalizeOptionalFloat($input['lng'] ?? null, 'lng', $errors, -180, 180);

    $yearBuilt = normalizeOptionalInt($input['year_built'] ?? null, 'year_built', $errors, 1800, (int) date('Y') + 1);

    $energy = strtoupper(trim((string) ($input['energy_rating'] ?? '')));
    if ($energy !== '' && !preg_match('/^[A-G]$/', $energy)) {
        $errors['energy_rating'] = 'La classe énergie doit être comprise entre A et G.';
    }

    $virtualTour = trim((string) ($input['virtual_tour_url'] ?? ''));
    if ($virtualTour !== '' && filter_var($virtualTour, FILTER_VALIDATE_URL) === false) {
        $errors['virtual_tour_url'] = 'L\'URL de visite virtuelle est invalide.';
    }

    $featuresInput = $input['features'] ?? [];
    if (is_string($featuresInput)) {
        $featuresInput = array_filter(array_map('trim', explode(',', $featuresInput)));
    }

    if (!is_array($featuresInput)) {
        $errors['features'] = 'Les caractéristiques doivent être une liste.';
        $featuresInput = [];
    }

    $features = [];
    foreach ($featuresInput as $feature) {
        $feature = trim((string) $feature);
        if ($feature !== '') {
            $features[] = mb_substr($feature, 0, 80);
        }
    }

    return [
        'title' => mb_substr($title, 0, 255),
        'reference' => mb_substr($reference, 0, 50),
        'type' => $type,
        'status' => $status,
        'transaction_type' => $transactionType,
        'price' => (float) $price,
        'surface' => (float) $surface,
        'rooms' => $rooms,
        'bedrooms' => $bedrooms,
        'bathrooms' => $bathrooms,
        'description' => trim((string) ($input['description'] ?? '')),
        'address' => mb_substr(trim((string) ($input['address'] ?? '')), 0, 255),
        'city' => mb_substr(trim((string) ($input['city'] ?? '')), 0, 100),
        'postal_code' => mb_substr(trim((string) ($input['postal_code'] ?? '')), 0, 10),
        'department' => mb_substr(trim((string) ($input['department'] ?? '')), 0, 100),
        'lat' => $lat,
        'lng' => $lng,
        'energy_rating' => $energy !== '' ? $energy : null,
        'heating' => mb_substr(trim((string) ($input['heating'] ?? '')), 0, 100),
        'virtual_tour_url' => $virtualTour !== '' ? $virtualTour : null,
        'year_built' => $yearBuilt,
        'condition' => $condition,
        'features' => array_values(array_unique($features)),
        'floor' => $floor,
        'parking' => toBoolInt($input['parking'] ?? false),
        'garden' => toBoolInt($input['garden'] ?? false),
        'pool' => toBoolInt($input['pool'] ?? false),
        'terrace' => toBoolInt($input['terrace'] ?? false),
        'balcony' => toBoolInt($input['balcony'] ?? false),
        'elevator' => toBoolInt($input['elevator'] ?? false),
        'is_featured' => toBoolInt($input['is_featured'] ?? false),
    ];
}

function buildSqlPayload(array $data, string $slug): array
{
    return [
        ':slug' => $slug,
        ':reference' => $data['reference'],
        ':titre' => $data['title'],
        ':type_transaction' => $data['transaction_type'],
        ':type_bien' => $data['type'],
        ':prix' => $data['price'],
        ':surface' => $data['surface'],
        ':pieces' => $data['rooms'],
        ':chambres' => $data['bedrooms'],
        ':sdb' => $data['bathrooms'],
        ':etage' => $data['floor'],
        ':adresse' => $data['address'] !== '' ? $data['address'] : null,
        ':ville' => $data['city'] !== '' ? $data['city'] : null,
        ':code_postal' => $data['postal_code'] !== '' ? $data['postal_code'] : null,
        ':secteur' => $data['department'] !== '' ? $data['department'] : null,
        ':latitude' => $data['lat'],
        ':longitude' => $data['lng'],
        ':description' => $data['description'],
        ':caracteristiques' => json_encode($data['features'], JSON_UNESCAPED_UNICODE),
        ':dpe_classe' => $data['energy_rating'],
        ':mode_chauffage' => $data['heating'] !== '' ? $data['heating'] : null,
        ':visite_virtuelle_url' => $data['virtual_tour_url'],
        ':annee_construction' => $data['year_built'],
        ':statut' => $data['status'],
        ':etat_bien' => $data['condition'],
        ':exclusif' => $data['is_featured'],
        ':a_parking' => $data['parking'],
        ':a_jardin' => $data['garden'],
        ':a_piscine' => $data['pool'],
        ':a_terrasse' => $data['terrace'],
        ':a_balcon' => $data['balcony'],
        ':a_ascenseur' => $data['elevator'],
    ];
}

function normalizeOptionalInt(mixed $value, string $field, array &$errors, int $min, ?int $max = null): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    if (!is_numeric($value) || (string) (int) $value !== (string) $value) {
        $errors[$field] = 'Valeur entière invalide.';
        return null;
    }

    $int = (int) $value;
    if ($int < $min || ($max !== null && $int > $max)) {
        $errors[$field] = 'Valeur hors plage.';
        return null;
    }

    return $int;
}

function normalizeOptionalFloat(mixed $value, string $field, array &$errors, float $min, float $max): ?float
{
    if ($value === null || $value === '') {
        return null;
    }

    $float = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($float === false || $float < $min || $float > $max) {
        $errors[$field] = 'Valeur décimale invalide.';
        return null;
    }

    return (float) $float;
}

function toBoolInt(mixed $value): int
{
    if (is_bool($value)) {
        return $value ? 1 : 0;
    }

    $truthy = ['1', 'true', 'on', 'yes', 'oui'];

    return in_array(mb_strtolower(trim((string) $value)), $truthy, true) ? 1 : 0;
}

function isReferenceUnique(PDO $pdo, string $reference, ?int $excludeId = null): bool
{
    $sql = 'SELECT id FROM biens WHERE reference = :reference';
    $params = [':reference' => $reference];

    if ($excludeId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $excludeId;
    }

    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return !$stmt->fetchColumn();
}

function buildUniqueSlug(PDO $pdo, string $baseSlug, ?int $excludeId = null): string
{
    $slug = $baseSlug !== '' ? $baseSlug : 'bien';
    $candidate = $slug;
    $index = 1;

    while (slugExists($pdo, $candidate, $excludeId)) {
        $index++;
        $candidate = $slug . '-' . $index;
    }

    return $candidate;
}

function slugExists(PDO $pdo, string $slug, ?int $excludeId = null): bool
{
    $sql = 'SELECT id FROM biens WHERE slug = :slug';
    $params = [':slug' => $slug];

    if ($excludeId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $excludeId;
    }

    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (bool) $stmt->fetchColumn();
}
