<section class="est-card">
  <h2>Résultat estimation</h2>
  <p>Fourchette: <strong><?= number_format((float)$resultPayload['result']['estimate_min'], 0, ',', ' ') ?>€</strong> à <strong><?= number_format((float)$resultPayload['result']['estimate_max'], 0, ',', ' ') ?>€</strong></p>
  <p>Cible: <?= number_format((float)$resultPayload['result']['estimate_target'], 0, ',', ' ') ?>€</p>
</section>
