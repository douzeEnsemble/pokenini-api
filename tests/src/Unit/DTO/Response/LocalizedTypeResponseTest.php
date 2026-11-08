<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedTypeResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedTypeResponse::class)]
final class LocalizedTypeResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedTypeResponse(
            slug: 'electric',
            name: 'Électrique',
            color: '#FFCC33',
        );

        self::assertSame('electric', $response->slug);
        self::assertSame('Électrique', $response->name);
        self::assertSame('#FFCC33', $response->color);
    }
}
