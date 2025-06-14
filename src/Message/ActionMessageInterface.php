<?php

declare(strict_types=1);

namespace App\Message;

interface ActionMessageInterface
{
    public function getActionId(): string;
}
