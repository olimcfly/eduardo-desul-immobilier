<?php
/**
 * renderers/secteur-listing.php
 * Délègue le rendu à la page Builder dont le slug est 'secteurs'.
 * Aucun design ici — tout est géré dans le Builder Editor.
 */

if (!defined('FRONT_ROUTER')) {
    http_response_code(403);
    exit('Accès direct interdit.');
}

global $db;
if (!$db) $db = getDB();

// ── Charger la page Builder slug='secteurs' ──
$page = null;
try {
    $stmt = $db->prepare("
        SELECT * FROM pages
        WHERE slug = 'secteurs'
          AND (status = 'published' OR statut = 'publié')
        LIMIT 1
    ");
    $stmt->execute();
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $page = null;
}

// ── Page introuvable → 404 ──
if (!$page) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// ── Injecter dans le contexte attendu par cms.php ──
// cms.php lit $page et génère la page complète avec header/footer Builder
require __DIR__ . '/cms.php';