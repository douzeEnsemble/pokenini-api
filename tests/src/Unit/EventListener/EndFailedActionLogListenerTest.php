<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\EndFailedActionLogListener;
use App\Message\UpdateLabels;
use App\Repository\ActionLogsRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * @internal
 */
#[CoversClass(EndFailedActionLogListener::class)]
final class EndFailedActionLogListenerTest extends TestCase
{
    #[Test]
    public function endsTheActionLogWithTheErrorOfTheHandler(): void
    {
        $envelope = new Envelope(new UpdateLabels('douze'));

        $repository = $this->createMock(ActionLogsRepository::class);
        $repository
            ->expects($this->once())
            ->method('endPendingInError')
            ->with('douze', 'TypeError: Ya un blèm !')
        ;

        new EndFailedActionLogListener($repository)(new WorkerMessageFailedEvent(
            $envelope,
            'async',
            new HandlerFailedException($envelope, [new \TypeError('Ya un blèm !')]),
        ));
    }

    #[Test]
    public function endsTheActionLogWithAnErrorRaisedOutsideTheHandler(): void
    {
        $repository = $this->createMock(ActionLogsRepository::class);
        $repository
            ->expects($this->once())
            ->method('endPendingInError')
            ->with('douze', 'RuntimeException: Ya un blèm !')
        ;

        new EndFailedActionLogListener($repository)(new WorkerMessageFailedEvent(
            new Envelope(new UpdateLabels('douze')),
            'async',
            new \RuntimeException('Ya un blèm !'),
        ));
    }

    #[Test]
    public function waitsWhenTheMessageWillBeRetried(): void
    {
        $repository = $this->createMock(ActionLogsRepository::class);
        $repository->expects($this->never())->method('endPendingInError');

        $event = new WorkerMessageFailedEvent(new Envelope(new UpdateLabels('douze')), 'async', new \RuntimeException());
        $event->setForRetry();

        new EndFailedActionLogListener($repository)($event);
    }

    #[Test]
    public function ignoresTheMessagesWithoutActionLog(): void
    {
        $repository = $this->createMock(ActionLogsRepository::class);
        $repository->expects($this->never())->method('endPendingInError');

        new EndFailedActionLogListener($repository)(
            new WorkerMessageFailedEvent(new Envelope(new \stdClass()), 'async', new \RuntimeException()),
        );
    }
}
