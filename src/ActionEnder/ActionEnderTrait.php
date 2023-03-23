<?php

declare(strict_types=1);

namespace App\ActionEnder;

use App\DTO\DataChangeReport\Report;
use App\Entity\ActionLog;
use App\Message\ActionMessageInterface;
use App\Repository\ActionLogsRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

trait ActionEnderTrait
{
    private readonly EntityManagerInterface $entityManager;

    protected function endActionLog(
        ActionMessageInterface $message,
        Report $report
    ): void {
        /** @var ActionLogsRepository $repo */
        $repo = $this->entityManager->getRepository(ActionLog::class);

        /** @var ?ActionLog $actionLog */
        $actionLog = $repo->find($message->getActionId());

        if (null === $actionLog) {
            throw new RuntimeException("Can't find ActionLog #{$message->getActionId()}");
        }

        $actionLog->reportData = (string) json_encode($report);
        $actionLog->doneAt = new \DateTime();

        $this->entityManager->flush();
    }
}
