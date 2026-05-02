<?php
declare(strict_types=1);

require_once __DIR__ . '/AiService.php';
require_once __DIR__ . '/GuideLocalLlmGuardService.php';

/**
 * Génération de contenu guide local via le LLM (Anthropic / AiService).
 * Cache disque + rate limit génération : {@see GuideLocalLlmGuardService}.
 */
final class GuideLocalLlmService
{
    private const INTERNAL_CLIENT = 'internal';

    /**
     * @return array{text: string, cached: bool}
     */
    public static function describeDistrictForClient(string $clientKey, string $districtName, string $cityName = 'Bordeaux'): array
    {
        $districtName = trim($districtName);
        $cityName = trim($cityName) !== '' ? trim($cityName) : 'Bordeaux';
        $params = [
            'district' => mb_strtolower($districtName, 'UTF-8'),
            'city'     => mb_strtolower($cityName, 'UTF-8'),
        ];

        return GuideLocalLlmGuardService::rememberWithLimits(
            $clientKey,
            'describe_district',
            $params,
            static function () use ($districtName, $cityName): string {
                $system = 'Tu es un expert immobilier et de la vie locale à Bordeaux Métropole. '
                    . 'Tu écris en français, ton chaleureux et informatif, sans HTML.';

                $user = "Génère une description d'environ 200 mots pour le quartier « {$districtName} » à {$cityName}. "
                    . "Mets en avant histoire, ambiance, types de logements, et 3 idées de sorties ou commerces typiques (sans inventer d'adresse précise si tu n'es pas sûr).";

                return AiService::ask($system, $user);
            }
        );
    }

    /**
     * @return array{text: string, cached: bool}
     */
    public static function describePoiForClient(string $clientKey, string $poiName, string $categoryName, string $areaLabel): array
    {
        $poiName = trim($poiName);
        $categoryName = trim($categoryName);
        $areaLabel = trim($areaLabel);
        $params = [
            'poi'      => mb_strtolower($poiName, 'UTF-8'),
            'category' => mb_strtolower($categoryName, 'UTF-8'),
            'area'     => mb_strtolower($areaLabel, 'UTF-8'),
        ];

        return GuideLocalLlmGuardService::rememberWithLimits(
            $clientKey,
            'describe_poi',
            $params,
            static function () use ($poiName, $categoryName, $areaLabel): string {
                $system = 'Tu rédiges des fiches lieux pour un guide immobilier local à Bordeaux. Français, factuel, sans HTML.';
                $user = "Écris environ 120 mots pour le lieu « {$poiName} », catégorie « {$categoryName} », secteur « {$areaLabel} ». "
                    . 'Mets en avant l’intérêt pour de futurs résidents / visiteurs.';

                return AiService::ask($system, $user);
            }
        );
    }

    public static function describeDistrict(string $districtName, string $cityName = 'Bordeaux'): string
    {
        return self::describeDistrictForClient(self::INTERNAL_CLIENT, $districtName, $cityName)['text'];
    }

    public static function describePoi(string $poiName, string $categoryName, string $areaLabel): string
    {
        return self::describePoiForClient(self::INTERNAL_CLIENT, $poiName, $categoryName, $areaLabel)['text'];
    }

    /**
     * 5 à 8 expressions ou courtes requêtes SEO séparées par des virgules, sans guillemets inutiles.
     *
     * @return array{text: string, cached: bool}
     */
    public static function suggestSeoKeywordsForClient(string $clientKey, string $businessName, string $categoryName, string $cityName): array
    {
        $businessName = trim($businessName);
        $categoryName = trim($categoryName);
        $cityName = trim($cityName) !== '' ? trim($cityName) : 'Bordeaux Métropole';
        $params = [
            'biz'  => mb_strtolower($businessName, 'UTF-8'),
            'cat'  => mb_strtolower($categoryName, 'UTF-8'),
            'city' => mb_strtolower($cityName, 'UTF-8'),
        ];

        return GuideLocalLlmGuardService::rememberWithLimits(
            $clientKey,
            'suggest_seo_keywords',
            $params,
            static function () use ($businessName, $categoryName, $cityName): string {
                $system = 'Tu génères des mots-clés et expressions de recherche locale (SEO) en français, sans HTML, sans explication, une seule ligne, séparateur virgule.';

                $user = "Propose 5 à 8 expressions utiles (requêtes Google) pour l’annuaire local. Commerce : « {$businessName} ». Type : « {$categoryName} ». Ville/zone : « {$cityName} ». Rien d’autre qu’une liste séparée par des virgules.";

                return AiService::ask($system, $user);
            }
        );
    }
}
