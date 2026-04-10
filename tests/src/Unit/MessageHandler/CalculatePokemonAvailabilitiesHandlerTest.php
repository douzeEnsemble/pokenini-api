<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\CalculatePokemonAvailabilities;
use App\MessageHandler\CalculateHandlerInterface;
use App\MessageHandler\CalculatePokemonAvailabilitiesHandler;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\PokemonAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(CalculatePokemonAvailabilitiesHandler::class)]
#[UsesClass(PokemonAvailabilitiesCalculatorService::class)]
#[UsesClass(CalculatePokemonAvailabilities::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class CalculatePokemonAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return PokemonAvailabilitiesCalculatorService::class;
    }

    #[\Override]
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        /** @var PokemonAvailabilitiesCalculatorService $calculatorService */
        return new CalculatePokemonAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new CalculatePokemonAvailabilities('12');
    }
}
