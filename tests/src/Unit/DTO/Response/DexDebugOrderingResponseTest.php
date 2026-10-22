<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexDebugOrderingResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexDebugOrderingResponse::class)]
final class DexDebugOrderingResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $ordering = new DexDebugOrderingResponse(main: 10, election: 3, colorGame: 4, shinyGame: 5, whichShinyGame: 6);

        self::assertSame(10, $ordering->main);
        self::assertSame(3, $ordering->election);
        self::assertSame(4, $ordering->colorGame);
        self::assertSame(5, $ordering->shinyGame);
        self::assertSame(6, $ordering->whichShinyGame);
    }

    #[Test]
    public function constructorAcceptsZeroValues(): void
    {
        $ordering = new DexDebugOrderingResponse(main: 0, election: 0, colorGame: 0, shinyGame: 0, whichShinyGame: 0);

        self::assertSame(0, $ordering->main);
        self::assertSame(0, $ordering->election);
        self::assertSame(0, $ordering->colorGame);
        self::assertSame(0, $ordering->shinyGame);
        self::assertSame(0, $ordering->whichShinyGame);
    }
}
