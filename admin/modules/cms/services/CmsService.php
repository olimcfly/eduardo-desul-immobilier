<?php
namespace Admin\Modules\Cms\Services;

class CmsService {
    private $db;

    public function __construct() {
        $this->db = \Database::getInstance();
    }

    // Liste des pages gérées par le CMS
    public function getPagesList() {
        return [
            ['slug' => 'home', 'title' => 'Accueil'],
            ['slug' => 'a-propos', 'title' => 'À propos'],
            ['slug' => 'contact', 'title' => 'Contact'],
            ['slug' => 'blog', 'title' => 'Blog'],
        ];
    }

    // Récupérer les données d'une page
    public function getPageData($page_slug) {
        return $this->getPageContent((string) $page_slug);
    }

    public function getPageContent(string $page_slug): array
    {
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

    public function getSectionsDefinition(string $page_slug): array
    {
        if ($page_slug === 'blog') {
            return [
                'hero' => [
                    'title' => 'Hero du Blog',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                    ],
                ],
            ];
        }

        return [
            'main' => [
                'title' => 'Contenu principal',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Titre'],
                    'content' => ['type' => 'textarea', 'label' => 'Contenu'],
                ],
            ],
        ];
    }

    // Sauvegarder une page
    public function savePage($data) {
        $pageSlug = (string) ($data['page_slug'] ?? '');
        $sections = $data['sections'] ?? $data;
        unset($sections['page_slug']);
        $this->savePageContent($pageSlug, is_array($sections) ? $sections : []);
    }

    public function savePageContent(string $page_slug, array $sections): void
    {
        foreach ($sections as $section_name => $fields) {
            if (!is_array($fields)) {
                continue;
            }
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
