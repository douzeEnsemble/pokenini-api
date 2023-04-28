<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\AbstractActionMessage;
use App\Message\UpdatePokemons;
use App\MessageHandler\UpdatePokemonsHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\UpdaterServiceInterface;
use App\Service\UpdaterService\PokemonsUpdaterService;
use Doctrine\ORM\EntityManagerInterface;

class UpdatePokemonsHandlerTest extends AbstractTestUpdateHandler
{
    public function getServiceClass(): string
    {
        return PokemonsUpdaterService::class;
    }

    /**
     * @param PokemonsUpdaterService $updaterService
    **/
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        return new UpdatePokemonsHandler(
            $updaterService,
            $entityManager,
        );
    }

    public function getMessage(): AbstractActionMessage
    {
        return new UpdatePokemons('12');
    }
}
