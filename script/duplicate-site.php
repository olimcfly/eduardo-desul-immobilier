#!/usr/bin/env php
<?php

declare(strict_types=1);

// ============================================================
// SCRIPT : Duplication du site pour un nouveau conseiller
// Usage  : php script/duplicate-site.php [--dry-run]
//
// Ce script copie l'intégralité du projet, crée une nouvelle
// base de données, joue les migrations, génère un .env
// personnalisé et crée le compte administrateur.
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
define('SCRIPT_VERSION', '1.0.0');

// ── Couleurs terminal ────────────────────────────────────────
const C_RESET  = "\033[0m";
const C_BOLD   = "\033[1m";
const C_GREEN  = "\033[0;32m";
const C_YELLOW = "\033[0;33m";
const C_BLUE   = "\033[0;34m";
const C_RED    = "\033[0;31m";
const C_CYAN   = "\033[0;36m";
const C_DIM    = "\033[2m";

// ── Helpers affichage ────────────────────────────────────────
function step(int $n, int $total, string $label): void
{
    echo C_BOLD . C_BLUE . "\n[{$n}/{$total}] " . C_RESET . C_BOLD . $label . C_RESET . "\n";
    echo C_DIM . str_repeat('─', 60) . C_RESET . "\n";
}

function ok(string $msg): void
{
    echo C_GREEN . "  ✓ " . C_RESET . $msg . "\n";
}

function warn(string $msg): void
{
    echo C_YELLOW . "  ⚠ " . C_RESET . $msg . "\n";
}

function fail(string $msg, int $code = 1): never
{
    echo C_RED . "\n  ✗ ERREUR : " . C_RESET . $msg . "\n\n";
    exit($code);
}

function info(string $msg): void
{
    echo C_DIM . "    " . $msg . C_RESET . "\n";
}

// ── Helpers saisie ───────────────────────────────────────────
function ask(string $prompt, string $default = '', bool $secret = false): string
{
    $hint = $default !== '' ? C_DIM . " [{$default}]" . C_RESET : '';
    echo C_CYAN . "  → " . C_RESET . $prompt . $hint . " : ";

    if ($secret && PHP_OS_FAMILY !== 'Windows') {
        system('stty -echo');
    }

    $value = trim((string) fgets(STDIN));

    if ($secret && PHP_OS_FAMILY !== 'Windows') {
        system('stty echo');
        echo "\n";
    }

    return $value !== '' ? $value : $default;
}

function askBool(string $prompt, bool $default = true): bool
{
    $hint = $default ? C_DIM . ' (O/n)' . C_RESET : C_DIM . ' (o/N)' . C_RESET;
    echo C_CYAN . "  → " . C_RESET . $prompt . $hint . " : ";
    $value = strtolower(trim((string) fgets(STDIN)));
    if ($value === '') {
        return $default;
    }
    return in_array($value, ['o', 'oui', 'y', 'yes', '1'], true);
}

function generateSecret(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function generateDbPassword(): string
{
    $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    $pass  = '';
    for ($i = 0; $i < 16; $i++) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pass;
}

// ── Copie récursive avec exclusions ──────────────────────────
function copyDirRecursive(string $src, string $dst, array $excludes, bool $dryRun): int
{
    $src   = rtrim(realpath($src) ?: $src, '/\\');
    $dst   = rtrim($dst, '/\\');
    $count = 0;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $srcPath     = $item->getPathname();
        $relative    = ltrim(substr($srcPath, strlen($src)), '/\\');
        $dstPath     = $dst . DIRECTORY_SEPARATOR . $relative;

        if (isExcluded($relative, $excludes)) {
            continue;
        }

        if ($item->isDir()) {
            if (!$dryRun && !is_dir($dstPath)) {
                mkdir($dstPath, 0775, true);
            }
        } else {
            if (!$dryRun) {
                $parent = dirname($dstPath);
                if (!is_dir($parent)) {
                    mkdir($parent, 0775, true);
                }
                copy($srcPath, $dstPath);
            }
            $count++;
        }
    }

    return $count;
}

function isExcluded(string $relative, array $excludes): bool
{
    $relative  = str_replace('\\', '/', $relative);
    $parts     = explode('/', $relative);

    foreach ($excludes as $pattern) {
        // Correspondance exacte ou glob sur le chemin complet
        if (fnmatch($pattern, $relative)) {
            return true;
        }
        // Correspondance sur le nom de fichier/dossier seul
        if (fnmatch($pattern, end($parts))) {
            return true;
        }
        // Correspondance sur chaque segment de chemin (pour exclure dossiers entiers)
        foreach ($parts as $i => $segment) {
            $partial = implode('/', array_slice($parts, 0, $i + 1));
            if (fnmatch($pattern, $partial) || $partial === $pattern) {
                return true;
            }
        }
    }

    return false;
}

