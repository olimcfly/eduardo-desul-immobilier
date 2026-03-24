<?php
/**
 *  /admin/api/builder/builder.php
 *  Builder Pro — save universel multi-table + templates + layouts
 *
 *  Actions: save_content, save, save-direct,
 *           templates-list, template-load, template-save, template-delete, template-apply,
 *           layouts
 */

$pdo    = $ctx['pdo'];
$action = $ctx['action'];
$method = $ctx['method'];
$p      = $ctx['params'];

// ═══════════════════════════════════════════════════════════
//  MAPPING : context → table + colonne HTML
// ═══════════════════════════════════════════════════════════
function getTableConfig(string $context): array {
    return match($context) {
        'secteur'  => ['table' => 'secteurs',  'html_col' => 'content'],
        'article'  => ['table' => 'articles',  'html_col' => 'content'],
        'capture'  => ['table' => 'captures',  'html_col' => 'html_capture'],
        'landing'  => ['table' => 'pages',     'html_col' => 'content'],
        'header'   => ['table' => 'site_headers', 'html_col' => 'custom_html'],
        'footer'   => ['table' => 'site_footers', 'html_col' => 'custom_html'],
        default    => ['table' => 'pages',     'html_col' => 'content'],
    };
}

// Vérifie qu'une colonne existe dans la table (sécurité)
function colExists(PDO $pdo, string $table, string $col): bool {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
        return in_array($col, $cols);
    } catch (PDOException $e) {
        return false;
    }
}

// ═══════════════════════════════════════════════════════════
//  save_content — action envoyée par editor.php (JS BP.save)
// ═══════════════════════════════════════════════════════════
if (($action === 'save_content' || $action === 'save-content') && $method === 'POST') {

    $context      = trim($p['context']      ?? '');
    $entityId     = (int)($p['entity_id']   ?? 0);
    $sourceTable  = trim($p['source_table'] ?? '');   // passé par le JS comme hint
    $htmlContent  = $p['html_content']      ?? '';
    $customCss    = $p['custom_css']        ?? '';
    $customJs     = $p['custom_js']         ?? '';
    $headerId     = $p['header_id']         ?? null;
    $footerId     = $p['footer_id']         ?? null;
    $layoutId     = (int)($p['layout_id']   ?? 0);
    $metaTitle    = $p['meta_title']        ?? '';
    $metaDesc     = $p['meta_description']  ?? '';
    $focusKw      = $p['focus_keyword']     ?? '';
    $status       = $p['status']            ?? 'draft';

    if (!$context || !$entityId) {
        return ['success' => false, 'error' => 'context et entity_id requis'];
    }

    // Déterminer table et colonne HTML
    $cfg      = getTableConfig($context);
    $table    = !empty($sourceTable) ? $sourceTable : $cfg['table'];
    $htmlCol  = $cfg['html_col'];

    // Si la colonne déclarée n'existe pas, chercher un fallback
    if (!colExists($pdo, $table, $htmlCol)) {
        foreach (['content', 'html_content', 'contenu', 'html_capture'] as $try) {
            if (colExists($pdo, $table, $try)) { $htmlCol = $try; break; }
        }
    }

    // Construire l'UPDATE dynamiquement selon les colonnes disponibles
    try {
        $allCols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return ['success' => false, 'error' => "Table `{$table}` introuvable : " . $e->getMessage()];
    }

    $sets = ["`{$htmlCol}` = ?"];
    $vals = [$htmlContent];

    if (in_array('custom_css', $allCols))        { $sets[] = '`custom_css` = ?';        $vals[] = $customCss; }
    if (in_array('custom_js', $allCols))         { $sets[] = '`custom_js` = ?';         $vals[] = $customJs; }
    if (in_array('header_id', $allCols) && $headerId !== null && $headerId !== 'default') {
        $sets[] = '`header_id` = ?'; $vals[] = ($headerId === '' ? null : (int)$headerId);
    }
    if (in_array('footer_id', $allCols) && $footerId !== null && $footerId !== 'default') {
        $sets[] = '`footer_id` = ?'; $vals[] = ($footerId === '' ? null : (int)$footerId);
    }
    if (in_array('meta_title', $allCols) && $metaTitle !== '')       { $sets[] = '`meta_title` = ?';       $vals[] = $metaTitle; }
    if (in_array('meta_description', $allCols) && $metaDesc !== '')  { $sets[] = '`meta_description` = ?'; $vals[] = $metaDesc; }
    if (in_array('focus_keyword', $allCols) && $focusKw !== '')      { $sets[] = '`focus_keyword` = ?';    $vals[] = $focusKw; }
    if (in_array('status', $allCols))            { $sets[] = '`status` = ?';             $vals[] = $status; }
    if (in_array('updated_at', $allCols))        { $sets[] = '`updated_at` = NOW()'; }

    $vals[] = $entityId;

    try {
        $sql  = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE `id` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);
        $affected = $stmt->rowCount();

        return [
            'success'   => true,
            'message'   => "Contenu sauvegardé ({$context} #{$entityId})",
            'table'     => $table,
            'html_col'  => $htmlCol,
            'rows'      => $affected,
            'status'    => $status,
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'SQL error: ' . $e->getMessage(), 'sql' => $sql];
    }
}

