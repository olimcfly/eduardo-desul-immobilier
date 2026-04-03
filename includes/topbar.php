<?php
$authUser = Auth::user();
if (!$authUser) {
    return;
}

$role = (string) ($authUser['role'] ?? '');
if (!in_array($role, ['admin', 'superadmin'], true) && $role !== 'user') {
    return;
}

$displayName = trim((string) ($authUser['name'] ?? '')) ?: (string) ($authUser['email'] ?? 'Compte');
$dashboardHref = $role === 'superadmin' ? '/admin?module=superadmin' : '/admin';
$logoutHref = '/admin/logout';

if (in_array($role, ['admin', 'superadmin'], true)):
?>
    <div class="role-topbar <?= $role === 'superadmin' ? 'topbar-superadmin' : 'topbar-admin' ?>">
        <?= $role === 'superadmin' ? '⭐ Compte Super Administrateur' : '🔧 Compte Administrateur' ?> — <?= htmlspecialchars($displayName) ?>
        &nbsp;|&nbsp;
        <a href="<?= htmlspecialchars($dashboardHref) ?>">Dashboard</a>
        &nbsp;|&nbsp;
        <a href="<?= htmlspecialchars($logoutHref) ?>">Déconnexion</a>
    </div>
<?php elseif ($role === 'user'): ?>
    <div id="session-access-modal" class="session-access-modal" hidden>
        <div class="session-access-modal__card">
            <h3>Demande d'accès administrateur</h3>
            <p>Un administrateur souhaite accéder à votre session. Autoriser ?</p>
            <div class="session-access-modal__actions">
                <button type="button" data-decision="allowed">Autoriser</button>
                <button type="button" data-decision="denied" class="danger">Refuser</button>
            </div>
        </div>
    </div>
    <script>
    (function () {
        let currentRequestId = null;
        const modal = document.getElementById('session-access-modal');
        if (!modal) return;

        function sendDecision(decision) {
            if (!currentRequestId) return;
            const body = new URLSearchParams();
            body.set('request_id', String(currentRequestId));
            body.set('decision', decision);

            fetch('/admin?module=superadmin&action=respond_request', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            }).finally(function () {
                modal.setAttribute('hidden', 'hidden');
                currentRequestId = null;
            });
        }

        modal.querySelectorAll('button[data-decision]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                sendDecision(this.getAttribute('data-decision'));
            });
        });

        function pollRequests() {
            fetch('/admin?module=superadmin&action=poll_request', {headers: {'Accept': 'application/json'}})
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    if (!json.ok || !json.request) return;
                    if (json.request.status !== 'pending') return;
                    if (currentRequestId && currentRequestId === Number(json.request.id)) return;

                    currentRequestId = Number(json.request.id);
                    modal.removeAttribute('hidden');
                })
                .catch(function () {});
        }

        pollRequests();
        setInterval(pollRequests, 5000);
    })();
    </script>
<?php endif; ?>
