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
#[CoversClass(PokemonCreditResponse::class)]
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
                'localized_name' => 'Bulbasaur',
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
                'localized_name' => 'Charmander',
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

    #[Test]
    public function localizedFromRowsUsesTheLocalizedName(): void
    {
        $responses = PokemonCreditResponseFactory::localizedFromRows([
            [
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'localized_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'small_regular_credit' => 'PokéSprite - https://github.com/msikma/pokesprite',
                'small_shiny_credit' => null,
                'big_regular_credit' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur',
                'big_shiny_credit' => null,
            ],
            [
                'pokemon_slug' => 'charmander',
                'pokemon_name' => 'Charmander',
                'pokemon_french_name' => 'Salamèche',
                'localized_name' => 'Salamèche',
                'pokemon_icon' => 'charmander',
                'small_regular_credit' => null,
                'small_shiny_credit' => 'Shiny - https://example.org/charmander',
                'big_regular_credit' => null,
                'big_shiny_credit' => null,
            ],
        ]);

        self::assertCount(2, $responses);

        self::assertSame('bulbasaur', $responses[0]->pokemonSlug);
        self::assertSame('Bulbizarre', $responses[0]->pokemonName);
        self::assertSame('bulbasaur', $responses[0]->pokemonIcon);
        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $responses[0]->smallRegularCredit?->credit);
        self::assertNull($responses[0]->smallShinyCredit);
        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $responses[0]->bigRegularCredit?->credit);
        self::assertNull($responses[0]->bigShinyCredit);

        self::assertSame('Salamèche', $responses[1]->pokemonName);
        self::assertNull($responses[1]->smallRegularCredit);
        self::assertSame('Shiny - https://example.org/charmander', $responses[1]->smallShinyCredit?->credit);
        self::assertNull($responses[1]->bigRegularCredit);
        self::assertNull($responses[1]->bigShinyCredit);
    }

    #[Test]
    public function localizedFromRowsHandlesEmptyArray(): void
    {
        self::assertSame([], PokemonCreditResponseFactory::localizedFromRows([]));
    }
}
