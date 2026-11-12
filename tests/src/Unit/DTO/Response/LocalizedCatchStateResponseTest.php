<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedCatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedCatchStateResponse::class)]
final class LocalizedCatchStateResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedCatchStateResponse(slug: 'no', name: 'Non', color: '#e57373', textColor: '#2a0e0e');

        self::assertSame('no', $response->slug);
        self::assertSame('Non', $response->name);
        self::assertSame('#e57373', $response->color);
        self::assertSame('#2a0e0e', $response->textColor);
    }
}
