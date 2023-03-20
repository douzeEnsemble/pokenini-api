<?php

declare(strict_types=1);

namespace App\MessageHandler\Traits;

use App\Message\AbstractActionMessage;

trait UpdateHandlerTrait
{
    use ActionHandlerTrait;

    public function update(AbstractActionMessage $message): void
    {
        $this->updaterService->execute();

        $report = $this->updaterService->getReport();

        $this->saveMessengerAction($message, $report);
    }
}
