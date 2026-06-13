<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsResponseFactory::class)]
final class ElectionMetricsResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromArrayTransformsDataCorrectly(): void
    {
        $data = [
            'view_count_sum' => 9,
            'win_count_sum' => 6,
            'view_count_max' => 3,
            'win_count_max' => 3,
            'under_max_view_count' => 1,
            'max_view_count' => 1,
            'dex_total_count' => 7,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(9, $response->viewCount->sum);
        self::assertSame(3, $response->viewCount->max);
        self::assertSame(6, $response->winCount->sum);
        self::assertSame(3, $response->winCount->max);
        self::assertSame(1, $response->completion->atMaxCount);
        self::assertSame(1, $response->completion->underMaxCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesZeroValues(): void
    {
        $data = [
            'view_count_sum' => 0,
            'win_count_sum' => 0,
            'view_count_max' => 0,
            'win_count_max' => 0,
            'under_max_view_count' => 15,
            'max_view_count' => 15,
            'dex_total_count' => 21,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(0, $response->viewCount->sum);
        self::assertSame(0, $response->viewCount->max);
        self::assertSame(0, $response->winCount->sum);
        self::assertSame(0, $response->winCount->max);
        self::assertSame(15, $response->completion->atMaxCount);
        self::assertSame(15, $response->completion->underMaxCount);
        self::assertSame(21, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayHandlesLargeValues(): void
    {
        $data = [
            'view_count_sum' => 100000,
            'win_count_sum' => 75000,
            'view_count_max' => 500,
            'win_count_max' => 499,
            'under_max_view_count' => 3,
            'max_view_count' => 2,
            'dex_total_count' => 1025,
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(100000, $response->viewCount->sum);
        self::assertSame(500, $response->viewCount->max);
        self::assertSame(75000, $response->winCount->sum);
        self::assertSame(499, $response->winCount->max);
        self::assertSame(2, $response->completion->atMaxCount);
        self::assertSame(3, $response->completion->underMaxCount);
        self::assertSame(1025, $response->dexTotalCount);
    }

    #[Test]
    public function fromArrayCastsStringValuesToInt(): void
    {
        $data = [
            'view_count_sum' => '9',
            'win_count_sum' => '6',
            'view_count_max' => '3',
            'win_count_max' => '3',
            'under_max_view_count' => '1',
            'max_view_count' => '1',
            'dex_total_count' => '7',
        ];

        $response = ElectionMetricsResponseFactory::fromArray($data);

        self::assertSame(9, $response->viewCount->sum);
        self::assertSame(3, $response->viewCount->max);
        self::assertSame(6, $response->winCount->sum);
        self::assertSame(3, $response->winCount->max);
        self::assertSame(1, $response->completion->atMaxCount);
        self::assertSame(1, $response->completion->underMaxCount);
        self::assertSame(7, $response->dexTotalCount);
    }
}
