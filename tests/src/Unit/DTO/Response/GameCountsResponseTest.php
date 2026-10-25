<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameCountsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameCountsResponse::class)]
final class GameCountsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GameCountsResponse(
            total: 6,
            played: 5,
            inProgress: 2,
            completed: 3,
            abandoned: 1,
            players: 4,
        );

        self::assertSame(6, $response->total);
        self::assertSame(5, $response->played);
        self::assertSame(2, $response->inProgress);
        self::assertSame(3, $response->completed);
        self::assertSame(1, $response->abandoned);
        self::assertSame(4, $response->players);
    }
}
