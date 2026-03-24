<?php
/**
 * Configuration SMTP / IMAP - EXEMPLE
 * config/smtp.php
 *
 * Copier ce fichier en config/smtp.php et remplir les valeurs
 * À MODIFIER pour chaque duplication
 */

return [
    // SMTP (envoi)
    'smtp_host'      => 'mail.mon-domaine.fr',
    'smtp_port'      => 465,
    'smtp_secure'    => 'ssl',
    'smtp_user'      => 'admin@mon-domaine.fr',
    'smtp_pass'      => 'MOT_DE_PASSE_SMTP',
    'smtp_from'      => 'contact@mon-domaine.fr',
    'smtp_from_name' => 'Mon Agence Immobilier',

    // IMAP (reception)
    'imap_host'   => 'mail.mon-domaine.fr',
    'imap_port'   => 993,
    'imap_secure' => 'ssl',
    'imap_user'   => 'admin@mon-domaine.fr',
    'imap_pass'   => 'MOT_DE_PASSE_IMAP',

    // Comptes email du domaine
    'email_accounts' => [
        'admin@mon-domaine.fr',
        'contact@mon-domaine.fr',
        'info@mon-domaine.fr',
        'estimation@mon-domaine.fr',
        'support@mon-domaine.fr',
        'ne-pas-repondre@mon-domaine.fr',
        'bounce@mon-domaine.fr',
    ],

    // Alias (optionnel)
    'email_aliases' => [
        // 'contact@mon-domaine.com',
    ],

    // Roles
    'email_roles' => [
        'primary' => 'contact@mon-domaine.fr',
        'system'  => 'ne-pas-repondre@mon-domaine.fr',
        'support' => 'support@mon-domaine.fr',
        'bounce'  => 'bounce@mon-domaine.fr',
    ],
];
