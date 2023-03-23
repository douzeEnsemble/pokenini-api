<?php

declare(strict_types=1);

namespace App\ActionEnder;

use App\DTO\DataChangeReport\Report;
use App\Entity\MessengerAction;
use App\Message\ActionMessageInterface;
use App\Repository\MessengerActionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

trait ActionEnderTrait
{
    private readonly EntityManagerInterface $entityManager;

    protected function endMessengerAction(
        ActionMessageInterface $message,
        Report $report
    ): void {
        /** @var MessengerActionsRepository $repo */
        $repo = $this->entityManager->getRepository(MessengerAction::class);

        /** @var ?MessengerAction $messengerAction */
        $messengerAction = $repo->find($message->getActionId());

        if (null === $messengerAction) {
            throw new RuntimeException("Can't find MessengerAction #{$message->getActionId()}");
        }

        $messengerAction->reportData = (string) json_encode($report);
        $messengerAction->doneAt = new \DateTime();

        $this->entityManager->flush();
    }
}
