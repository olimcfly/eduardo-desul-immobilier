<?php
/**
 * 📧 CONFIGURATION SMTP/IMAP
 * /config/smtp.php
 *
 * Configuration des emails et des serveurs mail
 * À adapter selon votre hébergeur (OVH, Ionos, etc.)
 */

return [
    // ═══════════════════════════════════════════════════════════
    // 📤 SMTP (Envoi d'emails)
    // ═══════════════════════════════════════════════════════════

    'smtp_host'      => 'smtp.your-provider.com',    // ← À adapter (OVH, Ionos, etc.)
    'smtp_port'      => 587,                         // 587 (TLS) ou 465 (SSL)
    'smtp_secure'    => 'tls',                       // 'tls' ou 'ssl'
    'smtp_user'      => 'noreply@eduardodesulimmobilier.fr',  // Email pour envoi
    'smtp_pass'      => 'your_email_password',       // Mot de passe email
    'smtp_from'      => 'noreply@eduardodesulimmobilier.fr',  // Email "from"
    'smtp_from_name' => 'Eduardo Desul - Immobilier', // Nom qui s'affiche

    // ═══════════════════════════════════════════════════════════
    // 📥 IMAP (Réception d'emails) - OPTIONNEL
    // ═══════════════════════════════════════════════════════════

    'imap_host'   => 'imap.your-provider.com',    // Serveur IMAP
    'imap_port'   => 993,                         // Généralement 993
    'imap_secure' => 'ssl',                       // 'ssl' ou 'tls'
    'imap_user'   => 'contact@eduardodesulimmobilier.fr',
    'imap_pass'   => 'your_email_password',

    // ═══════════════════════════════════════════════════════════
    // 📬 COMPTES EMAIL DU DOMAINE
    // ═══════════════════════════════════════════════════════════

    'email_accounts' => [
        'admin@eduardodesulimmobilier.fr',
        'contact@eduardodesulimmobilier.fr',
        'info@eduardodesulimmobilier.fr',
        'estimation@eduardodesulimmobilier.fr',
        'support@eduardodesulimmobilier.fr',
        'noreply@eduardodesulimmobilier.fr',
        'bounce@eduardodesulimmobilier.fr',
    ],

    // ═══════════════════════════════════════════════════════════
    // 📮 ALIAS EMAIL (Redirects) - OPTIONNEL
    // ═══════════════════════════════════════════════════════════

    'email_aliases' => [
        // 'contact@old-domain.fr' => 'contact@eduardodesulimmobilier.fr',
    ],

    // ═══════════════════════════════════════════════════════════
    // 🎯 RÔLES EMAIL
    // ═══════════════════════════════════════════════════════════

    'email_roles' => [
        'primary'  => 'contact@eduardodesulimmobilier.fr',      // Email principal
        'system'   => 'noreply@eduardodesulimmobilier.fr',       // Système (ne pas répondre)
        'support'  => 'support@eduardodesulimmobilier.fr',       // Support client
        'estimation' => 'estimation@eduardodesulimmobilier.fr',  // Demandes estimation
        'bounce'   => 'bounce@eduardodesulimmobilier.fr',        // Erreurs de livraison
    ],

    // ═══════════════════════════════════════════════════════════
    // 🔧 OPTIONS ADDITIONNELLES
    // ═══════════════════════════════════════════════════════════

    'timeout'       => 30,          // Timeout connexion (secondes)
    'verify_ssl'    => true,        // Vérifier certificat SSL
    'dkim_enabled'  => false,       // DKIM (optionnel, demander à hébergeur)
    'spf_enabled'   => false,       // SPF (vérifier DNS)

    // ═══════════════════════════════════════════════════════════
    // 📝 TEMPLATES EMAIL (Optionnel)
    // ═══════════════════════════════════════════════════════════

    'templates_enabled' => true,
    'templates_path'    => dirname(__DIR__) . '/includes/email-templates',

];

/**
 * NOTES IMPORTANTES:
 *
 * 1. TROUVER LES PARAMÈTRES SMTP:
 *    - OVH: smtp.ovh.net (port 587, TLS)
 *    - Ionos: smtp.ionos.fr (port 587, TLS)
 *    - Google Workspace: smtp.gmail.com (port 587, TLS)
 *    - Godaddy: smtpout.secureserver.net (port 465, SSL)
 *
 * 2. EMAIL NOREPLY:
 *    - Créer un email noreply@votredomaine.fr
 *    - Ne pas utiliser pour réception
 *    - Utiliser pour emails auto (système, confirmations, etc.)
 *
 * 3. SÉCURITÉ:
 *    - Jamais commiter ce fichier en git
 *    - Utiliser mot de passe FORT
 *    - Changer régulièrement
 *
 * 4. SPF & DKIM:
 *    - Configurer SPF dans DNS (ajouter +include:votrehebergeur.fr)
 *    - Configurer DKIM si possible (meilleur délivrabilité)
 *    - Vérifier dans diagnostic DKIM/SPF
 *
 * 5. TEST SMTP:
 *    - Utiliser DIAGNOSTIC.php pour tester
 *    - Envoyer email test
 *    - Vérifier dossier spam
 */

?>
