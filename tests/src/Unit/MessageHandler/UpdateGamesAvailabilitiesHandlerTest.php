<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\UpdateGamesAvailabilities;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\MessageHandler\UpdateGamesAvailabilitiesHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(UpdateGamesAvailabilitiesHandler::class)]
#[UsesClass(GamesAvailabilitiesUpdaterService::class)]
#[UsesClass(UpdateGamesAvailabilities::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
class UpdateGamesAvailabilitiesHandlerTest extends AbstractTestUpdateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return GamesAvailabilitiesUpdaterService::class;
    }

    #[\Override]
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        /** @var GamesAvailabilitiesUpdaterService $updaterService */
        return new UpdateGamesAvailabilitiesHandler(
            $updaterService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new UpdateGamesAvailabilities('12');
    }
}
