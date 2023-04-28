<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\CalculateGameBundlesAvailabilities;
use App\MessageHandler\CalculateGameBundlesAvailabilitiesHandler;
use App\MessageHandler\CalculateHandlerInterface;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\GameBundlesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;

class CalculateGameBundlesAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    public function getServiceClass(): string
    {
        return GameBundlesAvailabilitiesCalculatorService::class;
    }

    /**
     * @param GameBundlesAvailabilitiesCalculatorService $calculatorService
    **/
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        return new CalculateGameBundlesAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new CalculateGameBundlesAvailabilities('12');
    }
}
