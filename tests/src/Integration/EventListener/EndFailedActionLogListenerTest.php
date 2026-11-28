<?php

declare(strict_types=1);

namespace App\Tests\Integration\EventListener;

use App\EventListener\EndFailedActionLogListener;
use App\Message\UpdateLabels;
use App\Repository\ActionLogsRepository;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Tests\Common\Traits\GetterTrait\GetActionLogTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * @internal
 */
#[CoversClass(EndFailedActionLogListener::class)]
#[CoversClass(ActionLogsRepository::class)]
final class EndFailedActionLogListenerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetActionLogTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * The handler only catches exceptions: an Error left the action unfinished.
     */
    #[Test]
    public function anErrorOfTheHandlerEndsTheActionLog(): void
    {
        $updaterService = $this->createStub(LabelsUpdaterService::class);
        $updaterService->method('execute')->willThrowException(new \TypeError('Ya un blèm !'));
        self::getContainer()->set(LabelsUpdaterService::class, $updaterService);

        $actionId = $this->getIdToProcess(UpdateLabels::class);

        self::getMessengerTransport('async')->send(new Envelope(new UpdateLabels($actionId)));

        try {
            self::consumeQueuedMessages('async', 1);
            $this->fail('The failure of the handler should be rethrown');
        } catch (HandlerFailedException $e) {
            $this->assertInstanceOf(\TypeError::class, $e->getPrevious());
        }

        $actionLog = $this->getActionLog($actionId);
        $this->assertNotNull($actionLog['done_at']);
        $this->assertSame('TypeError: Ya un blèm !', $actionLog['error_trace']);
    }

    /**
     * A database error closes the entity manager: the action must still be ended.
     */
    #[Test]
    public function endsTheActionLogWhenTheEntityManagerIsClosed(): void
    {
        $actionId = $this->getIdToProcess(UpdateLabels::class);
        self::getContainer()->get(EntityManagerInterface::class)->close();

        self::getContainer()->get(ActionLogsRepository::class)->endPendingInError($actionId, 'Closed');

        $actionLog = $this->getActionLog($actionId);
        $this->assertNotNull($actionLog['done_at']);
        $this->assertSame('Closed', $actionLog['error_trace']);
    }

    #[Test]
    public function keepsTheReportOfAnEndedActionLog(): void
    {
        $repository = self::getContainer()->get(ActionLogsRepository::class);
        $actionId = $this->getIdToProcess(UpdateLabels::class);

        $repository->endPendingInError($actionId, 'First');
        $firstDoneAt = $this->getActionLog($actionId)['done_at'];
        $this->assertNotNull($firstDoneAt);
        $repository->endPendingInError($actionId, 'Second');

        $actionLog = $this->getActionLog($actionId);
        $this->assertSame('First', $actionLog['error_trace']);
        $this->assertSame($firstDoneAt, $actionLog['done_at']);
    }

    /**
     * @return array<string, null|string>
     */
    private function getActionLog(string $actionId): array
    {
        $row = self::getContainer()->get(Connection::class)->fetchAssociative(
            'SELECT done_at, error_trace FROM action_log WHERE id = :id',
            ['id' => $actionId],
        );
        $this->assertIsArray($row);

        /** @var array<string, null|string> $row */
        return $row;
    }
}
