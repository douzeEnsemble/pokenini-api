<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\CalculateGameBundlesShiniesAvailabilities;

final class CalculateGameBundlesShiniesAvailabilitiesActionStarter extends AbstractActionStarter
{
    #[\Override]
    protected function getMessageClass(): string
    {
        return CalculateGameBundlesShiniesAvailabilities::class;
    }

    #[\Override]
    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new CalculateGameBundlesShiniesAvailabilities($identifier);
    }
}
