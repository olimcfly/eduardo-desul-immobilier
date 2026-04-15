<?php
// ============================================================
// ANCIEN FICHIER DATABASE - VERSION SIMPLIFIÉE SANS CONFLIT
// ============================================================

// Si la classe Database n'existe pas encore, inclure la nouvelle
if (!class_exists('Database')) {
    require_once __DIR__ . '/../Database.php';
}

// Fonction db() - Compatibilité avec l'ancien code
function db(): PDO {
    return Database::getInstance();
}

// Fonction getPDO() - Alternative si utilisée
function getPDO(): PDO {
    return Database::getInstance();
}
