<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\DTO\Response\AlbumReportStatisticResponse;
use App\Factory\AlbumReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumReportResponseFactory::class)]
final class AlbumReportResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromReportMapsAllTotalsCorrectly(): void
    {
        $stat1 = new Statistic('no', 'No', 'Non', 3);
        $stat2 = new Statistic('yes', 'Yes', 'Oui', 5);
        $report = new Report(10, 5, 2, [$stat1, $stat2]);

        $result = AlbumReportResponseFactory::fromReport($report);

        self::assertSame(10, $result->total);
        self::assertSame(5, $result->totalCaught);
        self::assertSame(2, $result->totalUncaught);
        self::assertCount(2, $result->detail);
    }

    #[Test]
    public function fromReportMapsStatisticFieldsCorrectly(): void
    {
        $stat = new Statistic('maybe', 'Maybe', 'Peut être', 7);
        $report = new Report(7, 0, 7, [$stat]);

        $result = AlbumReportResponseFactory::fromReport($report);

        $detail = $result->detail[0];
        self::assertInstanceOf(AlbumReportStatisticResponse::class, $detail);
        self::assertSame('maybe', $detail->slug);
        self::assertSame('Maybe', $detail->name);
        self::assertSame('Peut être', $detail->frenchName);
        self::assertSame(7, $detail->count);
    }

    #[Test]
    public function fromReportHandlesEmptyDetail(): void
    {
        $report = new Report(0, 0, 0, []);

        $result = AlbumReportResponseFactory::fromReport($report);

        self::assertSame(0, $result->total);
        self::assertEmpty($result->detail);
    }
}
