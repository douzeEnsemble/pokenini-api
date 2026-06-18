<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\DTO\DataChangeReport\Report;
use App\Entity\ActionLog;
use App\Message\AbstractActionMessage;
use App\MessageHandler\CalculateHandlerInterface;
use App\Repository\ActionLogsRepository;
use App\Service\CalculatorService\CalculatorServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCalculateHandler extends TestCase
{
    /**
     * @return class-string
     */
    abstract public function getServiceClass(): string;

    abstract public function getHandler(
        CalculatorServiceInterface $calculatorService,
        EntityManagerInterface $entityManager,
    ): CalculateHandlerInterface;

    abstract public function getMessage(): AbstractActionMessage;

    public function testCalculate(): void
    {
        $calculatorService = $this->createMock($this->getServiceClass());
        $calculatorService
            ->expects($this->once())
            ->method('execute')
        ;
        $calculatorService
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

        /**
         * @var CalculatorServiceInterface|MockObject $calculatorService
         *
         * @psalm-suppress PossiblyInvalidArgument
         */
        $handler = $this->getHandler($calculatorService, $entityManager);

        $message = $this->getMessage();

        $handler->calculate($message);

        $this->assertEquals('[]', $actionLog->reportData);
        $this->assertInstanceOf(\DateTime::class, $actionLog->doneAt);
        $this->assertNull($actionLog->errorTrace);
    }

    public function testCalculateError(): void
    {
        $calculatorService = $this->createMock($this->getServiceClass());
        $calculatorService
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

        /**
         * @var CalculatorServiceInterface|MockObject $calculatorService
         *
         * @psalm-suppress PossiblyInvalidArgument
         */
        $handler = $this->getHandler($calculatorService, $entityManager);

        $message = $this->getMessage();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIsOrContains('Ya un blèm !');

        $handler->calculate($message);

        $this->assertNull($actionLog->reportData);
        $this->assertInstanceOf(\DateTime::class, $actionLog->doneAt);
        $this->assertEquals('Ya un blèm !', $actionLog->errorTrace);
    }
}
