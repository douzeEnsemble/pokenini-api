<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\DataChangeReport;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
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
            [
                new Statistic('form_variant', 1),
                new Statistic('form_regional', 1),
            ]
        );

        $this->assertCount(2, $report->detail);
    }

    public function testMerge(): void
    {
        $reportA = new Report(
            [
                new Statistic('a', 1),
                new Statistic('b', 2),
            ]
        );
        $reportB = new Report(
            [
                new Statistic('c', 1),
            ]
        );

        $reportA->merge($reportB);

        $this->assertCount(3, $reportA->detail);
    }

    public function testJsonEncore(): void
    {
        $report = new Report([
            new Statistic('a', 1),
            new Statistic('b', 2),
        ]);

        $this->assertEquals(
            '{"a":1,"b":2}',
            (string) json_encode($report),
        );
    }
}
