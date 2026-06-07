<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionViewCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionViewCountResponse::class)]
final class ElectionViewCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionViewCountResponse(
            sum: 42,
            max: 10,
        );

        self::assertSame(42, $response->sum);
        self::assertSame(10, $response->max);
    }

    #[Test]
    public function constructorHandlesZeroValues(): void
    {
        $response = new ElectionViewCountResponse(
            sum: 0,
            max: 0,
        );

        self::assertSame(0, $response->sum);
        self::assertSame(0, $response->max);
    }
}
