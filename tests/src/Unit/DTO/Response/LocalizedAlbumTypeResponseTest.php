<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedAlbumTypeResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedAlbumTypeResponse::class)]
final class LocalizedAlbumTypeResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedAlbumTypeResponse(slug: 'grass', name: 'Plante', color: '#78C850');

        self::assertSame('grass', $response->slug);
        self::assertSame('Plante', $response->name);
        self::assertSame('#78C850', $response->color);
    }
}
