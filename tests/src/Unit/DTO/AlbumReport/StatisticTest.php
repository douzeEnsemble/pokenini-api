<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\AlbumReport;

use App\DTO\AlbumReport\Statistic;
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
        $statisticWithCount = new Statistic(
            slug: 'douze',
            name: 'Twelve',
            frenchName: 'Douze',
            color: '#e57373',
            count: 12,
        );

        $this->assertEquals('douze', $statisticWithCount->slug);
        $this->assertEquals('Twelve', $statisticWithCount->name);
        $this->assertEquals('Douze', $statisticWithCount->frenchName);
        $this->assertEquals('#e57373', $statisticWithCount->color);
        $this->assertEquals(12, $statisticWithCount->count);

        $statisticWithoutCount = new Statistic(
            slug: 'zero',
            name: 'Zero',
            frenchName: 'Zéro',
            color: '#e57373',
        );

        $this->assertEquals('zero', $statisticWithoutCount->slug);
        $this->assertEquals('Zero', $statisticWithoutCount->name);
        $this->assertEquals('Zéro', $statisticWithoutCount->frenchName);
        $this->assertEquals('#e57373', $statisticWithoutCount->color);
        $this->assertEquals(0, $statisticWithoutCount->count);
    }

    #[Test]
    public function increment(): void
    {
        $statisticWithCount = new Statistic(
            slug: 'douze',
            name: 'Twelve',
            frenchName: 'Douze',
            color: '#e57373',
            count: 12,
        );

        $this->assertEquals(12, $statisticWithCount->count);

        $this->assertEquals(13, $statisticWithCount->increment());

        $statisticWithoutCount = new Statistic(
            slug: 'zero',
            name: 'Zero',
            frenchName: 'Zéro',
            color: '#e57373',
        );

        $this->assertEquals(0, $statisticWithoutCount->count);

        $this->assertEquals(1, $statisticWithoutCount->increment());
    }
}
