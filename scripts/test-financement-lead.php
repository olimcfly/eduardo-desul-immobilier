#!/usr/bin/env php
<?php
/**
 * Insère une demande de financement de test (crm_leads, source financement).
 *
 * Usage :
 *   php scripts/test-financement-lead.php
 *   php scripts/test-financement-lead.php votre@email.com
 *   php scripts/test-financement-lead.php votre@email.com --send-mails
 *
 * Ensuite : Admin → Contacts → filtre « Financement » ou recherche par e-mail.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
define('ROOT_PATH', $root);

require_once ROOT_PATH . '/core/bootstrap.php';

$testEmail = trim((string) ($argv[1] ?? ''));
if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    $testEmail = 'test-financement+' . date('YmdHis') . '@example.com';
    fwrite(STDERR, "Aucun e-mail valide en argument — utilisation de : {$testEmail}\n");
}

$sendMails = in_array('--send-mails', $argv ?? [], true);

$formData = [
    'prenom' => 'Test',
    'nom' => 'Financement CLI',
    'email' => $testEmail,
    'telephone' => '0600000000',
    'type_projet' => 'Premier achat',
    'secteur_recherche' => 'Mérignac',
    'budget_estime' => '350 000 €',
    'apport_personnel' => '35 000 €',
    'situation_professionnelle' => 'CDI',
    'delai_projet' => '3 à 6 mois',
    'message' => 'Demande générée par scripts/test-financement-lead.php le ' . date('c'),
];

$leadId = LeadService::capture([
    'source_type' => LeadService::SOURCE_FINANCEMENT,
    'pipeline' => LeadService::SOURCE_FINANCEMENT,
    'stage' => 'nouveau',
    'first_name' => $formData['prenom'],
    'last_name' => $formData['nom'],
    'email' => $formData['email'],
    'phone' => $formData['telephone'],
    'intent' => $formData['type_projet'],
    'notes' => $formData['message'],
    'consent' => true,
    'metadata' => [
        'secteur_recherche' => $formData['secteur_recherche'],
        'budget_estime' => $formData['budget_estime'],
        'apport_personnel' => $formData['apport_personnel'],
        'situation_professionnelle' => $formData['situation_professionnelle'],
        'delai_projet' => $formData['delai_projet'],
        'type_projet' => $formData['type_projet'],
        'origin_path' => '/financement (test CLI)',
    ],
]);

if ($leadId <= 0) {
    fwrite(STDERR, "Échec : aucun lead créé.\n");
    exit(1);
}

if ($sendMails) {
    $fn = ROOT_PATH . '/core/services/FinancementNotificationService.php';
    if (is_file($fn)) {
        require_once $fn;
        try {
            FinancementNotificationService::afterCapture($leadId, $formData);
            echo "E-mails (conseiller + accusé IA si clé) : tentative d’envoi effectuée.\n";
        } catch (Throwable $e) {
            fwrite(STDERR, 'afterCapture : ' . $e->getMessage() . "\n");
        }
    }
}

$base = defined('APP_URL') && APP_URL !== '' ? rtrim((string) APP_URL, '/') : '(définir APP_URL dans .env)';
$adminContacts = $base !== '(définir APP_URL dans .env)'
    ? $base . '/admin?module=contacts&source=financement&q=' . rawurlencode($testEmail)
    : '/admin?module=contacts&source=financement';

echo PHP_EOL;
echo "Lead créé : ID {$leadId}" . PHP_EOL;
echo "E-mail test : {$testEmail}" . PHP_EOL;
echo "Voir dans l’admin (liste filtrée financement + recherche) :" . PHP_EOL;
echo "  {$adminContacts}" . PHP_EOL;
echo "Ou CRM hub (compteurs leads) :" . PHP_EOL;
echo "  {$base}/admin?module=crm-hub" . PHP_EOL;
if (!$sendMails) {
    echo PHP_EOL . "Pour aussi déclencher les e-mails (SMTP + IA), relancer avec : --send-mails" . PHP_EOL;
}
echo PHP_EOL;
exit(0);
