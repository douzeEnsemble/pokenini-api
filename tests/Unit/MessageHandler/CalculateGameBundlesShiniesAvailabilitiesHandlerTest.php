<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\MessageHandler\CalculateGameBundlesShiniesAvailabilitiesHandler;
use App\MessageHandler\CalculateHandlerInterface;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\GameBundlesShiniesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;

class CalculateGameBundlesShiniesAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    public function getServiceClass(): string
    {
        return GameBundlesShiniesAvailabilitiesCalculatorService::class;
    }

    /**
     * @param GameBundlesShiniesAvailabilitiesCalculatorService $calculatorService
    **/
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        return new CalculateGameBundlesShiniesAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new CalculateGameBundlesShiniesAvailabilities('12');
    }
}
