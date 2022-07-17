<?php

namespace App\Service;

use App\DTO\GameBundlesAvailabilities;
use App\Entity\Pokemon;
use App\Repository\GameBundleAvailabilityRepository;
use Symfony\Contracts\Cache\CacheInterface;

class GameBundleAvailabilityService
{
    public function __construct(
        private GameBundleAvailabilityRepository $gameBundleAvailabilityRepository,
        private CacheInterface $cache
    ) {
    }

    public function getFromPokemon(Pokemon $pokemon): GameBundlesAvailabilities
    {
        return $this->cache->get($pokemon->slug, function () use ($pokemon) {
            return $this->gameBundleAvailabilityRepository->getFromPokemon($pokemon);
        });
    }
}
