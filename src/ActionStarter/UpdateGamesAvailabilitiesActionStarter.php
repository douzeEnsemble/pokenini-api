<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\UpdateGamesAvailabilities;

final class UpdateGamesAvailabilitiesActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return UpdateGamesAvailabilities::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new UpdateGamesAvailabilities($identifier);
    }
}
