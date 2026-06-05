<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumRegionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumRegionResponse::class)]
final class AlbumRegionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumRegionResponse(
            name: 'Kanto',
            frenchName: 'Kanto',
        );

        self::assertSame('Kanto', $response->name);
        self::assertSame('Kanto', $response->frenchName);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumRegionResponse(
            name: 'Johto',
            frenchName: 'Johto',
        );

        self::assertSame('Johto', $response->name);
        self::assertSame('Johto', $response->frenchName);
    }
}
