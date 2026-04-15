<?php
declare(strict_types=1);

$siteName = trim((string) ($siteSettings['site_name'] ?? 'Site immobilier'));
$legalNoticeUrl = trim((string) ($siteSettings['legal_notice_url'] ?? '/mentions-legales'));
$privacyUrl = trim((string) ($siteSettings['privacy_url'] ?? '/politique-de-confidentialite'));
?>

<div class="footer-legal">
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>. Tous droits réservés.</p>
    <div class="footer-legal__links">
        <a href="<?= htmlspecialchars($legalNoticeUrl, ENT_QUOTES, 'UTF-8') ?>">Mentions légales</a>
        <a href="<?= htmlspecialchars($privacyUrl, ENT_QUOTES, 'UTF-8') ?>">Confidentialité</a>
    </div>
</div>