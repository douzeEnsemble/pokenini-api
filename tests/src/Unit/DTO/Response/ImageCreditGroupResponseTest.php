<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ImageCreditGroupResponse;
use App\DTO\Response\ImageCreditImageResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditGroupResponse::class)]
final class ImageCreditGroupResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesCreditAndImages(): void
    {
        $images = [
            new ImageCreditImageResponse(
                pokemonSlug: 'bulbasaur',
                pokemonName: 'Bulbasaur',
                pokemonFrenchName: 'Bulbizarre',
                pokemonIcon: 'bulbasaur',
                size: 'small',
                isShiny: false,
            ),
        ];

        $response = new ImageCreditGroupResponse(
            credit: 'PokéSprite - https://github.com/msikma/pokesprite',
            images: $images,
        );

        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $response->credit);
        self::assertSame($images, $response->images);
    }

    #[Test]
    public function constructorInitializesEmptyImages(): void
    {
        $response = new ImageCreditGroupResponse(
            credit: 'Serebii - https://serebii.net',
            images: [],
        );

        self::assertSame('Serebii - https://serebii.net', $response->credit);
        self::assertSame([], $response->images);
    }
}