// ── Exécution des migrations SQL ─────────────────────────────
function runMigrations(PDO $pdo, string $dir): void
{
    $files = glob($dir . '/*.sql');
    if (empty($files)) {
        warn("Aucun fichier SQL trouvé dans {$dir}");
        return;
    }

    sort($files); // Tri alphabétique = ordre numérique (001, 002…)

    foreach ($files as $file) {
        $name = basename($file);
        $sql  = file_get_contents($file);
        if ($sql === false) {
            warn("Lecture impossible : {$name}");
            continue;
        }

        try {
            $pdo->exec($sql);
            ok("Migration : {$name}");
        } catch (PDOException $e) {
            // Tolérer "table already exists" pour idempotence
            if (str_contains($e->getMessage(), 'already exists')) {
                info("Déjà appliquée : {$name}");
            } else {
                throw new RuntimeException("Erreur migration [{$name}] : " . $e->getMessage());
            }
        }
    }
}

// ── Génération du fichier .env ────────────────────────────────
function buildEnvContent(array $c): string
{
    $date = date('Y-m-d H:i:s');
    return <<<ENV
# ============================================================
# FICHIER D'ENVIRONNEMENT — {$c['app_name']}
# Généré automatiquement le {$date} par script/duplicate-site.php
# ============================================================

# ── Application ──────────────────────────────────────────────
APP_NAME="{$c['app_name']}"
APP_URL={$c['app_url']}
APP_EMAIL={$c['app_email']}
APP_ENV=production
APP_DEBUG=false

APP_PHONE="{$c['app_phone']}"
APP_SIRET="{$c['app_siret']}"

# ── Base de donnees MySQL/MariaDB ────────────────────────────
DB_HOST={$c['db_host']}
DB_PORT={$c['db_port']}
DB_NAME={$c['db_name']}
DB_USER={$c['db_user']}
DB_PASS={$c['db_pass']}
DB_CHARSET=utf8mb4

# ── Session PHP ───────────────────────────────────────────────
SESSION_NAME={$c['session_name']}
SESSION_LIFE=28800

# ── SMTP (envoi d emails) ─────────────────────────────────────
MAIL_FROM={$c['smtp_from']}
MAIL_FROM_NAME="{$c['app_name']}"

SMTP_HOST={$c['smtp_host']}
SMTP_PORT={$c['smtp_port']}
SMTP_USER={$c['smtp_user']}
SMTP_PASS={$c['smtp_pass']}
SMTP_FROM={$c['smtp_from']}
SMTP_FROM_NAME="{$c['app_name']}"
SMTP_SECURE={$c['smtp_secure']}

# ── IMAP (réception emails) ───────────────────────────────────
IMAP_HOST=
IMAP_PORT=993
IMAP_USER=
IMAP_PASS=
IMAP_SECURE=ssl

# ── Notification email ────────────────────────────────────────
NOTIF_EMAIL={$c['notif_email']}

# ── Google My Business API ───────────────────────────────────
GMB_ACCOUNT_ID=
GMB_CLIENT_ID=
GMB_CLIENT_SECRET=
GMB_REFRESH_TOKEN=

# ── Gmail API (Messagerie intégrée) ──────────────────────────
GMAIL_CLIENT_ID=
GMAIL_CLIENT_SECRET=

# ── Google Analytics ─────────────────────────────────────────
GA_MEASUREMENT_ID=
SEARCH_CONSOLE_VERIFICATION=

# ── OpenAI (génération de contenu) ───────────────────────────
OPENAI_API_KEY=

# ── Sécurité ──────────────────────────────────────────────────
CSRF_SECRET={$c['csrf_secret']}
ENV;
}

// ════════════════════════════════════════════════════════════
// POINT D'ENTRÉE
// ════════════════════════════════════════════════════════════

$isDryRun    = in_array('--dry-run', $argv ?? [], true);
$skipDb      = in_array('--skip-db', $argv ?? [], true);
$TOTAL_STEPS = 8;

// Vérification que le script tourne en CLI
if (PHP_SAPI !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.\n");
}

