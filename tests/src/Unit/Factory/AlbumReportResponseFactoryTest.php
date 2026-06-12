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
        $stat1 = new Statistic(slug: 'no', name: 'No', frenchName: 'Non', color: '#e57373', count: 3);
        $stat2 = new Statistic(slug: 'yes', name: 'Yes', frenchName: 'Oui', color: '#66bb6a', count: 5);
        $report = new Report(10, 5, 2, [$stat1, $stat2]);

        $result = AlbumReportResponseFactory::fromReport($report);

        self::assertSame(10, $result->total);
        self::assertSame(5, $result->totalCaught);
        self::assertSame(2, $result->totalUncaught);
        self::assertCount(2, $result->detail);
    }

    #[Test]
    public function fromReportMapsStatisticCatchStateCorrectly(): void
    {
        $stat = new Statistic(slug: 'maybe', name: 'Maybe', frenchName: 'Peut être', color: 'blue', count: 7);
        $report = new Report(7, 0, 7, [$stat]);

        $result = AlbumReportResponseFactory::fromReport($report);

        $detail = $result->detail[0];
        self::assertInstanceOf(AlbumReportStatisticResponse::class, $detail);
        self::assertSame(7, $detail->count);
        self::assertSame('maybe', $detail->catchState->slug);
        self::assertSame('Maybe', $detail->catchState->name);
        self::assertSame('Peut être', $detail->catchState->frenchName);
        self::assertSame('blue', $detail->catchState->color);
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
