<?php
// ============================================================
// SANITIZE & VALIDATION
// ============================================================

function sanitizeString(string $val): string
{
    return trim(strip_tags($val));
}

function sanitizeEmail(string $val): string|false
{
    $val = trim($val);
    return filter_var($val, FILTER_VALIDATE_EMAIL) ? strtolower($val) : false;
}

function sanitizePhone(string $val): string
{
    return preg_replace('/[^\d+\s()-]/', '', trim($val));
}

function sanitizeInt(mixed $val): int
{
    return (int) filter_var($val, FILTER_SANITIZE_NUMBER_INT);
}

function sanitizeFloat(mixed $val): float
{
    return (float) filter_var(str_replace(',', '.', $val), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function sanitizeSlug(string $val): string
{
    return slugify(sanitizeString($val));
}

function validateRequired(array $fields, array $data): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[$field] = $label . ' est requis.';
        }
    }
    return $errors;
}
