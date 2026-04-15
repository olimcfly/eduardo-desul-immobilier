<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

$baseUrl = defined('APP_URL') ? rtrim((string) APP_URL, '/') : '';
$sitemapUrl = ($baseUrl !== '' ? $baseUrl : '') . '/sitemap.xml';

echo "User-agent: *\n";
echo "Allow: /\n\n";
echo "# Bloquer pages techniques / faible valeur SEO\n";
echo "Disallow: /admin/\n";
echo "Disallow: /settings/\n";
echo "Disallow: /private/\n";
echo "Disallow: /logs/\n";
echo "Disallow: /*?*\n";
echo "Disallow: /merci\n";
echo "Disallow: /merci-estimation\n";
echo "Disallow: /tag/\n";
echo "Disallow: /author/\n";
echo "Disallow: /wp-\n\n";
echo 'Sitemap: ' . $sitemapUrl . "\n";
