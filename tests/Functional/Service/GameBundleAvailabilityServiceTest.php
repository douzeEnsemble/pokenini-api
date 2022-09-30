<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use App\Service\GameBundleAvailabilityService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
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

        $listDouze = $service->getFromPokemon($pokemonDouze);
        $this->assertTrue($listDouze->redgreenblueyellow);
        $this->assertFalse($listDouze->goldsilvercrystal);

        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonRepo->findOneBy(['name' => 'Bulbasaur']);

        $listBulbasaur = $service->getFromPokemon($pokemonBulbasaur);
        $this->assertTrue($listBulbasaur->redgreenblueyellow);
        $this->assertTrue($listBulbasaur->goldsilvercrystal);

        /** @var Pokemon $pokemonMegaVenusaur */
        $pokemonMegaVenusaur = $pokemonRepo->findOneBy(['name' => 'Mega Venusaur']);

        $listMegaVenusaur = $service->getFromPokemon($pokemonMegaVenusaur);
        $this->assertFalse($listMegaVenusaur->redgreenblueyellow);
        $this->assertFalse($listMegaVenusaur->goldsilvercrystal);
    }
}
