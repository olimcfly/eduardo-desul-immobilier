<?php
$guide = $guideContext['guide'] ?? [];
$persona = $guideContext['persona'] ?? 'persona';
$personaLabel = $guideContext['persona_label'] ?? ucfirst($persona);

$pageTitle = ($guide['title'] ?? 'Guide immobilier') . ' — ' . ADVISOR_NAME . '';
$metaDesc = $guide['excerpt'] ?? 'Guide immobilier pratique par persona.';
$extraCss = ['/assets/css/guide.css'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a>
            <a href="/ressources">Ressources</a>
            <span><?= e($guide['title'] ?? 'Guide') ?></span>
        </nav>
        <h1><?= e($guide['title'] ?? 'Guide') ?></h1>
        <p><?= e($guide['excerpt'] ?? '') ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="article-layout">
            <div>
                <div class="article-content">
                    <h2>Guide persona : <?= e($personaLabel) ?></h2>
                    <div class="guide-steps">
                        <?php foreach (($guide['points'] ?? []) as $point): ?>
                        <div class="guide-step">
                            <div class="guide-step__content">
                                <h3>Point clé</h3>
                                <p><?= e($point) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <aside class="blog-sidebar">
                <div class="guide-form-box">
                    <h3>Être rappelé pour ce guide</h3>
                    <p>Laissez votre nom et email, votre demande est enregistrée dans les leads.</p>
                    <form action="/ressources/guides/<?= e($persona) ?>/<?= e($guide['slug'] ?? '') ?>" method="POST">
                        <?= csrfField() ?>
                        <div class="form-group">
                            <label class="form-label" for="lead-nom">Nom <span>*</span></label>
                            <input id="lead-nom" name="nom" type="text" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lead-email">Email <span>*</span></label>
                            <input id="lead-email" name="email" type="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lead-telephone">Téléphone</label>
                            <input id="lead-telephone" name="telephone" type="tel" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lead-message">Message</label>
                            <textarea id="lead-message" name="message" class="form-control" rows="4" placeholder="Parlez-nous de votre projet..."></textarea>
                        </div>
                        <button class="btn btn--primary btn--full" type="submit">Envoyer ma demande</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>
