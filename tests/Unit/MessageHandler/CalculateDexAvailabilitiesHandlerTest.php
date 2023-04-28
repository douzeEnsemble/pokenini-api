<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\CalculateDexAvailabilities;
use App\MessageHandler\CalculateDexAvailabilitiesHandler;
use App\MessageHandler\CalculateHandlerInterface;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\DexAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;

class CalculateDexAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    public function getServiceClass(): string
    {
        return DexAvailabilitiesCalculatorService::class;
    }

    /**
     * @param DexAvailabilitiesCalculatorService $calculatorService
    **/
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        return new CalculateDexAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new CalculateDexAvailabilities('12');
    }
}
