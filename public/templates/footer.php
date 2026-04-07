<?php
$advisorName = trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', ''));
if ($advisorName === '') {
    $advisorName = ADVISOR_NAME ?: APP_NAME;
}
$advisorTitle = setting('advisor_title', 'Conseiller Immobilier');
$advisorTagline = setting('advisor_tagline', '');
$zoneCity = setting('zone_city', APP_CITY);
?>
<footer class="site-footer">
    <div class="container footer__grid">

        <!-- Identité -->
        <div class="footer__col footer__brand">
            <a href="/" class="footer__logo">
                <span>🏡</span>
                <span><strong><?= e($advisorName) ?></strong><br><em><?= e((string)$advisorTitle) ?></em></span>
            </a>
            <p class="footer__tagline"><?= e($advisorTagline ?: "Expert immobilier indépendant. Je vous accompagne dans l'achat, la vente et l'estimation de votre bien avec transparence et proximité.") ?></p>
            <div class="footer__social">
                <a href="#" class="social-link" aria-label="Facebook" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="#" class="social-link" aria-label="LinkedIn" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
                <a href="#" class="social-link" aria-label="Instagram" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
            </div>
        </div>

        <!-- Services -->
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

        <!-- Informations -->
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

        <!-- Contact -->
        <div class="footer__col">
            <h3 class="footer__title">Contact</h3>
            <address class="footer__address">
                <p>📍 <?= e(APP_ADDRESS) ?></p>
                <?php if (APP_PHONE): ?>
                <p>📞 <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>"><?= e(APP_PHONE) ?></a></p>
                <?php endif; ?>
                <p>✉️ <a href="mailto:<?= e(APP_EMAIL) ?>"><?= e(APP_EMAIL) ?></a></p>
            </address>
            <a href="/contact" class="btn btn--outline btn--sm" style="margin-top:1rem">Nous contacter</a>
        </div>

    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> — Tous droits réservés<?= APP_SIRET ? ' — SIRET&nbsp;: ' . e(APP_SIRET) : '' ?>.</p>
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
