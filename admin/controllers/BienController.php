<?php

declare(strict_types=1);

class BienController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ── Liste publique ────────────────────────────────────────
    public function index(array $filters = []): array
    {
        $where  = ["b.statut <> 'archive'"];
        $params = [];

        if (!empty($filters['type'])) {
            $where[]          = 'b.type_bien = :type';
            $params[':type']  = $filters['type'];
        }
        if (!empty($filters['transaction'])) {
            $where[]                = 'b.transaction = :transaction';
            $params[':transaction'] = $filters['transaction'];
        }
        if (!empty($filters['ville'])) {
            $where[]          = 'b.ville LIKE :ville';
            $params[':ville'] = '%' . $filters['ville'] . '%';
        }
        if (!empty($filters['prix_max'])) {
            $where[]              = 'b.prix <= :prix_max';
            $params[':prix_max']  = (int) $filters['prix_max'];
        }
        if (!empty($filters['surface_min'])) {
            $where[]                  = 'b.surface >= :surface_min';
            $params[':surface_min']   = (float) $filters['surface_min'];
        }

        $sql = 'SELECT b.id, b.slug, b.titre, b.type_bien, b.transaction,
                       b.statut, b.prix, b.surface, b.pieces, b.chambres,
                       b.ville, b.secteur, b.code_postal, b.photo_principale,
                       b.mis_en_avant, b.exclusif, b.created_at
                FROM biens b
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY b.mis_en_avant DESC, b.created_at DESC
                LIMIT 100';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Détail par slug ───────────────────────────────────────
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM biens WHERE slug = :slug AND statut <> 'archive' LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $bien = $stmt->fetch();

        if (!$bien) {
            return null;
        }

        // Incrémenter les visites
        $this->db->prepare('UPDATE biens SET visites = visites + 1 WHERE id = :id')
                 ->execute([':id' => $bien['id']]);

        // Décoder JSON
        $bien['photos']          = json_decode((string) ($bien['photos'] ?? '[]'), true) ?: [];
        $bien['caracteristiques'] = json_decode((string) ($bien['caracteristiques'] ?? '[]'), true) ?: [];
        $bien['equipements']     = json_decode((string) ($bien['equipements'] ?? '[]'), true) ?: [];

        return $bien;
    }

    // ── Biens mis en avant (homepage) ─────────────────────────
    public function getMisEnAvant(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, slug, titre, type_bien, prix, surface, pieces,
                    chambres, ville, secteur, photo_principale, statut
             FROM biens
             WHERE mis_en_avant = 1 AND statut = 'disponible'
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Admin : liste complète ────────────────────────────────
    public function adminList(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int) $this->db->query('SELECT COUNT(*) FROM biens')->fetchColumn();

        $stmt = $this->db->prepare(
            'SELECT id, slug, titre, type_bien, transaction, statut,
                    prix, surface, pieces, ville, mis_en_avant, created_at
             FROM biens
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'      => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }

    // ── Admin : créer ─────────────────────────────────────────
    public function create(array $data): int
    {
        $slug = $this->generateSlug($data['titre'] ?? '', $data['ville'] ?? '');

        $stmt = $this->db->prepare(
            'INSERT INTO biens
             (slug, titre, description, type_bien, transaction, statut, prix,
              surface, pieces, chambres, salle_de_bain, etage, nb_etages,
              annee_construction, ville, secteur, code_postal, adresse,
              latitude, longitude, dpe, ges, dpe_valeur, ges_valeur,
              photo_principale, photos, caracteristiques, equipements,
              honoraires, ref_interne, mandat, exclusif, mis_en_avant)
             VALUES
             (:slug, :titre, :description, :type_bien, :transaction, :statut, :prix,
              :surface, :pieces, :chambres, :salle_de_bain, :etage, :nb_etages,
              :annee_construction, :ville, :secteur, :code_postal, :adresse,
              :latitude, :longitude, :dpe, :ges, :dpe_valeur, :ges_valeur,
              :photo_principale, :photos, :caracteristiques, :equipements,
              :honoraires, :ref_interne, :mandat, :exclusif, :mis_en_avant)'
        );

        $stmt->execute($this->buildParams($slug, $data));
        return (int) $this->db->lastInsertId();
    }

    // ── Admin : modifier ──────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $existing = $this->db->prepare('SELECT slug, titre FROM biens WHERE id = :id');
        $existing->execute([':id' => $id]);
        $row = $existing->fetch();

        $slug = $row ? $row['slug'] : $this->generateSlug($data['titre'] ?? '', $data['ville'] ?? '');

        // Regénérer slug si titre changé
        if ($row && trim($data['titre'] ?? '') !== '' && $data['titre'] !== $row['titre']) {
            $slug = $this->generateSlug($data['titre'], $data['ville'] ?? '', $id);
        }

        $params          = $this->buildParams($slug, $data);
        $params[':id']   = $id;

        $stmt = $this->db->prepare(
            'UPDATE biens SET
             slug=:slug, titre=:titre, description=:description, type_bien=:type_bien,
             transaction=:transaction, statut=:statut, prix=:prix, surface=:surface,
             pieces=:pieces, chambres=:chambres, salle_de_bain=:salle_de_bain,
             etage=:etage, nb_etages=:nb_etages, annee_construction=:annee_construction,
             ville=:ville, secteur=:secteur, code_postal=:code_postal, adresse=:adresse,
             latitude=:latitude, longitude=:longitude, dpe=:dpe, ges=:ges,
             dpe_valeur=:dpe_valeur, ges_valeur=:ges_valeur,
             photo_principale=:photo_principale, photos=:photos,
             caracteristiques=:caracteristiques, equipements=:equipements,
             honoraires=:honoraires, ref_interne=:ref_interne, mandat=:mandat,
             exclusif=:exclusif, mis_en_avant=:mis_en_avant
             WHERE id=:id'
        );

        return $stmt->execute($params);
    }

    // ── Admin : supprimer ─────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM biens WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ── Privé : construire params PDO ─────────────────────────
    private function buildParams(string $slug, array $d): array
    {
        return [
            ':slug'              => $slug,
            ':titre'             => trim($d['titre'] ?? ''),
            ':description'       => trim($d['description'] ?? ''),
            ':type_bien'         => $d['type_bien'] ?? 'appartement',
            ':transaction'       => $d['transaction'] ?? 'vente',
            ':statut'            => $d['statut'] ?? 'disponible',
            ':prix'              => (int) ($d['prix'] ?? 0),
            ':surface'           => isset($d['surface']) && $d['surface'] !== '' ? (float) $d['surface'] : null,
            ':pieces'            => isset($d['pieces']) && $d['pieces'] !== '' ? (int) $d['pieces'] : null,
            ':chambres'          => isset($d['chambres']) && $d['chambres'] !== '' ? (int) $d['chambres'] : null,
            ':salle_de_bain'     => isset($d['salle_de_bain']) && $d['salle_de_bain'] !== '' ? (int) $d['salle_de_bain'] : null,
            ':etage'             => isset($d['etage']) && $d['etage'] !== '' ? (int) $d['etage'] : null,
            ':nb_etages'         => isset($d['nb_etages']) && $d['nb_etages'] !== '' ? (int) $d['nb_etages'] : null,
            ':annee_construction' => isset($d['annee_construction']) && $d['annee_construction'] !== '' ? (int) $d['annee_construction'] : null,
            ':ville'             => trim($d['ville'] ?? ''),
            ':secteur'           => trim($d['secteur'] ?? ''),
            ':code_postal'       => trim($d['code_postal'] ?? ''),
            ':adresse'           => trim($d['adresse'] ?? ''),
            ':latitude'          => isset($d['latitude']) && $d['latitude'] !== '' ? (float) $d['latitude'] : null,
            ':longitude'         => isset($d['longitude']) && $d['longitude'] !== '' ? (float) $d['longitude'] : null,
            ':dpe'               => $d['dpe'] ?? null,
            ':ges'               => $d['ges'] ?? null,
            ':dpe_valeur'        => isset($d['dpe_valeur']) && $d['dpe_valeur'] !== '' ? (int) $d['dpe_valeur'] : null,
            ':ges_valeur'        => isset($d['ges_valeur']) && $d['ges_valeur'] !== '' ? (int) $d['ges_valeur'] : null,
            ':photo_principale'  => trim($d['photo_principale'] ?? ''),
            ':photos'            => json_encode($d['photos'] ?? []),
            ':caracteristiques'  => json_encode($d['caracteristiques'] ?? []),
            ':equipements'       => json_encode($d['equipements'] ?? []),
            ':honoraires'        => trim($d['honoraires'] ?? ''),
            ':ref_interne'       => trim($d['ref_interne'] ?? ''),
            ':mandat'            => trim($d['mandat'] ?? ''),
            ':exclusif'          => (int) !empty($d['exclusif']),
            ':mis_en_avant'      => (int) !empty($d['mis_en_avant']),
        ];
    }

    // ── Privé : générer un slug unique ────────────────────────
    private function generateSlug(string $titre, string $ville = '', ?int $excludeId = null): string
    {
        $base = strtolower(trim($titre . ($ville ? '-' . $ville : '')));
        $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: $base;
        $base = preg_replace('/[^a-z0-9]+/', '-', $base) ?? $base;
        $base = trim($base, '-');
        $base = substr($base, 0, 200);

        $slug  = $base;
        $i     = 1;

        while (true) {
            $sql    = 'SELECT id FROM biens WHERE slug = :slug' . ($excludeId ? ' AND id <> :id' : '');
            $check  = $this->db->prepare($sql);
            $params = [':slug' => $slug];
            if ($excludeId) {
                $params[':id'] = $excludeId;
            }
            $check->execute($params);

            if (!$check->fetch()) {
                break;
            }

            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
EOF
