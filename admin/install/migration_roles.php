<?php
/**
 * Migration : Système de rôles Super User / Admin
 * /admin/install/migration_roles.php
 *
 * Exécuter UNE SEULE FOIS pour créer les tables et colonnes nécessaires.
 * Accès : /admin/install/migration_roles.php?key=VOTRE_CLE
 */

// Sécurité : clé obligatoire pour exécuter
$secretKey = 'install-roles-2024';
if (($_GET['key'] ?? '') !== $secretKey) {
    http_response_code(403);
    die('Accès interdit. Ajoutez ?key=... à l\'URL.');
}

require_once dirname(__DIR__, 2) . '/config/config.php';

$pdo = getDB();
$results = [];

// ── 1. Ajouter la colonne role à la table admins ─────────────
try {
    $pdo->exec("ALTER TABLE `admins` ADD COLUMN `role` ENUM('superuser','admin') NOT NULL DEFAULT 'admin' AFTER `email`");
    $results[] = '✅ Colonne `role` ajoutée à `admins`';
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        $results[] = '⏭️ Colonne `role` existe déjà';
    } else {
        $results[] = '❌ Erreur role: ' . $e->getMessage();
    }
}

// ── 2. Ajouter colonnes nom et téléphone à admins ────────────
$extraCols = [
    'name'  => "VARCHAR(255) DEFAULT NULL AFTER `role`",
    'phone' => "VARCHAR(32) DEFAULT NULL AFTER `name`",
    'is_active' => "TINYINT(1) NOT NULL DEFAULT 1 AFTER `phone`",
    'created_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `is_active`",
    'last_login' => "DATETIME DEFAULT NULL AFTER `created_at`",
];

foreach ($extraCols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE `admins` ADD COLUMN `$col` $def");
        $results[] = "✅ Colonne `$col` ajoutée à `admins`";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            $results[] = "⏭️ Colonne `$col` existe déjà";
        } else {
            $results[] = "❌ Erreur $col: " . $e->getMessage();
        }
    }
}

// ── 3. Table des permissions par admin ───────────────────────
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admin_module_permissions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `admin_id` INT UNSIGNED NOT NULL,
            `module_slug` VARCHAR(64) NOT NULL,
            `is_allowed` TINYINT(1) NOT NULL DEFAULT 1,
            `granted_by` INT UNSIGNED DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_admin_module` (`admin_id`, `module_slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $results[] = '✅ Table `admin_module_permissions` créée';
} catch (PDOException $e) {
    $results[] = '❌ Erreur table permissions: ' . $e->getMessage();
}

// ── 4. Mettre le premier admin en Super User ─────────────────
try {
    $stmt = $pdo->query("SELECT id FROM admins ORDER BY id ASC LIMIT 1");
    $first = $stmt->fetch();
    if ($first) {
        $pdo->prepare("UPDATE admins SET role = 'superuser' WHERE id = ?")->execute([$first['id']]);
        $results[] = "✅ Admin #" . $first['id'] . " promu Super User";
    }
} catch (PDOException $e) {
    $results[] = '❌ Erreur promotion: ' . $e->getMessage();
}

// ── Affichage ────────────────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Migration Rôles — Résultats</h2><ul>";
foreach ($results as $r) {
    echo "<li>$r</li>";
}
echo "</ul>";
echo "<p><strong>Migration terminée.</strong> Supprimez ce fichier après exécution.</p>";
