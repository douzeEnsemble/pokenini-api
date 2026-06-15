<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonLabelsResponse;
use App\Factory\AlbumIndexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumIndexResponseFactory::class)]
final class AlbumIndexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromPartsWithNonNullDexMapsAllParts(): void
    {
        $dex = $this->buildAlbumDexResponse();
        $pokemons = [$this->buildAlbumPokemonResponse()];
        $report = new AlbumReportResponse(total: 10, totalCaught: 5, totalUncaught: 3, detail: []);
        $filteredReport = new AlbumReportResponse(total: 5, totalCaught: 2, totalUncaught: 2, detail: []);

        $result = AlbumIndexResponseFactory::fromParts($dex, $pokemons, $report, $filteredReport);

        self::assertSame($dex, $result->dex);
        self::assertSame($pokemons, $result->pokemons);
        self::assertSame($report, $result->report);
        self::assertSame($filteredReport, $result->filteredReport);
    }

    #[Test]
    public function fromPartsWithNullDexSetsNullDex(): void
    {
        $report = new AlbumReportResponse(total: 0, totalCaught: 0, totalUncaught: 0, detail: []);
        $filteredReport = new AlbumReportResponse(total: 1, totalCaught: 0, totalUncaught: 1, detail: []);

        $result = AlbumIndexResponseFactory::fromParts(null, [], $report, $filteredReport);

        self::assertNull($result->dex);
        self::assertSame([], $result->pokemons);
        self::assertSame($report, $result->report);
        self::assertSame($filteredReport, $result->filteredReport);
    }

    private function buildAlbumDexResponse(): AlbumDexResponse
    {
        return new AlbumDexResponse(
            slug: 'national',
            originalSlug: 'national',
            name: 'National',
            frenchName: 'National',
            flags: new DexFlagsResponse(
                isShiny: false,
                isPrivate: false,
                isOnHome: true,
                isDisplayForm: false,
                isReleased: true,
                isPremium: false,
                isCustom: false,
            ),
            displayTemplate: 'list',
            region: null,
            selectionRule: '',
            description: '',
            frenchDescription: '',
            version: '1.0',
        );
    }

    private function buildAlbumPokemonResponse(): AlbumPokemonResponse
    {
        return new AlbumPokemonResponse(
            pokemon: new PokemonDataResponse(
                slug: 'bulbasaur',
                labels: new PokemonLabelsResponse(
                    name: 'Bulbasaur',
                    frenchName: 'Bulbizarre',
                    simplifiedName: null,
                    simplifiedFrenchName: null,
                    formsLabel: null,
                    formsFrenchLabel: null,
                ),
                nationalDexNumber: 1,
                regionalDexNumber: null,
                icon: null,
                familyOrder: 1,
                familyLead: null,
                originalGameBundle: null,
                orderNumber: '001',
                gameBundles: new GameBundlesGroupResponse(normal: [], shiny: []),
            ),
            catchState: null,
            forms: null,
            types: new AlbumTypesResponse(primary: null, secondary: null),
        );
    }
}
