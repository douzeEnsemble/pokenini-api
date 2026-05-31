<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionMetricsResponse;
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
        $response = new ElectionMetricsResponse(
            viewCountSum: 9,
            winCountSum: 6,
            viewCountMax: 3,
            winCountMax: 3,
            underMaxViewCount: 1,
            maxViewCount: 1,
            dexTotalCount: 7,
        );

        self::assertSame(9, $response->viewCountSum);
        self::assertSame(6, $response->winCountSum);
        self::assertSame(3, $response->viewCountMax);
        self::assertSame(3, $response->winCountMax);
        self::assertSame(1, $response->underMaxViewCount);
        self::assertSame(1, $response->maxViewCount);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ElectionMetricsResponse(
            viewCountSum: 0,
            winCountSum: 0,
            viewCountMax: 0,
            winCountMax: 0,
            underMaxViewCount: 21,
            maxViewCount: 21,
            dexTotalCount: 21,
        );

        self::assertSame(0, $response->viewCountSum);
        self::assertSame(0, $response->winCountSum);
        self::assertSame(0, $response->viewCountMax);
        self::assertSame(0, $response->winCountMax);
        self::assertSame(21, $response->underMaxViewCount);
        self::assertSame(21, $response->maxViewCount);
        self::assertSame(21, $response->dexTotalCount);
    }
}
