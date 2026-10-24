<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexCategoryResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexCategoryResponse::class)]
final class DexCategoryResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new DexCategoryResponse(
            slug: 'main',
            name: 'Main games',
            frenchName: 'Jeux principaux',
            orderNumber: 1,
        );

        self::assertSame('main', $response->slug);
        self::assertSame('Main games', $response->name);
        self::assertSame('Jeux principaux', $response->frenchName);
        self::assertSame(1, $response->orderNumber);
    }
}
