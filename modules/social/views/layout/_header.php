<?php
$activeView = (string) ($_GET['action'] ?? 'sequences');
$isJournal = $activeView === 'journal';
?>
<section class="social-module-header">
    <div>
        <h1>Séquences de posts</h1>
        <p><?= $isJournal ? 'Vue chronologique des publications.' : 'Gestion visuelle des séquences automatiques.' ?></p>
    </div>
    <div class="social-actions-top">
        <a class="social-chip<?= $isJournal ? '' : ' is-active' ?>" href="/admin?module=social&action=sequences">Grille</a>
        <a class="social-chip<?= $isJournal ? ' is-active' : '' ?>" href="/admin?module=social&action=journal">Journal</a>
        <a class="social-btn social-btn-primary" href="/admin?module=social&action=post-form">+ Nouvelle publication</a>
    </div>
</section>
