<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\GameBundlesAvailabilities;
use App\Entity\Pokemon;
use App\Repository\GameBundlesAvailabilitiesRepository;
use App\Service\GameBundlesAvailabilitiesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @internal
 */
#[CoversClass(GameBundlesAvailabilitiesService::class)]
final class GameBundlesAvailabilitiesServiceTest extends TestCase
{
    #[Test]
    public function getFromPokemonWithCacheHit(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'pikachu';

        $expectedResult = new GameBundlesAvailabilities([]);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('gba-pikachu')
            ->willReturn($expectedResult)
        ;

        $repository = $this->createMock(GameBundlesAvailabilitiesRepository::class);
        $repository->expects($this->never())
            ->method('getFromPokemon')
        ;

        $service = new GameBundlesAvailabilitiesService($repository, $cache);

        $result = $service->getFromPokemon($pokemon);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function getFromPokemonWithCacheMiss(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'charizard';

        $expectedResult = new GameBundlesAvailabilities([]);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('gba-charizard')
            ->willReturnCallback(function (string $key, callable $callback): mixed {
                unset($key); // To remove PHPMD.UnusedFormalParameter warning

                return $callback();
            })
        ;

        $repository = $this->createMock(GameBundlesAvailabilitiesRepository::class);
        $repository->expects($this->once())
            ->method('getFromPokemon')
            ->willReturn($expectedResult)
        ;

        $service = new GameBundlesAvailabilitiesService($repository, $cache);

        $result = $service->getFromPokemon($pokemon);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function cleanCacheFromPokemon(): void
    {
        $repository = $this->createMock(GameBundlesAvailabilitiesRepository::class);
        $repository
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('gba-azertyuiop'))
        ;

        $service = new GameBundlesAvailabilitiesService(
            $repository,
            $cache
        );

        $pokemon = new Pokemon();
        $pokemon->slug = 'azertyuiop';

        $service->cleanCacheFromPokemon($pokemon);
    }
}
