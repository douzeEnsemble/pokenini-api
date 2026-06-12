<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\AlbumReport;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Report::class)]
final class ReportTest extends TestCase
{
    public function testConstructor(): void
    {
        $report = new Report(
            12,
            1,
            5,
            [
                new Statistic(slug: 'no', name: 'No', frenchName: 'Non', color: '#e57373', count: 1),
                new Statistic(slug: 'yes', name: 'Yes', frenchName: 'Yes', color: '#81c784', count: 1),
            ]
        );

        $this->assertEquals(12, $report->total);
        $this->assertEquals(1, $report->totalCaught);
        $this->assertEquals(5, $report->totalUncaught);
        $this->assertCount(2, $report->detail);
    }
}
