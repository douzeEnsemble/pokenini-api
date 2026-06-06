<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\RegionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RegionResponse::class)]
final class RegionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new RegionResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'kanto',
            name: 'Kanto',
            frenchName: 'Kanto',
            orderNumber: 1,
            deletedAt: '2024-01-15T10:30:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->identifier);
        self::assertSame('kanto', $response->slug);
        self::assertSame('Kanto', $response->name);
        self::assertSame('Kanto', $response->frenchName);
        self::assertSame(1, $response->orderNumber);
        self::assertSame('2024-01-15T10:30:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new RegionResponse(
            identifier: null,
            slug: 'johto',
            name: 'Johto',
            frenchName: 'Johto',
            orderNumber: 2,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertSame('johto', $response->slug);
        self::assertSame('Johto', $response->name);
        self::assertSame('Johto', $response->frenchName);
        self::assertSame(2, $response->orderNumber);
        self::assertNull($response->deletedAt);
    }
}
