<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedCollectionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedCollectionResponse::class)]
final class LocalizedCollectionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedCollectionResponse(slug: 'swsh', name: 'Sword, Shield', orderNumber: 11);

        self::assertSame('swsh', $response->slug);
        self::assertSame('Sword, Shield', $response->name);
        self::assertSame(11, $response->orderNumber);
    }
}
