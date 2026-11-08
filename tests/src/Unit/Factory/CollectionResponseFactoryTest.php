<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\CollectionResponse;
use App\Factory\CollectionResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionResponseFactory::class)]
final class CollectionResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'swshdynamaxadventuresbosses',
            'name' => 'Sword, Shield - Dynamax Adventures bosses',
            'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            'order_number' => 11,
        ];

        $response = CollectionResponseFactory::fromSqlRow($row);

        self::assertSame('swshdynamaxadventuresbosses', $response->slug);
        self::assertSame('Sword, Shield - Dynamax Adventures bosses', $response->name);
        self::assertSame('Sword, Shield - Boss des expéditions Dynamax', $response->frenchName);
        self::assertSame(11, $response->orderNumber);
    }

    #[Test]
    public function fromSqlRowCastsValuesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
            'order_number' => '42',
        ];

        $response = CollectionResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
        self::assertSame(42, $response->orderNumber);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'swshdynamaxadventuresbosses',
                'name' => 'Sword, Shield - Dynamax Adventures bosses',
                'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
                'order_number' => 11,
            ],
            [
                'slug' => 'pogodynamax',
                'name' => 'Pokemon Go - Dynamax',
                'french_name' => 'Pokemon Go - Dynamax',
                'order_number' => 52,
            ],
        ];

        $responses = CollectionResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(CollectionResponse::class, $responses);
        self::assertSame('swshdynamaxadventuresbosses', $responses[0]->slug);
        self::assertSame('pogodynamax', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = CollectionResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }

    #[Test]
    public function localizedFromSqlRowUsesTheLocalizedName(): void
    {
        $response = CollectionResponseFactory::localizedFromSqlRow([
            'slug' => 'swshdynamaxadventuresbosses',
            'name' => 'Sword, Shield - Dynamax Adventures bosses',
            'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            'localized_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            'order_number' => 11,
        ]);

        self::assertSame('swshdynamaxadventuresbosses', $response->slug);
        self::assertSame('Sword, Shield - Boss des expéditions Dynamax', $response->name);
        self::assertSame(11, $response->orderNumber);
    }

    #[Test]
    public function localizedFromSqlRowCastsValues(): void
    {
        $response = CollectionResponseFactory::localizedFromSqlRow([
            'slug' => 123,
            'localized_name' => 456,
            'order_number' => '42',
        ]);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame(42, $response->orderNumber);
    }

    #[Test]
    public function localizedFromSqlRowsTransformsEveryRowInOrder(): void
    {
        $responses = CollectionResponseFactory::localizedFromSqlRows([
            ['slug' => 'a', 'localized_name' => 'A', 'order_number' => 1],
            ['slug' => 'b', 'localized_name' => 'B', 'order_number' => 2],
        ]);

        self::assertCount(2, $responses);
        self::assertSame('a', $responses[0]->slug);
        self::assertSame('B', $responses[1]->name);
    }
}
