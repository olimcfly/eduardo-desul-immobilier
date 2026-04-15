<?php
require_once __DIR__ . '/config.php';

class SettingsManager {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO(
            "mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT.";charset=".DB_CHARSET,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    public function getSetting($key) {
        $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }

    public function setSetting($key, $value) {
        $stmt = $this->pdo->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, $value]);
        return true;
    }

    public function getAllSettings() {
        $stmt = $this->pdo->query("SELECT * FROM settings");
        return $stmt->fetchAll();
    }
}

// Exemple d'utilisation
$settingsManager = new SettingsManager();
echo "Nom du site : " . $settingsManager->getSetting('site_name');
