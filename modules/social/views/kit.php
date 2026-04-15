<?php
$kitVilles = ['Bordeaux', 'Aix-en-Provence', 'Nantes', 'Lyon', 'Toulouse', 'Montpellier', 'Rennes', 'Strasbourg'];
$kitPersonas = [
    'primo' => [
        'label' => 'Primo-Accédant',
        'emoji' => '🛡️',
        'levier' => 'Sécurité',
        'hook' => "Vous payez un loyer à {{ville}} depuis 5 ans… et vous n'avez rien en retour. 😔",
        'tiktok' => "Honnêtement… chaque mois de loyer à {{ville}}, c'est de l'argent qui part sans construire votre patrimoine.\n\nJ'accompagne des primo-accédants à {{ville}} qui pensaient que ce n'était pas le bon moment. Et pourtant, avec une bonne méthode, c'est souvent possible plus tôt qu'on ne le croit.",
        'facebook' => "📍 {{ville}} — Vous louez depuis plusieurs années ?\n\nLe frein n°1 que je vois chez les couples que j'accompagne : le manque d'information, pas le budget.\n\nDites-moi en commentaire où vous en êtes 👇",
        'hashtags' => ['#PremierAchat', '#PrimoAccédant', '#AchatImmobilier', '#Immo{{ville_tag}}', '#{{ville_tag}}Immobilier'],
    ],
    'famille' => [
        'label' => 'Famille en Expansion',
        'emoji' => '🎯',
        'levier' => 'Confort & Avenir',
        'hook' => 'Votre logement à {{ville}} est devenu trop petit… mais vous ne savez pas par où commencer ? 🏡',
        'tiktok' => "Quand la famille grandit, chaque m² compte.\n\nÀ {{ville}}, j'aide des familles à clarifier leur projet pour trouver plus grand sans perdre du temps dans des visites inutiles.",
        'facebook' => "🏡 Vous cherchez plus grand à {{ville}} ?\n\nJe vous aide à cadrer le projet, choisir les bons quartiers et éviter les pièges du marché local.",
        'hashtags' => ['#VieEnFamille', '#MaisonFamiliale', '#AchatMaison', '#Famille{{ville_tag}}', '#{{ville_tag}}Maison'],
    ],
    'vendeur' => [
        'label' => 'Vendeur Pressé',
        'emoji' => '🏷️',
        'levier' => 'Rapidité & Prix Juste',
        'hook' => 'Vous devez vendre à {{ville}} rapidement… sans brader votre prix ? ⏱️',
        'tiktok' => "Vendre vite ne veut pas dire vendre moins cher.\n\nAvec une estimation juste, une mise en valeur propre et une diffusion ciblée, on sécurise vitesse + prix à {{ville}}.",
        'facebook' => "⏱️ Vendre rapidement à {{ville}} — c'est possible.\n\nTout se joue sur 3 points : estimation, présentation du bien, et ciblage des acheteurs actifs.",
        'hashtags' => ['#VendreSonBien', '#EstimationGratuite', '#MandatVente', '#Vente{{ville_tag}}', '#{{ville_tag}}Vente'],
    ],
];
?>
<div class="social-page" style="max-width:1100px;margin:0 auto;">
    <div class="breadcrumb"><a href="/admin/">Accueil</a> &gt; <a href="/admin/?module=social">Social</a> &gt; Kit publications</div>
    <h1>🧰 Kit Publications (version PHP)</h1>
    <p style="color:#64748b;margin-top:4px;">Générateur rapide de hooks, scripts et hashtags par persona et par ville.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:14px 0;">
        <label style="display:flex;flex-direction:column;gap:6px;font-size:13px;">
            Ville ciblée
            <select id="kit-ville" style="padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
                <?php foreach ($kitVilles as $ville): ?>
                    <option value="<?= e($ville) ?>" <?= $ville === 'Aix-en-Provence' ? 'selected' : '' ?>><?= e($ville) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label style="display:flex;flex-direction:column;gap:6px;font-size:13px;">
            Persona
            <select id="kit-persona" style="padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
                <?php foreach ($kitPersonas as $id => $persona): ?>
                    <option value="<?= e($id) ?>"><?= e($persona['emoji'] . ' ' . $persona['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <div style="display:grid;grid-template-columns:1fr;gap:10px;">
        <article style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
            <h3 style="margin:0 0 8px;font-size:15px;">⚡ Hook TikTok</h3>
            <pre id="kit-hook" style="margin:0;white-space:pre-wrap;background:#fff7ed;border:1px solid #fed7aa;padding:10px;border-radius:8px;"></pre>
        </article>

        <article style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
            <h3 style="margin:0 0 8px;font-size:15px;">🎥 Script TikTok (court)</h3>
            <pre id="kit-tiktok" style="margin:0;white-space:pre-wrap;background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:8px;"></pre>
        </article>

        <article style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
            <h3 style="margin:0 0 8px;font-size:15px;">📘 Post Facebook</h3>
            <pre id="kit-facebook" style="margin:0;white-space:pre-wrap;background:#eff6ff;border:1px solid #bfdbfe;padding:10px;border-radius:8px;"></pre>
        </article>

        <article style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
            <h3 style="margin:0 0 8px;font-size:15px;">🏷️ Hashtags conseillés</h3>
            <div id="kit-hashtags" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
        </article>
    </div>
</div>

<script>
(() => {
  const personas = <?= json_encode($kitPersonas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const $ville = document.getElementById('kit-ville');
  const $persona = document.getElementById('kit-persona');

  const $hook = document.getElementById('kit-hook');
  const $tiktok = document.getElementById('kit-tiktok');
  const $facebook = document.getElementById('kit-facebook');
  const $hashtags = document.getElementById('kit-hashtags');

  const renderTemplate = (text, ville) => {
    const villeTag = ville.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z]/g, '');
    return String(text)
      .replaceAll('{{ville}}', ville)
      .replaceAll('{{ville_tag}}', villeTag);
  };

  const update = () => {
    const ville = $ville.value;
    const persona = personas[$persona.value];
    if (!persona) return;

    $hook.textContent = renderTemplate(persona.hook, ville);
    $tiktok.textContent = renderTemplate(persona.tiktok, ville);
    $facebook.textContent = renderTemplate(persona.facebook, ville);

    $hashtags.innerHTML = '';
    persona.hashtags.map(tag => renderTemplate(tag, ville)).forEach(tag => {
      const span = document.createElement('span');
      span.textContent = tag;
      span.style.cssText = 'background:#f1f5f9;border:1px solid #cbd5e1;color:#334155;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:600;';
      $hashtags.appendChild(span);
    });
  };

  $ville.addEventListener('change', update);
  $persona.addEventListener('change', update);
  update();
})();
</script>
