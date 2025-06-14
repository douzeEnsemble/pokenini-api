<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CollectionsAvailabilities;
use App\Entity\Pokemon;
use App\Repository\CollectionsAvailabilitiesRepository;
use Symfony\Contracts\Cache\CacheInterface;

class CollectionsAvailabilitiesService
{
    private const string CACHE_PREFIX = 'ca-';

    public function __construct(
        private readonly CollectionsAvailabilitiesRepository $repository,
        private readonly CacheInterface $cache
    ) {}

    public function getFromPokemon(Pokemon $pokemon): CollectionsAvailabilities
    {
        $key = $this->getCacheKey($pokemon);

        /** @var CollectionsAvailabilities */
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
