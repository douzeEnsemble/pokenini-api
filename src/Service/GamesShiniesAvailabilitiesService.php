<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\GamesShiniesAvailabilities;
use App\Entity\Pokemon;
use App\Repository\GamesShiniesAvailabilitiesRepository;
use Symfony\Contracts\Cache\CacheInterface;

class GamesShiniesAvailabilitiesService
{
    public function __construct(
        private readonly GamesShiniesAvailabilitiesRepository $repository,
        private readonly CacheInterface $cache
    ) {
    }

    public function getFromPokemon(Pokemon $pokemon): GamesShiniesAvailabilities
    {
        $key = 'gsa-' . $pokemon->slug;

        /** @var GamesShiniesAvailabilities */
        return $this->cache->get($key, function () use ($pokemon) {
            return $this->repository->getFromPokemon($pokemon);
        });
    }
}
