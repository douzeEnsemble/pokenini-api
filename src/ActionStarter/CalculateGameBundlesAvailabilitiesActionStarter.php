<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\CalculateGameBundlesAvailabilities;

final class CalculateGameBundlesAvailabilitiesActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return CalculateGameBundlesAvailabilities::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new CalculateGameBundlesAvailabilities($identifier);
    }
}
