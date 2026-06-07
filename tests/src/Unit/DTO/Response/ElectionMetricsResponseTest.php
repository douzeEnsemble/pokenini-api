<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionMetricsResponse;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsResponse::class)]
final class ElectionMetricsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $viewCount = new ElectionViewCountResponse(sum: 9, max: 3);
        $winCount = new ElectionWinCountResponse(sum: 6, max: 3);

        $response = new ElectionMetricsResponse(
            viewCount: $viewCount,
            winCount: $winCount,
            underMaxViewCount: 1,
            maxViewCount: 1,
            dexTotalCount: 7,
        );

        self::assertSame($viewCount, $response->viewCount);
        self::assertSame($winCount, $response->winCount);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $viewCount = new ElectionViewCountResponse(sum: 0, max: 0);
        $winCount = new ElectionWinCountResponse(sum: 0, max: 0);

        $response = new ElectionMetricsResponse(
            viewCount: $viewCount,
            winCount: $winCount,
            underMaxViewCount: 21,
            maxViewCount: 21,
            dexTotalCount: 21,
        );

        self::assertSame($viewCount, $response->viewCount);
        self::assertSame($winCount, $response->winCount);
        self::assertSame(21, $response->underMaxViewCount);
        self::assertSame(21, $response->maxViewCount);
        self::assertSame(21, $response->dexTotalCount);
    }
}
