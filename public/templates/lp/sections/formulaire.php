<?php

declare(strict_types=1);

$action = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');

$name = htmlspecialchars((string) ($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars((string) ($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars((string) ($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
?>

<section class="lp-section lp-section--formulaire">
    <div class="container">

        <form method="post" action="<?= $action ?>" class="lp-form">

            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="name" value="<?= $name ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= $email ?>" required>
            </div>

            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="phone" value="<?= $phone ?>" required>
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message"><?= $message ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Continuer
            </button>

        </form>

    </div>
</section>