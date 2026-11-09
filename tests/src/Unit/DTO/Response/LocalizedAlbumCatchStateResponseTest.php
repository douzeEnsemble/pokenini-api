<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedAlbumCatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedAlbumCatchStateResponse::class)]
final class LocalizedAlbumCatchStateResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedAlbumCatchStateResponse(slug: 'no', name: 'Non', color: '#e57373');

        self::assertSame('no', $response->slug);
        self::assertSame('Non', $response->name);
        self::assertSame('#e57373', $response->color);
    }
}
