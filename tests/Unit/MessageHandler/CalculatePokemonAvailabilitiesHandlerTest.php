<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\CalculatePokemonAvailabilities;
use App\MessageHandler\CalculateHandlerInterface;
use App\MessageHandler\CalculatePokemonAvailabilitiesHandler;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\PokemonAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;

class CalculatePokemonAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    public function getServiceClass(): string
    {
        return PokemonAvailabilitiesCalculatorService::class;
    }

    /**
     * @param PokemonAvailabilitiesCalculatorService $calculatorService
    **/
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        return new CalculatePokemonAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new CalculatePokemonAvailabilities('12');
    }
}
