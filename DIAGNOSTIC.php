<?php
/**
 * DIAGNOSTIC COMPLET - VÉRIFICATION PRÉ-LIVRAISON
 * /DIAGNOSTIC.php
 *
 * Utilisation:
 * 1. Copier ce fichier à la racine
 * 2. Accéder à https://domaine.fr/DIAGNOSTIC.php
 * 3. Vérifier tous les tests
 * 4. Supprimer le fichier après vérification
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);

$results = [
    'system' => [],
    'config' => [],
    'database' => [],
    'modules' => [],
    'files' => [],
    'security' => [],
];

$status_colors = [
    'ok' => '#4CAF50',
    'warning' => '#FF9800',
    'error' => '#F44336'
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 Diagnostic Eduardo Desul</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: linear-gradient(135deg, #1a4d7a 0%, #0e3a5c 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        header h1 { font-size: 32px; margin-bottom: 10px; }
        header p { opacity: 0.9; }
        .section { background: white; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .section-title { background: #f9f9f9; padding: 15px 20px; border-left: 4px solid #1a4d7a; font-weight: 600; }
        .test-item { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 15px; }
        .test-item:last-child { border-bottom: none; }
        .status-badge { width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 12px; }
        .status-ok { background: #4CAF50; }
        .status-warning { background: #FF9800; }
        .status-error { background: #F44336; }
        .test-content { flex: 1; }
        .test-name { font-weight: 500; margin-bottom: 3px; }
        .test-value { font-size: 13px; color: #666; font-family: monospace; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .summary-card { background: white; padding: 20px; border-radius: 8px; text-align: center; }
        .summary-card .number { font-size: 28px; font-weight: bold; }
        .summary-card .label { font-size: 12px; color: #666; margin-top: 5px; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px; margin-top: 5px; }
        footer { text-align: center; padding: 20px; color: #666; font-size: 12px; margin-top: 40px; }
        .warning-box { background: #fff3cd; border-left: 4px solid #FF9800; padding: 15px 20px; margin: 20px 0; border-radius: 4px; }
        .error-box { background: #f8d7da; border-left: 4px solid #F44336; padding: 15px 20px; margin: 20px 0; border-radius: 4px; }
        .success-box { background: #d4edda; border-left: 4px solid #4CAF50; padding: 15px 20px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>📋 Diagnostic de Déploiement</h1>
        <p>Eduardo Desul Immobilier - Vérification complète avant livraison</p>
    </header>

    <?php
    // ══════════════════════════════════════════════════════════════
    // 1. SYSTÈME & PHP
    // ══════════════════════════════════════════════════════════════

    $results['system']['PHP Version'] = [
        'value' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'error',
        'required' => '7.4.0+'
    ];

    $results['system']['OS'] = [
        'value' => php_uname('s'),
        'status' => 'ok'
    ];

    // Extensions PHP
    $required_extensions = ['PDO', 'pdo_mysql', 'json', 'curl', 'openssl', 'mbstring'];
    foreach ($required_extensions as $ext) {
        $results['system']["Extension: $ext"] = [
            'value' => extension_loaded($ext) ? 'Activée' : 'MANQUANTE',
            'status' => extension_loaded($ext) ? 'ok' : 'error'
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // 2. CONFIGURATION
    // ══════════════════════════════════════════════════════════════

    // Vérifier config.php
    $config_exists = file_exists(ROOT_PATH . '/config/config.php');
    $results['config']['config/config.php existe'] = [
        'value' => $config_exists ? 'Oui' : 'NON',
        'status' => $config_exists ? 'ok' : 'error',
        'hint' => $config_exists ? '' : 'Copier config.example.php → config.php'
    ];

    // Vérifier smtp.php
    $smtp_exists = file_exists(ROOT_PATH . '/config/smtp.php');
    $results['config']['config/smtp.php existe'] = [
        'value' => $smtp_exists ? 'Oui' : 'NON',
        'status' => $smtp_exists ? 'ok' : 'warning',
        'hint' => $smtp_exists ? '' : 'Copier smtp.example.php → smtp.php'
    ];

    // Vérifier constantes
    if ($config_exists) {
        require_once ROOT_PATH . '/config/config.php';

        $constants = [
            'INSTANCE_ID' => ['value' => INSTANCE_ID ?? 'NON DÉFINI', 'required' => true],
            'SITE_TITLE' => ['value' => SITE_TITLE ?? 'NON DÉFINI', 'required' => true],
            'SITE_DOMAIN' => ['value' => SITE_DOMAIN ?? 'NON DÉFINI', 'required' => true],
            'DB_HOST' => ['value' => DB_HOST ?? 'NON DÉFINI', 'required' => true],
            'DB_NAME' => ['value' => DB_NAME ?? 'NON DÉFINI', 'required' => true],
            'DB_USER' => ['value' => DB_USER ?? 'NON DÉFINI', 'required' => true],
        ];

        foreach ($constants as $const => $data) {
            $results['config']["Constante: $const"] = [
                'value' => $data['value'],
                'status' => (strpos($data['value'], 'NON') === false && !empty($data['value'])) ? 'ok' : 'warning'
            ];
        }
    }

    // ══════════════════════════════════════════════════════════════
    // 3. BASE DE DONNÉES
    // ══════════════════════════════════════════════════════════════

    if ($config_exists) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . (DB_PORT ?? 3306) . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $results['database']['Connexion'] = [
                'value' => 'Connectée',
                'status' => 'ok'
            ];

            // Compter les tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $results['database']['Nombre de tables'] = [
                'value' => count($tables),
                'status' => count($tables) > 10 ? 'ok' : 'warning'
            ];

            // Tables importantes
            $important_tables = [
                'admins' => 'Comptes admin',
                'pages' => 'Pages CMS',
                'articles' => 'Articles blog',
                'leads' => 'Leads/Prospects',
                'estimation_requests' => 'Demandes estimation',
                'settings' => 'Paramètres site'
            ];

            foreach ($important_tables as $table => $label) {
                $exists = in_array($table, $tables);
                $results['database']["Table: $label"] = [
                    'value' => $exists ? 'Oui' : 'NON',
                    'status' => $exists ? 'ok' : 'warning'
                ];
            }

        } catch (PDOException $e) {
            $results['database']['Connexion'] = [
                'value' => 'ERREUR',
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    } else {
        $results['database']['Connexion'] = [
            'value' => 'IMPOSSIBLE - config.php manquant',
            'status' => 'error'
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // 4. MODULES
    // ══════════════════════════════════════════════════════════════

    $modules_path = ROOT_PATH . '/admin/modules';
    $modules = array_filter(scandir($modules_path), function($item) {
        return $item !== '.' && $item !== '..' && is_dir($modules_path . '/' . $item);
    });

    $critical_modules = [
        'pages' => 'Pages CMS',
        'articles' => 'Articles Blog',
        'immobilier' => 'Module Immobilier',
        'crm' => 'CRM',
        'marketing' => 'Marketing/Leads',
        'seo' => 'SEO',
        'content' => 'Contenu'
    ];

    foreach ($critical_modules as $module => $label) {
        $exists = in_array($module, $modules);
        $results['modules']["$label ($module)"] = [
            'value' => $exists ? 'Installé' : 'MANQUANT',
            'status' => $exists ? 'ok' : 'warning'
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // 5. FICHIERS IMPORTANTS
    // ══════════════════════════════════════════════════════════════

    $important_files = [
        'index.php' => 'Point d\'entrée',
        'front/page.php' => 'Router frontend',
        'admin/login.php' => 'Login admin',
        'admin/dashboard.php' => 'Dashboard admin',
        '.htaccess' => 'Sécurité Apache',
        'includes/classes/Database.php' => 'Classe Database'
    ];

    foreach ($important_files as $file => $label) {
        $path = ROOT_PATH . '/' . $file;
        $exists = file_exists($path);
        $results['files']["$label ($file)"] = [
            'value' => $exists ? 'Présent' : 'MANQUANT',
            'status' => $exists ? 'ok' : 'error'
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // 6. SÉCURITÉ
    // ══════════════════════════════════════════════════════════════

    // Vérifier que config.php n'est pas versionnée (doit être .gitignore)
    if (file_exists(ROOT_PATH . '/.gitignore')) {
        $gitignore = file_get_contents(ROOT_PATH . '/.gitignore');
        $config_ignored = strpos($gitignore, 'config/config.php') !== false;
        $results['security']['config.php ignorée par git'] = [
            'value' => $config_ignored ? 'Oui' : 'NON',
            'status' => $config_ignored ? 'ok' : 'warning'
        ];
    }

    // Vérifier uploads writable
    $uploads_writable = is_writable(ROOT_PATH . '/uploads');
    $results['security']['uploads/ writable'] = [
        'value' => $uploads_writable ? 'Oui' : 'NON',
        'status' => $uploads_writable ? 'ok' : 'warning'
    ];

    // Vérifier logs writable
    $logs_writable = is_writable(ROOT_PATH . '/logs');
    $results['security']['logs/ writable'] = [
        'value' => $logs_writable ? 'Oui' : 'NON',
        'status' => $logs_writable ? 'ok' : 'warning'
    ];

    // ══════════════════════════════════════════════════════════════
    // AFFICHAGE RÉSULTATS
    // ══════════════════════════════════════════════════════════════

    $summary = [
        'ok' => 0,
        'warning' => 0,
        'error' => 0
    ];

    foreach ($results as $section => $tests) {
        foreach ($tests as $test => $data) {
            $summary[$data['status']]++;
        }
    }

    ?>

    <div class="summary">
        <div class="summary-card">
            <div class="number" style="color: #4CAF50;"><?php echo $summary['ok']; ?></div>
            <div class="label">Tests OK</div>
        </div>
        <div class="summary-card">
            <div class="number" style="color: #FF9800;"><?php echo $summary['warning']; ?></div>
            <div class="label">Avertissements</div>
        </div>
        <div class="summary-card">
            <div class="number" style="color: #F44336;"><?php echo $summary['error']; ?></div>
            <div class="label">Erreurs</div>
        </div>
        <div class="summary-card">
            <div class="number"><?php echo $summary['ok'] + $summary['warning'] + $summary['error']; ?></div>
            <div class="label">Tests Totaux</div>
        </div>
    </div>

    <?php if ($summary['error'] > 0) { ?>
        <div class="error-box">
            <strong>⚠️ Erreurs détectées!</strong> Veuillez corriger les erreurs avant la livraison.
        </div>
    <?php } elseif ($summary['warning'] > 0) { ?>
        <div class="warning-box">
            <strong>⚠️ Avertissements</strong> - À traiter avant la mise en production.
        </div>
    <?php } else { ?>
        <div class="success-box">
            <strong>✅ Tous les tests sont passés!</strong> Le site est prêt pour la livraison.
        </div>
    <?php } ?>

    <?php foreach ($results as $section => $tests) { ?>
        <div class="section">
            <div class="section-title">
                <?php
                $icons = [
                    'system' => '💻',
                    'config' => '⚙️',
                    'database' => '🗄️',
                    'modules' => '📦',
                    'files' => '📁',
                    'security' => '🔒'
                ];
                echo ($icons[$section] ?? '📋') . ' ' . ucfirst($section);
                ?>
            </div>

            <?php foreach ($tests as $test => $data) { ?>
                <div class="test-item">
                    <div class="status-badge status-<?php echo $data['status']; ?>">
                        <?php
                        if ($data['status'] === 'ok') echo '✓';
                        elseif ($data['status'] === 'warning') echo '!';
                        else echo '✕';
                        ?>
                    </div>
                    <div class="test-content">
                        <div class="test-name"><?php echo htmlspecialchars($test); ?></div>
                        <div class="test-value">
                            <?php echo htmlspecialchars($data['value']); ?>
                        </div>
                        <?php if (!empty($data['hint'])) { ?>
                            <div class="code">💡 <?php echo htmlspecialchars($data['hint']); ?></div>
                        <?php } ?>
                        <?php if (!empty($data['error'])) { ?>
                            <div class="code" style="color: #F44336;">❌ <?php echo htmlspecialchars($data['error']); ?></div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <footer>
        <p>🔐 <strong>IMPORTANT:</strong> Supprimez ce fichier après vérification!</p>
        <p>Diagnostic généré le <?php echo date('d/m/Y H:i:s'); ?></p>
    </footer>

</div>

</body>
</html>
