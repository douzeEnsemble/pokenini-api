<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateLabels;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\LabelsUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateLabelsHandler
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly LabelsUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(UpdateLabels $message): void
    {
        $this->update($message);
    }
}
