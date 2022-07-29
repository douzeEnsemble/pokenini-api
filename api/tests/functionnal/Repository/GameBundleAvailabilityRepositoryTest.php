<?php

namespace App\Tests\Functionnal\Repository;

use App\Entity\GameBundle;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use App\Repository\GameBundleAvailabilityRepository;
use App\Repository\GameBundleRepository;
use App\Repository\PokemonRepository;
use App\Tests\Resources\functionnal\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameBundleAvailabilityRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    /** @var GameBundle[] */
    private array $gameBundles;
    /** @var Pokemon[] */
    private array $pokemons;

    private GameBundleAvailabilityRepository $gameBundleAvailabilityRepo;
    private GameBundleRepository $gameBundleRepo;
    private PokemonRepository $pokemonRepo;

    public function setUp(): void
    {
        self::bootKernel();

        // Using temp variables is for avoid typing conflict
        /** @var GameBundleAvailabilityRepository $gameBundleAvailabilityRepo */
        $gameBundleAvailabilityRepo = static::getContainer()->get(GameBundleAvailabilityRepository::class);
        $this->gameBundleAvailabilityRepo = $gameBundleAvailabilityRepo;
        /** @var GameBundleRepository $gameBundleRepo */
        $gameBundleRepo = static::getContainer()->get(GameBundleRepository::class);
        $this->gameBundleRepo = $gameBundleRepo;
        /** @var PokemonRepository $pokemonRepo */
        $pokemonRepo = static::getContainer()->get(PokemonRepository::class);
        $this->pokemonRepo = $pokemonRepo;
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameBundleAvailabilityCount());

        $this->gameBundleAvailabilityRepo->removeAll();

        $this->assertEquals(0, $this->getGameBundleAvailabilityCount());
    }

    public function testCalculate(): void
    {
        $this->gameBundleAvailabilityRepo->removeAll();

        $this->assertEquals(12, $this->gameBundleAvailabilityRepo->calculate());

//        /** @var GameBundleAvailability $item */
//        foreach ($this->gameBundleAvailabilityRepo->findAll() as $item) {
//            dump($item->bundle->name, $item->pokemon->name, $item->isAvailable);
//        }
//        exit;

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'Douze');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'Douze');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'Douze');

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'Bulbasaur');
        $this->assertIsAvailable('Gold, Silver, Crystal', 'Bulbasaur');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'Bulbasaur');

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'Ivysaur');
        $this->assertIsAvailable('Gold, Silver, Crystal', 'Ivysaur');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'Ivysaur');

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'Venusaur');
        $this->assertIsAvailable('Gold, Silver, Crystal', 'Venusaur');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'Venusaur');

        $this->assertIsNotAvailable('Red, Green, Blue, Yellow', 'Mega Venusaur');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'Mega Venusaur');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'Mega Venusaur');

        $this->assertIsNotAvailable('Red, Green, Blue, Yellow', 'Deoxys');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'Deoxys');
        $this->assertIsAvailable('Ruby, Sapphire, Emerald', 'Deoxys');

        $this->assertIsNotAvailable('Red, Green, Blue, Yellow', 'Deoxys-Attack');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'Deoxys-Attack');
        $this->assertIsAvailable('Ruby, Sapphire, Emerald', 'Deoxys-Attack');
    }

    public function testGetFromPokemon(): void
    {
        $pokemonDouze = $this->getPokemon('Douze');
        $pokemonBulbasaur = $this->getPokemon('Bulbasaur');

        $listDouze = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonDouze);
        $this->assertTrue($listDouze->redgreenblueyellow);
        $this->assertFalse($listDouze->goldsilvercrystal);

        $listBulbasaur = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonBulbasaur);
        $this->assertNull($listBulbasaur->redgreenblueyellow);
        $this->assertNull($listBulbasaur->goldsilvercrystal);
    }

    private function assertIsAvailable(string $bundleName, string $pokemonName): void
    {
        $gameBundleAvailability = $this->getGameBundleAvailability($bundleName, $pokemonName);

        $this->assertTrue($gameBundleAvailability?->isAvailable);
    }

    private function assertIsNotAvailable(string $bundleName, string $pokemonName): void
    {
        $gameBundleAvailability = $this->getGameBundleAvailability($bundleName, $pokemonName);

        $this->assertNotEquals(true, $gameBundleAvailability?->isAvailable);
    }

    private function getGameBundleAvailability(string $bundleName, string $pokemonName): ?GameBundleAvailability
    {
        $bundle = $this->getGameBundle($bundleName);
        $pokemon = $this->getPokemon($pokemonName);

        return $this->gameBundleAvailabilityRepo->findOneBy([
            'pokemon' => $pokemon,
            'bundle' => $bundle
        ]);
    }

    private function getGameBundle(string $name): GameBundle
    {
        if (isset($this->gameBundles[$name])) {
            return $this->gameBundles[$name];
        }

        /** @var GameBundle $gameBundle */
        $gameBundle = $this->gameBundleRepo->findOneBy(['name' => $name]);

        $this->assertNotNull($gameBundle);

        $this->gameBundles[$name] = $gameBundle;

        return $gameBundle;
    }

    private function getPokemon(string $name): Pokemon
    {
        if (isset($this->pokemons[$name])) {
            return $this->pokemons[$name];
        }

        /** @var Pokemon $pokemon */
        $pokemon = $this->pokemonRepo->findOneBy(['name' => $name]);

        $this->assertNotNull($pokemon);

        $this->pokemons[$name] = $pokemon;

        return $pokemon;
    }
}
