<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\CalculatePokemonAvailabilities;

final class CalculatePokemonAvailabilitiesActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return CalculatePokemonAvailabilities::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new CalculatePokemonAvailabilities($identifier);
    }
}
