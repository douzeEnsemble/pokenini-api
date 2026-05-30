<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\CatchStateResponse;
use App\Factory\CatchStateResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchStateResponseFactory::class)]
final class CatchStateResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'no',
            'name' => 'No',
            'french_name' => 'Non',
            'color' => '#e57373',
        ];

        $response = CatchStateResponseFactory::fromSqlRow($row);

        self::assertSame('no', $response->slug);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame('#e57373', $response->color);
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

        $response = CatchStateResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->name);
        self::assertSame('789', $response->frenchName);
        self::assertSame('#ABC123', $response->color);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'no',
                'name' => 'No',
                'french_name' => 'Non',
                'color' => '#e57373',
            ],
            [
                'slug' => 'yes',
                'name' => 'Yes',
                'french_name' => 'Oui',
                'color' => '#66bb6a',
            ],
        ];

        $responses = CatchStateResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(CatchStateResponse::class, $responses);
        self::assertSame('no', $responses[0]->slug);
        self::assertSame('yes', $responses[1]->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = CatchStateResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
