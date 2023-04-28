<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\UpdateRegionalDexNumbers;
use App\MessageHandler\UpdateRegionalDexNumbersHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\UpdaterServiceInterface;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use Doctrine\ORM\EntityManagerInterface;

class UpdateRegionalDexNumbersHandlerTest extends AbstractTestUpdateHandler
{
    public function getServiceClass(): string
    {
        return RegionalDexNumbersUpdaterService::class;
    }

    /**
     * @param RegionalDexNumbersUpdaterService $updaterService
    **/
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        return new UpdateRegionalDexNumbersHandler(
            $updaterService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new UpdateRegionalDexNumbers('12');
    }
}
