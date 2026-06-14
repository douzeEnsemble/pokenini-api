<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionEloScoreResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionEloScoreResponse::class)]
final class ElectionEloScoreResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ElectionEloScoreResponse(
            elo: 1523.75,
            significance: true,
        );

        self::assertSame(1523.75, $response->elo);
        self::assertTrue($response->significance);
    }

    #[Test]
    public function constructorHandlesFalseSignificance(): void
    {
        $response = new ElectionEloScoreResponse(
            elo: 1000.0,
            significance: false,
        );

        self::assertSame(1000.0, $response->elo);
        self::assertFalse($response->significance);
    }
}
