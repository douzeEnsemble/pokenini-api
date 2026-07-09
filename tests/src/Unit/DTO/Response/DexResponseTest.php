<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexResponse;
use App\Factory\ElectionReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexResponse::class)]
final class DexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );
        $report = ElectionReportResponseFactory::fromReport(new Report([], [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 22,
            'max_view_count' => 0,
            'dex_total_count' => 22,
        ]));

        $response = new DexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            flags: $flags,
            description: 'The National Dex in Home',
            frenchDescription: 'Le Pokédex National dans Home',
            dexTotalCount: 22,
            report: $report,
        );

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame($flags, $response->flags);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
        self::assertSame(22, $response->dexTotalCount);
        self::assertSame($report, $response->report);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: true,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: false,
            isReleased: true,
            isPremium: true,
            isCustom: false,
        );
        $report = ElectionReportResponseFactory::fromReport(new Report([], [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 7,
            'max_view_count' => 0,
            'dex_total_count' => 7,
        ]));

        $response = new DexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            flags: $flags,
            description: '',
            frenchDescription: '',
            dexTotalCount: 7,
            report: $report,
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertTrue($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertTrue($response->flags->isPremium);
        self::assertSame(7, $response->dexTotalCount);
        self::assertSame($report, $response->report);
    }
}
