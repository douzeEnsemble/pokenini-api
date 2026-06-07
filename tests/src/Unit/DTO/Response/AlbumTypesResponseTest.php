<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumTypesResponse::class)]
final class AlbumTypesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $primary = new AlbumTypeResponse('grass', 'Grass', 'Plante');
        $secondary = new AlbumTypeResponse('poison', 'Poison', 'Poison');

        $response = new AlbumTypesResponse(
            primary: $primary,
            secondary: $secondary,
        );

        self::assertSame($primary, $response->primary);
        self::assertSame($secondary, $response->secondary);
    }

    #[Test]
    public function constructorAcceptsNullProperties(): void
    {
        $response = new AlbumTypesResponse(
            primary: null,
            secondary: null,
        );

        self::assertNull($response->primary);
        self::assertNull($response->secondary);
    }
}
