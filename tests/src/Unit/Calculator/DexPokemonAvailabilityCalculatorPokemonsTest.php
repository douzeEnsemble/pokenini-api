<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexPokemonAvailabilityCalculator;
use App\Entity\Dex;
use App\Entity\GameGeneration;
use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexPokemonAvailabilityCalculator::class)]
final class DexPokemonAvailabilityCalculatorPokemonsTest extends TestCase
{
    #[Test]
    #[DataProvider('providerCalculateIncludingPokemonsValues')]
    public function calculateIncludingPokemonsValues(string $rule): void
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
    public static function providerCalculateIncludingPokemonsValues(): array
    {
        return [
            'p' => [
                'p.slug == "pikachu"',
            ],
            'p?' => [
                'p?.slug == "pikachu"',
            ],
            'p_p?' => [
                'p.slug == "pikachu" or p?.slug == "raichu"',
            ],
        ];
    }

    #[Test]
    #[DataProvider('providerCalculateWithOriginGeneration')]
    public function calculateWithOriginGeneration(?string $originGenerationSlug, bool $expectedAvailable): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'venusaur-f';

        if (null !== $originGenerationSlug) {
            $generation = new GameGeneration();
            $generation->slug = $originGenerationSlug;
            $generation->name = $originGenerationSlug;

            $pokemon->originGeneration = $generation;
        }

        $dex = new Dex();
        $dex->selectionRule = 'p.originGeneration?.slug === "1"';

        $calculator = new DexPokemonAvailabilityCalculator(
            $this->createStub(GameBundlesAvailabilitiesService::class),
            $this->createStub(GameBundlesShiniesAvailabilitiesService::class),
            $this->createStub(GamesAvailabilitiesService::class),
            $this->createStub(GamesShiniesAvailabilitiesService::class),
            $this->createStub(CollectionsAvailabilitiesService::class),
        );

        $dexAvailability = $calculator->calculate($dex, $pokemon);

        $this->assertSame($expectedAvailable, null !== $dexAvailability);
    }

    /**
     * @return array<string, array{0: null|string, 1: bool}>
     */
    public static function providerCalculateWithOriginGeneration(): array
    {
        return [
            'same generation' => ['1', true],
            'other generation' => ['4', false],
            'no generation' => [null, false],
        ];
    }
}
