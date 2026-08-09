<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\GamesShiniesAvailabilities;
use App\Entity\Pokemon;
use App\Repository\GamesShiniesAvailabilitiesRepository;
use App\Service\GamesShiniesAvailabilitiesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @internal
 */
#[CoversClass(GamesShiniesAvailabilitiesService::class)]
final class GamesShiniesAvailabilitiesServiceTest extends TestCase
{
    #[Test]
    public function getFromPokemonWithCacheHit(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'pikachu';

        $expectedResult = new GamesShiniesAvailabilities([]);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('gsa-pikachu')
            ->willReturn($expectedResult)
        ;

        $repository = $this->createMock(GamesShiniesAvailabilitiesRepository::class);
        $repository->expects($this->never())
            ->method('getFromPokemon')
        ;

        $service = new GamesShiniesAvailabilitiesService($repository, $cache);

        $result = $service->getFromPokemon($pokemon);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function getFromPokemonWithCacheMiss(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'charizard';

        $expectedResult = new GamesShiniesAvailabilities([]);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('gsa-charizard')
            ->willReturnCallback(function (string $key, callable $callback): mixed {
                unset($key); // To remove PHPMD.UnusedFormalParameter warning

                return $callback();
            })
        ;

        $repository = $this->createMock(GamesShiniesAvailabilitiesRepository::class);
        $repository->expects($this->once())
            ->method('getFromPokemon')
            ->willReturn($expectedResult)
        ;

        $service = new GamesShiniesAvailabilitiesService($repository, $cache);

        $result = $service->getFromPokemon($pokemon);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function cleanCacheFromPokemon(): void
    {
        $repository = $this->createMock(GamesShiniesAvailabilitiesRepository::class);
        $repository
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('gsa-azertyuiop'))
        ;

        $service = new GamesShiniesAvailabilitiesService(
            $repository,
            $cache
        );

        $pokemon = new Pokemon();
        $pokemon->slug = 'azertyuiop';

        $service->cleanCacheFromPokemon($pokemon);
    }
}
