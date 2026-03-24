<?php
/**
 * /admin/api/builder/template-load.php
 * Endpoint dédié : charge un template par son ID
 */
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 2) . '/includes/init.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'error'=>'id requis']); exit; }

try {
    // Essayer builder_templates d'abord
    $tpl = null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM builder_templates WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {}

    // Fallback : table templates
    if (!$tpl) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM templates WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                // Normaliser les colonnes
                $tpl = [
                    'id'           => $row['id'],
                    'name'         => $row['name'],
                    'html_content' => $row['html_content'] ?? $row['content'] ?? '',
                    'css_content'  => $row['css_content']  ?? $row['custom_css'] ?? '',
                    'js_content'   => $row['js_content']   ?? $row['custom_js']  ?? '',
                ];
            }
        } catch(PDOException $e) {}
    }

    if (!$tpl) {
        echo json_encode(['success'=>false,'error'=>'Template #'.$id.' introuvable']); exit;
    }

    // Normaliser : html_content peut s'appeler content, blocks_data (JSON), etc.
    $html = $tpl['html_content'] ?? $tpl['content'] ?? '';
    $css  = $tpl['css_content']  ?? $tpl['custom_css'] ?? $tpl['css'] ?? '';
    $js   = $tpl['js_content']   ?? $tpl['custom_js']  ?? $tpl['js']  ?? '';

    // Si blocks_data est présent et html vide, on extrait le HTML des blocs
    if (empty($html) && !empty($tpl['blocks_data'])) {
        $blocks = is_array($tpl['blocks_data']) ? $tpl['blocks_data'] : json_decode($tpl['blocks_data'], true);
        if (is_array($blocks)) {
            $parts = [];
            foreach ($blocks as $b) {
                if (!empty($b['html']))    $parts[] = $b['html'];
                elseif (!empty($b['content'])) $parts[] = $b['content'];
            }
            $html = implode("\n", $parts);
        }
    }

    // Incrémenter usage_count si la colonne existe
    try {
        $pdo->prepare("UPDATE builder_templates SET usage_count = COALESCE(usage_count,0)+1 WHERE id=?")->execute([$id]);
    } catch(PDOException $e) {}

    echo json_encode([
        'success'  => true,
        'template' => [
            'id'           => $tpl['id'],
            'name'         => $tpl['name'] ?? '',
            'html_content' => $html,
            'css_content'  => $css,
            'js_content'   => $js,
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
