<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumFormResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumFormResponse::class)]
final class AlbumFormResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new AlbumFormResponse(
            slug: 'mega',
            name: 'Mega',
        );

        self::assertSame('mega', $response->slug);
        self::assertSame('Mega', $response->name);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumFormResponse(
            slug: 'starter',
            name: 'Starter',
        );

        self::assertSame('starter', $response->slug);
        self::assertSame('Starter', $response->name);
    }
}
