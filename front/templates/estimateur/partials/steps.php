<section class="est-card">
  <h2>Formulaire multi-étapes</h2>
  <?php if ($errors): ?><div class="est-errors"><?= htmlspecialchars(implode(' | ', $errors)) ?></div><?php endif; ?>
  <form method="post" id="estimateur-form">
    <input type="hidden" name="config_id" value="<?= (int)($cfg['id'] ?? 0) ?>">
    <input type="hidden" name="city_slug" value="<?= htmlspecialchars($cfg['city_slug'] ?? '') ?>">

    <div class="est-grid">
      <label>Mode
        <select name="mode">
          <option value="quick">quick</option>
          <option value="advanced">advanced</option>
        </select>
      </label>
      <label>Type de bien
        <select name="property_type" required>
          <option value="appartement">Appartement</option>
          <option value="maison">Maison</option>
          <option value="immeuble">Immeuble</option>
        </select>
      </label>
      <label>Zone / quartier
        <select name="zone_code">
          <?php foreach ($zones as $z): ?>
            <option value="<?= htmlspecialchars($z['zone_code']) ?>"><?= htmlspecialchars($z['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Surface (m²)<input type="number" name="surface_m2" min="1" required></label>
      <label>Pièces<input type="number" name="rooms" min="1"></label>
      <label>Email<input type="email" name="contact_email" required></label>
    </div>
    <button type="submit">Calculer mon estimation</button>
  </form>
</section>
