<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\TypeResponse;
use App\Factory\TypeResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TypeResponseFactory::class)]
final class TypeResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'electric',
            'name' => 'Electric',
            'french_name' => 'Électrique',
            'color' => '#FFCC33',
        ];

        $response = TypeResponseFactory::fromSqlRow($row);

        self::assertSame('electric', $response->slug);
        self::assertSame('Electric', $response->name);
        self::assertSame('Électrique', $response->frenchName);
        self::assertSame('#FFCC33', $response->color);
    }

    #[Test]
    public function fromSqlRowCastsSlugsAndNamesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
            'color' => '#ABC123',
        ];

        $response = TypeResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'electric',
                'name' => 'Electric',
                'french_name' => 'Électrique',
                'color' => '#FFCC33',
            ],
            [
                'slug' => 'water',
                'name' => 'Water',
                'french_name' => 'Eau',
                'color' => '#3399FF',
            ],
        ];

        $responses = TypeResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TypeResponse::class, $responses);
        self::assertSame('electric', $responses[0]->slug);
        self::assertSame('water', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = TypeResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
