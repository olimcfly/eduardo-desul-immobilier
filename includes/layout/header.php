<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/topbar.php';

// Mode fragment : pas de second `<html>` / `<body>`.
// Le gabarit public/templates/layout.php fournit déjà la coquille complète.
$siteHeaderEmbedOnly = true;
require_once ROOT_PATH . '/public/templates/header.php';
