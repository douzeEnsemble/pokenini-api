<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CalculateGameBundlesAvailabilities;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\Service\CalculatorService\GameBundlesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CalculateGameBundlesAvailabilitiesHandler
{
    use CalculateHandlerTrait;

    public function __construct(
        private readonly GameBundlesAvailabilitiesCalculatorService $calculatorService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(CalculateGameBundlesAvailabilities $message): void
    {
        $this->calculate($message);
    }
}
