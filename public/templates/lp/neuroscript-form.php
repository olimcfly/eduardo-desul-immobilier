<?php

declare(strict_types=1);

$page = is_array($page ?? null) ? $page : [];
$pageData = json_decode((string) ($page['data_json'] ?? '{}'), true);
$pageData = is_array($pageData) ? $pageData : [];

$formAction = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
$thankYouUrl = htmlspecialchars((string) ($pageData['thankyou_url'] ?? '/lp/neuroscript-thankyou'), ENT_QUOTES, 'UTF-8');

$errors = [];
$success = false;

$name = '';
$email = '';
$phone = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '') {
        $errors[] = 'Nom requis';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide';
    }

    if ($phone === '') {
        $errors[] = 'Téléphone requis';
    }

    if ($errors === []) {

        try {

            $pdo = $pdo ?? null;

            if ($pdo instanceof PDO) {

                $stmt = $pdo->prepare("
                    INSERT INTO prospects (name, email, phone, message, source, created_at)
                    VALUES (:name, :email, :phone, :message, :source, NOW())
                ");

                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'message' => $message,
                    'source' => 'lp_neuroscript'
                ]);
            }

            header('Location: ' . $thankYouUrl);
            exit;

        } catch (Throwable $e) {
            $errors[] = 'Erreur serveur';
        }
    }
}
?>

<section class="lp-page lp-page--form">
    <div class="container">

        <h1>Accéder à la méthode</h1>
        <p>Remplissez ce formulaire pour passer à l'étape suivante</p>

        <?php if (!empty($errors)): ?>
            <div class="form-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= $formAction ?>" class="lp-form">

            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Continuer
            </button>

        </form>

    </div>
</section>