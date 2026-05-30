<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\DexResponse;
use App\Factory\DexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexResponseFactory::class)]
final class DexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'slug' => 'home',
            'original_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'is_shiny' => false,
            'is_display_form' => true,
            'description' => 'The National Dex in Home',
            'french_description' => 'Le Pokédex National dans Home',
            'is_released' => true,
            'is_premium' => false,
            'dex_total_count' => 22,
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertSame(22, $response->dexTotalCount);
    }

    #[Test]
    public function fromSqlRowCastsValuesToCorrectTypes(): void
    {
        $row = [
            'slug' => 123,
            'original_slug' => 456,
            'name' => 789,
            'french_name' => 101,
            'is_shiny' => 0,
            'is_display_form' => 1,
            'description' => 202,
            'french_description' => 303,
            'is_released' => 1,
            'is_premium' => 0,
            'dex_total_count' => '7',
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->originalSlug);
        self::assertSame('789', $response->name);
        self::assertSame('101', $response->frenchName);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('202', $response->description);
        self::assertSame('303', $response->frenchDescription);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertSame(7, $response->dexTotalCount);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'slug' => 'home',
                'original_slug' => 'home',
                'name' => 'Home',
                'french_name' => 'Home',
                'is_shiny' => false,
                'is_display_form' => true,
                'description' => '',
                'french_description' => '',
                'is_released' => true,
                'is_premium' => false,
                'dex_total_count' => 22,
            ],
            [
                'slug' => 'redgreenblueyellow',
                'original_slug' => 'redgreenblueyellow',
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'is_shiny' => false,
                'is_display_form' => true,
                'description' => '',
                'french_description' => '',
                'is_released' => true,
                'is_premium' => true,
                'dex_total_count' => 7,
            ],
        ];

        $responses = DexResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(DexResponse::class, $responses);
        self::assertSame('home', $responses[0]->slug);
        self::assertSame(22, $responses[0]->dexTotalCount);
        self::assertSame('redgreenblueyellow', $responses[1]->slug);
        self::assertSame(7, $responses[1]->dexTotalCount);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = DexResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
