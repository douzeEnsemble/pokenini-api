<?php

declare(strict_types=1);

namespace App\MessageHandler\Traits;

use App\Message\AbstractActionMessage;

trait CalculateHandlerTrait
{
    use ActionHandlerTrait;

    public function calculate(AbstractActionMessage $message): void
    {
        $this->calculatorService->execute();

        $report = $this->calculatorService->getReport();

        $this->saveMessengerAction($message, $report);
    }
}
