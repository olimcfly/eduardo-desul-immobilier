<?php
namespace Admin\Modules\Cms\Services;

use Core\Database;

class CmsService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Liste des pages gérées par le CMS
    public function getPagesList() {
        return [
            ['slug' => 'home', 'title' => 'Accueil'],
            ['slug' => 'a-propos', 'title' => 'À propos'],
            ['slug' => 'contact', 'title' => 'Contact']
        ];
    }

    // Récupérer les données d'une page
    public function getPageData($page_slug) {
        $stmt = $this->db->prepare("
            SELECT section_name, field_name, field_value, field_type
            FROM page_contents
            WHERE page_slug = ?
            ORDER BY `order`
        ");
        $stmt->execute([$page_slug]);
        $sections = $stmt->fetchAll();

        $result = [];
        foreach ($sections as $section) {
            if ($section['field_type'] === 'repeater') {
                $section['field_value'] = json_decode($section['field_value'], true);
            }
            $result[$section['section_name']][$section['field_name']] = $section['field_value'];
        }
        return $result;
    }

    // Sauvegarder une page
    public function savePage($data) {
        $page_slug = $data['page_slug'];
        unset($data['page_slug']); // On ne sauvegarde pas le slug comme champ

        foreach ($data as $section_name => $fields) {
            foreach ($fields as $field_name => $field_value) {
                $field_type = 'text';
                if (is_array($field_value)) {
                    $field_value = json_encode($field_value);
                    $field_type = 'repeater';
                } elseif (strpos($field_value, '<') !== false) {
                    $field_type = 'richtext';
                }

                $stmt = $this->db->prepare("
                    INSERT INTO page_contents (page_slug, section_name, field_name, field_value, field_type)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        field_value = VALUES(field_value),
                        field_type = VALUES(field_type)
                ");
                $stmt->execute([$page_slug, $section_name, $field_name, $field_value, $field_type]);
            }
        }
    }
}
