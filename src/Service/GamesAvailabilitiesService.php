<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\GamesAvailabilities;
use App\Entity\Pokemon;
use App\Repository\GamesAvailabilitiesRepository;
use Symfony\Contracts\Cache\CacheInterface;

class GamesAvailabilitiesService
{
    private const string CACHE_PREFIX = 'ga-';

    public function __construct(
        private readonly GamesAvailabilitiesRepository $repository,
        private readonly CacheInterface $cache
    ) {}

    public function getFromPokemon(Pokemon $pokemon): GamesAvailabilities
    {
        $key = $this->getCacheKey($pokemon);

        /** @var GamesAvailabilities */
        return $this->cache->get($key, function () use ($pokemon) {
            return $this->repository->getFromPokemon($pokemon);
        });
    }

    public function cleanCacheFromPokemon(Pokemon $pokemon): void
    {
        $this->cache->delete($this->getCacheKey($pokemon));
    }

    private function getCacheKey(Pokemon $pokemon): string
    {
        return self::CACHE_PREFIX.$pokemon->slug;
    }
}
