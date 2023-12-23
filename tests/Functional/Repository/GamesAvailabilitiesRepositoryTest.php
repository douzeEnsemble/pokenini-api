<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\Game;
use App\Entity\GameAvailability;
use App\Entity\Pokemon;
use App\Repository\GamesAvailabilitiesRepository;
use App\Repository\GamesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GamesAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameAvailabilityTrait;

    /** @var Game[] */
    private array $games;
    /** @var Pokemon[] */
    private array $pokemons;

    private GamesAvailabilitiesRepository $gamesAvailabilitiesRepo;
    private GamesRepository $gamesRepo;
    private PokemonsRepository $pokemonsRepo;

    public function setUp(): void
    {
        self::bootKernel();

        // Using temp variables is for avoid typing conflict
        /** @var GamesAvailabilitiesRepository $gamesAvailabilitiesRepo */
        $gamesAvailabilitiesRepo = static::getContainer()->get(GamesAvailabilitiesRepository::class);
        $this->gamesAvailabilitiesRepo = $gamesAvailabilitiesRepo;
        /** @var GamesRepository $gamesRepo */
        $gamesRepo = static::getContainer()->get(GamesRepository::class);
        $this->gamesRepo = $gamesRepo;
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

        /** @var Pokemon $pokemon */
        $pokemon = $this->pokemonsRepo->findOneBy(['name' => $name]);

        $this->assertNotNull($pokemon);

        $this->pokemons[$name] = $pokemon;

        return $pokemon;
    }
}
