<?php

class BienService
{
    private PDO $pdo;
    private array $errors = [];

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        [$whereSql, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT * FROM biens {$whereSql} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(array $filters = []): int
    {
        [$whereSql, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT COUNT(*) FROM biens {$whereSql}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM biens WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $bien = $stmt->fetch(PDO::FETCH_ASSOC);

        return $bien ?: null;
    }

    public function create(array $data): int
    {
        $this->errors = [];

        if (!$this->validateReferenceUnique((string) ($data['reference'] ?? ''))) {
            throw new RuntimeException('La référence existe déjà.');
        }

        $sql = 'INSERT INTO biens (
            slug, titre, description, type_transaction, type_bien, prix, surface, pieces, chambres, sdb,
            etage, adresse, ville, code_postal, secteur, latitude, longitude, statut,
            dpe_classe, mode_chauffage, reference, etat_bien, visite_virtuelle_url,
            exclusif, a_parking, a_jardin, a_piscine, a_terrasse, a_balcon, a_ascenseur,
            caracteristiques, annee_construction
        ) VALUES (
            :slug, :titre, :description, :type_transaction, :type_bien, :prix, :surface, :pieces, :chambres, :sdb,
            :etage, :adresse, :ville, :code_postal, :secteur, :latitude, :longitude, :statut,
            :dpe_classe, :mode_chauffage, :reference, :etat_bien, :visite_virtuelle_url,
            :exclusif, :a_parking, :a_jardin, :a_piscine, :a_terrasse, :a_balcon, :a_ascenseur,
            :caracteristiques, :annee_construction
        )';

        $payload = $this->normalizePayload($data);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($payload);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->errors = [];

        if (!$this->validateReferenceUnique((string) ($data['reference'] ?? ''), $id)) {
            throw new RuntimeException('La référence existe déjà.');
        }

        $sql = 'UPDATE biens SET
            slug = :slug,
            titre = :titre,
            description = :description,
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
            statut = :statut,
            dpe_classe = :dpe_classe,
            mode_chauffage = :mode_chauffage,
            reference = :reference,
            etat_bien = :etat_bien,
            visite_virtuelle_url = :visite_virtuelle_url,
            exclusif = :exclusif,
            a_parking = :a_parking,
            a_jardin = :a_jardin,
            a_piscine = :a_piscine,
            a_terrasse = :a_terrasse,
            a_balcon = :a_balcon,
            a_ascenseur = :a_ascenseur,
            caracteristiques = :caracteristiques,
            annee_construction = :annee_construction
        WHERE id = :id';

        $payload = $this->normalizePayload($data);
        $payload[':id'] = $id;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($payload);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM biens WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function getPropertyTypes(): array
    {
        return ['appartement', 'maison', 'terrain', 'local', 'immeuble', 'autre'];
    }

    public function getPropertyStatuses(): array
    {
        return ['actif', 'pending', 'vendu', 'archive'];
    }

    public function getFeaturesList(): array
    {
        return ['climatisation', 'fibre', 'meuble', 'cheminee', 'interphone', 'digicode'];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateReferenceUnique(string $reference, ?int $excludeId = null): bool
    {
        if ($reference === '') {
            $this->errors['reference'] = 'La référence est obligatoire.';
            return false;
        }

        $sql = 'SELECT id FROM biens WHERE reference = :reference';
        $params = [':reference' => $reference];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetchColumn()) {
            $this->errors['reference'] = 'Référence déjà utilisée.';
            return false;
        }

        return true;
    }

    private function buildWhereClause(array $filters): array
    {
        $clauses = [];
        $params = [];

        if (!empty($filters['type'])) {
            $clauses[] = 'type_bien = :type';
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['city'])) {
            $clauses[] = 'ville LIKE :city';
            $params[':city'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['status'])) {
            $clauses[] = 'statut = :status';
            $params[':status'] = $filters['status'];
        }

        if (isset($filters['min_price'])) {
            $clauses[] = 'prix >= :min_price';
            $params[':min_price'] = (float) $filters['min_price'];
        }

        if (isset($filters['max_price'])) {
            $clauses[] = 'prix <= :max_price';
            $params[':max_price'] = (float) $filters['max_price'];
        }

        $whereSql = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        return [$whereSql, $params];
    }

    private function normalizePayload(array $data): array
    {
        return [
            ':slug' => slugify((string) $data['title']),
            ':titre' => (string) $data['title'],
            ':description' => (string) ($data['description'] ?? ''),
            ':type_transaction' => (string) ($data['transaction_type'] ?? 'vente'),
            ':type_bien' => (string) $data['type'],
            ':prix' => (float) $data['price'],
            ':surface' => (float) $data['surface'],
            ':pieces' => (int) $data['rooms'],
            ':chambres' => (int) $data['bedrooms'],
            ':sdb' => (int) $data['bathrooms'],
            ':etage' => isset($data['floor']) ? (int) $data['floor'] : null,
            ':adresse' => (string) $data['address'],
            ':ville' => (string) $data['city'],
            ':code_postal' => (string) $data['postal_code'],
            ':secteur' => (string) ($data['department'] ?? ''),
            ':latitude' => (float) $data['lat'],
            ':longitude' => (float) $data['lng'],
            ':statut' => (string) $data['status'],
            ':dpe_classe' => !empty($data['energy_rating']) ? strtoupper((string) $data['energy_rating']) : null,
            ':mode_chauffage' => $data['heating'] ?? null,
            ':reference' => (string) $data['reference'],
            ':etat_bien' => (string) ($data['condition'] ?? 'good'),
            ':visite_virtuelle_url' => $data['virtual_tour_url'] ?? null,
            ':exclusif' => !empty($data['is_featured']) ? 1 : 0,
            ':a_parking' => !empty($data['parking']) ? 1 : 0,
            ':a_jardin' => !empty($data['garden']) ? 1 : 0,
            ':a_piscine' => !empty($data['pool']) ? 1 : 0,
            ':a_terrasse' => !empty($data['terrace']) ? 1 : 0,
            ':a_balcon' => !empty($data['balcony']) ? 1 : 0,
            ':a_ascenseur' => !empty($data['elevator']) ? 1 : 0,
            ':caracteristiques' => json_encode($data['features'] ?? [], JSON_UNESCAPED_UNICODE),
            ':annee_construction' => !empty($data['year_built']) ? (int) $data['year_built'] : null,
        ];
    }
}
