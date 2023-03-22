<?php

declare(strict_types=1);

namespace App\MessageHandler\Traits;

use App\DTO\DataChangeReport\Report;
use App\Entity\MessengerAction;
use App\Message\AbstractActionMessage;
use App\Repository\MessengerActionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

trait ActionHandlerTrait
{
    private readonly EntityManagerInterface $entityManager;

    private function saveMessengerAction(
        AbstractActionMessage $message,
        Report $report
    ): void {
        /** @var MessengerActionsRepository $repo */
        $repo = $this->entityManager->getRepository(MessengerAction::class);

        /** @var ?MessengerAction $messengerAction */
        $messengerAction = $repo->find($message->actionId);

        if (null === $messengerAction) {
            throw new RuntimeException("Can't find MessengerAction #{$message->actionId}");
        }

        $messengerAction->reportData = (string) json_encode($report);
        $messengerAction->doneAt = new \DateTime();

        $this->entityManager->flush();
    }
}
