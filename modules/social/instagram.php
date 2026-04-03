<?php
require_once __DIR__ . '/includes/_bootstrap.php';
$pageTitle = 'Social Instagram';
$pageDescription = 'Gestion Instagram';
require_once __DIR__ . '/../../admin/views/layout.php';

function renderContent()
{
    ?>
    <link rel="stylesheet" href="/modules/social/assets/social.css">
    <section class="social-page">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &gt; <a href="/admin/?module=social">Social</a> &gt; Instagram</div>
        <h1>Instagram</h1>
        <div class="tabs"><button class="active" data-tab="create">Créer une publication</button><button data-tab="posts">Mes publications IG</button><button data-tab="stats">Statistiques Instagram</button></div>
        <div class="tab active" id="tab-create">
            <form id="ig-create-form"><?= csrfField() ?>
                <label>Type <select name="type_post"><option>post</option><option>story</option><option>carrousel</option><option>reel</option></select></label>
                <label>Médias <input type="file" name="media[]" multiple required></label>
                <label>Légende <textarea id="ig-caption" name="contenu" maxlength="2200"></textarea><small id="ig-count">0 / 2200</small></label>
                <label>Hashtags (max 30) <input type="text" id="ig-tags" name="hashtags"></label>
            </form>
        </div>
        <div class="tab" id="tab-posts"><div class="ig-grid" id="ig-posts-grid"></div></div>
        <div class="tab" id="tab-stats"><canvas id="ig-chart"></canvas></div>
    </section>
    <script src="/modules/social/assets/social.js"></script>
    <?php
}
