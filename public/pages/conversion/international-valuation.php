<?php

declare(strict_types=1);

$config = require ROOT_PATH . '/public/pages/conversion/config/international-valuation.php';

$locale = $GLOBALS['landingLocale'] ?? 'fr';
$locale = is_string($locale) ? strtolower(trim($locale)) : 'fr';
$localeData = $config['locales'][$locale] ?? $config['locales']['fr'];
$advisor = $config['advisor'];

$meta = $localeData['meta'];
$pageTitle = $meta['title'];
$metaDesc = $meta['description'];
$extraCss = ['/assets/css/international-valuation.css'];

$htmlLang = $localeData['html_lang'];
$ogLocale = $localeData['og_locale'];
$canonical = rtrim(APP_URL, '/') . $localeData['slug'];
$showPrimaryNav = true;
$showPrimaryFooter = true;

$hreflangLinks = [];
foreach ($config['locales'] as $code => $data) {
    $hreflangLinks[$code] = rtrim(APP_URL, '/') . $data['slug'];
}
$hreflangLinks['x-default'] = rtrim(APP_URL, '/') . $config['locales']['fr']['slug'];

$ctaPrimaryUrl = '/avis-de-valeur';
$ctaSecondaryUrl = '/prendre-rendez-vous';
$contactUrl = '/contact';
?>

<section class="section hero hero--premium iv-hero" aria-labelledby="international-valuation-title">
    <div class="container iv-shell">
        <span class="section-label"><?= e($localeData['hero']['label']) ?></span>
        <h1 id="international-valuation-title"><?= e($localeData['hero']['title']) ?></h1>
        <p class="hero__subtitle iv-subtitle"><?= e($localeData['hero']['subtitle']) ?></p>

        <div class="iv-cta-row">
            <a class="btn btn--primary btn--lg" href="<?= e($ctaPrimaryUrl) ?>"><?= e($localeData['hero']['primaryCta']) ?></a>
            <a class="btn btn--outline btn--lg" href="<?= e($ctaSecondaryUrl) ?>"><?= e($localeData['hero']['secondaryCta']) ?></a>
        </div>

        <ul class="iv-trust" aria-label="Trust bar">
            <?php foreach ($localeData['hero']['trust'] as $trustPoint): ?>
                <li><?= e($trustPoint) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container iv-shell">
        <h2><?= e($localeData['motivation']['title']) ?></h2>
        <p><?= e($localeData['motivation']['text']) ?></p>
    </div>
</section>

<section class="section section--alt">
    <div class="container iv-shell iv-grid">
        <div>
            <h2><?= e($localeData['positioning']['title']) ?></h2>
            <p><?= e($localeData['positioning']['text']) ?></p>
            <ul class="benefits-list">
                <?php foreach ($localeData['positioning']['points'] as $point): ?>
                    <li><?= e($point) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <aside class="card iv-profile">
            <h3><?= e($advisor['name']) ?></h3>
            <p><?= e($advisor['city']) ?> · <?= e($advisor['zone']) ?></p>
            <a href="tel:<?= e(preg_replace('/\s+/', '', (string) $advisor['phone'])) ?>" class="btn btn--outline btn--sm"><?= e($advisor['phone']) ?></a>
            <a href="mailto:<?= e($advisor['email']) ?>" class="btn btn--outline btn--sm"><?= e($advisor['email']) ?></a>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container iv-shell">
        <h2><?= e($localeData['services']['title']) ?></h2>
        <div class="grid-3 iv-services">
            <?php foreach ($localeData['services']['items'] as $service): ?>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><?= e($service['title']) ?></h3>
                        <p class="card__text"><?= e($service['text']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container iv-shell">
        <h2><?= e($localeData['method']['title']) ?></h2>
        <div class="grid-3">
            <?php foreach ($localeData['method']['steps'] as $step): ?>
                <article class="step-card">
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= e($step['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container iv-shell">
        <h2><?= e($localeData['reassurance']['title']) ?></h2>
        <div class="grid-2">
            <div>
                <?php foreach ($localeData['reassurance']['testimonials'] as $testimonial): ?>
                    <blockquote class="iv-quote"><?= e($testimonial) ?></blockquote>
                <?php endforeach; ?>
            </div>
            <div>
                <h3>FAQ</h3>
                <?php foreach ($localeData['reassurance']['faq'] as $faq): ?>
                    <details class="iv-faq-item">
                        <summary><?= e($faq['q']) ?></summary>
                        <p><?= e($faq['a']) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt iv-final-cta">
    <div class="container iv-shell text-center">
        <h2><?= e($localeData['finalCta']['title']) ?></h2>
        <p><?= e($localeData['finalCta']['text']) ?></p>
        <div class="iv-cta-row iv-cta-row--center">
            <a class="btn btn--primary btn--lg" href="<?= e($ctaSecondaryUrl) ?>"><?= e($localeData['finalCta']['button']) ?></a>
            <a class="btn btn--outline btn--lg" href="<?= e($contactUrl) ?>">Contact</a>
        </div>
    </div>
</section>
