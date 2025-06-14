<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\DataChangeReport;

use App\DTO\DataChangeReport\Statistic;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Statistic::class)]
class StatisticTest extends TestCase
{
    public function testConstructor(): void
    {
        $statisticWithCount = new Statistic('douze', 12);

        $this->assertEquals('douze', $statisticWithCount->slug);
        $this->assertEquals(12, $statisticWithCount->count);

        $statisticWithoutCount = new Statistic('zero');

        $this->assertEquals('zero', $statisticWithoutCount->slug);
        $this->assertEquals(0, $statisticWithoutCount->count);
    }

    public function testIncrement(): void
    {
        $statisticWithCount = new Statistic('douze', 12);

        $this->assertEquals(12, $statisticWithCount->count);

        $this->assertEquals(13, $statisticWithCount->increment());

        $statisticWithoutCount = new Statistic('zero');

        $this->assertEquals(0, $statisticWithoutCount->count);

        $this->assertEquals(1, $statisticWithoutCount->increment());
    }

    public function testIncrementBy(): void
    {
        $statistic = new Statistic('douze', 12);

        $this->assertEquals(12, $statistic->count);

        $this->assertEquals(13, $statistic->incrementBy(1));

        $this->assertEquals(16, $statistic->incrementBy(3));

        $this->assertEquals(16, $statistic->count);
    }
}
