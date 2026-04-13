<?php

declare(strict_types=1);

namespace App\ActionEnder;

use App\DTO\DataChangeReport\Report;
use App\Entity\ActionLog;
use App\Message\ActionMessageInterface;
use App\Repository\ActionLogsRepository;
use Doctrine\ORM\EntityManagerInterface;

trait ActionEnderTrait
{
    private readonly EntityManagerInterface $entityManager;

    private function endInErrorActionLog(
        ActionMessageInterface $message,
        string $errorTrace,
    ): void {
        $actionLog = $this->findActionLog($message);

        $actionLog->errorTrace = $errorTrace;

        $this->crimpActionLog($actionLog);
    }

    private function endActionLog(
        ActionMessageInterface $message,
        Report $report,
    ): void {
        $actionLog = $this->findActionLog($message);

        $json = json_encode($report);
        $actionLog->reportData = false === $json ? '' : $json;

        $this->crimpActionLog($actionLog);
    }

    private function findActionLog(
        ActionMessageInterface $message
    ): ActionLog {
        /** @var ActionLogsRepository $repo */
        $repo = $this->entityManager->getRepository(ActionLog::class);

        $actionLog = $repo->find($message->getActionId());

        if (null === $actionLog) {
            throw new \RuntimeException("Can't find ActionLog #{$message->getActionId()}");
        }

        return $actionLog;
    }

    private function crimpActionLog(ActionLog $actionLog): void
    {
        $actionLog->doneAt = new \DateTime();

        $this->entityManager->flush();
    }
}
