<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AbstractActionMessage;

interface UpdateHandlerInterface
{
    public function update(AbstractActionMessage $message): void;
}
