<?php
/**
 * Script d'installation pour nouveau site
 * /setup/install.php
 *
 * Acces: https://mon-domaine.fr/setup/install.php
 * SUPPRIMER CE FICHIER APRES INSTALLATION
 */

// Securite: bloquer si config deja en place et install deja fait
$configExists = file_exists(__DIR__ . '/../config/config.php');
$lockFile = __DIR__ . '/../config/.installed';

if (file_exists($lockFile)) {
    die('Installation deja effectuee. Supprimez config/.installed pour reinstaller.');
}

$step = $_GET['step'] ?? '1';
$errors = [];
$success = [];

// ═══════════════════════════════════════════════════════════
// ETAPE 1 : FORMULAIRE DE CONFIGURATION
// ═══════════════════════════════════════════════════════════

if ($step === '1' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $fields = [
        'instance_id' => 'Identifiant instance',
        'site_title' => 'Titre du site',
        'site_domain' => 'Domaine',
        'admin_email' => 'Email admin',
        'db_host' => 'Serveur BDD',
        'db_name' => 'Nom BDD',
        'db_user' => 'Utilisateur BDD',
        'db_pass' => 'Mot de passe BDD',
    ];

    foreach ($fields as $key => $label) {
        if (empty(trim($_POST[$key] ?? ''))) {
            $errors[] = "$label est requis";
        }
    }

    // Tester la connexion BDD
    if (empty($errors)) {
        try {
            $testDb = new PDO(
                'mysql:host=' . $_POST['db_host'] . ';dbname=' . $_POST['db_name'] . ';charset=utf8mb4',
                $_POST['db_user'],
                $_POST['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $success[] = 'Connexion base de donnees OK';
        } catch (PDOException $e) {
            $errors[] = 'Connexion BDD echouee : ' . $e->getMessage();
        }
    }

    // Generer config.php
    if (empty($errors)) {
        $template = file_get_contents(__DIR__ . '/../config/config.example.php');

        $replacements = [
            "'mon-instance'" => "'" . addslashes($_POST['instance_id']) . "'",
            "'Mon Site Immobilier'" => "'" . addslashes($_POST['site_title']) . "'",
            "'mon-domaine.fr'" => "'" . addslashes($_POST['site_domain']) . "'",
            "'admin@mon-domaine.fr'" => "'" . addslashes($_POST['admin_email']) . "'",
            "'localhost'" => "'" . addslashes($_POST['db_host']) . "'",
            "'ma_base_de_donnees'" => "'" . addslashes($_POST['db_name']) . "'",
            "'mon_utilisateur_db'" => "'" . addslashes($_POST['db_user']) . "'",
            "'mon_mot_de_passe_db'" => "'" . addslashes($_POST['db_pass']) . "'",
            "'sk-proj-VOTRE_CLE_OPENAI'" => "'" . addslashes($_POST['openai_key'] ?? '') . "'",
            "'sk-ant-VOTRE_CLE_ANTHROPIC'" => "'" . addslashes($_POST['anthropic_key'] ?? '') . "'",
            "'Conseiller immobilier independant. Achat, vente, location.'" => "'" . addslashes($_POST['site_description'] ?? 'Conseiller immobilier independant. Achat, vente, location.') . "'",
            "'immobilier, achat, vente, location'" => "'" . addslashes($_POST['site_keywords'] ?? 'immobilier, achat, vente, location') . "'",
        ];

        $config = str_replace(array_keys($replacements), array_values($replacements), $template);

        if (file_put_contents(__DIR__ . '/../config/config.php', $config)) {
            $success[] = 'config/config.php genere avec succes';
        } else {
            $errors[] = 'Impossible d\'ecrire config/config.php - verifiez les permissions';
        }
    }

    // Creer les tables de base
    if (empty($errors) && isset($testDb)) {
        try {
            // Table admins
            $testDb->exec("CREATE TABLE IF NOT EXISTS `admins` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `nom` VARCHAR(255) DEFAULT NULL,
                `prenom` VARCHAR(255) DEFAULT NULL,
                `role` ENUM('superuser','admin') DEFAULT 'admin',
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = 'Table admins creee';

            // Table admin_module_permissions
            $testDb->exec("CREATE TABLE IF NOT EXISTS `admin_module_permissions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `admin_id` INT NOT NULL,
                `module_slug` VARCHAR(100) NOT NULL,
                `is_allowed` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `admin_module` (`admin_id`, `module_slug`),
                FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = 'Table admin_module_permissions creee';

            // Table pages
            $testDb->exec("CREATE TABLE IF NOT EXISTS `pages` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `content` LONGTEXT,
                `meta_title` VARCHAR(255) DEFAULT NULL,
                `meta_description` TEXT DEFAULT NULL,
                `status` ENUM('draft','published','archived') DEFAULT 'draft',
                `header_id` INT DEFAULT NULL,
                `footer_id` INT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = 'Table pages creee';

            // Table headers
            $testDb->exec("CREATE TABLE IF NOT EXISTS `headers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `logo_type` ENUM('text','image') DEFAULT 'text',
                `logo_text` VARCHAR(255) DEFAULT NULL,
                `logo_image` VARCHAR(500) DEFAULT NULL,
                `nav_items` JSON DEFAULT NULL,
                `bg_color` VARCHAR(20) DEFAULT '#ffffff',
                `text_color` VARCHAR(20) DEFAULT '#333333',
                `is_sticky` TINYINT(1) DEFAULT 0,
                `custom_css` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = 'Table headers creee';

            // Table footers
            $testDb->exec("CREATE TABLE IF NOT EXISTS `footers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `content` LONGTEXT DEFAULT NULL,
                `bg_color` VARCHAR(20) DEFAULT '#333333',
                `text_color` VARCHAR(20) DEFAULT '#ffffff',
                `custom_css` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = 'Table footers creee';

            // Table leads
            $testDb->exec("CREATE TABLE IF NOT EXISTS `leads` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nom` VARCHAR(255) DEFAULT NULL,
                `prenom` VARCHAR(255) DEFAULT NULL,
                `email` VARCHAR(255) NOT NULL,
                `telephone` VARCHAR(50) DEFAULT NULL,
                `message` TEXT DEFAULT NULL,
                `source` VARCHAR(100) DEFAULT 'website',
                `status` ENUM('new','contacted','qualified','lost') DEFAULT 'new',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = 'Table leads creee';

            // Table otp_codes (pour le login)
            $testDb->exec("CREATE TABLE IF NOT EXISTS `otp_codes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `admin_id` INT NOT NULL,
                `code` VARCHAR(10) NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `used` TINYINT(1) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = 'Table otp_codes creee';

            // Inserer le premier admin (superuser)
            $stmt = $testDb->prepare("INSERT IGNORE INTO admins (email, role) VALUES (?, 'superuser')");
            $stmt->execute([$_POST['admin_email']]);
            $success[] = 'Admin superuser cree : ' . htmlspecialchars($_POST['admin_email']);

            // Fichier lock
            file_put_contents($lockFile, date('Y-m-d H:i:s') . ' - Installation terminee');
            $success[] = 'Installation terminee avec succes !';
            $step = 'done';

        } catch (PDOException $e) {
            $errors[] = 'Erreur creation tables : ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Ecosysteme Immo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 30px; color: #1a1a2e; }
        .card { background: #fff; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .card h2 { margin-bottom: 20px; color: #16213e; border-bottom: 2px solid #e8e8e8; padding-bottom: 10px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #444; }
        label small { font-weight: 400; color: #888; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px;
            font-size: 14px; transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: #4a6cf7; }
        .btn { display: inline-block; padding: 12px 30px; background: #4a6cf7; color: #fff; border: none;
            border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .btn:hover { background: #3a5ce5; }
        .btn-center { text-align: center; margin-top: 20px; }
        .error { background: #ffe0e0; color: #c00; padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; }
        .success { background: #e0ffe0; color: #060; padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; }
        .done-box { text-align: center; padding: 40px; }
        .done-box h2 { color: #060; border: none; }
        .done-box a { color: #4a6cf7; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">
    <h1>Installation Nouveau Site</h1>

    <?php foreach ($errors as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <?php foreach ($success as $s): ?>
        <div class="success"><?= htmlspecialchars($s) ?></div>
    <?php endforeach; ?>

    <?php if ($step === 'done'): ?>
        <div class="card done-box">
            <h2>Installation terminee !</h2>
            <p style="margin: 15px 0;">Votre site est pret. Prochaines etapes :</p>
            <ol style="text-align: left; margin: 20px 40px;">
                <li>Configurez <code>config/smtp.php</code> (copiez depuis smtp.example.php)</li>
                <li>Connectez-vous a l'admin : <a href="/admin/login.php">/admin/login.php</a></li>
                <li><strong>Supprimez ce fichier</strong> : <code>setup/install.php</code></li>
                <li>Supprimez <code>config/.installed</code> seulement si reinstallation necessaire</li>
            </ol>
        </div>

    <?php else: ?>
        <form method="POST">
            <div class="card">
                <h2>Identite du site</h2>
                <div class="form-group">
                    <label>Identifiant instance <small>(ex: dupont-bordeaux)</small></label>
                    <input type="text" name="instance_id" value="<?= htmlspecialchars($_POST['instance_id'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Titre du site <small>(ex: Dupont Immobilier - Bordeaux)</small></label>
                    <input type="text" name="site_title" value="<?= htmlspecialchars($_POST['site_title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Domaine <small>(ex: dupont-immobilier.fr)</small></label>
                    <input type="text" name="site_domain" value="<?= htmlspecialchars($_POST['site_domain'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email administrateur</label>
                    <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Description du site <small>(SEO)</small></label>
                    <input type="text" name="site_description" value="<?= htmlspecialchars($_POST['site_description'] ?? 'Conseiller immobilier independant. Achat, vente, location.') ?>">
                </div>
                <div class="form-group">
                    <label>Mots-cles <small>(SEO, separes par virgule)</small></label>
                    <input type="text" name="site_keywords" value="<?= htmlspecialchars($_POST['site_keywords'] ?? 'immobilier, achat, vente, location') ?>">
                </div>
            </div>

            <div class="card">
                <h2>Base de donnees MySQL</h2>
                <div class="form-group">
                    <label>Serveur</label>
                    <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                </div>
                <div class="form-group">
                    <label>Nom de la base</label>
                    <input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Utilisateur</label>
                    <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>" required>
                </div>
            </div>

            <div class="card">
                <h2>Cles API <small style="font-weight:400; color:#888;">(optionnel)</small></h2>
                <div class="form-group">
                    <label>OpenAI API Key</label>
                    <input type="text" name="openai_key" value="<?= htmlspecialchars($_POST['openai_key'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Anthropic (Claude) API Key</label>
                    <input type="text" name="anthropic_key" value="<?= htmlspecialchars($_POST['anthropic_key'] ?? '') ?>">
                </div>
            </div>

            <div class="btn-center">
                <button type="submit" class="btn">Installer le site</button>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
