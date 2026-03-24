<?php
/**
 * API Handler: local-onboarding
 * Called via: /admin/api/router.php?module=local-onboarding&action=...
 */

require_once __DIR__ . '/_tenant.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = CURRENT_ACTION;

if (!function_exists('localOnboardingResponse')) {
    function localOnboardingResponse(int $statusCode, bool $success, string $message, array $extra = []): void
    {
        http_response_code($statusCode);
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
        ], $extra));
    }
}

if (!function_exists('isValidCountryCode')) {
    function isValidCountryCode(string $countryCode): bool
    {
        return (bool)preg_match('/^[A-Z]{2}$/', $countryCode);
    }
}

if (!function_exists('sanitizeDistricts')) {
    /**
     * @return string[]
     */
    function sanitizeDistricts(array $districts): array
    {
        $clean = [];
        foreach ($districts as $districtName) {
            $districtName = trim((string)$districtName);
            if ($districtName === '') {
                continue;
            }
            $clean[$districtName] = true;
        }

        return array_keys($clean);
    }
}

try {
    $tenantContext = requireTenantContext($pdo);
    $tenantId = (int)$tenantContext['tenant_id'];
    $role = (string)$tenantContext['membership_role'];
} catch (InvalidArgumentException $e) {
    localOnboardingResponse(400, false, $e->getMessage());
    return;
} catch (Throwable $e) {
    localOnboardingResponse(403, false, $e->getMessage());
    return;
}

