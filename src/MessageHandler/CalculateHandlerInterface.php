<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AbstractActionMessage;

interface CalculateHandlerInterface
{
    public function calculate(AbstractActionMessage $message): void;
}
