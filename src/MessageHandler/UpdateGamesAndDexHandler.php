<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateGamesAndDex;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\GamesAndDexUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateGamesAndDexHandler implements UpdateHandlerInterface
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly GamesAndDexUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(UpdateGamesAndDex $message): void
    {
        $this->update($message);
    }
}
