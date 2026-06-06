<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameGenerationDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameGenerationDebugResponse::class)]
final class GameGenerationDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GameGenerationDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440001',
            slug: '6',
            name: '6',
            deletedAt: '2024-05-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440001', $response->identifier);
        self::assertSame('6', $response->slug);
        self::assertSame('6', $response->name);
        self::assertSame('2024-05-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new GameGenerationDebugResponse(
            identifier: null,
            slug: '1',
            name: '1',
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
