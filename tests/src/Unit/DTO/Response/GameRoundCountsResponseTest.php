<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameRoundCountsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameRoundCountsResponse::class)]
final class GameRoundCountsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GameRoundCountsResponse(
            planned: 30,
            answered: 20,
            correct: 12,
        );

        self::assertSame(30, $response->planned);
        self::assertSame(20, $response->answered);
        self::assertSame(12, $response->correct);
    }
}
