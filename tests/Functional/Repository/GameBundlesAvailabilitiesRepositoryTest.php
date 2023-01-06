<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\GameBundle;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use App\Repository\GameBundlesAvailabilitiesRepository;
use App\Repository\GameBundlesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameBundlesAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    /** @var GameBundle[] */
    private array $gameBundles;
    /** @var Pokemon[] */
    private array $pokemons;

    private GameBundlesAvailabilitiesRepository $gameBundleAvailabilityRepo;
    private GameBundlesRepository $gameBundlesRepo;
    private PokemonsRepository $pokemonsRepo;

    public function setUp(): void
    {
        self::bootKernel();

        // Using temp variables is for avoid typing conflict
        /** @var GameBundlesAvailabilitiesRepository $gameBundleAvailabilityRepo */
        $gameBundleAvailabilityRepo = static::getContainer()->get(GameBundlesAvailabilitiesRepository::class);
        $this->gameBundleAvailabilityRepo = $gameBundleAvailabilityRepo;
        /** @var GameBundlesRepository $gameBundlesRepo */
        $gameBundlesRepo = static::getContainer()->get(GameBundlesRepository::class);
        $this->gameBundlesRepo = $gameBundlesRepo;
        /** @var PokemonsRepository $pokemonsRepo */
        $pokemonsRepo = static::getContainer()->get(PokemonsRepository::class);
        $this->pokemonsRepo = $pokemonsRepo;
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

        $this->assertEquals(18, $this->gameBundleAvailabilityRepo->calculate());

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

        // Because we only look for Venusaur. Dex availability will take care of mega form
        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'Mega Venusaur');
        $this->assertIsAvailable('Gold, Silver, Crystal', 'Mega Venusaur');
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

        $listDouze = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonDouze);
        $this->assertTrue($listDouze->redgreenblueyellow);
        $this->assertFalse($listDouze->goldsilvercrystal);

        $pokemonBulbasaur = $this->getPokemon('Bulbasaur');

        $listBulbasaur = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonBulbasaur);
        $this->assertTrue($listBulbasaur->redgreenblueyellow);
        $this->assertTrue($listBulbasaur->goldsilvercrystal);

        $pokemonMegaVenusaur = $this->getPokemon('Mega Venusaur');

        $listMegaVenusaur = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonMegaVenusaur);
        $this->assertFalse($listMegaVenusaur->redgreenblueyellow);
        $this->assertFalse($listMegaVenusaur->goldsilvercrystal);
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
        $gameBundle = $this->gameBundlesRepo->findOneBy(['name' => $name]);

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
        $pokemon = $this->pokemonsRepo->findOneBy(['name' => $name]);

        $this->assertNotNull($pokemon);

        $this->pokemons[$name] = $pokemon;

        return $pokemon;
    }
}
