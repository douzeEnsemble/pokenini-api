<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateGamesShiniesAvailabilities;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\GamesShiniesAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateGamesShiniesAvailabilitiesHandler implements UpdateHandlerInterface
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly GamesShiniesAvailabilitiesUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(UpdateGamesShiniesAvailabilities $message): void
    {
        $this->update($message);
    }
}
