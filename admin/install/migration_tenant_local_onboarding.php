<?php
/**
 * Migration : Socle tenant + onboarding local (Lot 1)
 * /admin/install/migration_tenant_local_onboarding.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

$installKey = getenv('TENANT_LOCAL_INSTALL_KEY') ?: '';
$allowInstall = getenv('ALLOW_INSTALL_MIGRATIONS') === '1';

if ($installKey === '') {
    http_response_code(403);
    die('Accès interdit. Variable TENANT_LOCAL_INSTALL_KEY non configurée.');
}

if (!$allowInstall) {
    http_response_code(403);
    die('Accès interdit. Activez ALLOW_INSTALL_MIGRATIONS=1 temporairement pour exécuter cette migration.');
}

if (($_GET['key'] ?? '') !== $installKey) {
    http_response_code(403);
    die('Accès interdit. Clé de migration invalide.');
}
if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';

$pdo = Database::getInstance();
$results = [];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(190) NOT NULL,
        slug VARCHAR(190) NOT NULL,
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_tenants_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = '✅ Table `tenants` créée';
} catch (PDOException $e) {
    $results[] = '❌ tenants: ' . $e->getMessage();
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_tenant_memberships (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL,
        admin_id INT UNSIGNED NOT NULL,
        role ENUM('owner','editor','viewer') NOT NULL DEFAULT 'editor',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_membership (tenant_id, admin_id),
        KEY idx_membership_admin (admin_id),
        CONSTRAINT fk_membership_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
        CONSTRAINT fk_membership_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = '✅ Table `admin_tenant_memberships` créée';
} catch (PDOException $e) {
    $results[] = '❌ admin_tenant_memberships: ' . $e->getMessage();
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS local_profiles (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL,
        city_name VARCHAR(190) NOT NULL,
        country_code CHAR(2) NOT NULL DEFAULT 'FR',
        activity_label VARCHAR(190) NOT NULL,
        persona_summary TEXT NOT NULL,
        goals_json JSON NULL,
        status ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
        created_by INT UNSIGNED NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_local_profiles_tenant (tenant_id),
        CONSTRAINT fk_local_profile_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
        CONSTRAINT fk_local_profile_creator FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = '✅ Table `local_profiles` créée';
} catch (PDOException $e) {
    $results[] = '❌ local_profiles: ' . $e->getMessage();
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS local_profile_districts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL,
        local_profile_id INT UNSIGNED NOT NULL,
        district_name VARCHAR(190) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_local_profile_district (tenant_id, local_profile_id, district_name),
        KEY idx_local_district_tenant (tenant_id),
        CONSTRAINT fk_local_district_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
        CONSTRAINT fk_local_district_profile FOREIGN KEY (local_profile_id) REFERENCES local_profiles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = '✅ Table `local_profile_districts` créée';
} catch (PDOException $e) {
    $results[] = '❌ local_profile_districts: ' . $e->getMessage();
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL,
        actor_user_id INT UNSIGNED DEFAULT NULL,
        action VARCHAR(190) NOT NULL,
        entity_type VARCHAR(120) NOT NULL,
        entity_id BIGINT UNSIGNED NOT NULL,
        before_json JSON NULL,
        after_json JSON NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_audit_tenant_created (tenant_id, created_at),
        CONSTRAINT fk_audit_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = '✅ Table `audit_logs` créée';
} catch (PDOException $e) {
    $results[] = '❌ audit_logs: ' . $e->getMessage();
}

try {
    $countTenants = (int)$pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
    if ($countTenants === 0) {
        $pdo->prepare("INSERT INTO tenants (name, slug, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())")
            ->execute(['Tenant Principal', 'tenant-principal']);
        $tenantId = (int)$pdo->lastInsertId();

        $firstAdminId = (int)$pdo->query("SELECT id FROM admins ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($firstAdminId > 0) {
            $pdo->prepare("INSERT INTO admin_tenant_memberships (tenant_id, admin_id, role, is_active, created_at) VALUES (?, ?, 'owner', 1, NOW())")
                ->execute([$tenantId, $firstAdminId]);
        }

        $results[] = '✅ Tenant par défaut + membership owner créé';
    } else {
        $results[] = '⏭️ Seed tenant ignoré (tenants déjà présents)';
    }
} catch (PDOException $e) {
    $results[] = '❌ seed tenant: ' . $e->getMessage();
}

header('Content-Type: text/html; charset=utf-8');
echo '<h2>Migration Tenant + Local Onboarding — Résultats</h2><ul>';
foreach ($results as $line) {
    echo '<li>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
}
echo '</ul>';
echo '<p><strong>Migration terminée.</strong> Désactivez ALLOW_INSTALL_MIGRATIONS après exécution.</p>';
