<?php
/**
 * Télécharge des images libres de droits depuis Pexels CDN pour les articles du blog.
 * Pexels : licence gratuite, usage commercial autorisé.
 * Usage CLI : php script/download_blog_images.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/core/bootstrap.php';

$dest = $root . '/public/assets/images/blog';
if (!is_dir($dest)) {
    mkdir($dest, 0755, true);
}

// Correspondance slug → image Pexels
// Format : [pexels_photo_id, largeur, hauteur, description]
$images = [
    'devenir-proprietaire-aix-en-provence-2026' => [1396122, 800, 450, 'Maison moderne'],
    'acheter-aix-en-provence-sans-apport'        => [1643389, 800, 450, 'Clés de maison'],
    'budget-acheter-aix-en-provence-2026'        => [534151,  800, 450, 'Vue urbaine Aix'],
    'combien-emprunter-3000-euros-salaire-aix'   => [3243090, 800, 450, 'Finances personnelles'],
    'acheter-appartement-jas-de-bouffan-aix'     => [1029599, 800, 450, 'Immeuble résidentiel'],
    'acheter-encagnane-aix-en-provence'           => [2102587, 800, 450, 'Appartement moderne'],
    'vivre-luynes-aix-immobilier-prix-2026'      => [280222,  800, 450, 'Quartier résidentiel'],
    'comment-acheter-rapidement-aix-en-provence' => [323780,  800, 450, 'Agent immobilier'],
    'erreurs-primo-accedant-aix-en-provence'     => [259950,  800, 450, 'Intérieur maison'],
    'pourquoi-je-narrive-pas-acheter-aix-en-provence' => [106399, 800, 450, 'Maison avec jardin'],
];

$pdo  = db();
$stmt = $pdo->prepare("UPDATE blog_articles SET image = :image WHERE slug = :slug AND website_id = 1");

$ok  = 0;
$err = 0;

foreach ($images as $slug => [$photoId, $w, $h, $desc]) {
    $filename = $slug . '.jpg';
    $filepath = $dest . '/' . $filename;
    $webPath  = '/assets/images/blog/' . $filename;

    // URL Pexels CDN — format officiel compress/auto
    $url = "https://images.pexels.com/photos/{$photoId}/pexels-photo-{$photoId}.jpeg"
         . "?auto=compress&cs=tinysrgb&w={$w}&h={$h}&fit=crop";

    echo "Téléchargement : {$slug} (photo #{$photoId})... ";

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 20,
            'header'  => "User-Agent: Mozilla/5.0 (compatible; site-builder/1.0)\r\n",
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);

    $data = @file_get_contents($url, false, $ctx);

    if ($data === false || strlen($data) < 5000) {
        echo "✗ Échec téléchargement\n";
        $err++;
        continue;
    }

    file_put_contents($filepath, $data);

    // Mettre à jour la DB
    $stmt->execute([':image' => $webPath, ':slug' => $slug]);
    echo "✔ ({$stmt->rowCount()} ligne)\n";
    $ok++;
}

echo "\n✅ {$ok} images téléchargées, {$err} erreurs.\n";
