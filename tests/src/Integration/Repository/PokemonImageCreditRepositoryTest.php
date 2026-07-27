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
    public function findAllWithPokemonReturnsEachNonNullCreditWithItsPokemonAndSlot(): void
    {
        $repo = self::getContainer()->get(PokemonImageCreditRepository::class);

        $result = $repo->findAllWithPokemon();

        self::assertCount(5, $result);
        self::assertContains(
            [
                'source' => 'PokéSprite - https://github.com/msikma/pokesprite',
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'size' => 'small',
                'is_shiny' => false,
            ],
            $result,
        );
        self::assertContains(
            [
                'source' => 'PokéSprite - https://github.com/msikma/pokesprite',
                'pokemon_slug' => 'ivysaur',
                'pokemon_name' => 'Ivysaur',
                'pokemon_french_name' => 'Herbizarre',
                'pokemon_icon' => 'ivysaur',
                'size' => 'big',
                'is_shiny' => false,
            ],
            $result,
        );
        self::assertContains(
            [
                'source' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur',
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'size' => 'big',
                'is_shiny' => false,
            ],
            $result,
        );
        self::assertContains(
            [
                'source' => 'Bulbapedia - https://bulbapedia.bulbagarden.net',
                'pokemon_slug' => 'ivysaur',
                'pokemon_name' => 'Ivysaur',
                'pokemon_french_name' => 'Herbizarre',
                'pokemon_icon' => 'ivysaur',
                'size' => 'small',
                'is_shiny' => false,
            ],
            $result,
        );
        self::assertContains(
            [
                'source' => 'Serebii - https://serebii.net',
                'pokemon_slug' => 'venusaur',
                'pokemon_name' => 'Venusaur',
                'pokemon_french_name' => 'Florizarre',
                'pokemon_icon' => 'venusaur',
                'size' => 'small',
                'is_shiny' => false,
            ],
            $result,
        );
    }

    #[Test]
    public function findAllWithPokemonExcludesRowsWithNullSource(): void
    {
        $repo = self::getContainer()->get(PokemonImageCreditRepository::class);

        $result = $repo->findAllWithPokemon();

        foreach ($result as $row) {
            self::assertNotSame('douze', $row['pokemon_slug']);
        }
    }
}
