<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(409)]
final class ActionAlreadyRunningException extends \RuntimeException
{
    public function __construct(string $messageClass)
    {
        parent::__construct("Action already running: {$messageClass}");
    }
}
