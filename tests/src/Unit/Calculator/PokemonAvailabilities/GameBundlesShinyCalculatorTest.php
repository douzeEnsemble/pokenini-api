<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator\PokemonAvailabilities;

use App\Calculator\PokemonAvailabilities\GameBundlesShinyCalculator;
use App\Repository\PokemonAvailabilitiesRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesShinyCalculator::class)]
final class GameBundlesShinyCalculatorTest extends TestCase
{
    #[Test]
    public function execute(): void
    {
        $repository = $this->createMock(PokemonAvailabilitiesRepository::class);
        $repository
            ->expects($this->once())
            ->method('removeAllByCategory')
            ->with('game_bundle_shiny')
        ;
        $repository
            ->expects($this->once())
            ->method('calculateGameBundleShiny')
            ->willReturn(12)
        ;

        $service = new GameBundlesShinyCalculator($repository);

        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(12, $statistic->count);
    }

    #[Test]
    public function executeTwice(): void
    {
        $repository = $this->createMock(PokemonAvailabilitiesRepository::class);
        $repository
            ->expects($this->exactly(2))
            ->method('removeAllByCategory')
            ->with('game_bundle_shiny')
        ;
        $repository
            ->expects($this->exactly(2))
            ->method('calculateGameBundleShiny')
            ->willReturn(12)
        ;

        $service = new GameBundlesShinyCalculator($repository);

        $service->execute();
        $firstCount = $service->getStatistic()->count;

        $service->execute();
        $this->assertEquals($firstCount, $service->getStatistic()->count);
    }
}
