<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\MessageHandler\CalculateGameBundlesShiniesAvailabilitiesHandler;
use App\MessageHandler\CalculateHandlerInterface;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\GameBundlesShiniesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(CalculateGameBundlesShiniesAvailabilitiesHandler::class)]
#[UsesClass(GameBundlesShiniesAvailabilitiesCalculatorService::class)]
#[UsesClass(CalculateGameBundlesShiniesAvailabilities::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
class CalculateGameBundlesShiniesAvailabilitiesHandlerTest extends AbstractTestCalculateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return GameBundlesShiniesAvailabilitiesCalculatorService::class;
    }

    #[\Override]
    public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface {
        /** @var GameBundlesShiniesAvailabilitiesCalculatorService $calculatorService */
        return new CalculateGameBundlesShiniesAvailabilitiesHandler(
            $calculatorService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new CalculateGameBundlesShiniesAvailabilities('12');
    }
}
