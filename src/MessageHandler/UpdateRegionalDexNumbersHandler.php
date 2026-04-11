<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateRegionalDexNumbers;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateRegionalDexNumbersHandler implements UpdateHandlerInterface
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly RegionalDexNumbersUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(UpdateRegionalDexNumbers $message): void
    {
        $this->update($message);
    }
}
