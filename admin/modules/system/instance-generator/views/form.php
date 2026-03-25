<?php
$flash = $_SESSION['instance_generator_flash'] ?? null;
unset($_SESSION['instance_generator_flash']);
?>
<div class="card">
    <div class="card-hd" style="display:flex;justify-content:space-between;align-items:center;">
        <h3><?= $formAction === 'store' ? 'Créer une instance' : 'Modifier une instance' ?></h3>
        <a class="btn btn-s btn-sm" href="/admin/dashboard.php?page=instance-generator">Retour liste</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success' ?>" style="margin-bottom:12px;">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom:16px;border:1px dashed #cbd5e1;border-radius:8px;padding:10px;background:#f8fafc;">
        <strong>Pré-remplissage intelligent (coller texte ou importer fichier)</strong>
        <p style="margin:8px 0;font-size:12px;color:#475569;">
            Format conseillé : <code>clé = valeur</code> (ex: <code>client_name = Pascal Hamm</code>).
        </p>
        <textarea id="instance-intake-text" rows="8" style="width:100%;margin-bottom:8px;" placeholder="Collez ici votre brief client, ou importez un fichier .txt/.md/.json"></textarea>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <input type="file" id="instance-intake-file" accept=".txt,.md,.json,.env">
            <button type="button" id="instance-intake-parse" class="btn btn-s btn-sm">Analyser & préremplir</button>
        </div>
        <div id="instance-intake-feedback" style="font-size:12px;color:#334155;margin-top:6px;"></div>
    </div>

    <form method="post" action="/admin/dashboard.php?page=instance-generator" style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px;" id="instance-form">
        <input type="hidden" name="action" value="<?= htmlspecialchars($formAction) ?>">
        <?php if ($formAction === 'update'): ?>
            <input type="hidden" name="id" value="<?= (int) $instance['id'] ?>">
        <?php endif; ?>

        <?php
        $fields = [
            'client_name','business_name','domain','city','admin_email','admin_password_temp',
            'db_host','db_port','db_name','db_user','db_pass',
            'smtp_host','smtp_port','smtp_user','smtp_pass','smtp_encryption','from_email',
            'openai_api_key','perplexity_api_key','logo_path'
        ];
        foreach ($fields as $field):
        ?>
            <label style="display:flex;flex-direction:column;font-size:13px;gap:4px;">
                <span><?= htmlspecialchars($field) ?></span>
                <input
                    type="<?= str_contains($field, 'email') ? 'email' : (str_contains($field, 'password') || str_ends_with($field, '_pass') || str_contains($field, 'api_key') ? 'password' : 'text') ?>"
                    name="<?= htmlspecialchars($field) ?>"
                    value="<?= htmlspecialchars((string) ($instance[$field] ?? '')) ?>"
                    <?= in_array($field, ['client_name','business_name','domain','city','admin_email','admin_password_temp','db_host','db_port','db_name','db_user','db_pass'], true) ? 'required' : '' ?>
                >
            </label>
        <?php endforeach; ?>

        <label style="display:flex;flex-direction:column;font-size:13px;gap:4px;">
            <span>status</span>
            <select name="status">
                <?php foreach (ClientInstance::STATUSES as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= ($instance['status'] ?? 'draft') === $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div style="grid-column:1/-1;display:flex;gap:10px;">
            <button class="btn btn-p" type="submit">Enregistrer</button>
            <a class="btn btn-s" href="/admin/dashboard.php?page=instance-generator">Annuler</a>
        </div>
    </form>
</div>

<script>
(() => {
    const parseBtn = document.getElementById('instance-intake-parse');
    const textArea = document.getElementById('instance-intake-text');
    const fileInput = document.getElementById('instance-intake-file');
    const feedback = document.getElementById('instance-intake-feedback');
    const form = document.getElementById('instance-form');

    if (!parseBtn || !textArea || !fileInput || !feedback || !form) {
        return;
    }

    const readFileText = (file) => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result || ''));
        reader.onerror = () => reject(new Error('Lecture du fichier impossible.'));
        reader.readAsText(file);
    });

    const applyFields = (fields) => {
        let count = 0;
        Object.entries(fields || {}).forEach(([key, value]) => {
            const input = form.querySelector(`[name="${key}"]`);
            if (!input) return;
            input.value = value ?? '';
            count += 1;
        });
        return count;
    };

    parseBtn.addEventListener('click', async () => {
        try {
            parseBtn.disabled = true;
            feedback.textContent = 'Analyse en cours...';

            let rawText = textArea.value.trim();
            const file = fileInput.files && fileInput.files[0];
            if (file) {
                rawText = await readFileText(file);
                textArea.value = rawText;
            }

            if (!rawText) {
                feedback.textContent = 'Ajoutez un texte ou importez un fichier.';
                return;
            }

            const body = new URLSearchParams({
                action: 'parse_intake',
                raw_text: rawText
            });

            const response = await fetch('/admin/dashboard.php?page=instance-generator', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString()
            });

            const data = await response.json();
            const filled = applyFields(data.fields || {});
            const warnings = Array.isArray(data.warnings) && data.warnings.length > 0
                ? ` | ⚠️ ${data.warnings.join(' ')}`
                : '';

            feedback.textContent = `${filled} champ(s) prérempli(s)${warnings}`;
        } catch (err) {
            feedback.textContent = 'Erreur de parsing: ' + (err?.message || 'inconnue');
        } finally {
            parseBtn.disabled = false;
        }
    });
})();
</script>
