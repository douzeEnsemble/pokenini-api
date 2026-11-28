<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Message\ActionMessageInterface;
use App\Repository\ActionLogsRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Safety net of the handlers: they end their ActionLog on an Exception, but not on an Error, nor when
 * ending it fails. Without this, the action stays unfinished and blocks a new run for an hour.
 *
 * The priority lets Messenger send the message to the failure transport first.
 */
#[AsEventListener(priority: -200)]
final class EndFailedActionLogListener
{
    public function __construct(
        private readonly ActionLogsRepository $actionLogsRepository,
    ) {}

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if ($event->willRetry() || !$message instanceof ActionMessageInterface) {
            return;
        }

        $error = $event->getThrowable();
        if ($error instanceof HandlerFailedException) {
            $error = $error->getPrevious() ?? $error;
        }

        $this->actionLogsRepository->endPendingInError(
            $message->getActionId(),
            $error::class.': '.$error->getMessage(),
        );
    }
}
