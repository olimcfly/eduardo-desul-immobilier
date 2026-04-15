(function () {
    'use strict';

    const statusLabels = {
        pending: 'En attente',
        running: 'En cours',
        done: 'Terminé',
        error: 'Erreur',
    };

    async function postForm(url, formData) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        return response.json();
    }

    function syncBadgeClass(status) {
        return 'gmb-sync-' + (status || 'pending');
    }

    function renderSyncStatus(job) {
        const statusNode = document.querySelector('[data-gmb-sync-status]');
        const jobNode = document.querySelector('[data-gmb-sync-job]');
        const updatedNode = document.querySelector('[data-gmb-sync-updated]');
        const errorNode = document.querySelector('[data-gmb-sync-error]');

        if (!statusNode) return;

        const status = (job && job.status) ? job.status : 'pending';
        statusNode.textContent = statusLabels[status] || status;
        statusNode.className = 'gmb-sync-badge ' + syncBadgeClass(status);

        if (jobNode) {
            jobNode.textContent = job && job.id ? String(job.id) : '-';
        }

        if (updatedNode) {
            updatedNode.textContent = (job && job.updated_at) ? job.updated_at : 'Jamais';
        }

        if (errorNode) {
            errorNode.textContent = status === 'error' ? ((job && job.error_message) || 'Erreur inconnue') : '';
        }
    }

    async function fetchSyncStatus() {
        const response = await fetch('/admin/api/gmb/sync', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        if (data && data.success && data.job) {
            renderSyncStatus(data.job);
            return data.job;
        }
        return null;
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
            const data = await postForm('/admin/api/gmb/sync', new FormData());
            if (!data.success) {
                alert(data.message || 'Impossible de lancer la synchronisation.');
                return;
            }

            alert(data.message || 'Synchronisation mise en file d\'attente.');
            await fetchSyncStatus();
            return;
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

    if (document.querySelector('[data-gmb-sync-status]')) {
        fetchSyncStatus();
        setInterval(fetchSyncStatus, 5000);
    }
})();
