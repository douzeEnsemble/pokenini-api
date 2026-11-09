<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedAlbumFormResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedAlbumFormResponse::class)]
final class LocalizedAlbumFormResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedAlbumFormResponse(slug: 'starter', name: 'de Départ');

        self::assertSame('starter', $response->slug);
        self::assertSame('de Départ', $response->name);
    }
}
