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
            color: '#78C850',
        );

        self::assertSame('grass', $response->slug);
        self::assertSame('Grass', $response->name);
        self::assertSame('Plante', $response->frenchName);
        self::assertSame('#78C850', $response->color);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumTypeResponse(
            slug: 'poison',
            name: 'Poison',
            frenchName: 'Poison',
            color: '#A040A0',
        );

        self::assertSame('poison', $response->slug);
        self::assertSame('Poison', $response->name);
        self::assertSame('Poison', $response->frenchName);
        self::assertSame('#A040A0', $response->color);
    }
}
