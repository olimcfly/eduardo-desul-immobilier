<?php

declare(strict_types=1);

final class BlueprintService
{
    public const VERSION = '2.0';

    public function compile(array $answersByStep): array
    {
        $identity = $answersByStep['identity'] ?? [];
        $target = $answersByStep['target'] ?? [];
        $offer = $answersByStep['offer'] ?? [];
        $territory = $answersByStep['territory'] ?? [];
        $goal = $answersByStep['goal'] ?? [];

        $name = $this->cleanText($identity['name'] ?? '');
        $city = $this->cleanText($identity['city'] ?? ($territory['primary_city'] ?? ''));
        $status = $this->cleanText($identity['status'] ?? '');
        $experience = $this->cleanText($identity['experience'] ?? '');
        $clientTypes = $this->cleanText($target['client_types'] ?? '');
        $situations = $this->cleanText($target['main_situations'] ?? '');
        $offerDescription = $this->cleanText($offer['description'] ?? '');
        $methods = $this->cleanText($offer['methods'] ?? '');

        return [
            'version' => self::VERSION,
            'identity' => [
                'name' => $name,
                'city' => $city,
                'status' => $status,
                'experience' => $experience,
            ],
            'target' => [
                'client_types' => $clientTypes,
                'main_situations' => $situations,
            ],
            'offer' => [
                'description' => $offerDescription,
                'methods' => $methods,
            ],
            'zone' => [
                'primary_city' => $this->cleanText($territory['primary_city'] ?? ''),
                'secondary_zones' => $this->toList($territory['secondary_zones'] ?? ''),
            ],
            'goals' => [
                'leads_per_month' => $this->toPositiveInt($goal['leads_per_month'] ?? 0),
                'appointments_target' => $this->toPositiveInt($goal['appointments_target'] ?? 0),
                'revenue_target' => $this->toNullableFloat($goal['revenue_target'] ?? null),
            ],
            'auto_generation' => [
                'persona' => $this->buildPersona($clientTypes, $situations, $city),
                'positioning' => $this->buildPositioning($status, $experience, $city, $clientTypes),
                'offer' => $this->buildOffer($offerDescription, $methods, $goal),
            ],
        ];
    }

    private function buildPersona(string $clientTypes, string $situations, string $city): string
    {
        $persona = $clientTypes !== '' ? $clientTypes : 'clients immobiliers';
        $pain = $situations !== '' ? $situations : 'avec un besoin de conseil clair et rassurant';
        $zone = $city !== '' ? ' à ' . $city : '';

        return sprintf('%s%s, principalement %s.', ucfirst($persona), $zone, $pain);
    }

    private function buildPositioning(string $status, string $experience, string $city, string $clientTypes): string
    {
        $role = $status !== '' ? $status : 'professionnel immobilier';
        $xp = $experience !== '' ? $experience : 'expérience non précisée';
        $zone = $city !== '' ? 'sur ' . $city : 'sur votre secteur';
        $target = $clientTypes !== '' ? $clientTypes : 'une cible locale';

        return sprintf('Positionnement : %s %s, spécialisé %s pour %s.', $role, $xp, $zone, $target);
    }

    private function buildOffer(string $description, string $methods, array $goal): string
    {
        $base = $description !== '' ? $description : 'Offre immobilière personnalisée';
        $approach = $methods !== '' ? $methods : 'accompagnement structuré de la prise de contact à la signature';

        $leads = $this->toPositiveInt($goal['leads_per_month'] ?? 0);
        $rdv = $this->toPositiveInt($goal['appointments_target'] ?? 0);
        $targetPart = ($leads > 0 || $rdv > 0)
            ? sprintf(' Objectif opérationnel : %d leads/mois et %d RDV.', $leads, $rdv)
            : '';

        return sprintf('%s. Méthodes actuelles : %s.%s', $base, $approach, $targetPart);
    }

    private function cleanText(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function toList(mixed $value): array
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = preg_split('/[,;\n]+/', (string) $value) ?: [];
        }

        return array_values(array_filter(array_map(static function ($item): string {
            return trim((string) $item);
        }, $items), static function ($item): bool {
            return $item !== '';
        }));
    }

    private function toPositiveInt(mixed $value): int
    {
        $number = (int) $value;
        return $number > 0 ? $number : 0;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace([',', ' '], ['.', ''], trim((string) $value));
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        $amount = (float) $normalized;
        return $amount >= 0 ? $amount : null;
    }
}
