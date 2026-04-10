<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\GameBundle;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use App\Repository\GameBundlesAvailabilitiesRepository;
use App\Repository\GameBundlesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesAvailabilitiesRepository::class)]
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

    #[\Override]
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

        $this->assertEquals(11, $this->gameBundleAvailabilityRepo->calculate());

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'douze');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'douze');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'douze');

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'bulbasaur');
        $this->assertIsAvailable('Gold, Silver, Crystal', 'bulbasaur');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'bulbasaur');

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'ivysaur');
        $this->assertIsAvailable('Gold, Silver, Crystal', 'ivysaur');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'ivysaur');

        $this->assertIsAvailable('Red, Green, Blue, Yellow', 'venusaur');
        $this->assertIsAvailable('Gold, Silver, Crystal', 'venusaur');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'venusaur');

        $this->assertIsNotAvailable('Red, Green, Blue, Yellow', 'venusaur-mega');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'venusaur-mega');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'venusaur-mega');
        $this->assertIsAvailable('X, Y', 'venusaur-mega');

        $this->assertIsNotAvailable('Red, Green, Blue, Yellow', 'deoxys');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'deoxys');
        $this->assertIsAvailable('Ruby, Sapphire, Emerald', 'deoxys');

        $this->assertIsNotAvailable('Red, Green, Blue, Yellow', 'deoxys-attack');
        $this->assertIsNotAvailable('Gold, Silver, Crystal', 'deoxys-attack');
        $this->assertIsNotAvailable('Ruby, Sapphire, Emerald', 'deoxys-attack');
    }

    public function testGetFromPokemon(): void
    {
        $pokemonDouze = $this->getPokemon('douze');

        $listDouze = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonDouze);
        $this->assertTrue($listDouze->redgreenblueyellow);
        $this->assertFalse($listDouze->goldsilvercrystal);

        $pokemonBulbasaur = $this->getPokemon('bulbasaur');

        $listBulbasaur = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonBulbasaur);
        $this->assertTrue($listBulbasaur->redgreenblueyellow);
        $this->assertTrue($listBulbasaur->goldsilvercrystal);

        $pokemonMegaVenusaur = $this->getPokemon('venusaur-mega');

        $listMegaVenusaur = $this->gameBundleAvailabilityRepo->getFromPokemon($pokemonMegaVenusaur);
        $this->assertFalse($listMegaVenusaur->redgreenblueyellow);
        $this->assertFalse($listMegaVenusaur->goldsilvercrystal);
    }

    private function assertIsAvailable(string $bundleName, string $pokemonSlug): void
    {
        $gameBundleAvailability = $this->getGameBundleAvailability($bundleName, $pokemonSlug);

        $this->assertTrue($gameBundleAvailability?->isAvailable);
    }

    private function assertIsNotAvailable(string $bundleName, string $pokemonSlug): void
    {
        $gameBundleAvailability = $this->getGameBundleAvailability($bundleName, $pokemonSlug);

        $this->assertNotEquals(true, $gameBundleAvailability?->isAvailable);
    }

    private function getGameBundleAvailability(string $bundleName, string $pokemonSlug): ?GameBundleAvailability
    {
        $bundle = $this->getGameBundle($bundleName);
        $pokemon = $this->getPokemon($pokemonSlug);

        return $this->gameBundleAvailabilityRepo->findOneBy([
            'pokemon' => $pokemon,
            'bundle' => $bundle,
        ]);
    }

    private function getGameBundle(string $name): GameBundle
    {
        if (isset($this->gameBundles[$name])) {
            return $this->gameBundles[$name];
        }

        /** @var ?GameBundle $gameBundle */
        $gameBundle = $this->gameBundlesRepo->findOneBy(['name' => $name]);

        $this->assertNotNull($gameBundle);

        $this->gameBundles[$name] = $gameBundle;

        return $gameBundle;
    }

    private function getPokemon(string $slug): Pokemon
    {
        if (isset($this->pokemons[$slug])) {
            return $this->pokemons[$slug];
        }

        /** @var ?Pokemon $pokemon */
        $pokemon = $this->pokemonsRepo->findOneBy(['slug' => $slug]);

        $this->assertNotNull($pokemon);

        $this->pokemons[$slug] = $pokemon;

        return $pokemon;
    }
}
