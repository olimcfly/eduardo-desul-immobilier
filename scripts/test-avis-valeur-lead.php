#!/usr/bin/env php
<?php
/**
 * Insère un lead de test « avis de valeur » (crm_leads, source avis_valeur).
 *
 * Usage :
 *   php scripts/test-avis-valeur-lead.php
 *   php scripts/test-avis-valeur-lead.php votre@email.com
 *   php scripts/test-avis-valeur-lead.php votre@email.com --send-mails
 *
 * Admin : Contacts → filtre « Avis de valeur » ou recherche par e-mail.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
define('ROOT_PATH', $root);

require_once ROOT_PATH . '/core/bootstrap.php';

$testEmail = trim((string) ($argv[1] ?? ''));
if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    $testEmail = 'test-avis-valeur+' . date('YmdHis') . '@example.com';
    fwrite(STDERR, "Aucun e-mail valide en argument — utilisation de : {$testEmail}\n");
}

$sendMails = in_array('--send-mails', $argv ?? [], true);

$formData = [
    'prenom' => 'Olivier',
    'nom' => 'Test CLI',
    'email' => $testEmail,
    'telephone' => '0612345678',
    'adresse_bien' => '15 cours de l\'Intendance, Bordeaux',
    'type_bien' => 'Appartement',
    'surface_m2' => '72',
    'message' => 'Demande générée par scripts/test-avis-valeur-lead.php le ' . date('c'),
];

$leadId = LeadService::capture([
    'source_type' => LeadService::SOURCE_AVIS_VALEUR,
    'pipeline' => LeadService::SOURCE_AVIS_VALEUR,
    'stage' => 'nouveau',
    'first_name' => $formData['prenom'],
    'last_name' => $formData['nom'],
    'email' => $formData['email'],
    'phone' => $formData['telephone'],
    'intent' => 'Demande d\'avis de valeur',
    'property_type' => $formData['type_bien'],
    'property_address' => $formData['adresse_bien'],
    'notes' => $formData['message'],
    'consent' => true,
    'metadata' => [
        'demande_type' => 'avis_valeur',
        'surface_m2' => (int) $formData['surface_m2'],
        'origin_path' => '/avis-de-valeur (test CLI)',
        'type_bien_key' => 'appartement',
    ],
]);

if ($leadId <= 0) {
    fwrite(STDERR, "Échec : aucun lead créé.\n");
    exit(1);
}

if ($sendMails) {
    $fn = ROOT_PATH . '/core/services/AvisValeurNotificationService.php';
    if (is_file($fn)) {
        require_once $fn;
        try {
            AvisValeurNotificationService::afterCapture($leadId, $formData);
            echo "E-mails (conseiller + accusé IA si clé) : tentative d’envoi effectuée.\n";
        } catch (Throwable $e) {
            fwrite(STDERR, 'afterCapture : ' . $e->getMessage() . "\n");
        }
    }
}

$base = defined('APP_URL') && APP_URL !== '' ? rtrim((string) APP_URL, '/') : '(définir APP_URL dans .env)';
$adminContacts = $base !== '(définir APP_URL dans .env)'
    ? $base . '/admin?module=contacts&source=avis_valeur&q=' . rawurlencode($testEmail)
    : '/admin?module=contacts&source=avis_valeur';

echo PHP_EOL;
echo "Lead créé : ID {$leadId}" . PHP_EOL;
echo "E-mail test : {$testEmail}" . PHP_EOL;
echo "Voir dans l’admin :" . PHP_EOL;
echo "  {$adminContacts}" . PHP_EOL;
if (!$sendMails) {
    echo PHP_EOL . "Pour aussi déclencher les e-mails (SMTP + IA), relancer avec : --send-mails" . PHP_EOL;
}
echo PHP_EOL;
exit(0);
