<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumTypeResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumTypeResponse::class)]
final class AlbumTypeResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumTypeResponse(
            slug: 'grass',
            name: 'Grass',
            frenchName: 'Plante',
        );

        self::assertSame('grass', $response->slug);
        self::assertSame('Grass', $response->name);
        self::assertSame('Plante', $response->frenchName);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumTypeResponse(
            slug: 'poison',
            name: 'Poison',
            frenchName: 'Poison',
        );

        self::assertSame('poison', $response->slug);
        self::assertSame('Poison', $response->name);
        self::assertSame('Poison', $response->frenchName);
    }
}
