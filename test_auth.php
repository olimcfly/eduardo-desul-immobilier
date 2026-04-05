<?php
require_once __DIR__ . '/core/bootstrap.php';

$email = $argv[1] ?? 'admin@votresite.com';
$password = $argv[2] ?? 'password';

if (Auth::attempt($email, $password, ['admin', 'superadmin'])) {
    echo "Connexion réussie !\n";
    print_r(Auth::user());
    Auth::logout();
    exit(0);
}

echo "Échec de la connexion. Vérifiez les identifiants, le hash du mot de passe ou la base de données.\n";
exit(1);
