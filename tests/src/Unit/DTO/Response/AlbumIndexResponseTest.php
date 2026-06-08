<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumIndexResponse;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\DexFlagsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumIndexResponse::class)]
final class AlbumIndexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $dex = new AlbumDexResponse(
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
            description: 'Test dex',
            frenchDescription: 'Dex de test',
            version: '1.0',
        );
        $report = new AlbumReportResponse(total: 10, totalCaught: 5, totalUncaught: 3, detail: []);
        $filteredReport = new AlbumReportResponse(total: 5, totalCaught: 2, totalUncaught: 2, detail: []);

        $response = new AlbumIndexResponse(
            dex: $dex,
            pokemons: [],
            report: $report,
            filteredReport: $filteredReport,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame([], $response->pokemons);
        self::assertSame($report, $response->report);
        self::assertSame($filteredReport, $response->filteredReport);
    }

    #[Test]
    public function constructorAcceptsNullDex(): void
    {
        $report = new AlbumReportResponse(total: 0, totalCaught: 0, totalUncaught: 0, detail: []);
        $filteredReport = new AlbumReportResponse(total: 1, totalCaught: 0, totalUncaught: 1, detail: []);

        $response = new AlbumIndexResponse(
            dex: null,
            pokemons: [],
            report: $report,
            filteredReport: $filteredReport,
        );

        self::assertNull($response->dex);
        self::assertSame([], $response->pokemons);
        self::assertSame($report, $response->report);
        self::assertSame($filteredReport, $response->filteredReport);
    }
}
