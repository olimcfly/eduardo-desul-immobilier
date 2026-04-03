<?php

class BienAdminController extends Controller
{
    private BienService $bienService;
    private PhotoService $photoService;

    public function __construct()
    {
        $this->bienService = new BienService();
        $this->photoService = new PhotoService();
        $this->checkAdminAuth();
    }

    public function index(): void
    {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 12;
        $filters = $this->getFilters();

        $biens = $this->bienService->getAll($filters, $page, $perPage);
        $total = $this->bienService->count($filters);
        $totalPages = (int) ceil(max(1, $total) / $perPage);

        $this->render('admin/biens/index', [
            'biens' => $biens,
            'total' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'filters' => $filters,
            'propertyTypes' => $this->bienService->getPropertyTypes(),
            'propertyStatuses' => $this->bienService->getPropertyStatuses(),
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateBienData($_POST);
            $files = $_FILES['photos'] ?? [];

            try {
                $bienId = $this->bienService->create($data);

                if (!empty($files['name'][0])) {
                    $this->photoService->uploadPhotos($bienId, $files);
                }

                flash('success', 'Bien créé avec succès.');
                $this->redirect('/admin/biens');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
                $this->render('admin/biens/form', [
                    'bien' => $data,
                    'errors' => $this->bienService->getErrors(),
                    'propertyTypes' => $this->bienService->getPropertyTypes(),
                    'propertyStatuses' => $this->bienService->getPropertyStatuses(),
                ]);
            }

            return;
        }

        $this->render('admin/biens/form', [
            'bien' => [],
            'propertyTypes' => $this->bienService->getPropertyTypes(),
            'propertyStatuses' => $this->bienService->getPropertyStatuses(),
            'featuresList' => $this->bienService->getFeaturesList(),
        ]);
    }

    public function edit(int $id): void
    {
        $bien = $this->bienService->getById($id);

        if (!$bien) {
            flash('error', 'Bien non trouvé.');
            $this->redirect('/admin/biens');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateBienData($_POST);
            $files = $_FILES['photos'] ?? [];

            try {
                $this->bienService->update($id, $data);

                if (!empty($files['name'][0])) {
                    $this->photoService->uploadPhotos($id, $files);
                }

                flash('success', 'Bien mis à jour avec succès.');
                $this->redirect('/admin/biens');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
                $this->render('admin/biens/form', [
                    'bien' => array_merge($bien, $data),
                    'errors' => $this->bienService->getErrors(),
                    'photos' => $this->photoService->getPhotos($id),
                    'propertyTypes' => $this->bienService->getPropertyTypes(),
                    'propertyStatuses' => $this->bienService->getPropertyStatuses(),
                ]);
            }

            return;
        }

        $this->render('admin/biens/form', [
            'bien' => $bien,
            'photos' => $this->photoService->getPhotos($id),
            'propertyTypes' => $this->bienService->getPropertyTypes(),
            'propertyStatuses' => $this->bienService->getPropertyStatuses(),
            'featuresList' => $this->bienService->getFeaturesList(),
        ]);
    }

    public function delete(int $id): void
    {
        try {
            $this->bienService->delete($id);
            flash('success', 'Bien supprimé avec succès.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }

        $this->redirect('/admin/biens');
    }

    public function managePhotos(int $id): void
    {
        $bien = $this->bienService->getById($id);

        if (!$bien) {
            flash('error', 'Bien non trouvé.');
            $this->redirect('/admin/biens');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['set_primary'])) {
                $this->photoService->setPrimaryPhoto((int) ($_POST['photo_id'] ?? 0));
                flash('success', 'Photo principale mise à jour.');
            }

            if (isset($_POST['delete_photo'])) {
                $this->photoService->deletePhoto((int) ($_POST['photo_id'] ?? 0));
                flash('success', 'Photo supprimée.');
            }

            $this->redirect('/admin/biens/photos/' . $id);
        }

        $this->render('admin/biens/photos', [
            'bien' => $bien,
            'photos' => $this->photoService->getPhotos($id),
        ]);
    }

    private function checkAdminAuth(): void
    {
        $this->requireAdmin();
    }

    private function getFilters(): array
    {
        $filters = [];

        if (!empty($_GET['type'])) {
            $filters['type'] = trim((string) $_GET['type']);
        }

        if (!empty($_GET['city'])) {
            $filters['city'] = trim((string) $_GET['city']);
        }

        if (!empty($_GET['status'])) {
            $filters['status'] = trim((string) $_GET['status']);
        }

        if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
            $filters['min_price'] = (int) $_GET['min_price'];
        }

        if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
            $filters['max_price'] = (int) $_GET['max_price'];
        }

        return $filters;
    }

    private function validateBienData(array $data): array
    {
        $requiredFields = [
            'title', 'description', 'type', 'price', 'surface', 'rooms',
            'bedrooms', 'bathrooms', 'address', 'city', 'postal_code',
            'department', 'lat', 'lng', 'status', 'reference',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException("Le champ {$field} est requis.");
            }
        }

        if (!is_numeric($data['price']) || (float) $data['price'] <= 0) {
            throw new InvalidArgumentException('Le prix doit être un nombre positif.');
        }

        if (!is_numeric($data['surface']) || (float) $data['surface'] <= 0) {
            throw new InvalidArgumentException('La surface doit être un nombre positif.');
        }

        if (!is_numeric($data['rooms']) || (int) $data['rooms'] <= 0) {
            throw new InvalidArgumentException('Le nombre de pièces doit être un entier positif.');
        }

        if (!is_numeric($data['lat']) || (float) $data['lat'] < -90 || (float) $data['lat'] > 90) {
            throw new InvalidArgumentException('Latitude invalide.');
        }

        if (!is_numeric($data['lng']) || (float) $data['lng'] < -180 || (float) $data['lng'] > 180) {
            throw new InvalidArgumentException('Longitude invalide.');
        }

        return [
            'title' => htmlspecialchars((string) $data['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'description' => (string) $data['description'],
            'type' => (string) $data['type'],
            'transaction_type' => (string) ($data['transaction_type'] ?? 'vente'),
            'price' => (float) $data['price'],
            'surface' => (float) $data['surface'],
            'rooms' => (int) $data['rooms'],
            'bedrooms' => (int) $data['bedrooms'],
            'bathrooms' => (int) $data['bathrooms'],
            'address' => htmlspecialchars((string) $data['address'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'city' => htmlspecialchars((string) $data['city'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'postal_code' => htmlspecialchars((string) $data['postal_code'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'department' => htmlspecialchars((string) $data['department'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'lat' => (float) $data['lat'],
            'lng' => (float) $data['lng'],
            'features' => $data['features'] ?? [],
            'year_built' => !empty($data['year_built']) ? (int) $data['year_built'] : null,
            'condition' => (string) ($data['condition'] ?? 'good'),
            'energy_rating' => $data['energy_rating'] ?? null,
            'heating' => $data['heating'] ?? null,
            'floor' => isset($data['floor']) ? (int) $data['floor'] : null,
            'parking' => isset($data['parking']),
            'garden' => isset($data['garden']),
            'pool' => isset($data['pool']),
            'terrace' => isset($data['terrace']),
            'balcony' => isset($data['balcony']),
            'elevator' => isset($data['elevator']),
            'status' => (string) $data['status'],
            'reference' => htmlspecialchars((string) $data['reference'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'is_featured' => isset($data['is_featured']),
            'virtual_tour_url' => $data['virtual_tour_url'] ?? null,
        ];
    }
}
