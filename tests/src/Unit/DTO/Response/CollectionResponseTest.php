<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CollectionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionResponse::class)]
final class CollectionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new CollectionResponse(
            slug: 'pogodynamax',
            name: 'Pokemon Go - Dynamax',
            frenchName: 'Pokemon Go - Dynamax',
            orderNumber: 52,
        );

        self::assertSame('pogodynamax', $response->slug);
        self::assertSame('Pokemon Go - Dynamax', $response->name);
        self::assertSame('Pokemon Go - Dynamax', $response->frenchName);
        self::assertSame(52, $response->orderNumber);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new CollectionResponse(
            slug: 'scarletviolet',
            name: 'Scarlet & Violet',
            frenchName: 'Écarlate & Violet',
            orderNumber: 1,
        );

        self::assertSame('scarletviolet', $response->slug);
        self::assertSame('Scarlet & Violet', $response->name);
        self::assertSame('Écarlate & Violet', $response->frenchName);
        self::assertSame(1, $response->orderNumber);
    }
}