// ═══════════════════════════════════════════════════════════
//  save — ancien format (pages uniquement, rétro-compat)
// ═══════════════════════════════════════════════════════════
if ($action === 'save' && $method === 'POST') {
    $pageId  = (int)($p['page_id'] ?? $p['id'] ?? 0);
    $content = $p['content'] ?? $p['html'] ?? '';
    $css     = $p['css'] ?? '';
    $js      = $p['js'] ?? '';
    if ($pageId > 0) {
        $pdo->prepare("UPDATE pages SET content=?, custom_css=?, custom_js=?, updated_at=NOW() WHERE id=?")
            ->execute([$content, $css, $js, $pageId]);
        return ['success' => true, 'message' => 'Page sauvegardée', 'id' => $pageId];
    }
    return ['success' => false, 'error' => 'page_id requis'];
}

// ═══════════════════════════════════════════════════════════
//  save-direct — sauvegarde fichier physique
// ═══════════════════════════════════════════════════════════
if ($action === 'save-direct' && $method === 'POST') {
    // Nouveau format : context + entity_id (depuis editor.php)
    $context  = trim($p['context']    ?? '');
    $entityId = (int)($p['entity_id'] ?? 0);
    if ($context && $entityId) {
        // Réutiliser save_content
        $p['action'] = 'save_content';
        $ctx2 = array_merge($ctx, ['action' => 'save_content', 'params' => $p]);
        // Appel récursif via re-dispatch
        $cfg     = getTableConfig($context);
        $table   = $cfg['table'];
        $htmlCol = $cfg['html_col'];
        $html    = $p['html_content'] ?? $p['html'] ?? '';
        $css     = $p['custom_css']   ?? $p['css']  ?? '';
        $js      = $p['custom_js']    ?? $p['js']   ?? '';
        $status  = $p['status']       ?? 'draft';
        try {
            $allCols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array($htmlCol, $allCols)) {
                foreach (['content','html_content','contenu','html_capture'] as $t) {
                    if (in_array($t, $allCols)) { $htmlCol = $t; break; }
                }
            }
            $sets = ["`{$htmlCol}`=?"]; $vals = [$html];
            if (in_array('custom_css',$allCols)) { $sets[]='`custom_css`=?'; $vals[]=$css; }
            if (in_array('custom_js', $allCols)) { $sets[]='`custom_js`=?';  $vals[]=$js; }
            if (in_array('status',    $allCols)) { $sets[]='`status`=?';      $vals[]=$status; }
            if (in_array('updated_at',$allCols)) { $sets[]='`updated_at`=NOW()'; }
            $vals[] = $entityId;
            $pdo->prepare("UPDATE `{$table}` SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
            return ['success'=>true,'message'=>"Sauvé via save-direct ({$context} #{$entityId})"];
        } catch (PDOException $e) {
            return ['success'=>false,'error'=>$e->getMessage()];
        }
    }
    // Ancien format : path + html (fichier physique)
    $path = $p['path'] ?? '';
    $html = $p['html'] ?? '';
    if (empty($path) || empty($html)) return ['success'=>false,'error'=>'path et html requis'];
    $base = realpath(__DIR__ . '/../../..');
    $full = $base . '/' . ltrim($path, '/');
    if (strpos(realpath($full) ?: $full, $base) !== 0) return ['success'=>false,'error'=>'Chemin invalide'];
    file_put_contents($full, $html);
    return ['success'=>true,'message'=>"Fichier sauvegardé: {$path}"];
}

// ═══════════════════════════════════════════════════════════
//  templates-list
// ═══════════════════════════════════════════════════════════
if ($action === 'templates-list') {
    try {
        $ctx2 = $p['context'] ?? '';
        $sql  = "SELECT id, name, slug, category, thumbnail, is_default, created_at FROM builder_templates";
        if ($ctx2) $sql .= " WHERE category = " . $pdo->quote($ctx2) . " OR category = 'general' OR category IS NULL";
        $sql .= " ORDER BY is_default DESC, name ASC";
        $rows = $pdo->query($sql)->fetchAll();
        return ['success'=>true,'templates'=>$rows];
    } catch (PDOException $e) {
        return ['success'=>true,'templates'=>[],'_warn'=>$e->getMessage()];
    }
}

