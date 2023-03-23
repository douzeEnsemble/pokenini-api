<?php

declare(strict_types=1);

namespace App\MessageHandler\Traits;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;

trait CalculateHandlerTrait
{
    use ActionEnderTrait;

    public function calculate(AbstractActionMessage $message): void
    {
        $this->calculatorService->execute();

        $report = $this->calculatorService->getReport();

        $this->endMessengerAction($message, $report);
    }
}
