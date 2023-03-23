<?php

declare(strict_types=1);

namespace App\Message;

abstract class AbstractActionMessage implements ActionMessageInterface
{
    public function __construct(public readonly string $actionId)
    {
    }

    public function getActionId(): string
    {
        return $this->actionId;
    }
}
