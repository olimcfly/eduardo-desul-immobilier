<?php
/**
 * TEMPLATES HELPER
 * Fonctions pour manipuler templates et blocs
 */

if (!function_exists('getTemplatesConfig')) {
    /**
     * Charge la configuration des templates
     */
    function getTemplatesConfig() {
        static $config = null;
        if ($config === null) {
            $configPath = dirname(__DIR__) . '/../config/templates-config.php';
            $config = file_exists($configPath) ? require $configPath : [];
        }
        return $config;
    }
}

if (!function_exists('getAvailableTemplates')) {
    /**
     * Retourne la liste des templates disponibles
     * @return array ['home' => ['name' => 'Accueil', ...], ...]
     */
    function getAvailableTemplates() {
        $config = getTemplatesConfig();
        return $config['templates'] ?? [];
    }
}

if (!function_exists('getTemplate')) {
    /**
     * Retourne la config d'un template spécifique
     */
    function getTemplate($templateKey) {
        $templates = getAvailableTemplates();
        return $templates[$templateKey] ?? null;
    }
}

if (!function_exists('getBlockTypesConfig')) {
    /**
     * Retourne la config des types de blocs
     */
    function getBlockTypesConfig() {
        $config = getTemplatesConfig();
        return $config['block_types'] ?? [];
    }
}

if (!function_exists('getBlockType')) {
    /**
     * Retourne la config d'un type de bloc
     */
    function getBlockType($blockType) {
        $types = getBlockTypesConfig();
        return $types[$blockType] ?? null;
    }
}

if (!function_exists('getPageBlocks')) {
    /**
     * Charge les blocs d'une page depuis la DB
     * @param PDO $db
     * @param int $pageId
     * @return array Blocs indexés par block_key
     */
    function getPageBlocks($db, $pageId) {
        try {
            $stmt = $db->prepare("
                SELECT block_key, block_type, block_data, is_visible
                FROM page_blocks
                WHERE page_id = ?
                ORDER BY block_order, id ASC
            ");
            $stmt->execute([(int)$pageId]);

            $blocks = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $blocks[$row['block_key']] = [
                    'type' => $row['block_type'],
                    'data' => json_decode($row['block_data'], true) ?: [],
                    'visible' => (bool)$row['is_visible'],
                ];
            }
            return $blocks;
        } catch (Exception $e) {
            error_log("getPageBlocks error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getPageBlock')) {
    /**
     * Charge un bloc spécifique d'une page
     */
    function getPageBlock($db, $pageId, $blockKey) {
        try {
            $stmt = $db->prepare("
                SELECT block_type, block_data, is_visible
                FROM page_blocks
                WHERE page_id = ? AND block_key = ?
                LIMIT 1
            ");
            $stmt->execute([(int)$pageId, $blockKey]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return null;

            return [
                'type' => $row['block_type'],
                'data' => json_decode($row['block_data'], true) ?: [],
                'visible' => (bool)$row['is_visible'],
            ];
        } catch (Exception $e) {
            error_log("getPageBlock error: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('savePageBlock')) {
    /**
     * Sauvegarde ou met à jour un bloc d'une page
     */
    function savePageBlock($db, $pageId, $blockKey, $blockType, $blockData, $blockOrder = 0) {
        try {
            $stmt = $db->prepare("
                INSERT INTO page_blocks (page_id, block_key, block_type, block_data, block_order)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                  block_type = VALUES(block_type),
                  block_data = VALUES(block_data),
                  block_order = VALUES(block_order),
                  updated_at = NOW()
            ");

            $jsonData = is_string($blockData) ? $blockData : json_encode($blockData, JSON_UNESCAPED_UNICODE);

            return $stmt->execute([
                (int)$pageId,
                $blockKey,
                $blockType,
                $jsonData,
                (int)$blockOrder
            ]);
        } catch (Exception $e) {
            error_log("savePageBlock error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('deletePageBlock')) {
    /**
     * Supprime un bloc d'une page
     */
    function deletePageBlock($db, $pageId, $blockKey) {
        try {
            $stmt = $db->prepare("DELETE FROM page_blocks WHERE page_id = ? AND block_key = ?");
            return $stmt->execute([(int)$pageId, $blockKey]);
        } catch (Exception $e) {
            error_log("deletePageBlock error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('initializePageBlocks')) {
    /**
     * Initialise les blocs d'une page selon son template
     * Crée les entrées page_blocks pour chaque bloc du template
     */
    function initializePageBlocks($db, $pageId, $templateKey) {
        try {
            $template = getTemplate($templateKey);
            if (!$template) return false;

            $blocks = $template['blocks'] ?? [];
            $order = 0;

            foreach ($blocks as $blockKey => $blockConfig) {
                $blockType = $blockConfig['type'] ?? 'text';
                $emptyData = [];

                // Initialiser avec champs vides basés sur la config
                if (!empty($blockConfig['fields'])) {
                    foreach ($blockConfig['fields'] as $fieldKey => $fieldConfig) {
                        $emptyData[$fieldKey] = '';
                    }
                }

                savePageBlock($db, $pageId, $blockKey, $blockType, $emptyData, $order);
                $order++;
            }

            return true;
        } catch (Exception $e) {
            error_log("initializePageBlocks error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('validateBlockData')) {
    /**
     * Valide les données d'un bloc contre sa configuration
     */
    function validateBlockData($templateKey, $blockKey, $blockData) {
        $template = getTemplate($templateKey);
        if (!$template) return ['valid' => false, 'errors' => ['Template introuvable']];

        $blockConfig = $template['blocks'][$blockKey] ?? null;
        if (!$blockConfig) return ['valid' => false, 'errors' => ['Bloc introuvable dans le template']];

        $errors = [];
        $fields = $blockConfig['fields'] ?? [];

        // Vérifier champs obligatoires
        foreach ($fields as $fieldKey => $fieldConfig) {
            if (!empty($fieldConfig['required']) && empty($blockData[$fieldKey])) {
                $errors[] = $fieldConfig['label'] . ' est obligatoire';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

if (!function_exists('getPageWithBlocks')) {
    /**
     * Charge une page complète avec tous ses blocs
     */
    function getPageWithBlocks($db, $pageSlug) {
        try {
            // Charger la page
            $stmt = $db->prepare("
                SELECT * FROM pages
                WHERE slug = ? AND status = 'published'
                LIMIT 1
            ");
            $stmt->execute([$pageSlug]);
            $page = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$page) return null;

            // Charger ses blocs
            $page['blocks'] = getPageBlocks($db, $page['id']);
            $page['template_config'] = getTemplate($page['template'] ?? 'default');

            return $page;
        } catch (Exception $e) {
            error_log("getPageWithBlocks error: " . $e->getMessage());
            return null;
        }
    }
}
