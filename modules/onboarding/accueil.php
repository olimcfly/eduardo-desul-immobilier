<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/OnboardingRepository.php';
require_once __DIR__ . '/services/BlueprintService.php';
require_once __DIR__ . '/services/OnboardingService.php';
require_once __DIR__ . '/controllers/OnboardingController.php';

$pageTitle = 'Onboarding';
$pageDescription = 'Collectez les informations essentielles et définissez vos objectifs';

$onboardingController = new OnboardingController(
    new OnboardingService(
        new OnboardingRepository(),
        new BlueprintService()
    )
);

$onboardingViewData = $onboardingController->handle();

function renderContent(): void
{
    global $onboardingViewData;

    $viewData = $onboardingViewData;
    require __DIR__ . '/views/wizard.php';
}
