<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\ElectionReport;

use App\DTO\ElectionReport\Report;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Report::class)]
final class ReportTest extends TestCase
{
    #[Test]
    public function constructor(): void
    {
        $top = [
            ['pokemon_slug' => 'pikachu'],
        ];
        $metrics = [
            'view_count_sum' => 9,
            'win_count_sum' => 6,
            'view_count_max' => 3,
            'win_count_max' => 3,
            'under_max_view_count' => 1,
            'max_view_count' => 1,
            'dex_total_count' => 7,
        ];

        $report = new Report($top, $metrics);

        $this->assertSame($top, $report->top);
        $this->assertSame($metrics, $report->metrics);
    }
}
