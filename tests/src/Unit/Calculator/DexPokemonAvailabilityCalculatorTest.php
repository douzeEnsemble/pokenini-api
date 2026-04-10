<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexPokemonAvailabilityCalculator;
use App\DTO\GameBundlesAvailabilities;
use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexPokemonAvailabilityCalculator::class)]
final class DexPokemonAvailabilityCalculatorTest extends TestCase
{
    public function testCalculateNotAvailable(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'douze';

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
            ->expects($this->once())
            ->method('getFromPokemon')
            ->willReturn(new GameBundlesAvailabilities([
                'redgreenblueyellow' => true,
            ]))
        ;

        $gameBundlesShiniesAvailabilitiesService = $this->createMock(GameBundlesShiniesAvailabilitiesService::class);
        $gameBundlesShiniesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gamesAvailabilitiesService = $this->createMock(GamesAvailabilitiesService::class);
        $gamesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gamesShiniesAvailabilitiesService = $this->createMock(GamesShiniesAvailabilitiesService::class);
        $gamesShiniesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $collectionAvailabilitiesService = $this->createMock(CollectionsAvailabilitiesService::class);
        $collectionAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $dex = new Dex();
        $dex->selectionRule = <<<'RULE'
                p.slug == 'douze'
                and ba?.redgreenblueyellow
            RULE;

        $calculator = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
            $gamesAvailabilitiesService,
            $gamesShiniesAvailabilitiesService,
            $collectionAvailabilitiesService,
        );

        $dexAvailability = $calculator->calculate($dex, $pokemon);

        $this->assertInstanceOf(DexAvailability::class, $dexAvailability);
    }

    public function testCalculateWithoutValues(): void
    {
        $pokemon = new Pokemon();

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gameBundlesShiniesAvailabilitiesService = $this->createMock(GameBundlesShiniesAvailabilitiesService::class);
        $gameBundlesShiniesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gamesAvailabilitiesService = $this->createMock(GamesAvailabilitiesService::class);
        $gamesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gamesShiniesAvailabilitiesService = $this->createMock(GamesShiniesAvailabilitiesService::class);
        $gamesShiniesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $collectionAvailabilitiesService = $this->createMock(CollectionsAvailabilitiesService::class);
        $collectionAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $dex = new Dex();
        $dex->selectionRule = 'true';

        $calculator = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
            $gamesAvailabilitiesService,
            $gamesShiniesAvailabilitiesService,
            $collectionAvailabilitiesService,
        );

        $dexAvailability = $calculator->calculate($dex, $pokemon);

        $this->assertInstanceOf(DexAvailability::class, $dexAvailability);
    }

    public function testCalculateWithoutValuesFalse(): void
    {
        $pokemon = new Pokemon();

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gameBundlesShiniesAvailabilitiesService = $this->createMock(GameBundlesShiniesAvailabilitiesService::class);
        $gameBundlesShiniesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gamesAvailabilitiesService = $this->createMock(GamesAvailabilitiesService::class);
        $gamesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gamesShiniesAvailabilitiesService = $this->createMock(GamesShiniesAvailabilitiesService::class);
        $gamesShiniesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $collectionAvailabilitiesService = $this->createMock(CollectionsAvailabilitiesService::class);
        $collectionAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $dex = new Dex();
        $dex->selectionRule = 'false';

        $calculator = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
            $gamesAvailabilitiesService,
            $gamesShiniesAvailabilitiesService,
            $collectionAvailabilitiesService,
        );

        $dexAvailability = $calculator->calculate($dex, $pokemon);

        $this->assertNull($dexAvailability);
    }
}