// ── Bannière ─────────────────────────────────────────────────
echo C_BOLD . C_BLUE;
echo "\n";
echo "  ╔═══════════════════════════════════════════════════════╗\n";
echo "  ║     DUPLICATION SITE — Nouveau Conseiller Immo       ║\n";
echo "  ║                    v" . SCRIPT_VERSION . "                             ║\n";
echo "  ╚═══════════════════════════════════════════════════════╝\n";
echo C_RESET;

if ($isDryRun) {
    echo C_YELLOW . "\n  MODE SIMULATION — aucune action réelle ne sera effectuée.\n" . C_RESET;
}
if ($skipDb) {
    echo C_YELLOW . "  --skip-db activé — la base de données ne sera pas touchée.\n" . C_RESET;
}

echo C_DIM . "\n  Ce script duplique le site complet pour un nouveau conseiller.\n";
echo "  Préparez : chemin de destination, credentials MySQL, SMTP.\n\n" . C_RESET;

// ══════════════════════════════════════════════════════════════
// ÉTAPE 1 — Informations du conseiller
// ══════════════════════════════════════════════════════════════
step(1, $TOTAL_STEPS, "Informations du nouveau conseiller");

$conseillerNom = '';
while (trim($conseillerNom) === '') {
    $conseillerNom = ask("Nom complet du conseiller (ex: Marie Dupont)");
}

$agenceNom = ask("Nom de l'agence/site", trim($conseillerNom) . " Immobilier");
$appUrl    = ask("URL du site (avec https://)", "https://exemple-immobilier.fr");

// Extraire le host pour les valeurs par défaut
$parsedHost = parse_url($appUrl, PHP_URL_HOST) ?: 'exemple-immobilier.fr';
$defaultEmail = 'contact@' . $parsedHost;

$appEmail    = ask("Email de contact", $defaultEmail);
$appPhone    = ask("Téléphone", "+33 X XX XX XX XX");
$appSiret    = ask("SIRET (optionnel, Entrée pour ignorer)", "");
$ville       = ask("Ville principale du conseiller", "Aix-en-Provence");
$notifEmail  = ask("Email de réception des leads", $appEmail);

ok("Conseiller : {$conseillerNom} — {$agenceNom}");

// ══════════════════════════════════════════════════════════════
// ÉTAPE 2 — Répertoire de destination
// ══════════════════════════════════════════════════════════════
step(2, $TOTAL_STEPS, "Répertoire de destination");

// Générer un slug propre à partir du nom
$slug          = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', trim($conseillerNom)));
$slug          = trim($slug, '-');
$defaultTarget = dirname(ROOT_PATH) . '/' . $slug . '-immobilier';

$targetDir = ask("Chemin absolu de destination", $defaultTarget);
$targetDir = rtrim($targetDir, '/\\');

if (!str_starts_with($targetDir, '/')) {
    fail("Le chemin doit être absolu (commencer par /).");
}

if ($targetDir === ROOT_PATH) {
    fail("La destination ne peut pas être le répertoire source.");
}

if (is_dir($targetDir) && !$isDryRun) {
    warn("Le répertoire existe déjà : {$targetDir}");
    if (!askBool("Écraser le contenu existant ?", false)) {
        echo "\nAnnulé.\n";
        exit(0);
    }
} else {
    ok("Destination : {$targetDir}");
}

// ══════════════════════════════════════════════════════════════
// ÉTAPE 3 — Base de données
// ══════════════════════════════════════════════════════════════
step(3, $TOTAL_STEPS, "Configuration base de données");

$dbSlug  = str_replace('-', '_', $slug);
$dbHost  = ask("Host MySQL", "localhost");
$dbPort  = ask("Port MySQL", "3306");
$dbName  = ask("Nom de la nouvelle base de données", substr($dbSlug, 0, 16) . "_immo");
$dbUser  = ask("Utilisateur MySQL", substr($dbSlug, 0, 16) . "_user");
$dbPass  = ask("Mot de passe MySQL (Entrée = généré)", "");
if ($dbPass === '') {
    $dbPass = generateDbPassword();
    info("Mot de passe généré : {$dbPass}");
}

$createDbAndUser = false;
if (!$skipDb) {
    $createDbAndUser = askBool("Créer la base et l'utilisateur automatiquement (nécessite un accès root MySQL) ?", true);

    if ($createDbAndUser) {
        echo C_DIM . "\n  Credentials MySQL avec droits CREATE/GRANT :\n" . C_RESET;
        $dbRootUser = ask("Utilisateur MySQL admin", "root");
        $dbRootPass = ask("Mot de passe MySQL admin", "", true);
    }
}

