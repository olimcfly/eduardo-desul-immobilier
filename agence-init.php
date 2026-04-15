<?php

declare(strict_types=1);

session_start();

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function generateRandomPassword(int $length = 12): string
{
    $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lower = 'abcdefghijkmnopqrstuvwxyz';
    $digits = '23456789';
    $symbols = '!@#$%^&*()-_=+';

    $password = [
        $upper[random_int(0, strlen($upper) - 1)],
        $lower[random_int(0, strlen($lower) - 1)],
        $digits[random_int(0, strlen($digits) - 1)],
        $symbols[random_int(0, strlen($symbols) - 1)],
    ];

    $all = $upper . $lower . $digits . $symbols;
    for ($i = 4; $i < $length; $i++) {
        $password[] = $all[random_int(0, strlen($all) - 1)];
    }

    shuffle($password);
    return implode('', $password);
}

function updateConfigFile(string $email, string $plainPassword): bool
{
    $configPath = __DIR__ . '/includes/config.php';
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    if ($hash === false) {
        return false;
    }

    $content = "<?php\n\ndeclare(strict_types=1);\n\ndefine('ADMIN_EMAIL', '" . addslashes($email) . "');\ndefine('ADMIN_PASSWORD_HASH', '" . addslashes($hash) . "');\ndefine('ADMIN_PASSWORD_RESET_REQUIRED', true);\n";

    return file_put_contents($configPath, $content) !== false;
}

$step = (int) ($_POST['step'] ?? 7);
$error = null;
$success = null;

if ($step === 8) {
    $email = trim((string) ($_POST['admin_email'] ?? ''));
    $password = (string) ($_POST['admin_password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez saisir un email valide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe temporaire doit contenir au moins 8 caractères.';
    } elseif (!updateConfigFile($email, $password)) {
        $error = 'Impossible d\'écrire la configuration.';
    } else {
        $success = 'Installation terminée. Vous pouvez vous connecter dans /admin/login.php.';
    }
}

$tempPassword = $_POST['admin_password'] ?? generateRandomPassword();
$defaultEmail = $_SESSION['app_email'] ?? (defined('APP_EMAIL') ? APP_EMAIL : '');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation agence - Étape 7</title>
</head>
<body>
<h2>Étape 7 — Création du compte administrateur</h2>

<?php if ($error): ?><p style="color:#b42318"><?php echo h($error); ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:#0f7a2a"><?php echo h($success); ?></p><?php endif; ?>

<form method="post">
    <input type="hidden" name="step" value="8">
    <div>
        <label for="admin_email">Email*</label><br>
        <input type="email" id="admin_email" name="admin_email" value="<?php echo h((string) $defaultEmail); ?>" required>
    </div>

    <div>
        <label for="admin_password">Mot de passe temporaire*</label><br>
        <input type="text" id="admin_password" name="admin_password" value="<?php echo h((string) $tempPassword); ?>" readonly required>
        <button type="button" id="regen-password">↻ Régénérer</button>
    </div>

    <button type="submit">Finaliser l'installation</button>
</form>

<script>
function generateRandomPassword(length = 12) {
  const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  const lower = 'abcdefghijkmnopqrstuvwxyz';
  const digits = '23456789';
  const symbols = '!@#$%^&*()-_=+';
  const all = upper + lower + digits + symbols;

  const result = [
    upper[Math.floor(Math.random() * upper.length)],
    lower[Math.floor(Math.random() * lower.length)],
    digits[Math.floor(Math.random() * digits.length)],
    symbols[Math.floor(Math.random() * symbols.length)]
  ];

  while (result.length < length) {
    result.push(all[Math.floor(Math.random() * all.length)]);
  }

  return result.sort(() => Math.random() - 0.5).join('');
}

document.getElementById('regen-password').addEventListener('click', function () {
  document.getElementById('admin_password').value = generateRandomPassword();
});
</script>
</body>
</html>
