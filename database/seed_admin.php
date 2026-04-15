#!/usr/bin/env php
<?php
// ============================================================
// SCRIPT : Création du compte administrateur
// Usage  : php database/seed_admin.php
// ============================================================

define('ROOT_PATH', dirname(__DIR__));

// Charger .env
$envFile = ROOT_PATH . '/.env';
if (!file_exists($envFile)) {
    die("Erreur : fichier .env introuvable. Copiez .env.example en .env et remplissez-le.\n");
}
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim($v, " \t\"'");
    putenv(trim($k) . '=' . trim($v, " \t\"'"));
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Auth.php';

// ── Saisie interactive ───────────────────────────────────────
echo "\n=== Création du compte administrateur ===\n\n";

$name = readline("Nom complet [Pascal Hamm] : ");
if (empty(trim($name))) $name = 'Pascal Hamm';

$email = '';
while (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email = readline("Email admin : ");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "  ✗ Email invalide, réessayez.\n";
    }
}

$password = '';
while (strlen($password) < 8) {
    $password = readline("Mot de passe (8 car. min.) : ");
    if (strlen($password) < 8) {
        echo "  ✗ Minimum 8 caractères.\n";
    }
}

$confirm = readline("Confirmer le mot de passe : ");
if ($password !== $confirm) {
    die("  ✗ Les mots de passe ne correspondent pas.\n");
}

// ── Insertion en base ────────────────────────────────────────
try {
    $db = Database::getInstance();

    // Vérifier si l'email existe déjà
    $check = $db->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        echo "\n  ⚠ Un utilisateur avec cet email existe déjà.\n";
        $overwrite = readline("  Mettre à jour le mot de passe ? (o/N) : ");
        if (strtolower(trim($overwrite)) !== 'o') {
            die("  Annulé.\n");
        }
        $hash = Auth::hashPassword($password);
        $db->prepare('UPDATE users SET password = ?, name = ?, role = ? WHERE email = ?')
           ->execute([$hash, $name, 'admin', $email]);
        echo "\n  ✓ Mot de passe mis à jour pour {$email}\n\n";
    } else {
        $hash = Auth::hashPassword($password);
        $db->prepare('INSERT INTO users (email, password, role, name, created_at) VALUES (?, ?, ?, ?, NOW())')
           ->execute([$email, $hash, 'admin', $name]);
        echo "\n  ✓ Compte admin créé !\n";
        echo "  Email    : {$email}\n";
        echo "  Nom      : {$name}\n";
        echo "  Rôle     : admin\n\n";
        echo "  Connectez-vous sur : " . ($_ENV['APP_URL'] ?? 'https://pascal-hamm-immobilier.fr') . "/admin/login\n\n";
    }
} catch (PDOException $e) {
    die("  ✗ Erreur DB : " . $e->getMessage() . "\n");
}
