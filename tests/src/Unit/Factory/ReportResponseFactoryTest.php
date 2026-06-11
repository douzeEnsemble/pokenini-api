<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;
use App\Factory\ReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportResponseFactory::class)]
final class ReportResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromCatchStateCountRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 28, 'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554'];

        $response = ReportResponseFactory::fromCatchStateCountRow($row);

        self::assertSame(28, $response->count);
        self::assertSame('7b52009b64fd0a2a49e6d8a939753077792b0554', $response->trainer->externalId);
    }

    #[Test]
    public function fromCatchStateCountRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '5', 'trainer' => 'abc'];

        $response = ReportResponseFactory::fromCatchStateCountRow($row);

        self::assertSame(5, $response->count);
        self::assertSame('abc', $response->trainer->externalId);
    }

    #[Test]
    public function fromDexUsageRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 2, 'name' => 'Red / Green', 'french_name' => 'Rouge / Vert'];

        $response = ReportResponseFactory::fromDexUsageRow($row);

        self::assertSame(2, $response->count);
        self::assertSame('Red / Green', $response->dex->name);
        self::assertSame('Rouge / Vert', $response->dex->frenchName);
    }

    #[Test]
    public function fromDexUsageRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '3', 'name' => 123, 'french_name' => 456];

        $response = ReportResponseFactory::fromDexUsageRow($row);

        self::assertSame(3, $response->count);
        self::assertSame('123', $response->dex->name);
        self::assertSame('456', $response->dex->frenchName);
    }

    #[Test]
    public function fromCatchStateUsageRowTransformsRowCorrectly(): void
    {
        $row = ['nb' => 11, 'name' => 'No', 'french_name' => 'Non', 'color' => '#e57373'];

        $response = ReportResponseFactory::fromCatchStateUsageRow($row);

        self::assertSame(11, $response->count);
        self::assertSame('No', $response->catchState->name);
        self::assertSame('Non', $response->catchState->frenchName);
        self::assertSame('#e57373', $response->catchState->color);
    }

    #[Test]
    public function fromCatchStateUsageRowCastsToCorrectTypes(): void
    {
        $row = ['nb' => '4', 'name' => 789, 'french_name' => 0, 'color' => 16711680];

        $response = ReportResponseFactory::fromCatchStateUsageRow($row);

        self::assertSame(4, $response->count);
        self::assertSame('789', $response->catchState->name);
        self::assertSame('0', $response->catchState->frenchName);
        self::assertSame('16711680', $response->catchState->color);
    }

    #[Test]
    public function fromServiceArraysBuildsReportResponseCorrectly(): void
    {
        $catchStateCounts = [
            ['nb' => 28, 'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
            ['nb' => 3, 'trainer' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4'],
        ];
        $dexUsage = [
            ['nb' => 2, 'name' => 'Home', 'french_name' => 'Home'],
        ];
        $catchStateUsage = [
            ['nb' => 11, 'name' => 'No', 'french_name' => 'Non', 'color' => '#e57373'],
        ];

        $report = ReportResponseFactory::fromServiceArrays($catchStateCounts, $dexUsage, $catchStateUsage);

        self::assertCount(2, $report->catchStateCountsDefinedByTrainer);
        self::assertContainsOnlyInstancesOf(TrainerCatchStateCountResponse::class, $report->catchStateCountsDefinedByTrainer);
        self::assertCount(1, $report->dexUsage);
        self::assertContainsOnlyInstancesOf(DexUsageResponse::class, $report->dexUsage);
        self::assertCount(1, $report->catchStateUsage);
        self::assertContainsOnlyInstancesOf(CatchStateUsageResponse::class, $report->catchStateUsage);
    }

    #[Test]
    public function fromServiceArraysHandlesEmptyArrays(): void
    {
        $report = ReportResponseFactory::fromServiceArrays([], [], []);

        self::assertCount(0, $report->catchStateCountsDefinedByTrainer);
        self::assertCount(0, $report->dexUsage);
        self::assertCount(0, $report->catchStateUsage);
    }
}
