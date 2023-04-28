<?php

declare(strict_types=1);

namespace App\MessageHandler\Traits;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;

trait UpdateHandlerTrait
{
    use ActionEnderTrait;

    public function update(AbstractActionMessage $message): void
    {
        try {
            $this->updaterService->execute();

            $report = $this->updaterService->getReport();

            $this->endActionLog($message, $report);
        } catch (\Exception $e) {
            $this->endInErrorActionLog($message, $e->getMessage());

            throw $e;
        }
    }
}
