(function () {
    'use strict';

    async function postForm(url, formData) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        return response.json();
    }

    document.addEventListener('submit', async function (event) {
        const form = event.target;
        if (form.id === 'gmb-fiche-form') {
            event.preventDefault();
            const data = await postForm('/modules/gmb/ajax/save-fiche.php', new FormData(form));
            alert(data.message || 'Fiche sauvegardée.');
        }

        if (form.id === 'gmb-demande-form') {
            event.preventDefault();
            const data = await postForm('/modules/gmb/ajax/send-demande.php', new FormData(form));
            alert(data.message || 'Demande envoyée.');
        }

        if (form.id === 'gmb-template-form') {
            event.preventDefault();
            const data = await postForm('/modules/gmb/ajax/save-template.php', new FormData(form));
            alert(data.message || 'Template sauvegardé.');
        }
    });

    document.addEventListener('click', async function (event) {
        const btn = event.target.closest('[data-action]');
        if (!btn) return;

        if (btn.dataset.action === 'sync-fiche') {
            const data = await postForm('/modules/gmb/ajax/sync-fiche.php', new FormData());
            alert(data.message || 'Synchronisation terminée.');
            location.reload();
        }

        if (btn.dataset.action === 'get-avis') {
            const data = await postForm('/modules/gmb/ajax/get-avis.php', new FormData());
            alert(data.message || 'Avis synchronisés.');
            location.reload();
        }

        if (btn.dataset.action === 'reply-avis') {
            const container = btn.closest('.gmb-avis-item');
            const avisId = container ? container.dataset.avisId : null;
            const reply = container ? container.querySelector('.reply-input').value : '';
            const fd = new FormData();
            fd.append('avis_id', avisId || '');
            fd.append('reponse', reply || '');
            const data = await postForm('/modules/gmb/ajax/reply-avis.php', fd);
            alert(data.message || 'Réponse publiée.');
        }

        if (btn.dataset.action === 'get-stats') {
            const data = await postForm('/modules/gmb/ajax/get-stats.php', new FormData());
            if (!data.success || !data.stats) {
                alert(data.message || 'Erreur stats.');
                return;
            }
            Object.keys(data.stats).forEach(function (key) {
                const node = document.querySelector('[data-stat="' + key + '"]');
                if (node) node.textContent = data.stats[key];
            });
        }
    });
})();
