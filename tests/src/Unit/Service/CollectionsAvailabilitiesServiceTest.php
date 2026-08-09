<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\CollectionsAvailabilities;
use App\Entity\Pokemon;
use App\Repository\CollectionsAvailabilitiesRepository;
use App\Service\CollectionsAvailabilitiesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @internal
 */
#[CoversClass(CollectionsAvailabilitiesService::class)]
final class CollectionsAvailabilitiesServiceTest extends TestCase
{
    #[Test]
    public function getFromPokemonWithCacheHit(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'pikachu';

        $expectedResult = new CollectionsAvailabilities([]);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('ca-pikachu')
            ->willReturn($expectedResult)
        ;

        $repository = $this->createMock(CollectionsAvailabilitiesRepository::class);
        $repository->expects($this->never())
            ->method('getFromPokemon')
        ;

        $service = new CollectionsAvailabilitiesService($repository, $cache);

        $result = $service->getFromPokemon($pokemon);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function getFromPokemonWithCacheMiss(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'charizard';

        $expectedResult = new CollectionsAvailabilities([]);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('ca-charizard')
            ->willReturnCallback(function (string $key, callable $callback): mixed {
                unset($key); // To remove PHPMD.UnusedFormalParameter warning

                return $callback();
            })
        ;

        $repository = $this->createMock(CollectionsAvailabilitiesRepository::class);
        $repository->expects($this->once())
            ->method('getFromPokemon')
            ->willReturn($expectedResult)
        ;

        $service = new CollectionsAvailabilitiesService($repository, $cache);

        $result = $service->getFromPokemon($pokemon);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function cleanCacheFromPokemon(): void
    {
        $repository = $this->createMock(CollectionsAvailabilitiesRepository::class);
        $repository
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('ca-azertyuiop'))
        ;

        $service = new CollectionsAvailabilitiesService(
            $repository,
            $cache
        );

        $pokemon = new Pokemon();
        $pokemon->slug = 'azertyuiop';

        $service->cleanCacheFromPokemon($pokemon);
    }
}
