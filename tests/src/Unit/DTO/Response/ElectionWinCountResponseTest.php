<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionWinCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionWinCountResponse::class)]
final class ElectionWinCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionWinCountResponse(
            sum: 30,
            max: 7,
        );

        self::assertSame(30, $response->sum);
        self::assertSame(7, $response->max);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $response = new ElectionWinCountResponse(
            sum: 0,
            max: 0,
        );

        self::assertSame(0, $response->sum);
        self::assertSame(0, $response->max);
    }
}
