<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\FormResponse;
use App\Factory\FormResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormResponseFactory::class)]
final class FormResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'alolan',
            'name' => 'Alolan',
            'french_name' => "d'Alola",
        ];

        $response = FormResponseFactory::fromSqlRow($row);

        self::assertSame('alolan', $response->slug);
        self::assertSame('Alolan', $response->name);
        self::assertSame("d'Alola", $response->frenchName);
    }

    #[Test]
    public function fromSqlRowCastsValuesToStrings(): void
    {
        $row = [
            'slug' => 123,
            'name' => 456,
            'french_name' => 789,
        ];

        $response = FormResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'alolan',
                'name' => 'Alolan',
                'french_name' => "d'Alola",
            ],
            [
                'slug' => 'galarian',
                'name' => 'Galarian',
                'french_name' => 'de Galar',
            ],
        ];

        $responses = FormResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(FormResponse::class, $responses);
        self::assertSame('alolan', $responses[0]->slug);
        self::assertSame('galarian', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = FormResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }

    #[Test]
    public function localizedFromSqlRowUsesTheLocalizedName(): void
    {
        $response = FormResponseFactory::localizedFromSqlRow([
            'slug' => 'alolan',
            'name' => 'Alolan',
            'french_name' => "d'Alola",
            'localized_name' => "d'Alola",
        ]);

        self::assertSame('alolan', $response->slug);
        self::assertSame("d'Alola", $response->name);
    }

    #[Test]
    public function localizedFromSqlRowCastsToStrings(): void
    {
        $response = FormResponseFactory::localizedFromSqlRow([
            'slug' => 123,
            'localized_name' => 456,
        ]);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
    }

    #[Test]
    public function localizedFromSqlRowsTransformsEveryRowInOrder(): void
    {
        $responses = FormResponseFactory::localizedFromSqlRows([
            ['slug' => 'alolan', 'localized_name' => "d'Alola"],
            ['slug' => 'galarian', 'localized_name' => 'de Galar'],
        ]);

        self::assertCount(2, $responses);
        self::assertSame('alolan', $responses[0]->slug);
        self::assertSame('de Galar', $responses[1]->name);
    }
}
