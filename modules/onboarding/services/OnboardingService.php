<?php

declare(strict_types=1);

final class OnboardingService
{
    public function __construct(
        private OnboardingRepository $repository,
        private BlueprintService $blueprintService
    ) {
        $this->repository->ensureSchema();
    }

    public function getOrCreateSession(int $userId): array
    {
        $session = $this->repository->findActiveSessionByUserId($userId);
        if ($session !== null) {
            return $session;
        }

        return $this->repository->createSession($userId);
    }

    public function getSessionBundle(int $sessionId): array
    {
        $session = $this->repository->findSessionById($sessionId) ?? [];
        $answersByStep = $this->repository->getAnswersBySessionId($sessionId);
        $blueprint = $this->repository->getBlueprintBySessionId($sessionId, BlueprintService::VERSION)
            ?? $this->blueprintService->compile($answersByStep);

        return [
            'session' => $session,
            'answers' => $answersByStep,
            'blueprint' => $blueprint,
        ];
    }

    public function saveStep(int $sessionId, string $stepKey, array $answers, int $nextStep): array
    {
        $this->repository->upsertStepAnswers($sessionId, $stepKey, $answers);
        $this->repository->updateSessionProgress($sessionId, $nextStep, 'in_progress');

        $answersByStep = $this->repository->getAnswersBySessionId($sessionId);
        $blueprint = $this->blueprintService->compile($answersByStep);
        $this->repository->upsertBlueprint($sessionId, BlueprintService::VERSION, $blueprint);

        return [
            'answers' => $answersByStep,
            'blueprint' => $blueprint,
        ];
    }

    public function completeSession(int $sessionId): void
    {
        $answersByStep = $this->repository->getAnswersBySessionId($sessionId);
        $blueprint = $this->blueprintService->compile($answersByStep);
        $this->repository->upsertBlueprint($sessionId, BlueprintService::VERSION, $blueprint);
        $this->repository->markCompleted($sessionId);
    }
}
