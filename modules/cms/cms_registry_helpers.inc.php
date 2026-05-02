<?php
declare(strict_types=1);

/**
 * Regroupe les entrées du registre page_content_registry en « niveau 1 » (front)
 * vs « niveau 2 » (erreurs, maintenance, remerciements, pages transverses).
 *
 * Clé optionnelle sur une entrée : tier = primary|secondary (ou cms_tier, mêmes valeurs).
 */

/**
 * @param array<string, mixed> $registry
 * @return array{primary: list<string>, secondary: list<string>}
 */
function cms_registry_split_by_tier(array $registry): array
{
    $primary = [];
    $secondary = [];
    foreach ($registry as $key => $entry) {
        if (!is_string($key) || $key === '' || !is_array($entry)) {
            continue;
        }
        if (cms_registry_entry_is_secondary($key, $entry)) {
            $secondary[] = $key;
        } else {
            $primary[] = $key;
        }
    }

    $labelSort = static function (string $a, string $b) use ($registry): int {
        $ea = $registry[$a] ?? null;
        $eb = $registry[$b] ?? null;
        $la = is_array($ea) ? (string) ($ea['label'] ?? $a) : $a;
        $lb = is_array($eb) ? (string) ($eb['label'] ?? $b) : $b;

        return strcasecmp($la, $lb);
    };
    usort($primary, $labelSort);
    usort($secondary, $labelSort);

    return ['primary' => $primary, 'secondary' => $secondary];
}

/**
 * @param array<string, mixed> $entry
 */
function cms_registry_entry_is_secondary(string $key, array $entry): bool
{
    $tier = $entry['tier'] ?? $entry['cms_tier'] ?? null;
    if (is_string($tier) && $tier !== '') {
        $t = strtolower($tier);
        if (in_array($t, ['secondary', 'niveau-2', 'niveau2', 'system', 'erreur', 'transverse'], true)) {
            return true;
        }
        if (in_array($t, ['primary', 'niveau-1', 'niveau1', 'front'], true)) {
            return false;
        }
    }

    $k = strtolower($key);
    $template = strtolower((string) ($entry['template'] ?? ''));
    $route = strtolower((string) ($entry['route_slug'] ?? ''));

    if (preg_match('/(^|-)404(-|$)/', $k)) {
        return true;
    }
    if (str_contains($template, '/404') || preg_match('#(^|/)404$#', $template)) {
        return true;
    }
    if (str_contains($template, 'maintenance')) {
        return true;
    }
    if (str_contains($k, 'merci') || str_contains($template, '/merci')) {
        return true;
    }
    if (str_contains($template, 'merc-estimation') || str_contains($k, 'merc-estimation')) {
        return true;
    }
    if (str_contains($route, 'merci')) {
        return true;
    }
    if (str_contains($template, '/erreur') || str_contains($k, '-erreur')) {
        return true;
    }
    if (str_contains($template, 'indisponible')) {
        return true;
    }
    if (str_contains($template, 'unavailable') || str_contains($template, 'offline')) {
        return true;
    }

    return false;
}
