<?php
require_once __DIR__ . '/includes/_bootstrap.php';
$pageTitle = 'Social Facebook';
$pageDescription = 'Gestion Facebook';
require_once __DIR__ . '/../../admin/views/layout.php';

function renderContent()
{
    ?>
    <link rel="stylesheet" href="/modules/social/assets/social.css">
    <section class="social-page">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &gt; <a href="/admin/?module=social">Social</a> &gt; Facebook</div>
        <h1>Facebook</h1>
        <div class="tabs">
            <button class="active" data-tab="create">Créer un post</button>
            <button data-tab="posts">Mes posts Facebook</button>
            <button data-tab="stats">Statistiques Facebook</button>
        </div>
        <div class="tab active" id="tab-create">
            <form id="fb-create-form"><?= csrfField() ?>
                <label>Type <select name="type_post"><option>post</option><option>reel</option><option>event</option></select></label>
                <label>Contenu <textarea id="fb-content" name="contenu" maxlength="63206"></textarea><small id="fb-count">0 / 63206</small></label>
                <label>Upload médias <input type="file" name="media[]" multiple></label>
                <label>Hashtags <input type="text" name="hashtags" placeholder="#immobilier #maison"></label>
                <label><input type="radio" name="mode" value="now" checked> Publier maintenant</label>
                <label><input type="radio" name="mode" value="schedule"> Planifier</label>
                <label>Date/heure <input type="datetime-local" name="planifie_at"></label>
                <div class="actions"><button type="button" onclick="generateContent('facebook','bien',{})">Générer contenu IA</button><button type="button" onclick="saveDraft(new FormData(document.getElementById('fb-create-form')))">Sauver brouillon</button><button type="button" onclick="publishNow(0,['facebook'])">Publier</button><button type="button" onclick="schedulePost(0,document.querySelector('[name=planifie_at]').value,['facebook'])">Planifier</button></div>
            </form>
            <div class="preview" id="preview-facebook">Aperçu Facebook simulé…</div>
        </div>
        <div class="tab" id="tab-posts"><div id="fb-posts-grid"></div></div>
        <div class="tab" id="tab-stats"><canvas id="fb-chart"></canvas><div id="fb-top-posts"></div></div>
    </section>
    <script src="/modules/social/assets/social.js"></script>
    <?php
}
