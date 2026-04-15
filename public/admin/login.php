<?php
require_once '../../core/bootstrap.php';

// Déjà connecté → dashboard
if (Auth::check()) {
    header('Location: /admin/');
    exit;
}

$flash = Session::getFlash();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Email et mot de passe requis.';
    } else {
        try {
            $ok = Auth::attempt($email, $password, ['admin', 'superadmin']);

            if ($ok) {
                $_SESSION['show_welcome_popup'] = true;
                header('Location: /admin/');
                exit;
            } else {
                // Délai anti-brute-force
                sleep(1);
                $error = 'Email ou mot de passe incorrect.';
            }
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

require '../../admin/views/login.php';
