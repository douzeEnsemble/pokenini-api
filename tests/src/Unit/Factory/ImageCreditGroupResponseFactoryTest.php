<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ImageCreditGroupResponse;
use App\DTO\Response\ImageCreditImageResponse;
use App\Factory\ImageCreditGroupResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditGroupResponseFactory::class)]
final class ImageCreditGroupResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromGroupedRowsBuildsOneGroupPerSourceWithItsImages(): void
    {
        $groups = ImageCreditGroupResponseFactory::fromGroupedRows([
            [
                'source' => 'PokéSprite - https://github.com/msikma/pokesprite',
                'images' => [
                    [
                        'pokemon_slug' => 'bulbasaur',
                        'pokemon_name' => 'Bulbasaur',
                        'pokemon_french_name' => 'Bulbizarre',
                        'pokemon_icon' => 'bulbasaur',
                        'size' => 'small',
                        'is_shiny' => false,
                    ],
                ],
            ],
        ]);

        self::assertCount(1, $groups);
        self::assertInstanceOf(ImageCreditGroupResponse::class, $groups[0]);
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $groups[0]->credit);
        self::assertCount(1, $groups[0]->images);

        $image = $groups[0]->images[0];
        self::assertInstanceOf(ImageCreditImageResponse::class, $image);
        self::assertSame('bulbasaur', $image->pokemonSlug);
        self::assertSame('Bulbasaur', $image->pokemonName);
        self::assertSame('Bulbizarre', $image->pokemonFrenchName);
        self::assertSame('bulbasaur', $image->pokemonIcon);
        self::assertSame('small', $image->size);
        self::assertFalse($image->isShiny);
    }

    #[Test]
    public function fromGroupedRowsBuildsMultipleImagesPerGroup(): void
    {
        $groups = ImageCreditGroupResponseFactory::fromGroupedRows([
            [
                'source' => 'Serebii - https://serebii.net',
                'images' => [
                    ['pokemon_slug' => 'a', 'pokemon_name' => 'A', 'pokemon_french_name' => 'Afr', 'pokemon_icon' => 'a', 'size' => 'small', 'is_shiny' => false],
                    ['pokemon_slug' => 'b', 'pokemon_name' => 'B', 'pokemon_french_name' => 'Bfr', 'pokemon_icon' => 'b', 'size' => 'big', 'is_shiny' => true],
                ],
            ],
        ]);

        self::assertCount(2, $groups[0]->images);
        self::assertSame('a', $groups[0]->images[0]->pokemonSlug);
        self::assertSame('b', $groups[0]->images[1]->pokemonSlug);
        self::assertTrue($groups[0]->images[1]->isShiny);
    }

    #[Test]
    public function fromGroupedRowsHandlesEmptyArray(): void
    {
        self::assertSame([], ImageCreditGroupResponseFactory::fromGroupedRows([]));
    }
}
