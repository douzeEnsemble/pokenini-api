<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\GameBundleResponse;
use App\Factory\GameBundleResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleResponseFactory::class)]
final class GameBundleResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'name' => 'Red, Green, Blue, Yellow',
            'french_name' => 'Rouge, Vert, Bleu, Jaune',
            'slug' => 'redgreenblueyellow',
            'generation_slug' => '1',
        ];

        $response = GameBundleResponseFactory::fromSqlRow($row);

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('Red, Green, Blue, Yellow', $response->name);
        self::assertSame('Rouge, Vert, Bleu, Jaune', $response->frenchName);
        self::assertSame('1', $response->generation->slug);
    }

    #[Test]
    public function fromSqlRowCastsValuesToStrings(): void
    {
        $row = [
            'name' => 123,
            'french_name' => 456,
            'slug' => 789,
            'generation_slug' => 1,
        ];

        $response = GameBundleResponseFactory::fromSqlRow($row);

        self::assertSame('789', $response->slug);
        self::assertSame('123', $response->name);
        self::assertSame('456', $response->frenchName);
        self::assertSame('1', $response->generation->slug);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'name' => 'Red, Green, Blue, Yellow',
                'french_name' => 'Rouge, Vert, Bleu, Jaune',
                'slug' => 'redgreenblueyellow',
                'generation_slug' => '1',
            ],
            [
                'name' => 'Ruby, Sapphire, Emerald',
                'french_name' => 'Rubis, Saphir, Émeraude',
                'slug' => 'rubysapphireemerald',
                'generation_slug' => '3',
            ],
        ];

        $responses = GameBundleResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(GameBundleResponse::class, $responses);
        self::assertSame('redgreenblueyellow', $responses[0]->slug);
        self::assertSame('1', $responses[0]->generation->slug);
        self::assertSame('rubysapphireemerald', $responses[1]->slug);
        self::assertSame('3', $responses[1]->generation->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = GameBundleResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
