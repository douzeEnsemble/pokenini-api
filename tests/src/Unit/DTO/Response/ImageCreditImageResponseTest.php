<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ImageCreditImageResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditImageResponse::class)]
final class ImageCreditImageResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $response = new ImageCreditImageResponse(
            pokemonSlug: 'bulbasaur',
            pokemonName: 'Bulbasaur',
            pokemonFrenchName: 'Bulbizarre',
            pokemonIcon: 'bulbasaur',
            size: 'small',
            isShiny: false,
        );

        self::assertSame('bulbasaur', $response->pokemonSlug);
        self::assertSame('Bulbasaur', $response->pokemonName);
        self::assertSame('Bulbizarre', $response->pokemonFrenchName);
        self::assertSame('bulbasaur', $response->pokemonIcon);
        self::assertSame('small', $response->size);
        self::assertFalse($response->isShiny);
    }

    #[Test]
    public function constructorInitializesShinyProperty(): void
    {
        $response = new ImageCreditImageResponse(
            pokemonSlug: 'bulbasaur',
            pokemonName: 'Bulbasaur',
            pokemonFrenchName: 'Bulbizarre',
            pokemonIcon: 'bulbasaur-shiny',
            size: 'big',
            isShiny: true,
        );

        self::assertSame('bulbasaur-shiny', $response->pokemonIcon);
        self::assertSame('big', $response->size);
        self::assertTrue($response->isShiny);
    }
}
