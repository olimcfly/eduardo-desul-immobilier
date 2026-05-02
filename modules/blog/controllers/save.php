<?php
if (!function_exists('db')) { require_once __DIR__ . '/../../../core/config/database.php'; }
require_once __DIR__ . '/../services/PersonaResolver.php';
$pdo = db();

$id               = $_POST['id'] ?? null;
$website_id       = 1; // à rendre dynamique plus tard
$silo_id          = $_POST['silo_id'] ?: null;
$type             = $_POST['type'] ?? 'satellite';
$titre            = trim($_POST['titre'] ?? '');
$slug             = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $titre));
$slug             = trim($slug, '-');
$seo_title        = trim($_POST['seo_title'] ?? '');
$meta_desc        = trim($_POST['meta_desc'] ?? '');
$h1               = trim($_POST['h1'] ?? '');
$contenu          = $_POST['contenu'] ?? '';
$statut           = $_POST['statut'] ?? 'brouillon';
$statut           = in_array($statut, ['brouillon', 'publié'], true) ? $statut : 'brouillon';
$index_status     = $_POST['index_status'] ?? 'index';

$personaTarget    = $_POST['persona_target'] ?? null;
$personaReason    = $_POST['persona_reason'] ?? null;
$resolvedPersona  = PersonaResolver::resolve(
    is_string($personaTarget) ? $personaTarget : null,
    is_string($personaReason) ? $personaReason : null
);

$persona_id        = $resolvedPersona['persona_id'];
$niveau_conscience = $resolvedPersona['niveau_conscience'];

$date_publication_input = trim($_POST['date_publication'] ?? '');
$date_publication_ts = $date_publication_input !== '' ? strtotime($date_publication_input) : false;
$date_publication = $date_publication_ts !== false ? date('Y-m-d H:i:s', $date_publication_ts) : null;
$mots             = str_word_count(strip_tags($contenu));

if (empty($titre)) {
    header('Location: ../accueil.php?action=new&error=titre_vide');
    exit;
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE blog_articles SET 
        silo_id=?, type=?, titre=?, slug=?, seo_title=?, meta_desc=?, h1=?,
        contenu=?, statut=?, index_status=?, persona_id=?, niveau_conscience=?,
        date_publication=?, mots=?, updated_at=NOW()
        WHERE id=? AND website_id=?");
    $stmt->execute([
        $silo_id, $type, $titre, $slug, $seo_title, $meta_desc, $h1,
        $contenu, $statut, $index_status, $persona_id, $niveau_conscience,
        $date_publication, $mots, $id, $website_id
    ]);
} else {
    $stmt = $pdo->prepare("INSERT INTO blog_articles 
        (website_id, silo_id, type, titre, slug, seo_title, meta_desc, h1,
         contenu, statut, index_status, persona_id, niveau_conscience, date_publication, mots)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $website_id, $silo_id, $type, $titre, $slug, $seo_title, $meta_desc, $h1,
        $contenu, $statut, $index_status, $persona_id, $niveau_conscience, $date_publication, $mots
    ]);
    $id = $pdo->lastInsertId();
}

header('Location: ../accueil.php?action=view&id=' . $id . '&success=1');
exit;
