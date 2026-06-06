<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TypeDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TypeDebugResponse::class)]
final class TypeDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TypeDebugResponse(
            identifier: '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            slug: 'grass',
            name: 'Grass',
            frenchName: 'Plante',
            orderNumber: 3,
            color: '#78C850',
            deletedAt: '2024-04-01T00:00:00+00:00',
        );

        self::assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', $response->identifier);
        self::assertSame('grass', $response->slug);
        self::assertSame('Grass', $response->name);
        self::assertSame('Plante', $response->frenchName);
        self::assertSame(3, $response->orderNumber);
        self::assertSame('#78C850', $response->color);
        self::assertSame('2024-04-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new TypeDebugResponse(
            identifier: null,
            slug: 'poison',
            name: 'Poison',
            frenchName: 'Poison',
            orderNumber: 4,
            color: '#A040A0',
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
