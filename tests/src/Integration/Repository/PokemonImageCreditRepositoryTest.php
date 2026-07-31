<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\PokemonImageCreditRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokemonImageCreditRepository::class)]
final class PokemonImageCreditRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function findAllPokemonWithCreditsReturnsOneRowPerSpeciesOrderedByNationalDexNumber(): void
    {
        $repo = self::getContainer()->get(PokemonImageCreditRepository::class);

        $result = $repo->findAllPokemonWithCredits();

        // 26 species in fixtures/pokemons.yaml, including ones with zero
        // pokemon_image_credit rows at all — this query must return all of them.
        self::assertCount(26, $result);
        self::assertSame('bulbasaur', $result[0]['pokemon_slug']);
        self::assertSame('ivysaur', $result[1]['pokemon_slug']);
        self::assertSame('venusaur', $result[2]['pokemon_slug']);
        self::assertSame('douze', $result[25]['pokemon_slug']);
    }

    #[Test]
    public function findAllPokemonWithCreditsReturnsEachOfTheFourSlotsWithTheirIndividualSources(): void
    {
        $repo = self::getContainer()->get(PokemonImageCreditRepository::class);

        $result = $repo->findAllPokemonWithCredits();

        self::assertSame(
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
            $result[0],
        );
    }

    #[Test]
    public function findAllPokemonWithCreditsReturnsNullForASpeciesWithNoCreditRowAtAll(): void
    {
        $repo = self::getContainer()->get(PokemonImageCreditRepository::class);

        $result = $repo->findAllPokemonWithCredits();

        $charmander = self::findRowBySlug($result, 'charmander');
        self::assertSame(
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
            $charmander,
        );
    }

    #[Test]
    public function findAllPokemonWithCreditsReturnsNullForASpeciesWhoseOnlyCreditRowHasANullSource(): void
    {
        $repo = self::getContainer()->get(PokemonImageCreditRepository::class);

        $result = $repo->findAllPokemonWithCredits();

        $douze = self::findRowBySlug($result, 'douze');
        self::assertSame(null, $douze['small_regular_credit']);
        self::assertSame(null, $douze['small_shiny_credit']);
        self::assertSame(null, $douze['big_regular_credit']);
        self::assertSame(null, $douze['big_shiny_credit']);
    }

    /**
     * @param array<array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, small_regular_credit: ?string, small_shiny_credit: ?string, big_regular_credit: ?string, big_shiny_credit: ?string}> $rows
     *
     * @return array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, small_regular_credit: ?string, small_shiny_credit: ?string, big_regular_credit: ?string, big_shiny_credit: ?string}
     */
    private static function findRowBySlug(array $rows, string $slug): array
    {
        foreach ($rows as $row) {
            if ($row['pokemon_slug'] === $slug) {
                return $row;
            }
        }

        self::fail(\sprintf('No row found for slug "%s"', $slug));
    }
}
