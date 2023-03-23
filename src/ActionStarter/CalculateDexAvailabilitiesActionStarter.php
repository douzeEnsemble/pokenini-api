<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\CalculateDexAvailabilities;

final class CalculateDexAvailabilitiesActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return CalculateDexAvailabilities::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new CalculateDexAvailabilities($identifier);
    }
}
