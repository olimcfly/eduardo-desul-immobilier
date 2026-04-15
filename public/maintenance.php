<?php
http_response_code(503);
header('Retry-After: 3600');

$pageTitle = 'Site en construction — Immobilier à Bordeaux';
$metaDesc = 'Notre site est actuellement en construction. Contactez-nous pour votre projet immobilier à Bordeaux.';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">

    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        p {
            opacity: 0.8;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            margin: 5px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-primary {
            background: #22c55e;
            color: #000;
        }

        .btn-outline {
            border: 1px solid #fff;
            color: #fff;
        }

        .logo {
            margin-bottom: 2rem;
            font-weight: bold;
            font-size: 1.2rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="logo">
        Eduardo De Sul — Immobilier à Bordeaux
    </div>

    <h1>Site en construction</h1>

    <p>
        Notre site est actuellement en cours de création pour vous offrir
        une expérience optimale autour de votre projet immobilier à Bordeaux.
    </p>

    <p>
        En attendant, vous pouvez nous contacter directement ou demander
        une estimation gratuite de votre bien.
    </p>

    <div>
      
    </div>

</div>

</body>
</html>