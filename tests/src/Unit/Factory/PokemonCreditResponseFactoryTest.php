<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ImageCreditResponse;
use App\DTO\Response\PokemonCreditResponse;
use App\Factory\PokemonCreditResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonCreditResponseFactory::class)]
final class PokemonCreditResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromRowsBuildsOneResponsePerRowWrappingNonNullCreditsOnly(): void
    {
        $responses = PokemonCreditResponseFactory::fromRows([
            [
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'small_regular_credit' => 'PokéSprite - https://github.com/msikma/pokesprite',
                'small_shiny_credit' => null,
                'big_regular_credit' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur',
                'big_shiny_credit' => null,
            ],
        ]);

        self::assertCount(1, $responses);
        $response = $responses[0];
        self::assertInstanceOf(PokemonCreditResponse::class, $response);
        self::assertSame('bulbasaur', $response->pokemonSlug);
        self::assertSame('Bulbasaur', $response->pokemonName);
        self::assertSame('Bulbizarre', $response->pokemonFrenchName);
        self::assertSame('bulbasaur', $response->pokemonIcon);

        self::assertInstanceOf(ImageCreditResponse::class, $response->smallRegularCredit);
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $response->smallRegularCredit->credit);
        self::assertNull($response->smallShinyCredit);
        self::assertInstanceOf(ImageCreditResponse::class, $response->bigRegularCredit);
        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $response->bigRegularCredit->credit);
        self::assertNull($response->bigShinyCredit);
    }

    #[Test]
    public function fromRowsBuildsAllFourNullCreditsForASpeciesWithNoCredit(): void
    {
        $responses = PokemonCreditResponseFactory::fromRows([
            [
                'pokemon_slug' => 'charmander',
                'pokemon_name' => 'Charmander',
                'pokemon_french_name' => 'Salamèche',
                'pokemon_icon' => 'charmander',
                'small_regular_credit' => null,
                'small_shiny_credit' => null,
                'big_regular_credit' => null,
                'big_shiny_credit' => null,
            ],
        ]);

        self::assertNull($responses[0]->smallRegularCredit);
        self::assertNull($responses[0]->smallShinyCredit);
        self::assertNull($responses[0]->bigRegularCredit);
        self::assertNull($responses[0]->bigShinyCredit);
    }

    #[Test]
    public function fromRowsHandlesEmptyArray(): void
    {
        self::assertSame([], PokemonCreditResponseFactory::fromRows([]));
    }
}
