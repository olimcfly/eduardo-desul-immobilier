<?php
/**
 * Pont legacy vers le nouvel éditeur de pages structuré.
 */
$redirectUrl = '/admin/dashboard.php?page=pages&action=create';
?>
<script>window.location.href = "<?= $redirectUrl ?>";</script>
<div style="display:flex;align-items:center;justify-content:center;min-height:300px;color:#94a3b8;gap:12px">
    <i class="fas fa-spinner fa-spin" style="font-size:20px;color:#3b82f6"></i>
    <span>Ouverture de l’éditeur de page...</span>
</div>
