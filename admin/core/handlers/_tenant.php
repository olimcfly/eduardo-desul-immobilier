<?php
/**
 * Tenant helpers (strict multi-tenant guard for admin API handlers).
 */

if (!function_exists('resolveTenantIdFromRequest')) {
    function resolveTenantIdFromRequest(): int
    {
        $tenantId = $_GET['tenant_id'] ?? $_POST['tenant_id'] ?? $_REQUEST['tenant_id'] ?? $_SERVER['HTTP_X_TENANT_ID'] ?? null;
        if ($tenantId === null || $tenantId === '') {
            throw new InvalidArgumentException('tenant_id manquant (query, body ou X-Tenant-Id)');
        }

        if (!ctype_digit((string)$tenantId)) {
            throw new InvalidArgumentException('tenant_id invalide');
        }

        return (int)$tenantId;
    }
}

if (!function_exists('requireTenantContext')) {
    /**
     * @return array{tenant_id:int,membership_role:string}
     */
    function requireTenantContext(PDO $pdo): array
    {
        $tenantId = resolveTenantIdFromRequest();
        $adminId = (int)($_SESSION['admin_id'] ?? 0);

        if ($adminId <= 0) {
            throw new RuntimeException('Session admin invalide');
        }

        $stmt = $pdo->prepare(
            "SELECT tm.role
             FROM admin_tenant_memberships tm
             INNER JOIN tenants t ON t.id = tm.tenant_id
             WHERE tm.tenant_id = ?
               AND tm.admin_id = ?
               AND tm.is_active = 1
               AND t.status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$tenantId, $adminId]);
        $membership = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$membership) {
            throw new RuntimeException('Accès interdit: aucun accès au tenant demandé');
        }

        return [
            'tenant_id' => $tenantId,
            'membership_role' => $membership['role'],
        ];
    }
}

if (!function_exists('tenantCanWrite')) {
    function tenantCanWrite(string $role): bool
    {
        return in_array($role, ['owner', 'editor'], true);
    }
}

if (!function_exists('createTenantAuditLog')) {
    function createTenantAuditLog(PDO $pdo, int $tenantId, string $action, string $entityType, int $entityId, array $after = []): void
    {
        $actorUserId = (int)($_SESSION['admin_id'] ?? 0);

        $stmt = $pdo->prepare(
            "INSERT INTO audit_logs (tenant_id, actor_user_id, action, entity_type, entity_id, before_json, after_json, created_at)
             VALUES (?, ?, ?, ?, ?, NULL, ?, NOW())"
        );
        $stmt->execute([
            $tenantId,
            $actorUserId,
            $action,
            $entityType,
            $entityId,
            json_encode($after, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
