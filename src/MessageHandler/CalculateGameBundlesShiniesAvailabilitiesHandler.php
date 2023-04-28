<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\Service\CalculatorService\GameBundlesShiniesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CalculateGameBundlesShiniesAvailabilitiesHandler implements CalculateHandlerInterface
{
    use CalculateHandlerTrait;

    public function __construct(
        private readonly GameBundlesShiniesAvailabilitiesCalculatorService $calculatorService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(CalculateGameBundlesShiniesAvailabilities $message): void
    {
        $this->calculate($message);
    }
}
