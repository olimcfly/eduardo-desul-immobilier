<?php

class InstanceIntakeParserService
{
    /**
     * @return array{fields: array<string,string>, warnings: array<int,string>}
     */
    public function parse(string $rawText): array
    {
        $text = trim($rawText);
        if ($text === '') {
            return ['fields' => [], 'warnings' => ['Aucun texte fourni.']];
        }

        $fields = [];
        $warnings = [];

        $jsonFields = $this->parseAsJson($text);
        if ($jsonFields !== []) {
            $fields = array_merge($fields, $jsonFields);
        }

        $lineFields = $this->parseKeyValueLines($text);
        if ($lineFields !== []) {
            $fields = array_merge($fields, $lineFields);
        }

        $heuristicFields = $this->parseByHeuristics($text);
        if ($heuristicFields !== []) {
            $fields = array_merge($fields, $heuristicFields);
        }

        $allowed = [
            'client_name','business_name','domain','city','admin_email','admin_password_temp',
            'db_host','db_port','db_name','db_user','db_pass',
            'smtp_host','smtp_port','smtp_user','smtp_pass','smtp_encryption','from_email',
            'openai_api_key','perplexity_api_key','logo_path','status',
        ];

        $fields = array_intersect_key($fields, array_flip($allowed));

        if ($fields === []) {
            $warnings[] = 'Aucune donnée reconnue automatiquement. Vérifiez le format clé=valeur.';
        }

        return [
            'fields' => $fields,
            'warnings' => $warnings,
        ];
    }

    private function parseAsJson(string $text): array
    {
        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            return [];
        }

        $out = [];
        foreach ($decoded as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $mapped = $this->mapKey((string) $key);
            if ($mapped !== null) {
                $out[$mapped] = trim((string) $value);
            }
        }

        return $out;
    }

    private function parseKeyValueLines(string $text): array
    {
        $lines = preg_split('/\R/u', $text) ?: [];
        $out = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '//')) {
                continue;
            }

            if (!preg_match('/^([a-zA-Z0-9_\-\s\.]+)\s*[:=]\s*(.+)$/u', $line, $m)) {
                continue;
            }

            $rawKey = trim($m[1]);
            $rawValue = trim($m[2]);
            $rawValue = trim($rawValue, " \t\n\r\0\x0B\"'");

            $key = $this->mapKey($rawKey);
            if ($key === null || $rawValue === '') {
                continue;
            }

            $out[$key] = $rawValue;
        }

        return $out;
    }

    private function parseByHeuristics(string $text): array
    {
        $out = [];

        if (preg_match('/\b([a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,})\b/i', $text, $m) && !isset($out['admin_email'])) {
            $out['admin_email'] = strtolower($m[1]);
        }

        if (preg_match('/\b(?:https?:\/\/)?([a-z0-9\-]+(?:\.[a-z0-9\-]+)+)\b/i', $text, $m) && !isset($out['domain'])) {
            $out['domain'] = strtolower($m[1]);
        }

        return $out;
    }

    private function mapKey(string $rawKey): ?string
    {
        $key = $this->normalizeKey($rawKey);

        $map = [
            'client_name' => 'client_name',
            'client' => 'client_name',
            'nom_client' => 'client_name',

            'business_name' => 'business_name',
            'societe' => 'business_name',
            'entreprise' => 'business_name',
            'agence' => 'business_name',

            'domain' => 'domain',
            'domaine' => 'domain',
            'site_domain' => 'domain',
            'url' => 'domain',

            'city' => 'city',
            'ville' => 'city',

            'admin_email' => 'admin_email',
            'email_admin' => 'admin_email',
            'administrator_email' => 'admin_email',

            'admin_password_temp' => 'admin_password_temp',
            'mot_de_passe_admin' => 'admin_password_temp',
            'password_admin' => 'admin_password_temp',

            'db_host' => 'db_host',
            'db_port' => 'db_port',
            'db_name' => 'db_name',
            'db_user' => 'db_user',
            'db_pass' => 'db_pass',

            'smtp_host' => 'smtp_host',
            'smtp_port' => 'smtp_port',
            'smtp_user' => 'smtp_user',
            'smtp_pass' => 'smtp_pass',
            'smtp_encryption' => 'smtp_encryption',
            'smtp_secure' => 'smtp_encryption',

            'from_email' => 'from_email',
            'expediteur_email' => 'from_email',

            'openai_api_key' => 'openai_api_key',
            'perplexity_api_key' => 'perplexity_api_key',
            'logo_path' => 'logo_path',
            'status' => 'status',
        ];

        return $map[$key] ?? null;
    }

    private function normalizeKey(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = str_replace(['é','è','ê','ë','à','â','ä','î','ï','ô','ö','ù','û','ü','ç'], ['e','e','e','e','a','a','a','i','i','o','o','u','u','u','c'], $value);
        $value = preg_replace('/[^a-z0-9]+/u', '_', $value) ?? '';
        $value = trim($value, '_');

        return $value;
    }
}
