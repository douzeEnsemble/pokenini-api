<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\DTO\DataChangeReport\Report;
use App\Entity\ActionLog;
use App\Message\AbstractActionMessage;
use App\MessageHandler\UpdateHandlerInterface;
use App\Repository\ActionLogsRepository;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestUpdateHandler extends TestCase
{
    /**
     * @return class-string
     */
    abstract public function getServiceClass(): string;

    abstract public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface;

    abstract public function getMessage(): AbstractActionMessage;

    public function testUpdate(): void
    {
        $updaterService = $this->createMock($this->getServiceClass());
        $updaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $updaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(new Report([]))
        ;

        $actionLog = new ActionLog('douze');

        $repository = $this->createMock(ActionLogsRepository::class);
        $repository
            ->expects($this->once())
            ->method('find')
            ->willReturn($actionLog)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository)
        ;
        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        /** @var UpdaterServiceInterface $updaterService */
        $handler = $this->getHandler($updaterService, $entityManager);

        $message = $this->getMessage();

        $handler->update($message);

        $this->assertEquals('[]', $actionLog->reportData);
        $this->assertInstanceOf(\DateTime::class, $actionLog->doneAt);
        $this->assertNull($actionLog->errorTrace);
    }

    public function testUpdateError(): void
    {
        $updaterService = $this->createMock($this->getServiceClass());
        $updaterService
            ->expects($this->once())
            ->method('execute')
            ->will(
                $this->throwException(
                    new \Exception('Ya un blèm !')
                )
            )
        ;

        $actionLog = new ActionLog('douze');

        $repository = $this->createMock(ActionLogsRepository::class);
        $repository
            ->expects($this->once())
            ->method('find')
            ->willReturn($actionLog)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository)
        ;
        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        /** @var UpdaterServiceInterface $updaterService */
        $handler = $this->getHandler($updaterService, $entityManager);

        $message = $this->getMessage();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ya un blèm !');

        $handler->update($message);

        $this->assertNull($actionLog->reportData);
        $this->assertInstanceOf(\DateTime::class, $actionLog->doneAt);
        $this->assertEquals('Ya un blèm !', $actionLog->errorTrace);
    }
}