// ═══════════════════════════════════════════════════════════
//  template-load
// ═══════════════════════════════════════════════════════════
if ($action === 'template-load') {
    $id   = (int)($p['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM builder_templates WHERE id=?");
    $stmt->execute([$id]);
    $tpl  = $stmt->fetch();
    if (!$tpl) return ['success'=>false,'error'=>'Template non trouvé','_http_code'=>404];
    return ['success'=>true,'template'=>$tpl];
}

// ═══════════════════════════════════════════════════════════
//  template-save
// ═══════════════════════════════════════════════════════════
if ($action === 'template-save' && $method === 'POST') {
    $id     = (int)($p['id'] ?? 0);
    $fields = [
        'name'      => $p['name']      ?? 'Template',
        'slug'      => $p['slug']      ?? null,
        'category'  => $p['category']  ?? 'page',
        'content'   => $p['content']   ?? '',
        'css'       => $p['css']       ?? '',
        'js'        => $p['js']        ?? '',
        'thumbnail' => $p['thumbnail'] ?? null,
    ];
    if ($id > 0) {
        $sets=[]; $vals=[];
        foreach ($fields as $c=>$v) { $sets[]="`{$c}`=?"; $vals[]=$v; }
        $vals[]=$id;
        $pdo->prepare("UPDATE builder_templates SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        return ['success'=>true,'message'=>'Template mis à jour','id'=>$id];
    }
    $cols = array_keys($fields);
    $pdo->prepare("INSERT INTO builder_templates (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($fields));
    return ['success'=>true,'message'=>'Template créé','id'=>(int)$pdo->lastInsertId()];
}

// ═══════════════════════════════════════════════════════════
//  template-delete
// ═══════════════════════════════════════════════════════════
if ($action === 'template-delete' && $method === 'POST') {
    $pdo->prepare("DELETE FROM builder_templates WHERE id=?")->execute([(int)($p['id']??0)]);
    return ['success'=>true,'message'=>'Template supprimé'];
}

// ═══════════════════════════════════════════════════════════
//  template-apply
// ═══════════════════════════════════════════════════════════
if ($action === 'template-apply' && $method === 'POST') {
    $tplId    = (int)($p['template_id'] ?? 0);
    $pageId   = (int)($p['page_id']     ?? 0);
    $context  = $p['context'] ?? 'landing';
    $entityId = (int)($p['entity_id']   ?? $pageId);

    $stmt = $pdo->prepare("SELECT content, css, js FROM builder_templates WHERE id=?");
    $stmt->execute([$tplId]);
    $tpl = $stmt->fetch();
    if (!$tpl) return ['success'=>false,'error'=>'Template non trouvé'];

    $cfg     = getTableConfig($context);
    $table   = $cfg['table'];
    $htmlCol = $cfg['html_col'];

    try {
        $allCols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array($htmlCol, $allCols)) {
            foreach (['content','html_content','contenu'] as $t) {
                if (in_array($t, $allCols)) { $htmlCol = $t; break; }
            }
        }
        $sets = ["`{$htmlCol}`=?"]; $vals = [$tpl['content']];
        if (in_array('custom_css',$allCols)) { $sets[]='`custom_css`=?'; $vals[]=$tpl['css']??''; }
        if (in_array('custom_js', $allCols)) { $sets[]='`custom_js`=?';  $vals[]=$tpl['js']??''; }
        $vals[] = $entityId;
        $pdo->prepare("UPDATE `{$table}` SET ".implode(',',$sets)." WHERE id=?")->execute($vals);
        return ['success'=>true,'message'=>'Template appliqué'];
    } catch (PDOException $e) {
        return ['success'=>false,'error'=>$e->getMessage()];
    }
}

// ═══════════════════════════════════════════════════════════
//  layouts
// ═══════════════════════════════════════════════════════════
if ($action === 'layouts') {
    try {
        $rows = $pdo->query("SELECT * FROM builder_layouts ORDER BY is_default DESC, name ASC")->fetchAll();
        return ['success'=>true,'layouts'=>$rows];
    } catch (PDOException $e) {
        return ['success'=>true,'layouts'=>[],'_warn'=>$e->getMessage()];
    }
}

// ═══════════════════════════════════════════════════════════
//  Fallback
// ═══════════════════════════════════════════════════════════
return [
    'success'  => false,
    'error'    => "Action '{$action}' non reconnue",
    '_http_code' => 404,
    'actions'  => ['save_content','save','save-direct','templates-list','template-load','template-save','template-delete','template-apply','layouts'],
];