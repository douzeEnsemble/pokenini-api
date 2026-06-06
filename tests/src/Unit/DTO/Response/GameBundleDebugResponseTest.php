<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleDebugResponse::class)]
final class GameBundleDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $generation = new GameGenerationDebugResponse(
            identifier: null,
            slug: '6',
            name: '6',
            deletedAt: null,
        );

        $response = new GameBundleDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440002',
            slug: 'xy',
            name: 'X/Y',
            frenchName: 'X/Y',
            orderNumber: 6,
            generation: $generation,
            deletedAt: '2024-06-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440002', $response->identifier);
        self::assertSame('xy', $response->slug);
        self::assertSame('X/Y', $response->name);
        self::assertSame('X/Y', $response->frenchName);
        self::assertSame(6, $response->orderNumber);
        self::assertSame($generation, $response->generation);
        self::assertSame('2024-06-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $generation = new GameGenerationDebugResponse(
            identifier: null,
            slug: '1',
            name: '1',
            deletedAt: null,
        );

        $response = new GameBundleDebugResponse(
            identifier: null,
            slug: 'redgreenblueyellow',
            name: 'Red/Green/Blue/Yellow',
            frenchName: 'Rouge/Vert/Bleu/Jaune',
            orderNumber: 1,
            generation: $generation,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