// ══════════════════════════════════════════════════════════════
// ÉTAPE 4 — Email SMTP
// ══════════════════════════════════════════════════════════════
step(4, $TOTAL_STEPS, "Configuration SMTP");

$smtpHost   = ask("Serveur SMTP", "mail.{$parsedHost}");
$smtpPort   = ask("Port SMTP (465=SSL, 587=TLS)", "465");
$smtpUser   = ask("Identifiant SMTP", $appEmail);
$smtpPass   = ask("Mot de passe SMTP", "", true);
$smtpSecure = ask("Chiffrement (ssl/tls)", "ssl");

// ══════════════════════════════════════════════════════════════
// ÉTAPE 5 — Compte administrateur
// ══════════════════════════════════════════════════════════════
step(5, $TOTAL_STEPS, "Compte administrateur");

$adminEmail = ask("Email du compte admin", $appEmail);
$adminName  = ask("Nom du compte admin", $conseillerNom);

$adminPass = '';
while (strlen($adminPass) < 8) {
    $adminPass = ask("Mot de passe admin (8 caractères min.)", "", true);
    if (strlen($adminPass) < 8) {
        warn("Minimum 8 caractères.");
    }
}

$adminPassConfirm = ask("Confirmer le mot de passe admin", "", true);
if ($adminPass !== $adminPassConfirm) {
    fail("Les mots de passe ne correspondent pas.");
}

// ══════════════════════════════════════════════════════════════
// RÉCAPITULATIF AVANT EXÉCUTION
// ══════════════════════════════════════════════════════════════
echo "\n" . C_BOLD . C_BLUE;
echo "  ╔═══════════════════════════════════════════════════════╗\n";
echo "  ║                    RÉCAPITULATIF                     ║\n";
echo "  ╚═══════════════════════════════════════════════════════╝\n";
echo C_RESET . "\n";

$recap = [
    ["Conseiller",       $conseillerNom],
    ["Agence",           $agenceNom],
    ["URL",              $appUrl],
    ["Email contact",    $appEmail],
    ["Ville",            $ville],
    ["Destination",      $targetDir],
    ["Base de données",  "{$dbUser}@{$dbHost}:{$dbPort}/{$dbName}"],
    ["SMTP",             "{$smtpUser}@{$smtpHost}:{$smtpPort}"],
    ["Admin email",      $adminEmail],
];

foreach ($recap as [$label, $value]) {
    printf("  " . C_DIM . "%-18s" . C_RESET . " %s\n", $label, C_BOLD . $value . C_RESET);
}

echo "\n";
if (!askBool("Confirmer et lancer la duplication ?", true)) {
    echo "\nAnnulé.\n";
    exit(0);
}

// ══════════════════════════════════════════════════════════════
// ÉTAPE 6 — Copie des fichiers
// ══════════════════════════════════════════════════════════════
step(6, $TOTAL_STEPS, "Copie des fichiers");

// Patterns exclus du projet (relatifs à la racine)
$excludes = [
    // Secrets & config locaux
    '.env', '.env.local', '.env.production', '.env.staging',
    // Dépendances (réinstallées par composer)
    'vendor',
    // VCS
    '.git',
    // Logs & cache
    'logs', 'cache', 'storage',
    // Uploads (données du conseiller source, non pertinentes)
    'uploads',
    // Assets compilés (regénérés)
    'public/assets/build', 'public/admin/assets/build', 'modules/gmb/assets/build',
    'storage/cache/assets-manifest.json',
    // Archives & artefacts
    '*.zip', '*.tar', '*.gz', '*.tar.gz',
    // Fichiers temporaires
    '*.log', 'error_log', '*.tmp', '*.bak', '*.swp',
    // Outils de développement
    'node_modules', '.DS_Store', 'Thumbs.db',
    // Ce script lui-même ne se duplique pas pour éviter la confusion
    // (on laisse quand même le script dans la copie)
];

if (!$isDryRun) {
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
        fail("Impossible de créer le répertoire : {$targetDir}");
    }
}

info("Copie en cours…");
$fileCount = copyDirRecursive(ROOT_PATH, $targetDir, $excludes, $isDryRun);
ok("{$fileCount} fichiers copiés");

// Créer les répertoires runtime vides avec .gitkeep
$runtimeDirs = [
    'logs',
    'storage',
    'storage/cache',
    'storage/sessions',
    'uploads',
    'cache',
];

