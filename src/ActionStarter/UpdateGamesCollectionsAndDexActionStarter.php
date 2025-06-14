<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\UpdateGamesCollectionsAndDex;

final class UpdateGamesCollectionsAndDexActionStarter extends AbstractActionStarter
{
    #[\Override]
    protected function getMessageClass(): string
    {
        return UpdateGamesCollectionsAndDex::class;
    }

    #[\Override]
    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new UpdateGamesCollectionsAndDex($identifier);
    }
}
