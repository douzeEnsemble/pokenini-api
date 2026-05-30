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
        ];

        $response = CollectionResponseFactory::fromSqlRow($row);

        self::assertSame('swshdynamaxadventuresbosses', $response->slug);
        self::assertSame('Sword, Shield - Dynamax Adventures bosses', $response->name);
        self::assertSame('Sword, Shield - Boss des expéditions Dynamax', $response->frenchName);
    }

    #[Test]
    public function fromSqlRowCastsValuesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
        ];

        $response = CollectionResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'swshdynamaxadventuresbosses',
                'name' => 'Sword, Shield - Dynamax Adventures bosses',
                'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            ],
            [
                'slug' => 'pogodynamax',
                'name' => 'Pokemon Go - Dynamax',
                'french_name' => 'Pokemon Go - Dynamax',
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
}
