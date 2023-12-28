<?php

declare(strict_types=1);

namespace unit\Service;

use App\Entity\Pokemon;
use App\Repository\GameBundlesShiniesAvailabilitiesRepository;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

class GameBundlesShiniesAvailabilitiesServiceTest extends TestCase
{
    public function testCleanCacheFromPokemon(): void
    {
        $repository = $this->createMock(GameBundlesShiniesAvailabilitiesRepository::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('gbsa-azertyuiop'));
        ;

        $service = new GameBundlesShiniesAvailabilitiesService(
            $repository,
            $cache
        );

        $pokemon = new Pokemon();
        $pokemon->slug = 'azertyuiop';

        $service->cleanCacheFromPokemon($pokemon);
    }
}