foreach ($runtimeDirs as $dir) {
    $path = $targetDir . '/' . $dir;
    if (!$isDryRun) {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        // Créer .gitkeep si absent
        $keep = $path . '/.gitkeep';
        if (!file_exists($keep)) {
            touch($keep);
        }
    }
    ok("Dossier runtime : /{$dir}");
}

// ══════════════════════════════════════════════════════════════
// ÉTAPE 7 — Génération du fichier .env
// ══════════════════════════════════════════════════════════════
step(7, $TOTAL_STEPS, "Génération du fichier .env");

// Construire un nom de session unique basé sur le slug
$sessionName = 'immo_' . substr(str_replace('-', '_', $slug), 0, 20) . '_sess';

$envConfig = [
    'app_name'     => $agenceNom,
    'app_url'      => $appUrl,
    'app_email'    => $appEmail,
    'app_phone'    => $appPhone,
    'app_siret'    => $appSiret,
    'db_host'      => $dbHost,
    'db_port'      => $dbPort,
    'db_name'      => $dbName,
    'db_user'      => $dbUser,
    'db_pass'      => $dbPass,
    'session_name' => $sessionName,
    'smtp_host'    => $smtpHost,
    'smtp_port'    => $smtpPort,
    'smtp_user'    => $smtpUser,
    'smtp_pass'    => $smtpPass,
    'smtp_from'    => $appEmail,
    'smtp_secure'  => $smtpSecure,
    'notif_email'  => $notifEmail,
    'csrf_secret'  => generateSecret(32),
];

$envContent = buildEnvContent($envConfig);
$envPath    = $targetDir . '/.env';

if (!$isDryRun) {
    if (file_put_contents($envPath, $envContent) === false) {
        fail("Impossible d'écrire le fichier .env dans {$targetDir}");
    }
    chmod($envPath, 0600); // Lecture restreinte au propriétaire
}

ok(".env créé : {$envPath}");
info("Permissions : 600 (lecture propriétaire uniquement)");

// ══════════════════════════════════════════════════════════════
// ÉTAPE 8 — Base de données, migrations, compte admin
// ══════════════════════════════════════════════════════════════
step(8, $TOTAL_STEPS, "Base de données, migrations et compte admin");

