<?php
$advisorFirst = trim((string) setting('advisor_firstname', setting('profil_prenom', '')));
$advisorLast = trim((string) setting('advisor_lastname', setting('profil_nom', '')));
$advisorName = trim($advisorFirst . ' ' . $advisorLast);
if ($advisorName === '') {
    $advisorName = ADVISOR_NAME ?: APP_NAME;
}
$advisorTitle = setting('advisor_title', 'Conseiller Immobilier');
$advisorTagline = setting('advisor_tagline', defined('APP_ADVISOR_TAGLINE') ? APP_ADVISOR_TAGLINE : '');
$zoneCity = setting('zone_city', APP_CITY);
$advisorPhone = trim((string) setting('advisor_phone', setting('profil_telephone', APP_PHONE)));
$advisorEmail = trim((string) setting('advisor_email', setting('profil_email', APP_EMAIL)));
$advisorRsac = trim((string) setting('advisor_rsac', setting('profil_rsac', ADVISOR_RSAC)));
$socialFacebook = trim((string) setting('social_facebook', ''));
$socialLinkedin = trim((string) setting('social_linkedin', ''));
$socialInstagram = trim((string) setting('social_instagram', ''));
?>
<footer class="site-footer">
    <div class="container footer__grid">
        <div class="footer__col footer__brand">
            <a href="/" class="footer__logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span><strong><?= e($advisorName) ?></strong><br><em><?= e((string)$advisorTitle) ?></em></span>
            </a>
            <?php if ((string) $advisorTagline !== ''): ?>
            <p class="footer__tagline"><?= e((string) $advisorTagline) ?></p>
            <?php endif; ?>
            <div class="footer__social">
                <a href="<?= e($socialFacebook !== '' ? $socialFacebook : '#') ?>" class="social-link" aria-label="Facebook" rel="noopener noreferrer"<?= $socialFacebook !== '' ? ' target="_blank"' : '' ?>>
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="<?= e($socialLinkedin !== '' ? $socialLinkedin : '#') ?>" class="social-link" aria-label="LinkedIn" rel="noopener noreferrer"<?= $socialLinkedin !== '' ? ' target="_blank"' : '' ?>>
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
                <a href="<?= e($socialInstagram !== '' ? $socialInstagram : '#') ?>" class="social-link" aria-label="Instagram" rel="noopener noreferrer"<?= $socialInstagram !== '' ? ' target="_blank"' : '' ?>>
                    <svg viewBox="0 0 24 24" width="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
            </div>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">Services</h3>
            <ul class="footer__links">
                <li><a href="/services">Tous les services</a></li>
                <li><a href="/estimation-gratuite">Estimation gratuite</a></li>
                <li><a href="/biens">Annonces immobilières</a></li>
                <li><a href="/ressources/guide-vendeur">Guide vendeur</a></li>
                <li><a href="/ressources/guide-acheteur">Guide acheteur</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">Informations</h3>
            <ul class="footer__links">
                <li><a href="/a-propos">À propos</a></li>
                <li><a href="/blog">Blog immobilier</a></li>
                <li><a href="/actualites">Actualités</a></li>
                <li><a href="/guide-local">Guide local <?= e($zoneCity ?: "local") ?></a></li>
                <li><a href="/avis">Avis clients</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">Contact</h3>
            <address class="footer__address">
                <?php if (defined('APP_ADDRESS') && APP_ADDRESS !== ''): ?>
                <p><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:.35rem" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><?= e(APP_ADDRESS) ?></p>
                <?php endif; ?>
                <?php if ($advisorPhone !== ''): ?>
                <p><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:.35rem" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 14a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 3.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg><a href="tel:<?= e(preg_replace('/\s+/', '', $advisorPhone)) ?>"><?= e($advisorPhone) ?></a></p>
                <?php endif; ?>
                <?php if ($advisorEmail !== ''): ?>
                <p><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:.35rem" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><a href="mailto:<?= e($advisorEmail) ?>"><?= e($advisorEmail) ?></a></p>
                <?php endif; ?>
            </address>
            <a href="/contact" class="btn btn--outline btn--sm" style="margin-top:1rem">Nous contacter</a>
        </div>
    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> — Tous droits réservés<?= APP_SIRET ? ' — SIRET&nbsp;: ' . e(APP_SIRET) : '' ?><?= $advisorRsac !== '' ? ' — RCS / RSAC&nbsp;: ' . e($advisorRsac) : '' ?>.</p>
            <nav aria-label="Liens légaux">
                <a href="/mentions-legales">Mentions légales</a>
                <a href="/politique-confidentialite">Confidentialité</a>
                <a href="/politique-cookies">Cookies</a>
                <a href="/cgv">CGV</a>
                <a href="/plan-du-site">Plan du site</a>
            </nav>
        </div>
    </div>
</footer>
