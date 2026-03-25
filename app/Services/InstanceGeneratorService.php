<?php

class InstanceGeneratorService
{
    private array $excludedPaths = [
        'logs',
        'cache',
        'uploads',
        'sessions',
        'backups',
        'storage/logs',
        'storage/cache',
        '.git',
    ];

    public function __construct(
        private PlaceholderReplacementService $placeholderService,
        private ZipExportService $zipExportService
    ) {
    }

    public function generate(array $instance): string
    {
        $templatePath = getenv('INSTANCE_TEMPLATE_SOURCE') ?: ROOT_PATH . '/template-master';
        if (!is_dir($templatePath)) {
            throw new RuntimeException('Dossier template maître introuvable: ' . $templatePath);
        }

        $workDirBase = ROOT_PATH . '/storage/instance-generator';
        $tempDir = $workDirBase . '/tmp/' . $this->slugify($instance['domain']) . '-' . date('YmdHis');
        $exportDir = $workDirBase . '/exports';

        if (!is_dir($tempDir) && !mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
            throw new RuntimeException('Impossible de créer le dossier temporaire.');
        }

        try {
            $this->copyTemplate($templatePath, $tempDir);

            $variables = $this->buildVariables($instance);
            $this->placeholderService->replaceInDirectory($tempDir, $variables);

            $this->createDotEnv($tempDir, $instance);
            $this->createInstanceJson($tempDir, $instance);
            $this->createInstallNotes($tempDir, $instance);

            $zipPath = $exportDir . '/instance-' . $this->slugify($instance['domain']) . '-' . date('YmdHis') . '.zip';
            $this->zipExportService->createZipFromDirectory($tempDir, $zipPath);

            return $zipPath;
        } finally {
            $this->removeDirectory($tempDir);
        }
    }

    private function copyTemplate(string $source, string $destination): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $source = rtrim($source, '/');

        foreach ($iterator as $item) {
            $sourcePath = $item->getPathname();
            $relativePath = ltrim(str_replace($source, '', $sourcePath), '/');

            if ($this->isExcluded($relativePath)) {
                continue;
            }

            $destinationPath = $destination . '/' . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0775, true);
                }
                continue;
            }

            $parentDir = dirname($destinationPath);
            if (!is_dir($parentDir)) {
                mkdir($parentDir, 0775, true);
            }

            copy($sourcePath, $destinationPath);
        }
    }

    private function isExcluded(string $relativePath): bool
    {
        $relativePath = trim($relativePath, '/');
        foreach ($this->excludedPaths as $excluded) {
            if ($relativePath === $excluded || str_starts_with($relativePath, $excluded . '/')) {
                return true;
            }
        }

        return false;
    }

    private function buildVariables(array $instance): array
    {
        return [
            'client_name' => (string) $instance['client_name'],
            'business_name' => (string) $instance['business_name'],
            'domain' => (string) $instance['domain'],
            'city' => (string) $instance['city'],
            'admin_email' => (string) $instance['admin_email'],
            'admin_password_temp' => (string) $instance['admin_password_temp'],
            'db_host' => (string) $instance['db_host'],
            'db_port' => (string) $instance['db_port'],
            'db_name' => (string) $instance['db_name'],
            'db_user' => (string) $instance['db_user'],
            'db_pass' => (string) $instance['db_pass'],
            'smtp_host' => (string) ($instance['smtp_host'] ?? ''),
            'smtp_port' => (string) ($instance['smtp_port'] ?? ''),
            'smtp_user' => (string) ($instance['smtp_user'] ?? ''),
            'smtp_pass' => (string) ($instance['smtp_pass'] ?? ''),
            'smtp_encryption' => (string) ($instance['smtp_encryption'] ?? ''),
            'from_email' => (string) ($instance['from_email'] ?? ''),
            'openai_api_key' => (string) ($instance['openai_api_key'] ?? ''),
            'perplexity_api_key' => (string) ($instance['perplexity_api_key'] ?? ''),
            'logo_path' => (string) ($instance['logo_path'] ?? ''),
            'status' => (string) ($instance['status'] ?? 'draft'),
        ];
    }

    private function createDotEnv(string $workingDir, array $instance): void
    {
        $content = [
            'INSTANCE_ID=' . $this->slugify((string) $instance['domain']),
            'SITE_TITLE="' . addslashes((string) $instance['business_name']) . '"',
            'SITE_DOMAIN=' . $instance['domain'],
            'ADMIN_EMAIL=' . $instance['admin_email'],
            'DB_HOST=' . $instance['db_host'],
            'DB_PORT=' . $instance['db_port'],
            'DB_NAME=' . $instance['db_name'],
            'DB_USER=' . $instance['db_user'],
            'DB_PASS="' . addslashes((string) $instance['db_pass']) . '"',
            'SMTP_HOST=' . ($instance['smtp_host'] ?? ''),
            'SMTP_PORT=' . ($instance['smtp_port'] ?? ''),
            'SMTP_USER=' . ($instance['smtp_user'] ?? ''),
            'SMTP_PASS="' . addslashes((string) ($instance['smtp_pass'] ?? '')) . '"',
            'SMTP_ENCRYPTION=' . ($instance['smtp_encryption'] ?? ''),
            'FROM_EMAIL=' . ($instance['from_email'] ?? ''),
            'OPENAI_API_KEY=' . ($instance['openai_api_key'] ?? ''),
            'PERPLEXITY_API_KEY=' . ($instance['perplexity_api_key'] ?? ''),
        ];

        file_put_contents($workingDir . '/.env', implode(PHP_EOL, $content) . PHP_EOL);
    }

    private function createInstanceJson(string $workingDir, array $instance): void
    {
        $payload = [
            'generated_at' => gmdate(DATE_ATOM),
            'client' => [
                'client_name' => $instance['client_name'],
                'business_name' => $instance['business_name'],
                'domain' => $instance['domain'],
                'city' => $instance['city'],
            ],
            'status' => $instance['status'],
            'deployment' => [
                'mode' => 'manual-ftp',
                'notes_file' => 'install-notes.txt',
            ],
        ];

        file_put_contents(
            $workingDir . '/instance.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function createInstallNotes(string $workingDir, array $instance): void
    {
        $notes = [
            'Générateur d’instance client - Notes d’installation',
            '===================================================',
            '',
            'Client: ' . $instance['client_name'],
            'Société: ' . $instance['business_name'],
            'Domaine: ' . $instance['domain'],
            'Ville: ' . $instance['city'],
            '',
            'Étapes de déploiement manuel (FTP):',
            '1) Dézipper l’archive localement.',
            '2) Uploader tous les fichiers vers le serveur cible.',
            '3) Créer la base de données et injecter vos schémas applicatifs.',
            '4) Vérifier les valeurs du fichier .env.',
            '5) Tester /admin et les pages publiques.',
            '',
            'Ce package est basé sur un template maître sans données clients réelles.',
        ];

        file_put_contents($workingDir . '/install-notes.txt', implode(PHP_EOL, $notes) . PHP_EOL);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? 'instance';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'instance';
    }
}
