<?php

class DvfImportService
{
    public static function importCsv(array $file): array
    {
        DvfEstimatorService::ensureTables();

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Fichier invalide.'];
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['ok' => false, 'message' => 'Upload non reconnu.'];
        }

        $sourceName = basename((string) ($file['name'] ?? 'import.csv'));
        $runId = self::startRun($sourceName);

        $handle = fopen($tmpPath, 'r');
        if ($handle === false) {
            self::finishRun($runId, 'failed', 0, 0, 0, 1, 'Impossible de lire le fichier.');
            return ['ok' => false, 'message' => 'Impossible de lire le CSV.'];
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            self::finishRun($runId, 'failed', 0, 0, 0, 1, 'CSV vide.');
            return ['ok' => false, 'message' => 'CSV vide.'];
        }

        $map = self::buildHeaderMap($header);
        $rowsRead = 0;
        $inserted = 0;
        $updated = 0;
        $rejected = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowsRead++;
            $payload = self::extractRow($row, $map, $sourceName);
            if (!$payload) {
                $rejected++;
                continue;
            }

            $result = self::upsertTransaction($payload);
            if ($result === 'inserted') {
                $inserted++;
            } elseif ($result === 'updated') {
                $updated++;
            } else {
                $rejected++;
            }
        }

        fclose($handle);
        self::finishRun($runId, 'success', $rowsRead, $inserted, $updated, $rejected, null);

        return [
            'ok' => true,
            'message' => 'Import DVF terminé.',
            'rows_read' => $rowsRead,
            'rows_inserted' => $inserted,
            'rows_updated' => $updated,
            'rows_rejected' => $rejected,
        ];
    }

    private static function startRun(string $sourceName): int
    {
        $stmt = db()->prepare('INSERT INTO dvf_import_runs (source_file, status, started_at) VALUES (:source_file, "running", NOW())');
        $stmt->execute([':source_file' => $sourceName]);
        return (int) db()->lastInsertId();
    }

    private static function finishRun(int $runId, string $status, int $rowsRead, int $inserted, int $updated, int $rejected, ?string $error): void
    {
        $stmt = db()->prepare('UPDATE dvf_import_runs
            SET status = :status,
                rows_read = :rows_read,
                rows_inserted = :rows_inserted,
                rows_updated = :rows_updated,
                rows_rejected = :rows_rejected,
                error_log = :error_log,
                finished_at = NOW()
            WHERE id = :id');

        $stmt->execute([
            ':status' => $status,
            ':rows_read' => $rowsRead,
            ':rows_inserted' => $inserted,
            ':rows_updated' => $updated,
            ':rows_rejected' => $rejected,
            ':error_log' => $error,
            ':id' => $runId,
        ]);
    }

    private static function buildHeaderMap(array $header): array
    {
        $normalized = [];
        foreach ($header as $idx => $name) {
            $key = strtolower(trim((string) $name));
            $normalized[$key] = $idx;
        }
        return $normalized;
    }

    private static function extractRow(array $row, array $map, string $sourceName): ?array
    {
        $get = static function (string $key) use ($map, $row): string {
            $idx = $map[strtolower($key)] ?? null;
            if ($idx === null) {
                return '';
            }
            return trim((string) ($row[$idx] ?? ''));
        };

        $value = (float) str_replace(',', '.', $get('valeur_fonciere'));
        $surface = (float) str_replace(',', '.', $get('surface_reelle_bati'));
        $date = $get('date_mutation');
        $type = strtolower($get('type_local'));
        $mutationId = $get('id_mutation') ?: sha1(($date ?: '') . '|' . ($value ?: '0') . '|' . ($surface ?: '0') . '|' . ($get('commune') ?: ''));

        if ($value <= 0 || $surface <= 0 || $date === '' || $type === '') {
            return null;
        }

        $dateObj = DateTime::createFromFormat('Y-m-d', $date) ?: DateTime::createFromFormat('d/m/Y', $date);
        if (!$dateObj) {
            return null;
        }

        $priceM2 = $value / $surface;
        if ($priceM2 < 100 || $priceM2 > 50000) {
            return null;
        }

        return [
            'mutation_id' => $mutationId,
            'mutation_date' => $dateObj->format('Y-m-d'),
            'property_type' => $type,
            'surface' => $surface,
            'rooms' => (int) $get('nombre_pieces_principales') ?: null,
            'land_surface' => (float) str_replace(',', '.', $get('surface_terrain')) ?: null,
            'value_amount' => $value,
            'price_m2' => $priceM2,
            'city' => $get('commune'),
            'postal_code' => $get('code_postal'),
            'latitude' => (float) str_replace(',', '.', $get('latitude')) ?: null,
            'longitude' => (float) str_replace(',', '.', $get('longitude')) ?: null,
            'address_label' => $get('adresse_nom_voie'),
            'source_file' => $sourceName,
        ];
    }

    private static function upsertTransaction(array $payload): string
    {
        $sql = 'INSERT INTO dvf_transactions
            (mutation_id, mutation_date, property_type, surface, rooms, land_surface, value_amount, price_m2, city, postal_code, latitude, longitude, address_label, source_file, created_at, updated_at)
            VALUES
            (:mutation_id, :mutation_date, :property_type, :surface, :rooms, :land_surface, :value_amount, :price_m2, :city, :postal_code, :latitude, :longitude, :address_label, :source_file, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            mutation_date = VALUES(mutation_date),
            rooms = VALUES(rooms),
            land_surface = VALUES(land_surface),
            city = VALUES(city),
            postal_code = VALUES(postal_code),
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            address_label = VALUES(address_label),
            source_file = VALUES(source_file),
            updated_at = NOW()';

        $stmt = db()->prepare($sql);
        $ok = $stmt->execute([
            ':mutation_id' => $payload['mutation_id'],
            ':mutation_date' => $payload['mutation_date'],
            ':property_type' => $payload['property_type'],
            ':surface' => $payload['surface'],
            ':rooms' => $payload['rooms'],
            ':land_surface' => $payload['land_surface'],
            ':value_amount' => $payload['value_amount'],
            ':price_m2' => $payload['price_m2'],
            ':city' => $payload['city'],
            ':postal_code' => $payload['postal_code'],
            ':latitude' => $payload['latitude'],
            ':longitude' => $payload['longitude'],
            ':address_label' => $payload['address_label'],
            ':source_file' => $payload['source_file'],
        ]);

        if (!$ok) {
            return 'rejected';
        }

        return $stmt->rowCount() > 1 ? 'updated' : 'inserted';
    }
}
