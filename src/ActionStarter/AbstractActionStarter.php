<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Entity\MessengerAction;
use App\Message\ActionMessageInterface;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractActionStarter implements ActionStarterInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function start(): ActionMessageInterface
    {
        $messengerAction = new MessengerAction(
            $this->getMessageClass()
        );

        $this->entityManager->persist($messengerAction);
        $this->entityManager->flush();

        return $this->instanciate(
            (string) $messengerAction->getIdentifier()
        );
    }

    abstract protected function getMessageClass(): string;
    abstract protected function instanciate(string $identifier): ActionMessageInterface;
}
