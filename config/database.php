<?php
// ============================================================
// CONFIG BASE DE DONNÉES
// Toutes les valeurs viennent du .env
// ============================================================

return [
    'host'    => $_ENV['DB_HOST']    ?? 'localhost',
    'dbname'  => $_ENV['DB_NAME']    ?? '',
    'user'    => $_ENV['DB_USER']    ?? '',
    'pass'    => $_ENV['DB_PASS']    ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];
