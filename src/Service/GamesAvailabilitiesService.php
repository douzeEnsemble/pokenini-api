<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\GamesAvailabilities;
use App\Entity\Pokemon;
use App\Repository\GamesAvailabilitiesRepository;
use Symfony\Contracts\Cache\CacheInterface;

class GamesAvailabilitiesService
{
    public function __construct(
        private readonly GamesAvailabilitiesRepository $repository,
        private readonly CacheInterface $cache
    ) {
    }

    public function getFromPokemon(Pokemon $pokemon): GamesAvailabilities
    {
        $key = 'ga-' . $pokemon->slug;

        /** @var GamesAvailabilities */
        return $this->cache->get($key, function () use ($pokemon) {
            return $this->repository->getFromPokemon($pokemon);
        });
    }
}
