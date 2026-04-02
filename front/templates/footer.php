<?php
$advisor = $advisor ?? [];
$siteName = $siteName ?? ($advisor['trade_name'] ?? 'Conseiller Immobilier');
$siteLogo = $siteLogo ?? ($advisor['logo_url'] ?? '/assets/img/logo-default.svg');
$sitePhone = $sitePhone ?? ($advisor['phone'] ?? '');
$siteEmail = $siteEmail ?? ($advisor['email'] ?? '');

$footerLinks = [
    'Informations' => [
        ['label' => 'Guide local',          'url' => '/guide-local'],
        ['label' => 'Actualités',           'url' => '/actualites'],
        ['label' => 'Ressources vendeurs',  'url' => '/ressources/vendeurs'],
        ['label' => 'Ressources acheteurs', 'url' => '/ressources/acheteurs'],
    ],
    'Services' => [
        ['label' => 'Estimation gratuite',  'url' => '/estimation'],
        ['label' => 'Vendre mon bien',      'url' => '/ressources/vendeurs'],
        ['label' => 'Acheter un bien',      'url' => '/ressources/acheteurs'],
        ['label' => 'Me contacter',         'url' => '/contact'],
    ],
    'Légal' => [
        ['label' => 'Mentions légales',              'url' => '/mentions-legales'],
        ['label' => 'Politique de confidentialité',  'url' => '/politique-confidentialite'],
        ['label' => 'CGV',                           'url' => '/cgv'],
    ],
];
?>
<footer class="site-footer">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">

                <div class="footer-about">
                    <img src="<?= htmlspecialchars($siteLogo) ?>"
                         alt="<?= htmlspecialchars($siteName) ?>"
                         class="footer-logo" width="150" height="48" loading="lazy">

                    <?php if (!empty($advisor['bio'])): ?>
                    <p class="footer-bio">
                        <?= htmlspecialchars(mb_substr($advisor['bio'], 0, 180)) ?>...
                    </p>
                    <?php endif; ?>

                    <address class="footer-address">
                        <?php if ($sitePhone): ?>
                        <a href="tel:<?= preg_replace('/\s/', '', $sitePhone) ?>" class="footer-contact-link">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <?= htmlspecialchars($sitePhone) ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($siteEmail): ?>
                        <a href="mailto:<?= htmlspecialchars($siteEmail) ?>" class="footer-contact-link">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <?= htmlspecialchars($siteEmail) ?>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($advisor['address'])): ?>
                        <span class="footer-contact-link">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <?= htmlspecialchars($advisor['address']) ?>,
                            <?= htmlspecialchars($advisor['zip'] ?? '') ?>
                            <?= htmlspecialchars($advisor['city'] ?? '') ?>
                        </span>
                        <?php endif; ?>
                    </address>

                    <div class="footer-social">
                        <?php foreach ([
                            'facebook_url'  => ['fab fa-facebook-f', 'Facebook'],
                            'instagram_url' => ['fab fa-instagram',  'Instagram'],
                            'linkedin_url'  => ['fab fa-linkedin-in','LinkedIn'],
                            'youtube_url'   => ['fab fa-youtube',    'YouTube'],
                        ] as $key => [$icon, $label]): ?>
                            <?php if (!empty($advisor[$key])): ?>
                            <a href="<?= htmlspecialchars($advisor[$key]) ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="footer-social__link" aria-label="<?= $label ?>">
                                <i class="<?= $icon ?>"></i>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php foreach ($footerLinks as $title => $links): ?>
                <div class="footer-nav-col">
                    <h3 class="footer-col-title"><?= htmlspecialchars($title) ?></h3>
                    <ul class="footer-nav-list" role="list">
                        <?php foreach ($links as $link): ?>
                        <li>
                            <a href="<?= htmlspecialchars($link['url']) ?>"
                               class="footer-nav-link">
                                <?= htmlspecialchars($link['label']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>

                <div class="footer-newsletter">
                    <h3 class="footer-col-title">Restez informé</h3>
                    <p>Recevez les dernières actualités immobilières de votre secteur.</p>
                    <form class="newsletter-form" id="footer-newsletter-form"
                          action="/api/newsletter-subscribe.php" method="post"
                          novalidate>
                        <div class="newsletter-input-group">
                            <input type="email" name="email"
                                   placeholder="Votre email"
                                   required
                                   class="newsletter-input"
                                   autocomplete="email">
                            <button type="submit" class="newsletter-btn">
                                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                            </button>
                        </div>
                        <p class="newsletter-rgpd">
                            <i class="fas fa-lock"></i>
                            Vos données sont protégées. Désinscription à tout moment.
                        </p>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom__inner">
                <p class="footer-copyright">
                    © <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>.
                    Tous droits réservés.
                    <?php if (!empty($advisor['carte_t'])): ?>
                    — Carte T n° <?= htmlspecialchars($advisor['carte_t']) ?>
                    <?php endif; ?>
                </p>
                <div class="footer-legal-links">
                    <a href="/mentions-legales">Mentions légales</a>
                    <a href="/politique-confidentialite">Confidentialité</a>
                    <a href="/cgv">CGV</a>
                </div>
            </div>
        </div>
    </div>
</footer>
