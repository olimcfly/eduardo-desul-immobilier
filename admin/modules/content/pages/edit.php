<?php
/**
 * Pont legacy vers l’éditeur de pages structuré.
 */

$pageId = (int)($_GET['id'] ?? 0);

if ($pageId <= 0) {
    echo '<script>window.location.href="/admin/dashboard.php?page=pages";</script>';
    echo '<p style="text-align:center;padding:40px;color:#94a3b8">Redirection vers le listing...</p>';
    return;
}

$builderUrl = '/admin/dashboard.php?page=pages&action=edit&id=' . $pageId;
?>
<script>window.location.href = "<?= $builderUrl ?>";</script>
<div style="display:flex;align-items:center;justify-content:center;min-height:300px;color:#94a3b8;gap:12px">
    <i class="fas fa-spinner fa-spin" style="font-size:20px;color:#3b82f6"></i>
    <span>Ouverture de l’éditeur de page...</span>
</div>
