<?php

class ClientInstanceController
{
    private ClientInstance $model;
    private InstanceGeneratorService $generatorService;

    public function __construct(PDO $db)
    {
        $this->model = new ClientInstance($db);
        $this->model->ensureTable();

        $this->generatorService = new InstanceGeneratorService(
            new PlaceholderReplacementService(),
            new ZipExportService()
        );
    }

    public function handle(): void
    {
        $action = $_GET['action'] ?? 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['action'] ?? '') === 'store') {
                $this->store();
                return;
            }
            if (($_POST['action'] ?? '') === 'update') {
                $this->update();
                return;
            }
            if (($_POST['action'] ?? '') === 'generate') {
                $this->generate();
                return;
            }
        }

        if ($action === 'create') {
            $this->create();
            return;
        }
        if ($action === 'edit') {
            $this->edit();
            return;
        }
        if ($action === 'show') {
            $this->show();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        $instances = $this->model->all();
        include ROOT_PATH . '/admin/modules/system/instance-generator/views/index.php';
    }

    private function create(): void
    {
        $instance = $this->defaultInstance();
        $formAction = 'store';
        include ROOT_PATH . '/admin/modules/system/instance-generator/views/form.php';
    }

    private function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $instance = $this->model->find($id);

        if (!$instance) {
            $_SESSION['instance_generator_flash'] = ['type' => 'error', 'message' => 'Instance introuvable'];
            $this->redirectToIndex();
        }

        $formAction = 'update';
        include ROOT_PATH . '/admin/modules/system/instance-generator/views/form.php';
    }

    private function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $instance = $this->model->find($id);

        if (!$instance) {
            $_SESSION['instance_generator_flash'] = ['type' => 'error', 'message' => 'Instance introuvable'];
            $this->redirectToIndex();
        }

        include ROOT_PATH . '/admin/modules/system/instance-generator/views/show.php';
    }

    private function store(): void
    {
        $payload = $this->sanitizePayload($_POST);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['instance_generator_flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
            $_SESSION['instance_generator_old'] = $payload;
            $this->redirect('/admin/dashboard.php?page=instance-generator&action=create');
        }

        $id = $this->model->create($payload);
        $_SESSION['instance_generator_flash'] = ['type' => 'success', 'message' => 'Instance créée avec succès'];
        $this->redirect('/admin/dashboard.php?page=instance-generator&action=show&id=' . $id);
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->sanitizePayload($_POST);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['instance_generator_flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
            $_SESSION['instance_generator_old'] = $payload;
            $this->redirect('/admin/dashboard.php?page=instance-generator&action=edit&id=' . $id);
        }

        $this->model->update($id, $payload);
        $_SESSION['instance_generator_flash'] = ['type' => 'success', 'message' => 'Instance mise à jour'];
        $this->redirect('/admin/dashboard.php?page=instance-generator&action=show&id=' . $id);
    }

    private function generate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $instance = $this->model->find($id);

        if (!$instance) {
            $_SESSION['instance_generator_flash'] = ['type' => 'error', 'message' => 'Instance introuvable'];
            $this->redirectToIndex();
        }

        try {
            $zipPath = $this->generatorService->generate($instance);
            $this->model->markGenerated($id, $zipPath);

            $_SESSION['instance_generator_flash'] = [
                'type' => 'success',
                'message' => 'Package généré: ' . $zipPath,
            ];
        } catch (Throwable $e) {
            $_SESSION['instance_generator_flash'] = [
                'type' => 'error',
                'message' => 'Erreur de génération: ' . $e->getMessage(),
            ];
        }

        $this->redirect('/admin/dashboard.php?page=instance-generator&action=show&id=' . $id);
    }

    private function sanitizePayload(array $input): array
    {
        return [
            'client_name' => trim((string) ($input['client_name'] ?? '')),
            'business_name' => trim((string) ($input['business_name'] ?? '')),
            'domain' => trim((string) ($input['domain'] ?? '')),
            'city' => trim((string) ($input['city'] ?? '')),
            'admin_email' => trim((string) ($input['admin_email'] ?? '')),
            'admin_password_temp' => trim((string) ($input['admin_password_temp'] ?? '')),
            'db_host' => trim((string) ($input['db_host'] ?? 'localhost')),
            'db_port' => (int) ($input['db_port'] ?? 3306),
            'db_name' => trim((string) ($input['db_name'] ?? '')),
            'db_user' => trim((string) ($input['db_user'] ?? '')),
            'db_pass' => trim((string) ($input['db_pass'] ?? '')),
            'smtp_host' => trim((string) ($input['smtp_host'] ?? '')),
            'smtp_port' => trim((string) ($input['smtp_port'] ?? '')),
            'smtp_user' => trim((string) ($input['smtp_user'] ?? '')),
            'smtp_pass' => trim((string) ($input['smtp_pass'] ?? '')),
            'smtp_encryption' => trim((string) ($input['smtp_encryption'] ?? '')),
            'from_email' => trim((string) ($input['from_email'] ?? '')),
            'openai_api_key' => trim((string) ($input['openai_api_key'] ?? '')),
            'perplexity_api_key' => trim((string) ($input['perplexity_api_key'] ?? '')),
            'logo_path' => trim((string) ($input['logo_path'] ?? '')),
            'status' => trim((string) ($input['status'] ?? 'draft')),
        ];
    }

    private function validatePayload(array $payload): array
    {
        $errors = [];

        $required = [
            'client_name', 'business_name', 'domain', 'city', 'admin_email', 'admin_password_temp',
            'db_host', 'db_name', 'db_user', 'db_pass',
        ];

        foreach ($required as $field) {
            if (($payload[$field] ?? '') === '') {
                $errors[] = 'Le champ ' . $field . ' est obligatoire.';
            }
        }

        if (!filter_var($payload['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L’email administrateur est invalide.';
        }

        if (!in_array($payload['status'], ClientInstance::STATUSES, true)) {
            $errors[] = 'Le statut est invalide.';
        }

        if ($payload['db_port'] <= 0 || $payload['db_port'] > 65535) {
            $errors[] = 'Le port DB est invalide.';
        }

        if ($payload['smtp_port'] !== '' && ((int) $payload['smtp_port'] <= 0 || (int) $payload['smtp_port'] > 65535)) {
            $errors[] = 'Le port SMTP est invalide.';
        }

        return $errors;
    }

    private function defaultInstance(): array
    {
        $old = $_SESSION['instance_generator_old'] ?? [];
        unset($_SESSION['instance_generator_old']);

        return array_merge([
            'id' => null,
            'client_name' => '',
            'business_name' => '',
            'domain' => '',
            'city' => '',
            'admin_email' => '',
            'admin_password_temp' => '',
            'db_host' => 'localhost',
            'db_port' => 3306,
            'db_name' => '',
            'db_user' => '',
            'db_pass' => '',
            'smtp_host' => '',
            'smtp_port' => '',
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_encryption' => 'tls',
            'from_email' => '',
            'openai_api_key' => '',
            'perplexity_api_key' => '',
            'logo_path' => '',
            'status' => 'draft',
        ], $old);
    }

    private function redirectToIndex(): void
    {
        $this->redirect('/admin/dashboard.php?page=instance-generator');
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
