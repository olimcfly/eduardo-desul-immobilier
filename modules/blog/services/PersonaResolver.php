<?php

final class PersonaResolver
{
    /**
     * @return array<string, array{label:string,reasons:array<string,string>}>
     */
    public static function getTargetOptions(): array
    {
        return [
            'vendeur' => [
                'label' => 'Vendeur',
                'reasons' => [
                    'divorce_separation' => 'Divorce / séparation',
                    'succession' => 'Succession',
                    'mutation_professionnelle' => 'Mutation professionnelle',
                    'besoin_liquidites' => 'Besoin de liquidités',
                    'vente_rapide' => 'Vente rapide',
                    'bien_ne_se_vend_pas' => 'Bien qui ne se vend pas',
                    'premier_projet_vente' => 'Premier projet de vente',
                ],
            ],
            'acheteur' => [
                'label' => 'Acheteur',
                'reasons' => [
                    'premier_achat' => 'Premier achat',
                    'residence_principale' => 'Résidence principale',
                    'sans_apport' => 'Achat sans apport',
                    'investissement_locatif' => 'Investissement locatif',
                    'achat_familial' => 'Achat familial',
                    'mutation' => 'Mutation',
                    'retour_ville' => 'Retour dans la ville',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{label:string,description:string,niveau_conscience:int,target:string,reason:?string,is_fallback:bool}>
     */
    public static function getPersonaCatalog(): array
    {
        return [
            'vendeur_divorce' => [
                'label' => 'Vendeur en séparation',
                'description' => 'Urgence émotionnelle, besoin de clarté et de rapidité.',
                'niveau_conscience' => 4,
                'target' => 'vendeur',
                'reason' => 'divorce_separation',
                'is_fallback' => false,
            ],
            'vendeur_succession' => [
                'label' => 'Vendeur en succession',
                'description' => 'Besoin d’accompagnement administratif et de sécurisation.',
                'niveau_conscience' => 3,
                'target' => 'vendeur',
                'reason' => 'succession',
                'is_fallback' => false,
            ],
            'vendeur_mutation' => [
                'label' => 'Vendeur en mutation pro',
                'description' => 'Timing serré, arbitrage entre vitesse et prix.',
                'niveau_conscience' => 4,
                'target' => 'vendeur',
                'reason' => 'mutation_professionnelle',
                'is_fallback' => false,
            ],
            'vendeur_liquidites' => [
                'label' => 'Vendeur besoin de liquidités',
                'description' => 'Recherche d’une stratégie de vente fiable à court terme.',
                'niveau_conscience' => 4,
                'target' => 'vendeur',
                'reason' => 'besoin_liquidites',
                'is_fallback' => false,
            ],
            'vendeur_vente_rapide' => [
                'label' => 'Vendeur pressé',
                'description' => 'Priorité à la vitesse de transaction avec risque maîtrisé.',
                'niveau_conscience' => 5,
                'target' => 'vendeur',
                'reason' => 'vente_rapide',
                'is_fallback' => false,
            ],
            'vendeur_bien_bloque' => [
                'label' => 'Vendeur bien bloqué',
                'description' => 'Bien déjà en marché, besoin d’un repositionnement efficace.',
                'niveau_conscience' => 5,
                'target' => 'vendeur',
                'reason' => 'bien_ne_se_vend_pas',
                'is_fallback' => false,
            ],
            'vendeur_premier_projet' => [
                'label' => 'Premier vendeur',
                'description' => 'Nécessite pédagogie, étapes claires et réassurance.',
                'niveau_conscience' => 2,
                'target' => 'vendeur',
                'reason' => 'premier_projet_vente',
                'is_fallback' => false,
            ],
            'acheteur_premier_achat' => [
                'label' => 'Primo-accédant',
                'description' => 'Recherche de repères concrets sur budget et démarches.',
                'niveau_conscience' => 2,
                'target' => 'acheteur',
                'reason' => 'premier_achat',
                'is_fallback' => false,
            ],
            'acheteur_residence_principale' => [
                'label' => 'Acheteur résidence principale',
                'description' => 'Projet structurant, arbitrages long terme et cadre de vie.',
                'niveau_conscience' => 3,
                'target' => 'acheteur',
                'reason' => 'residence_principale',
                'is_fallback' => false,
            ],
            'acheteur_sans_apport' => [
                'label' => 'Acheteur sans apport',
                'description' => 'Besoin d’optimiser financement et crédibilité dossier.',
                'niveau_conscience' => 3,
                'target' => 'acheteur',
                'reason' => 'sans_apport',
                'is_fallback' => false,
            ],
            'acheteur_investisseur' => [
                'label' => 'Investisseur locatif',
                'description' => 'Focus rendement, fiscalité et risque locatif.',
                'niveau_conscience' => 5,
                'target' => 'acheteur',
                'reason' => 'investissement_locatif',
                'is_fallback' => false,
            ],
            'acheteur_famille' => [
                'label' => 'Acheteur familial',
                'description' => 'Priorité au confort de vie et à la projection familiale.',
                'niveau_conscience' => 3,
                'target' => 'acheteur',
                'reason' => 'achat_familial',
                'is_fallback' => false,
            ],
            'acheteur_mutation' => [
                'label' => 'Acheteur en mutation',
                'description' => 'Décision rapide, besoin de shortlist opérationnelle.',
                'niveau_conscience' => 4,
                'target' => 'acheteur',
                'reason' => 'mutation',
                'is_fallback' => false,
            ],
            'acheteur_retour_ville' => [
                'label' => 'Acheteur de retour en ville',
                'description' => 'Recherche de repères quartier/prix après une absence.',
                'niveau_conscience' => 3,
                'target' => 'acheteur',
                'reason' => 'retour_ville',
                'is_fallback' => false,
            ],
            'vendeur_generic' => [
                'label' => 'Vendeur générique',
                'description' => 'Persona vendeur par défaut pour angle éditorial transverse.',
                'niveau_conscience' => 3,
                'target' => 'vendeur',
                'reason' => null,
                'is_fallback' => true,
            ],
            'acheteur_generic' => [
                'label' => 'Acheteur générique',
                'description' => 'Persona acheteur par défaut pour angle éditorial transverse.',
                'niveau_conscience' => 3,
                'target' => 'acheteur',
                'reason' => null,
                'is_fallback' => true,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getMapping(): array
    {
        return [
            'vendeur:divorce_separation' => 'vendeur_divorce',
            'vendeur:succession' => 'vendeur_succession',
            'vendeur:mutation_professionnelle' => 'vendeur_mutation',
            'vendeur:besoin_liquidites' => 'vendeur_liquidites',
            'vendeur:vente_rapide' => 'vendeur_vente_rapide',
            'vendeur:bien_ne_se_vend_pas' => 'vendeur_bien_bloque',
            'vendeur:premier_projet_vente' => 'vendeur_premier_projet',
            'acheteur:premier_achat' => 'acheteur_premier_achat',
            'acheteur:residence_principale' => 'acheteur_residence_principale',
            'acheteur:sans_apport' => 'acheteur_sans_apport',
            'acheteur:investissement_locatif' => 'acheteur_investisseur',
            'acheteur:achat_familial' => 'acheteur_famille',
            'acheteur:mutation' => 'acheteur_mutation',
            'acheteur:retour_ville' => 'acheteur_retour_ville',
        ];
    }

    /**
     * @return array{target:?string,reason:?string,persona_id:?string,label:string,description:string,niveau_conscience:?int,is_fallback:bool}
     */
    public static function resolve(?string $target, ?string $reason): array
    {
        $target = is_string($target) ? trim($target) : null;
        $reason = is_string($reason) ? trim($reason) : null;

        if ($target === '') {
            $target = null;
        }

        if ($reason === '') {
            $reason = null;
        }

        $mapping = self::getMapping();
        $catalog = self::getPersonaCatalog();

        $personaId = null;
        if ($target !== null && $reason !== null) {
            $key = $target . ':' . $reason;
            if (isset($mapping[$key])) {
                $personaId = $mapping[$key];
            }
        }

        if ($personaId === null && $target !== null) {
            $personaId = $target === 'vendeur' ? 'vendeur_generic' : ($target === 'acheteur' ? 'acheteur_generic' : null);
        }

        $persona = $personaId !== null && isset($catalog[$personaId]) ? $catalog[$personaId] : null;

        return [
            'target' => $target,
            'reason' => $reason,
            'persona_id' => $personaId,
            'label' => $persona['label'] ?? 'Aucun persona détecté',
            'description' => $persona['description'] ?? 'Sélectionnez une cible puis une raison principale.',
            'niveau_conscience' => $persona['niveau_conscience'] ?? null,
            'is_fallback' => (bool)($persona['is_fallback'] ?? false),
        ];
    }

    /**
     * @return array{target:?string,reason:?string,persona_id:?string,label:string,description:string,niveau_conscience:?int,is_fallback:bool}
     */
    public static function resolveFromPersonaId(?string $personaId): array
    {
        $personaId = is_string($personaId) ? trim($personaId) : null;
        if ($personaId === '') {
            $personaId = null;
        }

        $catalog = self::getPersonaCatalog();
        $persona = $personaId !== null && isset($catalog[$personaId]) ? $catalog[$personaId] : null;

        if ($persona === null) {
            return self::resolve(null, null);
        }

        return [
            'target' => $persona['target'],
            'reason' => $persona['reason'],
            'persona_id' => $personaId,
            'label' => $persona['label'],
            'description' => $persona['description'],
            'niveau_conscience' => $persona['niveau_conscience'],
            'is_fallback' => (bool)$persona['is_fallback'],
        ];
    }
}
