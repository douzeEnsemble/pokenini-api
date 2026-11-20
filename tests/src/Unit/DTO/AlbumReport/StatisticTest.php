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

        $this->assertSame('douze', $statisticWithCount->slug);
        $this->assertSame('Twelve', $statisticWithCount->name);
        $this->assertSame('Douze', $statisticWithCount->frenchName);
        $this->assertSame('#e57373', $statisticWithCount->color);
        $this->assertSame(12, $statisticWithCount->count);

        $statisticWithoutCount = new Statistic(
            slug: 'zero',
            name: 'Zero',
            frenchName: 'Zéro',
            color: '#e57373',
        );

        $this->assertSame('zero', $statisticWithoutCount->slug);
        $this->assertSame('Zero', $statisticWithoutCount->name);
        $this->assertSame('Zéro', $statisticWithoutCount->frenchName);
        $this->assertSame('#e57373', $statisticWithoutCount->color);
        $this->assertSame(0, $statisticWithoutCount->count);
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

        $this->assertSame(12, $statisticWithCount->count);

        $this->assertSame(13, $statisticWithCount->increment());

        $statisticWithoutCount = new Statistic(
            slug: 'zero',
            name: 'Zero',
            frenchName: 'Zéro',
            color: '#e57373',
        );

        $this->assertSame(0, $statisticWithoutCount->count);

        $this->assertSame(1, $statisticWithoutCount->increment());
    }
}
