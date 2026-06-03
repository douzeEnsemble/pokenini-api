<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumCatchStateResponse::class)]
final class AlbumCatchStateResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumCatchStateResponse(
            slug: 'yes',
            name: 'Yes',
            frenchName: 'Oui',
        );

        self::assertSame('yes', $response->slug);
        self::assertSame('Yes', $response->name);
        self::assertSame('Oui', $response->frenchName);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumCatchStateResponse(
            slug: 'maybe',
            name: 'Maybe',
            frenchName: 'Peut être',
        );

        self::assertSame('maybe', $response->slug);
        self::assertSame('Maybe', $response->name);
        self::assertSame('Peut être', $response->frenchName);
    }
}
