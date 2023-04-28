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
        try {
            $this->calculatorService->execute();

            $report = $this->calculatorService->getReport();

            $this->endActionLog($message, $report);
        } catch (\Exception $e) {
            $this->endInErrorActionLog($message, $e->getMessage());

            throw $e;
        }
    }
}
