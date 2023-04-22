<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\GameBundlesAvailabilities;
use App\Entity\Pokemon;
use App\Repository\GameBundlesAvailabilitiesRepository;
use Symfony\Contracts\Cache\CacheInterface;

class GameBundlesAvailabilitiesService
{
    public function __construct(
        private readonly GameBundlesAvailabilitiesRepository $repository,
        private readonly CacheInterface $cache
    ) {
    }

    public function getFromPokemon(Pokemon $pokemon): GameBundlesAvailabilities
    {
        $key = 'gba-' . $pokemon->slug;

        /** @var GameBundlesAvailabilities */
        return $this->cache->get($key, function () use ($pokemon) {
            return $this->repository->getFromPokemon($pokemon);
        });
    }
}
