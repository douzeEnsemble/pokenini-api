<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\DexCategoryResponse;
use App\Factory\DexCategoryResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexCategoryResponseFactory::class)]
#[CoversClass(DexCategoryResponse::class)]
final class DexCategoryResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowWithCategory(): void
    {
        $response = DexCategoryResponseFactory::fromSqlRow([
            'slug' => 'home',
            'category_slug' => 'main',
            'category_name' => 'Main games',
            'category_french_name' => 'Jeux principaux',
            'category_order_number' => '2',
        ]);

        self::assertInstanceOf(DexCategoryResponse::class, $response);
        self::assertSame('main', $response->slug);
        self::assertSame('Main games', $response->name);
        self::assertSame('Jeux principaux', $response->frenchName);
        self::assertSame(2, $response->orderNumber);
    }

    #[Test]
    public function fromSqlRowWithoutCategory(): void
    {
        $response = DexCategoryResponseFactory::fromSqlRow([
            'slug' => 'home',
            'category_slug' => null,
            'category_name' => null,
            'category_french_name' => null,
            'category_order_number' => null,
        ]);

        self::assertNull($response);
    }
}
