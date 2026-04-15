<?php

declare(strict_types=1);

final class OnboardingController
{
    private const STEP_LABELS = [
        1 => 'Qui tu es',
        2 => 'Cible',
        3 => 'Offre actuelle',
        4 => 'Zone',
        5 => 'Objectifs',
        6 => 'Synthèse',
    ];

    private const STEP_KEYS = [
        1 => 'identity',
        2 => 'target',
        3 => 'offer',
        4 => 'territory',
        5 => 'goal',
    ];

    public function __construct(private OnboardingService $service)
    {
    }

    public function handle(): array
    {
        $user = Auth::user();
        if ($user === null) {
            throw new RuntimeException('Utilisateur non authentifié.');
        }

        $session = $this->service->getOrCreateSession((int) $user['id']);
        $sessionId = (int) ($session['id'] ?? 0);
        if ($sessionId <= 0) {
            throw new RuntimeException('Impossible de démarrer la session onboarding.');
        }

        $bundle = $this->service->getSessionBundle($sessionId);
        $session = $bundle['session'];
        $answersByStep = $bundle['answers'];
        $blueprint = $bundle['blueprint'];

        $currentStep = $this->resolveCurrentStep((int) ($session['current_step'] ?? 1));
        $requestedStep = $this->resolveCurrentStep((int) ($_GET['step'] ?? $currentStep));
        $step = $requestedStep;

        $errors = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            verifyCsrf();
            $intent = (string) ($_POST['intent'] ?? 'save_step');

            if ($intent === 'complete') {
                $this->service->completeSession($sessionId);
                Session::flash('success', 'Onboarding finalisé. Vos données sont prêtes pour le module Construire.');
                redirect('/admin?module=onboarding&step=6');
            }

            $postedStep = $this->resolveCurrentStep((int) ($_POST['step'] ?? $step));
            $stepKey = self::STEP_KEYS[$postedStep] ?? null;

            if ($stepKey !== null) {
                $payload = $this->extractStepPayload($postedStep, $_POST);
                $errors = $this->validateStep($postedStep, $payload);

                if ($errors === []) {
                    $navigation = (string) ($_POST['navigation'] ?? 'next');
                    $nextStep = $this->nextStep($postedStep, $navigation);
                    $this->service->saveStep($sessionId, $stepKey, $payload, min(5, max(1, $nextStep)));

                    if ($navigation === 'save') {
                        Session::flash('success', 'Étape enregistrée automatiquement.');
                        redirect('/admin?module=onboarding&step=' . $postedStep);
                    }

                    if ($navigation === 'prev') {
                        redirect('/admin?module=onboarding&step=' . max(1, $postedStep - 1));
                    }

                    if ($postedStep >= 5) {
                        redirect('/admin?module=onboarding&step=6');
                    }

                    redirect('/admin?module=onboarding&step=' . ($postedStep + 1));
                }

                $answersByStep[$stepKey] = $payload;
                $blueprint = (new BlueprintService())->compile($answersByStep);
                $step = $postedStep;
            }
        }

        $inProgress = ((string) ($session['status'] ?? 'draft')) !== 'completed';
        $canResume = $inProgress && ((int) ($session['current_step'] ?? 1) > 1);

        return [
            'step' => $step,
            'labels' => self::STEP_LABELS,
            'stepKeys' => self::STEP_KEYS,
            'session' => $session,
            'answers' => $answersByStep,
            'errors' => $errors,
            'blueprint' => $blueprint,
            'canResume' => $canResume,
            'resumeStep' => (int) ($session['current_step'] ?? 1),
        ];
    }

    private function resolveCurrentStep(int $step): int
    {
        if ($step < 1) {
            return 1;
        }

        if ($step > 6) {
            return 6;
        }

        return $step;
    }

    private function nextStep(int $currentStep, string $navigation): int
    {
        if ($navigation === 'prev') {
            return $currentStep - 1;
        }

        if ($navigation === 'save') {
            return $currentStep;
        }

        return $currentStep + 1;
    }

    private function extractStepPayload(int $step, array $input): array
    {
        if ($step === 1) {
            return [
                'name' => trim((string) ($input['name'] ?? '')),
                'city' => trim((string) ($input['city'] ?? '')),
                'status' => trim((string) ($input['status'] ?? '')),
                'experience' => trim((string) ($input['experience'] ?? '')),
            ];
        }

        if ($step === 2) {
            return [
                'client_types' => trim((string) ($input['client_types'] ?? '')),
                'main_situations' => trim((string) ($input['main_situations'] ?? '')),
            ];
        }

        if ($step === 3) {
            return [
                'description' => trim((string) ($input['description'] ?? '')),
                'methods' => trim((string) ($input['methods'] ?? '')),
            ];
        }

        if ($step === 4) {
            return [
                'primary_city' => trim((string) ($input['primary_city'] ?? '')),
                'secondary_zones' => trim((string) ($input['secondary_zones'] ?? '')),
            ];
        }

        if ($step === 5) {
            return [
                'leads_per_month' => trim((string) ($input['leads_per_month'] ?? '')),
                'appointments_target' => trim((string) ($input['appointments_target'] ?? '')),
                'revenue_target' => trim((string) ($input['revenue_target'] ?? '')),
            ];
        }

        return [];
    }

    private function validateStep(int $step, array $payload): array
    {
        $errors = [];

        if ($step === 1) {
            if ($payload['name'] === '') {
                $errors[] = 'Le nom est requis.';
            }
            if ($payload['city'] === '') {
                $errors[] = 'La ville est requise.';
            }
            if ($payload['status'] === '') {
                $errors[] = 'Le statut est requis.';
            }
        }

        if ($step === 2 && $payload['client_types'] === '') {
            $errors[] = 'Le type de clients est requis.';
        }

        if ($step === 3 && $payload['description'] === '') {
            $errors[] = 'La description de l’offre actuelle est requise.';
        }

        if ($step === 4 && $payload['primary_city'] === '') {
            $errors[] = 'La ville principale est requise.';
        }

        if ($step === 5) {
            if ($payload['leads_per_month'] === '') {
                $errors[] = 'Le nombre de leads visés est requis.';
            }
            if ($payload['appointments_target'] === '') {
                $errors[] = 'Le nombre de RDV souhaités est requis.';
            }
            if ($payload['revenue_target'] === '') {
                $errors[] = 'Le revenu cible est requis.';
            }
        }

        return $errors;
    }
}
