<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\UpdateRegionalDexNumbers;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\MessageHandler\UpdateHandlerInterface;
use App\MessageHandler\UpdateRegionalDexNumbersHandler;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(UpdateRegionalDexNumbersHandler::class)]
#[UsesClass(RegionalDexNumbersUpdaterService::class)]
#[UsesClass(UpdateRegionalDexNumbers::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
class UpdateRegionalDexNumbersHandlerTest extends AbstractTestUpdateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return RegionalDexNumbersUpdaterService::class;
    }

    /**
     * @param RegionalDexNumbersUpdaterService $updaterService
     */
    #[\Override]
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        return new UpdateRegionalDexNumbersHandler(
            $updaterService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new UpdateRegionalDexNumbers('12');
    }
}
