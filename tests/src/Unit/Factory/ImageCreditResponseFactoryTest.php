<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ImageCreditResponse;
use App\Factory\ImageCreditResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditResponseFactory::class)]
final class ImageCreditResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowBuildsImageCreditResponse(): void
    {
        $response = ImageCreditResponseFactory::fromSqlRow([
            'source' => 'PokemonDB - https://pokemondb.net',
        ]);

        self::assertSame('PokemonDB - https://pokemondb.net', $response->credit);
    }

    #[Test]
    public function fromSqlRowCastsNonStringValuesToString(): void
    {
        $response = ImageCreditResponseFactory::fromSqlRow([
            'source' => 42,
        ]);

        self::assertSame('42', $response->credit);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRows(): void
    {
        $responses = ImageCreditResponseFactory::fromSqlRows([
            ['source' => 'A'],
            ['source' => 'B'],
        ]);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(ImageCreditResponse::class, $responses);
        self::assertSame('A', $responses[0]->credit);
        self::assertSame('B', $responses[1]->credit);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        self::assertCount(0, ImageCreditResponseFactory::fromSqlRows([]));
    }
}
