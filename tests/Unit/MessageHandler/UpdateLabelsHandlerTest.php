<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\UpdateLabels;
use App\MessageHandler\UpdateLabelsHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\UpdaterServiceInterface;
use App\Service\UpdaterService\LabelsUpdaterService;
use Doctrine\ORM\EntityManagerInterface;

class UpdateLabelsHandlerTest extends AbstractTestUpdateHandler
{
    public function getServiceClass(): string
    {
        return LabelsUpdaterService::class;
    }

    /**
     * @param LabelsUpdaterService $updaterService
    **/
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        return new UpdateLabelsHandler(
            $updaterService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new UpdateLabels('12');
    }
}
