<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateGamesCollectionsAndDex;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\GamesCollectionsAndDexUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateGamesCollectionsAndDexHandler implements UpdateHandlerInterface
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly GamesCollectionsAndDexUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(UpdateGamesCollectionsAndDex $message): void
    {
        $this->update($message);
    }
}
