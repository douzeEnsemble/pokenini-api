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
            'source_name' => 'PokemonDB',
            'source_url' => 'https://pokemondb.net',
        ]);

        self::assertSame('PokemonDB', $response->name);
        self::assertSame('https://pokemondb.net', $response->url);
    }

    #[Test]
    public function fromSqlRowCastsNonStringValuesToString(): void
    {
        $response = ImageCreditResponseFactory::fromSqlRow([
            'source_name' => 42,
            'source_url' => 7,
        ]);

        self::assertSame('42', $response->name);
        self::assertSame('7', $response->url);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRows(): void
    {
        $responses = ImageCreditResponseFactory::fromSqlRows([
            ['source_name' => 'A', 'source_url' => 'https://a.example'],
            ['source_name' => 'B', 'source_url' => 'https://b.example'],
        ]);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(ImageCreditResponse::class, $responses);
        self::assertSame('A', $responses[0]->name);
        self::assertSame('B', $responses[1]->name);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        self::assertCount(0, ImageCreditResponseFactory::fromSqlRows([]));
    }
}
