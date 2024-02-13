<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\GameBundle;
use App\Entity\GameBundleShinyAvailability;
use App\Entity\Pokemon;
use App\Repository\GameBundlesShiniesAvailabilitiesRepository;
use App\Repository\GameBundlesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleShinyAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameBundlesShiniesAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleShinyAvailabilityTrait;

    /** @var GameBundle[] */
    private array $gameBundles;
    /** @var Pokemon[] */
    private array $pokemons;

    private GameBundlesShiniesAvailabilitiesRepository $gameBundleShinyAvailabilityRepo;
    private GameBundlesRepository $gameBundlesRepo;
    private PokemonsRepository $pokemonsRepo;

    public function setUp(): void
    {
        self::bootKernel();

        // Using temp variables is for avoid typing conflict
        /** @var GameBundlesShiniesAvailabilitiesRepository $gameBundleShinyAvailabilityRepo */
        $gameBundleShinyAvailabilityRepo = static::getContainer()
            ->get(GameBundlesShiniesAvailabilitiesRepository::class);
        $this->gameBundleShinyAvailabilityRepo = $gameBundleShinyAvailabilityRepo;
        /** @var GameBundlesRepository $gameBundlesRepo */
        $gameBundlesRepo = static::getContainer()->get(GameBundlesRepository::class);
        $this->gameBundlesRepo = $gameBundlesRepo;
        /** @var PokemonsRepository $pokemonsRepo */
        $pokemonsRepo = static::getContainer()->get(PokemonsRepository::class);
        $this->pokemonsRepo = $pokemonsRepo;
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameBundleShinyAvailabilityCount());

        $this->gameBundleShinyAvailabilityRepo->removeAll();

        $this->assertEquals(0, $this->getGameBundleShinyAvailabilityCount());
    }

    public function testCalculate(): void
    {
        $this->gameBundleShinyAvailabilityRepo->removeAll();

        $this->assertEquals(16, $this->gameBundleShinyAvailabilityRepo->calculate());

        $this->assertIsNotAvailable('Red, Green, Blue, Yellow', 'Douze');
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

        $listDouze = $this->gameBundleShinyAvailabilityRepo->getFromPokemon($pokemonDouze);
        $this->assertNull($listDouze->redgreenblueyellow);
        $this->assertNull($listDouze->goldsilvercrystal);

        $pokemonBulbasaur = $this->getPokemon('Bulbasaur');

        $listBulbasaur = $this->gameBundleShinyAvailabilityRepo->getFromPokemon($pokemonBulbasaur);
        $this->assertTrue($listBulbasaur->redgreenblueyellow);
        $this->assertTrue($listBulbasaur->goldsilvercrystal);

        $pokemonMegaVenusaur = $this->getPokemon('Mega Venusaur');

        $listMegaVenusaur = $this->gameBundleShinyAvailabilityRepo->getFromPokemon($pokemonMegaVenusaur);
        $this->assertFalse($listMegaVenusaur->redgreenblueyellow);
        $this->assertFalse($listMegaVenusaur->goldsilvercrystal);
    }

    private function assertIsAvailable(string $bundleName, string $pokemonName): void
    {
        $gameBundleShinyAvailability = $this->getGameBundleShinyAvailability($bundleName, $pokemonName);

        $this->assertTrue($gameBundleShinyAvailability?->isAvailable);
    }

    private function assertIsNotAvailable(string $bundleName, string $pokemonName): void
    {
        $gameBundleShinyAvailability = $this->getGameBundleShinyAvailability($bundleName, $pokemonName);

        $this->assertNotEquals(true, $gameBundleShinyAvailability?->isAvailable);
    }

    private function getGameBundleShinyAvailability(
        string $bundleName,
        string $pokemonName
    ): ?GameBundleShinyAvailability {
        $bundle = $this->getGameBundle($bundleName);
        $pokemon = $this->getPokemon($pokemonName);

        return $this->gameBundleShinyAvailabilityRepo->findOneBy([
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