if ($isDryRun || $skipDb) {
    warn($isDryRun ? "[SIMULATION] Étape DB ignorée." : "[--skip-db] Étape DB ignorée.");
} else {
    // ── Création de la base et de l'utilisateur (via root) ──
    if ($createDbAndUser) {
        try {
            info("Connexion MySQL admin…");
            $rootDsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $rootPdo = new PDO($rootDsn, $dbRootUser ?? 'root', $dbRootPass ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $rootPdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$dbName}`
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
            ok("Base de données créée : {$dbName}");

            // Créer l'utilisateur (MySQL 5.7+ / MariaDB)
            $rootPdo->exec(
                "CREATE USER IF NOT EXISTS '{$dbUser}'@'localhost' IDENTIFIED BY '{$dbPass}'"
            );
            $rootPdo->exec(
                "GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'localhost'"
            );
            $rootPdo->exec("FLUSH PRIVILEGES");
            ok("Utilisateur MySQL créé : {$dbUser}@localhost");

        } catch (PDOException $e) {
            fail(
                "Création DB impossible : " . $e->getMessage() . "\n\n" .
                "  Créez la base manuellement puis relancez avec --skip-db."
            );
        }
    }

    // ── Connexion avec le nouvel utilisateur ─────────────────
    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
        ok("Connexion à {$dbName} établie");
    } catch (PDOException $e) {
        fail("Connexion à la nouvelle base impossible : " . $e->getMessage());
    }

    // ── Migrations ───────────────────────────────────────────
    echo "\n";
    info("Exécution des migrations SQL…");
    $migrationsDir = ROOT_PATH . '/database/migrations';
    try {
        runMigrations($pdo, $migrationsDir);
    } catch (RuntimeException $e) {
        fail($e->getMessage());
    }

    // ── Settings de base ─────────────────────────────────────
    echo "\n";
    info("Mise à jour des paramètres du site…");
    $settings = [
        ['site_nom',       $agenceNom,              'general'],
        ['site_telephone', $appPhone,                'general'],
        ['site_email',     $appEmail,                'general'],
        ['site_adresse',   $ville . ', France',      'general'],
        ['site_ville',     $ville,                   'general'],
        ['site_url',       $appUrl,                  'general'],
    ];

    foreach ($settings as [$cle, $valeur, $groupe]) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO settings (cle, valeur, groupe)
                 VALUES (:cle, :valeur, :groupe)
                 ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)"
            );
            $stmt->execute([':cle' => $cle, ':valeur' => $valeur, ':groupe' => $groupe]);
            ok("Setting : {$cle} = {$valeur}");
        } catch (PDOException $e) {
            warn("Setting ignoré [{$cle}] : " . $e->getMessage());
        }
    }

    // ── Compte administrateur ────────────────────────────────
    echo "\n";
    info("Création du compte administrateur…");
    $hash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (email, password, name, role, created_at)
             VALUES (:email, :password, :name, 'admin', NOW())
             ON DUPLICATE KEY UPDATE password = VALUES(password), name = VALUES(name)"
        );
        $stmt->execute([':email' => $adminEmail, ':password' => $hash, ':name' => $adminName]);
        ok("Compte admin créé : {$adminEmail}");
    } catch (PDOException $e) {
        fail("Création du compte admin impossible : " . $e->getMessage());
    }
}

// ════════════════════════════════════════════════════════════
// RÉSUMÉ FINAL
// ════════════════════════════════════════════════════════════
echo "\n" . C_BOLD . C_GREEN;
echo "  ╔═══════════════════════════════════════════════════════╗\n";
echo "  ║           DUPLICATION TERMINÉE AVEC SUCCÈS !         ║\n";
echo "  ╚═══════════════════════════════════════════════════════╝\n";
echo C_RESET . "\n";

// Accès admin
echo C_BOLD . "  Accès administrateur :\n" . C_RESET;
printf("  " . C_DIM . "%-14s" . C_RESET . " %s\n", "URL admin",  C_BOLD . $appUrl . "/admin" . C_RESET);
printf("  " . C_DIM . "%-14s" . C_RESET . " %s\n", "Email",      C_BOLD . $adminEmail . C_RESET);
printf("  " . C_DIM . "%-14s" . C_RESET . " %s\n", "Mot de passe", C_BOLD . $adminPass . C_RESET);

if ($dbPass !== '') {
    echo "\n" . C_BOLD . "  Base de données :\n" . C_RESET;
    printf("  " . C_DIM . "%-14s" . C_RESET . " %s\n", "Base",       C_BOLD . $dbName . C_RESET);
    printf("  " . C_DIM . "%-14s" . C_RESET . " %s\n", "Utilisateur", C_BOLD . $dbUser . C_RESET);
    printf("  " . C_DIM . "%-14s" . C_RESET . " %s\n", "Mot de passe", C_BOLD . $dbPass . C_RESET);
}

// Prochaines étapes
echo "\n" . C_BOLD . "  Prochaines étapes :\n" . C_RESET . "\n";

echo C_DIM . "  1. Installer les dépendances PHP (Composer) :\n" . C_RESET;
echo "     " . C_YELLOW . "cd {$targetDir} && composer install --no-dev --optimize-autoloader\n" . C_RESET . "\n";

echo C_DIM . "  2. Compiler les assets CSS/JS :\n" . C_RESET;
echo "     " . C_YELLOW . "cd {$targetDir} && php script/build-assets.php\n" . C_RESET . "\n";

echo C_DIM . "  3. Configurer le virtualhost (Apache/Nginx) :\n" . C_RESET;
echo "     " . C_YELLOW . "DocumentRoot : {$targetDir}/public\n" . C_RESET;
echo "     " . C_YELLOW . "ServerName   : {$parsedHost}\n" . C_RESET . "\n";

echo C_DIM . "  4. Ajuster les permissions :\n" . C_RESET;
echo "     " . C_YELLOW . "chown -R www-data:www-data {$targetDir}\n" . C_RESET;
echo "     " . C_YELLOW . "chmod 755 {$targetDir}\n" . C_RESET;
echo "     " . C_YELLOW . "chmod 775 {$targetDir}/{logs,storage,uploads,cache}\n" . C_RESET . "\n";

echo C_DIM . "  5. Configurer les APIs depuis l'admin :\n" . C_RESET;
echo "     " . C_YELLOW . "Google My Business, Gmail API, Google Analytics, OpenAI\n" . C_RESET . "\n";

if ($isDryRun) {
    echo C_YELLOW . "  [MODE SIMULATION] Aucune action réelle n'a été exécutée.\n" . C_RESET;
}

echo "\n";
