<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TypeResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TypeResponse::class)]
final class TypeResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TypeResponse(
            slug: 'electric',
            name: 'Electric',
            frenchName: 'Électrique',
            color: '#FFCC33',
        );

        self::assertSame('electric', $response->slug);
        self::assertSame('Electric', $response->name);
        self::assertSame('Électrique', $response->frenchName);
        self::assertSame('#FFCC33', $response->color);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new TypeResponse(
            slug: 'water',
            name: 'Water',
            frenchName: 'Eau',
            color: '#3399FF',
        );

        self::assertSame('water', $response->slug);
        self::assertSame('Water', $response->name);
        self::assertSame('Eau', $response->frenchName);
        self::assertSame('#3399FF', $response->color);
    }
}
