<?php
// admin/migrate_articles.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';

// Vérification de l'authentification
session_start();
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

// Fonction pour vérifier/créer la table articles
function checkAndCreateArticlesTable($pdo) {
    $tableName = 'articles';

    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
    if ($stmt->rowCount() === 0) {
        $sql = "CREATE TABLE `$tableName` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `slug` varchar(200) NOT NULL,
            `titre` varchar(255) NOT NULL,
            `excerpt` text DEFAULT NULL,
            `contenu` longtext DEFAULT NULL,
            `categorie` varchar(50) DEFAULT NULL,
            `tags` varchar(255) DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            `meta_title` varchar(255) DEFAULT NULL,
            `meta_desc` varchar(320) DEFAULT NULL,
            `statut` enum('publie','brouillon') NOT NULL DEFAULT 'brouillon',
            `published_at` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug_unique` (`slug`),
            KEY `idx_categorie` (`categorie`),
            KEY `idx_statut` (`statut`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $pdo->exec($sql);
        return "<div class='alert alert-success'>Table '$tableName' créée avec succès.</div>";
    }
    return "<div class='alert alert-info'>La table '$tableName' existe déjà.</div>";
}

// Fonction pour migrer les données depuis actualites/articles_temp
function migrateData($pdo) {
    $sourceTables = ['articles_temp', 'actualites'];
    $targetTable = 'articles';
    $results = [];

    foreach ($sourceTables as $sourceTable) {
        // Vérifier si la table source existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$sourceTable'");
        if ($stmt->rowCount() === 0) {
            $results[] = "<div class='alert alert-warning'>Table source '$sourceTable' non trouvée - ignorée</div>";
            continue;
        }

        // Récupérer les colonnes de la table source
        $sourceColumns = $pdo->query("SHOW COLUMNS FROM `$sourceTable`")->fetchAll(PDO::FETCH_COLUMN);
        $commonColumns = array_intersect($sourceColumns, [
            'id', 'slug', 'titre', 'excerpt', 'contenu', 'categorie',
            'image', 'statut', 'published_at', 'created_at', 'updated_at'
        ]);

        if (empty($commonColumns)) {
            $results[] = "<div class='alert alert-error'>Aucune colonne compatible trouvée dans '$sourceTable'</div>";
            continue;
        }

        // Construire la requête de migration
        $selectFields = [];
        foreach ($commonColumns as $column) {
            $selectFields[] = "`$column`";
        }

        // Ajouter les champs manquants avec des valeurs par défaut
        $defaultFields = [
            'slug' => "COALESCE(`slug`, CONCAT('article-', `id`))",
            'meta_title' => "COALESCE(`meta_title`, `titre`)",
            'meta_desc' => "COALESCE(`meta_desc`, LEFT(`excerpt`, 320))",
            'tags' => "''",
            'statut' => "COALESCE(`statut`, 'brouillon')",
            'created_at' => "COALESCE(`created_at`, NOW())",
            'updated_at' => "COALESCE(`updated_at`, NOW())"
        ];

        foreach ($defaultFields as $field => $default) {
            if (!in_array($field, $sourceColumns)) {
                $selectFields[] = "$default AS `$field`";
            }
        }

        $selectQuery = implode(', ', $selectFields);
        $insertQuery = "INSERT INTO `$targetTable` SELECT $selectQuery FROM `$sourceTable`";

        // Gérer les doublons
        $updateFields = array_diff($commonColumns, ['id']);
        $updateParts = [];
        foreach ($updateFields as $field) {
            $updateParts[] = "`$field` = VALUES(`$field`)";
        }
        $updateQuery = implode(', ', $updateParts);

        $fullQuery = "$insertQuery ON DUPLICATE KEY UPDATE $updateQuery, `updated_at` = NOW()";

        try {
            $pdo->beginTransaction();
            $pdo->exec($fullQuery);
            $count = $pdo->query("SELECT COUNT(*) FROM `$targetTable`")->fetchColumn();
            $pdo->commit();

            $results[] = "<div class='alert alert-success'>Migration depuis '$sourceTable' terminée: $count articles dans la table cible</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $results[] = "<div class='alert alert-error'>Erreur lors de la migration depuis '$sourceTable': " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

    return implode("\n", $results);
}

// Traitement du formulaire
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate'])) {
    $pdo = getDBConnection();
    $message = checkAndCreateArticlesTable($pdo) . "\n" . migrateData($pdo);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration des Articles</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .alert {
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            font-weight: 500;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .migration-box {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .steps {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .steps ul {
            margin: 10px 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <h1>Migration des Articles</h1>

    <div class="migration-box">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="steps">
                <h3>Ce que fera ce script :</h3>
                <ul>
                    <li>Vérifier si la table <code>articles</code> existe</li>
                    <li>La créer avec la structure complète si nécessaire</li>
                    <li>Migrer les données depuis :
                        <ul>
                            <li><code>articles_temp</code> (si existe)</li>
                            <li><code>actualites</code> (si existe)</li>
                        </ul>
                    </li>
                    <li>Gérer les doublons via les champs <code>id</code> et <code>slug</code></li>
                    <li>Ajouter des valeurs par défaut pour les champs manquants</li>
                </ul>
            </div>

            <p><strong>⚠️ Important :</strong> Faites une sauvegarde de votre base de données avant de lancer cette migration.</p>

            <button type="submit" name="migrate">Lancer la Migration</button>
        </form>
    </div>
</body>
</html>
