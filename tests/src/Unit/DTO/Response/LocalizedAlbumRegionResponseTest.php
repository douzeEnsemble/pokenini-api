<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedAlbumRegionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedAlbumRegionResponse::class)]
final class LocalizedAlbumRegionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedAlbumRegionResponse(name: 'Kanto');

        self::assertSame('Kanto', $response->name);
    }
}
