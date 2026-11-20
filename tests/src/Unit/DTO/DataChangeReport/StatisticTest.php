<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\DataChangeReport;

use App\DTO\DataChangeReport\Statistic;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Statistic::class)]
final class StatisticTest extends TestCase
{
    #[Test]
    public function constructor(): void
    {
        $statisticWithCount = new Statistic('douze', 12);

        $this->assertSame('douze', $statisticWithCount->slug);
        $this->assertSame(12, $statisticWithCount->count);

        $statisticWithoutCount = new Statistic('zero');

        $this->assertSame('zero', $statisticWithoutCount->slug);
        $this->assertSame(0, $statisticWithoutCount->count);
    }

    #[Test]
    public function increment(): void
    {
        $statisticWithCount = new Statistic('douze', 12);

        $this->assertSame(12, $statisticWithCount->count);

        $statisticWithCount->increment();
        $this->assertSame(13, $statisticWithCount->count);

        $statisticWithoutCount = new Statistic('zero');

        $this->assertSame(0, $statisticWithoutCount->count);

        $statisticWithoutCount->increment();
        $this->assertSame(1, $statisticWithoutCount->count);
    }

    #[Test]
    public function incrementBy(): void
    {
        $statistic = new Statistic('douze', 12);

        $this->assertSame(12, $statistic->count);

        $statistic->incrementBy(1);
        $this->assertSame(13, $statistic->count);

        $statistic->incrementBy(3);
        $this->assertSame(16, $statistic->count);
    }
}
