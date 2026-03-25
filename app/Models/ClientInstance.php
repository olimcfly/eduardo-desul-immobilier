<?php

class ClientInstance
{
    private PDO $db;
    private SecretVaultService $secretVault;

    public const STATUSES = ['draft', 'ready', 'generated', 'deployed', 'delivered'];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $secretKey = getenv('INSTANCE_SECRET_KEY') ?: getenv('APP_KEY') ?: 'change-this-instance-secret-key';
        $this->secretVault = new SecretVaultService($secretKey);
    }

    public function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS client_instances (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(190) NOT NULL,
            business_name VARCHAR(190) NOT NULL,
            domain VARCHAR(190) NOT NULL,
            city VARCHAR(120) NOT NULL,
            admin_email VARCHAR(190) NOT NULL,
            admin_password_temp VARCHAR(255) NOT NULL,
            db_host VARCHAR(190) NOT NULL,
            db_port SMALLINT UNSIGNED NOT NULL DEFAULT 3306,
            db_name VARCHAR(190) NOT NULL,
            db_user VARCHAR(190) NOT NULL,
            db_pass VARCHAR(255) NOT NULL,
            smtp_host VARCHAR(190) DEFAULT NULL,
            smtp_port SMALLINT UNSIGNED DEFAULT NULL,
            smtp_user VARCHAR(190) DEFAULT NULL,
            smtp_pass VARCHAR(255) DEFAULT NULL,
            smtp_pass_encrypted TEXT DEFAULT NULL,
            smtp_encryption VARCHAR(20) DEFAULT NULL,
            from_email VARCHAR(190) DEFAULT NULL,
            first_name VARCHAR(120) DEFAULT NULL,
            last_name VARCHAR(120) DEFAULT NULL,
            install_email VARCHAR(190) DEFAULT NULL,
            instance_slug VARCHAR(190) DEFAULT NULL,
            openai_api_key TEXT DEFAULT NULL,
            perplexity_api_key TEXT DEFAULT NULL,
            logo_path VARCHAR(255) DEFAULT NULL,
            status ENUM('draft','ready','generated','deployed','delivered') NOT NULL DEFAULT 'draft',
            zip_path VARCHAR(255) DEFAULT NULL,
            progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
            current_step TINYINT UNSIGNED NOT NULL DEFAULT 1,
            generated_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_domain (domain)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM client_instances ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM client_instances WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO client_instances (
            client_name,business_name,domain,city,admin_email,admin_password_temp,
            db_host,db_port,db_name,db_user,db_pass,
            smtp_host,smtp_port,smtp_user,smtp_pass,smtp_encryption,from_email,
            openai_api_key,perplexity_api_key,logo_path,status
        ) VALUES (
            :client_name,:business_name,:domain,:city,:admin_email,:admin_password_temp,
            :db_host,:db_port,:db_name,:db_user,:db_pass,
            :smtp_host,:smtp_port,:smtp_user,:smtp_pass,:smtp_encryption,:from_email,
            :openai_api_key,:perplexity_api_key,:logo_path,:status
        )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->normalize($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE client_instances SET
            client_name=:client_name,business_name=:business_name,domain=:domain,city=:city,
            admin_email=:admin_email,admin_password_temp=:admin_password_temp,
            db_host=:db_host,db_port=:db_port,db_name=:db_name,db_user=:db_user,db_pass=:db_pass,
            smtp_host=:smtp_host,smtp_port=:smtp_port,smtp_user=:smtp_user,smtp_pass=:smtp_pass,
            smtp_encryption=:smtp_encryption,from_email=:from_email,
            openai_api_key=:openai_api_key,perplexity_api_key=:perplexity_api_key,
            logo_path=:logo_path,status=:status
            WHERE id=:id';

        $payload = $this->normalize($data);
        $payload['id'] = $id;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($payload);
    }

    public function markGenerated(int $id, string $zipPath): bool
    {
        $stmt = $this->db->prepare('UPDATE client_instances SET zip_path = :zip_path, status = :status, generated_at = :generated_at WHERE id = :id');

        return $stmt->execute([
            ':id' => $id,
            ':zip_path' => $zipPath,
            ':status' => 'generated',
            ':generated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    private function normalize(array $data): array
    {
        $status = in_array($data['status'] ?? 'draft', self::STATUSES, true) ? $data['status'] : 'draft';
        $dbPass = $this->protectSecret((string) ($data['db_pass'] ?? ''), false);
        $smtpPass = $this->protectSecret((string) ($data['smtp_pass'] ?? ''), true);

        return [
            'client_name' => trim((string) ($data['client_name'] ?? '')),
            'business_name' => trim((string) ($data['business_name'] ?? '')),
            'domain' => trim((string) ($data['domain'] ?? '')),
            'city' => trim((string) ($data['city'] ?? '')),
            'admin_email' => trim((string) ($data['admin_email'] ?? '')),
            'admin_password_temp' => trim((string) ($data['admin_password_temp'] ?? '')),
            'db_host' => trim((string) ($data['db_host'] ?? '')),
            'db_port' => (int) ($data['db_port'] ?? 3306),
            'db_name' => trim((string) ($data['db_name'] ?? '')),
            'db_user' => trim((string) ($data['db_user'] ?? '')),
            'db_pass' => $dbPass ?? '',
            'smtp_host' => $this->nullIfEmpty($data['smtp_host'] ?? null),
            'smtp_port' => $this->nullIfEmpty($data['smtp_port'] ?? null),
            'smtp_user' => $this->nullIfEmpty($data['smtp_user'] ?? null),
            'smtp_pass' => $smtpPass,
            'smtp_encryption' => $this->nullIfEmpty($data['smtp_encryption'] ?? null),
            'from_email' => $this->nullIfEmpty($data['from_email'] ?? null),
            'openai_api_key' => $this->nullIfEmpty($data['openai_api_key'] ?? null),
            'perplexity_api_key' => $this->nullIfEmpty($data['perplexity_api_key'] ?? null),
            'logo_path' => $this->nullIfEmpty($data['logo_path'] ?? null),
            'status' => $status,
        ];
    }

    private function nullIfEmpty($value): mixed
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function protectSecret(string $value, bool $allowNull = true): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return $allowNull ? null : '';
        }

        if (str_starts_with($value, 'enc:')) {
            return $value;
        }

        return 'enc:' . $this->secretVault->encrypt($value);
    }
}
