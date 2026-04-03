(function(){
    const post = async (url, payload) => {
        const body = new URLSearchParams(payload || {});
        const response = await fetch(url, {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body});
        return response.json();
    };

    const keywordForm = document.getElementById('keyword-form');
    if (keywordForm) {
        keywordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = Object.fromEntries(new FormData(keywordForm).entries());
            const result = await post('/modules/seo/ajax/save-keyword.php', formData);
            alert(result.success ? 'Mot-clé enregistré' : (result.error || 'Erreur'));
            if (result.success) location.reload();
        });
    }

    document.querySelectorAll('.check-position').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const result = await post('/modules/seo/ajax/check-position.php', {keyword_id: btn.dataset.id});
            alert(result.success ? `Nouvelle position: ${result.data.position}` : (result.error || 'Erreur'));
            if (result.success) location.reload();
        });
    });

    const ficheVilleForm = document.getElementById('fiche-ville-form');
    if (ficheVilleForm) {
        ficheVilleForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const result = await post('/modules/seo/ajax/save-fiche-ville.php', Object.fromEntries(new FormData(ficheVilleForm).entries()));
            alert(result.success ? 'Fiche ville sauvegardée' : (result.error || 'Erreur'));
            if (result.success) location.reload();
        });
    }

    const generateBtn = document.getElementById('generate-sitemap');
    if (generateBtn) {
        generateBtn.addEventListener('click', async () => {
            const result = await post('/modules/seo/ajax/generate-sitemap.php', {});
            alert(result.success ? `Sitemap généré: ${result.path}` : (result.error || 'Erreur'));
        });
    }

    const auditForm = document.getElementById('audit-form');
    if (auditForm) {
        auditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const result = await post('/modules/seo/ajax/run-audit.php', Object.fromEntries(new FormData(auditForm).entries()));
            alert(result.success ? `Audit SEO: ${result.data.score_seo}/100` : (result.error || 'Erreur'));
            if (result.success) location.reload();
        });
    }
})();
