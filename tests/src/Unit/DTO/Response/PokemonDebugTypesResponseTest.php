<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDebugTypesResponse;
use App\DTO\Response\TypeDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugTypesResponse::class)]
final class PokemonDebugTypesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $primary = new TypeDebugResponse(null, 'grass', 'Grass', 'Plante', 3, '#78C850', null);
        $secondary = new TypeDebugResponse(null, 'poison', 'Poison', 'Poison', 4, '#A040A0', null);

        $response = new PokemonDebugTypesResponse(
            primary: $primary,
            secondary: $secondary,
        );

        self::assertSame($primary, $response->primary);
        self::assertSame($secondary, $response->secondary);
    }

    #[Test]
    public function constructorAcceptsNullProperties(): void
    {
        $response = new PokemonDebugTypesResponse(
            primary: null,
            secondary: null,
        );

        self::assertNull($response->primary);
        self::assertNull($response->secondary);
    }
}
