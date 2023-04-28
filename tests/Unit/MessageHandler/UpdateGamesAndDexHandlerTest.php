<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\UpdateGamesAndDex;
use App\MessageHandler\UpdateGamesAndDexHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\UpdaterServiceInterface;
use App\Service\UpdaterService\GamesAndDexUpdaterService;
use Doctrine\ORM\EntityManagerInterface;

class UpdateGamesAndDexHandlerTest extends AbstractTestUpdateHandler
{
    public function getServiceClass(): string
    {
        return GamesAndDexUpdaterService::class;
    }

    /**
     * @param GamesAndDexUpdaterService $updaterService
    **/
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        return new UpdateGamesAndDexHandler(
            $updaterService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new UpdateGamesAndDex('12');
    }
}
