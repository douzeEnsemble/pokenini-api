<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\CalculateGameBundlesAvailabilities;
use App\MessageHandler\CalculateGameBundlesAvailabilitiesHandler;
use App\MessageHandler\CalculateHandlerInterface;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\GameBundlesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(CalculateGameBundlesAvailabilitiesHandler::class)]
#[UsesClass(GameBundlesAvailabilitiesCalculatorService::class)]
#[UsesClass(CalculateGameBundlesAvailabilities::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
class CalculateGameBundlesAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return GameBundlesAvailabilitiesCalculatorService::class;
    }

    #[\Override]
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        /** @var GameBundlesAvailabilitiesCalculatorService $calculatorService */
        return new CalculateGameBundlesAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new CalculateGameBundlesAvailabilities('12');
    }
}
