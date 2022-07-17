<?php

namespace App\Tests\functionnal\Repository;

use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use App\Repository\GameBundleAvailabilityRepository;
use App\Repository\GameBundleRepository;
use App\Repository\PokemonRepository;
use App\Tests\resources\functionnal\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameBundleAvailabilityRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameBundleAvailabilityCount());

        /** @var GameBundleAvailabilityRepository $repo */
        $repo = static::getContainer()->get(GameBundleAvailabilityRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameBundleAvailabilityCount());
    }

    public function testCalculate(): void
    {
        /** @var GameBundleAvailabilityRepository $repo */
        $repo = static::getContainer()->get(GameBundleAvailabilityRepository::class);
        $repo->removeAll();

        $this->assertEquals(4, $repo->calculate());

        /** @var GameBundleRepository $bundleRepo */
        $bundleRepo = static::getContainer()->get(GameBundleRepository::class);
        /** @var PokemonRepository $pokemonRepo */
        $pokemonRepo = static::getContainer()->get(PokemonRepository::class);

        /** @var GameBundleAvailability $gameBundleRGBY */
        $gameBundleRGBY = $bundleRepo->findOneBy(['name' => 'Red, Green, Blue, Yellow']);
        /** @var GameBundleAvailability $gameBundleGSC */
        $gameBundleGSC = $bundleRepo->findOneBy(['name' => 'Gold, Silver, Crystal']);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonRepo->findOneBy(['name' => 'Douze']);
        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonRepo->findOneBy(['name' => 'Bulbasaur']);

        /** @var GameBundleAvailability $bundleAvailabilityDouzeRGBY */
        $bundleAvailabilityDouzeRGBY = $repo->findOneBy(['pokemon' => $pokemonDouze, 'bundle' => $gameBundleRGBY]);
        $this->assertTrue($bundleAvailabilityDouzeRGBY->isAvailable);
        /** @var GameBundleAvailability $bundleAvailabilityDouzeGSC */
        $bundleAvailabilityDouzeGSC = $repo->findOneBy(['pokemon' => $pokemonDouze, 'bundle' => $gameBundleGSC]);
        $this->assertFalse($bundleAvailabilityDouzeGSC->isAvailable);

        /** @var GameBundleAvailability $bundleAvailabilityBulbasaurRGBY */
        $bundleAvailabilityBulbasaurRGBY = $repo->findOneBy([
            'pokemon' => $pokemonBulbasaur,
            'bundle' => $gameBundleRGBY
        ]);
        $this->assertTrue($bundleAvailabilityBulbasaurRGBY->isAvailable);
        /** @var GameBundleAvailability $bundleAvailabilityBulbasaurGSC */
        $bundleAvailabilityBulbasaurGSC = $repo->findOneBy([
            'pokemon' => $pokemonBulbasaur,
            'bundle' => $gameBundleGSC
        ]);
        $this->assertTrue($bundleAvailabilityBulbasaurGSC->isAvailable);
    }

    public function testGetFromPokemon(): void
    {
        /** @var GameBundleAvailabilityRepository $repo */
        $repo = static::getContainer()->get(GameBundleAvailabilityRepository::class);

        /** @var PokemonRepository $pokemonRepo */
        $pokemonRepo = static::getContainer()->get(PokemonRepository::class);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonRepo->findOneBy(['name' => 'Douze']);
        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonRepo->findOneBy(['name' => 'Bulbasaur']);

        $listDouze = $repo->getFromPokemon($pokemonDouze);
        $this->assertTrue(isset($listDouze->redgreenblueyellow));
        $this->assertTrue(isset($listDouze->goldsilvercrystal));
        $this->assertTrue($listDouze->redgreenblueyellow);
        $this->assertFalse($listDouze->goldsilvercrystal);

        $listBulbasaur = $repo->getFromPokemon($pokemonBulbasaur);
        $this->assertFalse(isset($listBulbasaur->redgreenblueyellow));
        $this->assertFalse(isset($listBulbasaur->goldsilvercrystal));
        $this->assertNull($listBulbasaur->redgreenblueyellow);
        $this->assertNull($listBulbasaur->goldsilvercrystal);
    }
}
