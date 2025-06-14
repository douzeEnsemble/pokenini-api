<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateGamesAvailabilities;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateGamesAvailabilitiesHandler implements UpdateHandlerInterface
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly GamesAvailabilitiesUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(UpdateGamesAvailabilities $message): void
    {
        $this->update($message);
    }
}
