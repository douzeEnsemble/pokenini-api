<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Pokemon;
use App\Repository\GamesAvailabilitiesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GamesAvailabilitiesRepository::class)]
final class GamesAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameAvailabilityTrait;

    /** @var Pokemon[] */
    private array $pokemons;

    private GamesAvailabilitiesRepository $gamesAvailabilitiesRepo;
    private PokemonsRepository $pokemonsRepo;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();

        // Using temp variables is for avoid typing conflict
        /** @var GamesAvailabilitiesRepository $gamesAvailabilitiesRepo */
        $gamesAvailabilitiesRepo = static::getContainer()->get(GamesAvailabilitiesRepository::class);
        $this->gamesAvailabilitiesRepo = $gamesAvailabilitiesRepo;

        /** @var PokemonsRepository $pokemonsRepo */
        $pokemonsRepo = static::getContainer()->get(PokemonsRepository::class);
        $this->pokemonsRepo = $pokemonsRepo;
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        $this->gamesAvailabilitiesRepo->removeAll();

        $this->assertEquals(0, $this->getGameAvailabilityCount());
    }

    public function testGetFromPokemon(): void
    {
        $pokemonDouze = $this->getPokemon('Douze');

        $listDouze = $this->gamesAvailabilitiesRepo->getFromPokemon($pokemonDouze);
        $this->assertNull($listDouze->nexistepas);
        $this->assertTrue($listDouze->red);
        $this->assertFalse($listDouze->green);
        $this->assertTrue($listDouze->blue);
        $this->assertFalse($listDouze->yellow);

        $pokemonBulbasaur = $this->getPokemon('Bulbasaur');

        $listBulbasaur = $this->gamesAvailabilitiesRepo->getFromPokemon($pokemonBulbasaur);
        $this->assertNull($listBulbasaur->nexistepas);
        $this->assertTrue($listBulbasaur->red);
        $this->assertTrue($listBulbasaur->green);
        $this->assertTrue($listBulbasaur->blue);
        $this->assertTrue($listBulbasaur->yellow);
    }

    private function getPokemon(string $name): Pokemon
    {
        if (isset($this->pokemons[$name])) {
            return $this->pokemons[$name];
        }

        /** @var ?Pokemon $pokemon */
        $pokemon = $this->pokemonsRepo->findOneBy(['name' => $name]);

        $this->assertNotNull($pokemon);

        $this->pokemons[$name] = $pokemon;

        return $pokemon;
    }
}
