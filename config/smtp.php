<?php
/**
 * Configuration SMTP / IMAP
 * config/smtp.php
 *
 * Les credentials sont lues depuis .env
 * Charge par loadSmtpConfig() dans :
 *   - admin/modules/system/modules.php
 *   - admin/api/marketing/emails.php
 */

// S'assurer que le .env est charge (sans fatal si helper absent)
if (!function_exists('env')) {
    $envHelper = dirname(dirname(__FILE__)) . '/core/env.php';
    if (is_file($envHelper)) {
        require_once $envHelper;
        if (function_exists('loadEnv')) {
            loadEnv(dirname(dirname(__FILE__)) . '/.env');
        }
    }
}

$domain = env('SITE_DOMAIN', 'localhost');

return [
    // SMTP (envoi)
    'smtp_host'      => env('SMTP_HOST', $domain),
    'smtp_port'      => (int) env('SMTP_PORT', 465),
    'smtp_secure'    => env('SMTP_SECURE', 'ssl'),
    'smtp_user'      => env('SMTP_USER', 'admin@' . $domain),
    'smtp_pass'      => env('SMTP_PASS', ''),
    'smtp_from'      => env('SMTP_FROM', 'contact@' . $domain),
    'smtp_from_name' => env('SMTP_FROM_NAME', env('SITE_TITLE', 'Mon Site')),

    // IMAP (reception)
    'imap_host'   => env('IMAP_HOST', $domain),
    'imap_port'   => (int) env('IMAP_PORT', 993),
    'imap_secure' => env('IMAP_SECURE', 'ssl'),
    'imap_user'   => env('IMAP_USER', 'admin@' . $domain),
    'imap_pass'   => env('IMAP_PASS', ''),

    // Comptes email du domaine
    'email_accounts' => [
        'admin@' . $domain,
        'contact@' . $domain,
        'info@' . $domain,
        'estimation@' . $domain,
        'support@' . $domain,
        'ne-pas-repondre@' . $domain,
        'bounce@' . $domain,
    ],

    // Alias (optionnel)
    'email_aliases' => [],

    // Roles
    'email_roles' => [
        'primary' => 'contact@' . $domain,
        'system'  => 'ne-pas-repondre@' . $domain,
        'support' => 'support@' . $domain,
        'bounce'  => 'bounce@' . $domain,
    ],
];
