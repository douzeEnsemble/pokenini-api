<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionMetricsCompletionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionMetricsCompletionResponse::class)]
final class ElectionMetricsCompletionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionMetricsCompletionResponse(
            atMaxCount: 15,
            underMaxCount: 3,
        );

        self::assertSame(15, $response->atMaxCount);
        self::assertSame(3, $response->underMaxCount);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $response = new ElectionMetricsCompletionResponse(
            atMaxCount: 0,
            underMaxCount: 0,
        );

        self::assertSame(0, $response->atMaxCount);
        self::assertSame(0, $response->underMaxCount);
    }
}
