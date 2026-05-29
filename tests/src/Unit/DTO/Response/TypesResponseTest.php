<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TypesResponse::class)]
final class TypesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $primary = new TypeResponse('fire', 'Fire', 'Feu', '#FF4422');
        $secondary = new TypeResponse('flying', 'Flying', 'Vol', '#89AADD');

        $response = new TypesResponse(
            primary: $primary,
            secondary: $secondary,
        );

        self::assertSame($primary, $response->primary);
        self::assertSame($secondary, $response->secondary);
    }

    #[Test]
    public function constructorAcceptsNullProperties(): void
    {
        $response = new TypesResponse(
            primary: null,
            secondary: null,
        );

        self::assertNull($response->primary);
        self::assertNull($response->secondary);
    }
}
