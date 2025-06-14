<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexPokemonAvailabilityCalculator;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\Entity\Dex;
use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexPokemonAvailabilityCalculator::class)]
class DexPokemonAvailabilityCalculatorGameBundlesShiniesTest extends TestCase
{
    #[DataProvider('providerCalculateIncludingGameBundlesShiniesValues')]
    public function testCalculateIncludingGameBundlesShiniesValues(string $rule): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'pikachu';

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $gameBundlesShiniesAvailabilitiesService = $this->createMock(GameBundlesShiniesAvailabilitiesService::class);
        $gameBundlesShiniesAvailabilitiesService
            ->expects($this->once())
            ->method('getFromPokemon')
            ->willReturn(new GameBundlesShiniesAvailabilities([
                'redgreenblueyellow' => true,
            ]))
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
        $dex->selectionRule = $rule;

        $calculator = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
            $gamesAvailabilitiesService,
            $gamesShiniesAvailabilitiesService,
            $collectionAvailabilitiesService,
        );

        $dexAvailability = $calculator->calculate($dex, $pokemon);

        $this->assertNotNull($dexAvailability);
    }

    /**
     * @return string[][]
     */
    public static function providerCalculateIncludingGameBundlesShiniesValues(): array
    {
        return [
            'bsa' => [
                'bsa.redgreenblueyellow',
            ],
            'bsa?' => [
                'bsa?.redgreenblueyellow',
            ],
            'bsa_bsa?' => [
                'bsa.redgreenblueyellow or bsa?.redgreenblueyellow',
            ],
        ];
    }
}
