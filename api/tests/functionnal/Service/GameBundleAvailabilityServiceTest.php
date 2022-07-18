<?php

namespace App\Tests\functionnal\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use App\Service\GameBundleAvailabilityService;
use App\Tests\resources\functionnal\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameBundleAvailabilityServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetFromPokemon(): void
    {
        /** @var GameBundleAvailabilityService $service */
        $service = static::getContainer()->get(GameBundleAvailabilityService::class);

        /** @var PokemonRepository $pokemonRepo */
        $pokemonRepo = static::getContainer()->get(PokemonRepository::class);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonRepo->findOneBy(['name' => 'Douze']);
        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonRepo->findOneBy(['name' => 'Bulbasaur']);

        $listDouze = $service->getFromPokemon($pokemonDouze);
        $this->assertTrue(isset($listDouze->redgreenblueyellow));
        $this->assertTrue(isset($listDouze->goldsilvercrystal));
        $this->assertTrue($listDouze->redgreenblueyellow);
        $this->assertFalse($listDouze->goldsilvercrystal);

        $listBulbasaur = $service->getFromPokemon($pokemonBulbasaur);
        $this->assertFalse(isset($listBulbasaur->redgreenblueyellow));
        $this->assertFalse(isset($listBulbasaur->goldsilvercrystal));
        $this->assertNull($listBulbasaur->redgreenblueyellow);
        $this->assertNull($listBulbasaur->goldsilvercrystal);
    }
}
