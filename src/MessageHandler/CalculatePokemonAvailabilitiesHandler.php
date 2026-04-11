<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CalculatePokemonAvailabilities;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\Service\CalculatorService\PokemonAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CalculatePokemonAvailabilitiesHandler implements CalculateHandlerInterface
{
    use CalculateHandlerTrait;

    public function __construct(
        private readonly PokemonAvailabilitiesCalculatorService $calculatorService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(CalculatePokemonAvailabilities $message): void
    {
        $this->calculate($message);
    }
}
