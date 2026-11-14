<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Entity\ActionLog;
use App\Exception\ActionAlreadyRunningException;
use App\Message\ActionMessageInterface;
use App\Repository\ActionLogsRepository;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractActionStarter implements ActionStarterInterface
{
    /**
     * An unfinished ActionLog older than this is considered abandoned (crashed worker)
     * and no longer blocks a new run.
     */
    private const string PENDING_TTL = '1 hour';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ActionLogsRepository $actionLogsRepository,
    ) {}

    #[\Override]
    public function start(): ActionMessageInterface
    {
        if ($this->actionLogsRepository->hasPending(
            $this->getMessageClass(),
            new \DateTimeImmutable('-'.self::PENDING_TTL)
        )) {
            throw new ActionAlreadyRunningException($this->getMessageClass());
        }

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
