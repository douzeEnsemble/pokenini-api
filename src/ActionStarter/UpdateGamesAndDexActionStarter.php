<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\UpdateGamesAndDex;

final class UpdateGamesAndDexActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return UpdateGamesAndDex::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new UpdateGamesAndDex($identifier);
    }
}
