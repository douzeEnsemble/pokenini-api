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
            'is_private' => false,
            'is_on_home' => false,
            'is_display_form' => true,
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
            'description' => 'The National Dex in Home',
            'french_description' => 'Le Pokédex National dans Home',
            'dex_total_count' => 22,
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
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
            'is_private' => 0,
            'is_on_home' => 0,
            'is_display_form' => 1,
            'is_released' => 1,
            'is_premium' => 0,
            'is_custom' => 0,
            'description' => 202,
            'french_description' => 303,
            'dex_total_count' => '7',
        ];

        $response = DexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->slug);
        self::assertSame('456', $response->originalSlug);
        self::assertSame('789', $response->name);
        self::assertSame('101', $response->frenchName);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
        self::assertSame('202', $response->description);
        self::assertSame('303', $response->frenchDescription);
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
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
                'description' => '',
                'french_description' => '',
                'dex_total_count' => 22,
            ],
            [
                'slug' => 'redgreenblueyellow',
                'original_slug' => 'redgreenblueyellow',
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'is_shiny' => false,
                'is_private' => false,
                'is_on_home' => false,
                'is_display_form' => true,
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => false,
                'description' => '',
                'french_description' => '',
                'dex_total_count' => 7,
            ],
        ];

        $responses = DexResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(DexResponse::class, $responses);
        self::assertSame('home', $responses[0]->slug);
        self::assertSame(22, $responses[0]->dexTotalCount);
        self::assertFalse($responses[0]->flags->isPrivate);
        self::assertSame('redgreenblueyellow', $responses[1]->slug);
        self::assertSame(7, $responses[1]->dexTotalCount);
        self::assertTrue($responses[1]->flags->isPremium);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = DexResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
