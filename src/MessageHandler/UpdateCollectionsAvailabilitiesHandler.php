<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateCollectionsAvailabilities;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\CollectionsAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateCollectionsAvailabilitiesHandler implements UpdateHandlerInterface
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly CollectionsAvailabilitiesUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(UpdateCollectionsAvailabilities $message): void
    {
        $this->update($message);
    }
}
