<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\CalculateDexAvailabilities;
use App\MessageHandler\CalculateDexAvailabilitiesHandler;
use App\MessageHandler\CalculateHandlerInterface;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\DexAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(CalculateDexAvailabilitiesHandler::class)]
#[UsesClass(DexAvailabilitiesCalculatorService::class)]
#[UsesClass(CalculateDexAvailabilities::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class CalculateDexAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return DexAvailabilitiesCalculatorService::class;
    }

    #[\Override]
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        /** @var DexAvailabilitiesCalculatorService $calculatorService */
        return new CalculateDexAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new CalculateDexAvailabilities('12');
    }
}
