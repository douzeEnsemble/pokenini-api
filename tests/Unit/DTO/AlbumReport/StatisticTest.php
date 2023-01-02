<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\AlbumReport;

use App\DTO\AlbumReport\Statistic;
use PHPUnit\Framework\TestCase;

class StatisticTest extends TestCase
{
    public function testConstructor(): void
    {
        $statisticWithCount = new Statistic('douze', 'Twelve', 'Douze', 12);

        $this->assertEquals('douze', $statisticWithCount->slug);
        $this->assertEquals('Twelve', $statisticWithCount->name);
        $this->assertEquals('Douze', $statisticWithCount->frenchName);
        $this->assertEquals(12, $statisticWithCount->count);

        $statisticWithoutCount = new Statistic('zero', 'Zero', 'Zéro');

        $this->assertEquals('zero', $statisticWithoutCount->slug);
        $this->assertEquals('Zero', $statisticWithoutCount->name);
        $this->assertEquals('Zéro', $statisticWithoutCount->frenchName);
        $this->assertEquals(0, $statisticWithoutCount->count);
    }

    public function testIncrement(): void
    {
        $statisticWithCount = new Statistic('douze', 'Twelve', 'Douze', 12);

        $this->assertEquals(12, $statisticWithCount->count);

        $this->assertEquals(13, $statisticWithCount->increment());

        $statisticWithoutCount = new Statistic('zero', 'Zero', 'Zéro');

        $this->assertEquals(0, $statisticWithoutCount->count);

        $this->assertEquals(1, $statisticWithoutCount->increment());
    }
}
