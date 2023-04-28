<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\UpdateGamesAvailabilities;
use App\MessageHandler\UpdateGamesAvailabilitiesHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\UpdaterServiceInterface;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;

class UpdateGamesAvailabilitiesHandlerTest extends AbstractTestUpdateHandler
{
    public function getServiceClass(): string
    {
        return GamesAvailabilitiesUpdaterService::class;
    }

    /**
     * @param GamesAvailabilitiesUpdaterService $updaterService
    **/
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        return new UpdateGamesAvailabilitiesHandler(
            $updaterService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new UpdateGamesAvailabilities('12');
    }
}
