<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\UpdatePokemons;

final class UpdatePokemonsActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return UpdatePokemons::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new UpdatePokemons($identifier);
    }
}
