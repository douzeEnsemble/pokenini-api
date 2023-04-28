<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\UpdateGamesShiniesAvailabilities;
use App\MessageHandler\UpdateGamesShiniesAvailabilitiesHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\UpdaterServiceInterface;
use App\Service\UpdaterService\GamesShiniesAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;

class UpdateGamesShiniesAvailabilitiesHandlerTest extends AbstractTestUpdateHandler
{
    public function getServiceClass(): string
    {
        return GamesShiniesAvailabilitiesUpdaterService::class;
    }

    /**
     * @param GamesShiniesAvailabilitiesUpdaterService $updaterService
    **/
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        return new UpdateGamesShiniesAvailabilitiesHandler(
            $updaterService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new UpdateGamesShiniesAvailabilities('12');
    }
}
