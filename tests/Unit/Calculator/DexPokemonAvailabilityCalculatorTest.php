<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexPokemonAvailabilityCalculator;
use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use PHPUnit\Framework\TestCase;

class DexPokemonAvailabilityCalculatorTest extends TestCase
{
    public function testCalculate(): void
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
            ->expects($this->once())
            ->method('getFromPokemon')
            ->willReturn(new GameBundlesShiniesAvailabilities([
                'redgreenblueyellow' => false,
            ]))
        ;

        $dex = new Dex();
        $dex->selectionRule = "p.slug == 'douze' and (ba?.redgreenblueyellow or bsa?.redgreenblueyellow)";

        $calculate = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
        );

        $dexAvailability = $calculate->calculate($dex, $pokemon);

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

        $dex = new Dex();
        $dex->selectionRule = 'true';

        $calculate = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
        );

        $dexAvailability = $calculate->calculate($dex, $pokemon);

        $this->assertInstanceOf(DexAvailability::class, $dexAvailability);
    }

    public function testCalculateWithoutRegularsValues(): void
    {
        $pokemon = new Pokemon();

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

        $dex = new Dex();
        $dex->selectionRule = 'bsa?.redgreenblueyellow';

        $calculate = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
        );

        $dexAvailability = $calculate->calculate($dex, $pokemon);

        $this->assertInstanceOf(DexAvailability::class, $dexAvailability);
    }

    public function testCalculateWithoutShiniesValues(): void
    {
        $pokemon = new Pokemon();

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
            ->expects($this->once())
            ->method('getFromPokemon')
            ->willReturn(new GameBundlesAvailabilities([
                'redgreenblueyellow' => false,
            ]))
        ;

        $gameBundlesShiniesAvailabilitiesService = $this->createMock(GameBundlesShiniesAvailabilitiesService::class);
        $gameBundlesShiniesAvailabilitiesService
            ->expects($this->never())
            ->method('getFromPokemon')
        ;

        $dex = new Dex();
        $dex->selectionRule = 'ba?.redgreenblueyellow';

        $calculate = new DexPokemonAvailabilityCalculator(
            $gameBundlesAvailabilitiesService,
            $gameBundlesShiniesAvailabilitiesService,
        );

        $dexAvailability = $calculate->calculate($dex, $pokemon);

        $this->assertNull($dexAvailability);
    }
}
