<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormDebugResponse::class)]
final class FormDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new FormDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'mega',
            name: 'Mega',
            frenchName: 'Méga',
            orderNumber: 2,
            deletedAt: '2024-03-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->identifier);
        self::assertSame('mega', $response->slug);
        self::assertSame('Mega', $response->name);
        self::assertSame('Méga', $response->frenchName);
        self::assertSame(2, $response->orderNumber);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new FormDebugResponse(
            identifier: null,
            slug: 'totem',
            name: 'Totem',
            frenchName: 'Totem',
            orderNumber: 5,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->deletedAt);
    }
}
