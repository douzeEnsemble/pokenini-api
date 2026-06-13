<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionMetricsCompletionResponse;
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
        $completion = new ElectionMetricsCompletionResponse(atMaxCount: 1, underMaxCount: 1);

        $response = new ElectionMetricsResponse(
            viewCount: $viewCount,
            winCount: $winCount,
            completion: $completion,
            dexTotalCount: 7,
        );

        self::assertSame($viewCount, $response->viewCount);
        self::assertSame($winCount, $response->winCount);
        self::assertSame($completion, $response->completion);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $viewCount = new ElectionViewCountResponse(sum: 0, max: 0);
        $winCount = new ElectionWinCountResponse(sum: 0, max: 0);
        $completion = new ElectionMetricsCompletionResponse(atMaxCount: 21, underMaxCount: 21);

        $response = new ElectionMetricsResponse(
            viewCount: $viewCount,
            winCount: $winCount,
            completion: $completion,
            dexTotalCount: 21,
        );

        self::assertSame($viewCount, $response->viewCount);
        self::assertSame($winCount, $response->winCount);
        self::assertSame($completion, $response->completion);
        self::assertSame(21, $response->dexTotalCount);
    }
}