switch ($action) {
    case 'list':
        try {
            $stmt = $pdo->prepare("SELECT id, city_name, country_code, activity_label, persona_summary, status, created_by, created_at, updated_at
                                   FROM local_profiles
                                   WHERE tenant_id = ?
                                   ORDER BY updated_at DESC, id DESC");
            $stmt->execute([$tenantId]);
            localOnboardingResponse(200, true, 'OK', ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            localOnboardingResponse(500, false, 'Erreur SQL lors de la récupération des profils');
        }
        break;

    case 'get':
        try {
            $id = (int)($input['id'] ?? $_GET['id'] ?? 0);
            if ($id <= 0) {
                localOnboardingResponse(400, false, 'ID requis');
                break;
            }

            $stmt = $pdo->prepare("SELECT id, tenant_id, city_name, country_code, activity_label, persona_summary, goals_json, status, created_by, created_at, updated_at
                                   FROM local_profiles
                                   WHERE id = ? AND tenant_id = ?
                                   LIMIT 1");
            $stmt->execute([$id, $tenantId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                localOnboardingResponse(404, false, 'Profil local introuvable');
                break;
            }

            $districtStmt = $pdo->prepare("SELECT district_name
                                           FROM local_profile_districts
                                           WHERE tenant_id = ? AND local_profile_id = ?
                                           ORDER BY district_name ASC");
            $districtStmt->execute([$tenantId, $id]);
            $profile['districts'] = $districtStmt->fetchAll(PDO::FETCH_COLUMN);

            localOnboardingResponse(200, true, 'OK', ['data' => $profile]);
        } catch (PDOException $e) {
            localOnboardingResponse(500, false, 'Erreur SQL lors de la récupération du profil');
        }
        break;

    case 'create':
        if (!tenantCanWrite($role)) {
            localOnboardingResponse(403, false, 'Permissions insuffisantes');
            break;
        }

        try {
            $cityName = trim((string)($input['city_name'] ?? ''));
            $countryCode = strtoupper(trim((string)($input['country_code'] ?? 'FR')));
            $activityLabel = trim((string)($input['activity_label'] ?? ''));
            $personaSummary = trim((string)($input['persona_summary'] ?? ''));
            $goals = $input['goals_json'] ?? [];
            $status = trim((string)($input['status'] ?? 'draft'));

            if ($cityName === '' || $activityLabel === '' || $personaSummary === '') {
                localOnboardingResponse(422, false, 'city_name, activity_label et persona_summary sont requis');
                break;
            }

            if (mb_strlen($cityName) > 190 || mb_strlen($activityLabel) > 190) {
                localOnboardingResponse(422, false, 'city_name/activity_label dépassent la longueur maximale');
                break;
            }

            if (!isValidCountryCode($countryCode)) {
                localOnboardingResponse(422, false, 'country_code invalide (format ISO-2 attendu)');
                break;
            }

            if (!in_array($status, ['draft', 'active', 'archived'], true)) {
                localOnboardingResponse(422, false, 'status invalide');
                break;
            }

            if (!is_array($goals)) {
                localOnboardingResponse(422, false, 'goals_json doit être un tableau JSON');
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO local_profiles
                (tenant_id, city_name, country_code, activity_label, persona_summary, goals_json, status, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $tenantId,
                $cityName,
                $countryCode,
                $activityLabel,
                $personaSummary,
                json_encode($goals, JSON_UNESCAPED_UNICODE),
                $status,
                (int)($_SESSION['admin_id'] ?? 0),
            ]);

            $profileId = (int)$pdo->lastInsertId();
            createTenantAuditLog($pdo, $tenantId, 'local_profile.created', 'local_profile', $profileId, [
                'city_name' => $cityName,
                'country_code' => $countryCode,
                'status' => $status,
            ]);

            localOnboardingResponse(201, true, 'Profil local créé', ['id' => $profileId]);
        } catch (PDOException $e) {
            localOnboardingResponse(500, false, 'Erreur SQL lors de la création du profil');
        }
        break;

    case 'update':
        if (!tenantCanWrite($role)) {
            localOnboardingResponse(403, false, 'Permissions insuffisantes');
            break;
        }

        try {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) {
                localOnboardingResponse(400, false, 'ID requis');
                break;
            }

            $existsStmt = $pdo->prepare("SELECT id FROM local_profiles WHERE id = ? AND tenant_id = ? LIMIT 1");
            $existsStmt->execute([$id, $tenantId]);
            if (!$existsStmt->fetchColumn()) {
                localOnboardingResponse(404, false, 'Profil local introuvable');
                break;
            }

            $allowed = ['city_name', 'country_code', 'activity_label', 'persona_summary', 'goals_json', 'status'];
            $sets = [];
            $params = [];

            foreach ($allowed as $field) {
                if (!array_key_exists($field, $input)) {
                    continue;
                }

                $value = $input[$field];
                if ($field === 'country_code') {
                    $value = strtoupper(trim((string)$value));
                    if (!isValidCountryCode($value)) {
                        localOnboardingResponse(422, false, 'country_code invalide (format ISO-2 attendu)');
                        return;
                    }
                } elseif ($field === 'goals_json') {
                    if (!is_array($value)) {
                        localOnboardingResponse(422, false, 'goals_json doit être un tableau JSON');
                        return;
                    }
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                } elseif (is_string($value)) {
                    $value = trim($value);
                }

                if (($field === 'city_name' || $field === 'activity_label') && mb_strlen((string)$value) > 190) {
                    localOnboardingResponse(422, false, $field . ' dépasse la longueur maximale');
                    return;
                }

                if ($field === 'status' && !in_array($value, ['draft', 'active', 'archived'], true)) {
                    localOnboardingResponse(422, false, 'status invalide');
                    return;
                }

                $sets[] = "{$field} = ?";
                $params[] = $value;
            }

            if (empty($sets)) {
                localOnboardingResponse(400, false, 'Aucun champ à mettre à jour');
                break;
            }

            $sets[] = 'updated_at = NOW()';
            $params[] = $id;
            $params[] = $tenantId;

            $stmt = $pdo->prepare("UPDATE local_profiles
                                   SET " . implode(', ', $sets) . "
                                   WHERE id = ? AND tenant_id = ?");
            $stmt->execute($params);

            createTenantAuditLog($pdo, $tenantId, 'local_profile.updated', 'local_profile', $id, $input);
            localOnboardingResponse(200, true, 'Profil local mis à jour');
        } catch (PDOException $e) {
            localOnboardingResponse(500, false, 'Erreur SQL lors de la mise à jour du profil');
        }
        break;

    case 'set-districts':
        if (!tenantCanWrite($role)) {
            localOnboardingResponse(403, false, 'Permissions insuffisantes');
            break;
        }

        try {
            $localProfileId = (int)($input['local_profile_id'] ?? 0);
            $districts = $input['districts'] ?? [];

            if ($localProfileId <= 0) {
                localOnboardingResponse(400, false, 'local_profile_id requis');
                break;
            }

            if (!is_array($districts)) {
                localOnboardingResponse(422, false, 'districts doit être un tableau');
                break;
            }

            $cleanDistricts = sanitizeDistricts($districts);
            if (count($cleanDistricts) > 200) {
                localOnboardingResponse(422, false, 'Trop de quartiers (max 200)');
                break;
            }

            $existsStmt = $pdo->prepare("SELECT id FROM local_profiles WHERE id = ? AND tenant_id = ? LIMIT 1");
            $existsStmt->execute([$localProfileId, $tenantId]);
            if (!$existsStmt->fetchColumn()) {
                localOnboardingResponse(404, false, 'Profil local introuvable');
                break;
            }

            $pdo->beginTransaction();

            $deleteStmt = $pdo->prepare("DELETE FROM local_profile_districts WHERE tenant_id = ? AND local_profile_id = ?");
            $deleteStmt->execute([$tenantId, $localProfileId]);

            $insertStmt = $pdo->prepare("INSERT INTO local_profile_districts (tenant_id, local_profile_id, district_name, created_at)
                                         VALUES (?, ?, ?, NOW())");

            foreach ($cleanDistricts as $districtName) {
                $insertStmt->execute([$tenantId, $localProfileId, $districtName]);
            }

            $pdo->commit();
            createTenantAuditLog($pdo, $tenantId, 'local_profile.districts.updated', 'local_profile', $localProfileId, [
                'districts_count' => count($cleanDistricts),
            ]);

            localOnboardingResponse(200, true, 'Quartiers mis à jour', ['districts_count' => count($cleanDistricts)]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            localOnboardingResponse(500, false, 'Erreur lors de la mise à jour des quartiers');
        }
        break;

    default:
        localOnboardingResponse(400, false, 'Action non supportée');
        break;
}
