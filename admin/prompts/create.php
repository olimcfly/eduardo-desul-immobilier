<?php
$basePromptTemplate = <<<'PROMPT'
CONTEXTE
Tu es un expert senior en conversion, UX/UI, copywriting immobilier et stratégie locale.
Tu travailles pour {{persona}} sur la zone {{ville}}.

OBJECTIF
{{objectif}}

RÈGLES MARKETING À RESPECTER
- Prioriser la clarté de la promesse et le bénéfice client immédiat.
- Structurer le message : Hook → Valeur → Réassurance → CTA.
- Employer un ton humain, premium et crédible.

RÈGLES SEO À RESPECTER
- Mot-clé principal : {{mot_cle}}
- Niveau de conscience : {{niveau_conscience}}
- Intégrer des variantes sémantiques naturelles sans sur-optimisation.

STYLE VISUEL ATTENDU
- Design sobre, premium, lisible et cohérent avec une marque immobilière locale.
- Hiérarchie visuelle claire (titres, blocs de preuve, CTA).

CONTRAINTES DE DESIGN
- Priorité mobile.
- CTA visible et non agressif.
- Paragraphes courts, titres explicites, scannabilité élevée.

STRUCTURE ATTENDUE
1) Hook d'ouverture orienté problème client
2) Proposition de valeur locale
3) Preuves / réassurance
4) Offre et CTA final

COPYWRITING
- Écrire en français naturel, sans jargon inutile.
- Parler en bénéfices client avant les caractéristiques.
- Rester précis, concret et actionnable.

CONTENU À UTILISER
- Type de contenu : {{type_contenu}}
- Persona : {{persona}}
- Ville : {{ville}}
- Mot-clé : {{mot_cle}}

IMPORTANT
- Ne pas inventer de données ni de promesses non vérifiables.
- Ne laisser aucun placeholder non remplacé.

LIVRABLE ATTENDU
Texte final prêt à publier, structuré avec les sections ci-dessus, sans explication méta.
PROMPT;

$typePromptTemplates = [
    'article' => "{$basePromptTemplate}\n\nSPÉCIFIQUE ARTICLE\n- Prévoir H1, H2, H3, FAQ et méta-description.",
    'secteur' => "{$basePromptTemplate}\n\nSPÉCIFIQUE PAGE SECTEUR\n- Objectif : créer une page secteur immobilière locale destinée à des lecteurs humains et au référencement local Google.\n- Ne jamais inventer de données.\n- Ne jamais donner de chiffres précis non confirmés.\n- Style naturel, professionnel, humain. Éviter le ton robotique et le bourrage SEO.\n- Mettre en valeur la ville et le secteur de façon crédible.\n- Aider à la fois les vendeurs et les acheteurs selon l'intention de la page.\n- Le contenu doit être différencié selon la ville, le secteur, l'intention de la page et les données de recherche locales fournies.\n- Respecter exactement cette structure :\n  1. Hero\n  2. Vue d’ensemble du secteur\n  3. Pourquoi ce secteur attire\n  4. Marché immobilier local\n  5. À qui s’adresse ce secteur\n  6. Vendre dans ce secteur\n  7. Acheter dans ce secteur\n  8. Regard d’expert\n  9. FAQ\n  10. CTA final.",
    'reseaux' => "{$basePromptTemplate}\n\nSPÉCIFIQUE RÉSEAUX SOCIAUX\n- Prévoir hook fort, format court et appel à interaction.",
    'image' => "{$basePromptTemplate}\n\nSPÉCIFIQUE IMAGE IA\n- Détailler cadrage, ambiance, style visuel, palette couleur et ratio.",
    'email' => "{$basePromptTemplate}\n\nSPÉCIFIQUE EMAIL\n- Prévoir objet + pré-header + corps + CTA unique.",
    'seo' => "{$basePromptTemplate}\n\nSPÉCIFIQUE SEO\n- Ajouter intention de recherche, cluster sémantique et maillage interne.",
    'gmb' => "{$basePromptTemplate}\n\nSPÉCIFIQUE GMB\n- Inclure ancrage local, preuve terrain et CTA contact discret.",
];
?>

<div class="mod-hero">
    <div class="mod-hero-content">
        <h1><i class="fas fa-plus"></i> Nouveau prompt</h1>
        <p>Formulaire guidé avec variables autorisées.</p>
    </div>
</div>

<div class="mod-card" style="padding:16px;max-width:920px;">
    <form method="post" action="/admin/dashboard.php?page=ai-prompts">
        <input type="hidden" name="action" value="store">
        <div class="mod-form-grid">
            <div class="mod-form-group"><label>Nom</label><input required name="name"></div>
            <div class="mod-form-group"><label>Type</label><select name="type" id="promptType"><?php foreach ($types as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
            <div class="mod-form-group"><label>Plateforme</label><select name="plateforme"><option value="">Aucune</option><?php foreach ($platforms as $p): ?><option value="<?= $p ?>"><?= ucfirst($p) ?></option><?php endforeach; ?></select></div>
            <div class="mod-form-group"><label><input type="checkbox" name="is_active" value="1" checked> Prompt actif</label></div>
            <div class="mod-form-group full">
                <label>Template (variables autorisées : {{ville}}, {{persona}}, {{objectif}}, {{mot_cle}}, {{niveau_conscience}}, {{type_contenu}})</label>
                <div style="display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-bottom:8px;">
                    <button type="button" class="mod-btn" id="fillTemplateBtn">Charger un template structuré</button>
                </div>
                <textarea name="template" id="promptTemplate" rows="20" required placeholder="CONTEXTE : ... OBJECTIF : ..."><?= htmlspecialchars($basePromptTemplate, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="mod-btn mod-btn-primary" type="submit">Enregistrer</button>
            <a class="mod-btn" href="/admin/dashboard.php?page=ai-prompts">Annuler</a>
        </div>
    </form>
</div>

<script>
(function () {
    const templatesByType = <?= json_encode($typePromptTemplates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const typeField = document.getElementById('promptType');
    const templateField = document.getElementById('promptTemplate');
    const fillBtn = document.getElementById('fillTemplateBtn');

    if (!typeField || !templateField || !fillBtn) return;

    fillBtn.addEventListener('click', function () {
        const selectedType = typeField.value;
        const nextTemplate = templatesByType[selectedType] || templatesByType.article || '';
        templateField.value = nextTemplate;
        templateField.focus();
    });
})();
</script>
