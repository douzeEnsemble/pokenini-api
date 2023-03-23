<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Entity\ActionLog;
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
        $actionLog = new ActionLog(
            $this->getMessageClass()
        );

        $this->entityManager->persist($actionLog);
        $this->entityManager->flush();

        return $this->instanciate(
            (string) $actionLog->getIdentifier()
        );
    }

    abstract protected function getMessageClass(): string;
    abstract protected function instanciate(string $identifier): ActionMessageInterface;
}
