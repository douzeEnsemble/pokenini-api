<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdatePokemons;
use App\MessageHandler\Traits\UpdateHandlerTrait;
use App\Service\UpdaterService\PokemonsUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdatePokemonsHandler
{
    use UpdateHandlerTrait;

    public function __construct(
        private readonly PokemonsUpdaterService $updaterService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(UpdatePokemons $message): void
    {
        $this->update($message);
    }
}
