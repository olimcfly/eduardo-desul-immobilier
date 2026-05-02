(function () {
    'use strict';

    async function postForm(url, formData) {
        if (!formData.has('csrf_token') && window.GMB_CSRF_TOKEN) {
            formData.append('csrf_token', window.GMB_CSRF_TOKEN);
        }

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });

        let data = {};
        try {
            data = await response.json();
        } catch (error) {
            data = { success: false, message: 'Réponse serveur invalide.', data: {} };
        }

        if (!response.ok && data.success !== false) {
            data.success = false;
            data.message = data.message || 'Erreur serveur.';
        }

        return data;
    }

    function updateStats(stats) {
        if (!stats) return;
        Object.keys(stats).forEach(function (key) {
            const node = document.querySelector('[data-stat="' + key + '"]');
            if (node) node.textContent = stats[key];
        });
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

        if (form.id === 'gmb-stats-form') {
            event.preventDefault();
            const fd = new FormData(form);
            fd.append('action', 'save');
            const data = await postForm('/modules/gmb/ajax/get-stats.php', fd);
            if (data.success) {
                updateStats(data.data && data.data.stats);
            }
            alert(data.message || 'Statistiques enregistrées.');
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
            if (!data.success) {
                alert(data.message || 'Erreur stats.');
                return;
            }
            updateStats(data.data && data.data.stats);
            alert(data.message || 'Dernières données affichées.');
        }
    });
})();
