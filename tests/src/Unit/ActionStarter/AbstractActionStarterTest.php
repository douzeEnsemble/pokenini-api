<?php

declare(strict_types=1);

namespace App\Tests\Unit\ActionStarter;

use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\UpdateLabelsActionStarter;
use App\Exception\ActionAlreadyRunningException;
use App\Message\UpdateLabels;
use App\Repository\ActionLogsRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(ActionAlreadyRunningException::class)]
final class AbstractActionStarterTest extends TestCase
{
    #[Test]
    public function startRefusesWhenAnActionIsAlreadyPending(): void
    {
        $repository = $this->createMock(ActionLogsRepository::class);
        $repository
            ->expects($this->once())
            ->method('hasPending')
            ->with(UpdateLabels::class, $this->isInstanceOf(\DateTimeInterface::class))
            ->willReturn(true)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $starter = new UpdateLabelsActionStarter($entityManager, $repository);

        try {
            $message = $starter->start();
        } catch (ActionAlreadyRunningException $exception) {
            $this->assertSame('Action already running: '.UpdateLabels::class, $exception->getMessage());

            return;
        }

        $this->fail('Expected ActionAlreadyRunningException, got '.$message::class);
    }
}
