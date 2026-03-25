<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class DataRetentionService
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @param array<int, array{table:string,date_column:string,retention_days:int,action:string,anonymize_columns?:array<int,string>,site_id_column?:string}> $rules
     * @return array<string, int>
     */
    public function run(array $rules, ?int $siteId = null): array
    {
        $summary = ['deleted' => 0, 'anonymized' => 0];

        foreach ($rules as $rule) {
            $table = preg_replace('/[^a-z0-9_]/i', '', $rule['table'] ?? '');
            $dateColumn = preg_replace('/[^a-z0-9_]/i', '', $rule['date_column'] ?? '');
            $retentionDays = max(1, (int) ($rule['retention_days'] ?? 0));
            $action = strtolower(trim((string) ($rule['action'] ?? 'delete')));
            $siteColumn = preg_replace('/[^a-z0-9_]/i', '', $rule['site_id_column'] ?? 'site_id');

            if ($table === '' || $dateColumn === '') {
                continue;
            }

            if ($action === 'anonymize') {
                $summary['anonymized'] += $this->anonymizeExpired($table, $dateColumn, $retentionDays, $rule['anonymize_columns'] ?? ['email'], $siteId, $siteColumn);
                continue;
            }

            $summary['deleted'] += $this->deleteExpired($table, $dateColumn, $retentionDays, $siteId, $siteColumn);
        }

        return $summary;
    }

    private function deleteExpired(string $table, string $dateColumn, int $retentionDays, ?int $siteId, string $siteColumn): int
    {
        $sql = "DELETE FROM {$table} WHERE {$dateColumn} < DATE_SUB(UTC_TIMESTAMP(), INTERVAL :retention_days DAY)";
        $params = [':retention_days' => $retentionDays];

        if ($siteId !== null) {
            $sql .= " AND {$siteColumn} = :site_id";
            $params[':site_id'] = $siteId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    private function anonymizeExpired(string $table, string $dateColumn, int $retentionDays, array $columns, ?int $siteId, string $siteColumn): int
    {
        $safeColumns = array_values(array_filter(array_map(
            static fn ($column): string => preg_replace('/[^a-z0-9_]/i', '', (string) $column),
            $columns
        )));

        if ($safeColumns === []) {
            return 0;
        }

        $sets = [];
        foreach ($safeColumns as $column) {
            $sets[] = "{$column} = NULL";
        }
        $sets[] = 'updated_at = UTC_TIMESTAMP()';

        $sql = "UPDATE {$table} SET " . implode(', ', $sets)
            . " WHERE {$dateColumn} < DATE_SUB(UTC_TIMESTAMP(), INTERVAL :retention_days DAY)";

        $params = [':retention_days' => $retentionDays];

        if ($siteId !== null) {
            $sql .= " AND {$siteColumn} = :site_id";
            $params[':site_id'] = $siteId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }
}
