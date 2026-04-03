<?php
// ============================================================
// NOAH API — Endpoint AJAX pour les outils IA
// ============================================================

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../core/services/AiService.php';

header('Content-Type: application/json');

// Auth obligatoire
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals(csrfToken(), $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
    exit;
}

$tool = preg_replace('/[^a-z_]/', '', (string) ($_POST['tool'] ?? ''));
$data = array_map(static fn ($value) => trim((string) $value), $_POST);

$systemPrompt = "Tu es Noah, assistant stratégique intégré dans une application immobilière. Tu n'es pas un chatbot conversationnel. Tu vas directement à l'essentiel. Tu utilises un langage simple, clair et professionnel. Tu ne dis jamais bonjour et tu ne poses pas de questions. Tes réponses sont directement affichables dans une interface.";

try {
    $userMessage = match ($tool) {
        'positionnement' => buildPrompt(
            "Ta mission est de proposer un positionnement clair et directement exploitable.\n\n" .
            "Données :\n" .
            "- métier : {$data['metier']}\n" .
            "- zone : {$data['zone']}\n" .
            "- type de clients : {$data['persona']}\n" .
            "- objectif : {$data['objectif']}\n\n" .
            "Consignes :\n- proposer 3 formulations simples\n- chaque formulation courte et claire\n- langage compréhensible\n\n" .
            "Format :\n\nProposition 1 :\n...\n\nProposition 2 :\n...\n\nProposition 3 :\n...\n\nRecommandation :\n..."
        ),

        'profils' => buildPrompt(
            "Ta mission est d'identifier les profils clients les plus pertinents.\n\n" .
            "Données :\n" .
            "- activité : {$data['activite']}\n" .
            "- zone : {$data['zone']}\n" .
            "- objectif : {$data['objectif']}\n\n" .
            "Consignes :\n- proposer 3 profils clients\n- rester simple et concret\n\n" .
            "Format :\n\nProfil 1 :\nType :\nProblème :\nObjectif :\n\nProfil 2 :\nType :\nProblème :\nObjectif :\n\nProfil 3 :\nType :\nProblème :\nObjectif :\n\nChoix recommandé :\n..."
        ),

        'offre' => buildPrompt(
            "Ta mission est de proposer des formulations d'offre claires.\n\n" .
            "Données :\n" .
            "- métier : {$data['metier']}\n" .
            "- persona : {$data['persona']}\n" .
            "- objectif client : {$data['objectif_client']}\n" .
            "- points forts : {$data['points_forts']}\n\n" .
            "Consignes :\n- phrases simples\n- 3 versions : simple, différenciante, orientée résultat\n\n" .
            "Format :\n\nVersion simple :\n...\n\nVersion différenciante :\n...\n\nVersion orientée résultat :\n...\n\nRecommandation :\n..."
        ),

        'zone' => buildPrompt(
            "Ta mission est de proposer une stratégie de zone de prospection.\n\n" .
            "Données :\n" .
            "- ville : {$data['ville']}\n" .
            "- type de biens : {$data['type_biens']}\n" .
            "- objectif : {$data['objectif']}\n\n" .
            "Consignes :\n- concret\n- proposer 3 niveaux\n\n" .
            "Format :\n\nZone précise :\n...\n\nZone équilibrée :\n...\n\nZone large :\n...\n\nRecommandation :\n..."
        ),

        'synthese' => buildPrompt(
            "Ta mission est de résumer la situation utilisateur.\n\n" .
            "Données :\n" .
            "- activité : {$data['activite']}\n" .
            "- positionnement : {$data['positionnement']}\n" .
            "- persona : {$data['persona']}\n" .
            "- offre : {$data['offre']}\n" .
            "- zone : {$data['zone']}\n\n" .
            "Consignes :\n- maximum 100 mots\n- ton clair et structuré\n\n" .
            "Format :\n\nSynthèse :\n...\n\nProchaine étape :\n..."
        ),

        'actions' => buildPrompt(
            "Ta mission est de proposer des actions concrètes à réaliser aujourd'hui.\n\n" .
            "Données :\n" .
            "- niveau d'expérience : {$data['experience']}\n" .
            "- objectif mensuel : {$data['objectif']}\n" .
            "- nombre de biens : {$data['biens']}\n" .
            "- activité actuelle : {$data['activite']}\n\n" .
            "Consignes :\n- 3 à 5 actions maximum\n- actions simples, concrètes, mesurables\n- inclure : prospection, contenu, relance\n\n" .
            "Format :\n\nActions du jour :\n\n1.\n2.\n3.\n4.\n\nObjectif :\n..."
        ),

        default => throw new InvalidArgumentException('Outil inconnu'),
    };

    $result = AiService::ask($systemPrompt, $userMessage);

    echo json_encode(['success' => true, 'result' => $result]);
} catch (Exception $e) {
    error_log('Noah API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Une erreur est survenue.']);
}

function buildPrompt(string $content): string
{
    return $content;
}
